<?php
$host = 'localhost';
$dbname = 'homzy'; // غيّر هذا إذا كانت قاعدة البيانات باسم آخر
$username = 'root';
$password = ''; // إذا كنت تستخدم WAMP، عادةً بدون كلمة مرور

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // تفعيل التقارير عن الأخطاء
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>