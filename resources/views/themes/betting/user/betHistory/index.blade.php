@extends($theme.'layouts.user')

@section('title')
    @lang('Bet History')
@endsection

@section('content')
    <section class="bet-history-page" aria-labelledby="bet-history-heading">
        <header class="bet-history-heading">
            <div class="bet-history-heading-copy">
                <span class="bet-history-kicker"><i class="fas fa-flag-checkered" aria-hidden="true"></i> @lang('Match day, at a glance')</span>
                <h1 id="bet-history-heading">@lang('Bet History')</h1>
                <p>@lang('Track every stake, potential return and settled result in one place.')</p>
            </div>
            @unless($betInvests->isEmpty())
                <div class="bet-history-page-count" aria-label="@lang('Wager history')">
                    <span class="bet-history-count-number">{{ $betInvests->total() }}</span>
                    <span class="bet-history-count-label">@lang('Wagers')</span>
                </div>
            @endunless
        </header>

        <div class="card bet-history-card">
            <div class="card-body bet-history-card-body">
                @if($betInvests->isEmpty())
                    <div class="bet-history-empty text-center">
                        <span class="bet-history-empty-mark" aria-hidden="true"><i class="fal fa-receipt"></i></span>
                        <span class="bet-history-kicker">@lang('Your match-day record')</span>
                        <h2>@lang('No bet history yet')</h2>
                        <p>@lang('Your placed bets will appear here, including their status and results.')</p>
                        <a href="{{ route('home') }}" class="btn-custom"><i class="fas fa-arrow-right" aria-hidden="true"></i> @lang('Browse matches')</a>
                    </div>
                @else
                    <div class="bet-history-table-wrap">
                        <div class="bet-history-table-intro">
                            <div>
                                <h2>@lang('Your wagers')</h2>
                                <p>@lang('Most recent bets appear first')</p>
                            </div>
                            <span class="bet-history-currency-note"><i class="fas fa-wallet" aria-hidden="true"></i> @lang('Amounts in your wallet currency')</span>
                        </div>
                        <div class="table-parent table-responsive bet-history-table m-0">
                            <table class="table table-striped" id="service-table">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('SL No.')</th>
                                        <th scope="col">@lang('Invest Amount')</th>
                                        <th scope="col">@lang('Return Amount')</th>
                                        <th scope="col">@lang('Charge')</th>
                                        <th scope="col">@lang('Ratio')</th>
                                        <th scope="col" class="text-center">@lang('Status')</th>
                                        <th scope="col" class="text-center">@lang('Information')</th>
                                        <th scope="col">@lang('Time')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($betInvests as $key => $item)
                                        @php
                                            $currency = $item->wallet_currency ?: 'USD';
                                            $currencySymbol = data_get(config('currencies.supported'), $currency.'.symbol', $currency);
                                        @endphp
                                        <tr>
                                            <td data-label="@lang('SL No.')"><span class="bet-history-serial">{{ $betInvests->firstItem() + $key }}</span></td>
                                            <td data-label="@lang('Invest Amount')">
                                                <span class="bet-history-money bet-history-stake">{{ $currencySymbol }}{{ $item->invest_amount }}</span>
                                                <span class="bet-history-currency">{{ $currency }}</span>
                                            </td>
                                            <td data-label="@lang('Return Amount')">
                                                <span class="bet-history-money">{{ $currencySymbol }}{{ $item->return_amount }}</span>
                                                <span class="bet-history-currency">{{ $currency }}</span>
                                            </td>
                                            <td data-label="@lang('Charge')">
                                                <span class="bet-history-money bet-history-charge">{{ $currencySymbol }}{{ $item->charge }}</span>
                                                <span class="bet-history-currency">{{ $currency }}</span>
                                            </td>
                                            <td data-label="@lang('Ratio')"><span class="bet-history-ratio">{{ $item->ratio }}</span></td>
                                            <td data-label="@lang('Status')" class="text-center">
                                                @if($item->status == 0)
                                                    <span class="bet-status bet-status-processing"><span class="bet-status-dot" aria-hidden="true"></span>@lang('Processing')</span>
                                                @elseif($item->status == 1)
                                                    <span class="bet-status bet-status-win"><span class="bet-status-dot" aria-hidden="true"></span>@lang('Win')</span>
                                                @elseif($item->status == -1)
                                                    <span class="bet-status bet-status-loss"><span class="bet-status-dot" aria-hidden="true"></span>@lang('Loss')</span>
                                                @elseif($item->status == 2)
                                                    <span class="bet-status bet-status-refund"><span class="bet-status-dot" aria-hidden="true"></span>@lang('Refund')</span>
                                                @endif
                                            </td>
                                            <td data-label="@lang('Information')" class="text-center">
                                                <button type="button"
                                                        data-resource="{{ $item->betInvestLog }}"
                                                        data-bs-target="#investLogList"
                                                        data-bs-toggle="modal"
                                                        class="bet-history-info investLogList"
                                                        aria-label="@lang('View bet information')">
                                                    <i class="fas fa-info" aria-hidden="true"></i>
                                                    <span>@lang('Details')</span>
                                                </button>
                                            </td>
                                            <td data-label="@lang('Time')">
                                                <time class="bet-history-time" datetime="{{ $item->created_at }}">{{ dateTime($item->created_at, 'd M Y h:i A') }}</time>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="bet-history-pagination">
                            {{ $betInvests->appends($_GET)->links($theme.'partials.pagination') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <div class="modal fade bet-history-modal" id="investLogList" tabindex="-1" role="dialog" aria-labelledby="investLogTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-custom-content">
                <div class="modal-header modal-colored-header">
                    <div class="bet-history-modal-heading">
                        <span class="bet-history-kicker"><i class="fas fa-list-ul" aria-hidden="true"></i> @lang('Wager breakdown')</span>
                        <h2 class="modal-title service-title" id="investLogTitle">@lang('Bet Information')</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('Close')"></button>
                </div>
                <div class="modal-body">
                    <div class="bet-history-details-intro">
                        <i class="fas fa-circle-info" aria-hidden="true"></i>
                        <span>@lang('Each selection and its settled outcome, shown separately.')</span>
                    </div>
                    <div class="bet-history-details-scroll">
                        <table class="table table-striped bet-history-details-table">
                            <thead>
                                <tr>
                                    <th scope="col">@lang('#')</th>
                                    <th scope="col">@lang('Match Name')</th>
                                    <th scope="col">@lang('Category Name')</th>
                                    <th scope="col">@lang('Tournament Name')</th>
                                    <th scope="col">@lang('Question Name')</th>
                                    <th scope="col">@lang('Option Name')</th>
                                    <th scope="col">@lang('Ratio')</th>
                                    <th scope="col">@lang('Result')</th>
                                </tr>
                            </thead>
                            <tbody class="result-body"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-custom bet-history-close" data-bs-dismiss="modal">
                        <span>@lang('Close')</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <script src="{{ asset('assets/admin/js/fontawesome/fontawesomepro.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        (function () {
            const labels = {
                processing: @json(__('Processing')),
                win: @json(__('Win')),
                loss: @json(__('Lose')),
                refunded: @json(__('Refunded')),
                noData: @json(__('No Data Found')),
                match: @json(__('Match Name')),
                category: @json(__('Category Name')),
                tournament: @json(__('Tournament Name')),
                question: @json(__('Question Name')),
                option: @json(__('Option Name')),
                ratio: @json(__('Ratio')),
                result: @json(__('Result')),
                number: @json(__('#'))
            };

            const resultBody = document.querySelector('#investLogList .result-body');
            if (!resultBody) return;

            function makeCell(label, value) {
                const cell = document.createElement('td');
                cell.dataset.label = label;
                cell.textContent = value == null ? '' : String(value);
                return cell;
            }

            function resultBadge(status) {
                const states = {
                    '0': { label: labels.processing, className: 'bet-status-processing' },
                    '2': { label: labels.win, className: 'bet-status-win' },
                    '-2': { label: labels.loss, className: 'bet-status-loss' },
                    '3': { label: labels.refunded, className: 'bet-status-refund' }
                };
                const state = states[String(status)];
                if (!state) return document.createTextNode('');

                const badge = document.createElement('span');
                badge.className = 'bet-status ' + state.className;
                const dot = document.createElement('span');
                dot.className = 'bet-status-dot';
                dot.setAttribute('aria-hidden', 'true');
                badge.append(dot, document.createTextNode(state.label));
                return badge;
            }

            document.addEventListener('click', function (event) {
                const trigger = event.target.closest('.investLogList');
                if (!trigger) return;

                let entries = [];
                try {
                    const parsed = JSON.parse(trigger.getAttribute('data-resource') || '[]');
                    entries = Array.isArray(parsed) ? parsed : [];
                } catch (error) {
                    entries = [];
                }

                resultBody.replaceChildren();
                if (!entries.length) {
                    const row = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = 8;
                    cell.className = 'bet-history-no-details';
                    cell.textContent = labels.noData;
                    row.appendChild(cell);
                    resultBody.appendChild(row);
                    return;
                }

                entries.forEach(function (entry, index) {
                    const row = document.createElement('tr');
                    row.appendChild(makeCell(labels.number, index + 1));
                    row.appendChild(makeCell(labels.match, entry.match_name));
                    row.appendChild(makeCell(labels.category, entry.category_name));
                    row.appendChild(makeCell(labels.tournament, entry.tournament_name));
                    row.appendChild(makeCell(labels.question, entry.question_name));
                    row.appendChild(makeCell(labels.option, entry.option_name));
                    row.appendChild(makeCell(labels.ratio, entry.ratio));
                    const resultCell = document.createElement('td');
                    resultCell.dataset.label = labels.result;
                    resultCell.appendChild(resultBadge(entry.status));
                    row.appendChild(resultCell);
                    resultBody.appendChild(row);
                });
            });
        })();
    </script>
@endpush
