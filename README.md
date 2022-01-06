# Laravel database data encryption with full text search

# 🚨 DO NOT USE IN PRODUCTION! 🚨

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

but it's not possible using LIKE

```php
$p = Post::where('title','LIKE','%Bea%'); // WRONG!
```

because the real data stored in database table are encrypted:

```php
"id";"title"
"1";"7OioIr/njEtH0fFHDoVopndh2z/yfQ4r8i40QlZjaITQ7Mh5QwnhH/Gjug=="
```

Laravel includes Eloquent, an object-relational mapper (ORM).
By extending the Eloquent classes, and using the Laravel Traits, it is possible to build a library that allows you to perform full text searches on encrypted text using a "Rainbow Table Index".

## The idea ...

The goal: perform a search using LIKE operator in encrypted data.
Building an alternative index (**Rainbow Table Index**) with a library, is possible to index encrypted values and query this values.

This is a working sample, with a Post model ([id, title]). We create a Post item.

```php
Post::create(['id'=> 88, 'title' => 'Beauty']); 
```
The resultant Rainbow Index Table  (is the minimum token size is 3):
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
$p = Post::whereIn('id', [88,99]);
```

## Rainbow Table Index 

A "Rainbow Table Index" is a index that is builded by RainbowTableService:

- when a Eloquent Model are created/updated automagically the Rainbow Table index are created/updated
- all values are hashed data
- the index table name is encrypted
- a database table for each Eloquent Model get all Rainbow Table Index data

This is sample of Rainbow Table Index for Post model on encrypted title_enc field

```
Index table name: rt_098f6bcd4621d373cade4e832627b4f6
Index table data:
'72caf77378e7701778ce0cccc0fd039e0d94b98a55c60ba682fc99d6721603c8', 1,
'dbd3f5ac60697264f02f0c15a48ea14caee3d406f67e2f9984428d47103ec044', 1,
'86157f358f2367aa1982a87b5b50c46cf2bbcbdc4ac6fe433e7d70832e515327', 1,
'ea65a3097b9a8b21001fff748c41361ebde9eeb4da8337ac52e399c5f7b56c63', 1,
'b8d6d5ae5fc5d8d9f30abd26c4121181d9d0ff93e5c284acbbf4a906746577e2', 1,
'05ae2a908b7b449aae4da0fcd4643a9d4a057da348483ae2003af5eaaa707559', 1,
'e89cb3bd55c9154a4e2d880328484c4319ba256bd8bfa831c90de1799ee05ebd', 1,
'594030e2225adb878eb684500e8052c1e0fe4d12ac45b44131a118b331c846ca', 1,
'a4a769bcea0af688f59b7a78b2b2b3c17666f159a351d82029c22acfa1e2b2e3', 1,
'01575a87c3747df1428f82505357aa714dc901adad47856df7721264ca60fa62', 1,
'8d5aec01cdd256ccea2dcb4dd740acf471c2dec508bec2e97b2c3342144c5fc7', 1,
'288185f42ed618ed8ed7f95f0d7869eda7d85fcaec6a49d81ca1e6ebe89e8c36', 1,
'85e4c5234afdcf3dc8f885d82361849df47c938d36ae4330d2a17a4e4bd57f32', 1,
```

## Use case

The library can be used in contexts where it is necessary to guarantee the privacy of sensitive data, and it is necessary to perform searches with LIKE, for example:

- name or surname 
- address 
- credit card number


## Installation

### Requirements

- Laravel 8
- Mysql 
- php Sodium
- Redis (Coming soon)

### Example - Demo - Running Tests

Create a Laravel Application

```bash
composer create-project laravel/laravel rainbow-table-index-app
cd rainbow-table-index-app
composer require paulodiff/rainbow-table-index
```

https://github.com/paulodiff/rainbow-table-index.git

Copy the following files in folder

Publish config  ... TODO


```bash

TO REVIEW!!!! WORK IN PROGRESS ....

copy rainbowtable.php -> rainbow-table-index\config

create folder rainbow-table-index\app\RainbowTable
copy RainbowTrait.php -> rainbow-table-index\app\RainbowTable
copy RainbowQueryBuilder.php -> rainbow-table-index\app\RainbowTable
copy Encrypter.php -> rainbow-table-index\app\RainbowTable

copy Post.php -> rainbow-table-index\app\Models
copy Category.php -> rainbow-table-index\app\Models
copy Comment.php -> rainbow-table-index\app\Models

copy PostCommentCategorySeeder.php -> rainbow-table-index\database\seeders
copy PostCommentCategoryTest.php -> rainbow-table-index\database\seeders
copy RainbowCheckConfig.php -> rainbow-table-index\database\seeders

create folder rainbow-table-index\app\Services
copy RainbowTableService.php in rainbow-table-index\app\Services
```
Create a dabase named: **rainbow**  and configure mysql in .env 

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rainbow
DB_USERNAME=root
DB_PASSWORD=
```

Run Check configuration for the first time

php artisan db:seed --class=RainbowCheckConfig

- check database connection
- create table for test (Post, Comments, Categories)
- check PHP SODIUM
- check config Rainbow security values
- create a sample Rainbow Index

set to .env
RAINBOW_TABLE_KEY=...
RAINBOW_TABLE_NONCE=..

and run

php artisan optimize

// re check configuration with until ALL OK!
php artisan db:seed --class=RainbowCheckConfig


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

Access time to an instance is different.
My test:
- 1000 insta
- Index dimension
- Time access.

It is possible to calculate the number of rows of an index for each data to be indexed ($w is token length and $s is a string to tokenize)

```
for ($w=3;$w<= strlen($s); $w++) $numOfEntries += (strlen($s) + 1 - $w);
```


# Customization

sanitize_string

# Future works
```

```