@extends($theme.'layouts.app')
@section('title',trans('Home'))

@section('content')

    <!-- wrapper -->
    <div class="wrapper" id="getMatchList" v-cloak>
        <!-- leftbar -->
        <div class="leftbar" id="leftbar">
            <div class="px-1 mt-2 d-lg-none">
                <button
                    class="remove-class-btn light btn-custom"
                    onclick="removeClass('leftbar')"
                >
                    <i class="fal fa-chevron-left"></i> @lang('Back')
                </button>
            </div>
            <div class="top p-1 d-flex">
                <button @click="liveUpComing('live')" type="button" :class="{light: (showType == 'upcoming')}"  class="btn-custom me-1">
                    <svg class="feed-tab-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="2.2"></circle>
                        <path d="M8.2 8.2a5.4 5.4 0 0 0 0 7.6m7.6-7.6a5.4 5.4 0 0 1 0 7.6M5.4 5.4a9.3 9.3 0 0 0 0 13.2m13.2-13.2a9.3 9.3 0 0 1 0 13.2"></path>
                    </svg>
                    @lang('Live')
                </button>
                <button @click="liveUpComing('upcoming')" type="button" :class="{light: (showType == 'live')}"  class="btn-custom ">
                    <svg class="feed-tab-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="4" y="5" width="16" height="16" rx="2"></rect>
                        <path d="M8 3v4m8-4v4M4 10h16M8 14h3"></path>
                    </svg>
                    @lang('Upcoming')
                </button>
            </div>
            @include($theme.'partials.home.leftMenu')

            <div class="bottom p-1">
                <a href="{{route('betResult')}}" class="btn-custom light w-100">@lang('results')</a>
            </div>
        </div>

        @include($theme.'partials.home.rightbar')

        <!-- contents -->
        <div class="content">
            @include($theme.'partials.home.navbar')

            @if(!Request::routeIs('match'))
                <nav class="match-competition-tabs" role="tablist" aria-label="@lang('Match sections')">
                    <button type="button" role="tab"
                            @click="selectCompetitionTab('highlights')"
                            :class="{active: showType == 'live'}"
                            :aria-selected="showType == 'live'">@lang('Highlights')</button>
                    <button type="button" role="tab"
                            @click="selectCompetitionTab('upcoming')"
                            :class="{active: showType == 'upcoming'}"
                            :aria-selected="showType == 'upcoming'">@lang('Upcoming')</button>
                    <button type="button" role="tab" class="is-unavailable" disabled title="@lang('This section is not connected to the current match feed.')">@lang('Countries')</button>
                    <button type="button" role="tab" class="is-unavailable" disabled title="@lang('This section is not connected to the current match feed.')">@lang('Zoom Soccer')</button>
                    <button type="button" role="tab" class="is-unavailable" disabled title="@lang('This section is not connected to the current match feed.')">@lang('Jackpots')</button>
                    <button type="button" role="tab" class="is-unavailable" disabled title="@lang('This section is not connected to the current match feed.')">@lang('New')</button>
                    <button type="button" role="tab" class="is-unavailable" disabled title="@lang('This section is not connected to the current match feed.')">@lang('Turbo')</button>
                </nav>

                <div class="match-filter-toolbar" role="group" aria-label="@lang('Match filters')">
                    <div class="match-filter-menu">
                        <button type="button" class="match-filter-button"
                                @click="toggleFeedMenu('filters')"
                                :aria-expanded="openFeedMenu == 'filters'">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2m4 0h10M14 4v6M8 14v6"></path><circle cx="16" cy="7" r="2"></circle><circle cx="8" cy="17" r="2"></circle></svg>
                            <span>@lang('Filters')</span>
                            <span class="match-filter-chevron" aria-hidden="true"></span>
                        </button>
                        <div v-if="openFeedMenu == 'filters'" class="match-filter-popover">
                            <label class="match-filter-check">
                                <input type="checkbox" v-model="onlyAvailableOdds">
                                <span>@lang('Only show matches with available odds')</span>
                            </label>
                            <button type="button" class="match-filter-reset" @click="resetFeedFilters">@lang('Reset filters')</button>
                        </div>
                    </div>

                    <div class="match-filter-menu">
                        <button type="button" class="match-filter-button"
                                @click="toggleFeedMenu('date')"
                                :aria-expanded="openFeedMenu == 'date'">
                            <span>@{{ dateFilter == 'today' ? '@lang('Today')' : '@lang('All dates')' }}</span>
                            <span class="match-filter-chevron" aria-hidden="true"></span>
                        </button>
                        <div v-if="openFeedMenu == 'date'" class="match-filter-popover">
                            <button type="button" @click="setDateFilter('today')">@lang('Today')</button>
                            <button type="button" @click="setDateFilter('all')">@lang('All dates')</button>
                        </div>
                    </div>

                    <div class="match-filter-menu">
                        <button type="button" class="match-filter-button"
                                @click="toggleFeedMenu('group')"
                                :aria-expanded="openFeedMenu == 'group'">
                            <span>@{{ marketGroupFilter == 'highlights' ? '@lang('Highlights')' : '@lang('All matches')' }}</span>
                            <span class="match-filter-chevron" aria-hidden="true"></span>
                        </button>
                        <div v-if="openFeedMenu == 'group'" class="match-filter-popover">
                            <button type="button" @click="setMarketGroupFilter('highlights')">@lang('Highlights')</button>
                            <button type="button" @click="setMarketGroupFilter('all')">@lang('All matches')</button>
                        </div>
                    </div>

                    <div class="match-filter-menu">
                        <button type="button" class="match-filter-button"
                                @click="toggleFeedMenu('market')"
                                :aria-expanded="openFeedMenu == 'market'">
                            <span>@{{ oddsMarketFilter == '1x2' ? '1x2' : '@lang('All markets')' }}</span>
                            <span class="match-filter-chevron" aria-hidden="true"></span>
                        </button>
                        <div v-if="openFeedMenu == 'market'" class="match-filter-popover">
                            <button type="button" @click="setOddsMarketFilter('1x2')">1x2</button>
                            <button type="button" @click="setOddsMarketFilter('all')">@lang('All markets')</button>
                        </div>
                    </div>
                </div>
            @else
                <div class="match-feed-controls">
                    <h1 class="feed-title">@lang('Match markets')</h1>
                    <div class="feed-switch" role="group" aria-label="@lang('Match timing')">
                        <button type="button" @click="liveUpComing('live')" :class="{active: showType == 'live'}" :aria-pressed="showType == 'live'">@lang('Live')</button>
                        <button type="button" @click="liveUpComing('upcoming')" :class="{active: showType == 'upcoming'}" :aria-pressed="showType == 'upcoming'">@lang('Upcoming')</button>
                    </div>
                </div>
            @endif

            @include($theme.'partials.home.slider')
            @if(Request::routeIs('match'))
                @include($theme.'partials.home.match')
            @else
                <div class="match-feed-columns" aria-hidden="true">
                    <span>@lang('Teams')</span>
                    <div><span>1</span><span>X</span><span>2</span></div>
                </div>
                @include($theme.'partials.home.content')
            @endif

        </div>

    </div>
