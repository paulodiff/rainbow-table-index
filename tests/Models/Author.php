<?php
namespace Paulodiff\RainbowTableIndex\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use  Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

class Author extends Model
{
    use HasFactory;
    use RainbowTableIndexTrait;

    public static $rainbowTableIndexConfig = [
    //    'title_enc' => 'full',
    //    'primaryKey' => 'id',
    //    'table' => 'posts',

        'table' => [
            'primaryKey' => 'id',
            'tableName' => 'authors',
        ],
        'fields' => [
            [
              'fName' => 'name_enc',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              // aggiungere anche l'apice per indicizzare O'Briant Malago'
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

    ];

    public function comments()
    {
        // return $this->hasMany(Comment::class)->whereNull('parent_id');
        return $this->hasMany(Comment::class);
    }

    public function category()
    {
        return $this->hasOne(Category::class,'cat_id', 'category_id');
    }

}
