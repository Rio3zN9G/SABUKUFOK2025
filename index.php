<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'config.php';

// Harga tiap lomba
$harga_lomba = [
    'Tari Kreasi Kelompok' => 65000,
    'Pop Sunda' => 35000,
    'Menggambar' => 25000,
    'Desain Poster' => 25000
];

// Inisialisasi variabel
$pesan = '';
$tipe_pesan = '';
$showModal = false;

// Tautan ke grup WhatsApp
$whatsapp_group_link = 'https://chat.whatsapp.com/GUWqDxXJj9h7qleNpVIg7T?mode=ac_t'; // GANTI DENGAN LINK GRUP WHATSAPP ANDA

// Cek jika ada parameter sukses dari redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $pesan = "Pendaftaran berhasil! Silakan tunggu informasi lebih lanjut.";
    $tipe_pesan = 'success';
    $showModal = true;
}

// Proses form pendaftaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = htmlspecialchars(trim($_POST['nama_lengkap']));
    $asal_sekolah = htmlspecialchars(trim($_POST['asal_sekolah']));
    $pilihan_lomba = $_POST['pilihan_lomba'];
    $no_wa = htmlspecialchars(trim($_POST['no_wa']));
    
    // Validasi input
    if (empty($nama_lengkap) || empty($asal_sekolah) || empty($pilihan_lomba) || empty($no_wa)) {
        $pesan = "Semua field harus diisi!";
        $tipe_pesan = 'error';
    } 
    // Validasi file
    elseif (isset($_FILES['bukti_pendaftaran']) && $_FILES['bukti_pendaftaran']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['bukti_pendaftaran'];
        $file_type = mime_content_type($file['tmp_name']);
        $file_size = $file['size'];
        
        // Cek tipe file
        if (!in_array($file_type, $allowed_types)) {
            $pesan = "Format file tidak didukung. Hanya JPEG, PNG, JPG, dan PDF yang diperbolehkan.";
            $tipe_pesan = 'error';
        } 
        // Cek ukuran file
        elseif ($file_size > MAX_FILE_SIZE) {
            $pesan = "Ukuran file terlalu besar. Maksimal 2MB.";
            $tipe_pesan = 'error';
        } else {
            // Generate nama file unik
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('bukti_', true) . '.' . $file_ext;
            $upload_path = UPLOAD_DIR . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Simpan ke database
                try {
                    $stmt = $pdo->prepare("INSERT INTO participants (nama_lengkap, asal_sekolah, pilihan_lomba, no_wa, bukti_pendaftaran) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$nama_lengkap, $asal_sekolah, $pilihan_lomba, $no_wa, $file_name]);
                    
                    // Redirect setelah berhasil
                    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                    exit();
                } catch (PDOException $e) {
                    $pesan = "Terjadi kesalahan sistem. Silakan coba lagi.";
                    $tipe_pesan = 'error';
                    // Hapus file yang sudah diupload jika gagal menyimpan ke database
                    unlink($upload_path);
                }
            } else {
                $pesan = "Gagal mengupload file. Silakan coba lagi.";
                $tipe_pesan = 'error';
            }
        }
    } else {
        $pesan = "Silakan upload bukti pendaftaran.";
        $tipe_pesan = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
        :root {
            --primary: #FF6B35;
            --primary-light: #FF9E6D;
            --secondary: #2EC4B6;
            --secondary-light: #6EE9DD;
            --accent: #FF9F1C;
            --accent-light: #FFC46B;
            --dark: #1A1A2E;
            --dark-light: #2D2D4A;
            --light: #F8F9FA;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --glass: rgba(255, 255, 255, 0.1);
            --glass-dark: rgba(0, 0, 0, 0.1);
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 20px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.15);
            --border-radius: 16px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: var(--light);
            overflow-x: hidden;
        }
        
        /* Header Styles */
        header {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: var(--dark);
            padding: 1rem 2rem;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        header.scrolled {
            padding: 0.7rem 2rem;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--shadow-md);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-weight: 800;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .logo i {
            margin-right: 0.5rem;
            color: var(--primary);
            font-size: 2rem;
        }
        
        .logo:after {
            content: '';
            position: absolute;
            width: 0;
            height: 3px;
            bottom: -5px;
            left: 0;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            transition: width 0.5s ease;
        }
        
        .logo:hover:after {
            width: 100%;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav li {
            margin-left: 2rem;
            position: relative;
        }
        
        nav a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
        }
        
        nav a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            transition: width 0.3s ease;
        }
        
        nav a:hover:after {
            width: 100%;
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--dark);
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 1001;
        }
        
        /* Bottom Navigation for Mobile */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            z-index: 999;
            padding: 0.5rem 0;
        }
        
        .bottom-nav-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--dark);
            font-size: 0.8rem;
            padding: 0.5rem;
            transition: all 0.3s ease;
            border-radius: 12px;
        }
        
        .nav-item.active, .nav-item:hover {
            color: var(--primary);
            background: rgba(255, 107, 53, 0.1);
        }
        
        .nav-icon {
            font-size: 1.2rem;
            margin-bottom: 0.2rem;
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 6rem 2rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }
        
        .hero-content {
            flex: 1;
            padding-right: 2rem;
            position: relative;
            z-index: 2;
        }
        
        .hero-image {
            flex: 1;
            position: relative;
            z-index: 1;
        }
        
        .hero-image img {
            width: 100%;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            transform: perspective(800px) rotateY(-10deg);
            transition: transform 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
        }
        
        .hero-image img:hover {
            transform: perspective(800px) rotateY(0);
        }
        
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            display: inline-block;
            line-height: 1.2;
        }
        
        .text-slash {
            position: relative;
            display: inline-block;
        }
        
        .text-slash:after {
            content: '';
            position: absolute;
            width: 110%;
            height: 30%;
            background: linear-gradient(45deg, var(--primary-light), transparent);
            bottom: 15%;
            left: -5%;
            transform: rotate(-5deg);
            z-index: -1;
            opacity: 0.7;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: #555;
            max-width: 600px;
            position: relative;
        }
        
        .block-reveal {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .block-reveal:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            transform: translateX(-100%);
            animation: blockReveal 1.5s cubic-bezier(0.77, 0, 0.175, 1) 0.5s forwards;
        }
        
        @keyframes blockReveal {
            0% {
                transform: translateX(-100%);
            }
            50% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(100%);
            }
        }
        
        .cta-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            cursor: pointer;
            font-size: 1rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        .btn:hover:before {
            width: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, var(--primary), var(--accent));
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.6);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-secondary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-5px);
        }
        
        .btn-full {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
        }
        
        /* Section Styles */
        section {
            padding: 5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            width: 100px;
            height: 4px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        /* Lomba Section */
        .lomba-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .lomba-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            padding: 2rem;
            text-align: center;
            border-top: 5px solid var(--primary);
        }
        
        .lomba-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }
        
        .lomba-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            transition: all 0.4s ease;
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }
        
        .lomba-card:hover .lomba-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.4);
        }
        
        .lomba-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--dark);
            font-weight: 700;
        }
        
        .lomba-desc {
            color: #666;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .lomba-harga {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.2rem;
            background: rgba(255, 107, 53, 0.1);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-block;
        }
        
        /* Resources Section */
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .resource-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-left: 5px solid var(--secondary);
            position: relative;
            overflow: hidden;
        }
        
        .resource-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 0;
            background: linear-gradient(to bottom, rgba(46, 196, 182, 0.1), transparent);
            transition: height 0.4s ease;
            z-index: 0;
        }
        
        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .resource-card:hover:before {
            height: 100%;
        }
        
        .resource-card i {
            font-size: 3rem;
            color: var(--secondary);
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .resource-card h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
            position: relative;
            z-index: 1;
        }
        
        .resource-card p {
            color: #666;
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
        }
        
        /* Pendaftaran Section */
        .pendaftaran-form {
            background: white;
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--shadow-lg);
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
        }
        
        .pendaftaran-form:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #eaeaea;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
            transform: translateY(-2px);
        }
        
        .form-group small {
            color: #888;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: block;
        }
        
        /* File Upload Styling */
        .file-upload-container {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .file-upload-label {
            display: block;
            padding: 1.5rem;
            border: 2px dashed #ccc;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-upload-label:hover {
            border-color: var(--primary);
            background-color: rgba(255, 107, 53, 0.05);
        }
        
        .file-upload-label i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .file-upload-label span {
            display: block;
            font-weight: 500;
            color: var(--dark);
        }
        
        .file-upload-label small {
            color: #888;
        }
        
        #file-name {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: var(--primary);
            font-weight: 500;
        }
        
        #image-preview {
            margin-top: 1rem;
            text-align: center;
            display: none;
        }
        
        #image-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
        }
        
        /* Alert Styles */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            font-weight: 500;
            position: relative;
            overflow: hidden;
            animation: slideIn 0.5s ease forwards;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.15);
            color: var(--success);
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .alert-error {
            background-color: rgba(220, 53, 69, 0.15);
            color: var(--danger);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal.active {
            display: flex;
            opacity: 1;
        }
        
        .modal-content {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            text-align: center;
            box-shadow: var(--shadow-lg);
            max-width: 400px;
            width: 90%;
            position: relative;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }
        
        .modal.active .modal-content {
            transform: translateY(0);
        }
        
        .modal-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, var(--success), #20c997);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        
        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .modal h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .modal p {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .modal-close {
            background: linear-gradient(45deg, var(--primary), var(--accent));
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
        }

        .modal-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--dark) 0%, #16213E 100%);
            color: white;
            padding: 4rem 2rem 5rem; /* Extra padding at bottom for mobile nav */
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        footer:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            text-align: left;
        }
        
        .footer-section h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .footer-section h3:after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: var(--primary);
            bottom: 0;
            left: 0;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        
        .footer-links a {
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .footer-links a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: -2px;
            left: 0;
            background: var(--primary);
            transition: width 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary);
        }
        
        .footer-links a:hover:after {
            width: 100%;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
        }
        
        .footer-bottom {
            max-width: 1200px;
            margin: 3rem auto 0;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        /* Animations */
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        
        @keyframes typewriter {
            from {
                width: 0;
            }
            to {
                width: 100%;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .float-animation {
            animation: float 5s ease-in-out infinite;
        }
        
        .typewriter {
            overflow: hidden;
            border-right: 3px solid var(--primary);
            white-space: nowrap;
            animation: typewriter 3s steps(40) 1s 1 normal both, blink 0.8s infinite;
        }
        
        @keyframes blink {
            from {
                border-color: transparent;
            }
            to {
                border-color: var(--primary);
            }
        }
        
        /* Particles Background */
        .particles-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
        }
        
        .particle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            opacity: 0.3;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .hero {
                flex-direction: column;
                text-align: center;
                padding-top: 8rem;
            }
            
            .hero-content {
                padding-right: 0;
                margin-bottom: 3rem;
            }
            
            .hero h1 {
                font-size: 3rem;
            }
            
            .cta-buttons {
                justify-content: center;
            }
            
            nav ul {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100vh;
                background: rgba(255, 255, 255, 0.98);
                flex-direction: column;
                justify-content: center;
                align-items: center;
                z-index: 999;
                transition: all 0.4s ease;
                opacity: 0;
                visibility: hidden;
            }
            
            nav ul.active {
                display: flex;
                opacity: 1;
                visibility: visible;
            }
            
            nav li {
                margin: 1.5rem 0;
                font-size: 1.5rem;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            /* Show bottom navigation on mobile */
            .bottom-nav {
                display: block;
            }
            
            /* Add padding to footer to account for bottom nav */
            footer {
                padding-bottom: 6rem;
            }
        }
        
        @media (max-width: 768px) {
            section {
                padding: 3rem 1.5rem;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .pendaftaran-form {
                padding: 2rem;
            }
            
            .btn {
                padding: 0.7rem 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .cta-buttons {
                flex-direction: column;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .lomba-grid,
            .resources-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header id="header">
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-landmark"></i>
                <span><?php echo SITE_NAME; ?></span>
            </div>
            <nav>
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <ul id="navMenu">
                    <li><a href="#lomba">Mata Lomba</a></li>
                    <li><a href="#resources">Dokumen</a></li>
                    <li><a href="#pendaftaran">Daftar</a></li>
                    <li><a href="#kontak">Kontak</a></li>
                </ul>
            </nav>
        </div>
    </header>
    
    <nav class="bottom-nav">
        <div class="bottom-nav-container">
            <a href="#lomba" class="nav-item">
                <i class="fas fa-trophy nav-icon"></i>
                <span>Lomba</span>
            </a>
            <a href="#resources" class="nav-item">
                <i class="fas fa-file-alt nav-icon"></i>
                <span>Dokumen</span>
            </a>
            <a href="#pendaftaran" class="nav-item">
                <i class="fas fa-pencil-alt nav-icon"></i>
                <span>Daftar</span>
            </a>
            <a href="#kontak" class="nav-item">
                <i class="fas fa-phone nav-icon"></i>
                <span>Kontak</span>
            </a>
        </div>
    </nav>
    
    <main class="container">
        <section class="hero">
            <div class="particles-container" id="particles"></div>
            <div class="hero-content" data-aos="fade-right" data-aos-duration="1200">
                <h1><span class="text-slash">====</span> <br>Satu Budaya Kuningan</h1>
                <p class="block-reveal">Event Perlombaan untuk Mengenalkan Kebudayaan Kuningan & Mengasah Bakat Siswa-Siswi di Kabupaten Kuningan</p>
                <div class="cta-buttons">
                    <a href="#pendaftaran" class="btn btn-primary">Daftar Sekarang</a>
                    <a href="#lomba" class="btn btn-secondary">Lihat Mata Lomba</a>
                </div>
            </div>
            <div class="hero-image" data-aos="fade-left" data-aos-duration="1200">
                <img src="img/cos.png" alt="SatuBudaya Kuningan" class="float-animation">
            </div>
        </section>

        <section id="lomba" class="lomba-section">
            <h2 class="section-title" data-aos="fade-up">Mata Lomba</h2>
            <div class="lomba-grid">
                <div class="lomba-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="lomba-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Tari Kreasi Kelompok</h3>
                    <p class="lomba-desc">Menampilkan kreativitas dalam mengekspresikan seni tari tradisional dan modern secara berkelompok.</p>
                    <p class="lomba-harga">Rp <?php echo number_format($harga_lomba['Tari Kreasi Kelompok'], 0, ',', '.'); ?></p>
                </div>
                
                <div class="lomba-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="lomba-icon">
                        <i class="fas fa-music"></i>
                    </div>
                    <h3>Pop Sunda</h3>
                    <p class="lomba-desc">Menampilkan bakat menyanyi dengan membawakan lagu-lagu pop berbahasa Sunda.</p>
                    <p class="lomba-harga">Rp <?php echo number_format($harga_lomba['Pop Sunda'], 0, ',', '.'); ?></p>
                </div>
                
                <div class="lomba-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="lomba-icon">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <h3>Menggambar</h3>
                    <p class="lomba-desc">Menuangkan imajinasi dan kreativitas dalam bentuk gambar dengan tema budaya Kuningan.</p>
                    <p class="lomba-harga">Rp <?php echo number_format($harga_lomba['Menggambar'], 0, ',', '.'); ?></p>
                </div>
                
                <div class="lomba-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="lomba-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Desain Poster</h3>
                    <p class="lomba-desc">Mendesain poster kreatif dengan tema pelestarian budaya daerah Kuningan.</p>
                    <p class="lomba-harga">Rp <?php echo number_format($harga_lomba['Desain Poster'], 0, ',', '.'); ?></p>
                </div>
            </div>
        </section>

        <section id="resources" class="resources-section">
            <h2 class="section-title" data-aos="fade-up">Dokumen & Twibbon</h2>
            <div class="resources-grid">
                <a href="https://drive.google.com/drive/folders/1WyDERJb_7T0gAUQIgb0UNMcmrsMCIwiQ" class="resource-card" data-aos="zoom-in" data-aos-delay="100">
                    <i class="fas fa-file-pdf"></i>
                    <h3>Berkas-Berkas</h3>
                    <p>>>Click Untuk<<< <br> Berkas-berkas yang di perlukan seperti Surat Rekomendasi, Delegasi, dll</p>
                </a>
                
                <a href="https://drive.google.com/drive/folders/1AoZgKYYavA_bzE58vuoOXAleIpbdFT7C" class="resource-card" data-aos="zoom-in" data-aos-delay="200">
                    <i class="fas fa-list-alt"></i>
                    <h3>Juknis Lomba</h3>
                    <p>>>>Click Untuk<<< <br> Petunjuk teknis peserta lomba</p>
                </a>
                
                <a href="#" class="resource-card" data-aos="zoom-in" data-aos-delay="300">
                    <i class="fas fa-camera"></i>
                    <h3>Twibbon</h3>
                    <p> >>>Click<<< <br> untuk menggunakan twibonze peserta SATU BUDAYA KUNINGAN</p>
                </a>
            </div>
        </section>

        <section id="pendaftaran" class="pendaftaran-section">
            <h2 class="section-title" data-aos="fade-up">Form Pendaftaran</h2>
            
            <?php if ($pesan && !$showModal): ?>
            <div class="alert alert-<?php echo $tipe_pesan; ?>" data-aos="fade-up">
                <?php echo $pesan; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="pendaftaran-form" data-aos="fade-up" id="pendaftaranForm">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap *</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" required value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="asal_sekolah">Asal Sekolah *</label>
                    <input type="text" id="asal_sekolah" name="asal_sekolah" required value="<?php echo isset($_POST['asal_sekolah']) ? htmlspecialchars($_POST['asal_sekolah']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="no_wa">Nomor WhatsApp *</label>
                    <input type="tel" id="no_wa" name="no_wa" required placeholder="Contoh: 081234567890" value="<?php echo isset($_POST['no_wa']) ? htmlspecialchars($_POST['no_wa']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="pilihan_lomba">Pilihan Lomba *</label>
                    <select id="pilihan_lomba" name="pilihan_lomba" required>
                        <option value="">-- Pilih Lomba --</option>
                        <option value="Tari Kreasi Kelompok" <?php echo (isset($_POST['pilihan_lomba']) && $_POST['pilihan_lomba'] == 'Tari Kreasi Kelompok') ? 'selected' : ''; ?>>Tari Kreasi Kelompok (Rp <?php echo number_format($harga_lomba['Tari Kreasi Kelompok'], 0, ',', '.'); ?>)</option>
                        <option value="Pop Sunda" <?php echo (isset($_POST['pilihan_lomba']) && $_POST['pilihan_lomba'] == 'Pop Sunda') ? 'selected' : ''; ?>>Pop Sunda (Rp <?php echo number_format($harga_lomba['Pop Sunda'], 0, ',', '.'); ?>)</option>
                        <option value="Menggambar" <?php echo (isset($_POST['pilihan_lomba']) && $_POST['pilihan_lomba'] == 'Menggambar') ? 'selected' : ''; ?>>Menggambar (Rp <?php echo number_format($harga_lomba['Menggambar'], 0, ',', '.'); ?>)</option>
                        <option value="Desain Poster" <?php echo (isset($_POST['pilihan_lomba']) && $_POST['pilihan_lomba'] == 'Desain Poster') ? 'selected' : ''; ?>>Desain Poster (Rp <?php echo number_format($harga_lomba['Desain Poster'], 0, ',', '.'); ?>)</option>
                    </select>
                </div>
                
                <div class="file-upload-container">
                    <label class="file-upload-label" for="bukti_pendaftaran">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Upload Bukti Pembayaran</span>
                        <small>Klik atau seret file ke sini</small>
                        <div id="file-name"></div>
                    </label>
                    <input type="file" id="bukti_pendaftaran" name="bukti_pendaftaran" accept=".jpg,.jpeg,.png,.pdf" required style="display: none;">
                    
                    <div id="image-preview">
                        <img src="" alt="Preview Gambar" id="preview-img">
                    </div>
                </div>
                
                <small>Format: JPG, PNG, PDF (Maks. 2MB)</small>
                
                <button type="submit" class="btn btn-primary btn-full" style="margin-top: 1.5rem;">Daftar Sekarang</button>
            </form>
        </section>
    </main>
    
    <div class="modal <?php echo $showModal ? 'active' : ''; ?>" id="successModal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-check"></i>
            </div>
            <h3>Pendaftaran Berhasil!</h3>
            <p>Terima kasih telah mendaftar. Silakan gabung ke grup WhatsApp untuk informasi lebih lanjut.</p>
            <div class="modal-buttons">
                <a href="<?php echo $whatsapp_group_link; ?>" target="_blank" class="btn btn-primary">Gabung Grup WhatsApp</a>
                <button class="modal-close btn btn-secondary" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>
    
    <footer id="kontak">
        <div class="footer-content">
            
            <div class="footer-section">
                <h3>Terdapat Kendala? Hubungi :</h3>
                <p><i class="fas fa-phone"></i><a href="https://wa.me/+6281327414126">+6281327414126</a></p>
                <div class="social-links">
                    <a href="https://www.instagram.com/forumosiskuningan?igsh=MWxjc2hnOXMwZzJ0bw=="><i class="fab fa-instagram"></i></a>
                    <a href="https://www.tiktok.com/@forumosiskuningan_?_t=ZS-8z85beRW2dU&_r=1"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 SatuBudaya Kuningan. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
   
        AOS.init({
            duration: 1200,
            once: true,
            offset: 100,
            easing: 'ease-out-cubic'
        });
        
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            // Update active state in bottom nav
            const sections = document.querySelectorAll('section');
            const navItems = document.querySelectorAll('.nav-item');
            
            let currentSection = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (window.scrollY >= sectionTop - 200) {
                    currentSection = section.getAttribute('id');
                }
            });
            
            navItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('href').substring(1) === currentSection) {
                    item.classList.add('active');
                }
            });
        });
        
        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('navMenu').classList.toggle('active');
            this.innerHTML = document.getElementById('navMenu').classList.contains('active') ? 
                '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });
        
        // Create particles background
        function createParticles() {
            const container = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random size between 5 and 20px
                const size = Math.random() * 15 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random position
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                particle.style.left = `${posX}%`;
                particle.style.top = `${posY}%`;
                
                // Random animation
                const animDuration = Math.random() * 15 + 10;
                const animDelay = Math.random() * 5;
                particle.style.animation = `float ${animDuration}s ease-in-out ${animDelay}s infinite`;
                
                // Random opacity
                particle.style.opacity = Math.random() * 0.4 + 0.1;
                
                container.appendChild(particle);
            }
        }
        
        // File upload handling
        const fileInput = document.getElementById('bukti_pendaftaran');
        const fileName = document.getElementById('file-name');
        const fileUploadLabel = document.querySelector('.file-upload-label');
        const imagePreview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                fileName.textContent = file.name;
                
                // Preview image if it's an image file
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.style.display = 'none';
                }
            }
        });
        
        // Drag and drop for file upload
        fileUploadLabel.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--primary)';
            this.style.backgroundColor = 'rgba(255, 107, 53, 0.1)';
        });
        
        fileUploadLabel.addEventListener('dragleave', function() {
            this.style.borderColor = '#ccc';
            this.style.backgroundColor = 'transparent';
        });
        
        fileUploadLabel.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#ccc';
            this.style.backgroundColor = 'transparent';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                const event = new Event('change');
                fileInput.dispatchEvent(event);
            }
        });
        
        // Modal functions
        function closeModal() {
            document.getElementById('successModal').classList.remove('active');
        }
        
        // Close modal when clicking outside
        document.getElementById('successModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Form validation
        document.getElementById('pendaftaranForm').addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[required], select[required]');
            let valid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    input.style.borderColor = 'var(--danger)';
                } else {
                    input.style.borderColor = '';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Harap isi semua field yang wajib diisi!');
            }
        });
        
        // Initialize particles when page loads
        window.addEventListener('load', function() {
            createParticles();
            
            // Show modal if there's a success message
            <?php if ($showModal): ?>
            document.getElementById('successModal').classList.add('active');
            <?php endif; ?>
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    // Close mobile menu if open
                    document.getElementById('navMenu').classList.remove('active');
                    document.getElementById('mobileMenuBtn').innerHTML = '<i class="fas fa-bars"></i>';
                    
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        
        const formElements = document.querySelectorAll('input, select, textarea');
        formElements.forEach(element => {
            element.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            element.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
    </script>
</body>
</html>