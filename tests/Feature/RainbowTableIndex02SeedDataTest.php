<?php

// php artisan test --testsuite=Feature --filter=RainbowTableIndex02SeedDataTest --stop-on-failure

namespace Paulodiff\RainbowTableIndex\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Paulodiff\RainbowTableIndex\Tests\TestCase;

use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Faker\Factory as Faker;

use Paulodiff\RainbowTableIndex\RainbowTableIndexService;
use Paulodiff\RainbowTableIndex\Tests\Models\Author;
use Paulodiff\RainbowTableIndex\Tests\Models\Post;

class RainbowTableIndex02SeedDataTest extends TestCase
{

    // -------------------- TO CHANGE ---------------------------------------
    public $NUM_OF_AUTHORS_TO_CREATE = 100;
    public $NUM_OF_POSTS_TO_CREATE = 2;
    public $NUM_OF_COMMENTS_TO_CREATE = 5;
    // -------------------- TO CHANGE ---------------------------------------

    public function test_seed_data()
    {

        $numOfPosts = $this->NUM_OF_POSTS_TO_CREATE;
        $numOfComments = $this->NUM_OF_COMMENTS_TO_CREATE;
        $numOfAuthors = $this->NUM_OF_AUTHORS_TO_CREATE;

        Log::channel('stderr')->info('SeedData:', [
            'start seeding ....',
            'Posts : ' . $this->NUM_OF_POSTS_TO_CREATE,
            'Comments : ' . $this->NUM_OF_COMMENTS_TO_CREATE,
            'Authors : ' . $this->NUM_OF_AUTHORS_TO_CREATE,
            'Categories : 8',

        ]);
        $faker = Faker::create('SeedData');

        /*
        Log::channel('stderr')->info('PostCommentCategorySeeder:', ['destroy category ... ']);
        Category::destroyRainbowIndex();
        Category::truncate();

        Log::channel('stderr')->info('PostCommentCategorySeeder:', ['destroy post ... ']);
        Post::destroyRainbowIndex();
        Post::truncate();

        Log::channel('stderr')->info('PostCommentCategorySeeder:', ['destroy comment ... ']);
        Comment::destroyRainbowIndex();
        Comment::truncate();
        */

        Log::channel('stderr')->info('SeedData:', ['destroy authors rainbox index... ']);
        Author::destroyRainbowIndex();

        Log::channel('stderr')->info('SeedData:', ['destroy authors table... ']);
        try
        {
            Author::truncate();
        } 
        catch (\Exception $e) 
        {
            Log::channel('stderr')->error('SeedData:', ['ERROR deleting Authors table', $e] );
            // die("ERRORE RainbowTableService re check previuos step!" . $e );
        }

        Log::channel('stderr')->info('SeedData:', ['start insert! ... ']);
/*
        $c = Category::firstOrCreate(['cat_id' => 1,'description' => 'posts'   , 'description_enc' => 'posts']);
        $c = Category::firstOrCreate(['cat_id' => 2,'description' => 'article' , 'description_enc' => 'article']);
        $c = Category::firstOrCreate(['cat_id' => 4,'description' => 'news'    , 'description_enc' => 'news']);
        $c = Category::firstOrCreate(['cat_id' => 5,'description' => 'theme'   , 'description_enc' => 'theme']);
        $c = Category::firstOrCreate(['cat_id' => 6,'description' => 'opinion' , 'description_enc' => 'opinion']);
        $c = Category::firstOrCreate(['cat_id' => 7,'description' => 'tutorial', 'description_enc' => 'tutorial']);
        $c = Category::firstOrCreate(['cat_id' => 8,'description' => 'guide'   , 'description_enc' => 'guide']);
*/
        for($i=0;$i<$numOfAuthors;$i++)
        {
            $p = new Author();

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
            // ----------


        }



/*

        for($i=0;$i<$numOfPosts;$i++)
        {

            $p = new Post();
            $p->title = strtoupper($faker->text(30));
            $p->title_enc = $p->title;
            $p->category_id = $faker->randomElement([1,2,3,4,5,6,7]);
            $p->save();

            Log::channel('stderr')->info($i . '#' . $numOfPosts .']PostCommentCategorySeeder:Post Added!:', [$p->toArray()]);

            for($j=0;$j<$numOfComments;$j++)
            {
                $c = new Comment();
                $c->post_id = $p->id;
                $c->body = strtoupper($faker->text(30));
                $c->body_enc = $c->body;
                $c->save();

                Log::channel('stderr')->info('PostCommentCategorySeeder:Comment to Post Added!:', [$c->toArray()]);

            }

        }
*/

        Log::channel('stderr')->info('SeedData finished!:', []);
        $this->assertTrue(true);


    }


}
