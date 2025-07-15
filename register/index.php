<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Kejora</title>
    <link rel="icon" type="image/png" href="../images/kejora-logo.png">
    <link rel="stylesheet" href="register.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Text:ital@0;1&family=Fira+Mono:wght@400;500;700&family=Host+Grotesk:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
    <div class="container">
        <div class="box">
            <div class="top">
                <img src="../images/kejora-nobg.png" class="logo"/>
            </div>
            <div class="bottom">
                <form class="login-box" id="registerForm" action="register.php" method="POST">
                    <input type="text" id="fullname" name="fullname" class="input-lr" placeholder="Nama Lengkap" required>
                    <input type="text" id="username" name="username" class="input-lr" placeholder="Username" required>
                    <input type="email" id="email" name="email" class="input-lr" placeholder="Email" required>
                    <input type="tel" id="phonenumber" name="phonenumber" class="input-lr" placeholder="Nomor Telepon" required>
                    <input type="password" id="password" name="password" class="input-lr" placeholder="Password" required>
                    <input type="password" id="password-check" name="password-check" class="input-lr" placeholder="Ulangi Password" required>
                </form>
                <div class="wrapper-button">
                    <a href="../login/index.html" class="button">Masuk</a>
                    <button type="submit" form="registerForm">Selanjutnya</button>
                </div>
                <div id="message-area" style="margin-top: var(--space-m); text-align: center;">
                    <?php
                        // Perlu memulai session di sini juga untuk mengakses $_SESSION
                        session_start();
                        if (isset($_SESSION['success'])) {
                            echo '<p style="color: green;">' . $_SESSION['success'] . '</p>';
                            unset($_SESSION['success']); // Hapus pesan setelah ditampilkan
                        }
                        if (isset($_SESSION['error'])) {
                            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
                            unset($_SESSION['error']); // Hapus pesan setelah ditampilkan
                        }
                    ?>
                </div>

            </div>
        </div>
    </div>
    <script src="register.js"></script>
</body>
</html>