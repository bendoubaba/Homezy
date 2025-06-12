<?php
session_start();
// تضمين ملف autoload.php لتضمين جميع المكتبات المثبتة عبر Composer
require '../vendor/autoload.php';

// الآن يمكنك استخدام PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);



$conn = new mysqli("localhost", "root", "", "homzy");

if ($conn->connect_error) {
    die("Échec de connexion : " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["method"])) {
        $method = $_POST["method"];
        $identifier = $_POST["identifier"];
        
        // Vérifier si l'utilisateur existe
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo "Utilisateur non trouvé";
            exit;
        }
        
        $otp = rand(100000, 999999);
        $_SESSION["otp"] = $otp;
        $_SESSION["identifier"] = $identifier;
        $_SESSION["otp_expiry"] = time() + 300; // Expire dans 5 minutes
        
        if ($method == "email") {
            // Utilisation de PHPMailer pour envoyer un e-mail
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            try {
                // Paramètres du serveur SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Exemple avec Gmail
                $mail->SMTPAuth = true;
                $mail->Username = 'votre-email@gmail.com'; // Votre email
                $mail->Password = 'votre-mot-de-passe'; // Votre mot de passe
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Destinataire
                $mail->setFrom('votre-email@gmail.com', 'Homzy');
                $mail->addAddress($identifier);
                $mail->Subject = 'Votre code OTP';
                $mail->Body    = 'Votre code OTP est : ' . $otp;
                
                if ($mail->send()) {
                    echo "success";
                } else {
                    echo "Erreur d'envoi de l'email";
                }
            } catch (Exception $e) {
                echo "Erreur de l'envoi de l'email : {$mail->ErrorInfo}";
            }
        } elseif ($method == "phone") {
            // API for sending SMS (this is a placeholder, ensure you use an SMS API)
            file_put_contents("otp_logs.txt", "OTP pour $identifier : $otp\n", FILE_APPEND);
            echo "success";
        }
        exit;
    }
    
    if (isset($_POST["otp"])) {
        $otp = $_POST["otp"];
        if (!isset($_SESSION["otp"]) || time() > $_SESSION["otp_expiry"]) {
            echo "expired";
            exit;
        }
        if ($_SESSION["otp"] == $otp) {
            echo "valid";
        } else {
            echo "invalid";
        }
        exit;
    }
    
    if (isset($_POST["password"])) {
        if (!isset($_SESSION["otp"]) || time() > $_SESSION["otp_expiry"]) {
            die("Session expirée.");
        }
        $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
        $identifier = $_SESSION["identifier"];

        $sql = "UPDATE users SET password = ? WHERE email = ? OR phone = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $password, $identifier, $identifier);
        $stmt->execute();

        echo "Mot de passe mis à jour";
        session_destroy();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe</title>
    <link rel="stylesheet" href="forgot_password.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<nav class="navbar custom_nav-container">
    <a class="navbar-brand" href="../index.php">
        <span>Homezy</span>
    </a>
</nav>

<body>
    <div class="container">
        <h2>Réinitialisation du mot de passe</h2>
        <p>Choisissez une méthode pour recevoir l'OTP</p>
        <form id="forgotForm">
            <div class="input-group">
                <select id="method">
                    <option value="email">Par e-mail</option>
                </select>
                <input type="text" id="identifier" placeholder="Entrez votre email ou téléphone" required>
            </div>
            <button type="button" onclick="sendOTP()">Envoyer OTP</button>
        </form>
        <div id="otpSection" style="display:none;">
            <p>Un code OTP a été envoyé</p>
            <input type="text" id="otp" placeholder="Entrez OTP" required>
            <button type="button" onclick="verifyOTP()">Vérifier OTP</button>
        </div>
        <div id="passwordSection" style="display:none;">
            <input type="password" id="newPassword" placeholder="Nouveau mot de passe" required>
            <input type="password" id="confirmPassword" placeholder="Confirmer le mot de passe" required>
            <button type="button" onclick="resetPassword()">Réinitialiser</button>
        </div>
    </div>

    <script>
        function sendOTP() {
            let method = document.getElementById("method").value;
            let identifier = document.getElementById("identifier").value;
            
            fetch("", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `method=${method}&identifier=${identifier}`
            })
            .then(response => response.text())
            .then(data => {
                if (data === "success") {
                    document.getElementById("otpSection").style.display = "block";
                } else {
                    alert("Erreur: " + data);
                }
            });
        }

        function verifyOTP() {
            let otp = document.getElementById("otp").value;
            
            fetch("", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `otp=${otp}`
            })
            .then(response => response.text())
            .then(data => {
                if (data === "valid") {
                    document.getElementById("passwordSection").style.display = "block";
                } else if (data === "expired") {
                    alert("OTP expiré, veuillez réessayer");
                } else {
                    alert("OTP incorrect");
                }
            });
        }
        
        function resetPassword() {
            let newPassword = document.getElementById("newPassword").value;
            let confirmPassword = document.getElementById("confirmPassword").value;
            
            if (newPassword !== confirmPassword) {
                alert("Les mots de passe ne correspondent pas");
                return;
            }
            
            fetch("", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `password=${newPassword}`
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                if (data === "Mot de passe mis à jour") {
                    window.location.href = "login.php";
                }
            });
        }
    </script>
