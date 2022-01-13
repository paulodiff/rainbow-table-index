# Laravel database data encryption with full text search

# 🚨 DO NOT USE IN PRODUCTION! 🚨
## Disclaimer
- This library is a proof of concept.

## TODO
- prefix table
- config default encrypted
- security customization
- test relationship

<p align="center">
    <a href="https://laravel.com">
    <img src="https://img.shields.io/badge/Built_for-Laravel-green.svg?style=flat-square">
    </a>
    <a href="https://packagist.org/packages/paulodiff/rainbow-table-index">
        <img src="https://img.shields.io/github/license/paulodiff/rainbow-table-index.svg?style=flat-square"/>
    </a>
    <a href="https://packagist.org/packages/paulodiff/rainbow-table-index">
        <img src="https://img.shields.io/packagist/v/paulodiff/rainbow-table-index.svg?style=flat-square"/>
    </a>
    <a href="https://packagist.org/packages/paulodiff/rainbow-table-index">
        <img src="https://img.shields.io/packagist/dt/paulodiff/rainbow-table-index.svg?style=flat-square"/>
    </a>
</p>

## Introduction

Database data encryption is a must-have and it's very simple to encrypt data in database using Laravel Eloquent ORM casts:

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Post extends Model
{
    protected $casts = [
        'title' => 'encrypted',
    ];
}
```

With encrypted data fields, you can search only with exact field name value:

```php
$p = Post::where('title','Beautiful Post'); // OK
```

but it's not possible using LIKE operator:

```php
$p = Post::where('title','LIKE','%Bea%'); // WRONG!
```

because the real data are stored in database table in encrypted form:

```php
"id";"title"
"1";"7OioIr/njEtH0fFHDoVopndh2z/yfQ4r8i40QlZjaITQ7Mh5QwnhH/Gjug=="
```

Laravel includes Eloquent, an object-relational mapper (ORM).
By extending the Eloquent classes, and using the Laravel Traits, it is possible to build a library that allows you to perform full text searches on encrypted text using a "Rainbow Table Index".

## The idea ...

The goal: perform a search using LIKE operator in encrypted data.
Building an alternative index (**Rainbow Table Index**) with a library, is possible to index encrypted values and query this values.

This is a working sample, with a Post model with fields "id" and "title". 
We create a Post item:

```php
Post::create(['id'=> 88, 'title' => 'Beauty']); 
```
The resultant RainbowTableIndex  (with the minimum token size is 3):

```php
Rainbow Table Index data
post:title BEA 88
post:title EAU 88
post:title AUT 88
post:title UTY 88
post:title BEAU 88
post:title EAUT 88
post:title AUTY 88
post:title BEAUTY 88
```
A LIKE search on an encrypted field
```php
$p = Post::where('title','LIKE','%BEA%');
```
is automatically converted first in a search on Rainbow Table Index and next the ids of token 'BEA' the query become:
```php
$p = Post::whereIn('id', [88]);
```

## Rainbow Table Index - details

A "Rainbow Table Index" is a index that is builded by RainbowTableService.
An Eloquent model is an oject that interact with a database.
This is an example:

```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

class Author extends Model
{
    use RainbowTableIndexTrait;
    protected $fillable = [
      'name'
    ];
    // Rainbow table configuration
    public static $rainbowTableIndexConfig = [
        'table' => [
            'primaryKey' => 'id',
            'tableName' => 'authors',
        ],
        'fields' => [
            [
              'fName' => 'name',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
              'fTransform' => 'UPPER_CASE',
              'fMinTokenLen' => 3,
            ]
        ]
    ];
}

