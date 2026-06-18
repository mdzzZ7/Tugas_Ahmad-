<?php
session_start();
require 'config/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Ambil data semua barang dari database untuk ditampilkan ke tabel
$result = mysqli_query($conn, "SELECT * FROM barang ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Inventaris</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Styling Utama Dashboard */
        body { display: block; padding: 20px; background: #f4f6f9; min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .dashboard-container { max-width: 1100px; margin: 30px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); animation: fadeInUp 0.5s ease; }
        .header-dash { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .user-info h1 { font-size: 24px; color: #333; }
        .user-info p { color: #777; font-size: 14px; }
        
        /* Navigasi Tombol */
        .action-buttons { display: flex; gap: 10px; align-items: center; }
        .btn-add { background: #10b981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; border: none; transition: all 0.3s ease; display: inline-block; }
        .btn-add:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16,185,129,0.2); }
        .btn-pdf { background: #4f46e5; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s ease; }
        .btn-pdf:hover { background: #4338ca; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(79,70,229,0.2); }
        .btn-logout { background: #ef4444; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; transition: all 0.3s ease; }
        .btn-logout:hover { background: #dc2626; }
        
        /* Styling Tabel Modern */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; text-align: left; }
        th { background: #4f46e5; color: white; padding: 14px; font-size: 15px; }
        td { padding: 12px 14px; border-bottom: 1px solid #eef2f3; color: #444; font-size: 14px; }
        tr { transition: all 0.2s ease; }
        tr:hover { background-color: #f8fafc; transform: scale(1.005); }
        
        .img-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .badge-stok { background: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 12px; font-weight: bold; font-size: 12px; }
        
        /* Tombol Aksi */
        .btn-edit { color: #3b82f6; text-decoration: none; font-weight: 600; margin-right: 10px; }
        .btn-hapus { color: #ef4444; text-decoration: none; font-weight: 600; }
        .btn-edit:hover, .btn-hapus:hover { text-decoration: underline; }

        /* Area Tanda Tangan & Media */
        .signature-area { display: flex; justify-content: space-between; margin-top: 50px; align-items: flex-start; gap: 20px; }
        .signature-box { text-align: center; width: 250px; }

        /* CSS KHUSUS PRINT */
        @media print {
            body { background: white !important; color: black !important; padding: 0; }
            .dashboard-container { box-shadow: none !important; margin: 0 !important; padding: 0 !important; max-width: 100% !important; }
            .btn-add, .btn-pdf, .btn-logout, .action-col, td:nth-child(6), th:nth-child(6), #clear-signature, .print-hide, #search-container {
                display: none !important;
            }
            th { background: #333 !important; color: white !important; }
            #signature-canvas { border: none !important; background: transparent !important; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>  

    <audio id="click-sound" src="https://assets.mixkit.co/active_storage/sfx/2568/2568-84.wav" preload="auto"></audio>

    <div class="dashboard-container">
        <div class="header-dash">
            <div class="user-info">
                <h1>Sistem Inventaris Barang 📦</h1>
                <p>Login sebagai: <strong><?= $_SESSION['nama']; ?></strong></p>
            </div>
            <div class="action-buttons">
                <button onclick="window.print()" class="btn-pdf">📄 Export PDF</button>
                <a href="tambah.php" class="btn-add">+ Tambah Barang</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <div id="search-container" style="margin-bottom: 15px; text-align: right;">
            <input type="text" id="search-input" placeholder="🔍 Cari nama barang atau kategori..." style="padding: 10px; width: 300px; border: 2px solid #e1e1e1; border-radius: 8px; outline: none;">
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th class="action-col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) === 0) : ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #999; padding: 30px;">Belum ada data barang.</td>
                    </tr>
                <?php endif; ?>
                
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($result)) : 
                    // Pecah array multiple file gambar, ambil indeks ke-0
                    $foto_array = explode(',', $row['foto']);
                    $foto_utama = $foto_array[0];
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td>
                        <img src="assets/uploads/<?= $foto_utama; ?>" class="img-thumb" alt="Foto Barang">
                    </td>
                    <td><strong><?= $row['nama_barang']; ?></strong></td>
                    <td><?= $row['kategori']; ?></td>
                    <td><span class="badge-stok"><?= $row['stok']; ?> Pcs</span></td>
                    <td class="action-col">
                        <a href="edit.php?id=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                        <a href="hapus.php?id=<?= $row['id']; ?>" class="btn-hapus" onclick="return confirm('Yakin mau hapus barang ini, Mang?');">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="signature-area">
            <div class="print-hide" style="width: 50%; background: #ffffff; padding: 15px; border-radius: 12px; border: 1px solid #eee; text-align: left;">
                 <p style="margin-top: 0; font-weight: bold; color: #4f46e5; font-size: 14px;">📺 Video Panduan Aktif</p>
            </div>
            
            <div class="signature-box" style="width: 45%;">
                <p>Sukabumi, <?= date('d F Y'); ?></p>
                <p><strong>Kepala Gudang Inventaris</strong></p>
                
                <canvas id="signature-canvas" width="250" height="130" style="border: 2px dashed #ccc; border-radius: 8px; background: #fafafa; margin-top: 10px; cursor: crosshair Haus;"></canvas>
                
                <div style="margin-top: 5px; margin-bottom: 10px;">
                    <button type="button" id="clear-signature" style="padding: 4px 10px; background: #9ca3af; color: white; border: none; border-radius: 4px; font-size: 11px; cursor: pointer;">Hapus Ttd</button>
                </div>
                <p><strong><?= $_SESSION['nama']; ?></strong></p>
            </div>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>