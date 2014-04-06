# Eloquental
Its eloquental my dear Watson! A rather clever model for Laravel's Eloquent ORM with self-validation, ordering, and query control.

Inspired by Ardent and Eloquent.

[![Build Status](https://travis-ci.org/innoscience/eloquental.png?branch=master)](https://travis-ci.org/innoscience/comrade-opml)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/innoscience/eloquental/badges/quality-score.png?s=9fd993af70c414594764232b350f9d153013a095)](https://scrutinizer-ci.com/g/innoscience/eloquental/)

Copyright (C) 2014 Brandon Fenning

## Obligations

Tested & Compatible with PHP 5.3+

Requires Laravel 4.1

## Instatement

Add `innoscience/eloquental` to the `composer.json` file:

	"require": {
        "innoscience/eloquental": "dev-master"
    }

After this, run `composer update` to install the package

## A Brief Overview

Eloquental is namespaced to `Innoscience\Eloquental`, below is a rather elemental example of a Eloquental model using conditional validation in conjunction with the model's validation events:

	use Innoscience\Eloquental\Eloquental;

	class News extends Eloquental {
		var $table = 'news';
		var $orderBy = array('date' => 'desc');
		var $rules = array(
				'title'=>'required',
				'slug'=>'required|unique:news,slug',
				'date'=>'required|date',
				'article'=>'required|min:50'
		);
	}

	News::validating(function($model){
		$model->getValidator()->sometimes('slug', 'required|unique:news,slug,'.$model->id, function($model) {
			return $model->exists;
		});
	});

	News::validated(function($model){
		// # Do some awesome validated stuff here that I can't think of at the moment
	});

	// # Elsewhere, our models initiated, the latent eloquental power is harnessed...

	$model = new Article(array(
			'title'=>'Case Files',
			'date'=>'1889-10-01',
			'slug'=>'case-files',
			'article'=>'My notes are as follows...'));

	if (!$model->save()) {
		return Redirect::back()->withErrors($model->errors());
	}

## The Natural Ordering of Things

Eloquental has a built in ordering property that if set, will automatically invoke `orderBy()` by default on queries:

> Note: This functionality currently does not work for the $query->lists() method

	News extends Eloquental {
		...
		var $orderBy = array('date' => 'desc');
	}

	User extends Eloquental {
		...
		var $orderBy = array('lastname' => 'asc', 'firstname' => 'asc');
	}

> **Fun fact**: If you add an `->orderBy()` clause when querying a model, the model's `->orderBy` property will be ignored when generating the query. This is made possible with a new `Builder` provided by Eloquental that allows the orderBy clause to be added conditionally at the end of the query building process. Later on the documentation will demonstrate how you can add similar custom functionality to your models.


## Validate This My Good Sir

### Basics

Eloquental has a built in rules set and the ability to add rules on the fly as required.

	Article extends Eloquental {
		...
		var $rules = array(
			'title'=>'required'		
		);
	}

	$model = new Article(array('title'=>'Case Files'));
	if (!$model->save()) {
		return Redirect::back()->withErrors($model->errors());
	}

	// Or validate the model without saving

	if (!$model->validate()) {
		return Redirect::back()->withErrors($model->errors());		
	}

> **Fun Fact**: If `$rules` are not set, neither the `->validate()` method nor the `::validating` or `::validated` events will fire for the model.

### Validation Events

Eloquental models have two validation events that are fired during saving:

	User::validating(function($model) {
		$model->getValidator()->sometimes('password', 'required|min:8|confirmed', function($model) {
			return ($model->password && $model->isDirty('password')) ? TRUE : FALSE;
		});	
	});

	User::validated(function($model) {
		// # Validated stuff goes here
	});

### Skip Validation

Sometimes you may wish a model to skip validation:

	$model->skipValidation()->save();

As soon as the `->save()` method is invoked on a model, the `skipValidation` flag returns to `FALSE`. Subsequent saves will be validated unless `skipValidation()` is invoked.

### Validation Errors

If a model fails validation, the errors are accessible via the `->errors()` method which returns a `MessageBag` from Laravel:

	if (!$model->save()) {
		echo $model->errors()->all('<li>:message</li>');
	}

### Manual Validation

	if (!$model->validate()) {
		echo $model->errors()->all('<li>:message</li>');
	}
	
### Manual Validation with override rules

	$rules = array('title'=>required');

	$customMessages = array('title'=>'This be required');

	if (!$model->validate($rules, $customMessages)) {
		echo $model->errors()->all('<li>:message</li>');
	}

> Note: Setting the `$rules` or `$customMessages` properties when calling `$model->validate($rule = array(), $customMessages = array())` will override the model's default rules and reset the validation instance.


### Manipulate the Validator instance

If you wish to manipulate the `Validator` instance, you can easily do so:

	$model = News::find(1);	
	$model->getValidator()->sometimes('author', 'required', function($model) {
	    return $model->published == 1;
	});

### Set the Validator instance

	$model->setValidator(Validator::make($rules, $messages));



## Auto-Purge Feckless Data

In combination with the validation mechanism, auto-purge allows attributes to be passed to the model and disposed of before saving:

	User extends Eloquental {
		...
		var $autoPurge = array('password_confirmation');
	}

This will cause the `password_confirmation` attribute to be purged from the attributes of the model before saving. 

> **Fun Fact**: Auto-purged properties are last accessible in the `::validated()` event as they are purged right before the save event fires. If the `$autoPurge` property is not populated, auto-purge is not applied.


## A Timely Builder of Queries

The query builder event is a powerful mechanism that allows you to insert query clauses conditionally just before the query builder is executed. The `->orderBy` mechanism in Eloquental is an implementation of this functionality. Other uses include conditionally showing models based on authentication status, etc. It should be used with care.

> **Fun fact**: If an `->orderBy()` clause is added during the `buildingQuery` event, this will cause the `$model->orderBy` property to be ignored.

The Query Builder event is invoked similarly to model events except passing the query object via the `buildingQuery` static method.

#### Example for showing only published content to guest users

	News::buildingQuery(function($query) {
		if (!Auth::check()) {
			return $query->where('published', 1);
		}
	
		return $query;
	});

The method must return either `FALSE`, `$query` or an instance of `Illuminate\Database\Query\Builder`.
  
> Note: This functionality currently does not work for the $query->lists() method

## Notorized in the Trial of Testing
Eloquental is fully unit tested. Tests are located in the `tests` directory of the Eloquental package and can be run with `phpunit` in the package's base directory.

## Licensed to all Honorable Personages
Eloquental is licensed under GPLv2