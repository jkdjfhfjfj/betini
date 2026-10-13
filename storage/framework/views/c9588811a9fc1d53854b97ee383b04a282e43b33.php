<?php if(isset($contentDetails['slider']) && $contentDetails['slider']->isNotEmpty()): ?>
    <?php
        $promoSlides = $contentDetails['slider'];
    ?>
    <section class="home-promo-carousel" aria-label="<?php echo app('translator')->get('Featured promotions'); ?>" data-home-promo-carousel>
        <div class="home-promo-track" data-home-promo-track>
            <?php $__currentLoopData = $promoSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $slideDescription = optional($data->description);
                    $slideMedia = optional(optional($data->content)->contentMedia)->description;
                    $slideImage = optional($slideMedia)->image;
                    $slidePrimaryUrl = optional($slideMedia)->button_link;
                    $slideSecondaryUrl = optional($slideMedia)->secondary_button_link;
                    $slideTitle = trim((string) ($slideDescription->name ?? ''));
                    $slideText = trim((string) ($slideDescription->short_description ?? ''));
                    $hasPlaceholderCopy = preg_match('/lorem ipsum|dolor sit amet|consectetur adipisicing/i', $slideTitle . ' ' . $slideText);

                    if ($hasPlaceholderCopy) {
                        $slideTitle = __('Featured matches');
                        $slideText = __('Explore upcoming fixtures, follow live action, and find markets for your next bet.');
                    } elseif ($slideTitle === '' && $slideText === '') {
                        $slideTitle = __('Featured matches');
                        $slideText = __('Explore upcoming fixtures and live sports markets.');
                    } elseif ($slideTitle === '') {
                        $slideTitle = __('Featured matches');
                    }
                ?>

                <article class="home-promo-slide"
                         role="group"
                         aria-roledescription="<?php echo app('translator')->get('slide'); ?>"
                         aria-label="<?php echo app('translator')->get('Slide :current of :total', ['current' => $loop->iteration, 'total' => $promoSlides->count()]); ?>">
                    <?php if($slideImage): ?>
                        <img
                            class="home-promo-image"
                            src="<?php echo e(getFile(config('location.content.path') . $slideImage)); ?>"
                            alt="<?php echo e($slideTitle); ?>"
                            <?php if($loop->first): ?> fetchpriority="high" <?php else: ?> loading="lazy" <?php endif; ?>
                        >
                    <?php endif; ?>

                    <div class="home-promo-copy">
                        <div class="home-promo-copy-inner">
                            <h2><?php echo e($slideTitle); ?></h2>
                            <?php if($slideText !== ''): ?>
                                <p><?php echo e($slideText); ?></p>
                            <?php endif; ?>

                            <div class="home-promo-actions">
                                <?php if($slidePrimaryUrl): ?>
                                    <a class="btn-custom home-promo-primary" href="<?php echo e($slidePrimaryUrl); ?>">
                                        <?php echo e($slideDescription->button_name ?: __('Explore')); ?>

                                    </a>
                                <?php endif; ?>
                                <?php if($slideSecondaryUrl && $slideDescription->secondary_button_name): ?>
                                    <a class="btn-custom home-promo-secondary" href="<?php echo e($slideSecondaryUrl); ?>">
                                        <?php echo e($slideDescription->secondary_button_name); ?>

                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if($promoSlides->count() > 1): ?>
            <div class="home-promo-controls" aria-label="<?php echo app('translator')->get('Promotion carousel controls'); ?>">
                <button type="button" class="home-promo-arrow" data-promo-previous aria-label="<?php echo app('translator')->get('Previous promotion'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7"></path></svg>
                </button>
                <div class="home-promo-dots">
                    <?php $__currentLoopData = $promoSlides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button type="button"
                                class="home-promo-dot<?php echo e($loop->first ? ' is-active' : ''); ?>"
                                data-promo-slide="<?php echo e($loop->index); ?>"
                                aria-label="<?php echo app('translator')->get('Show promotion :number', ['number' => $loop->iteration]); ?>"
                                aria-current="<?php echo e($loop->first ? 'true' : 'false'); ?>"></button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <button type="button" class="home-promo-arrow" data-promo-next aria-label="<?php echo app('translator')->get('Next promotion'); ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"></path></svg>
                </button>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php /**PATH /home/runner/workspace/resources/views/themes/betting/partials/home/slider.blade.php ENDPATH**/ ?>