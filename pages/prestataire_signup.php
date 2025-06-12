<?php
include __DIR__ . '/../db/db.php';  // الاتصال بقاعدة البيانات
session_start();

// التحقق من أن المستخدم قد سجل دخوله ولديه دور "prestataire"
if (!isset($_SESSION['users']) || $_SESSION['users']['role'] !== 'prestataire') {
    header("Location: signup.php");
    exit();
}

$user = $_SESSION['users']; // استرجاع بيانات المستخدم من الجلسة

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // استقبال جميع بيانات الفورم
    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $wilaya = isset($_POST['wilaya']) ? trim($_POST['wilaya']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $date_naissance = isset($_POST['date_naissance']) ? trim($_POST['date_naissance']) : '';
    $genre = isset($_POST['sexe']) ? trim($_POST['sexe']) : '';
    $profession = isset($_POST['metier']) ? trim($_POST['metier']) : '';

    // التحقق من وجود البريد الإلكتروني في قاعدة البيانات
    $stmt_check_email = $conn->prepare("SELECT id FROM prestataires WHERE email = ?");
    $stmt_check_email->execute([$email]);

    if ($stmt_check_email->rowCount() > 0) {
        // إذا كان البريد الإلكتروني موجودًا بالفعل في قاعدة البيانات
        $message = "❌ هذا البريد الإلكتروني مستخدم بالفعل في مقدمي الخدمة.";
    } else {
        // تحميل الصورة الشخصية
        $uploadDir = __DIR__ . '/../uploads/';
        $photo_profil = '';

        if (!empty($_FILES['photo']['name'])) {
            $photoName = basename($_FILES['photo']['name']);
            $photoPath = $uploadDir . $photoName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
                $photo_profil = $photoName;  // فقط اسم الملف نخزنه
            } else {
                echo "❌ خطأ في تحميل الصورة الشخصية.";
                exit();
            }
        }

        // بناء استعلام التحديث في جدول `users`
        $sql = "UPDATE users SET 
            fullname = :fullname,
            phone = :phone,
            wilaya = :wilaya,
            description = :description,
            date_naissance = :date_naissance,
            genre = :genre,
            profession = :profession,
            updated_at = NOW()";

        // إضافة حقل الصورة الشخصية إذا كانت موجودة
        if ($photo_profil) {
            $sql .= ", photo_profil = :photo_profil";
        }

        $sql .= " WHERE id = :id";

        $stmt = $conn->prepare($sql);

        // ربط القيم
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':wilaya', $wilaya);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':date_naissance', $date_naissance);
        $stmt->bindParam(':genre', $genre);
        $stmt->bindParam(':profession', $profession);
        $stmt->bindParam(':id', $user['id']); // استخدام id من الجلسة مباشرة

        // ربط صورة البروفايل إذا كانت موجودة
        if ($photo_profil) {
            $stmt->bindParam(':photo_profil', $photo_profil);
        }

        // تنفيذ استعلام التحديث
        if ($stmt->execute()) {
            // تحديث الجلسة أيضا بعد نجاح التحديث
            $_SESSION['users']['fullname'] = $fullname;
            $_SESSION['users']['phone'] = $phone;
            $_SESSION['users']['wilaya'] = $wilaya;
            $_SESSION['users']['description'] = $description;
            $_SESSION['users']['date_naissance'] = $date_naissance;
            $_SESSION['users']['genre'] = $genre;
            $_SESSION['users']['profession'] = $profession;

            // تحديث الصورة الشخصية إذا كانت موجودة
            if ($photo_profil) {
                $_SESSION['users']['photo_profil'] = $photo_profil;
            }

            // إدخال بيانات المستخدم في جدول `prestataires` بعد التحقق من البريد
            $stmt_insert_prestataire = $conn->prepare("INSERT INTO prestataires (name, phone, email, wilaya, description, date_naissance, genre, profession, photo_profil) 
                                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_insert_prestataire->execute([$fullname, $phone, $email, $wilaya, $description, $date_naissance, $genre, $profession, $photo_profil]);

            // إعادة التوجيه إلى صفحة البروفايل
            header("Location: profile_page.php?id=" . $user['id']);
            exit();
        } else {
            echo "❌ خطأ أثناء تحديث البيانات.";
        }
    }
}
?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Service Provider</title>
    <link rel="stylesheet" type="text/css" href="prestataire.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group input[type="file"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="date"]:focus,
        .form-group input[type="file"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #28a745;
        }

        .form-group textarea {
            resize: vertical;
            height: 120px;
        }
        .radio-group {
            display: flex;
            gap: 20px;
        }

        .radio-group label {
            font-weight: normal;
            color: #333;
        }
        button {
            display: block;
            width: 100%;
            padding: 14px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }

        .animated-element {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        /* Style for Subcategories */
        #subcategories-list {
            margin-top: 10px;
        }

        .subcategory-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 5px;
            border-radius: 5px;
            background-color: #f9f9f9;
            transition: background-color 0.3s ease;
        }

        .subcategory-item:hover {
            background-color: #e2e2e2; 
        }

        .subcategory-item input[type="checkbox"] {
            margin-right: 10px;
            accent-color: #28a745; 
        }

        .subcategory-item label {
            font-size: 1rem;
            color: #333;
            cursor: pointer;
        }

        .subcategory-item input[type="checkbox"]:focus + label {
            font-weight: bold;
            color: #28a745;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Become a Service Provider</h2>
        <p>You bring the skill, we make the gain easy.</p>

        <form method="POST" enctype="multipart/form-data">
            <!-- Form Fields for User Info -->

            <div class="form-group">
                <label for="fullname">Full Name *</label>
                <input type="text" id="fullname" name="name" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>




            <!-- Subcategory Selection -->
            <!-- Profession Selection -->
           <div id="professions-list">
<?php
$sql = "SELECT id, nom_service FROM services";
$stmt = $conn->prepare($sql);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '
    <div class="category-item">
        <input type="radio" id="service_'.$row['id'].'" name="metier" value="'.$row['id'].'" onchange="loadSubcategories(this.value)">
        <label for="service_'.$row['id'].'">'.htmlspecialchars($row['nom_service']).'</label>
    </div>';
}
?>
</div>