```

This model define an Author with one encrypted field: 'name'. 
The RainbowTableIndex configuration:

- primaryKey : the table's primary key
- tableName  : the table name database

for each field to encrypt and index with RainbowTableIndex you define

- fName : field name
- fType : ENCRYTPED_FULL_TEXT (other type are coming soon)
- fSafeChars : an array of char to sanitize field value
- fTransform : UPPER_CASE|LOWER_CASE|NONE apply a transformation to field
- fMinTokenLen : the minumum token size use on token generation

When an Eloquent Model are created a row are inserted in database table end all field configured in rainbowTableIndexConfig are encrypted.
Next all entries  are created in RainbowTableIndex.

```php
Author::create(['id'=> 1, 'name' => 'Billy White']); 
```
Show the database authors data

```bash
id;name;
1ZXlKcGRpSTZJa1JEUTB4cWNuTmxTRzFOSzFZMVowNUxiazVITjFFOVBTSXNJblpoYkhWbElqb2lUMFp2V1hwRVlsSkVWSFZ3TDBGNldURklXRzVuVEZnMk16QXhVQ3RpY1dWUVowMTJha1V5WkdOeFNUMGlMQ0p0WVdNaU9pSm1NbUpqWTJSaE5HSTNORFUxTlRjME16ZGlOalEyWVRjd05tRXdORFF4T1RCbU9EZGtaREl6TlRsallqa3laR0UwTTJJelkyVTFaV1kyWWpFd00yUXpJaXdpZEdGbklqb2lJbjA9;
```

Show the rainbow table index data related to field 'name'. 
```bash
table name: rt_6f48819d50e9b840e0c9a5e4a1375145
table data:
rt_key;rt_value
7327b0fa163b48eb9dca10700a55d717406e25f24da3223d78257bb55cbd5e77;1
3da30095b68d8df0348b86660aa9f8c33617b2f9bbecae4ef342bd708fcbf1fc;1
89aa7c63a9087d2f4da32e49d5949024d9c9a41cde32bb0c80ea3edc28c9384b;1
979d88c360ae25de60c5afdf97e48760e1dbff047c595431272d3c69c0056484;1
91024262f3055431a4aca784cca25fde500a3e6a498e12717d498667c322fec3;1
bf64a77aef0838485fff59fde2c889bea48c451ad7c157f005d5b93bf686ddb1;1
3d5834be7f15366d5a5d9c823d3e39713f932b6378f210cf3e842417bd20f81f;1
334dea03de177f5f22bfd8a571f4f1c696be6d08fa135a161680a3b0dfa1302c;1
4b0cdc4e3d3c57e606bdd14b4c69b4eb0d6535947290429fa135231b150b1173;1
0f91f40beba2d9e7400653124342097b17a05d62ddbc4fe8b4205d0a256344e2;1
b5da40127d66e01bc324557fa056c3ed89b7c44b832399c2373df3dc7ea60b29;1
fad5fc5354ddbd9adaccd041f96e7aa4c8a460d4acd94c0eb099ef637c6fb6df;1
f0647065844b606d53db1adef3d540404cc00e9ab040d14c422f6f4b9f5e4979;1
a5fd15ffb7150fb7dec15b758f3a118d724b11c67a4483ac2ec6d8b631d0320b;1
...
```

If the model are updated or deleted the Rainbow Table index are updated.

RainbowTableIndex characteristis:

- when a Eloquent Model (database item) are created/updated automagically the Rainbow Table index are created/updated
- all values are hashed data
- the index table name is encrypted
- a database table for each Eloquent Model get all Rainbow Table Index data

## Use case

The library can be used in contexts where it is necessary to guarantee the privacy of sensitive data, and it is necessary to perform searches with LIKE, for example:

- name or surname 
- address 
- credit card number


## Installation

### Requirements

- Laravel 8
- Mysql 
- Redis (Coming soon)

### Setup

#### Create a Laravel Application

```bash
composer create-project laravel/laravel rainbow-table-index-app
cd rainbow-table-index-app
composer require paulodiff/rainbow-table-index
php artisan vendor:publish --provider="Paulodiff\RainbowTableIndex\RainbowTableIndexServiceProvider" --tag="config"
```

#### Check Mysql config in .env !!!!


### Check config ....

- .env configuration

to enable debug information
DEBUG_LEVEL = debug

for debagging purpose you can use RainbowIndexTable without encryption with this options:
>>> RAINBOW_TABLE_INDEX_ENCRYPT=true

```bash
php artisan RainbowTableIndex:checkConfig
```

I'ts all ok! you are ready to go!

### Create a working demo

Warning! This procedure build two table in the datbase:

- authors


and seed this tables with 1000 rows of data.

#### Configure mysql

#### Copy model

in app/models copy Author.php and Posts.php from ...

#### Run dbseed

#### Run dbStats



https://github.com/paulodiff/rainbow-table-index.git

Copy the following files in folder

 - Publish config  ... TODO

#### create model Author
```bash
composer create-project laravel/laravel rainbow-table-index-app

cd rainbow-table-index-app

composer require paulodiff/rainbow-table-index

// publishing
php artisan vendor:publish --provider="Paulodiff\RainbowTableIndex\RainbowTableIndexServiceProvider" --tag="config"

