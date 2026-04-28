<?php
// setup.php - تهيئة قاعدة البيانات (شغّله مرة واحدة فقط)
require_once 'config.php';

$tables = [
    "CREATE TABLE IF NOT EXISTS platforms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    "INSERT IGNORE INTO platforms (name) VALUES ('meta'), ('snapchat'), ('tiktok'), ('x')",

    "CREATE TABLE IF NOT EXISTS campaigns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        platform_id INT NOT NULL,
        campaign_external_id VARCHAR(100),
        name VARCHAR(255) NOT NULL,
        daily_budget DECIMAL(10,2) NOT NULL DEFAULT 0,
        total_spend DECIMAL(10,2) NOT NULL DEFAULT 0,
        roas DECIMAL(5,2) NOT NULL DEFAULT 0,
        status ENUM('active','paused','ended') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (platform_id) REFERENCES platforms(id)
    )",

    "CREATE TABLE IF NOT EXISTS ads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT NOT NULL,
        creative_text TEXT,
        image_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
    )",

    "CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT NOT NULL,
        report_date DATE NOT NULL,
        spend DECIMAL(10,2) DEFAULT 0,
        impressions INT DEFAULT 0,
        clicks INT DEFAULT 0,
        conversions INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_report (campaign_id, report_date),
        FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
    )"
];

$errors = [];
foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        $errors[] = $e->getMessage();
    }
}

if (empty($errors)) {
    echo "✅ تم إنشاء الجداول بنجاح! يمكنك الآن حذف هذا الملف.";
} else {
    echo "❌ أخطاء:<br>" . implode("<br>", $errors);
}
?>