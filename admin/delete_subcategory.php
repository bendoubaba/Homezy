<?php
include __DIR__ . '/../db/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM sous_services WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: admin_dashboard.php?admin_key=yoursecretkey123");
    exit();
} else {
    echo "Subcategory غير موجود.";
}
?>
