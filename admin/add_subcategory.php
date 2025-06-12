<?php
include __DIR__ . '/../db/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subcategory_name = trim($_POST['subcategory_name']);
    $service_id = $_POST['service_id'];

    if (!empty($subcategory_name) && !empty($service_id)) {
        $stmt = $conn->prepare("INSERT INTO sous_services (id_service, nom_sous_service) VALUES (?, ?)");
        $stmt->execute([$service_id, $subcategory_name]);

        header("Location: admin_dashboard.php?admin_key=yoursecretkey123");
        exit();
    } else {
        echo "<script>alert('يرجى إدخال اسم Subcategory والخدمة.');</script>";
    }
}
?>
