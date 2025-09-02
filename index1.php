<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'config.php';

// Harga tiap lomba
$harga_lomba = [
    'Tari Kreasi Kelompok' => 75000,
    'Pop Sunda' => 50000,
    'Menggambar' => 25000,
    'Desain Poster' => 30000
];

// Inisialisasi variabel
$pesan = '';
$tipe_pesan = '';
$showModal = false;

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
    
    // Validasi input
    if (empty($nama_lengkap) || empty($asal_sekolah) || empty($pilihan_lomba)) {
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
                    $stmt = $pdo->prepare("INSERT INTO participants (nama_lengkap, asal_sekolah, pilihan_lomba, bukti_pendaftaran) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$nama_lengkap, $asal_sekolah, $pilihan_lomba, $file_name]);
                    
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
        /* ... Kode CSS yang sama ... */
    </style>
</head>
<body>
    <main class="container">
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
            <p>Terima kasih telah mendaftar. Silakan tunggu informasi lebih lanjut.</p>
            <button class="modal-close" onclick="closeModal()">Tutup</button>
        </div>
    </div>
    
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Initialize AOS (Animate On Scroll)
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
        
        // Add animation to form elements on focus
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