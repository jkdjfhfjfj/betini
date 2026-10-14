<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\Notify;
use App\Http\Traits\Upload;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stevebauman\Purify\Facades\Purify;
use Facades\App\Services\BasicService;


class TicketController extends Controller
{
    use Upload, Notify;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('admin')->user();
            return $next($request);
        });
    }

    public function tickets(string $type = null, Request $request)
    {

        $search = $request->all();
        $dateSearch = $request->input('date_time', '');
        $date = is_string($dateSearch)
            && preg_match("/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/", $dateSearch) === 1;

        $title = 'Tickets List';
        $pendingOnly = false;

        if (isset($type) && BasicService::validateKeyword($type, 'pending') == 1) {
            $title = 'Pending Tickets';
            $pendingOnly = true;
            $type = null;
        } elseif (isset($type) && BasicService::validateKeyword($type, 'open') == 1) {
            $title = 'Open Tickets';
            $type = 0;
        } elseif (isset($type) && BasicService::validateKeyword($type, 'answered') == 1) {
            $title = 'Answered Tickets';
            $type = 1;
        } elseif (isset($type) && BasicService::validateKeyword($type, 'replied') == 1) {
            $title = 'Replied Tickets';
            $type = 2;
        } elseif (isset($type) && BasicService::validateKeyword($type, 'closed') == 1) {
            $title = 'Closed Tickets';
            $type = 3;
        }

        $tickets = Ticket::when($request->filled('ticket'), function ($query) use ($search) {
            return $query->where('ticket', 'LIKE', "%{$search['ticket']}%");
        })
            ->when($request->filled('email'), function ($query) use ($search) {
                return $query->where('email', 'LIKE', "%{$search['email']}%");
            })
            ->when($request->filled('status'), function ($query) use ($search) {
                return $query->where('status', $search['status']);
            })
            ->when($date == 1, function ($query) use ($dateSearch) {
                return $query->whereDate("created_at", $dateSearch);
            })->with('user')
            ->when($pendingOnly, function ($query) {
                return $query->whereIn('status', [0, 2]);
            })
            ->when(isset($type), function ($query) use ($type) {
                return $query->where('status', $type);
            })
            ->latest()
            ->paginate(config('basic.paginate'));
        $empty_message = 'No Data found.';



        return view('admin.ticket.index', compact('tickets', 'title', 'empty_message'));
    }


    public function ticketReply($id)
    {
        $ticket = Ticket::where('id', $id)
            ->with('user', 'messages.admin', 'messages.attachments')
            ->firstOrFail();
        $title = 'Ticket #' . $ticket->ticket;
        return view('admin.ticket.show', compact('ticket', 'title'));
    }

    public function ticketReplySend(Request $request, $id)
    {
        $ticket = Ticket::where('id', $id)->with('user')->firstOrFail();
        $request->validate([
            'replayTicket' => ['required', 'in:1,2'],
        ]);
        if ($request->replayTicket == 1) {
            $request->validate([
                'attachments' => ['nullable', 'array', 'max:5'],
                'attachments.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
                'message' => ['required', 'string', 'max:10000'],
            ]);

            $messageText = Purify::clean($request->input('message'));
            $message = DB::transaction(function () use ($ticket, $messageText) {
                $ticket->status = 1;
                $ticket->last_reply = Carbon::now();
                $ticket->save();

                $message = new TicketMessage();
                $message->ticket_id = $ticket->id;
                $message->admin_id = $this->user->id;
                $message->message = $messageText;
                $message->save();

                return $message;
            });

            $path = config('location.ticket.path');
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $image) {
                    try {
                        TicketAttachment::create([
                            'ticket_message_id' => $message->id,
                            'image' => $this->uploadImage($image, $path),
                        ]);
                    } catch (\Throwable $exp) {
                        Log::warning('Admin ticket reply attachment upload failed', [
                            'ticket_id' => $ticket->id,
                            'message_id' => $message->id,
                            'exception' => $exp->getMessage(),
                        ]);
                        return back()->with('warning', 'Your reply was saved, but an attachment could not be uploaded.');
                    }
                }
            }



            $msg = [
                'ticket_id' => $ticket->ticket
            ];
            $action = [
                "link" => route('user.ticket.view', $ticket->ticket),
                "icon" => "fas fa-ticket-alt text-white"
            ];

            try {
                $this->userPushNotification($ticket->user, 'ADMIN_REPLIED_TICKET', $msg, $action);
                $this->sendMailSms($ticket->user, 'ADMIN_SUPPORT_REPLY', [
                    'ticket_id' => $ticket->ticket,
                    'ticket_subject' => $ticket->subject,
                    'reply' => $messageText,
                ]);
            } catch (\Throwable $exp) {
                Log::warning('Ticket reply notification failed', [
                    'ticket_id' => $ticket->id,
                    'exception' => $exp->getMessage(),
                ]);
            }

            return back()->with('success', "Ticket has been replied");
        } elseif ($request->replayTicket == 2) {
            $ticket->status = 3;
            $ticket->last_reply = Carbon::now();
            $ticket->save();
        }

        return back()->with('success', "Ticket has been closed");
    }


    public function ticketDownload($ticket_id)
    {
        $attachment = TicketAttachment::with('supportMessage', 'supportMessage.ticket')->findOrFail(decrypt($ticket_id));
        $file = $attachment->image;
        $path = config('location.ticket.path');
        $full_path = $path . '/' . $file;
        $title = slug($attachment->supportMessage->ticket->subject) . '-' . $file;
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mimetype = mime_content_type($full_path);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($full_path);
    }

    public function ticketDelete(Request $request)
    {
        $message = TicketMessage::findOrFail($request->message_id);
        $path = config('location.ticket.path');
        if (count($message->attachments) > 0) {
            foreach ($message->attachments as $img) {
                @unlink($path . '/' . $img->image);
                $img->delete();
            }
        }
        $message->delete();
        return back()->with('success', "Message has been deleted");
    }
}
