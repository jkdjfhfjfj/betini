<!-- navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo e(route('home')); ?>">
            <img src="<?php echo e(getFile(config('location.logoIcon.path').'logo.png')); ?>" alt="homepage">
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
                    <a class="nav-link <?php echo e(menuActive('home')); ?>" href="<?php echo e(route('home')); ?>"><?php echo app('translator')->get('Home'); ?> </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(menuActive('about')); ?>" href="<?php echo e(route('about')); ?>"><?php echo app('translator')->get('About'); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(menuActive('faq')); ?>" href="<?php echo e(route('faq')); ?>"><?php echo app('translator')->get('FAQ'); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(menuActive('blog')); ?>" href="<?php echo e(route('blog')); ?>"><?php echo app('translator')->get('Blog'); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(menuActive('contact')); ?>" href="<?php echo e(route('contact')); ?>"><?php echo app('translator')->get('Contact'); ?></a>
                </li>
            </ul>
        </div>
        <div class="navbar-text">
            <button
                type="button"
                class="theme-toggle"
                onclick="darkMode()"
                aria-label="<?php echo e(session()->get('dark-mode') == 'true' ? 'Switch to light mode' : 'Switch to dark mode'); ?>"
                aria-pressed="<?php echo e(session()->get('dark-mode') == 'true' ? 'true' : 'false'); ?>">
                <svg class="theme-toggle-sun" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path>
                </svg>
                <svg class="theme-toggle-moon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20.2 15.5A8.8 8.8 0 0 1 8.5 3.8 8.9 8.9 0 1 0 20.2 15.5Z"></path>
                </svg>
            </button>
            <?php if(auth()->guard()->check()): ?>
                <?php echo $__env->make($theme.'partials.wallet-currency-switch', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <div class="dropdown user-dropdown d-inline-block">
                    <button type="button"
                            class="dropdown-toggle"
                            data-toggle="dropdown"
                            aria-label="<?php echo app('translator')->get('Open account menu'); ?>"
                            aria-haspopup="true"
                            aria-expanded="false">
                        <svg class="header-account-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="12" cy="8" r="3.4"></circle>
                            <path d="M5.2 20c.6-3.8 3.1-5.8 6.8-5.8s6.2 2 6.8 5.8"></path>
                        </svg>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item <?php echo e(menuActive('user.home')); ?>" href="<?php echo e(route('user.home')); ?>">
                                <i class="fa fa-home"></i>
                                <?php echo app('translator')->get('Dashboard'); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php echo e(menuActive('user.wallet')); ?>" href="<?php echo e(route('user.wallet')); ?>">
                                <i class="fal fa-wallet"></i>
                                <?php echo app('translator')->get('Wallet · deposits & withdrawals'); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('user.wallet')); ?>#wallet-transactions">
                                <i class="fal fa-receipt"></i>
                                <?php echo app('translator')->get('Wallet activity'); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php echo e(menuActive('user.referral')); ?>" href="<?php echo e(route('user.referral')); ?>">
                                <i class="fal fa-user-friends"></i>
                                <?php echo app('translator')->get('invite friends'); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php echo e(menuActive('user.profile')); ?>" href="<?php echo e(route('user.profile')); ?>">
                                <i class="fal fa-user"></i>
                                <?php echo app('translator')->get('personal profile'); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php echo e(menuActive('user.betHistory')); ?>"
                               href="<?php echo e(route('user.betHistory')); ?>">
                                <i class="fal fa-history"></i>
                                <?php echo app('translator')->get('bet history'); ?>
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="<?php echo e(route('logout')); ?>"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i>
                                <?php echo app('translator')->get('Sign Out'); ?>
                            </a>
                            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                                <?php echo csrf_field(); ?>
                            </form>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if(auth()->guard()->guest()): ?>
                <a href="<?php echo e(route('login')); ?>" class="header-account-link" aria-label="<?php echo app('translator')->get('Sign in'); ?>">
                    <svg class="header-account-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="3.4"></circle>
                        <path d="M5.2 20c.6-3.8 3.1-5.8 6.8-5.8s6.2 2 6.8 5.8"></path>
                    </svg>
                </a>
            <?php endif; ?>

        <!-- notification panel -->
            <div class="notification-panel" id="pushNotificationArea">
                <?php if(auth()->guard()->check()): ?>
                    <?php if(config('basic.push_notification') == 1): ?>
                    <button class="dropdown-toggle" v-cloak>
                        <i class="fal fa-bell"></i>
                        <span v-if="items.length > 0" class="count">{{ items.length }}</span>
                    </button>
                    <ul class="notification-dropdown">
                        <div class="dropdown-box">
                            <li>
                                <a v-for="(item, index) in items"
                                   @click.prevent="readAt(item.id, item.description.link)"
                                   class="dropdown-item" href="javascript:void(0)">
                                    <i class="fal fa-bell"></i>
                                    <div class="text">
                                        <p v-cloak>{{ item.formatted_date }}</p>
                                        <span v-cloak v-html="item.description.text"></span>
                                    </div>
                                </a>
                            </li>
                        </div>
                        <div class="clear-all fixed-bottom">
                            <a v-if="items.length > 0" @click.prevent="readAll"
                               href="javascript:void(0)"><?php echo app('translator')->get('Clear all'); ?></a>
                            <a v-if="items.length == 0" href="javascript:void(0)"><?php echo app('translator')->get('You have no notifications'); ?></a>
                        </div>
                    </ul>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if(auth()->guard()->guest()): ?>
                <!-- Hide duplicate modal actions on the dedicated auth screens. -->
                <?php if(!Request::routeIs('login', 'register', 'register.sponsor')): ?>
                    <button
                        class="btn-custom"
                        data-bs-toggle="modal"
                        data-bs-target="#registerModal">
                        <?php echo app('translator')->get('Join'); ?>
                    </button>
                    <button
                        class="btn-custom"
                        data-bs-toggle="modal"
                        data-bs-target="#loginModal">
                        <?php echo app('translator')->get('Login'); ?>
                    </button>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if(in_array(Request::route()->getName(),['home','category','tournament','match']) || Request::routeIs('user.*')): ?>

    <nav class="bottom-bar fixed-bottom text-center" aria-label="<?php echo app('translator')->get('Primary mobile navigation'); ?>">
        <a href="<?php echo e(route('home')); ?>" class="text-dark <?php echo e(Request::routeIs('home','category','tournament','match') ? 'active' : ''); ?>" <?php if(Request::routeIs('home','category','tournament','match')): ?> aria-current="page" <?php endif; ?>>
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="m3.5 10.5 8.5-7 8.5 7M5.5 9v11h13V9M9.5 20v-6h5v6"></path>
            </svg>
            <span class="nav-label"><?php echo app('translator')->get('Home'); ?></span>
        </a>
        <a href="<?php echo e(route('home')); ?>" class="text-dark"
           onclick="localStorage.setItem('showType', 'live')"
           aria-label="<?php echo app('translator')->get('Live matches'); ?>">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="2"></circle>
                <path d="M7.8 7.8a6 6 0 0 0 0 8.4m8.4-8.4a6 6 0 0 1 0 8.4M4.8 4.8a10.2 10.2 0 0 0 0 14.4m14.4-14.4a10.2 10.2 0 0 1 0 14.4"></path>
            </svg>
            <span class="nav-label"><?php echo app('translator')->get('Live'); ?></span>
        </a>

        <a href="<?php echo e(route('home', ['panel' => 'betslip'])); ?>" class="text-dark bet-slip-link"
           aria-label="<?php echo app('translator')->get('Open bet slip'); ?>" aria-controls="rightbar" aria-expanded="false"
           onclick="if (window.openSidebar && window.openSidebar('rightbar')) return false;">
            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 6.5h16v3a2.5 2.5 0 0 0 0 5v3H4v-3a2.5 2.5 0 0 0 0-5z"></path>
                <path d="M12 8.5v1.8m0 1.8v1.8"></path>
            </svg>
            <span class="nav-label"><?php echo app('translator')->get('Bet Slip'); ?></span>
        </a>
        <button type="button"
                class="bottom-theme-toggle d-none"
                onclick="darkMode()"
                aria-label="<?php echo e(session()->get('dark-mode') == 'true' ? 'Switch to light mode' : 'Switch to dark mode'); ?>"
                aria-pressed="<?php echo e(session()->get('dark-mode') == 'true' ? 'true' : 'false'); ?>">
            <svg class="theme-toggle-sun" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path>
            </svg>
            <svg class="theme-toggle-moon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M20.2 15.5A8.8 8.8 0 0 1 8.5 3.8 8.9 8.9 0 1 0 20.2 15.5Z"></path>
            </svg>
            <span class="nav-label">Theme</span>
        </button>

        <?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(route('user.betHistory')); ?>" class="text-dark <?php echo e(Request::routeIs('user.betHistory') ? 'active' : ''); ?>" <?php if(Request::routeIs('user.betHistory')): ?> aria-current="page" <?php endif; ?>>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 3.5h12v17l-2-1.4-2 1.4-2-1.4-2 1.4-2-1.4-2 1.4z"></path>
                    <path d="M9 8h6m-6 3.5h6m-6 3.5h4"></path>
                </svg>
                <span class="nav-label"><?php echo app('translator')->get('Bet History'); ?></span>
            </a>
            <a href="<?php echo e(route('user.profile')); ?>" class="text-dark <?php echo e(Request::routeIs('user.profile') ? 'active' : ''); ?>" <?php if(Request::routeIs('user.profile')): ?> aria-current="page" <?php endif; ?>>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="3.4"></circle>
                    <path d="M5.2 20c.6-3.8 3.1-5.8 6.8-5.8s6.2 2 6.8 5.8"></path>
                </svg>
                <span class="nav-label"><?php echo app('translator')->get('Profile'); ?></span>
            </a>
        <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="text-dark">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="3.4"></circle>
                    <path d="M5.2 20c.6-3.8 3.1-5.8 6.8-5.8s6.2 2 6.8 5.8"></path>
                </svg>
                <span class="nav-label"><?php echo app('translator')->get('Profile'); ?></span>
            </a>
        <?php endif; ?>

    </nav>
<?php endif; ?>
<?php /**PATH /home/runner/workspace/resources/views/themes/betting/partials/nav.blade.php ENDPATH**/ ?>