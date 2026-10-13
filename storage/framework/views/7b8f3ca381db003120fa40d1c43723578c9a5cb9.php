<?php $__env->startSection('title',trans($page_title)); ?>

<?php $__env->startPush('style'); ?>
    <style>
        button[name="replayTicket"] {
            border-radius: 0;
        }
    </style>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-10">
            <div class="card secbg form-block br-4">
                <div class="card-header">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-sm-10">
                            <?php if($ticket->status == 0): ?>
                                <span class="badge bg-primary"><?php echo app('translator')->get('Open'); ?></span>
                            <?php elseif($ticket->status == 1): ?>
                                <span class="badge bg-success"><?php echo app('translator')->get('Answered'); ?></span>
                            <?php elseif($ticket->status == 2): ?>
                                <span class="badge bg-dark"><?php echo app('translator')->get('Customer Reply'); ?></span>
                            <?php elseif($ticket->status == 3): ?>
                                <span class="badge bg-danger"><?php echo app('translator')->get('Closed'); ?></span>
                            <?php endif; ?>
                            [<?php echo e(trans('Ticket#'). $ticket->ticket); ?>] <?php echo e($ticket->subject); ?>

                        </div>
                        <div class="col-sm-2 text-sm-end mt-sm-0 mt-3">
                            <button type="button" class="btn btn-sm btn-danger btn-rounded"
                                    data-bs-toggle="modal"
                                    data-bs-target="#closeTicketModal"><i
                                    class="fas fa-times-circle"></i> <?php echo e(trans('Close')); ?></button>

                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-body-inner">
                        <form class="form-row" action="<?php echo e(route('user.ticket.reply', $ticket->id)); ?>"
                              method="post"
                              enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group input-box">
                                                <textarea name="message" class="form-control  ticket-box" id="textarea1"
                                                          placeholder="<?php echo app('translator')->get('Type Here'); ?>"
                                                          rows="3"><?php echo e(old('message')); ?></textarea>
                                    </div>
                                    <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <span class="text-danger"><?php echo e(trans($message)); ?></span>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>


                                <div class="col-md-2">
                                    <div class="card-body-buttons mt-2 mt-md-0">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="upload-btn mt-5">
                                                    <div class="btn btn-primary new-file-upload me-0"
                                                         title="<?php echo e(trans('Image Upload')); ?>">
                                                        <a href="#">
                                                            <i class="fa fa-image"></i>
                                                        </a>
                                                        <input type="file" name="attachments[]" id="upload"
                                                               class="upload-box"
                                                               multiple
                                                               placeholder="<?php echo app('translator')->get('Upload File'); ?>">
                                                    </div>
                                                    <p class="text-danger select-files-count"></p>
                                                    <?php $__errorArgs = ['attachments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <span class="text-danger"><?php echo e(trans($message)); ?></span>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mt-5">
                                                <div class="submit-btn">
                                                    <button type="submit" name="replayTicket" value="1" class="submit-btn "><i
                                                            class="fas fa-paper-plane"></i> <?php echo e(trans('Reply')); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php if(count($ticket->messages) > 0): ?>
                        <div class="chat-box scrollable position-relative scroll-height">
                            <ul class="chat-list list-style-none ">
                                <?php $__currentLoopData = $ticket->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($item->admin_id == null): ?>

                                        <li class="chat-item list-style-none replied mt-3 text-sm-end">
                                            <div class="chat-img d-inline-block">

                                                <img
                                                    src="<?php echo e(getFile(config('location.user.path').optional($ticket->user)->image)); ?>"
                                                    alt="user"
                                                    class="rounded-circle" width="45">
                                            </div>
                                            <div class="w-100">
                                                <div class="chat-content d-inline-block me-3">
                                                    <h6 class="font-weight-medium"><?php echo e(optional($ticket->user)->username); ?> </h6>

                                                    <div class="media flex-row-reverse">

                                                        <div class="msg p-2 d-inline-block mb-1">
                                                            <?php echo e($item->message); ?>

                                                        </div>

                                                    </div>

                                                    <?php if(0 < count($item->attachments)): ?>
                                                        <div class="d-flex justify-content-end">
                                                            <?php $__currentLoopData = $item->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=> $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <a href="<?php echo e(route('user.ticket.download',encrypt($image->id))); ?>"
                                                                   class="ml-3 nowrap "><i
                                                                        class="fa fa-file"></i> <?php echo app('translator')->get('File'); ?> <?php echo e(++$k); ?>

                                                                </a>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div
                                                        class="chat-time"><?php echo e(dateTime($item->created_at, 'd M, y h:i A')); ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php else: ?>

                                        <li class="chat-item list-style-none mt-3">
                                            <div class="media">
                                                <div class="chat-img d-inline-block">
                                                    <img
                                                        src="<?php echo e(getFile(config('location.admin.path').optional($item->admin)->image)); ?>"
                                                        alt="user"
                                                        class="rounded-circle" width="45">
                                                </div>
                                                <div class="chat-content d-inline-block pl-3">
                                                    <h6 class="font-weight-medium"><?php echo e(optional($item->admin)->name); ?></h6>

                                                    <div class="media">
                                                        <div class="msg p-2 d-inline-block mb-1">
                                                            <?php echo e($item->message); ?>

                                                        </div>

                                                    </div>


                                                    <?php if(0 < count($item->attachments)): ?>
                                                        <div class="d-flex justify-content-start">
                                                            <?php $__currentLoopData = $item->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=> $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <a href="<?php echo e(route('user.ticket.download',encrypt($image->id))); ?>"
                                                                   class="mr-3 nowrap"><i
                                                                        class="fa fa-file"></i> <?php echo app('translator')->get('File'); ?> <?php echo e(++$k); ?>

                                                                </a>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="chat-time d-block font-10 mt-0 mr-0 mb-3"><?php echo e(dateTime($item->created_at, 'd M, y h:i A')); ?></div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="closeTicketModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-custom-content">
                <form method="post" action="<?php echo e(route('user.ticket.reply', $ticket->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="modal-header">
                        <h4 class="modal-title"> <?php echo app('translator')->get('Confirmation'); ?></h4>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><?php echo app('translator')->get('Are you want to close ticket?'); ?></p>
                    </div>
                    <div class="modal-footer">

                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <?php echo app('translator')->get('Close'); ?>
                        </button>

                        <button type="submit" class="btn-custom" name="replayTicket"
                                value="2"><?php echo app('translator')->get("Confirm"); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('script'); ?>
    <script>
        'use strict';
        $(document).on('change', '#upload', function () {
            var fileCount = $(this)[0].files.length;
            $('.select-files-count').text(fileCount + ' file(s) selected')
        })
    </script>
<?php $__env->stopPush(); ?>



<?php echo $__env->make($theme.'layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/themes/betting/user/support/view.blade.php ENDPATH**/ ?>