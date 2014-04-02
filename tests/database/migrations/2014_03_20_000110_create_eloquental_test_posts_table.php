<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEloquentalTestPostsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::dropIfExists('test_posts');
        Schema::create('test_posts', function(Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id');
            $table->string('title');
			$table->string('slug');
			$table->string('date');
			$table->text('content');
			$table->boolean('active')->default(1);
            $table->timestamps();
			$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('test_posts');
    }

}