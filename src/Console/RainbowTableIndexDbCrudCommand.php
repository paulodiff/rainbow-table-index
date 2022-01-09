<?php
namespace Paulodiff\RainbowTableIndex\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

use Paulodiff\RainbowTableIndex\RainbowTableIndexEncrypter;
use Paulodiff\RainbowTableIndex\RainbowTableIndexService;
use Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

use Paulodiff\RainbowTableIndex\Tests\Models\Author;
use Paulodiff\RainbowTableIndex\Tests\Models\Post;

class RainbowTableIndexDbCrudCommand extends Command
{
    use RainbowTableIndexTrait;
    protected $signature = 'RainbowTableIndex:dbCrud {numOfrows}';

    protected $description = 'DbCrudTest for RainbowTableIndex';

    public function handle()
    {
        $this->info('RainbowTableIndex DbCrud - search/update testE ');

        $numOfrows = $this->argument('numOfrows');
        $numOftests = $numOfrows;

        Log::channel('stderr')->info('CRUD: start!', [$numOftests] );

        
        $this->faker = Faker::create('PostCommentTest');

        // Author search test
        $a = new Author();
        Log::channel('stderr')->info('CRUD:config', [$a::$rainbowTableIndexConfig]);

        $DATA_ENCRYPTED_FULL_TEXT = [];
        $DATA_ENCRYPTED = [];

        $TIMING = [];

        // generate data for test
        // read 100 data and generate data to search...

        foreach($a::$rainbowTableIndexConfig['fields'] as $item)
        {
            Log::channel('stderr')->info('CRUD:item', [$item] );

            if ($item['fType'] == 'ENCRYPTED_FULL_TEXT')
            {
                $DATA_ENCRYPTED_FULL_TEXT[$item['fName']] = [];
                $fName_flat = substr($item['fName'], 0, -4);
                $ids = $a::select($fName_flat)->limit($numOftests)->get()->toArray();
                Log::channel('stderr')->info('CRUD:ids------->', [$ids] );

                foreach($ids as $o)
                {
                    Log::channel('stderr')->info('CRUD:', [$o]);
                    $fName =  $item['fName'];
                    $fValue = $o[$fName_flat];
                    $fSafeChars = $item['fSafeChars'];
                    $fTransform = $item['fTransform'];
                    $fMinTokenLen = $item['fMinTokenLen'];

                    $data = self::rtiSanitize($fValue, $fSafeChars, $fTransform);
                    $keyList = self::rtiTokenize($data, $fMinTokenLen);
                    Log::channel('stderr')->info('CRUD:keyList------->', [$keyList] );

                    $DATA_ENCRYPTED_FULL_TEXT[$item['fName']] = array_merge($DATA_ENCRYPTED_FULL_TEXT[$item['fName']] , $keyList);
                }
            }

            if ($item['fType'] == 'ENCRYPTED')
            {
                $DATA_ENCRYPTED[$item['fName']] = [];
                $ids = $a::select($item['fName'])->distinct()->limit(5)->get()->toArray();
                Log::channel('stderr')->info('CRUD:distinct------->', [$ids] );
                foreach($ids as $o)
                {
                    $DATA_ENCRYPTED[$item['fName']][] = $o[$item['fName']];
                }

            }

        }

        // RUN SEARCH ON DATA_ENCRYPTED_FULL_TEXT ....

        foreach($DATA_ENCRYPTED_FULL_TEXT as $k=>$v)
        {
            $fName_enc = $k;
            $fName = substr($fName_enc, 0, -4);
            // Log::channel('stderr')->info('CRUD:DATA_ENCRYPTED_FULL_TEXT------->', [$fName, $fName_enc] );

            $totalItem = count($DATA_ENCRYPTED_FULL_TEXT[$k]);
            $curItem = 0;
            $t1=0; $t2=0;
            
            foreach ($DATA_ENCRYPTED_FULL_TEXT[$k] as $v)
            {
                $curItem++;
                // Log::channel('stderr')->info('CRUD:DATA_ENCRYPTED_FULL_TEXT------->', [$k, $v] );

                $token_2_search = $v;
                $start1=hrtime(true);
                $arr1 = $a::select('id')->where($fName_enc, 'LIKE', '%' . $token_2_search . '%')->get()->toArray();
                $end1=hrtime(true);
                $eta1=$end1-$start1;
                // Log::channel('stderr')->info('Comment result encrypted field:', [$arr1] );

                // Full text search in flat field
                $start2=hrtime(true);
                $arr2 = $a::select('id')->where($fName, 'LIKE', '%' . $token_2_search . '%')->get()->toArray();
                // Log::channel('stderr')->info('Comment result      flat field:', [$arr2] );
                $end2=hrtime(true);
                $eta2=$end2-$start2;

                // Verify results ...
                if ( $arr1 === $arr2) {
                    Log::channel('stderr')->info('CRUD:' . $curItem . "#" . $totalItem . '-' . $k .']' . $token_2_search . '] same result for :', [$token_2_search, $eta1, $eta2, count($arr1), count($arr2)] );
                    $t1 += $eta1; $t2 += $eta2;
                } else {
                    Log::error('^ERROR^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ :', [count($arr1), count($arr2)] );
                    Log::error('Check mismatch :', [$fName, $token_2_search] );
                    Log::error('arr1 enc  :', [$arr1] );
                    Log::error('arr2 flat :', [$arr2] );
                    exit(9999);
                }
            }
            // echo $eta/1e+6; //nanoseconds to milliseconds
            $t1 = $t1 / $totalItem / 1e+6;
            $t2 = $t2 / $totalItem / 1e+6;
            Log::channel('stderr')->info('CRUD:TOTAL' . $curItem . "#" . $totalItem . '-' . $k .'] time (enc,flat) :', [$t1, $t2] );
            $TIMING[] = $k . '-' . $curItem . "#" . $totalItem . '-' . 'time (enc,flat) : . [' . $t1 . ',' . $t2 . ']';
        }

        // RUN SEARCH ON DATA_ENCRYPTED_FULL_TEXT !!!

        foreach($DATA_ENCRYPTED as $k=>$v)
        {
            $totalItem = count($DATA_ENCRYPTED[$k]);
            $curItem = 0;
            $fName_enc = $k;
            $fName = substr($fName_enc, 0, -4);
            // Log::channel('stderr')->info('CRUD:DATA_ENCRYPTED------->', [$fName, $fName_enc] );
            $t1=0; $t2=0;
            foreach ($DATA_ENCRYPTED[$k] as $v)
            {
                $curItem++;
                // Log::channel('stderr')->info('CRUD:DATA_ENCRYPTED------->', [$k, $v] );

                $token_2_search = $v;
                $start1=hrtime(true);
                $arr1 = $a::select('id')->where($fName_enc,  $token_2_search )->get()->toArray();
                $end1=hrtime(true);
                $eta1=$end1-$start1;
                // Log::channel('stderr')->info('Comment result encrypted field:', [$arr1] );

                // Full text search in flat field
                $start2=hrtime(true);
                $arr2 = $a::select('id')->where($fName, $token_2_search)->get()->toArray();
                // Log::channel('stderr')->info('Comment result      flat field:', [$arr2] );
                $end2=hrtime(true);
                $eta2=$end2-$start2;

                // Verify results ...
                if ( $arr1 === $arr2) {
                    Log::channel('stderr')->info('CRUD:' . $curItem . "#" . $totalItem . '-'  . $k .':'  . $token_2_search . '] same result for :', [$token_2_search, $eta1, $eta2, count($arr1), count($arr2)] );
                    $t1 += $eta1; $t2 += $eta2;
                } else {
                    Log::error('^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ :', [count($arr1), count($arr2)] );
                    Log::error('Check mismatch :', [$fName, $token_2_search] );
                    Log::error('arr1 enc  :', [$arr1] );
                    Log::error('arr2 flat :', [$arr2] );
                    exit(9999);
                }

            }
            $t1 = $t1 / $totalItem / 1e+6;
            $t2 = $t2 / $totalItem / 1e+6;
            Log::channel('stderr')->info('CRUD:TOTAL' . $curItem . "#" . $totalItem . '-' . $k .']' . $token_2_search . '] time :', [$t1, $t2] );
            $TIMING[] = $k . '-' . $curItem . "#" . $totalItem . '-' . 'time (enc,flat) : . [' . $t1 . ',' . $t2 . ']';

        }

        Log::channel('stderr')->info('CRUD:TOTAL TIMING:', [$TIMING] );





         
        Log::channel('stderr')->info('DbCrud finished!:', []);
        
    }

    private function configExists($fileName)
    {
        return File::exists(config_path($fileName));
    }

    private function shouldOverwriteConfig()
    {
        return $this->confirm(
            'Config file already exists. Do you want to overwrite it?',
            false
        );
    }

    private function publishConfiguration($forcePublish = false)
    {
        $params = [
            '--provider' => "JohnDoe\BlogPackage\BlogPackageServiceProvider",
            '--tag' => "config"
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

       $this->call('vendor:publish', $params);
    }
}