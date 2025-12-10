<div style="max-width:400px; margin:50px auto; padding:30px; background:#1b1e26; border-radius:10px; text-align:center;">
    <h2>Daftar Akun</h2>
    <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username Baru" required style="width:100%; padding:10px; margin:10px 0;">
        <input type="password" name="password" placeholder="Password" required style="width:100%; padding:10px; margin:10px 0;">
        <button type="submit" style="width:100%; padding:10px; background:#e50914; color:#fff; border:none;">Daftar</button>
    </form>
</div>