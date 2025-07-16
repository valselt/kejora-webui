<?php
session_start(); 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit;
}

require_once '../koneksi.php';

$user_id = $_SESSION['user_id'];
$sql_user_data = "SELECT fullname, username, email, phonenumber, password, profile_picture, groq_api, google_api FROM users WHERE id = ?";
$stmt_user_data = mysqli_prepare($koneksi, $sql_user_data);

if ($stmt_user_data === false) {
    die("Error preparing statement: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt_user_data, "i", $user_id);
mysqli_stmt_execute($stmt_user_data);
$result_user_data = mysqli_stmt_get_result($stmt_user_data);

$user_data = mysqli_fetch_assoc($result_user_data);

mysqli_stmt_close($stmt_user_data);
mysqli_close($koneksi);

$profile_picture_display_path = '';
if (!empty($user_data['profile_picture'])) {
    $profile_picture_display_path = '../' . htmlspecialchars($user_data['profile_picture']);
} else {
    $profile_picture_display_path = '../uploads/profile_pictures/default-image.png';
}

$username_display = htmlspecialchars($user_data['username']);
$fullname_display = htmlspecialchars($user_data['fullname']);
$email_display = htmlspecialchars($user_data['email']);
$phonenumber_display = htmlspecialchars($user_data['phonenumber']);
$password_placeholder = "********"; 

$groq_api_value = htmlspecialchars($user_data['groq_api'] ?? '');
$google_api_value = htmlspecialchars($user_data['google_api'] ?? '');

$message = '';
$message_class = '';
if (isset($_SESSION['api_message'])) {
    $message = $_SESSION['api_message']['text'];
    $message_class = $_SESSION['api_message']['type'];
    unset($_SESSION['api_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Center</title>
    <link rel="icon" type="image/png" href="../images/kejora-logo.png">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/cropper.min.css">
    <link rel="stylesheet" href="account-center.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Text:ital@0;1&family=Fira+Mono:wght@400;500;700&family=Host+Grotesk:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
    <div class="account-center">
        <div class="navbar-acc">
            <a class="navbar-left" href="../">
                <img src="../images/kejora-putih.png" class="logo"/>
            </a>
            <a href="../" class="button back-btn">
                <span class="material-symbols-outlined">reply</span>
                <p>Kembali</p>
            </a>
        </div>
        <div class="main-acc" id="mainAccContent"> <div class="box-acc">
                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $message_class; ?>" style="width: 70vw; padding: var(--space-s); border-radius: var(--radius-m); text-align: center; color: white;">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="acc-1 box-styling">
                    <img src="<?php echo $profile_picture_display_path; ?>" class="pp-acc" alt="Foto Profil Pengguna"/>
                    <div class="acc-1-1">
                        <p class="acc-title">Photo Profile</p>
                        <div class="acc-1-1-2">
                            <button id="edit-pp" class="button-acc">
                                <span class="material-symbols-outlined">edit</span>
                                <p>Ubah Photo Profile</p>
                            </button>
                            <button id="delete-pp" class="button-acc">
                                <span class="material-symbols-outlined">delete</span>
                                <p>Delete Photo Profile</p>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="acc-2 box-styling">
                    <button class="edit_biodata" id="edit_biodata">
                        <span class="material-symbols-outlined">manage_accounts</span>
                    </button>
                    <p class="acc-title">Biodata Pengguna</p>
                    <div class="acc-biodata" id="biodata">
                        <div class="acc-info">
                            <div class="info-left">
                                <p>Username</p>
                            </div>
                            <p class="colon">:</p>
                            <div class="info-right">
                                <p><?php echo $username_display; ?></p>
                            </div>
                        </div>
                        <div class="acc-info">
                            <div class="info-left">
                                <p>Nama Lengkap</p>
                            </div>
                            <p class="colon">:</p>
                            <div class="info-right">
                                <p><?php echo $fullname_display; ?></p>
                            </div>
                        </div>
                        <div class="acc-info">
                            <div class="info-left">
                                <p>Email</p>
                            </div>
                            <p class="colon">:</p>
                            <div class="info-right">
                                <p><?php echo $email_display; ?></p>
                            </div>
                        </div>
                        <div class="acc-info">
                            <div class="info-left">
                                <p>Nomor Telepon</p>
                            </div>
                            <p class="colon">:</p>
                            <div class="info-right">
                                <p><?php echo $phonenumber_display; ?></p>
                            </div>
                        </div>
                        <div class="acc-info">
                            <div class="info-left">
                                <p>Password</p>
                            </div>
                            <p class="colon">:</p>
                            <div class="info-right">
                                <p><?php echo $password_placeholder; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="acc-3 box-styling">
                    <p class="acc-title">API</p>
                    <div class="acc-biodata" id="biodata_api">
                        <div class="acc-info api" id="class-groq-api">
                            <p class="title-api">Groq API</p>
                            <input type="password" id="groq_api_input" class="input-api" placeholder="Masukkan Groq API Key" value="<?php echo $groq_api_value; ?>">
                            <button class="saveapi" id="groq_api_save">
                                <span class="material-symbols-outlined">save</span>
                            </button>
                        </div>
                        <div class="acc-info api" id="class-google-ai-studio-api">
                            <p class="title-api">Google AI Studio API</p>
                            <input type="password" id="google_api_input" class="input-api" placeholder="Masukkan Google AI Studio API Key" value="<?php echo $google_api_value; ?>">
                            <button class="saveapi" id="google_api_save">
                                <span class="material-symbols-outlined">save</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="acc-4 box-styling danger-box-styling">
                    <p class="acc-title danger-title">DANGER ZONE</p>
                    <div class="danger-zone">
                        <button class="danger-zone-button" id="change_password">
                            <span class="material-symbols-outlined">password</span>
                            <p>Ubah Password</p>
                        </button>
                        <button class="danger-zone-button" id="delete_acc_btn">
                            <span class="material-symbols-outlined">delete</span>
                            <p>Hapus Akun</p>
                        </button>
                    </div>
                </div>
                <div class="crafted-info">
                    <p>© 2025 Kejora, an Web UI by valselt. All rights reserved.</p>
                    <p>Crafted with ❤️ by Ivan Aldorino.</p>
                </div>
                
            </div>
        </div>
    </div>

    <div class="hover-overlay" id="hoverOverlay">
        <div class="hover-box" id="modalHoverBox">
            </div>
    </div>
    <script src="../js/cropper.min.js"></script>
    <script src="../js/api.js"></script> 
    <script src="account_modals.js"></script> </body>
</html>