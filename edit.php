<?php
session_start();
require 'config/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// 1. Ambil ID dari URL dan cek datanya
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM barang WHERE id = $id");

if (mysqli_num_rows($result) === 0) {
    header("Location: index.php");
    exit;
}

$row = mysqli_fetch_assoc($result);

// 2. Proses saat tombol Update diklik
if (isset($_POST['update'])) {
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $stok = intval($_POST['stok']);
    $foto_lama = $_POST['foto_lama'];

    // Cek apakah user upload foto baru atau tidak
    if ($_FILES['foto']['error'] === 4) {
        // Jika tidak upload foto baru, pakai foto lama
        $nama_file_final = $foto_lama;
    } else {
        // Jika upload foto baru, proses uploadnya
        $nama_file = $_FILES['foto']['name'];
        $tmp_name = $_FILES['foto']['tmp_name'];
        $ekstensi_valid = ['jpg', 'jpeg', 'png', 'webp'];
        $ekstensi_file = strtolower(end(explode('.', $nama_file)));

        if (in_array($ekstensi_file, $ekstensi_valid)) {
            // Hapus foto lama di folder uploads agar tidak menumpuk
            if (file_exists("assets/uploads/" . $foto_lama)) {
                unlink("assets/uploads/" . $foto_lama);
            }

            // Upload foto baru
            $nama_file_final = uniqid() . '.' . $ekstensi_file;
            move_uploaded_file($tmp_name, 'assets/uploads/' . $nama_file_final);
        } else {
            echo "<script>alert('Ekstensi file tidak valid!');</script>";
            return false;
        }
    }

    // Update database
    $query = "UPDATE barang SET 
                nama_barang = '$nama_barang', 
                kategori = '$kategori', 
                stok = '$stok', 
                foto = '$nama_file_final' 
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data barang berhasil diupdate!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate data!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang - Inventaris</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { 
            display: flex !important; 
            justify-content: center !important; 
            align-items: center !important; 
            background: url('https://replicate.delivery/xpbkg/2QyYqM9e7f4vB1M7R6E0rV4wK0B7G4a8g0e1b2c3d4e5f6g7/output.png') no-repeat center center fixed !important; 
            background-size: cover !important; 
            min-height: 100vh;
            padding: 20px;
        }
        
        .form-container { 
            background: white; 
            padding: 35px; 
            border-radius: 12px; 
            box-shadow: 0 8px 24px rgba(0,0,0,0.2); 
            width: 100%; 
            max-width: 500px; 
            animation: fadeInUp 0.5s ease; 
        }

        .form-crud { display: flex; flex-direction: column; }
        .form-crud .form-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        .form-crud .form-group label { font-weight: 600; margin-bottom: 5px; color: #4b5563; }
        .form-crud .form-group input { padding: 12px; border: 2px solid #e1e1e1; border-radius: 8px; outline: none; }
        .form-crud .form-group input:focus { border-color: #4f46e5; }

        .preview-img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; border: 2px solid #ddd; }
        
        .button-group { display: flex; justify-content: space-between; margin-top: 10px; }
        .btn-back { background: #9ca3af; color: white; padding: 12px; border-radius: 8px; text-decoration: none; width: 45%; text-align: center; font-weight: 600; }
        .btn-update { background: #4f46e5; color: white; padding: 12px; border: none; border-radius: 8px; width: 45%; font-weight: 600; cursor: pointer; }
        .btn-update:hover { background: #4338ca; }
    </style>
</head>
<body>

    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 20px;">Edit Barang 📝</h2>
        
        <form action="" method="POST" enctype="multipart/form-data" class="form-crud">
            <input type="hidden" name="foto_lama" value="<?= $row['foto']; ?>">

            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" value="<?= $row['nama_barang']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Kategori</label>
                <input type="text" name="kategori" value="<?= $row['kategori']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Jumlah Stok</label>
                <input type="number" name="stok" value="<?= $row['stok']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Foto Saat Ini</label>
                <img src="assets/uploads/<?= $row['foto']; ?>" class="preview-img">
                <label style="font-size: 12px; color: orange;">*Kosongkan jika tidak ingin ganti foto</label>
                <input type="file" name="foto" style="padding: 5px; border: 1px dashed #ccc;">
            </div>
            
            <div class="button-group">
                <a href="index.php" class="btn-back">Batal</a>
                <button type="submit" name="update" class="btn-update">Update Data</button>
            </div>
        </form>
    </div>

</body>
</html>