<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\User\SupportController as UserSupportController;
use App\Models\Admin;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TicketReplyTest extends TestCase
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

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('ticket')->unique();
            $table->string('subject');
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamp('last_reply')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function test_user_can_reply_without_attachments_and_cannot_reply_to_another_users_ticket()
    {
        $owner = User::create(['username' => 'owner', 'email' => 'owner@example.test']);
        $otherUser = User::create(['username' => 'other', 'email' => 'other@example.test']);
        $ownedTicket = $this->createTicket($owner, 'OWNED-1');
        $foreignTicket = $this->createTicket($otherUser, 'FOREIGN-1');

        $controller = new TestableUserSupportController();
        $controller->user = $owner;
        $controller->reply(Request::create('/ticket/reply/' . $ownedTicket->id, 'PUT', [
            'replayTicket' => 1,
            'message' => 'I can provide more details.',
        ]), $ownedTicket->id);

        $this->assertSame(2, (int) $ownedTicket->fresh()->status);
        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ownedTicket->id,
            'admin_id' => null,
            'message' => 'I can provide more details.',
        ]);

        try {
            $controller->reply(Request::create('/ticket/reply/' . $foreignTicket->id, 'PUT', [
                'replayTicket' => 1,
                'message' => 'This must not be added.',
            ]), $foreignTicket->id);
            $this->fail('A user must not be able to reply to another user’s ticket.');
        } catch (ModelNotFoundException $exception) {
            $this->assertSame(0, TicketMessage::where('ticket_id', $foreignTicket->id)->count());
        }
    }

    public function test_admin_can_reply_without_attachments_and_notification_errors_do_not_undo_the_reply()
    {
        $owner = User::create(['username' => 'owner', 'email' => 'owner@example.test']);
        $admin = Admin::create(['username' => 'support-agent']);
        $ticket = $this->createTicket($owner, 'ADMIN-1');

        $controller = new TestableAdminTicketController();
        $controller->user = $admin;
        $controller->notificationsShouldFail = true;
        $controller->ticketReplySend(Request::create('/admin/ticket/reply/' . $ticket->id, 'PUT', [
            'replayTicket' => 1,
            'message' => 'We are looking into this.',
        ]), $ticket->id);

        $this->assertSame(1, (int) $ticket->fresh()->status);
        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'admin_id' => $admin->id,
            'message' => 'We are looking into this.',
        ]);
    }

    private function createTicket(User $user, string $reference): Ticket
    {
        return Ticket::create([
            'user_id' => $user->id,
            'name' => $user->username,
            'email' => $user->email,
            'ticket' => $reference,
            'subject' => 'Help needed',
            'status' => 0,
            'last_reply' => now(),
        ]);
    }
}

class TestableUserSupportController extends UserSupportController
{
    public $user;

    public function __construct()
    {
    }

    public function adminPushNotification($templateKey, $params = [], $action = [])
    {
    }
}

class TestableAdminTicketController extends AdminTicketController
{
    public $user;
    public $notificationsShouldFail = false;

    public function __construct()
    {
    }

    public function userPushNotification($user, $templateKey, $params = [], $action = [])
    {
        if ($this->notificationsShouldFail) {
            throw new \RuntimeException('Simulated notification outage');
        }
    }

    public function sendMailSms($user, $templateKey, $params = [], $subject = null, $requestMessage = null)
    {
        if ($this->notificationsShouldFail) {
            throw new \RuntimeException('Simulated mail outage');
        }
    }
}
