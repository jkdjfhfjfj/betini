<?php $__env->startSection('title','Payout Details'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid">
        <?php if($payout->last_error): ?>
            <div class="bd-callout bd-callout-warning mb-3">
                <i class="fas fa-info-circle mr-2"></i> <?php echo app('translator')->get('Last API Error:'); ?> <?php echo e($payout->last_error); ?>

            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title"><?php echo app('translator')->get("Payout Details"); ?></h4>

                            <div>
                                <?php if($payout->status == 1): ?>
                                    <a href="<?php echo e(route('admin.payout-confirm',$payout->id)); ?>"
                                       data-target="#confirmModal" data-toggle="modal"
                                       class="btn btn-success btn-sm confirmButton"><i class="far fa-check-circle"></i> <?php echo app('translator')->get('Confirm'); ?></a>
                                    <a href="<?php echo e(route('admin.payout-cancel',$payout->id)); ?>"
                                       data-toggle="modal"
                                       data-target="#confirmModal"
                                       class="btn btn-danger btn-sm confirmButton"><i class="far fa-times-circle"></i> <?php echo app('translator')->get('Reject'); ?></a>
                                <?php endif; ?>

                                <a href="<?php echo e(route('admin.payout-log')); ?>" class="btn btn-sm btn-primary ml-2">
                                    <span><i class="fas fa-arrow-left"></i> <?php echo app('translator')->get('Back'); ?></span>
                                </a>

                            </div>

                        </div>

                        <hr>

                        <div class="p-4 border shadow-sm rounded">
                            <div class="row">
                                <div class="col-md-6 border-right">
                                    <ul class="list-style-none">
                                        <li class="my-2 border-bottom pb-3">
                                            <span class="font-weight-medium text-dark"><i
                                                    class="icon-info mr-2 text-primary"></i> <?php echo app('translator')->get("Transaction"); ?>: <small
                                                    class="float-right"><?php echo e(dateTime($payout->created_at)); ?> </small></span>
                                        </li>

                                        <li class="my-3 d-flex align-items-center">
                                            <span><i class="icon-check mr-2 text-primary"></i> <?php echo app('translator')->get('Sender name :'); ?></span>

                                            <a class="ml-3" href="<?php echo e(route('admin.user-edit',$payout->user_id)); ?>">
                                                <div class="d-lg-flex d-block align-items-center ">
                                                    <div class="mr-1"><img
                                                            src="<?php echo e(getFile(config('location.user.path').optional($payout->user)->image)); ?>"
                                                            alt="user" class="rounded-circle" width="45"
                                                            height="45"></div>
                                                    <div class="">
                                                        <h5 class="text-dark mb-0 font-16 font-weight-medium"><?php echo app('translator')->get(optional($payout->user)->username); ?></h5>
                                                        <p class="text-muted mb-0 font-12 font-weight-medium"><?php echo app('translator')->get(optional($payout->user)->email); ?></p>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>

                                        <li class="my-3">
                                            <span class="font-weight-bold text-dark"><i
                                                    class="icon-check mr-2 text-primary"></i> <?php echo app('translator')->get("Payment method"); ?> : <span
                                                    class="font-weight-medium text-info"><?php echo e(__(optional($payout->method)->name)); ?></span></span>
                                        </li>


                                        <li class="my-3">
                                            <span class="font-weight-medium text-dark"><i
                                                    class="icon-check mr-2 text-primary"></i> <?php echo app('translator')->get('Transaction Id'); ?> : <span
                                                    class="font-weight-medium text-success"><?php echo e(__($payout->trx_id)); ?></span></span>
                                        </li>

                                        <li class="my-3">
                                            <span><i class="icon-check mr-2 text-primary"></i> <?php echo app('translator')->get('Status'); ?> :
                                                <?php if($payout->status == 1): ?>
                                                    <span class="badge badge-pill badge-warning"><?php echo app('translator')->get('Pending'); ?></span>
                                                <?php elseif($payout->status == 2): ?>
                                                    <span
                                                        class="badge badge-pill badge-success"><?php echo app('translator')->get('Completed'); ?></span>
                                                <?php elseif($payout->status == 3): ?>
                                                    <span class="badge badge-pill badge-danger"><?php echo app('translator')->get('Rejected'); ?></span>
                                                <?php elseif($payout->status == 4): ?>
                                                    <span class="badge badge-pill badge-danger"><?php echo app('translator')->get('Failed'); ?></span>
                                                <?php endif; ?>

                                            </span>
                                        </li>


                                        <li class="my-3">
                                            <span><i class="icon-check mr-2 text-primary"></i> <?php echo app('translator')->get('Amount'); ?> : <span
                                                    class="font-weight-bold text-dark"><?php echo e((getAmount($payout->amount,2)).' '.config('basic.currency')); ?>

                                                </span>
                                            </span>
                                        </li>

                                        <li class="my-3">
                                            <span><i class="icon-check mr-2 text-primary"></i> <?php echo app('translator')->get('Charge'); ?> : <span
                                                    class="font-weight-bold text-danger"><?php echo e((getAmount($payout->charge,2)).' '.config('basic.currency')); ?>

                                                </span>
                                            </span>
                                        </li>

                                        <?php if($payout->other_charge): ?>
                                            <li class="my-3">
                                            <span><i class="icon-check mr-2 text-primary"></i> <?php echo app('translator')->get('Charge'); ?> : <span
                                                    class="font-weight-bold text-danger">
                                                    <?php echo e((getAmount($payout->other_charge,2)).' '.config('basic.currency')); ?>

                                                </span>
                                            </span>
                                            </li>
                                        <?php endif; ?>

                                        <li class="my-3">
                                            <span><i
                                                    class="icon-check mr-2 text-primary"></i> <?php echo app('translator')->get('Total Amount'); ?> : <span
                                                    class="font-weight-bold text-dark">
                                                    <?php echo e((getAmount($payout->net_amount,2)).' '.config('basic.currency')); ?>

                                                </span>
                                            </span>
                                        </li>
                                    </ul>
                                </div>


                                <div class="col-md-6 ">
                                    <?php if(isset($payout->information)): ?>
                                        <ul class="list-style-none border-bottom">
                                            <li class="my-2 border-bottom pb-3">
                                            <span class="font-weight-medium text-dark"><i
                                                    class="icon-user mr-2 text-primary"></i> <?php echo app('translator')->get('Withdraw Information'); ?></span>
                                            </li>


                                            <li class="my-3">
                                            <span><i class="icon-check mr-2 text-primary"></i> <?php echo app('translator')->get('Bank Currency'); ?> : <span
                                                    class="font-weight-bold text-danger"><?php echo e(__($payout->currency_code)); ?>

                                                </span>
                                            </span>
                                            </li>

                                            <?php $__currentLoopData = $payout->information; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li class="my-3">
                                            <span><i class="icon-check mr-2 text-primary"></i> <?php echo e(__(snake2Title($key))); ?> :

                                                <span>
														<?php if($value->type == 'file'): ?>

                                                        <img class="img-profile rounded-circle w-50"
                                                             src="<?php echo e(getFile(config('location.withdrawLog.path').@$value->fieldValue??$value->field_name)); ?>">
                                                    <?php else: ?>
                                                        <?php if($key == 'amount'): ?>
                                                            <span class="font-weight-bold text-dark">
                                                                        <?php echo e(getAmount(@$value->fieldValue??$value->field_name,8)); ?>

                                                            </span>
                                                        <?php else: ?>
                                                            <span class="font-weight-bold text-dark">
                                                                    <?php echo e(__(@$value->fieldValue??$value->field_name)); ?>

                                                            </span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
													</span>
                                            </span>
                                                </li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    <?php endif; ?>

                                    <?php if($payout->meta_field): ?>
                                        <ul class="list-style-none mt-4">
                                            <li class="my-2 border-bottom pb-3">
                                            <span class="font-weight-medium text-dark"><i
                                                    class="icon-user mr-2 text-success"></i> <?php echo app('translator')->get('Additional Information'); ?></span>
                                            </li>

                                            <?php $__currentLoopData = $payout->meta_field; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li class="my-3">
                                            <span><i class="icon-check mr-2 text-success"></i> <?php echo e(__(snake2Title($key))); ?> :
                                                <span class="font-weight-bold"><?php echo e(__($value->fieldValue)); ?></span>
                                            </span>
                                                </li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    <?php endif; ?>

                                    <?php if($payout->meta_field): ?>
                                        <ul class="list-style-none mt-4">
                                            <li class="my-2 border-bottom pb-3">
                                            <span class="font-weight-medium text-dark"><i
                                                    class="icon-user mr-2 text-success"></i> <?php echo app('translator')->get('Additional Information'); ?></span>
                                            </li>

                                            <?php $__currentLoopData = $payout->meta_field; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li class="my-3">
                                            <span><i class="icon-check mr-2 text-success"></i> <?php echo e(__(snake2Title($key))); ?> :
                                                <span class="font-weight-bold"><?php echo e(__($value->fieldValue)); ?></span>
                                            </span>
                                                </li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    <?php endif; ?>


                                    <?php if($payout->feedback): ?>
                                        <ul class="list-style-none mt-4">
                                            <li class="my-2 border-bottom pb-3">
                                            <span class="font-weight-medium text-dark"><i
                                                    class="icon-user mr-2 text-success"></i> <?php echo app('translator')->get('Feedback'); ?></span>
                                            </li>

                                            <li class="my-3 text-dark">
                                                <?php echo e($payout->feedback); ?>

                                            </li>
                                        </ul>
                                    <?php endif; ?>

                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header text-white bg-primary">
                    <h5 class="modal-title" id="exampleModalLabel"><i
                            class="fas fa-info-circle"></i> <?php echo app('translator')->get('Confirmation !'); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post" id="confirmForm">
                    <div class="modal-body text-center">
                        <p><?php echo app('translator')->get('Are you sure you want to confirm this action?'); ?></p>
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label for="note" class="text-dark"><?php echo app('translator')->get('Note'); ?> :</label>
                            <textarea name="feedback" rows="5" class="form-control form-control-sm"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark"
                                data-dismiss="modal"><?php echo app('translator')->get('Close'); ?></button>
                        <input type="submit" class="btn btn-primary" value="<?php echo app('translator')->get('Confirm'); ?>">
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
    <script src="<?php echo e(asset('assets/admin/js/bootstrap-select.min.js')); ?>"></script>
    <script>
        'use strict'
        $(document).on('click', '.confirmButton', function (e) {
            e.preventDefault();
            let submitUrl = $(this).attr('href');
            $('#confirmForm').attr('action', submitUrl)
        })
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/payout/view.blade.php ENDPATH**/ ?>