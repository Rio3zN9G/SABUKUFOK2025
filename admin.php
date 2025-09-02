<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'config.php';


if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}


try {
    $stmt = $pdo->query("SELECT * FROM participants ORDER BY tanggal_daftar DESC");
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
  
    $total_peserta = count($participants);
    
    $stmt = $pdo->query("SELECT pilihan_lomba, COUNT(*) as jumlah FROM participants GROUP BY pilihan_lomba");
    $statistik_lomba = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $jumlah_per_lomba = [];
    foreach ($statistik_lomba as $stat) {
        $jumlah_per_lomba[$stat['pilihan_lomba']] = $stat['jumlah'];
    }
    
} catch (PDOException $e) {
    die("Error mengambil data: " . $e->getMessage());
}


if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}


function formatWhatsAppLink($number) {
    $number = preg_replace('/[^0-9]/', '', $number);
    if (substr($number, 0, 2) === '08') {
        $number = '628' . substr($number, 2);
    }
    return 'https://wa.me/' . $number;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        }
        
        /* Header Styles */
        header {
            background: rgba(255, 255, 255, 0.9);
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
        }
        
        .logo i {
            margin-right: 0.5rem;
            color: var(--primary);
            font-size: 2rem;
        }
        
        /* Main Content */
        main.container {
            max-width: 1200px;
            margin: 100px auto 2rem;
            padding: 0 2rem;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }
        
        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }
        
        .admin-header h1:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            bottom: -10px;
            left: 0;
            border-radius: 2px;
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
            transform: translateY(-3px) scale(1.05);
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
            transform: translateY(-3px);
        }
        
        .btn-sm {
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }
        
        /* Stats Section */
        .stats-section {
            margin-bottom: 3rem;
        }
        
        .stats-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .stats-section h2:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        
        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-top: 5px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .stat-info h3 {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.3rem;
            font-weight: 500;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            font-family: 'Playfair Display', serif;
        }
        
        /* Participants Section */
        .participants-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .participants-section:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
        }
        
        .participants-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .participants-section h2:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            bottom: 0;
            left: 0;
            border-radius: 2px;
        }
        
        .table-responsive {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
        }
        
        .participants-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .participants-table th {
            background: linear-gradient(45deg, var(--primary), var(--accent));
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        
        .participants-table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .participants-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .participants-table tr:hover {
            background-color: #f1f1f1;
            transition: background-color 0.3s ease;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* New styles for WhatsApp link */
        .whatsapp-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--success);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .whatsapp-link:hover {
            color: #218838;
        }

        .whatsapp-link i {
            font-size: 1rem;
        }
        
        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--dark) 0%, #16213E 100%);
            color: white;
            padding: 3rem 2rem 2rem;
            text-align: center;
            margin-top: 3rem;
        }
        
        .footer-bottom {
            max-width: 1200px;
            margin: 2rem auto 0;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        /* Animations */
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
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            main.container {
                margin-top: 120px;
                padding: 0 1.5rem;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .admin-header h1:after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .participants-section {
                padding: 1.5rem;
            }
            
            .participants-table th,
            .participants-table td {
                padding: 0.7rem;
            }
        }
        
        @media (max-width: 576px) {
            .admin-header h1 {
                font-size: 2rem;
            }
            
            .stats-section h2,
            .participants-section h2 {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-landmark"></i>
                <span><?php echo SITE_NAME; ?></span>
            </div>
            <a href="?logout=1" class="btn btn-secondary">Logout</a>
        </div>
    </header>
    
    <main class="container">
        <div class="admin-header" data-aos="fade-down">
            <h1>Panel Admin</h1>
            <p>Selamat datang, Administrator!</p>
        </div>
        
        <section class="stats-section" data-aos="fade-up">
            <h2>Statistik Pendaftaran</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Peserta</h3>
                        <p class="stat-number"><?php echo $total_peserta; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dancing"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tari Kreasi</h3>
                        <p class="stat-number"><?php echo $jumlah_per_lomba['Tari Kreasi Kelompok'] ?? 0; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-music"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pop Sunda</h3>
                        <p class="stat-number"><?php echo $jumlah_per_lomba['Pop Sunda'] ?? 0; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Menggambar</h3>
                        <p class="stat-number"><?php echo $jumlah_per_lomba['Menggambar'] ?? 0; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Desain Poster</h3>
                        <p class="stat-number"><?php echo $jumlah_per_lomba['Desain Poster'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="participants-section" data-aos="fade-up" data-aos-delay="200">
            <h2>Data Peserta</h2>
            
            <div class="table-responsive">
                <table class="participants-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Asal Sekolah</th>
                            <th>Nomor WhatsApp</th>
                            <th>Lomba</th>
                            <th>Tanggal Daftar</th>
                            <th>Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($participants) > 0): ?>
                            <?php foreach ($participants as $index => $participant): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($participant['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($participant['asal_sekolah']); ?></td>
                                <td>
                                    <?php if (!empty($participant['no_wa'])): ?>
                                        <a href="<?php echo formatWhatsAppLink($participant['no_wa']); ?>" target="_blank" class="whatsapp-link">
                                            <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($participant['no_wa']); ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $participant['pilihan_lomba']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($participant['tanggal_daftar'])); ?></td>
                                <td>
                                    <a href="uploads/<?php echo $participant['bukti_pendaftaran']; ?>" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Lihat
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada peserta yang terdaftar</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2023 SatuBudaya Kuningan. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Initialize AOS (Animate On Scroll)
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>