<?php
namespace Paulodiff\RainbowTableIndex\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use  Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

class Author extends Model
{
    use HasFactory;
    use RainbowTableIndexTrait;

    // name, name_enc, card_number, card_number_enc, address, address_enc, role, role_enc

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
                'fType' => 'ENCRYPTED_FULL_TEXT',
                'fSafeChars' => ' àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.',
                'fTransform' => 'UPPER_CASE',
                'fMinTokenLen' => 0,
            ],

        ]

    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
