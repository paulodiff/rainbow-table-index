<?php
namespace App\RainbowTableIndex;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

use App\RainbowTableIndex\RainbowTableIndexEncrypter;
use App\RainbowTableIndex\RainbowTableIndexService;


class RainbowTableIndexQueryBuilder extends Builder {

    protected $model;
    public $enc_fields;
    protected $rtService;

    // $q : query
    // $p : elenco campi cifrati da tenere per la cifratura
    public static function makeWithParameter($q, $p) {
        Log::debug('RainbowQueryBuilder!makeWithParameter', [$p] );
        $obj = new RainbowTableIndexQueryBuilder($q); 
        $obj->enc_fields = $p;
        // other initialization
        $obj->rtService = new \App\RainbowTableIndex\RainbowTableIndexService();
        return $obj;
    }

    /**
     * EncryptableQueryBuilder constructor.
     * @param ConnectionInterface $connection
     * @param Encryptable $model
     */
    public function __construct($query)
    {
        Log::debug('RainbowQueryBuilder! __construct', [] );
        $this->model = $this->getModel();
        parent::__construct($query);
    }
    /*
    public function __construct(ConnectionInterface $connection, $model)
    {
        parent::__construct($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
        $this->model = $model;
    }
    */

    /**
     * @param array|\Closure|string $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return Builder
     * @throws \Exception
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        Log::debug('RainbowQueryBuilder:>>>DATI>>>>', [$column, $operator, $value, $boolean] );
        Log::debug('RainbowQueryBuilder:>>>RAINBOW CONFIG>>>>', [$this->enc_fields] );
        /**
                'table' => [
            'primaryKey' => 'id',
            'tableName' => 'posts',
        ],
        'fields' => [
            [
              'fName' => 'title_enc',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              'fSafeChars' => 'AEIOU',
            ],
        ]   
         */
        // controllo se il campo è in configurazione e di che tipo

        if(!is_string($column)) 
        {
            Log::debug('RainbowQueryBuilder:>>>>>>>>> NO STRING returm immediatly SIMPLE ----->', [$column, $operator] );
            return parent::where($column, $operator, $value, $boolean);
        }

        // check type

        $tName = $this->enc_fields['table']['tableName']; 
        $primaryKey = $this->enc_fields['table']['primaryKey'];  
        $fName = ""; $fType = "";
        foreach ($this->enc_fields['fields'] as $key => $val) 
        {
            // print_r($key);
            // print_r($val);
            if($val['fName'] == $column)
            {
                $fName = $column; $fType = $val['fType'];
                Log::debug('RainbowQueryBuilder:>>> RAINBOW Field found in enc config!->', [$fName, $fType] );
            }
        }

        // se il campo deve utilizzare una RainbowTable
        if ( is_string($column) && ($operator == 'LIKE') && ($fName !== "") && ($fType == 'ENCRYPTED_FULL_TEXT') )
        {
            Log::debug('RainbowQueryBuilder:>>> RAINBOW Table --GO! ENCRYPTED_FULL_TEXT ->', [$column, $operator, $tName, $primaryKey, $fType] );
            // accesso alla rainbow table per ottenere i valori da mettere nella query tramite ServiceProvider
            $tag = $tName . ":" . $column;
            $r = $this->rtService->getRT($tag, $value);
            Log::debug('RainbowQueryBuilder:>>> RAINBOW Table --DATA!->', [$tag, $r] );
            return self::whereIn( $primaryKey , $r );
            // return self::whereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$filter->field}`), '{$salt}') USING utf8mb4) {$filter->operation} ? ", [$filter->value]);
        }
        elseif ( is_string($column) && ($fName !== "") && ($fType == 'ENCRYPTED') )
        {
            Log::debug('RainbowQueryBuilder:>>>ENCRYPTED ----->', [$column, $operator, $value] );
            $operator = RainbowTableIndexEncrypter::encrypt($operator);
            Log::debug('RainbowQueryBuilder:>>>ENCRYPTED ----->', [$column, $operator, $value] );
            return parent::where($column, $operator, $value, $boolean);
        }
        else
        // il campo può essere cifrato o meno ....
        {
            Log::debug('RainbowQueryBuilder:>>>>>>>>> SIMPLE ----->', [$column, $operator] );
            return parent::where($column, $operator, $value, $boolean);
        }

            
    }

    public function likeEncrypted($param1, $param2, $param3 = null)
    {
      $filter            = new \stdClass();
      $filter->field     = $param1;
      $filter->operation = isset($param3) ? $param2 : '=';
      $filter->value     = isset($param3) ? $param3 : $param2;

      // $salt = substr(hash('sha256', config('laravelDatabaseEncryption.encrypt_key')), 0, 16);

      return self::whereRaw("CONVERT(AES_DECRYPT(FROM_BASE64(`{$filter->field}`), '{$salt}') USING utf8mb4) {$filter->operation} ? ", [$filter->value]);
    }
}