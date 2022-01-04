<?php

// php artisan test --testsuite=Feature --filter=RainbowTableIndex03CRUDTest --stop-on-failure

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Faker\Factory as Faker;

use App\RainbowTableIndex\RainbowTableIndexService;
use App\RainbowTableIndex\RainbowTableIndexTrait;

use App\Models\Post;
use App\Models\Author;



class RainbowTableIndex03CRUDTest extends TestCase
{
    use RainbowTableIndexTrait;
    // -------------------- TO CHANGE ---------------------------------------
    public $NUM_OF_SEARCH = 100;
    // -------------------- TO CHANGE ---------------------------------------
    public $faker;

    public function getText2Search()
    {
        $s = str_replace(['.', ' '], '', $this->faker->text(5));
        while( mb_strlen($s) < 3)
        {
            $s = str_replace(['.', ' '], '', $this->faker->text(5));
        }
        Log::channel('stderr')->info('getText2Search:', [$s]);
        return strtoupper($s);
    }

    public function test_search_verify()
    {

        Log::channel('stderr')->info('CRUD: start!', [] );

        $numOftests = $this->NUM_OF_SEARCH;
        $this->faker = Faker::create('PostCommentTest');

        // Author search test
        $a = new Author();
        Log::channel('stderr')->info('CRUD:config', [$a::$rainbowTableIndexConfig]);

        $DATA_ENCRYPTED_FULL_TEXT = [];
        $DATA_ENCRYPTED = [];

        foreach($a::$rainbowTableIndexConfig['fields'] as $item)
        {
            Log::channel('stderr')->info('CRUD:item', [$item] );

            if ($item['fType'] == 'ENCRYPTED_FULL_TEXT')
            {
                $DATA_ENCRYPTED_FULL_TEXT[$item['fName']] = [];
                $fName_flat = substr($item['fName'], 0, -4);
                $ids = $a::select($fName_flat)->limit(100)->get()->toArray();
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
                    Log::channel('stderr')->info('CRUD:@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@', [] );
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

        // RUN SEARCH ON DATA_ENCRYPTED_FULL_TEXT !!!

        foreach($DATA_ENCRYPTED_FULL_TEXT as $k=>$v)
        {
            $fName_enc = $k;
            $fName = substr($fName_enc, 0, -4);
            // Log::channel('stderr')->info('CRUD:DATA_ENCRYPTED_FULL_TEXT------->', [$fName, $fName_enc] );

            foreach ($DATA_ENCRYPTED_FULL_TEXT[$k] as $v)
            {

                // Log::channel('stderr')->info('CRUD:DATA_ENCRYPTED_FULL_TEXT------->', [$k, $v] );
                $t1=0; $t2=0;
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
                    Log::channel('stderr')->info('CRUD:ENC_FULL@' . $k .']' . $token_2_search . '] same result for :', [$token_2_search, $eta1, $eta2, count($arr1), count($arr2)] );
                    $t1 += $eta1; $t2 += $eta2;
                } else {
                    Log::error('^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ :', [count($arr1), count($arr2)] );
                    Log::error('Check mismatch :', [$fName, $token_2_search] );
                    Log::error('arr1 enc  :', [$arr1] );
                    Log::error('arr2 flat :', [$arr2] );
                    exit(9999);
                }

            }

        }

        // RUN SEARCH ON DATA_ENCRYPTED_FULL_TEXT !!!

        foreach($DATA_ENCRYPTED as $k=>$v)
        {
            $fName_enc = $k;
            $fName = substr($fName_enc, 0, -4);
            // Log::channel('stderr')->info('CRUD:DATA_ENCRYPTED------->', [$fName, $fName_enc] );

            foreach ($DATA_ENCRYPTED[$k] as $v)
            {

                // Log::channel('stderr')->info('CRUD:DATA_ENCRYPTED------->', [$k, $v] );
                $t1=0; $t2=0;
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
                    Log::channel('stderr')->info('CRUD:ENC@' . $k .':'  . $token_2_search . '] same result for :', [$token_2_search, $eta1, $eta2, count($arr1), count($arr2)] );
                    $t1 += $eta1; $t2 += $eta2;
                } else {
                    Log::error('^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ :', [count($arr1), count($arr2)] );
                    Log::error('Check mismatch :', [$fName, $token_2_search] );
                    Log::error('arr1 enc  :', [$arr1] );
                    Log::error('arr2 flat :', [$arr2] );
                    exit(9999);
                }

            }

        }



    }

