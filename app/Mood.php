<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mood extends Model {
	protected $table = 'moods';

	/**
	 * Relationship to users table
	 * 
	 * @return BelongsToRelationship
	 */
	public function user() {
		return $this->belongsTo('App\User');
	}
}
