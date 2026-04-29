<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Keuangan UP | SMK Taruna Bangsa</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --smk-orange: #ff6600;
            --income-green: #28a745;
            --expense-red: #dc3545;
        }

        body { 
            background-color: #f4f7f6; 
            color: #333; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px; 
            overflow-x: hidden; 
        }
        
        /* Navbar */
        .navbar { 
            background: #fff !important; 
            border-bottom: 3px solid var(--smk-orange); 
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand { 
            font-size: 14px; 
            font-weight: 800;
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .logo-img { width: 40px; height: auto; }

        /* Statistik Horizontal Scroll */
        .stat-container { 
            display: flex; 
            overflow-x: auto; 
            gap: 12px; 
            padding: 10px 5px;
            -webkit-overflow-scrolling: touch;
        }
        .stat-container::-webkit-scrollbar { display: none; } /* Sembunyikan scrollbar */

        .stat-card { 
            min-width: 170px; 
            background: #fff; 
            border-radius: 16px; 
            padding: 18px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            flex: 1;
            border: none;
        }
        .stat-label { font-size: 10px; color: #888; text-transform: uppercase; font-weight: 700; margin-bottom: 5px; }
        .stat-value { font-size: 16px; font-weight: 800; margin: 0; white-space: nowrap; }
        
        .border-income { border-top: 4px solid var(--income-green); }
        .border-expense { border-top: 4px solid var(--expense-red); }
        .border-balance { border-top: 4px solid var(--smk-orange); }

        /* Main Box Layout */
        .main-box { 
            background: #fff; 
            border-radius: 20px; 
            padding: 20px; 
            box-shadow: 0 2px 15px rgba(0,0,0,0.04); 
            margin-bottom: 20px; 
        }
        .section-title { 
            font-size: 15px; 
            font-weight: 800; 
            margin-bottom: 18px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }

        /* Top Expenses List */
        .expense-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        .expense-info { display: flex; align-items: center; gap: 12px; }
        .expense-icon {
            width: 35px;
            height: 35px;
            background: #fff5f5;
            color: var(--expense-red);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* Table Optimization */
        .table-responsive-custom {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
        }
        .table { min-width: 450px; }
        .table thead th { 
            background: #f8f9fa; 
            font-size: 11px; 
            color: #888;
            text-transform: uppercase;
            padding: 12px; 
            border: none; 
        }
        
        /* Badges & Buttons */
        .badge-type { padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 800; }
        .bg-masuk { background: #e8f5e9; color: #2e7d32; }
        .bg-keluar { background: #ffebee; color: #c62828; }

        .btn-orange { 
            background: var(--smk-orange); 
            color: #fff; 
            border: none; 
            border-radius: 12px; 
            font-weight: 700; 
            transition: 0.3s;
        }
        .btn-orange:hover { background: #e65c00; transform: translateY(-2px); }

        .form-control-sm, .form-select-sm { 
            border-radius: 10px; 
            padding: 10px; 
            border: 1px solid #eee;
            background-color: #fcfcfc;
        }

        /* Alerts */
        .alert { border-radius: 12px; border: none; font-weight: 600; }
    </style>
</head>
<body>

<nav class="navbar shadow-sm">
    <div class="container-fluid px-3">
        <a class="navbar-brand" href="#">
            <img src="https://tb-xiirpl1-24.vercel.app/icon/rpl.png" alt="Logo" class="logo-img">
            <div>
                <span class="d-block">Unit Produksi</span>
                <span class="text-muted small fw-normal">SMK Taruna Bangsa</span>
            </div>
        </a>
    </div>
</nav>

<div class="container-fluid px-3 pt-3">

    @if(session('success'))
        <div class="alert alert-success py-2 mb-3 small">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2 mb-3 small">{{ $errors->first() }}</div>
    @endif
    
    <div class="stat-container mb-2">
        <div class="stat-card border-income">
            <p class="stat-label">Total Masuk</p>
            <p class="stat-value text-success">Rp{{ number_format($totalIncome, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card border-expense">
            <p class="stat-label">Total Keluar</p>
            <p class="stat-value text-danger">Rp{{ number_format($totalExpense, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card border-balance">
            <p class="stat-label">Sisa Saldo</p>
            <p class="stat-value" style="color: var(--smk-orange);">Rp{{ number_format($balance, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="main-box">
        <h6 class="section-title">
            <span style="color: var(--smk-orange)"><i class="fas fa-plus-circle me-1"></i> Catat Transaksi</span>
        </h6>
        <form action="/store" method="POST">
            @csrf
            <div class="row g-2">
                <div class="col-12">
                    <input type="text" name="description" class="form-control form-control-sm" placeholder="Keterangan transaksi..." required>
                </div>
                <div class="col-7">
                    <input type="number" name="amount" class="form-control form-control-sm" placeholder="Nominal (Max 1M)" max="1000000000" required>
                </div>
                <div class="col-5">
                    <select name="type" class="form-select form-select-sm">
                        <option value="income">Masuk</option>
                        <option value="expense">Keluar</option>
                    </select>
                </div>
                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-orange w-100 py-2">
                        Simpan Data
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="main-box">
        <h6 class="section-title">
            <span><i class="fas fa-chart-line me-1 text-danger"></i> Pengeluaran Terbesar</span>
        </h6>
        <div class="expense-list">
            @forelse($topExpense as $exp)
                <div class="expense-item">
                    <div class="expense-info">
                        <div class="expense-icon"><i class="fas fa-shopping-bag"></i></div>
                        <div>
                            <div class="fw-bold" style="font-size: 12px;">{{ Str::limit($exp->description, 20) }}</div>
                            <div class="text-muted" style="font-size: 10px;">Total akumulasi</div>
                        </div>
                    </div>
                    <div class="fw-bold text-danger">
                        Rp{{ number_format($exp->total, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div class="text-center py-3 text-muted small">Belum ada data pengeluaran.</div>
            @endforelse
        </div>
    </div>

    <div class="main-box">
        <div class="section-title">
            <span><i class="fas fa-history me-1"></i> Riwayat</span>
            <div class="d-flex gap-1">
                <form action="/" method="GET">
                    <input type="month" name="filter_month" class="form-control form-control-sm" 
                           style="width: 105px; font-size: 11px;" 
                           value="{{ request('filter_month', date('Y-m')) }}" 
                           onchange="this.form.submit()">
                </form>
                <form action="/export" method="GET">
                    <input type="hidden" name="month" value="{{ request('filter_month') }}">
                    <button type="submit" class="btn btn-success btn-sm rounded-3 px-2">
                        <i class="fas fa-file-excel"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="table-responsive-custom">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Detail Transaksi</th>
                        <th class="text-end">Nominal</th>
                        <th width="30"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $t->description }}</div>
                            <div class="text-muted" style="font-size: 10px; margin-top: 2px;">
                                {{ $t->created_at->format('d M Y') }} • 
                                <span class="badge-type {{ $t->type == 'income' ? 'bg-masuk' : 'bg-keluar' }}">
                                    {{ $t->type == 'income' ? 'MASUK' : 'KELUAR' }}
                                </span>
                            </div>
                        </td>
                        <td class="text-end fw-bold {{ $t->type == 'income' ? 'text-success' : 'text-danger' }}">
                            {{ number_format($t->amount, 0, ',', '.') }}
                        </td>
                        <td class="text-end">
                            <form action="/delete/{{ $t->id }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-link text-muted p-0" onclick="return confirm('Hapus data ini?')">
                                    <i class="fas fa-trash-alt" style="font-size: 14px;"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open d-block mb-2 shadow-sm" style="font-size: 24px;"></i>
                            Data tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>