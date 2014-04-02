<?php

namespace Innoscience\Eloquental\Tests\Database\Seeds;

use Innoscience\Eloquental\Tests\Models\TestPost;
use Innoscience\Eloquental\Tests\Models\TestUser;
use Illuminate\Database\Seeder;

class EloquentalTestDatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		\Eloquent::unguard();

		$user1 = TestUser::create(array('name'=>'Guy Johnson', 'email'=>'guy@test.com', 'password'=>'test'));
		$user2 = TestUser::create(array('name'=>'John Jackson', 'email'=>'jack@test.com', 'password'=>'test'));

		$user1->posts()->save(new TestPost(array(
			'title'=>'Entry',
			'slug'=>'entry',
			'date'=>'2013-01-01',
			'content'=>'For a space the old man walked the deck in rolling reveries. But chancing to slip with his ivory heel, he saw the crushed copper sight-tubes of the quadrant he had the day before dashed to the deck.',
		)));

		$user1->posts()->save(new TestPost(array(
			'title'=>'Oldest',
			'slug'=>'oldest',
			'date'=>'2000-01-01',
			'content'=>'For a space the old man walked the deck in rolling reveries. But chancing to slip with his ivory heel, he saw the crushed copper sight-tubes of the quadrant he had the day before dashed to the deck.',
		)));

		$user1->posts()->save(new TestPost(array(
			'title'=>'Welcome to our new blog',
			'slug'=>'welcome',
			'date'=>'2013-05-07',
			'content'=>'During the most violent shocks of the Typhoon, the man at the Pequod\'s jaw-bone tiller had several times been reelingly hurled to the deck by its spasmodic motions, even though preventer tackles had been attached to it—for they were slack—because some play to the tiller was indispensable.',
		)));

		$user1->posts()->save(new TestPost(array(
			'title'=>'Exciting new updates!',
			'slug'=>'exciting-updates',
			'date'=>'2013-08-14',
			'content'=>'Belated, and not innocently, one bitter winter\'s midnight, on the road running between two country towns, the blacksmith half-stupidly felt the deadly numbness stealing over him, and sought refuge in a leaning, dilapidated barn. The issue was, the loss of the extremities of both feet. Out of this revelation, part by part, at last came out the four acts of the gladness, and the one long, and as yet uncatastrophied fifth act of the grief of his life\'s drama.',
			'active'=>0
		)));

		$user2->posts()->save(new TestPost(array(
			'title'=>'An incredible article',
			'slug'=>'incredible-article',
			'date'=>'2013-12-09',
			'content'=>'In the English boats two tubs are used instead of one; the same line being continuously coiled in both tubs. There is some advantage in this; because these twin-tubs being so small they fit more readily into the boat, and do not strain it so much; whereas, the American tub, nearly three feet in diameter and of proportionate depth, makes a rather bulky freight for a craft whose planks are but one half-inch in thickness; for the bottom of the whale-boat is like critical ice, which will bear up a considerable distributed weight, but not very much of a concentrated one.',
		)));

		$user2->posts()->save(new TestPost(array(
			'title'=>'A new year update',
			'slug'=>'new-year-update',
			'date'=>'2014-01-25',
			'content'=>'Meanwhile, whatever were his own secret thoughts, Starbuck said nothing, but quietly he issued all requisite orders; while Stubb and Flask—who in some small degree seemed then to be sharing his feelings—likewise unmurmuringly acquiesced. As for the men, though some of them lowly rumbled, their fear of Ahab was greater than their fear of Fate. But as ever before, the pagan harpooneers remained almost wholly unimpressed; or if impressed, it was only with a certain magnetism shot into their congenial hearts from inflexible Ahab\'s.',
			'active'=>0
		)));

		$user2->posts()->save(new TestPost(array(
			'title'=>'Latest',
			'slug'=>'latest',
			'date'=>'2014-04-01',
			'content'=>'Accessory, perhaps, to the impulse dictating the thing he was now about to do, were certain prudential motives, whose object might have been to revive the spirits of his crew by a stroke of his subtile skill, in a matter so wondrous as that of the inverted compasses. Besides, the old man well knew that to steer by transpointed needles, though clumsily practicable, was not a thing to be passed over by superstitious sailors, without some shudderings and evil portents.',
		)));


	}
}