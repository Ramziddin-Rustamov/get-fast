<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row d-flex justify-content-center align-items-center vh-100">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header text-center"><?php echo e(__('Kirish')); ?></div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('auth.login')); ?>">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Telefon raqam</label>
                            <input id="phone" type="text" name="phone" class="form-control" required value="<?php echo e(old('phone')); ?>">
                            <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Parol</label>
                            <input id="password" type="password" name="password" class="form-control" required>
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <span class="text-danger"><?php echo e($message); ?></span>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Kirish</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/get-fast/resources/views/auth/login.blade.php ENDPATH**/ ?>