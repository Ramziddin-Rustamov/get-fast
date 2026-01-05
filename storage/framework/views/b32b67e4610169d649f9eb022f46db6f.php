<?php $__env->startSection('title', 'Add New Client'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <a href="<?php echo e(route('clients.index')); ?>" class="btn btn-secondary mb-3">⬅ Back to Clients List</a>

    <div class="card shadow-sm rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
            <h4 class="mb-0"><i class="fas fa-user-plus"></i> Add New Client</h4>
        </div>
        <div class="card-body">
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?php echo e(route('clients.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>

                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" id="first_name" class="form-control" 
                           value="<?php echo e(old('first_name')); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" 
                           value="<?php echo e(old('last_name')); ?>">
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                    <input type="text" name="phone" id="phone" class="form-control" 
                           value="<?php echo e(old('phone')); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="is_verified" name="is_verified" value="1" checked>
                    <label class="form-check-label" for="is_verified">Verified</label>
                </div>

                <div class="mb-3">
                    <label for="verification_status" class="form-label">Verification Status</label>
                    <select name="verification_status" id="verification_status" class="form-select">
                        <option value="approved" selected>Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Add Client
                </button>
            </form>
        </div>
    </div>
</div>


<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/get-fast/resources/views/admin-views/clients/create.blade.php ENDPATH**/ ?>