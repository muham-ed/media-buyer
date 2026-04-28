<?php
// config.php - الإعدادات النهائية مع دعم كامل لـ API الحقيقية

// ============= قاعدة البيانات =============
// أضف هذا السطر بعد تعريفات API
define('SANDBOX_MODE', true);   // غيّره إلى false عند استخدام مفاتيح حقيقية
define('DB_HOST', 'localhost');
define('DB_NAME', 'media buyer');  // اسم قاعدة البيانات كما في phpMyAdmin
define('DB_USER', 'root');
define('DB_PASS', '');   // اترك فارغًا في XAMPP

// ============= مفاتيح API - أدخل مفاتيحك الحقيقية هنا =============
// للحصول على هذه المفاتيح، اتبع التعليمات في نهاية هذا الملف

// Meta (Facebook/Instagram) Ads API
define('META_ACCESS_TOKEN', 'YOUR_META_ACCESS_TOKEN');   // طويل الأجل
define('META_AD_ACCOUNT_ID', 'act_123456789');           // يبدأ بـ act_

// Claude API (Anthropic)
define('CLAUDE_API_KEY', 'YOUR_CLAUDE_API_KEY');         // من console.anthropic.com

// OpenAI DALL-E 3 (لتوليد الصور الإعلانية)
define('OPENAI_API_KEY', 'YOUR_OPENAI_API_KEY');         // من platform.openai.com

// Snapchat, TikTok, X (اختياري – للتوسعة)
define('SNAPCHAT_ACCESS_TOKEN', 'YOUR_SNAPCHAT_TOKEN');
define('TIKTOK_ACCESS_TOKEN', 'YOUR_TIKTOK_TOKEN');
define('X_ACCESS_TOKEN', 'YOUR_X_ACCESS_TOKEN');

// ============= الاتصال بقاعدة البيانات =============
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}
session_start();

// ============= تعليمات الحصول على المفاتيح (أضفها في لوحة التحكم أو اشرحها) =============
?>