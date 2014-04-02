<?php

use Innoscience\Eloquental\Tests\Models\TestPost;
use Innoscience\Eloquental\Tests\Models\TestUser;

class EloquentalTest extends PHPUnit_Framework_TestCase {

	static $packagePath;

	static function setUpBeforeClass() {

		\Config::set('database.default', 'sqlite');
		\Config::set('database.connections.sqlite.database', ':memory:');
		\Config::set('auth.model', 'Innoscience\Eloquental\Tests\Models\TestUser');
		\Mail::pretend(true);
		\Artisan::call('migrate:install');

		if (stripos(__DIR__, 'workbench') !== FALSE) {
			self::$packagePath = 'workbench/innoscience/eloquental';
		}
		else if (stripos(__DIR__, 'vendor') !== FALSE) {
			self::$packagePath = 'vendor/innoscience/eloquental';
		}

		\Artisan::call('migrate:rollback');
		\Artisan::call('migrate', array('--path'=>self::$packagePath.'/tests/database/migrations'));
		\Artisan::call('db:seed', array('--class'=>'Innoscience\Eloquental\Tests\Database\Seeds\EloquentalTestDatabaseSeeder'));
	}

	static function tearDownAfterClass() {

	}

	function tearDown () {
		\Auth::logout();
	}

	function setUp() {

	}

	function testOrdering() {
		$posts = TestPost::get();
		$this->assertEquals('latest', $posts[0]->slug);
	}

	function testNullOrdering() {
		$post = new TestPost;
		$post->setOrderBy(array());
		$posts = $post->first();

		$this->assertEquals('entry', $posts->slug);
	}

	function testOrderingOverride() {
		$post = new TestPost();
		$posts = $post->setOrderBy(array('title','desc'))->first();
		$this->assertEquals('welcome', $posts->slug);
	}

	function testOrderingOverrideStacked() {
		$post = new TestPost();
		$posts = $post->setOrderBy(array('title','asc', 'date', 'desc'))->first();
		$this->assertEquals('incredible-article', $posts->slug);
	}

	function testGetInactiveItemsWhenLoggedIn() {
		\Auth::loginUsingId(1);
		$post = new TestPost();
		$posts = $post->setOrderBy(array('title','asc', 'date', 'desc'))->first();
		$this->assertEquals('new-year-update', $posts->slug);
	}

	function testGetInactiveItemsWhenNotLoggedInAllAccessors() {
		$post = TestPost::where('slug', 'exciting-updates')->first();
		$post2 = TestPost::where('slug', 'exciting-updates')->get();
		$post3 = TestPost::where('slug', 'exciting-updates')->lists('title','id');
		$post4 = TestPost::where('slug', 'exciting-updates')->pluck('title');
		$post5 = TestPost::where('slug', 'exciting-updates')->get(array('title', 'id'));

		$this->assertEquals(false, $post);
		$this->assertEquals(0, $post2->count());
		$this->assertEquals(1, count($post3)); // # ->lists() currently does not work
		$this->assertEquals(false, $post4);
		$this->assertEquals(0, $post5->count());
	}

	function testEloquentalBasicSave() {
		$user = new TestUser();
		$user->name = 'tester';
		$user->email = 'test@test.com';
		$user->password = 'test';
		$user->save();

		$this->assertEquals('test@test.com', TestUser::where('email', 'test@test.com')->first()->email);
	}

	function testSkipValidation() {
		$post = new TestPost();
		$post->title = 'Welcome';
		$post->slug = 'welcome';
		$post->date = date('Y-m-d');
		$post->content = 'hello';
		$post->user_id = 1;

		$post->skipValidation()->save();

		$this->assertEquals(2, TestPost::where('slug', 'welcome')->count());
	}

	function testValidatingEvents() {
		$user = new TestUser();
		$user->name = 'secondary';
		$user->email = 'test3@test.com';
		$user->password = 'test';
		$user->save();
	}

	function testValidatingFalseEvent() {
		$user = new TestUser();
		$user->name = 'discard';
		$user->save();
	}

