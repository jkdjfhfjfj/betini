<!-- navbar -->
<nav class="navbar navbar-expand-md fixed-top">
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
            <i class="fas fa-bars"></i>
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
            <?php if(auth()->guard()->check()): ?>
                <?php echo $__env->make($theme.'partials.wallet-currency-switch', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <div class="dropdown user-dropdown d-inline-block">
                    <button class="dropdown-toggle">
                        <i class="fal fa-user"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item <?php echo e(menuActive('user.home')); ?>" href="<?php echo e(route('user.home')); ?>">
                                <i class="fa fa-home"></i>
                                <?php echo app('translator')->get('Dashboard'); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?php echo e(menuActive('user.addFund')); ?>" href="<?php echo e(route('user.addFund')); ?>">
                                <i class="fal fa-money-bill"></i>
                                <?php echo app('translator')->get('Make a deposit'); ?>
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item <?php echo e(menuActive('user.payout.money')); ?>"
                               href="<?php echo e(route('user.payout.money')); ?>">
                                <i class="fas fa-envelope-open-dollar"></i>
                                <?php echo app('translator')->get('withdraw funds'); ?>
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
                <!-- login register button -->
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
            </div>
        </div>
    </div>
</nav>

<?php if(in_array(Request::route()->getName(),['home','category','tournament','match'])): ?>

    <div class="bottom-bar fixed-bottom text-center">
        <a href="<?php echo e(route('home')); ?>" class="text-dark">
            <i class="fa fa-home"></i>
            <span class="nav-label"><?php echo app('translator')->get('Home'); ?></span>
        </a>
        <a href="<?php echo e(route('home')); ?>" class="text-dark"
           onclick="localStorage.setItem('showType', 'live')">
            <i class="las la-podcast"></i>
            <span class="nav-label"><?php echo app('translator')->get('Live'); ?></span>
        </a>

        <a href="javascript:void(0)" class="text-dark bet-slip-link"
           aria-label="<?php echo app('translator')->get('Bet Slip'); ?>" onclick="toggleSidebar('rightbar')">
            <i class="fal fa-ticket-alt"></i>
            <span class="nav-label"><?php echo app('translator')->get('Bet Slip'); ?></span>
        </a>

        <?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(route('user.betHistory')); ?>" class="text-dark">
                <i class="fal fa-receipt"></i>
                <span class="nav-label"><?php echo app('translator')->get('My Bets'); ?></span>
            </a>
            <a href="<?php echo e(route('user.profile')); ?>" class="text-dark">
                <i class="fal fa-user"></i>
                <span class="nav-label"><?php echo app('translator')->get('Profile'); ?></span>
            </a>
        <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="text-dark">
                <i class="fal fa-receipt"></i>
                <span class="nav-label"><?php echo app('translator')->get('My Bets'); ?></span>
            </a>
            <a href="<?php echo e(route('login')); ?>" class="text-dark">
                <i class="fal fa-user"></i>
                <span class="nav-label"><?php echo app('translator')->get('Profile'); ?></span>
            </a>
        <?php endif; ?>

    </div>
<?php endif; ?>
<?php /**PATH /home/runner/workspace/resources/views/themes/betting/partials/nav.blade.php ENDPATH**/ ?>