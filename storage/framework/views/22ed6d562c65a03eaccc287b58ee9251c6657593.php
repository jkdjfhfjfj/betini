<?php if(0 < count($pending)): ?>
<div class="col-lg-3 col-xl-2 border-right">
    <div class="scrollable position-relative scroll-height">
        <ul class="mailbox list-style-none">
            <li>
                <div class="message-center">
                    <?php $__currentLoopData = $pending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('admin.ticket.view', $item->id)); ?>"
                           class="message-item d-flex align-items-center border-bottom px-3 py-2 <?php echo e(($ticket->ticket == $item->ticket) ? 'sideNavTicket' : ''); ?>">
                            <div class="user-img">
                                <img src="<?php echo e(getFile(config('location.user.path').optional($item->user)->image)); ?>"
                                     alt="user" class="img-fluid rounded-circle width-40p"> <span
                                    class="profile-status online float-right"></span>
                            </div>
                            <div class="w-75 d-inline-block v-middle pl-2">
                                <h6 class="message-title mb-0 mt-1"><?php echo e(trans($item->name)); ?></h6>
                                <span class="font-12 text-nowrap d-block text-muted text-truncate">
                                    <?php echo app('translator')->get(@$item->lastMessage); ?>
                                </span>
                                <span class="font-12 text-nowrap d-block text-muted"><?php echo e(dateTime($item->last_reply)); ?></span>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </li>
        </ul>
    </div>
</div>
<?php endif; ?>
<?php /**PATH /home/runner/workspace/resources/views/admin/ticket/nav.blade.php ENDPATH**/ ?>