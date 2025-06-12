<?php
session_start();
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
            mail($identifier, "Votre code OTP", "Votre code est : $otp");
        } elseif ($method == "phone") {
            // Ici, vous pouvez intégrer une API SMS pour envoyer l'OTP par SMS
            file_put_contents("otp_logs.txt", "OTP pour $identifier : $otp\n", FILE_APPEND);
        }
        echo "success";
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
</head>
<body>
    <div class="container">
        <h2>Réinitialisation du mot de passe</h2>
        <p>Choisissez une méthode pour recevoir l'OTP</p>
        <form id="forgotForm">
            <div class="input-group">
                <select id="method">
                    <option value="email">Par e-mail</option>
                    <option value="phone">Par téléphone</option>
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