    public function test_crud ()
    {
      // get items and update data
      // get all ids
      $faker = Faker::create('SeedData');
      $arr1 = Author::select('id')->get()->toArray();
      // shuffle
      shuffle($arr1);
      // get first
      $i = 0;
      foreach ($arr1 as $key => $value) {
        $i++;
        if ( $i > 10) return;
          Log::channel('stderr')->info('UPDATE:ENC@', [$value] );

          $p = Author::where('id',$value['id'])->get()->first();

          $p->name = strtoupper($faker->name());
          $p->name_enc = $p->name;

          $p->card_number = $faker->creditCardNumber('Visa');
          $p->card_number_enc = $p->card_number;

          $p->address = $faker->streetAddress();
          $p->address_enc = $p->address;

          $p->role =  $faker->randomElement(['author', 'reader', 'admin', 'user', 'publisher']);
          $p->role_enc =  $p->role;

          $p->save();


      }

      // update this data


    }

    public function search_test ( $obj, $f, $f_enc)
    {

        $t1=0; $t2=0;
        $token_2_search = $this->getText2Search();
        $token_2_search = str_replace(['.'], '', $token_2_search);
        // Log::channel('stderr')->info('CommentTest:', [$token_2_search]);


        $token_2_search = 'AMM';

        // Full text search in encrypted field
        $start1=hrtime(true);
        $arr1 = $obj::select('id')->where($f_enc, 'LIKE', '%' . $token_2_search . '%')->get()->toArray();
        $end1=hrtime(true);
        $eta1=$end1-$start1;
        // Log::channel('stderr')->info('Comment result encrypted field:', [$arr1] );

        // Full text search in flat field
        $start2=hrtime(true);
        $arr2 = $obj::select('id')->where($f, 'LIKE', '%' . $token_2_search . '%')->get()->toArray();
        // Log::channel('stderr')->info('Comment result      flat field:', [$arr2] );
        $end2=hrtime(true);
        $eta2=$end2-$start2;

        // Verify results ...
        if ( $arr1 === $arr2) {
            $ret = ['ALL OK!', $token_2_search, $eta1, $eta2, count($arr1), count($arr2)];
          } else {
            $ret = ['ERROR Check mismatch :', $token_2_search];
            exit(9999);
        }

        return $ret;

    }


}



/*
// Author TEST SEARCH on field name, name_enc
for($i=0;$i<$numOftests;$i++)
{
    $t1=0; $t2=0;
    $token_2_search = $this->getText2Search();
    $token_2_search = str_replace(['.'], '', $token_2_search);
    // Log::channel('stderr')->info('CommentTest:', [$token_2_search]);


    $token_2_search = 'IAN';

    // Full text search in encrypted field
    $start1=hrtime(true);
    $arr1 = Author::select('id')->where('name_enc', 'LIKE', '%' . $token_2_search . '%')->get()->toArray();
    $end1=hrtime(true);
    $eta1=$end1-$start1;
    // Log::channel('stderr')->info('Comment result encrypted field:', [$arr1] );

    // Full text search in flat field
    $start2=hrtime(true);
    $arr2 = Author::select('id')->where('name', 'LIKE', '%' . $token_2_search . '%')->get()->toArray();
    // Log::channel('stderr')->info('Comment result      flat field:', [$arr2] );
    $end2=hrtime(true);
    $eta2=$end2-$start2;

    // Verify results ...
    if ( $arr1 === $arr2) {
        Log::channel('stderr')->info('Comment ' . $i . ' - ' . $numOftests . 'same result for :', [$token_2_search, $eta1, $eta2, count($arr1), count($arr2)] );
        $t1 += $eta1; $t2 += $eta2;
    } else {
        Log::error('Check mismatch :', [$token_2_search] );
        exit(9999);
    }
}
*/





