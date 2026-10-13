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
                    <button type="button"
                            class="dropdown-toggle"
                            data-toggle="dropdown"
                            aria-label="@lang('Open account menu')"
                            aria-haspopup="true"
                            aria-expanded="false">
                        <svg class="header-account-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.4"></circle>
                            <path d="M5.2 20c.6-3.8 3.1-5.8 6.8-5.8s6.2 2 6.8 5.8"></path>
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
                        <circle cx="12" cy="8" r="3.4"></circle>
                        <path d="M5.2 20c.6-3.8 3.1-5.8 6.8-5.8s6.2 2 6.8 5.8"></path>
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

    <nav class="bottom-bar fixed-bottom text-center" aria-label="@lang('Primary mobile navigation')">
        <a href="{{ route('home') }}" class="text-dark {{ Request::routeIs('home','category','tournament','match') ? 'active' : '' }}" @if(Request::routeIs('home','category','tournament','match')) aria-current="page" @endif>
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="m3.5 10.5 8.5-7 8.5 7M5.5 9v11h13V9M9.5 20v-6h5v6"></path>
            </svg>
            <span class="nav-label">@lang('Home')</span>
        </a>
        <a href="{{ route('home') }}" class="text-dark"
           onclick="localStorage.setItem('showType', 'live')"
           aria-label="@lang('Live matches')">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="2"></circle>
                <path d="M7.8 7.8a6 6 0 0 0 0 8.4m8.4-8.4a6 6 0 0 1 0 8.4M4.8 4.8a10.2 10.2 0 0 0 0 14.4m14.4-14.4a10.2 10.2 0 0 1 0 14.4"></path>
            </svg>
            <span class="nav-label">@lang('Live')</span>
        </a>

        <a href="{{ route('home', ['panel' => 'betslip']) }}" class="text-dark bet-slip-link"
           aria-label="@lang('Open bet slip')" aria-controls="rightbar" aria-expanded="false"
           onclick="if (window.openSidebar && window.openSidebar('rightbar')) return false;">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 6.5h16v3a2.5 2.5 0 0 0 0 5v3H4v-3a2.5 2.5 0 0 0 0-5z"></path>
                <path d="M12 8.5v1.8m0 1.8v1.8"></path>
            </svg>
            <span class="nav-label">@lang('Bet Slip')</span>
        </a>

        @auth
            <a href="{{ route('user.betHistory') }}" class="text-dark {{ Request::routeIs('user.betHistory') ? 'active' : '' }}" @if(Request::routeIs('user.betHistory')) aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 3.5h12v17l-2-1.4-2 1.4-2-1.4-2 1.4-2-1.4-2 1.4z"></path>
                    <path d="M9 8h6m-6 3.5h6m-6 3.5h4"></path>
                </svg>
                <span class="nav-label">@lang('Bet History')</span>
            </a>
            <a href="{{ route('user.profile') }}" class="text-dark {{ Request::routeIs('user.profile') ? 'active' : '' }}" @if(Request::routeIs('user.profile')) aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="3.4"></circle>
                    <path d="M5.2 20c.6-3.8 3.1-5.8 6.8-5.8s6.2 2 6.8 5.8"></path>
                </svg>
                <span class="nav-label">@lang('Profile')</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="text-dark">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="3.4"></circle>
                    <path d="M5.2 20c.6-3.8 3.1-5.8 6.8-5.8s6.2 2 6.8 5.8"></path>
                </svg>
                <span class="nav-label">@lang('Profile')</span>
            </a>
        @endauth

    </nav>
@endif