	function testValidatedFalseEvent() {
		$user = new TestUser();
		$user->name = 'discard2';
		$user->email = 'test4@test.com';
		$user->password = 'test';
		$user->save();
	}


	function testFailedValidation() {
		$post = new TestPost();
		$post->title = '';
		$post->slug = '';
		$post->date = '';
		$post->content = '';
		$post->user_id = 1;

		$post->save();
		$this->assertEquals(4, $post->errors()->count());
	}

	function testValidatingSometimes() {
		$post = new TestPost();
		$post->title = 'Hello';
		$post->slug = 'hello';
		$post->date = date('Y-m-d');
		$post->content = 'hello';
		$post->user_id = 1;

		$post->getValidator()->sometimes('slug', 'required|max:2', function($post) {
			return strlen($post->title) >= 1;
		});

		$this->assertEquals(false, $post->validate());
	}

	function testValidatingManually() {
		$post = new TestPost();
		$post->title = 'Hello';
		$post->slug = 'hello';
		$post->date = date('Y-m-d');
		$post->content = 'hello';
		$post->user_id = 1;

		$result = $post->validate(array('title'=>'min:8'));

		$this->assertEquals(false, $result);
		$this->assertEquals(true, $post->validate());
	}


	function testAutoPurge() {
		$user = new TestUser();
		$user->name = 'secondary';
		$user->email = 'test5@test.com';
		$user->password = 'test';
		$user->password_confirmation = 'test';
		$user->setRule('password', 'required|confirmed');
		$result = $user->save();
		$this->assertEquals(true ,$result);
		$this->assertEquals(null ,$user->password_confirmation);
	}



	function testRuleAndMessageGettersSetters() {
		$post = new TestPost;

		$this->assertEquals(4, count($post->getRules()));
		$this->assertEquals('required', ($post->getRule('title')));
		$this->assertEquals(2, count($post->setRules(array('title'=>'required','date'=>'required'))->getRules()));

		$this->assertEquals(3, count($post->setRule('slug','required')->getRules()));

		$post->mergeRules(array('content'=>'required'));
		$this->assertEquals(4, count($post->getRules()));

		$post->setCustomMessage('title', 'hello');
		$this->assertEquals(1, count($post->getCustomMessages()));

		$post->setCustomMessages(array('title.required'=>'Bad title','date.required'=>'Bad date'));
		$this->assertEquals(2, count($post->getCustomMessages()));

		$post->mergeCustomMessages(array('content.required'=>'Bad content'));
		$this->assertEquals(3, count($post->getCustomMessages()));

		$post->getObservableEvents();
	}
	public function testSetValidatorInstance() {
		$post = new TestPost;
		$post->setValidator(\Validator::make($post->getAttributes(), $post->getRules()));
		$this->assertInstanceOf( '\Illuminate\Validation\Validator', $post->getValidator());
	}

	/*
	 * Removed for time being
	 *
	function testServiceProvider() {
		if (!\App::getRegistered('Innoscience\Eloquental\EloquentalServiceProvider')) {
			$provider = \App::register('Innoscience\Eloquental\EloquentalServiceProvider');
			$this->assertEquals(array('eloquental'), $provider->provides());
		}
	}

	function testFacade() {
		\App::register('Innoscience\Eloquental\EloquentalServiceProvider');

		$alias['Eloquental'] = 'Innoscience\Eloquental\Facades\Eloquental';
		\Illuminate\Foundation\AliasLoader::getInstance($alias)->register();

		$this->assertEquals('eloquental', Eloquental::getFacadeAccessor());
	}
	*/

	/**
	 * @expectedException Exception
	 */
	function testBuilderException() {
		TestUser::buildingQuery(function($query){
			return 'this is bad';
		});
		TestUser::validating(function($model){
			return true;
		});
		TestUser::validated(function($model){
			return true;
		});
		TestUser::get();
	}

	function testSetNewBuilder() {
		\Innoscience\Eloquental\Eloquental::setBuilder(null);
		TestPost::get();
	}


}