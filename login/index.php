<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kejora</title>
    <link rel="icon" type="image/png" href="../images/kejora-logo.png">
    <link rel="stylesheet" href="login.css">
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
                <form class="login-box" id="loginForm" action="login.php" method="POST">
                    <input type="text" id="username" name="username" class="input-lr" placeholder="Username atau Email" required>
                    <input type="password" id="password" name="password" class="input-lr" placeholder="Password" required>
                </form>
                <div class="wrapper-button">
                    <a href="../register/index.php" class="button">Buat Akun</a>
                    <button type="submit" form="loginForm">Selanjutnya</button>
                </div>

                <div id="message-area" style="margin-top: var(--space-m); text-align: center;">
                    <?php
                        session_start();
                        if (isset($_SESSION['login_error'])) {
                            echo '<p style="color: red;">' . $_SESSION['login_error'] . '</p>';
                            unset($_SESSION['login_error']);
                        }
                        if (isset($_SESSION['login_success'])) {
                            echo '<p style="color: green;">' . $_SESSION['login_success'] . '</p>';
                            unset($_SESSION['login_success']);
                        }
                    ?>
                </div>

            </div>
        </div>
    </div>
</body>
</html>