<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class MoodController extends Controller {
    /**
     * Return last mood
     * @return json Mood
     */
    public function show() {
        return json_encode([
            'moods' => Auth::user()->moods->pluck('mood'),
        ]);
    }

    /**
     * Store mood value in cache
     * @param  Request $request Request data
     * @return
     */
    public function store(Request $request) {
        Mood::create([
            'user_id' => Auth::user()->id,
            'mood' => $request->mood,
        ]);

        return json_encode([
            'moods' => "{$request->mood} added",
        ]);
    }
}