<!-- Subcategory Selection -->
<div class="form-group">
    <label>Subcategory *</label>
    <p>You can select more than one</p>
    <div id="subcategories-list">
        <!-- Subcategories will be loaded dynamically here -->
    </div>
</div>
            

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4" placeholder="Tell us about yourself..."></textarea>
            </div>

            <div class="form-group">
                <label for="wilaya">Wilaya *</label>
                <select id="wilaya" name="wilaya" required>
                    <option value="">Sélectionnez une wilaya</option>
                    <option value="Adrar">01- Adrar</option>
                    <option value="Chlef">02- Chlef</option>
                    <option value="Laghouat">03- Laghouat</option>
                    <option value="Oum El Bouaghi">04- Oum El Bouaghi</option>
                    <option value="Batna">05- Batna</option>
                    <option value="Béjaïa">06- Béjaïa</option>
                    <option value="Biskra">07- Biskra</option>
                    <option value="Béchar">08- Béchar</option>
                    <option value="Blida">09- Blida</option>
                    <option value="Bouira">10- Bouira</option>
                    <option value="Tamanrasset">11- Tamanrasset</option>
                    <option value="Tébessa">12- Tébessa</option>
                    <option value="Tlemcen">13- Tlemcen</option>
                    <option value="Tiaret">14- Tiaret</option>
                    <option value="Tizi Ouzou">15- Tizi Ouzou</option>
                    <option value="Alger">16- Alger</option>
                    <option value="Djelfa">17- Djelfa</option>
                    <option value="Jijel">18- Jijel</option>
                    <option value="Sétif">19- Sétif</option>
                    <option value="Saïda">20- Saïda</option>
                    <option value="Skikda">21- Skikda</option>
                    <option value="Sidi Bel Abbès">22- Sidi Bel Abbès</option>
                    <option value="Annaba">23- Annaba</option>
                    <option value="Guelma">24- Guelma</option>
                    <option value="Constantine">25- Constantine</option>
                    <option value="Médéa">26- Médéa</option>
                    <option value="Mostaganem">27- Mostaganem</option>
                    <option value="M’Sila">28- M’Sila</option>
                    <option value="Mascara">29- Mascara</option>
                    <option value="Ouargla">30- Ouargla</option>
                    <option value="Oran">31- Oran</option>
                    <option value="El Bayadh">32- El Bayadh</option>
                    <option value="Illizi">33- Illizi</option>
                    <option value="Bordj Bou Arreridj">34- Bordj Bou Arreridj</option>
                    <option value="Boumerdès">35- Boumerdès</option>
                    <option value="El Tarf">36- El Tarf</option>
                    <option value="Tindouf">37- Tindouf</option>
                    <option value="Tissemsilt">38- Tissemsilt</option>
                    <option value="El Oued">39- El Oued</option>
                    <option value="Khenchela">40- Khenchela</option>
                    <option value="Souk Ahras">41- Souk Ahras</option>
                    <option value="Tipaza">42- Tipaza</option>
                    <option value="Mila">43- Mila</option>
                    <option value="Aïn Defla">44- Aïn Defla</option>
                    <option value="Naâma">45- Naâma</option>
                    <option value="Aïn Témouchent">46- Aïn Témouchent</option>
                    <option value="Ghardaïa">47- Ghardaïa</option>
                    <option value="Relizane">48- Relizane</option>
                    <option value="Timimoun">49- Timimoun</option>
                    <option value="Bordj Badji Mokhtar">50- Bordj Badji Mokhtar</option>
                    <option value="Ouled Djellal">51- Ouled Djellal</option>
                    <option value="Béni Abbès">52- Béni Abbès</option>
                    <option value="In Salah">53- In Salah</option>
                    <option value="In Guezzam">54- In Guezzam</option>
                    <option value="Touggourt">55- Touggourt</option>
                    <option value="Djanet">56- Djanet</option>
                    <option value="El M’Ghair">57- El M’Ghair</option>
                    <option value="El Meniaa">58- El Meniaa</option>
                </select>
            </div>

            <div class="form-group">
                <label for="photo">Choose a Profile Photo *</label>
                <input type="file" id="photo" name="photo" required>
            </div>

            <div class="form-group">
                <label for="date_naissance">Date of Birth *</label>
                <small class="form-text text-muted">You should be more than 20 years old.</small>
    <input type="date" id="birth_date" name="date_naissance" required max="<?php echo date('Y-m-d', strtotime('-20 years')); ?>">
            </div>

            <div class="form-group">
                <label for="sexe">Gender *</label>
                <div class="radio-group">
                    <input type="radio" id="homme" name="sexe" value="homme" required>
                    <label for="homme">Man</label>
                    <input type="radio" id="femme" name="sexe" value="femme">
                    <label for="femme">Woman</label>
                </div>
            </div>
            <button type="submit" class="animated-element">Submit</button>
        </form>
    </div>


    <script>
    // Character count for description
    document.getElementById("description").addEventListener("input", function() {
        let maxLength = 500;
        let currentLength = this.value.length;
        let charCount = document.getElementById("charCount");
        charCount.textContent = `${currentLength}/${maxLength} characters`; // Corrected line
        if (currentLength > maxLength) {
            this.value = this.value.substring(0, maxLength);
        }
    });

    document.querySelector("form").addEventListener("submit", function(e) {
        let subcategories = document.querySelectorAll("#subcategories-list input[type='checkbox']:checked");
        if (subcategories.length === 0) {
            alert("Please select at least one subcategory.");
            e.preventDefault();
        } else {
            // بعد التأكد من إرسال البيانات، يتم إعادة التوجيه إلى صفحة مقدمي الخدمة
            setTimeout(function() {
                window.location.href = "service_providers.php";  // إعادة التوجيه إلى صفحة service_providers.php
            }, 100); 
        }
    });

