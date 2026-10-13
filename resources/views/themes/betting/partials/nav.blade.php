<!-- navbar -->
<nav class="navbar navbar-expand-md fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{route('home')}}">
            <img src="{{getFile(config('location.logoIcon.path').'logo.png')}}" alt="homepage">
        </a>
        <button
            class="navbar-toggler p-0"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation">
            <svg class="header-menu-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link {{menuActive('home')}}" href="{{route('home')}}">@lang('Home') </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{menuActive('about')}}" href="{{route('about')}}">@lang('About')</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{menuActive('faq')}}" href="{{route('faq')}}">@lang('FAQ')</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{menuActive('blog')}}" href="{{route('blog')}}">@lang('Blog')</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{menuActive('contact')}}" href="{{route('contact')}}">@lang('Contact')</a>
                </li>
            </ul>
        </div>
        <div class="navbar-text">
            <button
                type="button"
                class="theme-toggle"
                onclick="darkMode()"
                aria-label="{{ session()->get('dark-mode') == 'true' ? 'Switch to light mode' : 'Switch to dark mode' }}"
                aria-pressed="{{ session()->get('dark-mode') == 'true' ? 'true' : 'false' }}">
                <svg class="theme-toggle-sun" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path>
                </svg>
                <svg class="theme-toggle-moon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20.2 15.5A8.8 8.8 0 0 1 8.5 3.8 8.9 8.9 0 1 0 20.2 15.5Z"></path>
                </svg>
            </button>
            @auth
                @include($theme.'partials.wallet-currency-switch')
                <div class="dropdown user-dropdown d-inline-block">
                    <button class="dropdown-toggle">
                        <svg class="header-account-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.5"></circle>
                            <path d="M4.5 20c.7-3.5 3.4-5.5 7.5-5.5s6.8 2 7.5 5.5"></path>
                        </svg>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item {{menuActive('user.home')}}" href="{{route('user.home')}}">
                                <i class="fa fa-home"></i>
                                @lang('Dashboard')
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{menuActive('user.wallet')}}" href="{{route('user.wallet')}}">
                                <i class="fal fa-wallet"></i>
                                @lang('Wallet · deposits & withdrawals')
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{route('user.wallet')}}#wallet-transactions">
                                <i class="fal fa-receipt"></i>
                                @lang('Wallet activity')
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{menuActive('user.referral')}}" href="{{route('user.referral')}}">
                                <i class="fal fa-user-friends"></i>
                                @lang('invite friends')
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{menuActive('user.profile')}}" href="{{route('user.profile')}}">
                                <i class="fal fa-user"></i>
                                @lang('personal profile')
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{menuActive('user.betHistory')}}"
                               href="{{route('user.betHistory')}}">
                                <i class="fal fa-history"></i>
                                @lang('bet history')
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i>
                                @lang('Sign Out')
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth
            @guest
                <a href="{{ route('login') }}" class="header-account-link" aria-label="@lang('Sign in')">
                    <svg class="header-account-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="3.5"></circle>
                        <path d="M4.5 20c.7-3.5 3.4-5.5 7.5-5.5s6.8 2 7.5 5.5"></path>
                    </svg>
                </a>
            @endguest

        <!-- notification panel -->
            <div class="notification-panel" id="pushNotificationArea">
                @auth
                    @if(config('basic.push_notification') == 1)
                    <button class="dropdown-toggle" v-cloak>
                        <i class="fal fa-bell"></i>
                        <span v-if="items.length > 0" class="count">@{{ items.length }}</span>
                    </button>
                    <ul class="notification-dropdown">
                        <div class="dropdown-box">
                            <li>
                                <a v-for="(item, index) in items"
                                   @click.prevent="readAt(item.id, item.description.link)"
                                   class="dropdown-item" href="javascript:void(0)">
                                    <i class="fal fa-bell"></i>
                                    <div class="text">
                                        <p v-cloak>@{{ item.formatted_date }}</p>
                                        <span v-cloak v-html="item.description.text"></span>
                                    </div>
                                </a>
                            </li>
                        </div>
                        <div class="clear-all fixed-bottom">
                            <a v-if="items.length > 0" @click.prevent="readAll"
                               href="javascript:void(0)">@lang('Clear all')</a>
                            <a v-if="items.length == 0" href="javascript:void(0)">@lang('You have no notifications')</a>
                        </div>
                    </ul>
                    @endif
                @endauth

                @guest
                <!-- Hide duplicate modal actions on the dedicated auth screens. -->
                @if(!Request::routeIs('login', 'register', 'register.sponsor'))
                    <button
                        class="btn-custom"
                        data-bs-toggle="modal"
                        data-bs-target="#registerModal">
                        @lang('Join')
                    </button>
                    <button
                        class="btn-custom"
                        data-bs-toggle="modal"
                        data-bs-target="#loginModal">
                        @lang('Login')
                    </button>
                @endif
                @endguest
            </div>
        </div>
    </div>
