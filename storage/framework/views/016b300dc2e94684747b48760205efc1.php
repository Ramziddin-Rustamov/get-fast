<?php $__env->startSection('title', 'Client Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">

    <a href="<?php echo e(route('clients.index')); ?>" class="btn btn-secondary mt-3">⬅ Back to List</a>
    <h1 class="mb-4 text-center">👤 Client Details</h1>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>


    
    <div class="card mb-4 shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white rounded-top-4">
            <h4 class="mb-0">
                <i class="fas fa-user"></i> 
                <?php echo e($client->first_name); ?> <?php echo e($client->last_name); ?>

            </h4>
        </div>

        <div class="card-body">

            <div class="row mb-2">
                <div class="col-md-6">
                    <p><strong>📞 Telefon:</strong> <?php echo e($client->phone); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>🛡 Rol:</strong> 
                        <span class="badge bg-info px-3 py-2 rounded-pill">
                            <?php echo e(ucfirst($client->role)); ?>

                        </span>
                    </p>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <p>
                        <strong>📍 Region:</strong> 
                        <?php echo e($client->region->name_uz ?? 'N/A'); ?>

                    </p>
                </div>
                <div class="col-md-6">
                    <p>
                        <strong>🏘 District:</strong> 
                        <?php echo e($client->region->district->name_uz ?? 'N/A'); ?>

                    </p>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-6">
                    <p>
                        <strong>📌 Quarter:</strong> 
                        <?php echo e($client->region->district->quarter->name ?? 'N/A'); ?>

                    </p>
                </div>

                <div class="col-md-6">
                    <p>
                        <strong>✔ SMS Tasdiqlanganmi:</strong>
                        <?php if($client->is_verified): ?>
                            <span class="badge bg-success px-3 py-2 rounded-pill">Ha</span>
                        <?php else: ?>
                            <span class="badge bg-danger px-3 py-2 rounded-pill">Yo'q</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

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
                    <?php $__currentLoopData = $client->cards->where('status', 'verified'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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



<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">💸 Transfer Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="<?php echo e(route('clients.transfer', $client->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number"
                        name="amount"
                        id="amount"
                        class="form-control"
                        min="1000"
                        max="<?php echo e($client->balance?->balance); ?>"
                        placeholder="Enter amount">
                    </div>

                    <div class="mb-3">
                        <label for="card_number" class="form-label">Kartasi</label>
                        <select name="card_id" id="card_id" class="form-control">
                            <?php $__currentLoopData = $client->cards->where('status', 'verified'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
            <form action="<?php echo e(route('users.admin.withdraw', $client->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="minus">

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number"
                               name="amount"
                               class="form-control"
                               min="1"
                               max="<?php echo e($client->balance?->balance); ?>"
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
                <form action="<?php echo e(route('users.admin.balance.add', $client->id)); ?>" method="POST">
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



    
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <h4 class="card-title mb-3">
                <i class="fas fa-calendar-check text-primary"></i>
                <strong>Buyurtmalar</strong> 
                <span class="badge bg-primary ms-2"><?php echo e($client->bookings->count()); ?></span>
            </h4>
    
            <?php if($client->bookings->count()): ?>
                <?php $__currentLoopData = $client->bookings->sortByDesc('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border rounded p-3 mb-3 bg-white shadow-sm">
    
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-dark px-3 py-2">#<?php echo e($booking->id); ?></span>
    
                            <span class="badge 
                                <?php echo e($booking->status === 'expired' ? 'bg-danger' : ($booking->status === 'cancelled' ? 'bg-secondary' : 'bg-success')); ?>  
                                px-3 py-2">
                                <i class="fas fa-info-circle"></i> 
                                <?php echo e(ucfirst($booking->status)); ?>

                            </span>
                        </div>
    
                        <hr>
    
                        
                        <div class="row mb-2">
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-users text-primary"></i>
                                <strong>Seats:</strong>
                                <?php echo e($booking->seats_booked); ?>

                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-money-bill-wave text-success"></i>
                                <strong>Total Price:</strong>
                                <?php echo e(number_format($booking->total_price, 0, '.', ' ')); ?> so'm
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-clock text-info"></i>
                                <strong>Created:</strong>
                                <?php echo e($booking->created_at->format('d.m.Y H:i')); ?>

                            </div>
    
                        </div>
    
                        
                        <div class="mt-3">
                            <h6 class="fw-bold">
                                <i class="fas fa-user-friends text-primary"></i>
                                Yo‘lovchilar (<?php echo e($booking->passengers->count()); ?>)
                            </h6>
    
                            <?php if($booking->passengers->count()): ?>
                                <ul class="list-group mt-2">
                                    <?php $__currentLoopData = $booking->passengers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-user-circle me-2 text-secondary"></i>
                                                <strong><?php echo e($p->name); ?></strong>
                                            </div>
                                            <div>
                                                <i class="fas fa-phone-alt text-success me-1"></i>
                                                <?php echo e($p->phone); ?>

                                            </div>
                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            <?php else: ?>
                                <p class="text-muted mt-1">Yo‘lovchi qo‘shilmagan.</p>
                            <?php endif; ?>
                        </div>
    
                        <hr>
    
                        
                        <div class="row mb-2">
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-map-marker-alt text-danger"></i>
                                <strong>From:</strong><br>
                                <?php echo e($booking->trip->startQuarter->name ?? 'N/A'); ?> —
                                <?php echo e($booking->trip->startQuarter->district->name_uz ?? ''); ?>,
                                <?php echo e($booking->trip->startQuarter->district->region->name_uz ?? ''); ?>

                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-flag-checkered text-success"></i>
                                <strong>To:</strong><br>
                                <?php echo e($booking->trip->endQuarter->name ?? 'N/A'); ?> —
                                <?php echo e($booking->trip->endQuarter->district->name_uz ?? ''); ?>,
                                <?php echo e($booking->trip->endQuarter->district->region->name_uz ?? ''); ?>

                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-hourglass-start text-primary"></i>
                                <strong>Start Time:</strong>
                                <?php echo e(\Carbon\Carbon::parse($booking->trip->start_time)->format('d.m.Y H:i')); ?>

                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-hourglass-end text-danger"></i>
                                <strong>End Time:</strong>
                                <?php echo e(\Carbon\Carbon::parse($booking->trip->end_time)->format('d.m.Y H:i')); ?>

                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-money-check-alt text-success"></i>
                                <strong>Seat Price:</strong>
                                <?php echo e(number_format($booking->trip->price_per_seat, 0, '.', ' ')); ?> so'm
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-calendar-plus text-secondary"></i>
                                <strong>Trip Created:</strong>
                                <?php echo e(\Carbon\Carbon::parse($booking->trip->created_at)->format('d.m.Y H:i')); ?>

                            </div>
    
                        </div>
    
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

              
            <div class="d-flex justify-content-center mt-3">
                <?php echo e($bookings->links('pagination::bootstrap-5')); ?>

            </div>
            <?php else: ?>
                <p class="text-muted">Hozircha buyurtmalar mavjud emas.</p>
            <?php endif; ?>
        </div>
    </div>
    



   
   <div class="card mb-4 shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title">💰 Balance</h5>
            <p class="fs-4">
                So'm <?php echo e(number_format($client->balance?->balance ?? 0, 2, '.', ' ')); ?>

            </p>

              
         
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
        </div>
        
        

        
        
    </div>
</div>


<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="card-title">💳 Pul harakatlari (<?php echo e($client->balanceTransactions()->count()); ?>)</h5>

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
            <h5 class="card-title">✉ Clientga SMS yuborish</h5>

            <form action="<?php echo e(route('clients.sendSms', $client->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label for="message" class="form-label">Xabar</label>
                    <textarea name="message" id="message" class="form-control" rows="3"
                              placeholder="Xabar yozing..."></textarea>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Yuborish
                </button>
            </form>
        </div>
    </div>

</div>


<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/get-fast/resources/views/admin-views/clients/show.blade.php ENDPATH**/ ?>