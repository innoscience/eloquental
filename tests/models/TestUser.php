<?php

namespace Innoscience\Eloquental\Tests\Models;


use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;
use Innoscience\Eloquental\Eloquental;

class TestUser extends Eloquental implements UserInterface, RemindableInterface {

	var $table = 'test_users';
	var $fillable = array('name', 'email', 'password', 'password_confirmation');
	var $softDelete = TRUE;
	var $autoPurge = array('password_confirmation');

	var $rules = array(
		'name'=>'required',
		'email'=>'required|email|unique:test_users,email',
		'password'=>'required',
	);

	public function posts() {
		return $this->hasMany('Innoscience\Eloquental\Tests\Models\TestPost', 'user_id');
	}

	public function getAuthIdentifier()	{
		return $this->getKey();
	}

	public function getAuthPassword() {
		return $this->password;
	}

	public function getReminderEmail() {
		return $this->email;
	}

}

TestUser::saving(function($model) {
	if ($model->password && $model->isDirty('password')) {
		$model->password = \Hash::make($model->password);
	}
	return true;
});

TestUser::validating(function($model){
	$model->getValidator()->sometimes('password', 'min:4', function($model) {
		return  ($model->password && $model->isDirty('password')) ? TRUE: FALSE;
	});

	if ($model->name == 'discard') {
		return FALSE;
	}
});

TestUser::validated(function($model){
	if ($model->name == 'discard2') {
		return FALSE;
	}
});