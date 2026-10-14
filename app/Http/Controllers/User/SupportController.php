<?php

namespace App\Http\Controllers\User;


use App\Http\Traits\Notify;
use App\Http\Traits\Upload;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupportController extends Controller
{
    use Upload, Notify;

    public function __construct()
    {
        $this->theme = template();
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
    }

    public function index()
    {

        if ($this->user->id == null) {
            abort(404);
        }
        $page_title = "Support Ticket";
        $tickets = Ticket::where('user_id', $this->user->id)->latest()->paginate(config('basic.paginate'));
        return view($this->theme.'user.support.index', compact('tickets', 'page_title'));
    }

    public function create(Request $request)
    {
        $page_title = "New Ticket";
        $user = $this->user;
        $subject = old('subject', $request->query('subject', ''));
        $message = old('message', $request->query('message', ''));
        return view($this->theme.'user.support.create', compact('page_title', 'user', 'subject', 'message'));
    }

    public function store(Request $request)
    {

        $this->newTicketValidation($request);
        $random = rand(100000, 999999);
        $ticket = $this->saveTicket($request, $random);

        $message = $this->saveMsgTicket($request, $ticket);

        $path = config('location.ticket.path');
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $image) {
                try {
                    $this->saveAttachment($message, $image, $path);
                } catch (\Exception $exp) {
                    return back()->withInput()->with('error', 'Could not upload your ' . $image);
                }
            }
        }
        $msg = [
            'username' => optional($ticket->user)->username,
            'ticket_id' => $ticket->ticket
        ];
        $action = [
            "link" => route('admin.ticket.view',$ticket->id),
            "icon" => "fas fa-ticket-alt text-white"
        ];
        $this->adminPushNotification('SUPPORT_TICKET_CREATE', $msg, $action);
        return redirect()->route('user.ticket.list')->with('success', 'Your Ticket has been pending');
    }

    public function ticketView($ticketId)
    {
        $page_title = "Ticket: #".$ticketId;
        $ticket = Ticket::where('user_id', $this->user->id)
            ->where('ticket', $ticketId)
            ->with('messages.admin', 'messages.attachments', 'user')
            ->firstOrFail();
        $user = $this->user;
        return view($this->theme.'user.support.view', compact('ticket', 'page_title', 'user'));
    }

    public function reply(Request $request, $id)
    {
        $ticket = Ticket::where('user_id', $this->user->id)->findOrFail($id);
        $request->validate([
            'replayTicket' => ['required', 'in:1,2'],
        ]);

        if ($request->replayTicket == 1) {
            $request->validate([
                'attachments' => ['nullable', 'array', 'max:5'],
                'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
                'message' => ['required', 'string', 'max:10000'],
            ]);

            $message = \Illuminate\Support\Facades\DB::transaction(function () use ($ticket, $request) {
                $ticket->status = 2;
                $ticket->last_reply = Carbon::now();
                $ticket->save();

                $message = new TicketMessage();
                $message->ticket_id = $ticket->id;
                $message->message = $request->input('message');
                $message->save();

                return $message;
            });

            $path = config('location.ticket.path');
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $image) {
                    try {
                        $this->saveAttachment($message, $image, $path);
                    } catch (\Throwable $exp) {
                        \Illuminate\Support\Facades\Log::warning('Ticket reply attachment upload failed', [
                            'ticket_id' => $ticket->id,
                            'message_id' => $message->id,
                            'exception' => $exp->getMessage(),
                        ]);
                        return back()->with('warning', 'Your reply was saved, but an attachment could not be uploaded.');
                    }
                }
            }
            $msg = [
                'username' => optional($ticket->user)->username,
                'ticket_id' => $ticket->ticket
            ];
            $action = [
                "link" => route('admin.ticket.view',$ticket->id),
                "icon" => "fas fa-ticket-alt text-white"
            ];

            try {
                $this->adminPushNotification('SUPPORT_TICKET_REPLIED', $msg, $action);
            } catch (\Throwable $exp) {
                \Illuminate\Support\Facades\Log::warning('Ticket reply notification failed', [
                    'ticket_id' => $ticket->id,
                    'exception' => $exp->getMessage(),
                ]);
            }

            return back()->with('success', 'Ticket has been replied');
        } elseif ($request->replayTicket == 2) {
            $ticket->status = 3;
            $ticket->last_reply = Carbon::now();
            $ticket->save();

            return back()->with('success', 'Ticket has been closed');
        }
        return back();
    }


    public function download($ticket_id)
    {
        $attachmentId = decrypt($ticket_id);
        $attachment = TicketAttachment::whereHas('supportMessage.ticket', function ($query) {
            $query->where('user_id', $this->user->id);
        })->findOrFail($attachmentId);
        $file = $attachment->image;
        $path = config('location.ticket.path');
        $full_path = $path . '/' . $file;

        if(file_exists($full_path)){
            $title = slug($attachment->supportMessage->ticket->subject);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            return response()->download($full_path, $title . '.' . $ext);
        }
        abort(404);
    }


    /**
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function newTicketValidation(Request $request): void
    {
        $request->validate([
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'subject' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:10000'],
        ]);
    }

    /**
     * @param Request $request
     * @param $random
     * @return
     */
    public function saveTicket(Request $request, $random): Ticket
    {
        $ticket = new Ticket();
        $ticket->user_id = $this->user->id;
        $ticket->name = $this->user->username;
        $ticket->email = $this->user->email;
        $ticket->ticket = $random;
        $ticket->subject = $request->subject;
        $ticket->status = 0;
        $ticket->last_reply = Carbon::now();
        $ticket->save();
        return $ticket;
    }

    /**
     * @param Request $request
     * @param $ticket
     * @return Models\TicketMessage
     */
    public function saveMsgTicket(Request $request, $ticket): TicketMessage
    {
        $message = new TicketMessage();
        $message->ticket_id = $ticket->id;
        $message->message = $request->message;
        $message->save();
        return $message;
    }

    /**
     * @param $message
     * @param $image
     * @param $path
     * @throws \Exception
     */
    public function saveAttachment($message, $image, $path): void
    {
        $attachment = new TicketAttachment();
        $attachment->ticket_message_id = $message->id;
        $attachment->image = $this->uploadImage($image, $path);
        $attachment->save();
    }


}