</nav>

@if(in_array(Request::route()->getName(),['home','category','tournament','match']) || Request::routeIs('user.*'))

    <div class="bottom-bar fixed-bottom text-center">
        <a href="{{ route('home') }}" class="text-dark {{ Request::routeIs('home','category','tournament','match') ? 'active' : '' }}" @if(Request::routeIs('home','category','tournament','match')) aria-current="page" @endif>
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="m3.5 10 8.5-7 8.5 7v10.5h-6v-6h-5v6h-6z"></path>
            </svg>
            <span class="nav-label">@lang('Home')</span>
        </a>
        <a href="{{ route('home') }}" class="text-dark"
           onclick="localStorage.setItem('showType', 'live')">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="2.4"></circle>
                <path d="M7.8 7.8a5.9 5.9 0 0 0 0 8.4m8.4-8.4a5.9 5.9 0 0 1 0 8.4M4.6 4.6a10.5 10.5 0 0 0 0 14.8m14.8-14.8a10.5 10.5 0 0 1 0 14.8"></path>
            </svg>
            <span class="nav-label">@lang('Live')</span>
        </a>

        @if(Request::routeIs('home', 'category', 'tournament', 'match'))
            <a href="#rightbar" class="text-dark bet-slip-link"
               aria-label="@lang('Bet Slip')" aria-controls="rightbar" aria-expanded="false"
               onclick="window.openSidebar('rightbar'); return false;">
        @else
            <a href="{{ route('home', ['panel' => 'betslip']) }}" class="text-dark bet-slip-link"
               aria-label="@lang('Open bet slip')" aria-controls="rightbar" aria-expanded="false">
        @endif
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 6h16v4a2 2 0 0 0 0 4v4H4v-4a2 2 0 0 0 0-4z"></path>
                <path d="M12 8v1m0 3v1m0 3v1"></path>
            </svg>
            <span class="nav-label">@lang('Bet Slip')</span>
        </a>

        @auth
            <a href="{{ route('user.betHistory') }}" class="text-dark {{ Request::routeIs('user.betHistory') ? 'active' : '' }}" @if(Request::routeIs('user.betHistory')) aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 3.5 8 5l2-1.5L12 5l2-1.5L16 5l2-1.5v17L16 19l-2 1.5L12 19l-2 1.5L8 19l-2 1.5z"></path>
                    <path d="M9 9h6M9 12h6M9 15h4"></path>
                </svg>
                <span class="nav-label">@lang('Bet History')</span>
            </a>
            <a href="{{ route('user.profile') }}" class="text-dark {{ Request::routeIs('user.profile') ? 'active' : '' }}" @if(Request::routeIs('user.profile')) aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="3.5"></circle>
                    <path d="M4.5 20c.7-3.5 3.4-5.5 7.5-5.5s6.8 2 7.5 5.5"></path>
                </svg>
                <span class="nav-label">@lang('Profile')</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="text-dark">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 3.5 8 5l2-1.5L12 5l2-1.5L16 5l2-1.5v17L16 19l-2 1.5L12 19l-2 1.5L8 19l-2 1.5z"></path>
                    <path d="M9 9h6M9 12h6M9 15h4"></path>
                </svg>
                <span class="nav-label">@lang('Bet History')</span>
            </a>
            <a href="{{ route('login') }}" class="text-dark">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="3.5"></circle>
                    <path d="M4.5 20c.7-3.5 3.4-5.5 7.5-5.5s6.8 2 7.5 5.5"></path>
                </svg>
                <span class="nav-label">@lang('Profile')</span>
            </a>
        @endauth

    </div>
@endif
