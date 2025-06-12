<?php
include __DIR__ . '/../db/db.php';
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "homzy";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$message = "";
$emailExists = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // جمع البيانات المدخلة من الفورم
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
    
    // التحقق بوضوح من قيمة الدور (role)
    $role = isset($_POST['role']) && $_POST['role'] === 'prestataire' ? 'prestataire' : 'client';
    $role_id = ($role === 'prestataire') ? 2 : 1;

    if ($fullname && $email && $phone && $password) {
        // تحقق من صيغة البريد الإلكتروني
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "❌ البريد الإلكتروني غير صالح.";
        } else {
            // تحقق من البريد الإلكتروني
            $check_email_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_email_stmt->execute([$email]);

            if ($check_email_stmt->rowCount() > 0) {
                $emailExists = true;
                $message = "❌ هذا البريد الإلكتروني مستخدم بالفعل. يرجى اختيار بريد آخر.";
            } else {
                // إدخال البيانات في جدول `users` مع تحديد الدور بشكل واضح
                $stmt = $conn->prepare("INSERT INTO users (fullname, email, phone, password, role, role_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullname, $email, $phone, $password, $role, $role_id]);

                // إذا كان الدور "prestataire"، يتم إدخال البيانات في جدول `prestataires`
                if ($role === 'prestataire') {
                    // تأكد من أن جميع الحقول المطلوبة موجودة
                    if ($fullname && $phone && $email) {
                        $stmt = $conn->prepare("INSERT INTO prestataires (name, phone, email) VALUES (?, ?, ?)");
                        $stmt->execute([$fullname, $phone, $email]);
                    } else {
                        $message = "❌ هناك بيانات مفقودة لإدخالها في جدول prestataires.";
                    }
                }

                // تخزين البيانات في الجلسة
                $_SESSION['users'] = [
                    'fullname' => $fullname,
                    'phone' => $phone,
                    'email' => $email,
                    'role' => $role
                ];

                // تخزين user_id
                $_SESSION['user_id'] = $conn->lastInsertId();

                // إعادة التوجيه بناءً على الدور
                if ($role === 'prestataire') {
                    header("Location: prestataire_signup.php");
                } else {
                    header("Location: service_providers.php");
                }
                exit();
            }
        }
    } else {
        $message = "❌ يرجى ملء جميع الحقول.";
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Homezy</title>
    <link rel="stylesheet" href="signup.css">
    <link href="../css/style.css" rel="stylesheet" />
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link href="../css/responsive.css" rel="stylesheet" />
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-position: left center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            background-size: 50% 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 400px;
            margin: 70px auto;
            animation: fadeIn 1s ease-in-out, slideRight 1s ease-in-out;
        }

        .form-wrapper {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
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
            border-color: #ef325e;
        }

        .input-group .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #ef325e;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #d62b54;
        }

        .redirect {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .redirect a {
            color: #ef325e;
            text-decoration: none;
        }

        .redirect a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
            display: block;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

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

        @keyframes slideRight {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                margin: 50px auto;
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar custom_nav-container">
    <a class="navbar-brand" href="../index.php">
        <span>Homezy</span>
    </a>
    <div class="auth-buttons">
        <a href="login.php" class="login">Log in</a>
    </div>
</nav>

<div class="container">
    <div class="form-wrapper">
        <h1>Sign Up</h1>
        <?php if (!empty($message)) : ?>
            <p class="error-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="signup.php" method="POST" onsubmit="return validateForm()">
            <div class="input-group">
                <input type="text" id="fullname" name="fullname" placeholder="Full Name" required
                       pattern="^[A-Za-zÀ-ÖØ-öø-ÿ]+(?:\s[A-Za-zÀ-ÖØ-öø-ÿ]+)+$"
                       title="Entrez un vrai nom (ex: exemple ex)">
            </div>

            <div class="input-group">
                <input type="email" id="email" name="email" placeholder="Email" required
                       pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                       title="Veuillez entrer une adresse email valide (ex: exemple@email.com)"
                       onblur="checkEmailExistence()">
                <span id="email-error" class="error-message" style="display: none;">Email déjà enregistré !</span>
            </div>

            <div class="input-group">
                <input type="tel" name="phone" placeholder="Phone number" required
                       pattern="^(0[567])[0-9]{8}$"
                       title="Le numéro doit être algérien et commencer par 05, 06 ou 07">
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Password" required
                       pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$"
                       title="Le mot de passe doit contenir au moins 8 caractères, une lettre et un chiffre">
                <button type="button" class="toggle-password" onclick="togglePassword('password', 'toggle-icon1')">
                    <span id="toggle-icon1">👁️</span>
                </button>
            </div>

            <div class="input-group">
                <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm Password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('confirm-password', 'toggle-icon2')">
                    <span id="toggle-icon2">👁️</span>
                </button>
            </div>

            <div class="input-group">
                <select name="role" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="client">Client</option>
                    <option value="prestataire">Prestataire</option>
                </select>
            </div>

            <p id="error-message" class="error-message" style="display: none;"></p>
            <button type="submit" class="submit-btn">Sign Up</button>
        </form>
        <p class="redirect">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

<script>
function validateForm() {
    let password = document.getElementById("password").value;
    let confirmPassword = document.getElementById("confirm-password").value;
    let errorMessage = document.getElementById("error-message");

    if (password.length < 8) {
        errorMessage.textContent = "Le mot de passe doit contenir au moins 8 caractères.";
        errorMessage.style.display = "block";
        return false;
    }

    if (password !== confirmPassword) {
        errorMessage.textContent = "Les mots de passe ne correspondent pas.";
        errorMessage.style.display = "block";
        return false;
    }

    errorMessage.style.display = "none";
    return true;
}

function togglePassword(inputId, iconId) {
    let input = document.getElementById(inputId);
    let icon = document.getElementById(iconId);

    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "🙈";
    } else {
        input.type = "password";
        icon.textContent = "👁️";
    }
}

function checkEmailExistence() {
    let email = document.getElementById("email").value;
    let emailError = document.getElementById("email-error");
    
    // Only check if email is valid
    if (email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        fetch('check_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(response => response.text())
        .then(data => {
            if (data === "exists") {
                emailError.style.display = "inline";
            } else {
                emailError.style.display = "none";
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    } else {
        emailError.style.display = "none";
    }
}
</script>

</body>
</html>