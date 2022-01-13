<?php
namespace Paulodiff\RainbowTableIndex\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use  Paulodiff\RainbowTableIndex\RainbowTableIndexTrait;

class TeamType extends Model
{
    use HasFactory;
    use RainbowTableIndexTrait;

    protected $table = 'team_types';
    protected $primaryKey = 'team_type_id';

    protected $fillable = [
        'team_type_description', 
        'team_type_description_enc',
        'team_type_rules', 
    ];

    public static $rainbowTableIndexConfig = [
          'table' => [
            'primaryKey' => 'id',
            'tableName' => 'teams',
        ],
        'fields' => [
            [
              'fName' => 'team_type_description_enc',
              'fType' => 'ENCRYPTED_FULL_TEXT',
              'fSafeChars' => " 'àèéìòùqwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM.",
              'fTransform' => 'UPPER_CASE',
              'fMinTokenLen' => 3,
            ]
        ]
    ];
    
    public function teams()
    {
        return $this->hasMany(Team::class);
    }

}
