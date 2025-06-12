<?php
session_start();

// التأكد من أن الجلسة تحتوي على identifier (أي تم التحقق من OTP)
if (!isset($_SESSION["identifier"])) {
    header("Location: forgot_password.php"); // إعادة توجيه في حالة عدم التحقق من OTP
    exit;
}

$conn = new mysqli("localhost", "root", "", "homzy");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // تأكد من أن كلمة المرور غير فارغة
    if (isset($_POST["password"]) && !empty($_POST["password"])) {
        $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
        $identifier = $_SESSION["identifier"];

        // تحديث كلمة المرور للمستخدم
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? OR phone = ?");
        $stmt->bind_param("sss", $password, $identifier, $identifier);
        $stmt->execute();

        echo "Mot de passe mis à jour avec succès!";
        session_destroy(); // مسح الجلسة بعد التحديث
        exit;
    } else {
        echo "Veuillez entrer un mot de passe valide.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Nouveau mot de passe</h2>
        <form method="POST">
            <input type="password" name="password" id="newPassword" placeholder="Nouveau mot de passe" required>
            <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirmer le mot de passe" required>
            <button type="submit">Réinitialiser</button>
        </form>
    </div>
    <script>
        // تأكد من أن كلمة المرور الجديدة والمؤكدة متطابقتان قبل الإرسال
        const form = document.querySelector('form');
        form.addEventListener('submit', function(event) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                event.preventDefault(); // منع الإرسال
                alert("Les mots de passe ne correspondent pas.");
            }
        });
    </script>
</body>
</html>
