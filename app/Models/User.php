<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject 
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'team_id',
        'role',
        'is_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
        ];
    }


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    // Keep all the existing relationships and methods...
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function statistics()
    {
        return $this->hasMany(PlayerStatistic::class);
    }

    // public function goals()
    // {
    //     return $this->hasMany(Goal::class, 'player_id');
    // }

    // public function assists()
    // {
    //     return $this->hasMany(Assist::class, 'player_id');
    // }

    // public function cards()
    // {
    //     return $this->hasMany(Card::class, 'player_id');
    // }

    // public function handballs()
    // {
    //     return $this->hasMany(Handball::class, 'player_id');
    // }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function getTotalGoals()
    {
        return $this->statistics()->sum('goals');
    }

    public function getTotalAssists()
    {
        return $this->statistics()->sum('assists');
    }

    public function getTotalYellowCards()
    {
        return $this->statistics()->sum('yellow_cards');
    }

    public function getTotalRedCards()
    {
        return $this->statistics()->sum('red_cards');
    }

    public function getTotalHandballs()
    {
        return $this->statistics()->sum('handballs');
    }

    public function getLatestPayment()
    {
        return $this->payments()
            ->where('type', 'monthly_dues')
            ->where('is_verified', true)
            ->latest()
            ->first();
    }

}
