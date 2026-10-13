<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryAndSectionsToGameMatches extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('game_matches', 'country')) {
            Schema::table('game_matches', function (Blueprint $table) {
                $table->string('country', 100)->nullable();
            });
        }

        if (!Schema::hasTable('game_match_sections')) {
            Schema::create('game_match_sections', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('match_id');
                $table->string('section', 32);
                $table->timestamps();
                $table->unique(['match_id', 'section']);
                $table->foreign('match_id')
                    ->references('id')
                    ->on('game_matches')
                    ->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('game_match_sections');

        if (Schema::hasColumn('game_matches', 'country')) {
            Schema::table('game_matches', function (Blueprint $table) {
                $table->dropColumn('country');
            });
        }
    }
}
