@extends('admin.layouts.app')

@section('title')
    @lang($page_title)
@endsection

@section('content')
    <div class="page-header card card-primary m-0 m-md-4 my-4 m-md-0 p-4 p-md-5 shadow">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">@lang('Manual Wallet Requests')</h3>
                <p class="text-muted mb-0">Verify manual deposits. Send manual withdrawals before marking them as paid; rejection refunds reserved withdrawal funds.</p>
            </div>
            <span class="badge badge-warning">{{ $transactions->total() }} @lang('requests')</span>
        </div>

        <form method="get" action="{{ route('admin.wallet-manual.index') }}" class="mb-4">
            <div class="form-row align-items-end">
                <div class="col-md-4 mb-2">
                    <label for="manual_direction">@lang('Type')</label>
                    <select class="form-control" id="manual_direction" name="direction">
                        <option value="all" @if($direction === 'all') selected @endif>@lang('All requests')</option>
                        <option value="deposit" @if($direction === 'deposit') selected @endif>@lang('Deposits')</option>
                        <option value="withdrawal" @if($direction === 'withdrawal') selected @endif>@lang('Withdrawals')</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label for="manual_status">@lang('Status')</label>
                    <select class="form-control" id="manual_status" name="status">
                        <option value="pending" @if($status === 'pending') selected @endif>@lang('Pending')</option>
                        <option value="processing" @if($status === 'processing') selected @endif>@lang('Processing')</option>
                        <option value="succeeded" @if($status === 'succeeded') selected @endif>@lang('Succeeded')</option>
                        <option value="failed" @if($status === 'failed') selected @endif>@lang('Failed')</option>
                        <option value="all" @if($status === 'all') selected @endif>@lang('All statuses')</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <button class="btn btn-primary" type="submit">@lang('Filter requests')</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>@lang('Reference / User')</th>
                    <th>@lang('Request')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Details')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Submitted')</th>
                    <th>@lang('Review')</th>
                </tr>
                </thead>
                <tbody>
                @forelse($transactions as $transaction)
                    @php($metadata = (array)($transaction->metadata ?: []))
                    <tr>
                        <td>
                            <strong>{{ $transaction->reference }}</strong><br>
                            <span>{{ optional($transaction->user)->fullname ?: optional($transaction->user)->username ?: 'User #' . $transaction->user_id }}</span><br>
                            <small class="text-muted">{{ optional($transaction->user)->email }}</small>
                        </td>
                        <td>
                            <span class="text-capitalize">{{ $transaction->direction }}</span><br>
                            <small class="text-muted">
                                {{ $transaction->direction === 'deposit' ? ($metadata['gateway_name'] ?? 'Manual gateway') : ($metadata['payout_method'] ?? 'Manual payout') }}
                            </small>
                        </td>
                        <td>
                            {{ $transaction->currency_code }} {{ number_format((float)$transaction->amount, 2) }}
                            @if($transaction->direction === 'deposit')
                                <br><small class="text-muted">Expected payment: {{ $transaction->currency_code }} {{ number_format((float)($metadata['expected_payment'] ?? $transaction->amount), 2) }}</small>
                            @endif
                        </td>
                        <td>
                            <details>
                                <summary>@lang('Review details')</summary>
                                @if($transaction->direction === 'deposit')
                                    <div class="mt-2">
                                        @foreach(($metadata['submitted_fields'] ?? []) as $field)
                                            <div class="mb-2">
                                                <strong>{{ $field['label'] ?? 'Payment detail' }}:</strong>
                                                @if(($field['type'] ?? '') === 'file' && !empty($field['value']))
                                                    <a href="{{ getFile(config('location.deposit.path') . ltrim($field['value'], '/')) }}" target="_blank" rel="noopener noreferrer">@lang('View uploaded proof')</a>
                                                @else
                                                    <span>{{ $field['value'] ?? '' }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <dl class="mt-2 mb-0">
                                        <dt>@lang('Account name')</dt>
                                        <dd>{{ $metadata['account_name'] ?? '' }}</dd>
                                        <dt>@lang('Account or mobile number')</dt>
                                        <dd>{{ $metadata['account_number'] ?? '' }}</dd>
                                        @if(!empty($metadata['bank_name']))
                                            <dt>@lang('Bank')</dt>
                                            <dd>{{ $metadata['bank_name'] }}</dd>
                                        @endif
                                        @if(!empty($metadata['payment_notes']))
                                            <dt>@lang('Additional details')</dt>
                                            <dd>{{ $metadata['payment_notes'] }}</dd>
                                        @endif
                                    </dl>
                                @endif
                                @if(!empty($transaction->failure_reason))
                                    <div class="text-danger mt-2"><strong>@lang('Failure reason'):</strong> {{ $transaction->failure_reason }}</div>
                                @endif
                                @if(!empty($metadata['admin_review']['feedback']))
                                    <div class="text-muted mt-2"><strong>@lang('Admin note'):</strong> {{ $metadata['admin_review']['feedback'] }}</div>
                                @endif
                            </details>
                        </td>
                        <td>
                            <span class="badge badge-{{ $transaction->status === 'succeeded' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'warning') }}">
                                {{ ucfirst($transaction->status) }}
                            </span>
                        </td>
                        <td>{{ optional($transaction->created_at)->format('Y-m-d H:i') }}</td>
                        <td style="min-width: 230px;">
                            @if(in_array($transaction->status, ['pending', 'processing'], true))
                                <form method="post" action="{{ route('admin.wallet-manual.review', $transaction->id) }}" class="mb-2">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    @if($transaction->direction === 'withdrawal')
                                        <input class="form-control form-control-sm mb-2" name="provider_reference" maxlength="191" placeholder="@lang('Payout transfer reference (optional)')">
                                        <button class="btn btn-sm btn-success" type="submit">@lang('Mark paid')</button>
                                    @else
                                        <input class="form-control form-control-sm mb-2" name="provider_reference" maxlength="191" placeholder="@lang('Payment reference (optional)')">
                                        <button class="btn btn-sm btn-success" type="submit">@lang('Approve deposit')</button>
                                    @endif
                                </form>
                                <form method="post" action="{{ route('admin.wallet-manual.review', $transaction->id) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <textarea class="form-control form-control-sm mb-2" name="feedback" rows="2" maxlength="500" placeholder="@lang('Reason for rejection')" required></textarea>
                                    <button class="btn btn-sm btn-danger" type="submit">@lang('Reject')</button>
                                </form>
                            @else
                                <span class="text-muted">@lang('Reviewed')</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">@lang('No manual wallet requests found.')</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $transactions->links() }}</div>
    </div>
@endsection
