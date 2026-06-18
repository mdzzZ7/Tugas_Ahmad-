<?php
session_start();
require 'config/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $stok = intval($_POST['stok']);

    $files = $_FILES['foto'];
    $total_files = count($files['name']);
    $uploaded_images = [];
    $ekstensi_valid = ['jpg', 'jpeg', 'png', 'webp'];
    $upload_sukses = true;

    // Pastikan folder uploads sudah ada
    if (!is_dir('assets/uploads')) {
        mkdir('assets/uploads', 0777, true);
    }

    // Looping multiple file
    for ($i = 0; $i < $total_files; $i++) {
        $nama_file = $files['name'][$i];
        $ukuran_file = $files['size'][$i];
        $tmp_name = $files['tmp_name'][$i];
        $error = $files['error'][$i];

        if ($error === 0) {
            $pecah_nama = explode('.', $nama_file);
            $ekstensi_file = strtolower(end($pecah_nama));
            
            if (in_array($ekstensi_file, $ekstensi_valid)) {
                if ($ukuran_file < 2000000) { // Max 2MB
                    $nama_file_baru = uniqid() . '_' . $i . '.' . $ekstensi_file;
                    move_uploaded_file($tmp_name, 'assets/uploads/' . $nama_file_baru);
                    $uploaded_images[] = $nama_file_baru;
                } else {
                    echo "<script>alert('File " . $nama_file . " terlalu besar! Maksimal 2MB');</script>";
                    $upload_sukses = false;
                    break;
                }
            } else {
                echo "<script>alert('Format " . $nama_file . " tidak valid!');</script>";
                $upload_sukses = false;
                break;
            }
        }
    }

    if ($upload_sukses && !empty($uploaded_images)) {
        $foto_final = implode(',', $uploaded_images);
        
        $query = "INSERT INTO barang (nama_barang, kategori, stok, foto) VALUES ('$nama_barang', '$kategori', '$stok', '$foto_final')";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Barang berhasil ditambahkan!'); window.location='index.php';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan ke database!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang - Inventaris</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { 
            display: flex; justify-content: center; align-items: center; min-height: 100vh; 
            background: url('assets/css/bg-gudang.png') no-repeat center center fixed; background-size: cover;
        }
        .form-box {
            background: rgba(255, 255, 255, 0.9); padding: 30px; border-radius: 12px; 
            width: 100%; max-width: 450px; box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        }
        h2 { margin-bottom: 20px; color: #333; text-align: center; font-size: 22px; }
        .input-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        .input-group label { font-weight: 600; margin-bottom: 5px; color: #4b5563; font-size: 14px; }
        .input-group input { padding: 10px; border: 2px solid #e1e1e1; border-radius: 8px; outline: none; }
        .input-group input:focus { border-color: #4f46e5; }
        .btn-group { display: flex; justify-content: space-between; margin-top: 20px; }
        .btn { padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; text-align: center; width: 48%; }
        .btn-save { background: #10b981; color: white; }
        .btn-back { background: #9ca3af; color: white; }
    </style>
</head>
<body>

    <div class="form-box">
        <h2>Tambah Barang Baru 📦</h2>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" placeholder="Contoh: PlayStation 5" required autocomplete="off">
            </div>
            
            <div class="input-group">
                <label>Kategori</label>
                <input type="text" name="kategori" placeholder="Contoh: Konsol" required autocomplete="off">
            </div>
            
            <div class="input-group">
                <label>Jumlah Stok</label>
                <input type="number" name="stok" placeholder="Masukkan jumlah" required min="1">
            </div>
            
            <div class="input-group">
                <label>Foto Barang (Bisa Pilih Banyak)</label>
                <input type="file" name="foto[]" multiple required style="border: 2px dashed #ccc; padding: 5px; background: #fafafa;">
                <small style="color: #6b7280; margin-top: 4px; font-size: 11px;">*Tahan tombol Ctrl untuk memilih > 1 gambar</small>
            </div>
            
            <div class="btn-group">
                <a href="index.php" class="btn btn-back">Batal</a>
                <button type="submit" name="simpan" class="btn btn-save">Simpan</button>
            </div>
        </form>
    </div>

</body>
</html>