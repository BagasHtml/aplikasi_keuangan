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
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
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

<script src="{{ asset('js/app.js') }}"></script>

</body>
</html>