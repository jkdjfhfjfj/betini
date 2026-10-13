<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameMatchSection extends Model
{
    protected $table = 'game_match_sections';

    protected $fillable = ['section'];

    public function gameMatch()
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }
}
