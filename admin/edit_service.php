<?php
include __DIR__ . '/../db/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $service_name = trim($_POST['service_name']);

        if (!empty($service_name)) {
            $stmt = $conn->prepare("UPDATE services SET nom_service = ? WHERE id = ?");
            $stmt->execute([$service_name, $id]);

            header("Location: admin_dashboard.php?admin_key=yoursecretkey123");
            exit();
        } else {
            echo "<script>alert('يرجى إدخال اسم الخدمة.');</script>";
        }
    }
} else {
    echo "الخدمة غير موجودة.";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الخدمة</title>
    <style>
        /* تصميم الصفحة */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 1.2rem;
            color: #333;
        }

        input[type="text"] {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus {
            border-color: #4CAF50;
        }

        button {
            padding: 10px 20px;
            font-size: 1rem;
            color: white;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        .alert {
            color: red;
            font-size: 1rem;
            text-align: center;
        }

        /* تلميحات على الحقول */
        input[type="text"]::placeholder {
            color: #aaa;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>تعديل الخدمة</h1>

        <?php if (isset($message)) { echo "<p class='alert'>$message</p>"; } ?>

        <form method="POST">
            <label for="service_name">اسم الخدمة:</label>
            <input type="text" name="service_name" value="<?= htmlspecialchars($service['nom_service']) ?>" required>
            <button type="submit">تعديل</button>
        </form>
    </div>
</body>
</html>
