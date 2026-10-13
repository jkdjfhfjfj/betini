<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('Payment'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <section style="padding: 120px 0"id="dashboard">
        <div class="container add-fund pb-20">
            <div class="row feature-wrapper top-0 add-fund">
                <?php $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-xl-2 col-lg-3 col-md-4  col-sm-6 col-6 mb-30">
                        <div class="card card-type-1 text-center">

                            <div class="card-icon">
                                <img class="w-100"src="<?php echo e(getFile(config('location.gateway.path').$gateway->image)); ?>"
                                     alt="<?php echo e($gateway->name); ?>" class="gateway">
                            </div>

                            <button type="button"
                                    data-id="<?php echo e($gateway->id); ?>"
                                    data-name="<?php echo e($gateway->name); ?>"
                                    data-currency="<?php echo e($gateway->currency); ?>"
                                    data-gateway="<?php echo e($gateway->code); ?>"
                                    data-min_amount="<?php echo e(getAmount($gateway->min_amount, $basic->fraction_number)); ?>"
                                    data-max_amount="<?php echo e(getAmount($gateway->max_amount,$basic->fraction_number)); ?>"
                                    data-percent_charge="<?php echo e(getAmount($gateway->percentage_charge,$basic->fraction_number)); ?>"
                                    data-fix_charge="<?php echo e(getAmount($gateway->fixed_charge, $basic->fraction_number)); ?>"
                                    class="btn btn-danger btn-block  mt-2 colorbg-1 addFund"
                                    data-backdrop='static' data-keyboard='false'
                                    data-toggle="modal" data-target="#addFundModal"><?php echo app('translator')->get('Pay Now'); ?></button>

                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>
    <?php $__env->startPush('loadModal'); ?>
        <div id="addFundModal" class="modal fade addFundModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content form-block">
                    <div class="modal-header">
                        <h6 class="modal-title method-name"></h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="white-text">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body ">
                        <div class="payment-form ">
                            <?php if(0 == $totalPayment): ?>
                                <p class="text-danger depositLimit"></p>
                                <p class="text-danger depositCharge"></p>
                            <?php endif; ?>

                            <input type="hidden" class="gateway" name="gateway" value="">

                            <div class="form-group mb-30">
                                <label><?php echo app('translator')->get('Plan Name'); ?></label>
                                <input type="text" class=" form-control" value="<?php echo e($plan->name); ?>" readonly>
                            </div>


                            <div class="form-group mb-30">
                                <label><?php echo app('translator')->get('Amount'); ?></label>
                                <div class="input-group input-group-lg">
                                    <input type="text" class="amount form-control" name="amount"
                                           <?php if($totalPayment != null): ?> value="<?php echo e($totalPayment); ?>" readonly <?php endif; ?>>
                                    <div class="input-group-append">
                                        <span class="input-group-text show-currency"></span>
                                    </div>
                                </div>
                                <pre class="text-danger errors"></pre>
                            </div>
                        </div>

                        <div class="payment-info text-center">
                            <img class="w-100"id="loading" src="<?php echo e(asset('assets/admin/images/loading.gif')); ?>" alt="..."
                                 class="w-15"/>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-primary checkCalc"><?php echo app('translator')->get('Next'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php $__env->stopPush(); ?>


<?php $__env->stopSection(); ?>



<?php $__env->startPush('script'); ?>

    <script>
        $('#loading').hide();
        "use strict";
        var id, minAmount, maxAmount, baseSymbol, fixCharge, percentCharge, currency, amount, gateway;
        $('.addFund').on('click', function () {
            id = $(this).data('id');
            gateway = $(this).data('gateway');
            minAmount = $(this).data('min_amount');
            maxAmount = $(this).data('max_amount');
            baseSymbol = "<?php echo e(config('basic.currency_symbol')); ?>";
            fixCharge = $(this).data('fix_charge');
            percentCharge = $(this).data('percent_charge');
            currency = $(this).data('currency');
            $('.depositLimit').text(`<?php echo app('translator')->get('Transaction Limit:'); ?> ${minAmount} - ${maxAmount}  ${baseSymbol}`);

            var depositCharge = `<?php echo app('translator')->get('Charge:'); ?> ${fixCharge} ${baseSymbol}  ${(0 < percentCharge) ? ' + ' + percentCharge + ' % ' : ''}`;
            $('.depositCharge').text(depositCharge);

            $('.method-name').text(`<?php echo app('translator')->get('Payment By'); ?> ${$(this).data('name')} - ${currency}`);
            $('.show-currency').text("<?php echo e(config('basic.currency')); ?>");
            $('.gateway').val(currency);

            // amount
        });


        $(".checkCalc").on('click', function () {
            $('.payment-form').addClass('d-none');

            $('#loading').show();
            $('.modal-backdrop.fade').addClass('show');
            amount = $('.amount').val();
            $.ajax({
                url: "<?php echo e(route('user.addFund.request')); ?>",
                type: 'POST',
                data: {
                    amount,
                    gateway
                },
                success(data) {

                    $('.payment-form').addClass('d-none');
                    $('.checkCalc').closest('.modal-footer').addClass('d-none');

                    var htmlData = `
                     <ul class="list-group text-center">
                        <li class="list-group-item bg-transparent">
                            <img class="w-100"src="${data.gateway_image}"
                                style="max-width:100px; max-height:100px; margin:0 auto;"/>
                        </li>
                        <li class="list-group-item bg-transparent">
                            <?php echo app('translator')->get('Amount'); ?>:
                            <strong>${data.amount} </strong>
                        </li>
                        <li class="list-group-item bg-transparent"><?php echo app('translator')->get('Charge'); ?>:
                                <strong>${data.charge}</strong>
                        </li>
                        <li class="list-group-item bg-transparent">
                            <?php echo app('translator')->get('Payable'); ?>: <strong> ${data.payable}</strong>
                        </li>
                        <li class="list-group-item bg-transparent">
                            <?php echo app('translator')->get('Conversion Rate'); ?>: <strong>${data.conversion_rate}</strong>
                        </li>
                        <li class="list-group-item bg-transparent">
                            <strong>${data.in}</strong>
                        </li>

                        ${(data.isCrypto == true) ? `
                        <li class="list-group-item bg-transparent">
                            ${data.conversion_with}
                        </li>
                        ` : ``}

                        <li class="list-group-item bg-transparent">
                        <a href="${data.payment_url}" class="btn btn-primary   btn-block addFund "><?php echo app('translator')->get('Pay Now'); ?></a>
                        </li>
                        </ul>`;

                    $('.payment-info').html(htmlData)
                },
                complete: function () {
                    $('#loading').hide();
                },
                error(err) {
                    var errors = err.responseJSON;
                    for (var obj in errors) {
                        $('.errors').text(`${errors[obj]}`)
                    }

                    $('.payment-form').removeClass('d-none');
                }
            });
        });


        $('.close').on('click', function (e) {
            $('#loading').hide();
            $('.payment-form').removeClass('d-none');
            $('.checkCalc').closest('.modal-footer').removeClass('d-none');
            $('.payment-info').html(``)
            $('.amount').val(``);
            $("#addFundModal").modal("hide");
        });

    </script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/payment.blade.php ENDPATH**/ ?>