/*

$ps = Post::with(['category', 'comments'])->take(3)->get();
// Log::channel('stderr')->info('PostTest:JOIN', [$p]);
foreach($ps as $p)
{
    // echo '>>>' . $p->id .'#'. $p->title . '@' .  $p->category->cat_id . "@" . $p->category->description . "\n\n";
    // print_r($p->comments);
    // echo '--------------------------------------------------------------------------------------------------\n';
    // print_r($p->toArray());
    Log::channel('stderr')->info(json_encode($p->toArray()), [] );
    // print_r($p->category->toArray());
    // print_r($p->comments->toArray());
}

$this->faker = Faker::create('PostCommentTest');
*/

/*




  // TEST post title
  for($i=0;$i<$numOftests;$i++)
  {
      $t1=0; $t2=0;
      $token_2_search = $this->getText2Search();
      $token_2_search = str_replace(['.'], '', $token_2_search);
      // Log::channel('stderr')->info('PostTest                   :', [$token_2_search]);

      // Full text search in encrypted post.title_enc
      $start1=hrtime(true);
      $arr1 = Post::select('id')->where('title_enc', 'LIKE', '%' . $token_2_search . '%')->get()->toArray();
      $end1=hrtime(true);
      $eta1=$end1-$start1;

      // Log::channel('stderr')->info('Post result encrypted field:', [$arr1] );

      // Full text search in flat post.title_enc
      $start2=hrtime(true);
      $arr2 = Post::select('id')->where('title', 'LIKE', '%' . $token_2_search . '%')->get()->toArray();
      $end2=hrtime(true);
      $eta2=$end2-$start2;

      // Log::channel('stderr')->info('Post result      flat field:', [$arr2] );

      // Verify results ...
      if ( $arr1 === $arr2) {
          Log::channel('stderr')->info('Post ' . $i . ' - ' . $numOftests . ' search same result for :', [$token_2_search, $eta1, $eta2] );
          $t1 += $eta1; $t2 += $eta2;
      } else {
          Log::error('Check mismatch :', [$token_2_search] );
          exit(9999);
      }
  }
*/


/*

  Log::channel('stderr')->info('FINAL Post time for : Rainbow time:' .  $t1/$numOftests/1e+6 . 'ms sql time:' . $t2/$numOftests/1e+6 . 'ms', [$t1, $t2, $numOftests] );

  // TEST comment body
  for($i=0;$i<$numOftests;$i++)
  {
      $t1=0; $t2=0;
      $token_2_search = $this->getText2Search();
      $token_2_search = str_replace(['.'], '', $token_2_search);
      // Log::channel('stderr')->info('CommentTest:', [$token_2_search]);

      // Full text search in encrypted field
      $start1=hrtime(true);
      $arr1 = Comment::select('id')->where('body_enc', 'LIKE', '%' . $token_2_search . '%')->get()->toArray();
      $end1=hrtime(true);
      $eta1=$end1-$start1;
      // Log::channel('stderr')->info('Comment result encrypted field:', [$arr1] );

      // Full text search in flat field
      $start2=hrtime(true);
      $arr2 = Comment::select('id')->where('body', 'LIKE', '%' . $token_2_search . '%')->get()->toArray();
      // Log::channel('stderr')->info('Comment result      flat field:', [$arr2] );
      $end2=hrtime(true);
      $eta2=$end2-$start2;

      // Verify results ...
      if ( $arr1 === $arr2) {
          Log::channel('stderr')->info('Comment ' . $i . ' - ' . $numOftests . 'same result for :', [$token_2_search, $eta1, $eta2] );
          $t1 += $eta1; $t2 += $eta2;
      } else {
          Log::error('Check mismatch :', [$token_2_search] );
          exit(9999);
      }
  }
  Log::channel('stderr')->info('FINAL Comment time for : Rainbow time:' .  $t1/$numOftests/1e+6 . 'ms sql time:' . $t2/$numOftests/1e+6 . 'ms', [] );
*/





  /* MAINTENANCE - rebuild index for all instances
  Log::channel('stderr')->info('Post destroyRainbowIndex :', ['....'] );
  Post::destroyRainbowIndex();
  // Loop for and rebuild ....
  Log::channel('stderr')->info('Post rebuilding FullRainbowIndex ..... ', ['....'] );
  Post::rebuildFullRainbowIndex();
  Log::channel('stderr')->info('Post rebuilded! FullRainbowIndex', ['OK'] );
  */

  // Log::channel('stderr')->info('ALL DATA TO TEST:', [$DATA_ENCRYPTED_FULL_TEXT, $DATA_ENCRYPTED]);
