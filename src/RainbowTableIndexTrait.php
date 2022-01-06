<?php
namespace Paulodiff\RainbowTableIndex;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Paulodiff\RainbowTableIndex\RainbowTableIndexQueryBuilder;
use Paulodiff\RainbowTableIndex\RainbowTableIndexEncrypter;
use Paulodiff\RainbowTableIndex\RainbowTableIndexService;


trait RainbowTableIndexTrait
{

    public static $enableEncryption = true;
    public static $eE = [];
    public static $configFormat = [];


    public static function checkConfig()
    {
      $validator = Validator::make(self::$rainbowTableIndexConfig, self::$configFormat);

      Log::debug('RainbowTrait!config checkConfig!', [$validator] );

      if ($validator->fails()) {
        Log::error('RainbowTrait!config error!', [$validator->fails()] );
        Log::error('RainbowTrait!this error --->', [$validator->errors()] );
        Log::error('RainbowTrait!config error!', [self::$rainbowTableIndexConfig] );
        Log::error('Use this template:', [self::$configFormat] );
        die('STOP CONFIG RAINBOW INDEX ERROR!');
      }

    }

    public function generateSlug($string)
    {
      Log::debug('SluggableTrait!generateSlug!', [$string] );
      return strtolower(preg_replace(
        ['/[^\w\s]+/', '/\s+/'],
        ['', '-'],
        $string
      ));
    }

    public function isEncryptable($key)
    {
        // Log::debug('RainbowTrait!isEncryptable', [$key] );
        // Log::debug('RainbowTrait!isEncryptable', [self::$rainbowTableConfig] );
        if(self::$rainbowTableIndexConfig){
          foreach (self::$rainbowTableIndexConfig['fields'] as $index => $val) {
            if($val['fName'] == $key)
            {
              return true;
            }
          }
        }
        return false;
    }

    protected static function booted()
    {
        Log::debug('RainbowTrait!generateSlug!', ['set format'] );
        self::$configFormat =
        [
            'table' => 'array|required',
            'table.primaryKey' => 'string|required',
            'table.tableName' => 'string|required',
            'fields' => 'array|required',
            'fields.*.fName' => 'string|required',
            'fields.*.fSafeChars' => 'string|required',
            'fields.*.fType' => [
               'string',
               'required',
               Rule::in(['ENCRYPTED', 'ENCRYPTED_FULL_TEXT']),
            ],
            'fields.*.fTransform' => [
              'string',
              'required',
              Rule::in(['UPPER_CASE', 'LOWER_CASE', 'NONE']),
            ],
            'fields.*.fMinTokenLen' => 'integer|required',

        ];
        self::checkConfig();
        parent::boot();
    }





    // override save
    public function save(array $data=[])
    {
      Log::debug('RainbowTrait!SAVE!', [$data]);
      $o = parent::save($data);
      // static::query()->save();

      $rtService = new \Paulodiff\RainbowTableIndex\RainbowTableIndexService();

      // dal modello usando la configurazione prepara un elenco di campi che devono essere indicizzati poichè in una tabella i campi da
      // fulltext protrebbero essere più di uno
      $data2index = self::buildDataToIndex($this->toArray());

      $data2index = self::buildDataToIndex($this->toArray());

      // reset index data
      foreach( $data2index['fields'] as $item )
      {
        $rtService->delRT($item['tag'],$item['value']);
      }

      // set index
      foreach( $data2index['data'] as $item )
      {
        $rtService->setRT($item['tag'],$item['key'],$item['value']);
      }


      // reset and update index



      //  dd(static::query()->getModel());

      Log::debug('RainbowTrait!SAVED! model!', [$data] );
    }

    public static function rtiSanitize($s, $safeChars, $fTransform)
    {

      // SANITIZE and UPCASE...

      $SAFE_CHARS=" àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.";
      $SAFE_CHARS = $safeChars;

      Log::debug('RainbowTrait!rtiSanitize!', [$s, $safeChars, $fTransform] );

      if( $fTransform == "UPPER_CASE")
      {
        $s = strtoupper($s);
      }

      //echo "SAFE:$SAFE_CHARS\n";
      $ret = "";
      $sl = mb_strlen($s);

      for($i=0;$i<$sl;$i++)
      {
        $needle = mb_substr($s, $i, 1);
        // echo $needle . " - " . mb_strrpos( $SAFE_CHARS, $needle) . "\n";
        if ( mb_strrpos( $SAFE_CHARS, $needle) !== false  ) // mb_strstr
        {
          $ret = $ret . $needle;
        }
      }
      return $ret;
    }

