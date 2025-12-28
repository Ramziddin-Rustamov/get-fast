<?php $__env->startSection('title', 'Clients List'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <h1 class="text-center mb-4">👤 <?php echo e(__("Mijozlar")); ?></h1>

    
    <div class="row mb-3">
        <div class="col-md-12 d-flex flex-wrap align-items-center">
            <form action="<?php echo e(route('clients.index')); ?>" method="GET" class="d-flex flex-wrap gap-2">
                <input type="text" name="search" class="form-control me-2 mb-2"
                       placeholder="🔍 Client qidiring..." value="<?php echo e($search); ?>">

                <div class="btn-group me-2 mb-2" role="group">
                    <button type="submit" name="status" value="" class="btn btn-outline-secondary <?php echo e($status == '' ? 'active' : ''); ?>">
                        Barchasi
                    </button>
                    <button type="submit" name="status" value="0" class="btn btn-outline-dark <?php echo e($status != '0' ? 'active' : ''); ?>">
                        Tasdiqlanmagan
                    </button>
                    <button type="submit" name="status" value="1" class="btn btn-outline-warning <?php echo e($status == '1' ? 'active' : ''); ?>">
                        Tasdiqlangan
                    </button>
                  
                </div>
            </form>

            <a href="<?php echo e(route('clients.create')); ?>" class="btn btn-success ms-auto mb-2">
                <i class="fas fa-plus"></i> Yangi Client qo‘shish
            </a>
        </div>
    </div>

    
    <div class="table-responsive shadow rounded">
        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Ismi</th>
                    <th>Telefon</th>
                    <th>Ro‘li</th>
                    <th>Sms orqali tasdiqlanish</th>
                    <th>Holati</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="text-center">
                        <td><?php echo e($client->id); ?></td>
                        <td><?php echo e($client->first_name); ?></td>
                        <td><?php echo e($client->phone); ?></td>

                        <td><?php echo e(ucfirst($client->role)); ?></td>

                        <td>
                            <?php if($client->is_verified): ?>
                                <span class="badge bg-success">Tasdiqlangan</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Tasdiqlanmagan</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php
                                $statusColors = [
                                    'none' => 'bg-secondary',
                                    'pending' => 'bg-warning text-dark',
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    'blocked' => 'bg-dark text-white'
                                ];
                                $statusValue = $client->verification_status ?? 'none';
                                $badgeClass = $statusColors[$statusValue] ?? 'bg-secondary';
                            ?>

                            <span class="badge <?php echo e($badgeClass); ?> px-3 py-2 rounded-pill">
                                <?php echo e(ucfirst($statusValue)); ?>

                            </span>
                        </td>

                        <td>
                            <a href="<?php echo e(route('clients.show', $client->id)); ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Clientlar topilmadi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="d-flex justify-content-center mt-4">
        <?php echo e($clients->links('pagination::bootstrap-5')); ?>

    </div>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/get-fast/resources/views/admin-views/clients/index.blade.php ENDPATH**/ ?>