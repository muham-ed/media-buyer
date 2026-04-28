<?php
// get_ads.php
require_once 'config.php';

if (!isset($_GET['campaign_id'])) {
    echo json_encode([]);
    exit;
}
$campaign_id = intval($_GET['campaign_id']);
$stmt = $pdo->prepare("SELECT creative_text, image_url FROM ads WHERE campaign_id = ?");
$stmt->execute([$campaign_id]);
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($ads);
?>