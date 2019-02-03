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
            'moods' => Cache::get('moods'),
        ]);
    }

    /**
     * Store mood value in cache
     * @param  Request $request Request data
     * @return
     */
    public function store(Request $request) {
        if (!Cache::has('moods')) {
            // If cache is null, start it with an empty array
            Cache::put('moods', []);
        }

        // Add new mood to array
        $moods = Cache::get('moods');
        $moods[] = $request->mood;
        Cache::forever('moods', $moods);

        return json_encode([
            'moods' => "{$request->mood} added",
        ]);
    }
}
