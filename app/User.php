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

        $first_mood_date = $this->moods->first()->created_at;



        $i_date = Carbon::today();
        $i_mood = $this->moods()->where('created_at', '>=', Carbon::today())
            ->where('created_at', '<', Carbon::tomorrow())->last()->mood;

        while ($latest_mood == $i_mood) {
            $streak++;

            $i_date = $i_date->subDays(1);
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

        // Percentile is position / total steaks
        return $index / $streaks->count();
    }

    public function moodCounts() {
        return $this->moods()->select('mood', DB::raw('COUNT(*) as count'))->groupBy('mood');
    }

    public function longestStreaks() {
        // Longest streaks
        $longest_steaks = [];
        $current_streaks = [];
        $first_mood_day = Carbon::parse($this->moods->first()->created_at)->subDays(1);

        // Loop through all moods
        for ($day = Carbon::today(); $day >= $first_mood_day) {
            $days_moods = $this->moods()->where('created_by', '<=', $day)
                ->where('created_by', '>', $day->addDay(1));
            $unique_moods = $days->pluck('moods')->unique();

            foreach ($unique_moods as $mood) {
                if (!array_key_exists($mood)) {
                    // Add row to array, if first time we've seen it
                    $current_streaks[$mood] = 1;
                    $longest_steaks[$mood] = 1;
                } else () {
                    $current_streaks[$mood]++;
                    if ($current_streaks[$mood] > $longest_streaks[$mood]) {
                        // If longest streak for this mood has been broken, set it
                        $longest_steaks[$mood] = $current_streaks[$mood];
                    }
                }
            }

            // If mood is in current streaks list, but not in the list for today, reset the streak
            foreach (array_keys($current_streaks) as $mood) {
                if (!in_array($mood, $unique_moods)) {
                    $current_streaks[$mood] = 0;
                }
            }
        }

        return $longest_steaks;
    }
}
