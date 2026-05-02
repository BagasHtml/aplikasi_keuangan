<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Keuangan UP | SMK Taruna Bangsa</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bg-main: #f1f5f9;
            --surface: #ffffff;
            --primary: #0f172a;
            --primary-hover: #020617;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-glow-green: rgba(16, 185, 129, 0.15);
            --accent-glow-red: rgba(239, 68, 68, 0.15);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        * { transition: all 0.2s ease; }
        
        body { 
            background-color: var(--bg-main); 
            color: var(--text-main); 
            font-family: 'Plus Jakarta Sans', sans-serif;
            padding-top: 80px; 
            -webkit-font-smoothing: antialiased;
        }
        
        /* Glassmorphism Navbar */
        .navbar { 
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            padding: 12px 0;
        }
        .logo-img { width: 38px; height: 38px; margin-right: 12px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

        /* Stat Cards with Hover Lift */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--surface);
            padding: 20px;
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 4px; height: 100%;
        }
        .stat-card.income::before { background: var(--accent-green); }
        .stat-card.expense::before { background: var(--accent-red); }
        .stat-card.balance::before { background: var(--primary); }
        
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -5px rgba(0,0,0,0.08); }
        .stat-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; }
        .stat-value { font-size: 18px; font-weight: 800; margin-top: 8px; letter-spacing: -0.5px; }

        /* Main Bento Box */
        .main-box {
            background: var(--surface);
            border-radius: 24px;
            padding: 24px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }
        .section-title { font-size: 15px; font-weight: 700; margin-bottom: 20px; color: var(--primary); display: flex; align-items: center; gap: 8px;}

        /* Modern Forms */
        .form-label-custom { font-weight: 700; font-size: 11px; color: var(--text-muted); margin-bottom: 6px; display: block; text-transform: uppercase; letter-spacing: 0.5px;}
        
        .form-control, .form-select {
            background: #f8fafc;
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 500;
        }
        .form-control:focus, .form-select:focus { 
            background: #fff; 
            border-color: var(--primary); 
            box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.08);
        }

        .btn-save {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 14px;
            font-weight: 700;
            font-size: 14px;
            width: 100%;
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-save:hover { background: green; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.2); }
        .btn-save:active { transform: translateY(0); }

        /* Top Expense Leaderboard */
        .expense-list { display: flex; flex-direction: column; gap: 12px; }
        .expense-card {
            display: flex; align-items: center; gap: 14px;
            padding: 14px; background: var(--surface);
            border-radius: 14px; border: 1px solid var(--border);
            transition: all 0.3s ease;
        }
        .expense-card:hover { background: #f8fafc; border-color: #cbd5e1; }
        .rank-num { 
            font-size: 16px; font-weight: 800; color: #cbd5e1; 
            width: 30px; text-align: center;
        }
        .rank-1 .rank-num { color: #f59e0b; } /* Gold */
        .rank-2 .rank-num { color: #94a3b8; } /* Silver */
        .rank-3 .rank-num { color: #d97706; } /* Bronze */

        /* Interactive Table */
        .table-container { overflow-x: auto; }
        .custom-table { width: 100%; border-collapse: separate; border-spacing: 0 6px; }
        .custom-table th { padding: 8px 16px; color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .custom-table tbody tr { position: relative; }
        .custom-table td { 
            background: var(--surface); padding: 16px; 
            border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); 
            vertical-align: middle;
        }
        .custom-table td:first-child { border-left: 1px solid var(--border); border-radius: 14px 0 0 14px; }
        .custom-table td:last-child { border-right: 1px solid var(--border); border-radius: 0 14px 14px 0; }
        
        .custom-table tbody tr:hover td { background: #f8fafc; }

        /* Reveal Action Buttons on Hover */
        .action-group { opacity: 0; transition: opacity 0.2s ease; }
        .custom-table tbody tr:hover .action-group { opacity: 1; }
        
        .btn-icon {
            width: 32px; height: 32px; border-radius: 8px; border: none;
            display: inline-flex; align-items: center; justify-content: center;
            background: transparent; color: var(--text-muted); cursor: pointer;
        }
        .btn-icon:hover { background: #e2e8f0; color: var(--primary); }
        .btn-icon.delete:hover { background: var(--accent-glow-red); color: var(--accent-red); }

        .badge-status { padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 800; letter-spacing: 0.5px; }
        .bg-income { background: var(--accent-glow-green); color: var(--accent-green); }
        .bg-expense { background: var(--accent-glow-red); color: var(--accent-red); }

        /* Smooth Inline Edit Animation */
        .edit-wrapper {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.3s ease, opacity 0.2s ease, margin 0.3s ease;
            margin-bottom: 0;
            border-radius: 12px;
        }
        .edit-wrapper.active {
            max-height: 200px; /* Adjust based on content height */
            opacity: 1;
            margin-bottom: 8px;
            background: #f1f5f9;
            padding: 16px;
            border: 2px solid var(--border);
        }
        .edit-form-control {
            background: #fff !important;
            border: 1px solid var(--border) !important;
            padding: 8px 12px !important;
            border-radius: 8px !important;
            font-size: 12px !important;
        }

        /* Empty State */
        .empty-state {
            text-align: center; padding: 40px 20px; color: var(--text-muted);
        }
        .empty-state i { font-size: 40px; margin-bottom: 16px; opacity: 0.3; }

        /* Alert */
        .alert-custom {
            background: var(--accent-glow-green);
            color: #065f46;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Responsive adjustments for Mobile */
        @media (max-width: 768px) {
            .stat-grid { grid-template-columns: 1fr; }
            .action-group { opacity: 1; } /* Always show on mobile since there's no hover */
            .custom-table td:first-child, .custom-table td:last-child { border-radius: 0; }
            .custom-table td { border-left: 1px solid var(--border) !important; border-right: 1px solid var(--border) !important; }
        }
    </style>
</head>
<body>

<nav class="navbar fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center text-decoration-none" href="/">
            <img src="https://thi-web6.github.io/resume/images/tarunabangsaicon.png" class="logo-img" alt="Logo"
                 onerror="this.src='https://ui-avatars.com/api/?name=UP&background=0f172a&color=fff&bold=true'">
            <div>
                <div style="font-weight: 800; font-size: 15px; color: var(--text-main); letter-spacing: -0.5px;">Keuangan Unit Produksi</div>
                <div style="font-size: 11px; color: var(--text-muted); font-weight: 500;">SMK Taruna Bangsa</div>
            </div>
        </a>
    </div>
</nav>

<div class="container pb-5">

    @if(session('success'))
        <div class="alert-custom mb-3">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Stats Bento Grid -->
    <div class="stat-grid">
        <div class="stat-card income">
            <div class="stat-label">Total Masuk</div>
            <div class="stat-value" style="color: var(--accent-green);">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card expense">
            <div class="stat-label">Total Keluar</div>
            <div class="stat-value" style="color: var(--accent-red);">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
        </div>
        <div class="stat-card balance">
            <div class="stat-label">Sisa Saldo</div>
            <div class="stat-value" style="color: var(--primary);">Rp {{ number_format($balance, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Form Input -->
    <div class="main-box">
        <div class="section-title"><i class="fas fa-plus-circle" style="color: var(--accent-green);"></i> Tambah Transaksi Baru</div>
        <form action="/store" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label-custom">Keterangan</label>
                    <input type="text" name="description" class="form-control" placeholder="Contoh: Pembelian ATK" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label-custom">Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control" placeholder="0" id="amount" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label-custom">Jenis</label>
                    <select name="type" class="form-select">
                        <option value="income">Pemasukan</option>
                        <option value="expense">Pengeluaran</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-add"></i> Simpan ke Database
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Top 3 Expense Leaderboard -->
    @if($topExpense->count() > 0)
    <div class="main-box">
        <div class="section-title"><i class="bi bi-graph-up" style="color: red; box-shadow: 0px 0px  2px red"></i> Pengeluaran Terbesar</div>
        <div class="expense-list">
            @foreach($topExpense as $index => $exp)
                <div class="expense-card rank-{{ $index + 1 }}">
                    <div class="rank-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="flex-grow-1">
                        <div class="fw-bold" style="font-size: 13px;">{{ Str::limit($exp->description, 25) }}</div>
                        <div class="text-muted" style="font-size: 10px;">Total akumulasi</div>
                    </div>
                    <div class="fw-800 text-danger" style="font-size: 14px;">
                        Rp {{ number_format($exp->total, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Riwayat Transaksi -->
    <div class="main-box">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div class="section-title mb-0"><i class="fas fa-clock-rotate-left"></i> Riwayat Transaksi</div>
            <div class="d-flex gap-2 align-items-center">
                <form action="/" method="GET">
                    <input type="month" name="filter_month" class="form-control form-control-sm" style="border-radius:10px; font-size:12px; width: auto;" value="{{ request('filter_month', date('Y-m')) }}" onchange="this.form.submit()">
                </form>
                <a href="/export?month={{ request('filter_month') }}" class="btn btn-outline-dark btn-sm" style="border-radius:10px; font-weight:600;">
                    <i class="fas fa-file-excel me-1"></i> Excel
                </a>
            </div>
        </div>

        <div class="table-container">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th class="text-end">Jumlah</th>
                        <th width="80"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <!-- Normal Row -->
                    <tr id="row-{{ $t->id }}">
                        <td style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">{{ $t->created_at->format('d M Y') }}</td>
                        <td class="fw-bold" style="font-size: 13px;">{{ $t->description }}</td>
                        <td>
                            <span class="badge-status {{ $t->type == 'income' ? 'bg-income' : 'bg-expense' }}">
                                {{ $t->type == 'income' ? 'PEMASUKAN' : 'PENGELUARAN' }}
                            </span>
                        </td>
                        <td class="text-end fw-extrabold" style="font-size: 14px; letter-spacing: -0.3px;">
                            Rp {{ number_format($t->amount, 0, ',', '.') }}
                        </td>
                        <td class="text-end">
                            <div class="action-group d-inline-flex gap-1">
                                <button class="btn-icon" onclick="toggleEdit({{ $t->id }})" title="Edit">
                                    <i class="fas fa-pen-to-square fa-sm"></i>
                                </button>
                                <form action="/delete/{{ $t->id }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button class="btn-icon delete" onclick="return confirm('Yakin hapus data ini?')" title="Hapus">
                                        <i class="fas fa-trash fa-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <!-- Smooth Edit Row (Placed outside <tr> using wrapper logic via JS) -->
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-receipt d-block"></i>
                                <div class="fw-bold">Belum Ada Transaksi</div>
                                <div style="font-size: 12px; margin-top: 4px;">Mulai catat keuanganmu di form atas.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            
            <!-- Hidden Edit Forms (Rendered outside table to prevent table breakage) -->
            @foreach($transactions as $t)
            <div id="edit-{{ $t->id }}" class="edit-wrapper">
                <form action="/update/{{ $t->id }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <input type="text" name="description" class="form-control edit-form-control" value="{{ $t->description }}" required>
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="amount" class="form-control edit-form-control" value="{{ $t->amount }}" required>
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-select edit-form-control">
                                <option value="income" {{ $t->type == 'income' ? 'selected' : '' }}>Masuk</option>
                                <option value="expense" {{ $t->type == 'expense' ? 'selected' : '' }}>Keluar</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-dark flex-fill" style="border-radius:8px; padding: 6px;"><i class="fas fa-check"></i></button>
                            <button type="button" class="btn btn-sm btn-light flex-fill border" style="border-radius:8px; padding: 6px;" onclick="toggleEdit({{ $t->id }})"><i class="fas fa-xmark"></i></button>
                        </div>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    const input = document.getElementById('amount');

    input.addEventListener('input', function(e){
        let value = this.value.replace(/[^0-9]/g,'');
        this.value = new Intl.NumberFormat('id-ID').format(value);
    });

    // Fungsi toggle edit dengan animasi smooth
    function toggleEdit(id) {
        const normalRow = document.getElementById(`row-${id}`);
        const editWrapper = document.getElementById(`edit-${id}`);
        
        if (editWrapper.classList.contains('active')) {
            // Close animation
            editWrapper.classList.remove('active');
            normalRow.style.display = 'table-row';
        } else {
            // Open animation
            normalRow.style.display = 'none';
            editWrapper.classList.add('active');
            
            // Fokus ke input pertama setelah animasi mulai
            setTimeout(() => {
                editWrapper.querySelector('input').focus();
            }, 150);
        }
    }
</script>

</body>
</html>