    public static function rtiTokenize($s, $minTokenLen)
    {
      // splitta la stringa ' '
      // per ogni item vengono se >= della lunghezza minima vengono genarati i token

      $pieces = explode(" ", $s);
      $pieces2 = [];
      foreach($pieces as $it)
      {
        if ( strlen($it) >= $minTokenLen )
        {
          $pieces2[] = $it;
        }
      }

      Log::debug('RainbowTrait!rtiTokenize!', [$s, $minTokenLen, $pieces2] );

      $toReturn = [];
      foreach ($pieces2 as $key=>$value)
      {
        $tokens = self::rolling_window_string($value, $minTokenLen);
        foreach($tokens as $t)
        {
          $toReturn[] = $t;
        }
      }
      return $toReturn;

    }

    public static function rolling_window_string($s, $token_size)
    {
      $tokens = [];
      $str_len = strlen($s);
      if( $str_len > $token_size )
      {
        for($token_len = $token_size; $token_len <= $str_len; $token_len++)
        {
          for($start = 0; $start <= ($str_len - $token_len); $start++)
            {
              // echo "p tl:$token_len strlen:$str_len start:$start\n";
              $t = mb_substr($s, $start, $token_len);
              $tokens[] = $t;
            }
          }
      }
      else
      {
        $tokens[] = $s;
      }
      return $tokens;
    }


    // Partendo dal model e dalla configurazione costruisce l'elenco dei dati
    // da indicizzare per una riga
    // estrae tutti i campi che devono essere indicizzati

    public static function buildDataToIndex($model)
    {
      $conf = self::$rainbowTableIndexConfig;
      Log::debug('RainbowTrait!buildDataToIndex!', [$model, $conf] );

      $toIndex = [];
      $toIndex['data'] = [];
      $toIndex['fields'] = [];

      $index = -1;
      $table = "";
      $primaryKey = "";

      // $conf is ok already checked!
      $table = $conf['table']['tableName'];
      $primaryKey = $conf['table']['primaryKey'];

      // controllare se esiste il campo primaryKey in model
      if ( !array_key_exists($primaryKey, $model) )
      {
        Log::error('RainbowTrait!buildDataToIndex!NO table primaryKey in model!',[$primaryKey]);
        exit(3);
      }
      else
      {
        $index = $model[$primaryKey];
      }

      // per ogni campo in configurazione

      foreach($conf['fields'] as $item)
      {
        if($item['fType'] == 'ENCRYPTED_FULL_TEXT')
        {
          // prendo il valore dal model / data
          Log::debug('RainbowTrait!buildDataToIndex!FULLTEXT', [$item]);

          $fName =  $item['fName'];
          $fValue = $model[$fName];
          $fSafeChars = $item['fSafeChars'];
          $fTransform = $item['fTransform'];
          $fMinTokenLen = $item['fMinTokenLen'];

          $toIndex['fields'][] = [
            'tag' => $table . ":" . $fName,
            'key' => "*",
            'value' => $index
          ];

          // Sanitizza i dati
          Log::debug('RainbowTrait!buildDataToIndex!stiSanitize', [$item]);
          $data = self::rtiSanitize($fValue, $fSafeChars, $fTransform);

          // Tokenize ...
          $keyList = self::rtiTokenize($data, $fMinTokenLen);

          foreach($keyList as $t)
          {
            $toIndex['data'][] = [
              'tag' => $table . ":" . $fName,
              'key' => $t, // Decrypt
              'value' => $index,
            ];
          }
        }
      }

      Log::debug('RainbowTrait!buildDataToIndex!', [$toIndex]);

      return $toIndex;
    }


     /**
     * @return mixed
     */
    public static function getEncryptableAttributes()
    {
        return $this->encryptable;
    }

    public function setAttribute($key, $value)
    {
      // Log::debug('RainbowTrait!setAttribute', [$key, $value] );
      if ($this->isEncryptable($key) && (!is_null($value) && $value != ''))
      {
        try {
          $value = RainbowTableIndexEncrypter::encrypt($value);
        } catch (\Exception $th) {
            dd($th);
            exit(100);
        }
      }

      return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
      // Log::debug('RainbowTrait!getAttribute', [$key] );
      $value = parent::getAttribute($key);

      if ($this->isEncryptable($key) && (!is_null($value) && $value != ''))
      {
        try {
          // Log::debug('RainbowTrait!getAttribute ... @@@ decrypt1!', [$key, $value] );
          $value = RainbowTableIndexEncrypter::decrypt($value);
          // Log::debug('RainbowTrait!getAttribute ... @@@ decrypt2!', [$key, $value] );
        } catch (\Exception $th) {}
      }

      return $value;
    }

