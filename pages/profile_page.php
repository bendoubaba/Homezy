<?php
session_start(); // بدء الجلسة

// التحقق من أن المستخدم قد سجل دخوله
if (!isset($_SESSION['user_id'])) {
    // إذا لم يكن المستخدم قد سجل دخوله، إعادة توجيه إلى صفحة الدخول
    header("Location: login.php");
    exit();
}

// استرجاع user_id من الجلسة
$user_id = $_SESSION['user_id'];

// الاتصال بقاعدة البيانات
include __DIR__ . '/../db/db.php';  // تأكد من أن الاتصال بقاعدة البيانات مضبوط

try {
    $conn = new PDO("mysql:host=localhost;dbname=homzy", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// التحقق من أن المعرف موجود في الـ URL
if (isset($_GET['id']) && !empty($_GET['id']) && is_numeric($_GET['id'])) {
    $provider_id = (int)$_GET['id']; // تحويل الـ id إلى عدد صحيح

    // استعلام لاسترجاع بيانات المستخدم من جدول users باستخدام id
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$provider_id]);  // استخدام id للبحث عن المستخدم

    $provider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$provider) {
        echo "❌ المستخدم غير موجود!";
        exit();
    }
} else {
    // إذا لم يتم إرسال id في الـ URL أو إذا كان id فارغًا أو غير صالح
    echo "❌ معرف المستخدم غير صالح!";
    exit();
}

// إغلاق الاتصال بقاعدة البيانات
$conn = null;
?>










<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        /* إعدادات عامة */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .profile-photo-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
    margin-bottom: 20px;
}

.container {
    animation: fadeIn 1s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.profile-photo {
    border-radius: 50%;
    overflow: hidden;
    width: 150px;
    height: 150px;
}

.btn {
    background-color: #ef325e;
    color: white;
    padding: 12px 30px;
    font-size: 1rem;
    border-radius: 30px;
    text-decoration: none;
    transition: background-color 0.3s;
}

.btn:hover {
    background-color: #d62b54;
}

        .container {
            width: 80%;
            max-width: 800px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        h2 {
            color: #34495e;
            font-size: 1.4rem;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #555;
            margin: 10px 0;
        }

        .profile-photo {
            margin-top: 20px;
            border-radius: 50%;
            overflow: hidden;
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
            margin-left: 320px;
        }

        .profile-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn {
            background-color: #ef325e;
            color: white;
            padding: 12px 30px;
            font-size: 1rem;
            border-radius: 30px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #d62b54;
        }

        .section-header {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .detail {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 10px;
        }

        /* إضافة تأثيرات */
        .container {
            animation: fadeIn 1s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>User Profile</h1>

    <!-- عرض الصورة الشخصية -->
    <div class="profile-photo">
        <?php if ($provider['photo_profil']): ?>
            <img src="../uploads/<?php echo htmlspecialchars($provider['photo_profil']); ?>" alt="Profile Photo">
        <?php else: ?>
            <img src="default-profile.jpg" alt="Default Profile Photo">
        <?php endif; ?>
    </div>

    <h2>Profile Information</h2>

    <!-- عرض المعلومات -->
    <div class="detail">
        <div class="section-header">Full Name:</div>
        <p><?php echo htmlspecialchars($provider['fullname']); ?></p>
    </div>

    <div class="detail">
        <div class="section-header">Email:</div>
        <p><?php echo htmlspecialchars($provider['email']); ?></p>
    </div>

    <div class="detail">
        <div class="section-header">Phone:</div>
        <p><?php echo htmlspecialchars($provider['phone']); ?></p>
    </div>

    <div class="detail">
        <div class="section-header">Wilaya:</div>
        <p><?php echo htmlspecialchars($provider['wilaya'] ? $provider['wilaya'] : 'Not specified'); ?></p>
    </div>

    <div class="detail">
        <div class="section-header">Bio:</div>
        <p><?php echo htmlspecialchars($provider['description'] ? $provider['description'] : 'No bio provided'); ?></p>
    </div>

    <div class="detail">
        <div class="section-header">Date of Birth:</div>
        <p><?php echo htmlspecialchars($provider['date_naissance'] ? $provider['date_naissance'] : 'Not specified'); ?></p>
    </div>

    <div class="detail">
        <div class="section-header">Gender:</div>
        <p><?php echo htmlspecialchars($provider['genre'] ? $provider['genre'] : 'Not specified'); ?></p>
    </div>

    <div style="margin-top: 30px;">
        <a href="edit_profile.php?id=<?= $provider['id'] ?>" class="btn">Edit Profile</a>
    </div>

    <div style="margin-top: 30px;">
        <a href="service_providers.php" class="btn">View Service Providers</a>
    </div>
</div>

</body>
</html>


