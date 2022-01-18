# Laravel database data encryption with full text search

# 🚨 DO NOT USE IN PRODUCTION! 🚨
## Disclaimer
- This library is a proof of concept.

## TODO
- prefix table
- config default encrypted
- security customization
- test relationship
- stats

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
- fMinTokenLen : the minumum token size use on token generation. If = 0 the tokenizer return the full data.

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

When a data need to be inserted in RainbowTableIndex a trasformation is applied:

1. Only "safe chars" are valid   -   fSafeChars
2. Optional : CASE TRASFORMATION -   fTransform
3. Token size generation         -   fMinTokenLen

An Example:

Address data : "77712 O'Conner Plain Apt. 996 nw"  

1. Safe char filter: "O'Conner Plain Apt. nw" 
2. Case transformation: "O'CONNER PLAIN APT. NW"
3. Tokenization "[O'C]['CO]ONNER PLAIN APT. NW" .... TODO (NW skipped!)

If the model are updated or deleted the Rainbow Table index are updated.

RainbowTableIndex characteristics:

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

#### Check Laravel Mysql config in .env file

#### Running check config

```bash
php artisan RainbowTableIndex:checkConfig
```
### Configuration options

- .env configuration

#to enable debug information
DEBUG_LEVEL = debug

- config/rainbowtableindexconfig.php

#for debugging RainbowIndexTable without encryption with this options:
RAINBOW_TABLE_INDEX_ENCRYPT=true

I'ts all ok! you are ready to go!

### Create a working demo

Warning! This procedure build a table in the datbase:

- authors

and seed this tables with 1000 rows of data for testing.


#### Copy model

in app/models copy Author.php and Posts.php from ...

#### Run dbseed

#### Run dbStats




#### create model Author
```bash
composer create-project laravel/laravel rainbow-table-index-app

cd rainbow-table-index-app

composer require paulodiff/rainbow-table-index


php artisan RainbowTableIndex:keyGenerator

.env
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

create Author model

edit App/Models/Author.php

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

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

This is a web app demo crud with 5 tables:
```bash
Player
    'player_id'
    'player_name',           // ENCRYPTED
    'player_address',  
    'player_credit_card_no', // ENCRYPTED
    'player_phone',

PlayerRole
    'player_role_id'
    'player_role_description', // ENCRYPTED
    'player_role_salary',

Team
    'team_id',   // ENCRYPTED
    'team_name',
    'team_type_id', (1-1 with TeamType)

TeamType
    'team_type_id'
    'team_type_description',  // ENCRYPTED
    'team_type_rules',

Roster
    'roster_id'
    'roster_description',  // ENCRYPTED
    'roster_player_id',
    'roster_team_id',
    'roster_player_role_id',
    'roster_amount',       // ENCRYPTED

```

Livewire installation
composer require livewire/livewire

Set Mysql
Set .env
Copy Model and rename path foreach model
Copy resource
Copy web.app



```bash
php artisan RainbowTableIndex:dbCrud 100
php artisan optmize
php artisan serve
```

composer require livewire/livewire


php artisan make:livewire posts

app/Http/Livewire/Authors.php

resources/views/livewire/authors.blade.php


```
# Security customization

- Laravel Encrypt/Decrypt functions
- Hash



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

# Future works

-----

![alt text](https://github.com/paulodiff/rainbow-table-index/blob/main/blob/cube.jpg?raw=true)