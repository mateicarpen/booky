<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookmarkTypes extends Migration
{
    const BOOKMARK_ID = 1;
    const FOLDER_ID = 2;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookmark_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        DB::table('bookmark_types')->insert([
            [
                'id'   => self::BOOKMARK_ID,
                'name' => 'Bookmark',
            ],
            [
                'id'   => self::FOLDER_ID,
                'name' => 'Folder',
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bookmark_types');
    }
}
