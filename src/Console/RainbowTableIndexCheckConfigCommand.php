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

class RainbowTableIndexCheckConfigCommand extends Command
{
    protected $signature = 'RainbowTableIndex:checkConfig';

    protected $description = 'CheckConfig for RainbowTableIndex';

    public function handle()
    {
        $this->info('RainbowTableIndex CheckConfig ...');

        
        Log::channel('stderr')->info('CheckConfig:', [] );

        Log::channel('stderr')->info('CheckConfig:', ['Checking Laravel Crypt and Hash function'] );
        try {

            Log::channel('stderr')->info('Encryption config:', [config('hashing.driver')] );
            

            $h1 = RainbowTableIndexEncrypter::encrypt('test');
            $h2 = RainbowTableIndexEncrypter::encrypt('test');
            $h3 = RainbowTableIndexEncrypter::encrypt('test');
            $cr1 = RainbowTableIndexEncrypter::decrypt($h1);
            $cr2 = RainbowTableIndexEncrypter::decrypt($h2);
            $cr3 = RainbowTableIndexEncrypter::decrypt($h3);
            Log::channel('stderr')->info('Encrypted:', [$h1] );
            Log::channel('stderr')->info('Encrypted:', [$h2] );
            Log::channel('stderr')->info('Encrypted:', [$h3] );
            Log::channel('stderr')->info('Decrypted:', [$cr1] );
            Log::channel('stderr')->info('Decrypted:', [$cr2] );
            Log::channel('stderr')->info('Decrypted:', [$cr3] );
        } catch (\Exception $e) {
            Log::channel('stderr')->info('CheckConfig:', ['Please check hash, encrypt Laravel config'] );
            // die("Could not use PHP SODIUM.  Please check your PHP.INI for SODIUM configuration" . $e );
            // $this->assertTrue(false);
        }


        Log::channel('stderr')->info('CheckConfig:', ['Checking .env Rainbow parameter']);
        if (    
            ( config('rainbowtableindex.key')     !==  null) && 
            ( config('rainbowtableindex.nonce')   !==  null) &&
            ( config('rainbowtableindex.encrypt') !==  null ) 
            ) {
                 // $h1 = RainbowTableIndexEncrypter::hash($test);
            Log::channel('stderr')->info('CheckConfig:', [
                'Environment vars OK!',
                config('rainbowtableindex.key'),
                config('rainbowtableindex.nonce'),
                config('rainbowtableindex.encrypt')
            ] );
            // $h2 = RainbowTableIndexEncrypter::short_hash($test);
            // Log::channel('stderr')->info('Short_Hash("test"):', [$h2] );
        } else {
            Log::channel('stderr')->info('CheckConfig:', ['!ERROR! .env parameters NOT FOUND, check config/rainbowtableindex.php configuration, add this following values to .env and run '] );
            $key = sodium_crypto_secretbox_keygen();
            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            // return  sodium_base642bin(config('rainbowtable.key') , SODIUM_BASE64_VARIANT_ORIGINAL);
            Log::channel('stderr')->info('RAINBOW_TABLE_INDEX_KEY=' . sodium_bin2base64( $key , SODIUM_BASE64_VARIANT_ORIGINAL ), [] );
            Log::channel('stderr')->info('RAINBOW_TABLE_INDEX_NONCE=' . sodium_bin2base64( $nonce , SODIUM_BASE64_VARIANT_ORIGINAL ), [] );
            Log::channel('stderr')->info('RAINBOW_TABLE_INDEX_ENCRYPT=true', ['false only for debugging purpose'] );
            // $this->assertTrue(false);
        }

        Log::channel('stderr')->info('CheckConfig:', ['Checking PHP SODIUM'] );
        try {
            $out = sodium_crypto_generichash('CHECK SODIUM');
            Log::channel('stderr')->info('CheckConfig:', ['SODIUM OK'] );
        } catch (\Exception $e) {
            Log::channel('stderr')->info('CheckConfig:', ['Could not use PHP SODIUM.  Please check your PHP.INI for SODIUM configuration'] );
            // die("Could not use PHP SODIUM.  Please check your PHP.INI for SODIUM configuration" . $e );
            // $this->assertTrue(false);
        }


        // Test database connection
        Log::channel('stderr')->info('CheckConfig:', ['Checking db connection'] );
        try {
            $p = DB::connection()->getPdo();
            Log::channel('stderr')->info('CheckConfig:', ['DB connection OK', $p] );
        } catch (\Exception $e) {
            Log::channel('stderr')->error('CheckConfig:', ['DB connection ERROR!', $e] );
            // $this->assertTrue(false);
        }


        Log::channel('stderr')->info('CheckConfig:', ['Creating table authors ...'] );
        if ( !Schema::hasTable('authors'))
        {
               Schema::create('authors', function (Blueprint $table) {
                $table->increments('id');
                $table->text('name');
                $table->text('name_enc'); // for test only
                $table->text('card_number');
                $table->text('card_number_enc'); // for test only
                $table->text('address');
                $table->text('address_enc'); // for test only
                $table->text('role');
                $table->text('role_enc'); // for test only
                $table->timestamps();
            });
            Log::channel('stderr')->info('CheckConfig:', ['table authors created'] );
        }
        else
        {
            Log::channel('stderr')->info('CheckConfig:', ['table comments already exits'] );
        }
/*
        Log::channel('stderr')->info('CheckConfig:', ['Creating table posts ...'] );
        if ( !Schema::hasTable('posts'))
        {
               Schema::create('posts', function (Blueprint $table) {
                $table->increments('id');
                $table->text('title');
                $table->text('title_enc'); // for test only
                $table->integer('author_id');
                $table->timestamps();
            });
            Log::channel('stderr')->info('CheckConfig:', ['table posts created'] );
        }
        else
        {
            Log::channel('stderr')->info('CheckConfig:', ['table posts already exits'] );
        }
*/

        Log::channel('stderr')->info('CheckConfig:', ['Checking RainbowTableService'] );
        try {
            $rtService = new \Paulodiff\RainbowTableIndex\RainbowTableIndexService();
            Log::channel('stderr')->info('CheckConfig:', ['Rainbow index creating ...  TAG DEMO'] );
            $value = random_int ( 1, 1000 );
            $rtService->setRT('TEST','DEMO', $value);
            Log::channel('stderr')->info('CheckConfig:', ['Rainbow index OK', $value] );
            Log::channel('stderr')->info('CheckConfig:', ['Check database for table rt_test!!!!!'] );
        } catch (\Exception $e) {
            Log::channel('stderr')->error('CheckConfig:', ['Rainbow ERROR', $e] );
            // $this->assertTrue(false);
            // die("ERRORE RainbowTableService re check previuos step!" . $e );
        }

        // $this->assertTrue(true);


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