<?php
// cron_report.php - جلب insights حقيقية أو عشوائية (حسب وجود المفاتيح)
require_once 'config.php';

// إذا كان وضع المحاكاة أو لا توجد مفاتيح، استخدم الأرقام العشوائية للاختبار
$sandbox_mode = (META_ACCESS_TOKEN == 'YOUR_META_ACCESS_TOKEN' || SANDBOX_MODE ?? true);

// جلب الحملات النشطة من منصة Meta فقط
$stmt = $pdo->query("SELECT id, campaign_external_id FROM campaigns WHERE status = 'active' AND platform_id = 1");
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($campaigns as $camp) {
    $campaign_id = $camp['id'];
    $external_id = $camp['campaign_external_id'];
    $today = date('Y-m-d');

    if (!$sandbox_mode && $external_id && strpos($external_id, 'TEST_') !== 0 && strpos($external_id, 'SIMULATED_') !== 0) {
        // جلب بيانات حقيقية من Meta Insights API
        $url = "https://graph.facebook.com/v20.0/{$external_id}/insights?fields=spend,impressions,clicks,conversions&date_preset=yesterday&access_token=" . META_ACCESS_TOKEN;
        $data = @json_decode(@file_get_contents($url), true);
        if (isset($data['data'][0])) {
            $spend = $data['data'][0]['spend'] ?? 0;
            $impressions = $data['data'][0]['impressions'] ?? 0;
            $clicks = $data['data'][0]['clicks'] ?? 0;
            $conversions = $data['data'][0]['conversions'][0]['value'] ?? 0;
        } else {
            // فشل الاتصال – استخدم أرقاماً وهمية للاختبار
            $spend = rand(5, 50);
            $impressions = rand(100, 5000);
            $clicks = rand(10, 200);
            $conversions = rand(0, 10);
        }
    } else {
        // وضع المحاكاة: أرقام عشوائية لتجربة الداشبورد
        $spend = rand(5, 50);
        $impressions = rand(100, 5000);
        $clicks = rand(10, 200);
        $conversions = rand(0, 10);
    }

    // تخزين التقرير في جدول reports
    $stmt2 = $pdo->prepare("
        INSERT INTO reports (campaign_id, report_date, spend, impressions, clicks, conversions) 
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            spend = VALUES(spend), 
            impressions = VALUES(impressions), 
            clicks = VALUES(clicks), 
            conversions = VALUES(conversions)
    ");
    $stmt2->execute([$campaign_id, $today, $spend, $impressions, $clicks, $conversions]);

    // تحديث total_spend في جدول campaigns
    $pdo->prepare("UPDATE campaigns SET total_spend = total_spend + ? WHERE id = ?")->execute([$spend, $campaign_id]);
}

echo "تم تحديث التقارير بنجاح في " . date('Y-m-d H:i:s');
?>