<?php $__env->startSection('title'); ?>
    <?php echo e('Pay with '.optional($order->gateway)->name ?? ''); ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <section id="dashboard">
        <div class="container add-fund">
            <div class="row">
                <div class="col-md-12">
                    <div class="card secbg">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img
                                        src="<?php echo e(getFile(config('location.gateway.path').optional($order->gateway)->image)); ?>"
                                        class="card-img-top gateway-img br-4" alt="..">
                                </div>

                                <div class="col-md-9">
                                    <h4><?php echo app('translator')->get('Please Pay'); ?> <?php echo e(getAmount($order->final_amount)); ?> <?php echo e($order->gateway_currency); ?></h4>
                                    <h4 class="mt-15 mb-15"><?php echo app('translator')->get('To Get'); ?> <?php echo e(getAmount($order->amount)); ?>  <?php echo e($basic->currency); ?></h4>

                                    <button type="button"
                                            class="btn-custom  "
                                            id="payment-button"><?php echo app('translator')->get('Pay with Khalti'); ?>
                                    </button>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script'); ?>
    <script
        src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script>
    <script>

        $(document).ready(function () {
            $('body').addClass('antialiased')
        });

        var config = {
            // replace the publicKey with yours
            "publicKey": "<?php echo e($data->publicKey); ?>",
            "productIdentity": "<?php echo e($data->productIdentity); ?>",
            "productName": "Payment",
            "productUrl": "<?php echo e(url('/')); ?>",
            "paymentPreference": [
                "KHALTI",
                "EBANKING",
                "MOBILE_BANKING",
                "CONNECT_IPS",
                "SCT",
            ],
            "eventHandler": {
                onSuccess(payload) {
                    // hit merchant api for initiating verfication
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo e(route('khalti.verifyPayment',[$order->transaction])); ?>",
                        data: {
                            token: payload.token,
                            amount: payload.amount,
                            "_token": "<?php echo e(csrf_token()); ?>"
                        },
                        success: function (res) {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo e(route('khalti.storePayment')); ?>",
                                data: {
                                    response: res,
                                    "_token": "<?php echo e(csrf_token()); ?>"
                                },
                                success: function (res) {
                                    window.location.href = "<?php echo e(route('success')); ?>"
                                }
                            });
                        }
                    });
                    // console.log(payload);
                },
                onError(error) {
                    console.log(error);
                },
                onClose() {
                    console.log('widget is closing');
                }
            }
        };
        var checkout = new KhaltiCheckout(config);
        var btn = document.getElementById("payment-button");
        btn.onclick = function () {
            // minimum transaction amount must be 10, i.e 1000 in paisa.
            checkout.show({amount: "<?php echo e($data->amount *100); ?>"});
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment/khalti.blade.php ENDPATH**/ ?>