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

class RainbowTableIndexDbRelationalCommand extends Command
{
    protected $signature = 'RainbowTableIndex:dbRelational {startFrom}';
    protected $description = 'TEST for RainbowTableIndex:dbRelational';



    public function handle()
    {
        $this->info('RainbowTableIndex dbRelational - CREATE TABLE ');
        $startFrom = $this->argument('startFrom');

        Log::channel('stderr')->info('dbRelational:startFrom ->', [$startFrom] );

    if($startFrom < 1)
    {
        Log::channel('stderr')->info('dbRelational:', ['Creating table rosters ...'] );
        if ( Schema::hasTable('rosters') ) { Schema::drop('rosters'); }
        if ( !Schema::hasTable('rosters'))
        {
            Schema::create('rosters', function (Blueprint $table) {
                $table->increments('roster_id');
                $table->text('roster_description');
                $table->text('roster_description_enc'); 
                $table->integer('roster_player_id');
                $table->integer('roster_team_id'); 
                $table->integer('roster_player_role_id');
                $table->text('roster_amount'); 
                $table->text('roster_amount_enc');
                $table->timestamps();
            });
            Log::channel('stderr')->info('CheckConfig:', ['table rosters created'] );
        }
        else
        {
            Log::channel('stderr')->info('CheckConfig:', ['table rosters already exits'] );
        }

        Log::channel('stderr')->info('CheckConfig:', ['Creating table player ...'] );
        if ( Schema::hasTable('players') ) { Schema::drop('players'); }
        if ( !Schema::hasTable('players'))
        {
            Schema::create('players', function (Blueprint $table) {
                $table->increments('player_id');
                $table->text('player_name');
                $table->text('player_name_enc'); 
                $table->text('player_address'); 
                $table->text('player_credit_card_no'); 
                $table->text('player_credit_card_no_enc'); 
                $table->text('player_phone'); 
                $table->timestamps();
            });
            Log::channel('stderr')->info('CheckConfig:', ['table players created'] );
        }
        else
        {
            Log::channel('stderr')->info('CheckConfig:', ['table players already exits'] );
        }

        Log::channel('stderr')->info('CheckConfig:', ['Creating table team ...'] );
        if ( Schema::hasTable('teams') ) { Schema::drop('teams'); }
        if ( !Schema::hasTable('teams'))
        {
            Schema::create('teams', function (Blueprint $table) {
                $table->increments('team_id');
                $table->text('team_name');
                $table->text('team_name_enc'); 
                $table->integer('team_type_id'); 
                $table->timestamps();
            });
            Log::channel('stderr')->info('CheckConfig:', ['table team created'] );
        }
        else
        {
            Log::channel('stderr')->info('CheckConfig:', ['table team already exits'] );
        }


        Log::channel('stderr')->info('CheckConfig:', ['Creating player_roles team ...'] );
        if ( Schema::hasTable('player_roles') ) { Schema::drop('player_roles'); }
        if ( !Schema::hasTable('player_roles'))
        {
            Schema::create('player_roles', function (Blueprint $table) {
                $table->increments('player_role_id');
                $table->text('player_role_description');
                $table->text('player_role_description_enc');
                $table->text('player_role_fee'); 
                $table->timestamps();
            });
            Log::channel('stderr')->info('CheckConfig:', ['table player_roles created'] );
        }
        else
        {
            Log::channel('stderr')->info('CheckConfig:', ['table player_roles already exits'] );
        }


        Log::channel('stderr')->info('CheckConfig:', ['Creating team_types team ...'] );
        if ( Schema::hasTable('team_types') ) { Schema::drop('team_types'); }
        if ( !Schema::hasTable('team_types'))
        {
            Schema::create('team_types', function (Blueprint $table) {
                $table->increments('team_type_id');
                $table->text('team_type_description');
                $table->text('team_type_description_enc');
                $table->text('team_type_rules'); 
                $table->timestamps();
            });
            Log::channel('stderr')->info('CheckConfig:', ['table team_types created'] );
        }
        else
        {
            Log::channel('stderr')->info('CheckConfig:', ['table team_types already exits'] );
        }
    }


    
    //----------------------------  SEEDING ---------------------------------------
    if($startFrom < 2)
    {
        Log::channel('stderr')->info('Seeding:...', [$startFrom] );
              
        // Log::channel('stderr')->info('DbSeed:rows:', [$numOfrows] );

        $numOfPosts = 2;
        $numOfPlayers = 10;
        $numOfTeams = 10;
        $numOfTeamTypes = 10;
        $numOfPlayerRoles = 5;
        $numOfRosters = 10;

        $numOfAuthors = $numOfrows;

        $faker = Faker::create('SeedData');

        // Log::channel('stderr')->info('DbSeed:rows:', [$numOfrows] );

        Log::channel('stderr')->info('Seeding:Player', [] );
        for($i=0;$i<$numOfPlayers;$i++)
        {

            if ( class_exists('\Paulodiff\RainbowTableIndex\Tests\Models\Player') )
            {
                $p = new \Paulodiff\RainbowTableIndex\Tests\Models\Player();
            }
            else
            {
                $p = new \App\Models\Player();
            }
            $p->player_name = 'PLAYER_' . ($i + 1);
            $p->player_name_enc = $p->player_name;

            $p->player_address = $faker->streetAddress();

            $p->player_credit_card_no = $faker->creditCardNumber('Visa');
            $p->player_credit_card_no_enc = $p->player_credit_card_no;

            $p->player_phone = $faker->phoneNumber();
            $p->save();
        }
        Log::channel('stderr')->info('Seeding:TeamType', [] );
        for($i=0;$i<$numOfTeamTypes;$i++)
        {

            if ( class_exists('\Paulodiff\RainbowTableIndex\Tests\Models\TeamType') )
            {
                $teamType = new \Paulodiff\RainbowTableIndex\Tests\Models\TeamType();
            }
            else
            {
                $teamType = new \App\Models\TeamType();
            }
            $teamType->team_type_description = 'TEAM_TYPE_DESC_' . ($i + 1);
            $teamType->team_type_description_enc = $teamType->team_type_description;


            $teamType->team_type_rules = $faker->phoneNumber();
            $teamType->save();
        }


        Log::channel('stderr')->info('Seeding:Team', [] );
        for($i=0;$i<$numOfTeams;$i++)
        {

            if ( class_exists('\Paulodiff\RainbowTableIndex\Tests\Models\Team') )
            {
                $team = new \Paulodiff\RainbowTableIndex\Tests\Models\Team();
            }
            else
            {
                $team = new \App\Models\TeamType();
            }
            $team->team_name = 'TEAM_' . ($i + 1);
            $team->team_name_enc = $team->team_name;

            $team->team_type_id = $faker->numberBetween(1, $numOfTeamTypes);
            $team->save();
        }

        Log::channel('stderr')->info('Seeding:PlayerRole', [] );
        for($i=0;$i<$numOfPlayerRoles;$i++)
        {

            if ( class_exists('\Paulodiff\RainbowTableIndex\Tests\Models\PlayerRole') )
            {
                $playerRole = new \Paulodiff\RainbowTableIndex\Tests\Models\PlayerRole();
            }
            else
            {
                $playerRole = new \App\Models\PlayerRole();
            }
            $playerRole->player_role_description = 'PLAYER_ROLE_' . ($i + 1);
            $playerRole->player_role_description_enc = $playerRole->player_role_description;

            $playerRole->player_role_fee = $faker->numberBetween(1, 50000);
            $playerRole->save();
        }

        Log::channel('stderr')->info('Seeding:Roster', [] );
        for($i=0;$i<$numOfRosters;$i++)
        {

            if ( class_exists('\Paulodiff\RainbowTableIndex\Tests\Models\Roster') )
            {
                $roster = new \Paulodiff\RainbowTableIndex\Tests\Models\Roster();
            }
            else
            {
                $roster = new \App\Models\Roster();
            }
            $roster->roster_description = 'ROSTER_' . ($i + 1);
            $roster->roster_description_enc = $roster->roster_description;

            $roster->roster_player_id = $faker->numberBetween(1, $numOfPlayers);
            $roster->roster_team_id = $faker->numberBetween(1, $numOfTeams);
            $roster->roster_player_role_id = $faker->numberBetween(1, $numOfPlayerRoles);

            $roster->roster_amount = $faker->numberBetween(1, 50000);
            $roster->roster_amount_enc = $roster->roster_amount;

            $roster->save();
        }

    }

        //----------------------------  Query ing---------------------------------------
        if($startFrom < 3)
        {
            Log::channel('stderr')->info('Quering:...', [$startFrom] );

            if ( class_exists('\Paulodiff\RainbowTableIndex\Tests\Models\Roster') )
            {
                $roster = new \Paulodiff\RainbowTableIndex\Tests\Models\Roster();
            }
            else
            {
                $roster = new \App\Models\Roster();
            }

            // $rs = $roster::all();

            foreach ($roster::all() as $rs) {
                Log::channel('stderr')->info('Quering:...', [$rs->roster_description] );
                Log::channel('stderr')->info('Quering:...', [$rs->team->team_name] );
            }
            
            /*
             $user->posts()->where('active', 1)->get();
             $flights = Flight::where('active', 1)
               ->orderBy('name')
               ->take(10)
               ->get();
            $comments = Post::find(1)->comments;

            foreach ($comments as $comment) {
                //
            }


            $books = Book::with('author')->get();

            foreach ($books as $book) {
                echo $book->author->name;
            }
            */


        }    


/*
        if ( class_exists('\Paulodiff\RainbowTableIndex\Tests\Models\Author') )
        {
            $a = new \Paulodiff\RainbowTableIndex\Tests\Models\Author();
        }
        else
        {
            $a = new \App\Models\Author();
        }


        try
        {
            $a = new \Paulodiff\RainbowTableIndex\Tests\Models\Author();
        }
        catch (\Exception $e)
        {
            $a = new \App\Models\Author();
        }
*/

/*
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

            if ( class_exists('\Paulodiff\RainbowTableIndex\Tests\Models\Author') )
            {
                $p = new \Paulodiff\RainbowTableIndex\Tests\Models\Author();
            }
            else
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
            
            
        }
        */

        Log::channel('stderr')->info('SeedData startFrom:',  [$startFrom]);
        
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