php artisan RainbowTableIndex:keyGenerator

.env
RAINBOW_TABLE_INDEX_KEY=HfP+3eMCN/V6oMf9UgLt6hCtS3X3pklPc2M039xwMQI=
RAINBOW_TABLE_INDEX_NONCE=XaCEPxsuyPsRle2z0zQ2MMM2MHb6Lfty
RAINBOW_TABLE_INDEX_ENCRYPT=true
RAINBOW_TABLE_INDEX_PREFIX=rti // TODO

```

php artisan RainbowTableIndex:checkConfig


### Configure and running demo and test

#### Create a migration
```bash
php artisan make:migration create_authors_table
```

edit migration file

```php

    public function up()
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

 
    }
    public function down()
    {
        Schema::dropIfExists('authors');

    }
```
migrate database

```bash
php artisan migrate
```

create Author and Post model

edit App/Models/Author.php

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use  Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

class Author extends Model
{
    use HasFactory;
    use RainbowTableIndexTrait;

    public static $rainbowTableIndexConfig = [
  

        'table' => [
            'primaryKey' => 'id',
            'tableName' => 'authors',
        ],
        'fields' => [
            [
              'fName' => 'name_enc',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
              'fTransform' => 'UPPER_CASE',
              'fMinTokenLen' => 3,
            ],
            [
                'fName' => 'address_enc',
                'fType' => 'ENCRYPTED_FULL_TEXT',
                'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
                'fTransform' => 'UPPER_CASE',
                'fMinTokenLen' => 4,
            ],
            [
                'fName' => 'card_number_enc',
                'fType' => 'ENCRYPTED_FULL_TEXT',
                'fSafeChars' => '1234567890',
                'fTransform' => 'NONE',
                'fMinTokenLen' => 4,
            ],
            [
                'fName' => 'role_enc',
                'fType' => 'ENCRYPTED',
                'fSafeChars' => ' àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.',
                'fTransform' => 'ENCRYPTED_FULL_TEXT',
                'fMinTokenLen' => 0,
            ],

        ]

    ];


}

```


run db seed (with 100 rows)

```bash
php artisan RainbowTableIndex:dbSeed 100
```

run search test and metrics (with 100 )

```bash
php artisan RainbowTableIndex:dbCrud 100
```

### STOP Rainbow index is configured and running!, you want can test with a web app

Installa livewire

composer require livewire/livewire


php artisan make:livewire posts

app/Http/Livewire/Authors.php

resources/views/livewire/authors.blade.php

COPY FILES ...




Create a dabase named: **rainbow**  and configure mysql in .env 

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rainbow
DB_USERNAME=root
DB_PASSWORD=
```
## Security customization

- Laravel Encrypt/Decrypt
- Hash


### Using test

RainbowTableIndex01CheckConfigTest.php

- 

Run Check configuration for the first time

php artisan db:seed --class=RainbowCheckConfig

- check config Rainbow security values
- check PHP SODIUM
- check database connection
- create table for test (Author, Post, Comments, Categories)
- create a sample Rainbow Index

set to .env
RAINBOW_TABLE_KEY=...
RAINBOW_TABLE_NONCE=..

and run

php artisan optimize

// re check configuration with until ALL OK!
php artisan db:seed --class=RainbowCheckConfig

RainbowTableIndex02SeedDataTest.php

Edit if number of seeds. ...
    // -------------------- TO CHANGE ---------------------------------------
    public $NUM_OF_AUTHORS_TO_CREATE = 1000;
    public $NUM_OF_POSTS_TO_CREATE = 2;
    // -------------------- TO CHANGE ---------------------------------------

All ok! Installation and configuration is complete!

Run a demo with Posts, Comments and Categories

## Model configuration

This is the configuration to use encrypted/searchable field data in a model. 

For example, if you want configure this fields:

- name_enc, address_enc, card_number_enc are encrypted and searchable with Rainbow Index

- role_enc are only encrypted

the `$rainbowTableIndexConfig` is:

```php
		
