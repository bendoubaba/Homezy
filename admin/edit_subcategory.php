<?php
include __DIR__ . '/../db/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM sous_services WHERE id = ?");
    $stmt->execute([$id]);
    $subcategory = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $subcategory_name = trim($_POST['subcategory_name']);
        $service_id = $_POST['service_id'];

        if (!empty($subcategory_name) && !empty($service_id)) {
            $stmt = $conn->prepare("UPDATE sous_services SET id_service = ?, nom_sous_service = ? WHERE id = ?");
            $stmt->execute([$service_id, $subcategory_name, $id]);

            header("Location: admin_dashboard.php");
            exit();
        } else {
            echo "<script>alert('يرجى إدخال اسم Subcategory والخدمة.');</script>";
        }
    }
} else {
    echo "Subcategory غير موجود.";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل Subcategory</title>
    <style>
        /* CSS Design */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
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

        input[type="text"], select {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, select:focus {
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
        <h1>تعديل Subcategory</h1>

        <?php if (isset($message)) { echo "<p class='alert'>$message</p>"; } ?>

        <form method="POST">
            <label for="subcategory_name">اسم Subcategory:</label>
            <input type="text" name="subcategory_name" value="<?= htmlspecialchars($subcategory['nom_sous_service']) ?>" required>

            <label for="service_id">اختر الخدمة:</label>
            <select name="service_id" id="service_id">
                <?php 
                $servicesQuery = $conn->query("SELECT * FROM services");
                while ($service = $servicesQuery->fetch(PDO::FETCH_ASSOC)):
                ?>
                    <option value="<?= $service['id'] ?>" <?= $service['id'] == $subcategory['id_service'] ? 'selected' : '' ?>>
                        <?= $service['nom_service'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit">تعديل</button>
        </form>
    </div>
</body>
</html>
