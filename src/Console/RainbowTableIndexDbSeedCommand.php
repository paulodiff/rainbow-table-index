<?php
namespace Paulodiff\RainbowTableIndex\Console;

//.\vendor\bin\phpunit --filter the_db_seed_command tests\Unit\DbSeedCommandTest.php

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

class RainbowTableIndexDbSeedCommand extends Command
{
    protected $signature = 'RainbowTableIndex:dbSeed {numOfrows}';

    protected $description = 'CheckConfig for RainbowTableIndex';

    public function handle()
    {
        $this->info('RainbowTableIndex DbSeed - CREATE TABLE ');

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
     

        $this->info('RainbowTableIndex DbSeed - Seeding! ');

        $numOfrows = $this->argument('numOfrows');
        
        Log::channel('stderr')->info('DbSeed:rows:', [$numOfrows] );

        $numOfPosts = 2;
        $numOfAuthors = $numOfrows;

        Log::channel('stderr')->info('SeedData:', [
            'start seeding ....',
            'Posts : ' .  $numOfPosts,
            'Authors : ' . $numOfAuthors
        ]);
        $faker = Faker::create('SeedData');


        try
        {
            $a = new \Paulodiff\RainbowTableIndex\Tests\Models\Author();
        }
        catch (\Exception $e)
        {
            $a = new \App\Models\Author();
        }


        Log::channel('stderr')->info('SeedData:', ['destroy authors rainbox index... ']);
        $a::destroyRainbowIndex();

        Log::channel('stderr')->info('SeedData:', ['destroy authors table... ']);
        try
        {
            $a::truncate();
        } 
        catch (\Exception $e) 
        {
            Log::channel('stderr')->error('SeedData:', ['ERROR deleting Authors table', $e] );
            // die("ERRORE RainbowTableService re check previuos step!" . $e );
        }

        Log::channel('stderr')->info('SeedData:', ['start insert! ... ']);

        for($i=0;$i<$numOfAuthors;$i++)
        {
            // $p = new Author();

            try
            {
                $p = new \Paulodiff\RainbowTableIndex\Tests\Models\Author();
            }
            catch (\Exception $e)
            {
                $p = new \App\Models\Author();
            }


            $p->name = strtoupper($faker->name());
            $p->name_enc = $p->name;

            $p->card_number = $faker->creditCardNumber('Visa');
            $p->card_number_enc = $p->card_number;

            $p->address = $faker->streetAddress();
            $p->address_enc = $p->address;

            $p->role =  $faker->randomElement(['author', 'reader', 'admin', 'user', 'publisher']);
            $p->role_enc =  $p->role;

            $p->save();

            Log::channel('stderr')->info('SeedData:' . $i . '#' . $numOfAuthors .']Author Added!:', [$p->toArray()]);
            /*
            // Adding Posts
            for($j=0;$j<$numOfPosts;$j++)
            {
              $q = new Post();
              $q->title = strtoupper($faker->name());
              $q->title_enc = $q->title;
              $q->author_id = $p->id;
              $q->save();
              Log::channel('stderr')->info('SeedData:' . $j . '#' . $numOfPosts .']Post Added!:', [$q->toArray()]);
            }
            */
            
        }

        Log::channel('stderr')->info('SeedData finished!:', []);
        
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