@endsection

@push('script')

    @php
        $segments = request()->segments();
        $last  = end($segments);

    @endphp

    @php
        $betCurrency = auth()->check() ? strtoupper(auth()->user()->wallet_currency ?: 'USD') : strtoupper(config('basic.currency', 'USD'));
        $betCurrencySymbol = auth()->check()
            ? app(\App\Services\CurrencyService::class)->symbol($betCurrency)
            : config('basic.currency_symbol');
        $minimumBetForCurrency = config('basic.minimum_bet');
        if (auth()->check()) {
            try {
                $minimumBetForCurrency = app(\App\Services\CurrencyService::class)->convert(config('basic.minimum_bet'), 'USD', $betCurrency);
            } catch (\Throwable $exception) {
                // The betting endpoint returns an explicit error until the missing exchange rate is configured.
            }
        }
    @endphp

    <script>
        let getMatchList = new Vue({
            el: "#getMatchList",
            data: {

                loaded: true,
                currency_symbol: "{{ $betCurrencySymbol }}",
                currency: "{{ $betCurrency }}",
                minimum_bet: "{{ $minimumBetForCurrency }}",
                allSports_filter: [],
                upcoming_filter: [],

                selectedData: {},
                betSlip: [],
                totalOdds: 0,
                minimumAmo: 1,
                return_amount: 0,
                win_charge: "{{config('basic.win_charge')}}",
                form: {
                    amount: ''
                },
                showType: 'live',
                openFeedMenu: null,
                dateFilter: 'today',
                marketGroupFilter: 'highlights',
                oddsMarketFilter: '1x2',
                onlyAvailableOdds: false
            },
            mounted() {
                var showType = localStorage.getItem('showType');
                if (showType == null) {
                    localStorage.setItem("showType", 'live');
                }
                this.showType = localStorage.getItem("showType")

                this.getMatches();
                this.getSlip();
                this.getEvents();

                if (new URLSearchParams(window.location.search).get('panel') === 'betslip') {
                    this.$nextTick(function () {
                        var panel = document.getElementById('rightbar');
                        if (panel) {
                            panel.classList.add('active');
                            document.querySelectorAll('[aria-controls="rightbar"]').forEach(function (trigger) {
                                trigger.setAttribute('aria-expanded', 'true');
                            });
                        }
                    });
                }

            },
            methods: {
                async getMatches() {
                    var _this = this;
                    var _segment = "{{Request::segment(1)}}"
                    var routeName = "{{Request::route()->getName()}}"
                    var $lastSegment = "{{$last}}"

                    var $url = '{{route('allSports')}}';

                    if (routeName == 'category') {
                        $url = '{{route('allSports')}}?categoryId=' + $lastSegment;
                    }
                    if (routeName == 'tournament') {
                        $url = '{{route('allSports')}}?tournamentId=' + $lastSegment;
                    }

                    if (routeName == 'match') {
                        $url = '{{route('allSports')}}?matchId=' + $lastSegment;
                    }


                    await axios.get($url)
                        .then(function (response) {
                            _this.allSports_filter = response.data.liveList;
                            _this.upcoming_filter = response.data.upcomingList;
                        })
                        .catch(function (error) {
                            console.log(error);
                        })
                },

                addToSlip(data) {
                    if (data.is_unlock_question == 1 || data.is_unlock_match == 1) {
                        return 0;
                    }
                    var _this = this;
                    const index = _this.betSlip.findIndex(object => object.match_id === data.match_id);
                    if (index === -1) {
                        _this.betSlip.push(data);
                        Notiflix.Notify.Success("Added to Bet slip");
                    } else {
                        var result = _this.betSlip.map(function (obj) {
                            if (obj.match_id == data.match_id) {
                                obj = data
                            }
                            return obj
                        });
                        _this.betSlip = result

                        Notiflix.Notify.Info("Bet slip has been updated");
                    }
                    _this.totalOdds = _this.oddsCalc(_this.betSlip)
                    localStorage.setItem("newBetSlip", JSON.stringify(_this.betSlip));
                },
                getSlip() {
                    try {
                        var selectData = JSON.parse(localStorage.getItem('newBetSlip') || '[]');
                        this.betSlip = Array.isArray(selectData) ? selectData : [];
                    } catch (error) {
                        this.betSlip = [];
                    }
                    this.totalOdds = this.oddsCalc(this.betSlip)
                },

                removeItem(obj) {
                    var _this = this;
                    _this.betSlip = _this.betSlip.filter(function (item) {
                        return item.id !== obj.id;
                    });
                    _this.totalOdds = _this.oddsCalc(_this.betSlip)

                    localStorage.setItem("newBetSlip", JSON.stringify(_this.betSlip));
                },

                oddsCalc(obj) {
                    var ratio = 1;
                    for (var property in obj) {
                        ratio *= parseFloat(obj[property].ratio);
                    }
                    return ratio.toFixed(3);
                },

                decrement() {
                    if (this.form.amount > this.minimumAmo) {
                        this.form.amount--;
                        this.return_amount = parseFloat(this.form.amount * this.totalOdds).toFixed(3);

                        return 0;
                    }
                    return 1;
                },
                increment() {
                    this.form.amount++;
                    this.return_amount = parseFloat(this.form.amount * this.totalOdds).toFixed(3);
                    return 0;
                },
                calc(val) {
                    if (isNaN(val)) {
                        val = 0
                    }
                    if (0 >= val) {
                        val = 0;
                    }
                    this.return_amount = parseFloat(val * this.totalOdds).toFixed(2);
                },

                goMatch(item) {
                    var $url = '{{ route("match", [":match_name",":match_id"]) }}';
                    $url = $url.replace(':match_name', item.slug);
                    $url = $url.replace(':match_id', item.id);
                    window.location.href = $url;
                },

                getEvents() {
                    let _this = this;
                    // Pusher.logToConsole = true;
                    let pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
                        encrypted: true,
                        cluster: "{{ env('PUSHER_APP_CLUSTER') }}"
                    });
                    var channel = pusher.subscribe('match-notification');

                    channel.bind('App\\Events\\MatchNotification', function (data) {
                        console.log(data)
                        if (data && data.type == 'Edit') {
                            _this.updateEventData(data)
                        } else if (data && data.type != 'Edit') {
                            _this.enlistedEventData(data)
                        }
                    });

                },
                updateEventData(data) {
                    var _this = this;
                    var list = _this.allSports_filter;
                    const result = list.map(function (obj) {
                        if (obj.id == data.match.id) {
                            obj = data.match
                        }
                        return obj
                    });
                    _this.allSports_filter = result


                    var list2 = _this.upcoming_filter;


                    const upcomingResult = list2.map(function (obj) {
                        if (obj.id == data.match.id) {
                            obj = data.match
                        }
                        return obj
                    });

                    _this.upcoming_filter = upcomingResult
                },
                enlistedEventData(data) {
                    var _this = this;
                    if (data && data.type == 'Enlisted') {
                        var list = _this.allSports_filter;
                        list.push(data.match);
                    }
                    if (data && data.type == 'UpcomingList') {
                        var upcomingList = _this.upcoming_filter;
                        upcomingList.push(data.match);
                    }
                },
                betPlace() {
                    var _this = this;
                    var authCheck = "{{auth()->check()}}"
                    if (authCheck !== '1') {
                        window.location.href = "{{route('login')}}"
                        return 0;
                    }
                    if (_this.betSlip.length == 0) {
                        Notiflix.Notify.Failure("Please make a bet slip");
                        return 0
                    }
                    if (_this.form.amount == '') {
                        Notiflix.Notify.Failure("Please put a amount");
                        return 0
                    }
                    if (0 > (_this.form.amount)) {
                        Notiflix.Notify.Failure("Please put a valid amount");
                        return 0
                    }
                    if (parseInt(_this.minimum_bet) > parseInt(_this.form.amount)) {
                        Notiflix.Notify.Failure("Minimum Bet " + _this.minimum_bet + " " + _this.currency);
                        return 0
                    }
                    axios.post('{{route('user.betSlip')}}', {
                        amount: _this.form.amount,
                        activeSlip: _this.betSlip,
                    })
                        .then(function (response) {
                            if (response.data.errors) {
                                for (err in response.data.errors) {
                                    let error = response.data.errors[err][0]
                                    Notiflix.Notify.Failure("" + error);
                                }
                                return 0;
                            }
                            if (response.data.newSlipMessage) {
                                Notiflix.Notify.Warning("" + response.data.newSlipMessage);
                                var newSlip = response.data.newSlip;
                                var unlisted = _this.getDifference(_this.betSlip, newSlip);
                                const newUnlisted = unlisted.map(function (obj) {
                                    obj.is_unlock_match = 1;
                                    obj.is_unlock_question = 1;
                                    return obj
                                });
                                _this.betSlip = _this.betSlip.concat(
                                    Array.isArray(newSlip) ? newSlip : [],
                                    newUnlisted
                                );
                                _this.totalOdds = _this.oddsCalc(_this.betSlip);
                                localStorage.setItem("newBetSlip", JSON.stringify(_this.betSlip));
                                return 0;
                            }

                            if (response.data.success) {
                                _this.betSlip = [];
                                localStorage.setItem("newBetSlip", JSON.stringify(_this.betSlip));
                                Notiflix.Notify.Success("Your bet has place successfully");

                                return 0;
                            }

                        })
                        .catch(function (err) {
                            var message = err && err.response && err.response.data && err.response.data.message;
                            Notiflix.Notify.Failure(message || "We could not place your bet. Check your balance and try again.");
                        });
                },

                getDifference(array1, array2) {
                    return array1.filter(object1 => {
                        return !array2.some(object2 => {
                            return object1.id === object2.id;
                        });
                    });
                },
                visibleMatches(items) {
                    var matches = Array.isArray(items) ? items : [];
                    var today = new Date().toDateString();
                    var _this = this;

                    return matches.filter(function (match) {
                        if (_this.dateFilter == 'today') {
                            var startDate = new Date(match.start_date);
                            if (isNaN(startDate.getTime()) || startDate.toDateString() !== today) {
                                return false;
                            }
                        }

                        var questions = Array.isArray(match.questions) ? match.questions : [];
                        var hasOdds = questions.some(function (question) {
                            return Array.isArray(question.options) && question.options.length > 0;
                        });
                        if (_this.marketGroupFilter == 'highlights' && !hasOdds) {
                            return false;
                        }
                        if (_this.onlyAvailableOdds) {
                            var hasUnlockedOdds = questions.some(function (question) {
                                return (question.options || []).some(function (option) {
                                    return option.is_unlock_question != 1 && option.is_unlock_match != 1;
                                });
                            });
                            if (!hasUnlockedOdds) {
                                return false;
                            }
                        }
                        return true;
                    });
                },
                visibleQuestions(items) {
                    var questions = Array.isArray(items) ? items : [];
                    if (this.oddsMarketFilter == '1x2') {
                        var matchResult = questions.find(function (question) {
                            return /1\\s*x\\s*2|match\\s*(winner|result)|full\\s*time\\s*result/i.test(question.name || '');
                        });
                        return matchResult ? [matchResult] : questions.slice(0, 1);
                    }
                    return questions.slice(0, 3);
                },
                slicedArray(items) {
                    return this.visibleQuestions(items)[0] || { options: [] };
                },
                toggleFeedMenu(menu) {
                    this.openFeedMenu = this.openFeedMenu == menu ? null : menu;
                },
                setDateFilter(filter) {
                    this.dateFilter = filter;
                    this.openFeedMenu = null;
                },
                setMarketGroupFilter(filter) {
                    this.marketGroupFilter = filter;
                    this.openFeedMenu = null;
                },
                setOddsMarketFilter(filter) {
                    this.oddsMarketFilter = filter;
                    this.openFeedMenu = null;
                },
                resetFeedFilters() {
                    this.dateFilter = 'today';
                    this.marketGroupFilter = 'highlights';
                    this.oddsMarketFilter = '1x2';
                    this.onlyAvailableOdds = false;
                    this.openFeedMenu = null;
                },
                selectCompetitionTab(tab) {
                    this.liveUpComing(tab == 'upcoming' ? 'upcoming' : 'live');
                },
                liveUpComing(type){
                    localStorage.setItem("showType", type);
                    this.showType = type;
                    this.openFeedMenu = null;
                }


            }
        });

    </script>
@endpush
