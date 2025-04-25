<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'match_id',
        'goals',
        'assists',
        'yellow_cards',
        'red_cards',
        'handballs',
    ];

    /**
     * Get the user that owns the statistic
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the match that the statistic belongs to
     */
    public function match()
    {
        return $this->belongsTo(FootballMatch::class);
    }
}
