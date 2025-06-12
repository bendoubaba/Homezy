<?php
include __DIR__ . '/../db/db.php';
session_start();

// التحقق من أن المستخدم قد سجل دخوله
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// التحقق من وجود البيانات المرسلة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['provider_id'], $_POST['date'], $_POST['time'])) {
    $user_id = $_SESSION['user_id'];
    $provider_id = $_POST['provider_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $message = isset($_POST['message']) ? $_POST['message'] : '';  // إذا كانت موجودة

    // تأكد من أن الـ date و الـ time تم تنسيقهما بشكل صحيح
    $date = date('Y-m-d', strtotime($date)); // تأكد من أن التاريخ بصيغة Y-m-d
    $time = date('H:i', strtotime($time));  // تأكد من أن الوقت بصيغة H:i

    // استعلام لإدخال الحجز في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, provider_id, date, time, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");

    // ربط المعاملات مع الاستعلام
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':provider_id', $provider_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':time', $time, PDO::PARAM_STR);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);

    // التحقق من تنفيذ الاستعلام
    if ($stmt->execute()) {
        // إذا تم الحجز بنجاح، إعادة توجيه إلى صفحة تأكيد
        header("Location: confirmation.php");
        exit();
    } else {
        echo "حدث خطأ أثناء الحجز.";
    }
}
?>