</body>
</html>




<style>
    /* Global Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

.container {
    background-color: white;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

h2 {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
}

p {
    text-align: center;
    color: #666;
    margin-bottom: 20px;
}

.input-group {
    margin-bottom: 20px;
    position: relative;
}

.input-group input, .input-group select {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
    outline: none;
    transition: border-color 0.3s;
}

.input-group input:focus, .input-group select:focus {
    border-color: #ef325e; /* Burgundy color */
}

.input-group select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-right: 35px; /* Add padding for dropdown arrow */
    background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAiIGhlaWdodD0iMTAiIHZpZXdCb3g9IjAgMCAxMCAxMCIgdmlld0JveD0iMCAwIDEwIDEwIj4KPHBhdGggZD0iTTEuMzg2IDBoM2MwLjE1MiAwIDAuODkzLjYyIDAuOTY2IDEuMDg3IDEuMjcxIDEuMjkxIDEuNDMwIDEuMzI2IDAuOTY2IDEuMzc0IDAuNjg5IDAuOTY2IDEuMTQgMCAuMTUgMCAuOTYyIDAuNDE5IDEuNjgyIDIuNzI5IDIuNDE4IDEuMjkzIDAuMjYzIDEuMzg2IDEuMzg2IDEuMzQyIDIuMjU2IDEuMjkgMi44NzEgMCAwIC0wIC0wLTIuNzcgMCAwIC0zIiBzdHJva2U9IiNGRkZGRkYiIGZpbGw9IiNGRkZGRkYiPjwvcGF0aD4KPC9zdmc+Cg==') no-repeat scroll right center;
    background-size: 12px;
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
.input-group select:focus + label,
.input-group input:not(:placeholder-shown) + label,
.input-group select:not(:placeholder-shown) + label {
    top: -10px;
    font-size: 12px;
    color: #ef325e; /* Burgundy color */
}

button {
    width: 100%;
    padding: 12px;
    background-color: #ef325e; /* Burgundy color */
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #6a0d0d;
}

#otpSection, #passwordSection {
    margin-top: 20px;
}

#otpSection input, #passwordSection input {
    margin-bottom: 10px;
}

#otpSection button, #passwordSection button {
    margin-top: 10px;
}

.alert {
    color: red;
    font-size: 14px;
    margin-top: 10px;
    text-align: center;
}










/* Navbar Styling */
.navbar {

    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 1000;
}

.navbar-brand span {
    font-size: 24px;
    font-weight: bold;
    color: #ef325e; /* Burgundy color */
}

.auth-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.auth-buttons .login {
    font-size: 16px;
    color: #ef325e;
    text-decoration: none;
}

.auth-buttons .login:hover {
    text-decoration: underline;
}


</style>

</style>
</body>
</html>
