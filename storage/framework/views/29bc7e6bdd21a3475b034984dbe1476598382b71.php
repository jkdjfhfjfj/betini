<?php $__env->startSection('title'); ?>
    <?php echo e('Pay with '.optional($order->gateway)->name ?? ''); ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>

    <script src="https://js.stripe.com/v3/"></script>
    <style>
        form.form{box-shadow: none!important;}
        .StripeElement {
            box-sizing: border-box;
            height: 40px;
            padding: 10px 12px;
            border: 1px solid transparent;
            border-radius: 4px;
            background-color: white;
            box-shadow: 0 1px 3px 0 #e6ebf1;
            -webkit-transition: box-shadow 150ms ease;
            transition: box-shadow 150ms ease;
        }

        .StripeElement--focus {
            box-shadow: 0 1px 3px 0 #cfd7df;
        }

        .StripeElement--invalid {
            border-color: #fa755a;
        }

        .StripeElement--webkit-autofill {
            background-color: #fefde5 !important;
        }
    </style>

    <div class="row">
        <div class="col-md-8">
            <div class="card secbg br-4">
                <div class="card-body br-4">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <img
                                src="<?php echo e(getFile(config('location.gateway.path').optional($order->gateway)->image)); ?>"
                                class="card-img-top gateway-img br-4" alt="...">
                        </div>
                        <div class="col-md-9">
                            <h4><?php echo app('translator')->get('Please Pay'); ?> <?php echo e(getAmount($order->final_amount)); ?> <?php echo e($order->gateway_currency); ?></h4>
                            <h4 class="mt-15 mb-15"><?php echo app('translator')->get('To Get'); ?> <?php echo e(getAmount($order->amount)); ?>  <?php echo e($basic->currency); ?></h4>
                            <form action="<?php echo e($data->url); ?>" method="<?php echo e($data->method); ?>" class="form">
                                <script
                                    src="<?php echo e($data->src); ?>"
                                    class="stripe-button"
                                    <?php $__currentLoopData = $data->val; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=> $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        data-<?php echo e($key); ?>="<?php echo e($value); ?>"
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>>
                                </script>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('script'); ?>
        <script>
            $(document).ready(function () {
                $('button[type="submit"]').removeClass("stripe-button-el").addClass("btn-custom").find('span').css('min-height', 'initial');
            })
        </script>
    <?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>





<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment/stripe.blade.php ENDPATH**/ ?>