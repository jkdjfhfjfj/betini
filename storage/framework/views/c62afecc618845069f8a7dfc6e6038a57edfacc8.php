<?php $__env->startSection('title',$page_title); ?>

<?php $__env->startSection('content'); ?>

    <div class="card card-primary card-form m-0 m-md-4 my-4 m-md-0">
        <div class="card-body">

            <div class="row justify-content-between mb-3">

                <div class="col-md-4">
                    <button type="button" data-toggle="modal" data-target="#addModal" class="btn btn-sm btn-primary"><i
                            class="fa fa-plus-circle"></i> <?php echo app('translator')->get('Add New Key'); ?> </button>
                </div>


                <div class="col-md-5">
                    <div class="input-group has_append">
                        <select class="form-control select-language" required>
                            <option value=""><?php echo app('translator')->get('Import Keywords'); ?></option>
                            <?php $__currentLoopData = $list_lang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($data->id); ?>"
                                        <?php if($data->id == $lang->id): ?> style="display: none" <?php endif; ?>><?php echo e($data->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary import-language"><?php echo app('translator')->get('Import Now'); ?></button>
                        </div>
                    </div>

                    <small
                        class="text-danger"><?php echo app('translator')->get("If you import keywords from another language, Your present `$lang->name` all keywords will remove."); ?>
                    </small>
                </div>

            </div>


            <div class="table-responsive">
                <table id="zero_config" class="table table-striped table-bordered no-wrap">
                    <thead class="thead-primary">
                    <tr>
                        <th scope="col"><?php echo app('translator')->get('Key'); ?>
                        </th>
                        <th scope="col" class="text-left">
                            <?php echo e($lang->name); ?>

                        </th>
                        <th scope="col" class="w-85"><?php echo app('translator')->get('Action'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $json; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $langValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td data-label="<?php echo app('translator')->get('key'); ?>"><?php echo e($k); ?></td>
                            <td data-label="<?php echo app('translator')->get('Value'); ?>" class="text-left "><?php echo e($langValue); ?></td>


                            <td data-label="<?php echo app('translator')->get('Action'); ?>">
                                <a href="javascript:void(0)"
                                   data-target="#editModal"
                                   data-toggle="modal"
                                   data-title="<?php echo e($k); ?>"
                                   data-key="<?php echo e($k); ?>"
                                   data-value="<?php echo e($langValue); ?>"
                                   class="editModal btn btn-primary btn-sm "
                                   data-original-title="<?php echo app('translator')->get('Edit'); ?>">
                                    <i class="fa fa-pencil-alt"></i>
                                </a>

                                <a href="javascript:void(0)"
                                   data-key="<?php echo e($k); ?>"
                                   data-value="<?php echo e($langValue); ?>"
                                   data-toggle="modal" data-target="#deleteModal"
                                   class="btn btn-danger btn-sm deleteKey"
                                   data-original-title="<?php echo app('translator')->get('Remove'); ?>">
                                    <i class="fa fa-times-circle"></i>
                                </a>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </tbody>
                </table>
            </div>


        </div>
    </div>



    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title" id="myModalLabel"><?php echo app('translator')->get('Edit'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>

                <form action="<?php echo e(route('admin.language.updateKey',$lang->id)); ?>" method="post">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('put'); ?>
                    <div class="modal-body">
                        <div class="form-group ">
                            <label for="inputName" class="control-label font-weight-bold form-title"></label>

                            <input type="text" class="form-control form-control-lg" name="value"
                                   placeholder="Vale" value="">

                        </div>
                        <input type="hidden" name="key">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal"><?php echo app('translator')->get('Close'); ?></button>
                        <button type="submit" class="btn btn-primary"><?php echo app('translator')->get('Update'); ?></button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <!-- Modal for DELETE -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title" id="myModalLabel"><i class='fa fa-trash'></i> <?php echo app('translator')->get('Delete !'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>


                <div class="modal-body">
                    <strong><?php echo app('translator')->get('Are you sure you want to Delete ?'); ?></strong>
                </div>
                <form action="<?php echo e(route('admin.language.deleteKey',$lang->id)); ?>" method="post">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('delete'); ?>

                    <input type="hidden" name="key">
                    <input type="hidden" name="value">
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal"><?php echo app('translator')->get('Close'); ?></button>
                        <button type="submit" class="btn btn-danger "><?php echo app('translator')->get('Delete'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title" id="myModalLabel"> <?php echo app('translator')->get('Add New'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>

                <form action="<?php echo e(route('admin.language.storeKey',$lang->id)); ?>" method="post">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="key" class="control-label font-weight-bold"><?php echo app('translator')->get('Key'); ?></label>

                            <input type="text" class="form-control form-control-lg " id="key" name="key"
                                   placeholder="" value="<?php echo e(old('key')); ?>">

                        </div>
                        <div class="form-group">
                            <label for="value" class="control-label font-weight-bold"><?php echo app('translator')->get('Value'); ?></label>
                            <input type="text" class="form-control form-control-lg " id="value" name="value"
                                   placeholder="" value="<?php echo e(old('value')); ?>">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal"><?php echo app('translator')->get('Close'); ?></button>
                        <button type="submit" class="btn btn-primary"> <?php echo app('translator')->get('Save'); ?></button>
                    </div>
                </form>

            </div>
        </div>
    </div>


<?php $__env->stopSection(); ?>

<?php $__env->startPush('style-lib'); ?>
    <link href="<?php echo e(asset('assets/admin/css/dataTables.bootstrap4.css')); ?>" rel="stylesheet">
<?php $__env->stopPush(); ?>

<?php $__env->startPush('js'); ?>
    <script src="<?php echo e(asset('assets/admin/js/jquery.dataTables.min.js')); ?>"></script>
    <script>
        "use strict";
        $(document).ready(function (e) {
            $('#zero_config').DataTable();
        });

        $(document).on('click', '.deleteKey', function () {
            var modal = $('#deleteModal');
            modal.find('input[name=key]').val($(this).data('key'));
            modal.find('input[name=value]').val($(this).data('value'));
        });

        $(document).on('click', '.editModal', function () {
            var modal = $('#editModal');
            modal.find('.form-title').text($(this).data('title'));
            modal.find('input[name=key]').val($(this).data('key'));
            modal.find('input[name=value]').val($(this).data('value'));
        });


        $(document).on('click', '.import-language', function () {
            var id = $('.select-language').val();

            if (id == '') {
                Notiflix.Notify.Failure("<?php echo e(trans('Please Select a language to Import')); ?>");
                return 0;
            } else {
                $.ajax({
                    type: "post",
                    url: "<?php echo e(route('admin.language.importJson')); ?>",
                    data: {
                        id: id,
                        myLangid: "<?php echo e($lang->id); ?>",
                        _token: "<?php echo e(csrf_token()); ?>"
                    },
                    success: function (data) {
                        console.log(data);

                        if (data == 'success') {
                            Notiflix.Notify.Success("<?php echo e(trans('Import Data Successfully')); ?>");

                            window.location.href = "<?php echo e(url()->current()); ?>"
                        }
                    }
                    ,
                    error: function (res) {

                    }
                });
            }
        });


        $(document).ready(function () {
            $('select').select2({
                selectOnClose: true
            });
        });
    </script>

    <?php if($errors->any()): ?>
        <?php
            $collection = collect($errors->all());
            $errors = $collection->unique();
        ?>
        <script>
            "use strict";
            <?php $__currentLoopData = $errors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            Notiflix.Notify.Failure("<?php echo e(trans($error)); ?>");
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </script>
    <?php endif; ?>


<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/runner/workspace/resources/views/admin/language/keyword.blade.php ENDPATH**/ ?>