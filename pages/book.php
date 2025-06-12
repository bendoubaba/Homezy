<?php
include __DIR__ . '/../db/db.php';
session_start();

// التحقق من أن المستخدم قد سجل دخوله
if (!isset($_SESSION['user_id'])) {
    // إذا لم يكن المستخدم قد سجل دخوله، إعادة توجيه إلى صفحة الدخول
    header("Location: login.php");
    exit();
}

if (isset($_GET['provider_id'])) {
    $provider_id = $_GET['provider_id'];
    
    // استعلام لاسترجاع بيانات مقدم الخدمة باستخدام ID
    $stmt = $conn->prepare("SELECT * FROM prestataires WHERE id = ?");
    $stmt->bindParam(1, $provider_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // جلب البيانات باستخدام fetch() بدلاً من get_result()
    $provider = $stmt->fetch(PDO::FETCH_ASSOC);  // يستخدم fetch مع PDO مباشرة

    if (!$provider) {
        echo "مقدم الخدمة غير موجود.";
        exit();
    } else {
        echo "رقم مقدم الخدمة صالح.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حجز الخدمة</title>
    <link rel="stylesheet" href="path/to/your/css/styles.css">
</head>
<body>
    <div class="container">
        <h2>حجز الخدمة مع <?= htmlspecialchars($provider['name']) ?></h2>

        <form method="POST" action="process_booking.php">
            <input type="hidden" name="provider_id" value="<?= $provider['id'] ?>">
            
            <div class="form-group">
                <label for="date">اختر التاريخ:</label>
                <input type="date" name="date" required>
            </div>
            
            <div class="form-group">
                <label for="time">اختر الوقت:</label>
                <input type="time" name="time" required>
            </div>

            <div class="form-group">
                <label for="message">ملاحظة:</label>
                <textarea name="message" rows="4" placeholder="اكتب أي ملاحظات أو تفاصيل إضافية"></textarea>
            </div>

            <button type="submit">حجز الآن</button>
        </form>
    </div>
</body>
</html>
