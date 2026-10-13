<!-- categories -->
<div class="sports-category-bar" id="sportsCategoryBar">
    <div class="categories" id="categories">
        <a href="<?php echo e(route('home')); ?>" <?php if(Request::routeIs('home')): ?> class="active" <?php endif; ?>>
            <svg class="category-icon" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="9"></circle>
                <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"></path>
            </svg>
            <span><?php echo e(trans('All Sports')); ?></span>
        </a>

        <?php
            $segments = request()->segments();
            $last = end($segments);
            $visibleCategories = $gameCategories->take(5);
        ?>

        <?php $__currentLoopData = $visibleCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gameCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('category', [slug($gameCategory->name), $gameCategory->id])); ?>"
               class="<?php echo e((Request::routeIs('category') && $last == $gameCategory->id) ? 'active' : ''); ?>">
                <span class="category-icon-wrap"><?php echo $gameCategory->icon ?></span>
                <span><?php echo e($gameCategory->name); ?></span>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <button type="button" class="category-more"
                aria-label="<?php echo app('translator')->get('More sports'); ?>"
                aria-controls="moreSportsMenu"
                aria-expanded="false"
                onclick="window.toggleSportsMore(this)">
            <svg class="category-more-icon" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="5" cy="12" r="1.7"></circle>
                <circle cx="12" cy="12" r="1.7"></circle>
                <circle cx="19" cy="12" r="1.7"></circle>
            </svg>
            <span><?php echo app('translator')->get('More'); ?></span>
        </button>
    </div>

    <div class="category-more-menu" id="moreSportsMenu" role="menu" hidden>
        <h6><?php echo app('translator')->get('All sports'); ?></h6>
        <?php $__empty_1 = true; $__currentLoopData = $gameCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gameCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a role="menuitem"
               href="<?php echo e(route('category', [slug($gameCategory->name), $gameCategory->id])); ?>"
               class="<?php echo e((Request::routeIs('category') && $last == $gameCategory->id) ? 'active' : ''); ?>">
                <span class="category-icon-wrap"><?php echo $gameCategory->icon ?></span>
                <span><?php echo e($gameCategory->name); ?></span>
                <?php if((int)$gameCategory->game_active_match_count > 0): ?>
                    <small><?php echo e($gameCategory->game_active_match_count); ?></small>
                <?php endif; ?>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="category-more-empty"><?php echo app('translator')->get('No sports are available right now.'); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH /home/runner/workspace/resources/views/themes/betting/partials/home/navbar.blade.php ENDPATH**/ ?>