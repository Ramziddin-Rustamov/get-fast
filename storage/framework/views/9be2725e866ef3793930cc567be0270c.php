<?php $__env->startSection('title', 'Driver Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">

    <a href="<?php echo e(route('drivers.index')); ?>" class="btn btn-secondary mt-3">Back to List</a>
    <h1 class="mb-4 text-center">🚖 Driver Details</h1>

    <?php if(session('success')): ?>
    <div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if(session('error')): ?>
    <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
<?php endif; ?>

  
<div class="card mb-4 shadow-lg border-0 rounded-4">
    <div class="card-header bg-primary text-white rounded-top-4">
        <h4 class="mb-0"><i class="fas fa-user"></i> <?php echo e($driver->first_name); ?> <?php echo e($driver->last_name); ?></h4>
    </div>
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-6">
                <p class="mb-1"><strong>📞 Telefon:</strong> <span class="text-dark"><?php echo e($driver->phone); ?></span></p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>🛡 Rol:</strong> 
                    <span class="badge bg-primary text-white px-3 py-2 rounded-pill"><?php echo e(ucfirst($driver->role)); ?></span>
                </p>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6">
                <p class="mb-1"><strong>✅ Status:</strong> 
                    <?php if($driver->is_verified): ?>
                        <span class="badge bg-success px-3 py-2 rounded-pill">Tasdiqlangan</span>
                    <?php else: ?>
                        <span class="badge bg-danger px-3 py-2 rounded-pill">Tasdiqlanmagan</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>🌍 Region:</strong> <?php echo e($driver->region->name ?? 'N/A'); ?></p>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-6">
                <p class="mb-1"><strong>🏘 District:</strong> <?php echo e($driver->district->name ?? 'N/A'); ?></p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>📌 Quarter:</strong> <?php echo e($driver->quarter->name ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
</div>






<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">💸 Transfer Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="<?php echo e(route('drivers.transfer', $driver->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" name="amount" id="amount" class="form-control" min="1000" 
                               max="<?php echo e($driver->balance->sum('balance')); ?>" placeholder="Enter amount">
                    </div>

                    <div class="mb-3">
                        <label for="card_number" class="form-label">Kartasi</label>
                        <select name="card_id" id="card_id" class="form-control">
                            <?php $__currentLoopData = $driver->cards->where('status', 'verified'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($card->id); ?>"><?php echo e($card->number); ?> - <?php echo e($card->expiry_month); ?>/<?php echo e($card->expiry); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>


                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea name="note" id="note" class="form-control" rows="2" placeholder="Optional note"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">🏧 Withdraw Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
            <form action="<?php echo e(route('users.admin.withdraw', $driver->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="minus">

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number"
                               name="amount"
                               class="form-control"
                               min="1"
                               max="<?php echo e($driver->balance->balance); ?>"
                               placeholder="Enter amount"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="note"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Withdraw sababi"></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-minus-circle"></i> Withdraw
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">💰 Pay Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form action="<?php echo e(route('users.admin.balance.add', $driver->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="plus">

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number"
                               name="amount"
                               class="form-control"
                               min="1"
                               placeholder="Enter amount"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Pay izohi"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus-circle"></i> Pay
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

   
   <div class="card mb-4 shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title">💰 Balance</h5>
            <p class="fs-4">So'm <?php echo e(number_format($driver->balance->balance, 2, '.', ' ') ?? '0'); ?></p>
        </div>

                 
                <button class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#transferModal">
                <i class="fas fa-exchange-alt"></i> Transfer to card
                </button>

                
                <button class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#withdrawModal">
                <i class="fas fa-minus-circle"></i> Withdraw
                </button>

                
                <button class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#payModal">
                <i class="fas fa-plus-circle"></i> Pay by Company Account
    </button>
    </div>
</div>




<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="card-title">💳 Active Card</h5>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Kartasi</th>
                        <th>Expire</th>
                        <th>status</th>
                        <th>Ulangan nomer</th>

                        <th>Yaratilgan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $driver->cards->where('status', 'verified'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="text-center">
                        <td><?php echo e($card->id); ?></td>
                        <td><?php echo e($card->number); ?></td>
                        <td><?php echo e($card->expiry); ?></td>
                        <td><?php echo e($card->status); ?></td>
                        <td><?php echo e($card->phone); ?></td>
                        <td><?php echo e($card->created_at->format('Y-m-d')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>  




<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="card-title">💳 Pul harakatlari (<?php echo e($driver->balanceTransactions()->count()); ?>)</h5>

        <?php if($balanceTransactions->count()): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>#</th>
                            <th>Tur</th>
                            <th>Summa</th>
                            <th>Balans oldin</th>
                            <th>Balans keyin</th>
                            <th>Trip ID</th>
                            <th>Holat</th>
                            <th>Sabab / Izoh</th>
                            <th>Sana</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $balanceTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="text-center">
                            <td><?php echo e($transaction->id); ?></td>
                            <td>
                                <span class="badge <?php echo e($transaction->type === 'debit' ? 'bg-danger' : 'bg-success'); ?>">
                                    <?php echo e($transaction->type === 'debit' ? 'Chiqim' : 'Kirim'); ?>

                                </span>
                            </td>
                            <td>So'm <?php echo e(number_format($transaction->amount, 2, '.', ' ')); ?></td>
                            <td>So'm <?php echo e(number_format($transaction->balance_before, 2, '.', ' ')); ?></td>
                            <td>So'm <?php echo e(number_format($transaction->balance_after, 2, '.', ' ')); ?></td>
                            <td><?php echo e($transaction->trip_id ?? '-'); ?></td>
                            <td>
                                <?php if($transaction->status === 'success'): ?>
                                    <span class="badge bg-success">Muvaffaqiyatli</span>
                                <?php elseif($transaction->status === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Kutilmoqda</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Xato</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($transaction->reason ?? '-'); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($transaction->created_at)->format('d.m.Y H:i')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            
            <?php if($balanceTransactions->hasPages()): ?>
            <div class="d-flex justify-content-center mt-3">
                <?php echo e($balanceTransactions->links('pagination::bootstrap-5')); ?>

            </div>
            <?php endif; ?>

        <?php else: ?>
            <p class="text-muted text-center">Hozircha pul harakatlari mavjud emas.</p>
        <?php endif; ?>
    </div>
</div>





    
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">🗓 Trips (<?php echo e($driver->driverTrips->count()); ?>)</h5>

            <?php if($driver->driverTrips->count()): ?>
                <?php $__currentLoopData = $driver->driverTrips->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border rounded p-3 mb-3">

                        
                        <div 
                            class="d-flex justify-content-between align-items-center" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#trip_<?php echo e($trip->id); ?>" 
                            style="cursor: pointer;"
                        >
                            <div>
                                <div><strong>From:</strong> <?php echo e($trip->startQuarter->name ?? 'N/A'); ?>, <?php echo e($trip->startQuarter->district->name_uz ?? ''); ?></div>
                                <div><strong>To:</strong> <?php echo e($trip->endQuarter->name ?? 'N/A'); ?>, <?php echo e($trip->endQuarter->district->name ?? ''); ?></div>
                                <div><strong>Time:</strong> 
                                    <?php echo e(\Carbon\Carbon::parse($trip->start_time)->format('d.m.Y H:i')); ?> - 
                                    <?php echo e(\Carbon\Carbon::parse($trip->end_time)->format('d.m.Y H:i')); ?>

                                </div>
                                <div><strong>Price:</strong> <?php echo e(number_format($trip->price_per_seat, 0, '.', ' ')); ?> so'm</div>
                                <div><strong>Seats:</strong> <?php echo e($trip->available_seats); ?> available / <?php echo e($trip->total_seats); ?> seats </div>
                            </div>

                            <span class="badge <?php echo e($trip->status === 'cancelled' ? 'bg-danger' : 'bg-success'); ?>">
                                <?php echo e(ucfirst($trip->status)); ?>

                            </span>
                        </div>

                        
                        <div id="trip_<?php echo e($trip->id); ?>" class="collapse mt-3">

                            <?php if($trip->bookings->count()): ?>
                                <div class="p-3 bg-light rounded">

                                    <h6 class="mb-2">📌 Bookings (<?php echo e($trip->bookings->count()); ?>)</h6>

                                    <?php $__currentLoopData = $trip->bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="border rounded p-2 mb-2 bg-white">
                                            <div><strong>User:</strong> <?php echo e($booking->user->first_name ?? 'N/A'); ?> <?php echo e($booking->user->last_name ?? ''); ?></div>
                                            <div><strong>Phone:</strong> <?php echo e($booking->user->phone ?? 'N/A'); ?></div>
                                            <div><strong>Seats:</strong> <?php echo e($booking->seats); ?></div>
                                            <div><strong>Total Price:</strong> 
                                                <?php echo e(number_format($booking->total_price, 0, '.', ' ')); ?> so'm
                                            </div>
                                            <div><strong>Status:</strong>
                                                <span class="badge 
                                                    <?php echo e($booking->status == 'cancelled' ? 'bg-danger' : 'bg-primary'); ?>">
                                                    <?php echo e(ucfirst($booking->status)); ?>

                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                </div>

                            <?php else: ?>
                                <p class="text-muted">No bookings for this trip.</p>
                            <?php endif; ?>

                        </div>

                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <p class="text-muted">No trips available.</p>
            <?php endif; ?>
        </div>
    </div>




   
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="card-title d-flex justify-content-between">
            <span>📄 Haydovchi Hujjatlari</span>

            
            <form action="<?php echo e(route('driver.images.deleteAll', $driver->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button class="btn btn-danger btn-sm"
                        onclick="return confirm('Hamma hujjatlar o‘chirilsinmi?')">
                    Hamma Hujjatlarni O‘chirish
                </button>
            </form>
        </h5>

        <div class="row">

            <?php $__currentLoopData = $driverImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4 mb-3">
                    <div class="border rounded p-2 text-center shadow-sm">

                        <p class="fw-bold text-capitalize mb-1">
                            <?php echo e(str_replace('_', ' ', $img->type)); ?> 
                            <?php if($img->side): ?>
                                (<?php echo e(ucfirst($img->side)); ?>)
                            <?php endif; ?>
                        </p>

                        <img src="<?php echo e(asset('storage/' . $img->image_path)); ?>"
                             class="img-fluid rounded shadow-sm doc-preview"
                             style="cursor: zoom-in; max-height: 200px; object-fit: cover;"
                             data-bs-toggle="modal"
                             data-bs-target="#imageModal"
                             data-img="<?php echo e(asset('storage/' . $img->image_path)); ?>"
                        >

                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>
    </div>
</div>


  
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="card-title">🚘 Moshinalar</h5>

        <?php if($vehicles->count()): ?>
            <?php $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <div class="border rounded p-3 mb-3">

                    
                    <div class="d-flex justify-content-between align-items-center"
                         data-bs-toggle="collapse"
                         data-bs-target="#vehicle_<?php echo e($vehicle->id); ?>"
                         style="cursor: pointer;">
                        
                        <div>
                            <p class="mb-1"><strong>Model:</strong> <?php echo e($vehicle->model); ?></p>
                            <p class="mb-1"><strong>Color:</strong> <?php echo e($vehicle->color->title_uz); ?></p>
                            <p class="mb-1"><strong>Seats:</strong> <?php echo e($vehicle->seats); ?></p>
                            <p class="mb-1"><strong>Raqami:</strong> <?php echo e($vehicle->car_number); ?></p>
                            <p class="mb-1"><strong>License Plate:</strong> <?php echo e($vehicle->tech_passport_number); ?></p>
                        </div>

                        <span class="badge bg-primary">Rasmlarni Ko‘rish</span>
                    </div>

                    <?php
                    $images = $vehicleImages->where('vehicle_id', $vehicle->id);
                ?>

                <?php if($images->count()): ?>

                    
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold">📸 Moshina Rasmlari</h6>

                        <form action="<?php echo e(route('vehicle.images.deleteAll', $vehicle->id)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-danger btn-sm"
                                    onclick="return confirm('Hamma moshina rasmlari o‘chirilsinmi?')">
                                O‘chirish
                            </button>
                        </form>
                    </div>

                    <div class="row">
                        <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vimg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-md-3 mb-3">
                                <div class="border rounded p-2 text-center shadow-sm">

                                    <p class="fw-bold mb-1">
                                        <?php echo e(str_replace('_', ' ', $vimg->type)); ?>

                                        <?php if($vimg->side): ?>
                                            (<?php echo e(ucfirst($vimg->side)); ?>)
                                        <?php endif; ?>
                                    </p>

                                    <img src="<?php echo e(asset('storage/' . $vimg->image_path)); ?>"
                                         class="img-fluid rounded shadow-sm vehicle-preview"
                                         style="cursor: zoom-in; max-height: 160px; object-fit: cover;"
                                         data-bs-toggle="modal"
                                         data-bs-target="#imageModal"
                                         data-img="<?php echo e(asset('storage/' . $vimg->image_path)); ?>">
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                <?php else: ?>
                    <p class="text-muted">Rasmlar mavjud emas.</p>
                <?php endif; ?>



                </div>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php if($vehicles->hasPages()): ?>
                <div class="d-flex justify-content-center mt-3">
                    <?php echo e($vehicles->links('pagination::bootstrap-5')); ?>

                </div>
            <?php endif; ?>

        <?php else: ?>
            <p class="text-muted">No vehicles assigned.</p>
        <?php endif; ?>
    </div>
</div>





   
    <div class="card mb-4 shadow-sm">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
            <h5 class="card-title mb-2">🚦 Haydovchi statusi</h5>

            <form action="<?php echo e(route('drivers.updateStatus', $driver->id)); ?>" method="POST" class="d-flex align-items-center gap-2 mb-2">
                <?php echo csrf_field(); ?>

                <select name="status" class="form-select form-select-sm">
                    <option value="none" <?php echo e($driver->driving_verification_status == 'none' ? 'selected' : ''); ?>>None</option>
                    <option value="pending" <?php echo e($driver->driving_verification_status == 'pending' ? 'selected' : ''); ?>>Pending</option>
                    <option value="approved" <?php echo e($driver->driving_verification_status == 'approved' ? 'selected' : ''); ?>>Approved</option>
                    <option value="rejected" <?php echo e($driver->driving_verification_status == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                    <option value="blocked" <?php echo e($driver->driving_verification_status == 'blocked' ? 'selected' : ''); ?>>Blocked</option>
                </select>

                <button type="submit" class="btn btn-sm btn-success">
                    <i class="fas fa-check"></i> Saqlash
                </button>
            </form>

            <div>
                <strong>Joriy status:</strong>
                <?php
                    $statusColor = match($driver->driving_verification_status) {
                        'none' => 'bg-secondary',
                        'pending' => 'bg-warning text-dark',
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger',
                        'blocked' => 'bg-dark',
                        default => 'bg-secondary'
                    };
                ?>
                <span class="badge <?php echo e($statusColor); ?> px-3 py-2 rounded-pill">
                    <?php echo e(ucfirst($driver->driving_verification_status)); ?>

                </span>
            </div>
        </div>
    </div>


    

        
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">✉ Send SMS to Driver</h5>

            <form action="<?php echo e(route('drivers.sendSms', $driver->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea name="message" id="message" class="form-control" rows="3" placeholder="Type your message..."></textarea>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

</div>


<script>
    document.addEventListener('click', function(e) {
        if (e.target.matches('.doc-preview') || e.target.matches('.vehicle-preview')) {
            document.getElementById('modalImage').src = e.target.dataset.img;
        }
    });
    </script>


<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/get-fast/resources/views/admin-views/drivers/show.blade.php ENDPATH**/ ?>