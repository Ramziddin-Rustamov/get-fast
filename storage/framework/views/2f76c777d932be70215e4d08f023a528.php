<?php $__env->startSection('title', 'Company Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">

    <h2 class="fw-bold mb-4">
        <i class="bi bi-speedometer2"></i> Company Dashboard
    </h2>

    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-secondary">Current Balance</h6>
                    <h2 class="fw-bold text-success"><?php echo e(number_format($company->balance) ?? '0'); ?> UZS</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-secondary">Total Income</h6>
                    <h2 class="fw-bold text-primary"><?php echo e(number_format($company->total_income) ?? '0'); ?> UZS</h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-secondary">Today Income</h6>
                    <h2 class="fw-bold text-warning"><?php echo e(number_format($todayIncome) ?? '0'); ?> UZS</h2>
                </div>
            </div>
        </div>
    </div>



    
    <h4 class="mt-5 fw-bold">Bookings Statistics</h4>
    <div class="row g-4">

        <?php
            $bookingStats = [
                ['title' => 'Total Bookings', 'count' => $totalBookings, 'color' => 'primary'],
                ['title' => 'Confirmed', 'count' => $confirmedBookings, 'color' => 'success'],
                ['title' => 'Cancelled', 'count' => $cancelledBookings, 'color' => 'danger'],
                ['title' => 'Completed', 'count' => $completedBookings, 'color' => 'info'],
            ];
        ?>

        <?php $__currentLoopData = $bookingStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-2">
            <div class="card shadow rounded-4 border-0">
                <div class="card-body text-center">
                    <h6 class="text-<?php echo e($b['color']); ?>"><?php echo e($b['title']); ?></h6>
                    <h2 class="fw-bold"><?php echo e($b['count']); ?></h2>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>



    
    <h4 class="mt-5 fw-bold">Users Overview</h4>

    <div class="row g-4">

        <div class="col-md-3">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-info">Total Clients</h6>
                    <h2 class="fw-bold"><?php echo e($totalClients); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-warning">Total Drivers</h6>
                    <h2 class="fw-bold"><?php echo e($totalDrivers); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-success">Active Users</h6>
                    <h2 class="fw-bold"><?php echo e($activeUsers); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-danger">Inactive Users</h6>
                    <h2 class="fw-bold"><?php echo e($inactiveUsers); ?></h2>
                </div>
            </div>
        </div>

    </div>



    
    <h4 class="mt-5 fw-bold">Driver Verification Status</h4>

    <div class="row g-4">

        <?php
            $driverStats = [
                ['title' => 'Approved', 'count' => $driversApproved, 'color' => 'success'],
                ['title' => 'Rejected', 'count' => $driversRejected, 'color' => 'danger'],
                ['title' => 'Pending', 'count' => $driversPending, 'color' => 'warning'],
                ['title' => 'Blocked', 'count' => $driversBlocked, 'color' => 'dark'],
            ];
        ?>

        <?php $__currentLoopData = $driverStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-3">
            <div class="card shadow rounded-4 border-0">
                <div class="card-body text-center">
                    <h6 class="text-<?php echo e($d['color']); ?>"><?php echo e($d['title']); ?></h6>
                    <h2 class="fw-bold"><?php echo e($d['count']); ?></h2>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    </div>



    
    <h4 class="mt-5 fw-bold">Cards Overview</h4>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card shadow rounded-4">
                <div class="card-body text-center">
                    <h6 class="text-primary">Total Cards</h6>
                    <h2 class="fw-bold"><?php echo e($totalCards); ?></h2>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/get-fast/resources/views/admin-views/company/dashboard.blade.php ENDPATH**/ ?>