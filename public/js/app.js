    // 1. Fungsi format ribuan saat mengetik
    function formatRibuan(input) {
        let val = input.value.replace(/[^0-9]/g, '');
        input.value = val ? new Intl.NumberFormat('id-ID').format(val) : '';
    }

    const mainAmount = document.getElementById('amount');
    mainAmount.addEventListener('input', () => formatRibuan(mainAmount));

    document.querySelectorAll('.amount-input').forEach(inp => {
        inp.addEventListener('input', () => formatRibuan(inp));
    });

    // 2. Bersihkan titik sebelum kirim ke Controller
    document.addEventListener('submit', function(e) {
        if (e.target.tagName === 'FORM') {
            const amountInp = e.target.querySelector('input[name="amount"]');
            if (amountInp) {
                amountInp.value = amountInp.value.replace(/\./g, '');
            }
        }
    });

    // 3. Toggle Edit Row
    function toggleEdit(id) {
        const row = document.getElementById(`row-${id}`);
        const edit = document.getElementById(`edit-${id}`);
        if (edit.style.display === 'none') {
            row.style.display = 'none';
            edit.style.display = 'table-row';
        } else {
            row.style.display = 'table-row';
            edit.style.display = 'none';
        }
    }