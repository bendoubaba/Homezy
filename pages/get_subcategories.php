<?php
// تضمين الاتصال بقاعدة البيانات
include __DIR__ . '/../db/db.php';

// التحقق من أن المعامل 'serviceId' موجود في الرابط
if (isset($_GET['serviceId'])) {
    $serviceId = $_GET['serviceId'];

    // التأكد من أن serviceId هو قيمة صحيحة (عددية)
    if (!is_numeric($serviceId)) {
        echo json_encode(['error' => 'Invalid service ID']);
        exit();
    }

    try {
        // استعلام لجلب الفئات الفرعية بناءً على ID الخدمة
        $sql = "SELECT id, nom_sous_service FROM sous_services WHERE id_service = :serviceId";
        $stmt = $conn->prepare($sql);

        // ربط المتغير مع الاستعلام
        $stmt->bindParam(':serviceId', $serviceId);

        // تنفيذ الاستعلام
        $stmt->execute();

        // جلب الفئات الفرعية من قاعدة البيانات
        $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // التحقق إذا كانت هناك فئات فرعية
        if ($subcategories) {
            // إذا كانت الفئات الفرعية موجودة، إرجاعها بتنسيق JSON
            echo json_encode($subcategories);
        } else {
            // إذا لم توجد فئات فرعية، إرجاع رسالة فارغة
            echo json_encode([]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // إذا لم يتم إرسال 'serviceId' عبر GET، إرجاع رسالة خطأ
    echo json_encode(['error' => 'Service ID is missing']);
}
?>
