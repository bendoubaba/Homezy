<?php
session_start();

// التأكد من أن المستخدم قد سجل دخوله
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد الحجز</title>
</head>
<body>
    <div class="container">
        <h2>تم تأكيد الحجز بنجاح</h2>
        <p>شكرًا لك! لقد تم حجز الخدمة بنجاح. سنوافيك بالتفاصيل قريبًا.</p>
        <a href="profile_page.php">العودة إلى الملف الشخصي</a>
    </div>
</body>
</html>
