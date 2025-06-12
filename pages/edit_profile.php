<?php
include __DIR__ . '/../db/db.php'; // الاتصال بقاعدة البيانات
session_start(); // بدء الجلسة

// التحقق من أن المستخدم قد سجل دخوله
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // إعادة توجيه إلى صفحة الدخول إذا لم يكن المستخدم قد سجل دخوله
    exit();
}

// استرجاع بيانات المستخدم بناءً على الـ ID في URL
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $provider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$provider) {
        echo "المستخدم غير موجود.";
        exit();
    }
} else {
    echo "معرف المستخدم غير صالح!";
    exit();
}

// التحقق من الـ POST لتحديث البيانات
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // استرجاع البيانات المدخلة
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $bio = $_POST['bio'];

    // التحقق من رفع صورة جديدة
    $photo_profil = $_FILES['photo_profil']['name'];
    $photo_tmp = $_FILES['photo_profil']['tmp_name'];

    if ($photo_profil) {
        $photo_path = "../uploads/" . basename($photo_profil);
        move_uploaded_file($photo_tmp, $photo_path);
    } else {
        // إذا لم يتم رفع صورة جديدة، استخدم الصورة الحالية
        $photo_path = $provider['photo_profil'];
    }

    // استعلام لتحديث البيانات في قاعدة البيانات
    $stmt = $conn->prepare("UPDATE users SET fullname = :fullname, email = :email, phone = :phone, description = :bio, photo_profil = :photo_profil WHERE id = :user_id");
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':bio', $bio);
    $stmt->bindParam(':photo_profil', $photo_path);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    // إعادة التوجيه بعد التحديث
    header("Location: profile_page.php?id=" . $user_id);
    exit();
}

// إغلاق الاتصال بقاعدة البيانات
$conn = null;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الملف الشخصي</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
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
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: right;
        }

        label {
            display: block;
            color: #333;
            font-size: 1.1rem;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
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
    </style>
</head>
<body>

<div class="container">
    <h1>تعديل الملف الشخصي</h1>

    <form method="POST" enctype="multipart/form-data">
        <!-- حقل الصورة الشخصية -->
        <div class="form-group">
            <label for="photo_profil">صورة الملف الشخصي:</label>
            <input type="file" name="photo_profil" id="photo_profil">
            <?php if ($provider['photo_profil']): ?>
                <img src="../uploads/<?= htmlspecialchars($provider['photo_profil']); ?>" alt="Profile Photo" width="100">
            <?php else: ?>
                <img src="default-profile.jpg" alt="Default Profile Photo" width="100">
            <?php endif; ?>
        </div>

        <!-- حقل الاسم الكامل -->
        <div class="form-group">
            <label for="fullname">الاسم الكامل:</label>
            <input type="text" name="fullname" id="fullname" value="<?= htmlspecialchars($provider['fullname']) ?>" required>
        </div>

        <!-- حقل البريد الإلكتروني -->
        <div class="form-group">
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($provider['email']) ?>" required>
        </div>

        <!-- حقل الهاتف -->
        <div class="form-group">
            <label for="phone">رقم الهاتف:</label>
            <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($provider['phone']) ?>" required>
        </div>

        <!-- حقل السيرة الذاتية -->
        <div class="form-group">
            <label for="bio">السيرة الذاتية:</label>
            <textarea name="bio" id="bio"><?= htmlspecialchars($provider['description']) ?></textarea>
        </div>

        <!-- زر حفظ التغييرات -->
        <button type="submit" class="btn">حفظ التغييرات</button>
    </form>
</div>

</body>
</html>
