</main> <footer class="site-footer">
        <div class="container">
            <div class="footer-top">
                <div class="footer-col">
                    <a href="/" class="footer-brand">
                        <i class="ri-movie-2-fill text-primary"></i> <?= Config::SITE_NAME ?>
                    </a>
                    <p class="footer-desc">
                        Platform streaming drama Asia terbaik dengan subtitle Indonesia. 
                        Nikmati ribuan judul drama Korea, China, dan Thailand secara gratis dan legal.
                    </p>
                    <div class="social-icons">
                        <a href="#"><i class="ri-facebook-fill"></i></a>
                        <a href="#"><i class="ri-instagram-fill"></i></a>
                        <a href="#"><i class="ri-twitter-x-fill"></i></a>
                        <a href="https://t.me/jejakintel"><i class="ri-telegram-fill"></i></a>
                    </div>
                </div>

                <div class="footer-col">
                    <h4>Jelajahi</h4>
                    <ul>
                        <li><a href="/">Beranda</a></li>
                        <li><a href="/search?q=korea">Drama Korea</a></li>
                        <li><a href="/search?q=china">Drama China</a></li>
                        <li><a href="/dashboard/billing">Paket Premium</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Bantuan</h4>
                    <ul>
                        <li><a href="/terms">Syarat & Ketentuan</a></li>
                        <li><a href="/privacy">Kebijakan Privasi</a></li>
                        <li><a href="https://t.me/jejakintel" target="_blank">Pusat Bantuan</a></li>
                        <li><a href="https://t.me/jejakintel" target="_blank">Lapor Error</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4>Download Aplikasi</h4>
                    <p style="font-size:0.9rem; color:#888; margin-bottom:15px;">Tonton di mana saja dengan aplikasi kami.</p>
                    <div class="app-buttons">
                        <button class="btn-store"><i class="ri-google-play-fill"></i> Google Play</button>
                        <button class="btn-store"><i class="ri-apple-fill"></i> App Store</button>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <strong><?= Config::SITE_NAME ?></strong>. Made with <i class="ri-heart-fill text-primary"></i> for Drama Lovers.</p>
                
                <button onclick="scrollToTop()" id="scrollTopBtn" title="Ke Atas">
                    <i class="ri-arrow-up-line"></i>
                </button>
            </div>
        </div>
    </footer>

    <style>
    .site-footer { background: #0a0a0c; border-top: 1px solid #1f1f22; padding: 60px 0 20px; margin-top: 50px; }
    .footer-top { display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 40px; margin-bottom: 40px; }
    
    .footer-brand { font-size: 1.8rem; font-weight: 800; color: white; text-decoration: none; display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
    .footer-desc { color: #888; line-height: 1.6; font-size: 0.9rem; margin-bottom: 20px; max-width: 300px; }
    
    .social-icons { display: flex; gap: 15px; }
    .social-icons a { width: 40px; height: 40px; background: #1f1f22; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: white; transition: 0.3s; }
    .social-icons a:hover { background: var(--primary); transform: translateY(-3px); }

    .footer-col h4 { color: white; margin-bottom: 20px; font-size: 1.1rem; }
    .footer-col ul { list-style: none; padding: 0; }
    .footer-col ul li { margin-bottom: 10px; }
    .footer-col ul li a { color: #888; text-decoration: none; transition: 0.3s; font-size: 0.95rem; }
    .footer-col ul li a:hover { color: var(--primary); padding-left: 5px; }

    .btn-store { background: #1f1f22; border: 1px solid #333; color: white; padding: 10px 15px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 10px; width: 100%; margin-bottom: 10px; transition: 0.3s; }
    .btn-store:hover { border-color: var(--primary); background: #151518; }

    .footer-bottom { border-top: 1px solid #1f1f22; padding-top: 20px; text-align: center; color: #666; font-size: 0.9rem; position: relative; }
    .text-primary { color: #e50914; }

    #scrollTopBtn { position: absolute; right: 0; top: -25px; background: var(--primary); color: white; border: none; width: 50px; height: 50px; border-radius: 50%; cursor: pointer; box-shadow: 0 5px 20px rgba(229,9,20,0.4); transition: 0.3s; display: none; align-items: center; justify-content: center; font-size: 1.2rem; }
    #scrollTopBtn:hover { transform: translateY(-5px); }

    @media (max-width: 900px) {
        .footer-top { grid-template-columns: 1fr; gap: 30px; }
        #scrollTopBtn { right: 50%; transform: translateX(50%); top: -25px; }
    }
    </style>

    <script>
    // Logic Scroll to Top
    window.onscroll = function() {
        var btn = document.getElementById("scrollTopBtn");
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            btn.style.display = "flex";
        } else {
            btn.style.display = "none";
        }
    };
    function scrollToTop() {
        window.scrollTo({top: 0, behavior: 'smooth'});
    }
    </script>
    
    <script>
(function() {
    // 1. BLOKIR SHORTCUT KEYBOARD (Ctrl+U, F12, Ctrl+Shift+I)
    document.onkeydown = function(e) {
        if (
            e.keyCode === 123 || // F12
            (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) || // Ctrl+Shift+I/J/C
            (e.ctrlKey && e.keyCode === 85) // Ctrl+U (View Source)
        ) {
            e.preventDefault(); // Batalkan aksi default
            return false;
        }
    };

    // 2. JEBAKAN DEBUGGER (AUTO-CLOSE JIKA INSPECT ELEMENT DIBUKA)
    function detectDevTool(allow) {
        if(isNaN(+allow)) allow = 100;
        var start = +new Date(); 
        
        // Browser akan berhenti disini jika DevTools/Inspect Element nyala
        debugger; 
        
        var end = +new Date(); 
        
        // Jika ada jeda waktu (artinya user sedang membuka Inspect Element)
        if(isNaN(start) || isNaN(end) || end - start > allow) {
            // AKSI: Hapus konten website dan ganti jadi kosong
            document.body.innerHTML = '<div style="display:flex;justify-content:center;align-items:center;height:100vh;background:black;color:red;font-size:2rem;font-weight:bold;">AKSES DITOLAK</div>';
            
            // Redirect paksa ke halaman kosong untuk "menutup" akses
            window.location.href = "about:blank"; 
        }
    }

    // Jalankan pengecekan terus menerus saat user menggerakkan mouse atau resize layar
    if(window.attachEvent) {
        if (document.readyState === "complete" || document.readyState === "interactive") {
            detectDevTool();
            window.attachEvent('onresize', detectDevTool);
            window.attachEvent('onmousemove', detectDevTool);
            window.attachEvent('onfocus', detectDevTool);
            window.attachEvent('onblur', detectDevTool);
        } else {
            setTimeout(argument.callee, 0);
        }
    } else {
        window.addEventListener('load', detectDevTool);
        window.addEventListener('resize', detectDevTool);
        window.addEventListener('mousemove', detectDevTool);
        window.addEventListener('focus', detectDevTool);
        window.addEventListener('blur', detectDevTool);
    }
})();
</script>
    
</body>
</html>
