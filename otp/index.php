<?php
session_start();

// Cek apakah ada user_id yang perlu diverifikasi di session
// Jika tidak, tendang kembali ke halaman login
if (!isset($_SESSION['otp_user_id'])) {
    header('Location: ../login/');
    exit;
}

$error_message = '';
if (isset($_SESSION['otp_error'])) {
    $error_message = '<p style="color: red;">' . $_SESSION['otp_error'] . '</p>';
    unset($_SESSION['otp_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP</title>
    <link rel="icon" type="image/png" href="../images/kejora-logo.png">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="otp.css">
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
                <h2>Verifikasi Akun Anda</h2>
                <p>Kami telah mengirimkan kode OTP ke email Anda. Silakan masukkan kode di bawah ini.</p>
                
                <form id="otpForm" action="verify_otp.php" method="POST">
                    <div class="otp-container">
                        <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                        <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                        <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                        <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                        <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                        <input type="text" class="otp-input" name="otp[]" maxlength="1" required>
                    </div>
                </form>
                <div class="wrapper-button">
                    <div id="message-area">
                        <?php echo $error_message; ?>
                    </div>
                    <button type="button" class="resend-otp">Kirim Ulang OTP</button>
                    <button type="submit" class="button" form="otpForm">Selanjutnya</button>
                </div>
            </div>

            

        </div>
    </div>
    <script src="otp-input.js"></script>
</body>
</html>