<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBookmarksIncreaseLengthOfIcon extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->mediumText('icon_new')->after('url')->nullable();
        });

        DB::statement('update bookmarks set icon_new = icon;');

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropColumn('icon');
            $table->renameColumn('icon_new', 'icon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->text('icon_new')->after('url')->nullable();
        });

        DB::statement('update bookmarks set icon_new = icon;');

        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropColumn('icon');
            $table->renameColumn('icon_new', 'icon');
        });
    }
}
