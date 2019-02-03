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
            'mood' => Cache::get('mood'),
        ]);
    }

    /**
     * Store mood value in cache
     * @param  Request $request Request data
     * @return
     */
    public function store(Request $request) {
        Cache::put('mood', $request->mood, 15);

        return json_encode([
            'mood' => $request->mood,
        ]);
    }
}
