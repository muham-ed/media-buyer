<?php
require_once 'config.php';

// جلب جميع الحملات من قاعدة البيانات
$stmt = $pdo->query("
    SELECT c.*, p.name as platform_name 
    FROM campaigns c
    JOIN platforms p ON c.platform_id = p.id
    ORDER BY c.created_at DESC
");
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php if(isset($_SESSION['success'])): ?>
    <div class="alert success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if(isset($_SESSION['error'])): ?>
    <div class="alert error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Media Buyer – لوحة التحكم</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- في index.php داخل قسم عرض الإعلانات -->
<?php if (!empty($ad['image_url']) && file_exists($ad['image_url'])): ?>
    <img src="<?= $ad['image_url'] ?>" width="200" style="border-radius:8px;">
<?php elseif (!empty($ad['image_url'])): ?>
    <img src="<?= $ad['image_url'] ?>" width="200" style="border-radius:8px;" onerror="this.src='https://placehold.co/200x200?text=Image+Error';">
<?php endif; ?>
    <div class="container">
        <h1>🤖 AI Media Buyer Intelligence Dashboard</h1>
        
        <!-- نموذج إنشاء حملة جديدة عبر Claude -->
        <div class="card">
            <h2>+ حملة جديدة بالذكاء الاصطناعي (Claude)</h2>
            <form action="meta_api.php" method="POST" id="aiCampaignForm">
                <label>المنصة:</label>
                <select name="platform" required>
                    <option value="meta">Meta (Facebook/Instagram)</option>
                    <option value="snapchat">Snapchat</option>
                    <option value="tiktok">TikTok</option>
                    <option value="x">X (Twitter)</option>
                </select>
                
                <label>الهدف:</label>
                <select name="objective" required>
                    <option value="CONVERSIONS">تحويلات Conversions</option>
                    <option value="TRAFFIC">زيارة الموقع</option>
                    <option value="AWARENESS">وعي بالعلامة</option>
                </select>
                
                <label>الميزانية اليومية ($):</label>
                <input type="number" name="daily_budget" step="1" min="5" required>
                
                <label>وصف المنتج أو الجمهور المستهدف (سيُفهم بواسطة Claude):</label>
                <textarea name="audience_description" rows="4" placeholder="مثال: نبيع قهوة عضوية للشباب المهتمين بالصحة، أعمار 25-40، اهتمامات: رياضة، تغذية، أسلوب حياة صحي..." required></textarea>
                
                <button type="submit" name="create_ai_campaign"> أنشئ الحملة باستخدام Claude</button>
            </form>
        </div>
        
        <!-- قائمة الحملات الحالية -->
        <div class="card">
            <h2>الحملات النشطة والسابقة</h2>
            <table id="campaignsTable">
                <thead>
                    <tr>
                        <th>الحملة</th><th>المنصة</th><th>الميزانية</th><th>الإنفاق</th><th>ROAS</th><th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($campaigns) > 0): ?>
                        <?php foreach($campaigns as $camp): ?>
                        <tr>
                            <td><?= htmlspecialchars($camp['name']) ?></td>
                            <td><?= htmlspecialchars($camp['platform_name']) ?></td>
                            <td>$<?= number_format($camp['daily_budget'],2) ?></td>
                            <td>$<?= number_format($camp['total_spend'],2) ?></td>
                            <td><?= $camp['roas'] ?>x</td>
                            <td class="status-<?= $camp['status'] ?>"><?= $camp['status'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">لا توجد حملات بعد. أنشئ أول حملة باستخدام النموذج أعلاه.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- رسم بياني بسيط لأداء الحملات (تجميعي) -->
        <div class="card">
            <h2>📈 ملخص الأداء (آخر 7 أيام)</h2>
            <canvas id="performanceChart" width="400" height="200"></canvas>
        </div>
    </div>
    <!-- تفاصيل الإعلانات للحملة المختارة -->
<div class="card">
    <h2>📝 النصوص الإعلانية المولدة</h2>
    <select id="campaignSelect" onchange="showAds(this.value)">
        <option value="">اختر حملة لعرض إعلاناتها</option>
        <?php
        $campList = $pdo->query("SELECT id, name FROM campaigns ORDER BY id DESC")->fetchAll();
        foreach($campList as $c) {
            echo "<option value='{$c['id']}'>{$c['name']}</option>";
        }
        ?>
    </select>
    <div id="adsDisplay" style="margin-top:15px; background:#f9f9f9; padding:10px; border-radius:8px;">
        <!-- سيتم تحميل الإعلانات عبر AJAX -->
    </div>
</div>

<script>
function showAds(campaignId) {
    if(!campaignId) return;
    fetch(`get_ads.php?campaign_id=${campaignId}`)
        .then(res => res.json())
        .then(data => {
            let html = '<ul>';
            data.forEach(ad => {
                html += `<li><strong>${ad.creative_text}</strong><br><img src="${ad.image_url}" width="200"></li>`;
            });
            html += '</ul>';
            document.getElementById('adsDisplay').innerHTML = html;
        });
}
</script>
    <script src="script.js"></script>
    <script>
        // بيانات المخطط البياني من PHP إلى JavaScript
        const chartData = {
            labels: <?php 
                // جلب آخر 7 أيام مع الإنفاق
                $last7days = $pdo->query("
                    SELECT DATE(report_date) as day, SUM(spend) as total_spend 
                    FROM reports 
                    WHERE report_date >= CURDATE() - INTERVAL 7 DAY 
                    GROUP BY DAY(report_date)
                ")->fetchAll(PDO::FETCH_ASSOC);
                $days = array_column($last7days, 'day');
                $spends = array_column($last7days, 'total_spend');
                echo json_encode($days);
            ?>,
            spends: <?= json_encode($spends) ?>
        };
    </script>
</body>
</html>