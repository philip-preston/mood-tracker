<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Relationship to moods table
     * Default values are that the foreign key on the other table will be user_id
     * 
     * @return HasManyRelationship
     */
    public function moods() {
        return $this->hasMany('App\Mood');
    }

    /**
     * Calculate current mood streak
     * @return int current streak
     */
    public function currentStreak() {
        $streak = 0;
        $latest_mood = $this->moods->last()->mood;

        $i_date = Carbon::today();
        $i_mood = $this->moods()->where('created_at', '>=', Carbon::today())
            ->where('created_at', '<', Carbon::tomorrow())->last()->mood;

        while ($latest_mood == $i_mood) {
            $streak++;

            $i_date = $i_date->addDays(1);
            $i_mood = $this->moods()->where('created_at', '>=', $i_date)
                ->where('created_at', '<', $i_date->addDays(1))->last()->mood;
        }
    }

    public function streakPercentile() {
        $streaks = collect([]);

        foreach(User::all() as $user) {
            $streaks->push($user->currentStreak());
        }

        // Sort list of streaks to get percentiles
        // Get first index where your streak is listed
        $index = $streaks->sort()->search($this->currentStreak());

        // Percentile is posision / total steaks
        return $index / $streaks->count();
    }
}
