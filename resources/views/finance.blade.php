<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Keuangan</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            padding: 40px;
        }

        .card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 { color: #333; margin-top: 0; }

        .form-group { margin-bottom: 15px; }

        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box; /* Biar padding gak ngerusak lebar */
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        button:hover { background-color: #2980b9; }

        .alert {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .balance-info {
            background: #eef2f3;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="card">
        <h2>Catat Keuangan</h2>

        @if(session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        <div class="balance-info">
            <strong>Saldo Saat Ini:</strong><br>
            @foreach(\App\Models\Account::all() as $acc)
                {{ $acc->name }}: Rp {{ number_format($acc->balance, 0, ',', '.') }}
            @endforeach
        </div>

        <form action="/transactions" method="POST">
            @csrf
            <div class="form-group">
                <label>Tipe</label>
                <select name="type">
                    <option value="income">Pemasukan (+)</option>
                    <option value="expense">Pengeluaran (-)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nominal</label>
                <input type="number" name="amount" placeholder="Contoh: 50000" required>
            </div>

            <div class="form-group">
                <label>Rekening</label>
                <select name="account_id">
                    @foreach(\App\Models\Account::all() as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Keterangan</label>
                <input type="text" name="description" placeholder="Beli bakso, Gaji, dll">
            </div>

            <button type="submit">Simpan Transaksi</button>
        </form>
    </div>

</body>
</html>