// Animasi scroll halus
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling untuk anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Animasi elemen saat scroll
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.lomba-card, .resource-card, .stat-card');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            
            if (elementPosition < screenPosition) {
                element.style.opacity = 1;
                element.style.transform = 'translateY(0)';
            }
        });
    };
    
    // Set properti awal untuk animasi
    const animatedElements = document.querySelectorAll('.lomba-card, .resource-card, .stat-card');
    animatedElements.forEach(element => {
        element.style.opacity = 0;
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });
    
    // Jalankan animasi saat scroll
    window.addEventListener('scroll', animateOnScroll);
    // Jalankan sekali saat load
    animateOnScroll();
    
    // Validasi form pendaftaran
    const pendaftaranForm = document.querySelector('.pendaftaran-form');
    if (pendaftaranForm) {
        pendaftaranForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('bukti_pendaftaran');
            const file = fileInput.files[0];
            
            if (file) {
                const fileSize = file.size;
                const maxSize = 2 * 1024 * 1024; // 2MB
                
                if (fileSize > maxSize) {
                    e.preventDefault();
                    alert('Ukuran file terlalu besar. Maksimal 2MB.');
                    fileInput.value = '';
                }
            }
        });
    }
});