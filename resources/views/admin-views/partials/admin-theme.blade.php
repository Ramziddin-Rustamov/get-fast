{{-- Umumiy admin dizayn uslublari. @once tufayli sahifada faqat bir marta chiqadi.
     --k-acc-1/--k-acc-2 va .btn-k layout (app.blade.php) da global aniqlangan. --}}
@once
@push('styles')
<style>
    .admin-page { font-family: 'Nunito', system-ui, sans-serif; color: #0b1324; }
    .admin-page .page-title { font-family: 'Sora','Nunito',sans-serif; font-weight: 800; letter-spacing: -.02em; }
    .admin-page .crumb {
        display: inline-flex; align-items: center; gap: .5rem; font-weight: 700;
        color: #475569; text-decoration: none; padding: .5rem .9rem; border-radius: 12px;
        background: #fff; border: 1px solid #e7edf4; transition: .2s;
    }
    .admin-page .crumb:hover { color: #0ea5e9; border-color: #bae6fd; }

    /* Kartalar */
    .admin-page .a-card {
        background: #fff; border: 1px solid #eef2f7; border-radius: 20px;
        box-shadow: 0 22px 48px -32px rgba(11,19,36,.5); overflow: hidden; margin-bottom: 1.5rem;
    }
    .admin-page .a-card-head {
        display: flex; align-items: center; gap: .6rem; padding: 1rem 1.25rem;
        font-family: 'Sora','Nunito',sans-serif; font-weight: 800; font-size: 1.02rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .admin-page .a-card-head .ic { width: 38px; height: 38px; border-radius: 11px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1rem; }
    .admin-page .a-card-head.grad { color: #fff; border: none; }
    .admin-page .a-card-head.grad .ic { background: rgba(255,255,255,.2); }
    .admin-page .grad-green { background: linear-gradient(135deg, var(--k-acc-1), var(--k-acc-2)); }
    .admin-page .grad-blue  { background: linear-gradient(135deg, #0ea5e9, #6366f1); }
    .admin-page .grad-red   { background: linear-gradient(135deg, #fb7185, #ef4444); }
    .admin-page .grad-amber { background: linear-gradient(135deg, #fbbf24, #f59e0b); }
    .admin-page .a-card-body { padding: 1.25rem; }

    /* Jadval */
    .admin-page .a-table { margin: 0; }
    .admin-page .a-table thead th {
        background: #0b1324; color: #cbd5e1; font-weight: 700; font-size: .76rem;
        text-transform: uppercase; letter-spacing: .5px; border-color: #0b1324; white-space: nowrap; padding: .85rem 1rem;
    }
    .admin-page .a-table tbody td { vertical-align: middle; padding: .8rem 1rem; border-color: #eef2f7; }
    .admin-page .a-table tbody tr:hover { background: #f8fafc; }

    /* Forma inputlari */
    .admin-page .a-input, .admin-page .form-control, .admin-page .form-select {
        border-radius: 12px; border: 1px solid #e2e8f0; padding: .65rem .9rem;
    }
    .admin-page .form-control:focus, .admin-page .form-select:focus {
        border-color: var(--k-acc-2); box-shadow: 0 0 0 .2rem rgba(14,165,233,.15);
    }
    .admin-page .form-label { font-weight: 700; color: #334155; font-size: .9rem; }

    /* Statistik plitkalar */
    .admin-page .stat-tile {
        background: #fff; border: 1px solid #eef2f7; border-radius: 18px; padding: 1.1rem 1.3rem;
        display: flex; align-items: center; gap: 1rem; height: 100%;
    }
    .admin-page .stat-tile .si { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.4rem; flex-shrink: 0; }
    .admin-page .stat-tile .n { font-family: 'Sora'; font-weight: 800; font-size: 1.5rem; line-height: 1; }
    .admin-page .stat-tile .l { color: #64748b; font-size: .85rem; font-weight: 600; }

    /* Yumshoq tugmalar va piller */
    .admin-page .pill { border-radius: 999px; padding: .35rem .85rem; font-weight: 700; font-size: .76rem; display: inline-flex; align-items: center; gap: .35rem; }
    .admin-page .btn-soft { border-radius: 11px; font-weight: 700; }
    .admin-page .icon-badge { width: 44px; height: 44px; border-radius: 13px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.15rem; }

    /* Segment filtr */
    .admin-page .seg .btn { border-radius: 11px !important; font-weight: 700; }
    .admin-page .alert { border-radius: 14px; border: none; }
</style>
@endpush
@endonce
