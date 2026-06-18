<?php
session_start();
require 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Cari tahu dulu nama file fotonya sebelum datanya dihapus
    $result = mysqli_query($conn, "SELECT foto FROM barang WHERE id = $id");
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        $nama_foto = $row['foto'];

        // Hapus file fisik foto di folder uploads jika ada filenya
        if (file_exists("assets/uploads/" . $nama_foto)) {
            unlink("assets/uploads/" . $nama_foto);
        }

        // Baru hapus datanya dari database MySQL
        mysqli_query($conn, "DELETE FROM barang WHERE id = $id");
        echo "<script>alert('Barang berhasil dihapus!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
    }
} else {
    header("Location: index.php");
    exit;
}
?>