    public function attributesToArray()
    {

        $attributes = parent::attributesToArray();
        // Log::debug('SluggableTrait!attributesToArray', [$attributes] );

        if ($attributes) {
          foreach ($attributes as $key => $value)
          {
            if ($this->isEncryptable($key) && (!is_null($value)) && $value != '')
            {
              $attributes[$key] = $value;
              try {
                $attributes[$key] = RainbowTableIndexEncrypter::decrypt($value);
              } catch (\Exception $th) {
                dd($th);
              }
            }
          }
        }

        return $attributes;
    }

    public function newEloquentBuilder($query)
    {
        Log::debug('RainbowTrait!newEloquentBuilder:query', [$query] );
        Log::debug('RainbowTrait!newEloquentBuilder:conf', [self::$rainbowTableIndexConfig] );
        return RainbowTableIndexQueryBuilder::makeWithParameter($query, self::$rainbowTableIndexConfig);
        // makeWithParameter
        // return new RainbowQueryBuilder($query);
    }



    /* rigenera il rainbow Index per il $model istanziato  */
    public function rebuildRainbowIndex()
    {
      $output = [];
      Log::debug('RainbowTrait!rebuildRainbowIndex', [] );
      // static::query()->save();

      $rtService = new \Paulodiff\RainbowTableIndex\RainbowTableIndexService();

      // dal modello usando la configurazione prepara un elenco di campi che devono essere indicizzati poichè in una tabella i campi da
      // fulltext protrebbero essere più di uno

      $data2index = self::buildDataToIndex($this->toArray());

      // reset index data
      foreach( $data2index['fields'] as $item )
      {
        $rtService->delRT($item['tag'],$item['value']);
      }

      // set index
      foreach( $data2index['data'] as $item )
      {
        $rtService->setRT($item['tag'],$item['key'],$item['value']);
      }


      Log::debug('RainbowTrait!rebuildRainbowIndex', ['OK'] );
      $output[] = 'rebuildRainbowIndex:OK!';
      return $output;
    }


    /* CAUTION!!!! */
    /* Destroy from index all TAG for this model */
    /* RESET index before a rebuilding ...      */
    public static function destroyRainbowIndex()
    {
      Log::debug('RainbowTrait!destroyRainbowIndex', ['Start ... '] );

      $rtService = new \Paulodiff\RainbowTableIndex\RainbowTableIndexService();

      $conf = self::$rainbowTableIndexConfig;
      $toIndex = [];

      // $conf is ok already checked!
      $table = $conf['table']['tableName'];
      $primaryKey = $conf['table']['primaryKey'];

      // per ogni entry dell'indedice cerca di eliminare la relativa
      foreach($conf['fields'] as $k => $v)
      {
        if($v['fType'] == 'ENCRYPTED_FULL_TEXT')
        {
          $toIndex[] = [
            'tag' => $table . ":" . $v['fName'],
            'key' => '*',
            'value' => '*',
          ];
        }
      }

      Log::debug('RainbowTrait!buildDataToIndex!', [$toIndex]);

      // reset and update index
      foreach( $toIndex as $item )
      {
        Log::debug('RainbowTrait!buildDataToIndex! DESTROY...', [$item]);
        $rtService->resetRT($item['tag']);
      }
    }

    /* rigenere il rainbow Index per tutta la tabella */
    public static function rebuildFullRainbowIndex()
    {
      $output = [];
      Log::debug('RainbowTrait!rebuildFullRainbowIndex', [] );
      // static::query()->save();

      $rtService = new \Paulodiff\RainbowTableIndex\RainbowTableIndexService();

      // dal modello usando la configurazione prepara un elenco di campi che devono essere indicizzati poichè in una tabella i campi da
      // fulltext protrebbero essere più di uno
      // $data2index = self::buildDataToIndex($this->toArray());
      $o = parent::all();

      // reset and update index
      foreach( $o as $item )
      {
        // $rtService->delRT($item['tag'],$item['value']);
        // $rtService->setRT($item['tag'],$item['key'],$item['value']);
        $item->rebuildRainbowIndex();
        Log::debug('RainbowTrait!rebuildFullRainbowIndex', [$item->toArray()] );
      }

      //  dd(static::query()->getModel());
      Log::debug('RainbowTrait!rebuildFullRainbowIndex', ['END!'] );
      return $output;
    }






}
