<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get($title); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="card card-primary m-0 m-md-4 my-4 m-md-0 shadow">
        <div class="card-body">

            <div class="row no-gutters">
                <?php echo $__env->make('admin.ticket.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <div class="col-lg-9  col-xl-10">

                    <h5 class="card-title m-2 ">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-sm-10">
                                <?php if($ticket->status == 0): ?>
                                    <span class="badge badge-pill badge-primary"><?php echo app('translator')->get('Open'); ?></span>
                                <?php elseif($ticket->status == 1): ?>
                                    <span class="badge badge-pill badge-success"><?php echo app('translator')->get('Answered'); ?></span>
                                <?php elseif($ticket->status == 2): ?>
                                    <span class="badge badge-pill badge-dark"><?php echo app('translator')->get('Customer Reply'); ?></span>
                                <?php elseif($ticket->status == 3): ?>
                                    <span class="badge badge-pill badge-danger"><?php echo app('translator')->get('Closed'); ?></span>
                                <?php endif; ?>
                                [<?php echo e(trans('Ticket#'). $ticket->ticket); ?>] <?php echo e($ticket->subject); ?>

                            </div>
                            <div class="col-sm-2 text-sm-right mt-sm-0 mt-3">

                                <button type="button" class="btn  btn-sm btn-outline-danger btn-rounded" data-toggle="modal"
                                        data-target="#closeTicketModal"><i
                                        class="fas fa-times-circle"></i> <?php echo e(trans('Close')); ?></button>


                            </div>
                        </div>
                    </h5>
                    <div class="card-body border mx-2">
                        <form class="form-row" action="<?php echo e(route('admin.ticket.reply', $ticket->id)); ?>" method="post"
                              enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('put'); ?>

                            <div class="col-sm-10 col-12">
                                <div class="form-group mt-0 mb-0">
                                    <textarea name="message"  class="form-control  ticket-box" id="textarea1"  placeholder="<?php echo app('translator')->get('Type Here'); ?>" rows="3"><?php echo e(old('message')); ?></textarea>
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


                            <div class="col-sm-2 col-12">
                                <div
                                    class="justify-content-sm-end justify-content-start mt-sm-0 mt-2 align-items-center d-flex h-100">
                                    <div class="upload-btn">
                                        <div class="btn btn-primary new-file-upload mr-3"
                                             title="<?php echo e(trans('Image Upload')); ?>">
                                            <a href="#">
                                                <i class="fa fa-image"></i>
                                            </a>
                                            <input type="file" name="attachments[]" id="upload" class="upload-box"
                                                   multiple
                                                   placeholder="<?php echo app('translator')->get('Upload File'); ?>">
                                        </div>
                                        <p class="text-danger select-files-count"></p>
                                    </div>

                                    <button type="submit"
                                            class="btn btn-circle btn-lg btn-success float-right text-white"
                                            title="<?php echo e(trans('Reply')); ?>" name="replayTicket"
                                            value="1"><i class="fas fa-paper-plane"></i></button>
                                </div>

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
                        </form>
                    </div>


                    <?php if(count($ticket->messages) > 0): ?>
                        <div class="chat-box scrollable position-relative scroll-height">
                            <ul class="chat-list list-style-none px-3">
                                <?php $__currentLoopData = $ticket->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($item->admin_id != null): ?>


                                        <li class="chat-item list-style-none replied mt-3 text-right">
                                            <div class="chat-img d-inline-block">
                                                <img
                                                    src="<?php echo e(getFile(config('location.admin.path').optional($item->admin)->image)); ?>"
                                                    alt="user"
                                                    class="rounded-circle" width="45">
                                            </div>
                                            <div class="w-100">
                                                <div class="chat-content d-inline-block pr-3">
                                                    <h6 class="font-weight-medium"><?php echo e(optional($item->admin)->username); ?> </h6>

                                                    <div class="media flex-row-reverse">

                                                        <div class="msg p-2 d-inline-block mb-1">
                                                            <?php echo e($item->message); ?>

                                                        </div>

                                                        <?php if(adminAccessRoute(config('role.support_ticket.access.delete'))): ?>
                                                        <button data-id="<?php echo e($item->id); ?>" type="button"
                                                                data-toggle="modal" data-target="#DelMessage"
                                                                class="delete-message float-left btn btn-sm btn-default align-items-center">
                                                            <i class="ti-trash"></i></button>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if(0 < count($item->attachments)): ?>
                                                        <div class="d-flex justify-content-end">
                                                            <?php $__currentLoopData = $item->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=> $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <a href="<?php echo e(route('admin.ticket.download',encrypt($image->id))); ?>"
                                                                   class="ml-3 nowrap "><i
                                                                        class="fa fa-file"></i> <?php echo app('translator')->get('File'); ?> <?php echo e(++$k); ?> </a>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </div>
                                                    <?php endif; ?>


                                                </div>
                                                <div
                                                    class="chat-time d-block font-10 mt-0 mr-0 mb-3"><?php echo e(dateTime($item->created_at, 'd M, y h:i A')); ?>

                                                </div>
                                            </div>

                                        </li>


                                    <?php else: ?>

                                        <li class="chat-item list-style-none mt-3">
                                            <div class="chat-img d-inline-block">
                                                <img
                                                    src="<?php echo e(getFile(config('location.user.path').optional($item->user)->image)); ?>"
                                                    alt="user"
                                                    class="rounded-circle" width="45">
                                            </div>
                                            <div class="chat-content d-inline-block pl-3">
                                                <h6 class="font-weight-medium"><?php echo e(optional($item->user)->username); ?></h6>

                                                <div class="media">
                                                    <div class="msg p-2 d-inline-block mb-1">
                                                        <?php echo e($item->message); ?>

                                                    </div>
                                                    <button data-id="<?php echo e($item->id); ?>" type="button"
                                                            data-toggle="modal" data-target="#DelMessage"
                                                            class="delete-message float-left btn btn-sm btn-default align-items-center">
                                                        <i class="ti-trash"></i></button>

                                                </div>


                                                <?php if(0 < count($item->attachments)): ?>
                                                    <div class="d-flex justify-content-start">
                                                        <?php $__currentLoopData = $item->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=> $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <a href="<?php echo e(route('admin.ticket.download',encrypt($image->id))); ?>"
                                                               class="mr-3 nowrap "><i class="fa fa-file"></i> <?php echo app('translator')->get('File'); ?><?php echo e(++$k); ?>

                                                            </a>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                <?php endif; ?>


                                            </div>
                                            <div class="chat-time d-block font-10 mt-0 mr-0 mb-3"><?php echo e(dateTime($item->created_at, 'd M, y h:i A')); ?></div>
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




    <div class="modal fade" id="DelMessage" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-colored-header bg-primary">
                    <h4 class="modal-title"><?php echo app('translator')->get('Delete Confirmation'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        ×
                    </button>
                </div>
                <div class="modal-body">
                    <strong><?php echo app('translator')->get('Are you confirm to delete?'); ?></strong>
                </div>
                <div class="modal-footer">
                    <form method="post" action="<?php echo e(route('admin.ticket.delete')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="message_id" class="message_id">
                        <button type="button" class="btn btn-light" data-dismiss="modal"><?php echo app('translator')->get('Close'); ?></button>
                        <button type="submit" class="btn btn-primary"> <?php echo app('translator')->get('Yes'); ?> </button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="closeTicketModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header modal-colored-header bg-primary">
                    <h5 class="modal-title"><?php echo app('translator')->get('Close Ticket'); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        ×
                    </button>
                </div>
                <div class="modal-body">
                    <strong><?php echo app('translator')->get("Are you confirm to close this?"); ?></strong>
                </div>
                <div class="modal-footer">
                    <form method="post" action="<?php echo e(route('admin.ticket.reply', $ticket->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <button type="button" class="btn btn-light" data-dismiss="modal"><?php echo app('translator')->get('Close'); ?></button>
                        <button type="submit" class="btn btn-primary" name="replayTicket" value="2"> <?php echo app('translator')->get('Yes'); ?> </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>



<?php $__env->startPush('js'); ?>
    <script>
        $(document).ready(function () {
            'use strict';
            $('.delete-message').on('click', function (e) {
                $('.message_id').val($(this).data('id'));
            })

            $(document).on('change', '#upload', function () {
                var fileCount = $(this)[0].files.length;
                $('.select-files-count').text(fileCount + ' file(s) selected')
            })
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/ticket/show.blade.php ENDPATH**/ ?>