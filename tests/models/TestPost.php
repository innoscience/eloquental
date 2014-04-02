<?php

namespace Innoscience\Eloquental\Tests\Models;

use Innoscience\Eloquental\Eloquental;

class TestPost extends Eloquental {

	var $table = 'test_posts';
	var $fillable = array('title', 'date', 'content', 'active', 'slug');
	var $softDelete = TRUE;

	var $orderBy = array('date', 'desc');

	var $rules = array(
		'title'=>'required',
		'date'=>'required|date',
		'content'=>'required',
		'slug'=>'required|unique:test_posts,slug',
	);

	public function user() {
		return $this->belongsTo('Innoscience\Eloquental\Tests\Models\TestUser', 'user_id');
	}
}

TestPost::buildingQuery(function($query) {
	if (!\Auth::check()) {
		return $query->where('active', 1);
	}

	return $query;
});