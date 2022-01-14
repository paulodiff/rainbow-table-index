<?php
namespace Paulodiff\RainbowTableIndex\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

class Player extends Model
{
    use HasFactory;
    use RainbowTableIndexTrait;

    protected $table = 'players';
    protected $primaryKey = 'player_id';

    protected $fillable = [
        'player_name', 
        'player_name_enc', 
        'player_address', 
        'player_credit_card_no', 
        'player_credit_card_no_enc', 
        'player_phome', 
    ];

    public static $rainbowTableIndexConfig = [
          'table' => [
            'primaryKey' => 'player_id',
            'tableName' => 'players',
        ],
        'fields' => [
            [
              'fName' => 'player_name_enc',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
              'fTransform' => 'UPPER_CASE',
              'fMinTokenLen' => 3,
            ],
            [
                'fName' => 'player_credit_card_no_enc',
                'fType' => 'ENCRYPTED_FULL_TEXT',
                'fSafeChars' => '1234567890',
                'fTransform' => 'NONE',
                'fMinTokenLen' => 4,
            ],

        ]
    ];
    
    public function rosters()
    {
        return $this->hasMany(Roster::class);
    }

}
