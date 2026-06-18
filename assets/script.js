// Gunakan DOMContentLoaded biar dipastikan HTML canvas-nya keload dulu sebelum dibaca JS
        document.addEventListener("DOMContentLoaded", function() {
            const canvas = document.getElementById('signature-canvas');
            const ctx = canvas.getContext('2d');
            const clearBtn = document.getElementById('clear-signature');
            let isDrawing = false;

            // Atur gaya garis corat-coret (Warna hitam, tebal 3, smooth)
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#000000';

            function getCoordinates(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            }

            function startDrawing(e) {
                isDrawing = true;
                const coords = getCoordinates(e);
                ctx.beginPath();
                ctx.moveTo(coords.x, coords.y);
                if (e.touches) e.preventDefault();
            }

            function draw(e) {
                if (!isDrawing) return;
                const coords = getCoordinates(e);
                ctx.lineTo(coords.x, coords.y);
                ctx.stroke();
                if (e.touches) e.preventDefault();
            }

            function stopDrawing() {
                isDrawing = false;
                ctx.beginPath();
            }

            // Event handler Mouse (PC / Laptop)
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            // Event handler Touch (HP / Tablet)
            canvas.addEventListener('touchstart', startDrawing, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            canvas.addEventListener('touchend', stopDrawing);

            // Fungsi tombol hapus
            clearBtn.addEventListener('click', () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            });
        });
        // --- KODE FITUR PENCARIAN LIVE ---
            const searchInput = document.getElementById('search-input');
            const tableRows = document.querySelectorAll('tbody tr');

        searchInput.addEventListener('keyup', function() {
        const filterValue = searchInput.value.toLowerCase();
    
        tableRows.forEach(row => {
        // Ambil teks dari kolom Nama Barang (kolom ke-3) dan Kategori (kolom ke-4)
        const namaBarang = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';
        const kategori = row.cells[3] ? row.cells[3].textContent.toLowerCase() : '';
        
        // Cek apakah teks cocok dengan keyword pencarian
        if (namaBarang.includes(filterValue) || kategori.includes(filterValue)) {
            row.style.display = ""; // Tampilkan jika cocok
        } else {
            row.style.display = "none"; // Sembunyikan jika tidak cocok
        }
    });
});