<?php
session_start();

// كلمة مرور سريّة للوصول إلى لوحة التحكم
$admin_secret_key = 'yoursecretkey123'; // غيّرها إلى كلمة مرور سرية قوية

// التحقق من الرابط السري أو كلمة المرور في الرابط
if (!isset($_GET['admin_key']) || $_GET['admin_key'] !== $admin_secret_key) {
   echo "إنت غير مصرح لك بالدخول.";
   exit();
}

// استرجاع الخدمات و subcategories من قاعدة البيانات
include __DIR__ . '/../db/db.php';

// استعلام لاسترجاع الخدمات
$servicesQuery = $conn->query("SELECT * FROM services");
$services = $servicesQuery->fetchAll(PDO::FETCH_ASSOC);

// استعلام لاسترجاع Subcategories
$subcategoriesQuery = $conn->query("SELECT sous_services.*, services.nom_service FROM sous_services 
                                    JOIN services ON sous_services.id_service = services.id");
$subcategories = $subcategoriesQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">  <!-- تأكد من أنك رابط ملف CSS خاص بك -->
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">لوحة التحكم</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php?admin_key=<?= $admin_secret_key ?>">الرئيسية</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">تسجيل الخروج</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="text-center">لوحة التحكم</h1>

<!-- إضافة خدمة جديدة -->
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h4>إضافة خدمة جديدة</h4>
    </div>
    <div class="card-body">
        <form action="add_service.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="service_name">اسم الخدمة:</label>
                <input type="text" class="form-control" name="service_name" id="service_name" required>
            </div>

            <div class="form-group">
                <label for="image">الصورة:</label>
                <input type="file" class="form-control" name="image" id="image" accept="image/*" required>
            </div>

            <button type="submit" class="btn btn-success btn-block">إضافة الخدمة</button>
        </form>
    </div>
</div>

        <!-- إضافة Subcategory جديدة -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h4>إضافة Subcategory جديدة</h4>
            </div>
            <div class="card-body">
                <form action="add_subcategory.php" method="POST">
                    <div class="form-group">
                        <label for="subcategory_name">اسم Subcategory:</label>
                        <input type="text" class="form-control" name="subcategory_name" id="subcategory_name" required>
                    </div>

                    <div class="form-group">
                        <label for="service_id">اختر الخدمة:</label>
                        <select class="form-control" name="service_id" id="service_id">
                            <?php foreach ($services as $service): ?>
                                <option value="<?= $service['id'] ?>"><?= $service['nom_service'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-info btn-block">إضافة Subcategory</button>
                </form>
            </div>
        </div>

        <!-- إدارة الخدمات و Subcategories -->
        <div class="row">
            <div class="col-md-6">
                <h3 class="text-center">الخدمات</h3>
                <ul class="list-group">
                    <?php foreach ($services as $service): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($service['nom_service']) ?>
                            <div>
                                <a href="edit_service.php?id=<?= $service['id'] ?>&admin_key=<?= $admin_secret_key ?>" class="btn btn-sm btn-warning">تعديل</a>
                                <a href="delete_service.php?id=<?= $service['id'] ?>&admin_key=<?= $admin_secret_key ?>" class="btn btn-sm btn-danger">حذف</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-md-6">
                <h3 class="text-center">Subcategories</h3>
                <ul class="list-group">
                    <?php foreach ($subcategories as $subcategory): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= $subcategory['nom_sous_service'] ?> (<?= $subcategory['nom_service'] ?>)
                            <div>
                                <a href="edit_subcategory.php?id=<?= $subcategory['id'] ?>&admin_key=<?= $admin_secret_key ?>" class="btn btn-sm btn-warning">تعديل</a>
                                <a href="delete_subcategory.php?id=<?= $subcategory['id'] ?>&admin_key=<?= $admin_secret_key ?>" class="btn btn-sm btn-danger">حذف</a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <?php
if (isset($_GET['view_user_id']) && isset($_GET['type'])) {
    $id = intval($_GET['view_user_id']);
    $type = $_GET['type'];

    if ($type === 'client') {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    } else {
        $stmt = $conn->prepare("SELECT * FROM prestataires WHERE id = ?");
    }

    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user):
        $admin_secret_key = $_GET['admin_key'] ?? ''; // تأكد أن المفتاح متاح
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الملف الشخصي</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="mb-4">الملف الشخصي للمستخدم</h2>
    <table class="table table-bordered">
        <tr><th>الاسم</th><td><?= htmlspecialchars($user['fullname'] ?? $user['name'] ?? 'غير متاح') ?></td></tr>
        <tr><th>البريد الإلكتروني</th><td><?= htmlspecialchars($user['email'] ?? 'غير متاح') ?></td></tr>
        <tr><th>الهاتف</th><td><?= htmlspecialchars($user['phone'] ?? 'غير متاح') ?></td></tr>
    </table>
    <a href="manage_users.php?admin_key=<?= urlencode($admin_secret_key) ?>" class="btn btn-secondary">العودة</a>
</body>
</html>
<?php
    exit;
    endif;
}

// حذف عميل
if (isset($_GET['delete_client_id'])) {
    $id = intval($_GET['delete_client_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: manage_users.php?admin_key=" . urlencode($_GET['admin_key']));
    exit;
}

// حذف مقدم خدمة
if (isset($_GET['delete_prestataire_id'])) {
    $id = intval($_GET['delete_prestataire_id']);
    $stmt = $conn->prepare("DELETE FROM prestataires WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: manage_users.php?admin_key=" . urlencode($_GET['admin_key']));
    exit;
}

// جلب البيانات
$clientsQuery = $conn->query("SELECT * FROM users");
$clients = $clientsQuery->fetchAll(PDO::FETCH_ASSOC);

$prestatairesQuery = $conn->query("SELECT * FROM prestataires");
$prestataires = $prestatairesQuery->fetchAll(PDO::FETCH_ASSOC);

// تأكد من المفتاح الإداري
$admin_secret_key = $_GET['admin_key'] ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المستخدمين</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Custom Styles */
        .container {
            max-width: 1200px;
        }
        .card-header {
            font-weight: bold;
        }
        .btn-sm {
            padding: 5px 10px;
        }
        .thead-dark th {
            background-color: #343a40;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center mb-4">قائمة المستخدمين</h2>

    

    <!-- قائمة مقدمي الخدمات -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">مقدمو الخدمات (Prestataires)</div>
        <div class="card-body">
            <table class="table table-bordered table-striped text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>الرقم</th>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>الخدمة</th>
                        <th>الصورة</th>
                        <th>بطاقة الهوية</th>
                        <th>الولاية</th>
                        <th>تاريخ الميلاد</th>
                        <th>الجنس</th>
                        <th>الوصف</th>
                        <th>نبذة</th>
                        <th>الأيام المتاحة</th>
                        <th>الأوقات المتاحة</th>
                        <th>الخيارات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prestataires as $prestat): ?>
                        <tr>
                            <td><?= $prestat['id'] ?></td>
                            <td><?= htmlspecialchars($prestat['name'] ?? 'غير متاح') ?></td>
                            <td><?= htmlspecialchars($prestat['email'] ?? 'غير متاح') ?></td>
                            <td><?= htmlspecialchars($prestat['phone'] ?? 'غير متاح') ?></td>
                            <td><?= htmlspecialchars($prestat['service'] ?? 'غير متاح') ?></td>
                            <td>
                                <?php if (!empty($prestat['photo'])): ?>
                                    <img src="<?= htmlspecialchars($prestat['photo']) ?>" alt="صورة" width="80">
                                <?php else: ?>غير متاحة<?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($prestat['id_photo'])): ?>
                                    <img src="<?= htmlspecialchars($prestat['id_photo']) ?>" alt="بطاقة الهوية" width="80">
                                <?php else: ?>غير متاحة<?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($prestat['wilaya'] ?? 'غير متاح') ?></td>
                            <td><?= htmlspecialchars($prestat['birth_date'] ?? 'غير متاح') ?></td>
                            <td><?= htmlspecialchars($prestat['gender'] ?? 'غير متاح') ?></td>
                            <td><?= htmlspecialchars($prestat['description'] ?? 'غير متاح') ?></td>
                            <td><?= htmlspecialchars($prestat['bio'] ?? 'غير متاح') ?></td>
                            <td><?= htmlspecialchars($prestat['available_days'] ?? 'غير متاح') ?></td>
                            <td><?= htmlspecialchars($prestat['available_times'] ?? 'غير متاح') ?></td>
                            <td>
                                <a href="?delete_prestataire_id=<?= $prestat['id'] ?>&admin_key=<?= urlencode($admin_secret_key) ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- قائمة العملاء -->
<div class="card mb-4">
        <div class="card-header bg-primary text-white">العملاء (Clients)</div>
        <div class="card-body">
            <table class="table table-bordered table-striped text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>الرقم</th>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>الخيارات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?= $client['id'] ?></td>
                            <td><?= htmlspecialchars($client['fullname']) ?></td>
                            <td><?= htmlspecialchars($client['email'] ?? 'غير متاح') ?></td>
                            <td><?= htmlspecialchars($client['phone'] ?? 'غير متاح') ?></td>
                            <td>
                                <a href="manage_users.php?view_user_id=<?= $client['id'] ?>&type=client&admin_key=<?= urlencode($admin_secret_key) ?>" class="btn btn-sm btn-info">عرض</a>
                                <a href="?delete_client_id=<?= $client['id'] ?>&admin_key=<?= urlencode($admin_secret_key) ?>" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من الحذف؟')">حذف</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>