function toggleTimeInputs() {
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    
    days.forEach(day => {
        const dayCheckbox = document.getElementById(day);
        const timeInput = document.getElementById(day + "_time");
        timeInput.disabled = !dayCheckbox.checked;
    });
}
// Function to load subcategories dynamically based on the selected profession
function loadSubcategories(serviceId) {
    const listContainer = document.getElementById('subcategories-list');
    listContainer.innerHTML = ''; // Clear existing subcategories (to prevent duplicates)

    $.ajax({
        url: 'get_subcategories.php', // Ensure this file returns the correct subcategories data
        type: 'GET',
        data: { serviceId: serviceId },
        success: function(data) {
            const subcategories = JSON.parse(data);
            if (subcategories.length > 0) {
                subcategories.forEach(function(sub) {
                    const div = document.createElement('div');
                    div.className = 'subcategory-item';
                    div.innerHTML = `
                        <input type="checkbox" name="sous_metier[]" id="sub_${sub.id}" value="${sub.id}">
                        <label for="sub_${sub.id}">${sub.nom_sous_service}</label>
                    `;
                    listContainer.appendChild(div);
                });
            } else {
                listContainer.innerHTML = 'No subcategories available for this service.';
            }
        },
        error: function() {
            alert('Error loading subcategories.');
        }
    });
}

    </script>
</body>
</html>