public static $rainbowTableIndexConfig = [
       'table' => [
            'primaryKey' => 'id',       // table primary key
            'tableName' => 'authors',   // table name
        ],
        'fields' => [
            [
              'fName' => 'name_enc',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
              'fTransform' => 'UPPER_CASE',
              'fMinTokenLen' => 3,
            ],

            [
                'fName' => 'address_enc',
                'fType' => 'ENCRYPTED_FULL_TEXT',
                'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
                'fTransform' => 'UPPER_CASE',
                'fMinTokenLen' => 4,
            ],
            [
                'fName' => 'card_number_enc',
                'fType' => 'ENCRYPTED_FULL_TEXT',
                'fSafeChars' => '1234567890',
                'fTransform' => 'NONE',
                'fMinTokenLen' => 4,
            ],
            [
                'fName' => 'role_enc',
                'fType' => 'ENCRYPTED',
                'fSafeChars' => ' àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.',
                'fTransform' => 'UPPER_CASE',
                'fMinTokenLen' => 3,
            ],

        ]
    ]
```



The field configuration explanation:


```php
'fName' => NAME OF TABLE FIELD TO ENCRYPT
'fType' => ENCRYPTION TYPE (ENCRYPTED_FULL_TEXT|ENCRYPTED) with Rainbow Index or only encription
// data transformation config before insert into Rainbow Index
'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
'fTransform' => 'UPPER_CASE', (UPPER_CASE|LOWER_CASE|NONE)
'fMinTokenLen' => 3, (Token lenght, smaller are skipped!))

```

When a data need to be inserted in RainbowTableIndex a trasformation is applied:

1. Only "safe chars" are valid
2. Optional : CASE TRASFORMATION
3. Token size generation

An Example:

Address data : "77712 O'Conner Plain Apt. 996 nw"  

1. Safe char filter: "O'Conner Plain Apt. nw" 
2. Case transformation: "O'CONNER PLAIN APT. NW"
3. Tokenization "[O'C]['CO]ONNER PLAIN APT. NW" .... TODO (NW skipped!)









php artisan make:migration create_posts_comments_categories_table

# fill migration

```
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsCommentsCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('title_enc');
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('post_id')->unsigned();
            $table->text('body');
            $table->text('body_enc');
            $table->text('category');
            $table->timestamps();
        });
    }
       
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('categories');
    }
}
```

# run migration

php artisan migrate

# create model

php artisan make:model Post
php artisan make:model Comment
php artisan make:model Category

# fill modes

TODO edit Post & Comment .php

# run seeder 

php artisan db:seed --class=PostCommentSeeder

# run test

# set LOG LEVEL to show activities ...
.env
LOG_LEVEL=debug
php artisan optimize

php artisan db:seed --class=PostCommentTest



# Service operation

You can rebuild all indexed data for an intaciated model:

```php
$a = Author::where('id', 3)->first();
$r1 = $a->rebuildRainbowIndex();
```

or you can rebuild ALL indexes for ALL instances of a model

```php
$r2 = Author::rebuildFullRainbowIndex();
```

# Stats / Performance and dimensions

Performance system characterirtcs

- I3 / 16gb
- Windows 10 / XAMPP
- 1000 Authors
- 4000 Posts


Access time to an instance is different.
My test:
- 1000 insta
- Index dimension
- Time access.
ù
[2022-01-07 12:25:55] testing.INFO: CRUD:TOTAL TIMING: [["name_enc-2613#2613-time (enc,flat) : . [16.161010447761,2.5544727133563]","address_enc-1408#1408-time (enc,flat) : . [10.007975923295,2.5428640625]","card_number_enc-9100#9100-time (enc,flat) : . [43.496440604396,2.1048867032967]","role_enc-5#5-time (enc,flat) : . [5.10922,5.35346]"]]

It is possible to calculate the number of rows of an index for each data to be indexed ($w is token length and $s is a string to tokenize)

```
for ($w=3;$w<= strlen($s); $w++) $numOfEntries += (strlen($s) + 1 - $w);
```


# Customization

sanitize_string

# Example app with Laravel Livewire with search .... 
```
## Realdatabase - https://dbdiagram.io/d/61d3251f3205b45b73d51c25

Table player as A {
  player_id int [pk] // auto-increment
  player_full_name varchar
  player_address varchar
  player_credit varchar
  player_phone varchar
}

Table team as B {
  team_id int [pk]
  team_name varchar
  team_type_id int
}

Table team_type as C {
  team_type_id int [pk]
  team_type_description varchar
  team_type_rules varchar
}

Table roster as D {
  roster_id int [pk]
  roster_player_id int
  roster_team_id int
  roster_role_id int
  roster_amount varchar 
}

Table roles as E {
  roles_id int [pk]
  roles_description varchar
  roles_fee varchar
}




```