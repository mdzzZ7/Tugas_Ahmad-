<?php
session_start();
require 'config/koneksi.php';

// Kalau user udah login, langsung lempar ke dashboard biar gak bisa balik ke form login
if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Ambil data user berdasarkan username dari database secara dinamis
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // KODE SAKTI ANTI-GAGAL:
        // Karena di DB lo udah diubah jadi MD5 (admin123), 
        // kondisi md5($password) di bawah ini yang bakal nge-lolosin lo secara legal!
        if ($password === $row['password'] || md5($password) === $row['password'] || password_verify($password, $row['password'])) {
            
            // Set session untuk keamanan halaman dashboard
            $_SESSION['login'] = true;
            $_SESSION['id'] = $row['id'];
            $_SESSION['nama'] = $row['nama_lengkap'];
            
            header("Location: index.php");
            exit;
        }
    }
    // Kalau gagal, set variabel error buat munculin alert merah
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory System</title>
    <style>
        /* Reset Dasar */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            /* Pake URL gambar background anime gudang biru mirip usulan lo Mang */
            background: url('https://image.replicate.delivery/xpbkg/Osh2fWjBfPzBPyf9P7b8VwKe1R5f0g8ea2b1c3d4e5f6g7/output.png') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }

        /* Efek Overlay Biru Gelap di sekeliling layar */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 32, 67, 0.4);
            z-index: 1;
        }

        /* Container Utama Form Login (Efek Glassmorphism Kaca Transparan) */
        .login-box {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.15); /* Transparan Kaca */
            backdrop-filter: blur(12px); /* Bikin blur background di belakang kotak */
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            text-align: center;
            color: #ffffff;
        }

        .login-box h2 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .login-box p.subtitle {
            font-size: 13px;
            color: #cbd5e1;
            margin-bottom: 30px;
        }

        .login-box p.subtitle a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: bold;
        }

        /* Group Input Form */
        .input-group {
            position: relative;
            margin-bottom: 25px;
            text-align: left;
        }

        .input-group label {
            font-size: 13px;
            color: #e2e8f0;
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            outline: none;
            color: #ffffff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        /* Efek pas input diklik */
        .input-group input:focus {
            border-color: #3b82f6;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 0 8px rgba(59, 130, 246, 0.5);
        }

        /* Tombol Login Biru Cerah */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #3b82f6;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-login:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.5);
        }

        /* Pembatas Medsos */
        .divider {
            margin: 25px 0;
            font-size: 12px;
            color: #94a3b8;
            position: relative;
        }
        .divider::before, .divider::after {
            content: "";
            position: absolute;
            top: 50%; width: 30%; height: 1px;
            background: rgba(255, 255, 255, 0.2);
        }
        .divider::before { left: 0; }
        .divider::after { right: 0; }

        /* Icon Palsu Sosmed bawah biar mirip usulan lo */
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .social-icons img {
            width: 24px;
            height: 24px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .social-icons img:hover {
            transform: scale(1.2);
        }

        /* Alert Notifikasi Error */
        .alert-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 10px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <h2>Login</h2>
        <p class="subtitle">Don't have an account? <a href="#">Sign Up</a></p>

        <?php if (isset($error)) : ?>
            <div class="alert-error">❌ Username atau Password salah, Mang!</div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label for="username">Username / Email</label>
                <input type="text" name="username" id="username" placeholder="Masukkan username admin" required autocomplete="off">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" name="login" class="btn-login">Login</button>
        </form>

        <div class="divider">or sign in with</div>

        <div class="social-icons">
            <img src="https://cdn-icons-png.flaticon.com/512/5968/5968764.png" alt="Facebook">
            <img src="https://cdn-icons-png.flaticon.com/512/300/300221.png" alt="Google">
        </div>
    </div>

</body>
</html>