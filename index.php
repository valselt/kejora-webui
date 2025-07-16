<?php
    session_start(); 
    require_once 'koneksi.php'; 

    $is_logged_in = isset($_SESSION['user_id']);
    
    // Inisialisasi variabel dengan nilai default
    $username_display = '';
    $fullname_display = 'Pengguna';
    $profile_pic_path = 'uploads/profile_pictures/default-image.png';
    $groq_api_status = 'Belum dikonfigurasi';
    $groq_api_class = 'red'; 
    $google_api_status = 'Belum dikonfigurasi';
    $google_api_class = 'red';

    if ($is_logged_in) {
        $user_id = $_SESSION['user_id'];
        
        // Query database untuk semua data yang diperlukan
        $sql = "SELECT username, fullname, profile_picture, groq_api, google_api FROM users WHERE id = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        
        if ($stmt === false) {
            error_log("Failed to prepare user data query: " . mysqli_error($koneksi));
        } else {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            // Bind semua kolom yang kita SELECT
            mysqli_stmt_bind_result($stmt, $db_username, $db_fullname, $db_profile_pic, $db_groq_api, $db_google_api);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            // Perbarui semua variabel dengan data dari database
            $username_display = htmlspecialchars($db_username);
            $fullname_display = htmlspecialchars($db_fullname);

            // Logika untuk foto profil, sekarang menggunakan data dari DB
            if (!empty($db_profile_pic)) {
                $profile_pic_path = htmlspecialchars($db_profile_pic);
            }

            // Logika untuk status API
            if (!empty($db_groq_api)) {
                $groq_api_status = 'Dikonfigurasi';
                $groq_api_class = 'green';
            }
            if (!empty($db_google_api)) {
                $google_api_status = 'Dikonfigurasi';
                $google_api_class = 'green';
            }
            
            // Tetap update session jika belum sesuai, untuk konsistensi
            $_SESSION['username'] = $db_username;
            $_SESSION['fullname'] = $db_fullname;
            $_SESSION['profile_picture'] = $db_profile_pic;
        }
        mysqli_close($koneksi); 
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kejora by PKKMB UNNES 2025</title>
    <link rel="icon" type="image/png" href="images/kejora-logo.png">
    <link rel="stylesheet" href="css/_config.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/dropdown.css">
    <link rel="stylesheet" href="css/box-user.css"> 
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Text:ital@0;1&family=Fira+Mono:wght@400;500;700&family=Host+Grotesk:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
    

    <div class="container">
        <div class="navbar">
            <div class="navbar-left">
                <img src="images/kejora-nobg.png" class="logo"/>
                <button class="button chat-button">
                    <span class="material-symbols-outlined">forum</span>
                </button>
            </div>
            <div class="dropdrown">
                <div class="custom-dropdown-wrapper" >
                    <button class="custom-dropdown-button" id="server-selected">
                        <div class="selected-item-content">
                            <span>Server</span>
                        </div>
                        <span class="material-symbols-outlined">arrow_drop_down</span>
                    </button>
                    <div class="custom-dropdown-panel">
                        <a href="#" class="custom-dropdown-item <?php echo ($groq_api_class === 'red') ? 'is-disabled' : ''; ?>" data-value="groq" data-api-status="<?php echo $groq_api_class; ?>">
                            <div class="ai-dropdown-things">
                                <div class="title-ai">
                                    <img src="images/icon/groq.png"/>
                                    <span class="text">Groq</span>
                                </div>
                            </div>
                        </a>
                        <a href="#" class="custom-dropdown-item <?php echo ($google_api_class === 'red') ? 'is-disabled' : ''; ?>" data-value="googleaistudio" data-api-status="<?php echo $google_api_class; ?>">
                            <div class="ai-dropdown-things">
                                <div class="title-ai">
                                    <img src="images/icon/gemma.png"/>
                                    <span class="text">Google AI Studio</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="custom-dropdown-wrapper">
                    <button class="custom-dropdown-button is-disabled" id="model-selected">
                        <div class="selected-item-content" id="model-selected-content">
                            <span>Model</span>
                        </div>
                        <span class="material-symbols-outlined">arrow_drop_down</span>
                    </button>
                    <div class="custom-dropdown-panel" id="models-panel">
                        
                    </div>
                </div>
            </div>
            <div class="user" id="userProfileArea">
                <?php if (!$is_logged_in): ?>
                    <a href="login/" class="button login-btn">Login</a>
                <?php else: ?>
                    <p class="login-btn-verified" id="loggedInUsername">Selamat Datang, <?php echo $fullname_display; ?></p>
                    <img src="<?php echo $profile_pic_path; ?>" class="img-profile" alt="Foto Profil" id="profileImage"/>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="main">
            <div class="menu-chat" style="display: none;">
                <button class="new-chat">
                    <span class="material-symbols-outlined">add</span>
                    <p>New Chat</p>
                </button>
                <p class="title-chats">Chats</p>
                <div class="list-chats">
                    <p class="bar-chats"></p>
                </div>
            </div>
            <div class="main-chat">
                <div class="chat-opener">
                    <img src="images/kejoraai.png"/>
                    <p>Apa yang anda pikirkan hari ini?</p>
                </div>

                <div class="input-output">
                    
                </div>
                <form class="chat-bar">
                    <textarea id="prompt-input" placeholder="Tanya Kejora..." rows="1"></textarea>
                    <button type="submit" class="send-btn">
                        <span class="material-symbols-outlined">send</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <?php if ($is_logged_in): ?>
    <div class="profile-hover-box" id="profileHoverBox">
        <a class="box-hover-2" href="account-center/">
            <span class="material-symbols-outlined">arrow_outward</span>
            <span class="account-center-text">Account Center</span> </a>
        <hr class="divider">
        <div class="box-hover-api">
            <p>Groq API</p>
            <p class="api-verifier <?php echo $groq_api_class; ?>" id="grok_api_verified"><?php echo $groq_api_status; ?></p>
        </div>
        <div class="box-hover-api">
            <p>Google AI Studio API</p>
            <p class="api-verifier <?php echo $google_api_class; ?>" id="google_api_verified"><?php echo $google_api_status; ?></p>
        </div>
        <form action="logout.php" method="POST" class="logout-form">
            <button type="submit" class="logout-button">Logout</button>
        </form>
    </div>
    <?php endif; ?>
    <script src="js/navbar.js"></script>
    <script src="js/dropdown.js"></script>
    <script src="js/chat.js"></script>
    <script src="js/box-user.js"></script> 
</body>
</html>