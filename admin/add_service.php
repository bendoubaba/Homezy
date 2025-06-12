<?php
include __DIR__ . '/../db/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_name = trim($_POST['service_name']);
    $image = $_FILES['image']['name']; // اسم الصورة
    $target_dir = "../images/"; // المسار الذي سيتم رفع الصورة فيه
    $target_file = $target_dir . basename($image); // المسار الكامل للصورة

    // التحقق من رفع الصورة
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // التحقق من نوع الملف
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            // نقل الصورة إلى المجلد المحدد
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // الصورة تم رفعها بنجاح
                if (!empty($service_name)) {
                    $stmt = $conn->prepare("INSERT INTO services (nom_service, image) VALUES (?, ?)");
                    $stmt->execute([$service_name, $image]); // إضافة اسم الخدمة والصورة إلى قاعدة البيانات

                    header("Location: admin_dashboard.php?admin_key=yoursecretkey123");
                    exit();
                } else {
                    echo "<script>alert('يرجى إدخال اسم الخدمة.');</script>";
                }
            } else {
                echo "<script>alert('فشل رفع الصورة.');</script>";
            }
        } else {
            echo "<script>alert('الصورة غير صالحة. يجب أن تكون بصيغة JPG أو PNG أو GIF.');</script>";
        }
    } else {
        echo "<script>alert('يرجى رفع صورة للخدمة.');</script>";
    }
}
?>

