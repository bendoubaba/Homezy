<?php
session_start(); // بدء الجلسة

// التحقق من أن المستخدم قد سجل دخوله
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// استرجاع user_id من الجلسة
$user_id = $_SESSION['user_id'];

// الاتصال بقاعدة البيانات
include __DIR__ . '/../db/db.php';  // تأكد من الاتصال بقاعدة البيانات

try {
    $conn = new PDO("mysql:host=localhost;dbname=homzy", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// استرجاع بيانات المستخدم بناءً على id الموجود في الجلسة
$sql_user = "SELECT * FROM users WHERE id = :user_id";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_user->execute();
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// التحقق من أن المستخدم موجود في قاعدة البيانات
if (!$user) {
    echo "المستخدم غير موجود.";
    exit;
}

// استعلام لاسترجاع جميع مقدمي الخدمات مع الفلاتر
$serviceFilter = isset($_GET['service']) ? $_GET['service'] : '';
$wilayaFilter = isset($_GET['wilaya']) ? $_GET['wilaya'] : '';
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT u.*, s.nom_service as service_name 
        FROM users u 
        LEFT JOIN services s ON u.profession = s.id 
        WHERE u.role = 'prestataire'";

// إضافة الفلتر الخاص بالخدمة إذا كان موجودًا
if ($serviceFilter) {
    $sql .= " AND u.profession = :service";
}

// إضافة الفلتر الخاص بالولاية إذا كان موجودًا
if ($wilayaFilter) {
    $sql .= " AND u.wilaya = :wilaya";
}

// إضافة الفلتر الخاص بالبحث حسب الاسم
if ($searchTerm) {
    $sql .= " AND LOWER(u.fullname) LIKE :search";
}

// تحضير الاستعلام
$stmt = $conn->prepare($sql);

// ربط القيم إذا كانت موجودة
if ($serviceFilter) {
    $stmt->bindParam(':service', $serviceFilter, PDO::PARAM_INT);
}

if ($wilayaFilter) {
    $stmt->bindParam(':wilaya', $wilayaFilter, PDO::PARAM_STR);
}

if ($searchTerm) {
    $stmt->bindValue(':search', '%' . strtolower($searchTerm) . '%', PDO::PARAM_STR);
}

// تنفيذ الاستعلام
$stmt->execute();
$providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// استعلام لاسترجاع جميع الولايات من جدول `users`
$wilayas = $conn->query("SELECT DISTINCT wilaya FROM users WHERE role = 'prestataire'")->fetchAll(PDO::FETCH_COLUMN);

// استعلام لاسترجاع جميع الخدمات
$services = $conn->query("SELECT * FROM services")->fetchAll(PDO::FETCH_ASSOC);

// إغلاق الاتصال بقاعدة البيانات
$conn = null;
?>





<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* إضافة الأنماط الأساسية */
        body {
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background-color: #28a745;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-radius: 0 0 10px 10px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-actions a {
            color: white;
            margin-left: 15px;
            text-decoration: none;
        }

        .search-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .filter-box {
            flex: 1;
            min-width: 200px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .providers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .provider-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .provider-card:hover {
            transform: translateY(-5px);
        }

        .provider-img {
            height: 200px;
            overflow: hidden;
        }

        .provider-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .provider-card:hover .provider-img img {
            transform: scale(1.05);
        }

        .provider-info {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .provider-name {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #28a745;
        }

        .provider-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #6c757d;
        }

        .provider-rating {
            color: #ffc107;
            margin-bottom: 10px;
        }

        .provider-wilaya {
            background-color: #f0f0f0;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
            font-size: 0.9rem;
        }

        .card-actions {
            margin-top: auto;
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
            text-align: center;
            flex: 1;
        }

        .btn-primary {
            background-color: #28a745;
            color: white;
        }

        .btn-primary:hover {
            background-color: #218838;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .providers-grid {
                grid-template-columns: 1fr;
            }

            .header-content {
                flex-direction: column;
                gap: 15px;
            }
        }

        /* 🌟 تحسين تصميم الـ Navbar */
.navbar {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 10%;
  background-color: #ffffff;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 1000;
}

/* 🚀 تحسين تصميم الشعار */
.navbar-brand {
  font-size: 24px;
  font-weight: bold;
  color:rgb(217, 53, 97);
  margin-right: 20px; /* تعديل المسافة للعلامة التجارية */
}

/* 🛠️ ضبط القائمة الأساسية */
.nav-menu {
  display: flex;
  align-items: center;
  gap: 20px;
}

/* 🌎 ضبط القائمة للـ Desktop */
.navbar-nav {
  list-style: none;
  display: flex;
  gap: 20px;
  margin: 0;
  padding: 0;
}

.navbar-nav li a {
  text-decoration: none;
  color: #130802;
  font-weight: 500;
  transition: color 0.3s;
}

.navbar-nav li a:hover,
.navbar-nav li a.active {
  color: #FF9F1C;
}

/* 🎟️ تحسين أزرار تسجيل الدخول والتسجيل */
.auth-buttons {
  display: flex;
  gap: 10px;
}

.auth-buttons a {
  text-decoration: none;
  padding: 10px 20px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: bold;
  transition: transform 0.3s ease;
}

.auth-buttons a:hover {
  transform: scale(1.1);
}

.auth-buttons .login {
  background-color: transparent;
  color: #4E3629;
  border: 1px solid #ef325e;
}

.auth-buttons .Sign {
  background: linear-gradient(45deg, #ef325e, #ef325e);
  color: #fff;
  border: none;
}

/* 📱 تحسين التجاوب للجوال */
.nav-toggle {
  display: none;
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
}

/* 📱 تعديل تخطيط القائمة على الشاشات الصغيرة */
@media (max-width: 768px) {
  .nav-toggle {
      display: block;
  }

  .nav-menu {
      position: absolute;
      top: 60px;
      left: 0;
      width: 100%;
      background-color: #ffffff;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      flex-direction: column;
      align-items: center;
      padding: 20px 0;
      display: none;
  }

  .navbar-nav {
      flex-direction: column;
      align-items: center;
      gap: 15px;
  }

  .auth-buttons {
      flex-direction: column;
      align-items: center;
      margin-top: 10px;
  }

  .nav-menu.active {
      display: flex;
  }
}

/* ✅ اجعل القائمة أفقية على الشاشات الكبيرة */
@media (min-width: 769px) {
  .nav-menu {
      display: flex !important; /* تأكد من أن القائمة تبقى ظاهرة */
      flex-direction: row; /* جعل القائمة أفقية */
      align-items: center;
      justify-content: space-between;
      width: auto; /* السماح للقائمة بالتكيف */
      position: static; /* تجنب تعارض المواضع */
      box-shadow: none; /* إزالة الظل من القائمة */
      padding: 0;
  }

  .navbar-nav {
      display: flex;
      flex-direction: row; /* ترتيب العناصر أفقياً */
      gap: 20px;
  }

  .auth-buttons {
      display: flex;
      flex-direction: row; /* ترتيب الأزرار أفقياً */
      gap: 10px;
  }
}

/* ✅ تأكد من إخفاء زر القائمة (الهامبرغر) على الشاشات الكبيرة */
@media (min-width: 769px) {
  .nav-toggle {
      display: none; /* إخفاء زر القائمة */
  }
}

    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <!-- قسم المستخدم -->
                <div class="user-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- إذا كان المستخدم قد سجل دخوله -->
                        <div class="user-links">
                            <?php if ($_SESSION['role'] === 'prestataire'): ?>
    <a href="profile_page.php?id=<?= htmlspecialchars($_SESSION['user_id']) ?>" class="btn btn-primary">My Profile</a>
<?php endif; ?>

                            <!-- رابط لتسجيل الخروج -->
                            <a href="logout.php" class="logout-link">Logout</a>
                        </div>
                    <?php else: ?>
                        <!-- إذا لم يكن المستخدم قد سجل دخوله -->
                        <?php header("Location: login.php"); exit(); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <script>
    // عرض إشعار بعد إرسال الطلب
    function showNotification() {
        alert("تم إرسال طلبك إلى الحرفي");
    }

    document.getElementById('requestButton').addEventListener('click', showNotification);

    // دالة لتصفية مقدمي الخدمة بناءً على الفلاتر
    function filterProviders() {
        const serviceFilter = document.getElementById('serviceFilter').value;
        const wilayaFilter = document.getElementById('wilayaFilter').value;
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();

        const providers = document.querySelectorAll('.provider-card');
        let visibleCount = 0;

        // التكرار على جميع مقدمي الخدمات
        providers.forEach(provider => {
            // الحصول على القيم من البيانات المخزنة في العناصر باستخدام data-attributes
            const serviceMatch = serviceFilter === '' || provider.dataset.service === serviceFilter;
            const wilayaMatch = wilayaFilter === '' || provider.dataset.wilaya === wilayaFilter;
            const nameMatch = provider.dataset.name.includes(searchTerm);

            // إذا كانت جميع الفلاتر تتطابق، عرض مقدم الخدمة
            if(serviceMatch && wilayaMatch && nameMatch) {
                provider.style.display = 'flex';
                visibleCount++;
            } else {
                provider.style.display = 'none';
            }
        });

        // عرض رسالة إذا لم تكن هناك نتائج تطابق الفلاتر
        const noResults = document.querySelector('.no-results');
        if(visibleCount === 0) {
            if(!noResults) {
                const container = document.getElementById('providersContainer');
                container.innerHTML = `
                    <div class="no-results">
                        <h3>لا توجد نتائج مطابقة لبحثك</h3>
                        <p>حاول تغيير معايير البحث</p>
                    </div>
                `;
            }
        } else if(noResults) {
            noResults.remove();
        }
    }

    // إضافة مستمعين للأحداث (Events) لكل فلتر
    document.getElementById('serviceFilter').addEventListener('change', filterProviders);
    document.getElementById('wilayaFilter').addEventListener('change', filterProviders);
    document.getElementById('searchInput').addEventListener('input', filterProviders);
</script>

<div class="container">
    <div class="search-filters">
        <div class="filter-box">
            <select id="serviceFilter">
                <option value="">جميع الخدمات</option>
                <?php foreach($services as $service): ?>
                    <option value="<?= $service['id'] ?>" <?= isset($_GET['service']) && $_GET['service'] == $service['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($service['nom_service']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-box">
            <select id="wilayaFilter">
                <option value="">جميع الولايات</option>
                <?php foreach($wilayas as $wilaya): ?>
                    <option value="<?= htmlspecialchars($wilaya) ?>" <?= isset($_GET['wilaya']) && $_GET['wilaya'] == $wilaya ? 'selected' : '' ?>>
                        <?= htmlspecialchars($wilaya) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-box">
            <input type="text" id="searchInput" placeholder="ابحث باسم مقدم الخدمة..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
    </div>
</div>

        <div class="providers-grid" id="providersContainer">
    <?php if(count($providers) > 0): ?>
        <?php foreach ($providers as $provider): ?>
            <div class="provider-card" 
            data-service="<?= $provider['service_name'] ?>"
            data-wilaya="<?= htmlspecialchars($provider['wilaya']) ?>"
                 data-name="<?= htmlspecialchars(strtolower($provider['fullname'])) ?>">
                <div class="provider-img">
                <?php if (!empty($provider['photo_profil'])): ?>
        <!-- إذا كانت الصورة موجودة، اعرضها -->
        <img src="../uploads/<?= htmlspecialchars($provider['photo_profil']) ?>" alt="<?= htmlspecialchars($provider['fullname']) ?>">
    <?php else: ?>
        <!-- إذا لم تكن الصورة موجودة، اعرض صورة افتراضية -->
        <img src="uploads/default-profile-service.jpg" alt="Default Profile Photo">
    <?php endif; ?>                alt="<?= htmlspecialchars($provider['fullname']) ?>">
                </div>
                <div class="provider-info">
                    <h3 class="provider-name"><?= htmlspecialchars($provider['fullname']) ?></h3>
                    <div class="provider-meta">
                        <span class="service-type"><?= htmlspecialchars($provider['service_name']) ?></span>
                        <span class="provider-wilaya"><?= htmlspecialchars($provider['wilaya']) ?></span>
                    </div>
                    <div class="card-actions">
                        <a href="profile_page.php?id=<?= $provider['id'] ?>" class="btn btn-primary">عرض البروفايل</a>
                        <?php if($isCustomer): ?>
                            <a href="book.php?provider_id=<?= $provider['id'] ?>" class="btn btn-secondary">حجز الخدمة</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-results">
            <h3>لا يوجد مقدمي خدمات متاحين حالياً</h3>
            <p>يمكنك المحاولة لاحقاً أو تغيير معايير البحث</p>
        </div>
    <?php endif; ?>
</div>


</body>
</html>
