<?php
session_start();

$servername = "localhost"; // Modifier si nécessaire
$username = "root"; // Nom d'utilisateur de la base de données
$password = ""; // Mot de passe de la base de données
$dbname = "homzy"; // Nom de la base de données

try {
    // Connexion à la base de données avec PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Gestion des erreurs en mode exception
} catch (PDOException $e) {
    die("Échec de la connexion : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];

    // Préparer la requête SQL pour vérifier si l'utilisateur existe avec le numéro de téléphone
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE phone = ?");
    $stmt->execute([$phone]); // Passer le numéro de téléphone pour exécution de la requête

    // Vérifier si un utilisateur existe avec ce numéro
    if ($stmt->rowCount() > 0) {
        // Récupérer les résultats
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // Utilisation de FETCH_ASSOC pour obtenir un tableau associatif
        $hashedPassword = $user['password']; // Le mot de passe haché

        // Vérifier le mot de passe
        if (password_verify($password, $hashedPassword)) {
            $_SESSION["user_id"] = $user['id']; // Stocker l'ID de l'utilisateur dans la session
            $_SESSION["role"] = $user['role']; // Stocker le rôle de l'utilisateur dans la session

            // Vérifier le rôle et rediriger l'utilisateur en conséquence
            if ($_SESSION["role"] == 'prestataire') {
                header("Location: profile_page.php?id=" . $_SESSION['user_id']); // Rediriger vers le profil du prestataire
            } else if ($_SESSION["role"] == 'client') {
                header("Location: service_providers.php"); // Rediriger vers les prestataires de services
            }
            exit();
        } else {
            echo "<script>alert('Mot de passe incorrect.');</script>";
        }
    } else {
        echo "<script>alert('Numéro de téléphone non trouvé.');</script>";
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Homezy</title>

    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="../css/style.css">

  
  <link href="../css/responsive.css" rel="stylesheet" />


</head>
<body>



<nav class="navbar custom_nav-container">
                    <a class="navbar-brand" href="../index.php">
                        <span>Homezy</span>
                    </a>


                   

                        <!-- أزرار تسجيل الدخول والتسجيل -->
                        <div class="auth-buttons">
                        <a href="signup.php" class="Sign">Sign up</a>
                        </div>
                </nav>
    

   

 
    <style>
 /* Global Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
html, body {
    height: 100%;
    font-family: Arial, sans-serif;
    font-family: 'Roboto', sans-serif;
    display: flex;
    flex-direction: column;  /* Arrange children vertically */
    justify-content: space-between; /* Ensure content is spaced */
}

body {
    margin: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;  /* Center the content vertically */
    align-items: center;  /* Center content horizontally */
}

/* Header Animation */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 10%;
    background-color: #ffffff;
    animation: fadeIn 1.5s ease-in-out, slideDown 1s ease-in-out;
}

header .logo {
    font-size: 24px;
    font-weight: bold;
    color: #ef325e;
    position: absolute;
    top: 20px;
    left: 150px;
}

/* اجعل الـ navbar مثبتًا في أعلى الصفحة */
nav.navbar {
    position: fixed;

}


/* Right side navigation links */
header nav {
    margin-left: 900px;  /* Pushes the nav to the right */
    font-size: 20px;
}

header nav ul {
    list-style: none;
    display: flex;
    gap: 20px;
}

header nav ul li a {
    text-decoration: none;
    color: #130802;
    font-weight: 500;
    transition: 0.3s;
}

header nav ul li a:hover,
header nav ul li a.active {
    color: #FF9F1C;
}

/* Left side image styling */
.left-side {
    width: 40%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.left-side img {
    max-width: 100%;
    height: auto;
}

.left-side {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 40%;
    height: 100vh;
}

lottie-player {
    max-width: 50%;
}

/* Container for the form */
.container {
    width: 100%;
    max-width: 400px;
    z-index: 10; /* Ensures form content appears above the image */
    margin-top: 70px;
    margin-bottom: 30px; /* Adds space between the container and footer */
    margin-right: 70px;
    margin-left: 97px; /* Moves the container to the right */
    animation: fadeIn 1s ease-in-out, slideRight 1s ease-in-out;
}

/* Form wrapper styling */
.form-wrapper {
    background-color: #ffffff;
    padding: 50px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    animation: fadeIn 1s ease-in-out;
}

/* Add the keyframes for the fadeIn animation */
@keyframes fadeIn {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

/* Add animation for the header */
@keyframes slideDown {
    0% {
        transform: translateY(-100px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Add animation for container */
@keyframes slideRight {
    0% {
        transform: translateX(-50px);
        opacity: 0;
    }
    100% {
        transform: translateX(0);
        opacity: 1;
    }
}

h1 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
    font-size: 2rem;
}

.input-group {
    position: relative;
    margin-bottom: 20px;
    animation: slideUp 1s ease-in-out;
}

.input-group input {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #a4a4a4;
    border-radius: 5px;
    outline: none;
    transition: border-color 0.3s;
}

.input-group input:focus {
    border-color: #8B1A1A; /* Burgundy Color */
}

.input-group label {
    position: absolute;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    font-size: 16px;
    color: #aaa;
    transition: 0.3s;
}

.input-group input:focus + label,
.input-group input:not(:placeholder-shown) + label {
    top: -10px;
    left: 10px;
    font-size: 12px;
    color: #8B1A1A;
}

.submit-btn {
    width: 100%;
    padding: 12px;
    background-color: #ef325e; /* Burgundy Color */
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.submit-btn:hover {
    background-color: #6a0d0d;
    transform: scale(1.05); /* Subtle scale effect */
}

.redirect {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
}

.redirect a {
    color: #8B1A1A;
    text-decoration: none;
}

.redirect a:hover {
    text-decoration: underline;
}

/* Keyframe Animations */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

footer {
    background-color: #f7f5f5;
    color: rgb(17, 3, 3);
    padding: 50px 10%;
    margin-top: auto; /* Pushes footer to the bottom */
    width: 100%; /* Ensures full width */
    padding-top: 30px; /* Adds space between footer and content above */
    animation: fadeIn 1s ease-in-out, slideUp 1s ease-in-out;
}

footer .footer-content {
    display: flex;
    justify-content: space-between;
}

footer .footer-bottom {
    text-align: center;
    margin-top: 20px;
}

footer a {
    color: rgb(59, 16, 16);
    text-decoration: none;
    margin-right: 15px;
}

footer a:hover {
    text-decoration: underline;
}

/* Footer animation */
@keyframes slideUp {
    0% {
        transform: translateY(50px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}





    </style>
    </div>
    
    
    <div class="container">
        
        <div class="form-wrapper">
            <h1>Login
                <lottie-player 
                src="https://assets10.lottiefiles.com/packages/lf20_jcikwtux.json"  
                background="transparent"  
                speed="1"  
                style="width: 100px; height: 700px; position: absolute; top: 70%; left: 39%; transform: translate(-50%, -50%);"  
                loop  
                autoplay>
            </lottie-player>
            </h1>
            <form action="login.php" method="POST">

                <!-- Numéro de téléphone algérien -->
                <div class="input-group">
                    <input type="tel" name="phone" placeholder="Phone number" required
                           pattern="^(0[567])[0-9]{8}$"
                           title="Le numéro doit être algérien et commencer par +213">
                    <label for="phone"></label>
                </div>
                
                <!-- Password -->
                <div class="input-group">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <label for="password"></label>
                    <!-- Toggle Password Visibility (using Eye or Monkey emoji) -->
                    <button type="button" class="toggle-password">👁️</button>
                </div>
                
                <!-- Forgot Password Link -->
                <p class="forgot-password"><a href="forget_password.php">Forgot Password?</a></p>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">Login</button>
            </form>
            <p class="redirect">Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>


  



    <script>
        const togglePassword = document.querySelector('.toggle-password');
        const passwordField = document.querySelector('#password');

        togglePassword.addEventListener('click', function() {
            // Toggle the type between password and text
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;

            // Toggle the icon between eye (👁️) and monkey (🙈)
            togglePassword.textContent = passwordField.type === 'password' ? '👁️' : '🙈';
        });
    </script>

    <style>
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 10px;
        }

        .forgot-password a {
            color: #4a1212;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
    







</body>

</html>
