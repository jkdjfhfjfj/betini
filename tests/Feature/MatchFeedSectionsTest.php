<?php

namespace Tests\Feature;

use App\Models\GameMatch;
use App\Http\Controllers\GameFetchController;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MatchFeedSectionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('game_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('tournament_id')->nullable();
            $table->unsignedBigInteger('team1_id')->nullable();
            $table->unsignedBigInteger('team2_id')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('name')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('is_unlock')->default(1);
            $table->timestamps();
        });

        require_once base_path('database/migrations/2026_10_14_000001_add_country_and_sections_to_game_matches.php');
        (new \AddCountryAndSectionsToGameMatches())->up();

        Schema::create('game_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('icon')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
        Schema::create('game_tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
        Schema::create('game_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->timestamps();
        });
        Schema::create('game_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('match_id');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
        Schema::create('game_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('question_id');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function test_match_country_and_admin_section_labels_persist_through_the_match_relationship()
    {
        $match = new GameMatch();
        $match->country = 'Kenya';
        $match->save();
        $match->sectionAssignments()->create(['section' => 'jackpots']);
        $match->sectionAssignments()->create(['section' => 'turbo']);

        $this->assertSame('Kenya', $match->fresh()->country);
        $this->assertSame(
            ['jackpots', 'turbo'],
            $match->fresh()->sectionAssignments()->orderBy('section')->pluck('section')->all()
        );
    }

    public function test_match_feed_exposes_admin_country_and_section_labels_for_filtering()
    {
        DB::table('game_categories')->insert(['id' => 1, 'name' => 'Football', 'status' => 1]);
        DB::table('game_tournaments')->insert(['id' => 1, 'name' => 'Premier League', 'status' => 1]);
        DB::table('game_teams')->insert([
            ['id' => 1, 'name' => 'Home FC'],
            ['id' => 2, 'name' => 'Away FC'],
        ]);

        $start = Carbon::now()->addHour();
        $match = new GameMatch();
        $match->category_id = 1;
        $match->tournament_id = 1;
        $match->team1_id = 1;
        $match->team2_id = 2;
        $match->name = 'Preview fixture';
        $match->country = 'Kenya';
        $match->start_date = $start;
        $match->end_date = $start->copy()->addHours(2);
        $match->status = 1;
        $match->is_unlock = 1;
        $match->save();
        $match->sectionAssignments()->create(['section' => 'zoom-soccer']);
        $match->sectionAssignments()->create(['section' => 'new']);

        $feedResponse = (new GameFetchController())->index();
        $feed = json_decode($feedResponse->getContent(), true);

        $this->assertCount(1, $feed['allList']);
        $this->assertCount(1, $feed['upcomingList']);
        $this->assertSame('Kenya', $feed['allList'][0]['country']);
        $this->assertSame(['new', 'zoom-soccer'], $feed['allList'][0]['sections']);
    }

    public function test_match_feed_returns_matches_across_many_tournaments_without_a_page_limit()
    {
        DB::table('game_categories')->insert([
            'id' => 1,
            'name' => 'Football',
            'status' => 1,
        ]);
        DB::table('game_teams')->insert([
            ['id' => 1, 'name' => 'Home FC'],
            ['id' => 2, 'name' => 'Away FC'],
        ]);

        $tournamentRows = [];
        for ($id = 1; $id <= 25; $id++) {
            $tournamentRows[] = [
                'id' => $id,
                'name' => 'Tournament ' . $id,
                'status' => 1,
            ];
        }
        DB::table('game_tournaments')->insert($tournamentRows);

        $start = Carbon::now()->addHour();
        for ($id = 1; $id <= 25; $id++) {
            $match = new GameMatch();
            $match->category_id = 1;
            $match->tournament_id = $id;
            $match->team1_id = 1;
            $match->team2_id = 2;
            $match->name = 'Fixture ' . $id;
            $match->start_date = $start->copy()->addMinutes($id);
            $match->end_date = $start->copy()->addDays(1);
            $match->status = 1;
            $match->is_unlock = 1;
            $match->save();
        }

        $response = (new GameFetchController())->index();
        $feed = json_decode($response->getContent(), true);

        $this->assertCount(25, $feed['allList']);
        $this->assertCount(25, $feed['upcomingList']);
    }
}
