<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kejora</title>
    <link rel="icon" type="image/png" href="images/kejora-logo.png">
    <link rel="stylesheet" href="css/_config.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/dropdown.css">
    <link rel="stylesheet" href="css/box-user.css"> <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Text:ital@0;1&family=Fira+Mono:wght@400;500;700&family=Host+Grotesk:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
    <?php
    session_start(); // Mulai session di awal file PHP
    
    // Tentukan status login pengguna
    $is_logged_in = isset($_SESSION['user_id']);
    $username_display = $is_logged_in ? htmlspecialchars($_SESSION['username']) : '';
    $fullname_display = $is_logged_in ? htmlspecialchars($_SESSION['fullname']) : 'Pengguna';
    
    // Tentukan path foto profil
    $profile_pic_path = 'uploads/profile_pictures/default-image.png'; // Path default
    if ($is_logged_in && isset($_SESSION['profile_picture']) && !empty($_SESSION['profile_picture'])) {
        $profile_pic_path = htmlspecialchars($_SESSION['profile_picture']);
    }

    $groq_api_status = 'Belum dikonfigurasi';
    $groq_api_class = 'red';
    $google_api_status = 'Belum dikonfigurasi';
    $google_api_class = 'red';

    if ($is_logged_in) {
        require_once 'koneksi.php'; 

        $user_id = $_SESSION['user_id'];
        $sql_api = "SELECT groq_api, google_api FROM users WHERE id = ?";
        $stmt_api = mysqli_prepare($koneksi, $sql_api);
        
        if ($stmt_api === false) {
            error_log("Failed to prepare API status query: " . mysqli_error($koneksi));
        } else {
            mysqli_stmt_bind_param($stmt_api, "i", $user_id);
            mysqli_stmt_execute($stmt_api);
            mysqli_stmt_bind_result($stmt_api, $groq_api_value, $google_api_value);
            mysqli_stmt_fetch($stmt_api);
            mysqli_stmt_close($stmt_api);

            if (!empty($groq_api_value)) {
                $groq_api_status = 'Dikonfigurasi';
                $groq_api_class = 'green';
            }
            if (!empty($google_api_value)) {
                $google_api_status = 'Dikonfigurasi';
                $google_api_class = 'green';
            }
        }
        mysqli_close($koneksi); 
    }
    ?>

    <div class="container">
        <div class="navbar">
            <div class="navbar-left">
                <img src="images/kejora-nobg.png" class="logo"/>
                <button class="chat-button">
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
                        <a href="#" class="custom-dropdown-item" data-value="groq">
                            <div class="ai-dropdown-things">
                                <div class="title-ai">
                                    <img src="https://i.postimg.cc/sgkpJ0hD/deepseek.png"/>
                                    <span class="text">Groq</span>
                                </div>
                            </div>
                            <div class="check-api">
                                <span class="version-text">1.5b</span>
                            </div>
                        </a>
                        <a href="#" class="custom-dropdown-item" data-value="googleaistudio">
                            <div class="ai-dropdown-things">
                                <div class="title-ai">
                                    <img src="https://i.postimg.cc/sgkpJ0hD/deepseek.png"/>
                                    <span class="text">Google AI Studio</span>
                                </div>
                            </div>
                            <div class="check-api">
                                <span class="version-text">1.5b</span>
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
                    <a href="login/index.php" class="button login-btn">Login</a>
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
                <div class="list-chat">
                    
                </div>
            </div>
            <div class="main-chat">
                <div class="input-output">
                    <div class="prompt-box-input">
                        <p id="input">berikan saya beberapa nutrisi dari makan telur</p>
                    </div>
                    <div class="answer-output">
                        <img src="images/kejoraai.png" class="logo-output"/>
                        <p>Telur merupakan sumber nutrisi yang sangat kaya dan padat gizi. Di dalam satu butir telur, terutama bagian kuningnya, terkandung protein berkualitas tinggi yang penting untuk pembentukan otot dan jaringan tubuh. Selain itu, telur mengandung vitamin-vitamin esensial seperti vitamin A, D, E, dan B12, serta mineral seperti zat besi, fosfor, dan selenium yang mendukung fungsi kekebalan dan metabolisme tubuh. Telur juga mengandung kolin, yang sangat penting untuk kesehatan otak dan fungsi saraf. Dengan kandungan lemak sehat dan antioksidan lutein serta zeaxanthin, konsumsi telur secara moderat dapat membantu menjaga kesehatan mata dan jantung.</p>
                    </div>
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
        <a class="box-hover-2" href="profile_settings.php">
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
    <script src="js/box-user.js"></script> </body>
</html>