<?php
// generate_image.php - توليد صور إعلانية باستخدام DALL-E 3 أو وضع محاكاة

function generateAdImage($prompt) {
    $api_key = OPENAI_API_KEY;
    
    // إذا لم يتم إدخال مفتاح حقيقي، استخدم رابط placeholder
    if ($api_key == 'YOUR_OPENAI_API_KEY') {
        // في وضع المحاكاة، نعيد صورة وهمية مع النص التعريفي
        return "https://placehold.co/600x400?text=AI+Generated+Image+Placeholder";
    }
    
    // إعداد الطلب إلى OpenAI DALL-E 3
    $data = [
        'model' => 'dall-e-3',
        'prompt' => $prompt . " clean, professional ad, no text overlay, high quality, vibrant colors",
        'n' => 1,
        'size' => '1024x1024',
        'quality' => 'standard'
    ];
    
    $ch = curl_init('https://api.openai.com/v1/images/generations');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        if (isset($result['data'][0]['url'])) {
            return $result['data'][0]['url'];
        } else {
            return "https://placehold.co/600x400?text=Image+Generation+Unexpected+Response";
        }
    } else {
        // في حالة فشل API، نستخدم placeholder مع رسالة الخطأ (للتصحيح)
        return "https://placehold.co/600x400?text=OpenAI+Error+Code+$http_code";
    }
}

// دالة إضافية: رفع الصورة إلى خدمة مؤقتة مثل ImgBB (اختياري، أضفها إذا أردت حل مشكلة HTTPS لـ Meta)
function uploadToImgBB($image_data_or_url) {
    // هذه الدالة اختيارية – تُستخدم إذا واجهت مشكلة في رفع الصورة مباشرة إلى Meta
    // يمكنك تفعيلها عند الحاجة
    $api_key = 'YOUR_IMGBB_API_KEY'; // سجل مجاني في imgbb.com
    if ($api_key == 'YOUR_IMGBB_API_KEY') return null;
    
    // إذا كان المدخل رابطاً، نحمل الصورة أولاً
    if (filter_var($image_data_or_url, FILTER_VALIDATE_URL)) {
        $image_data = file_get_contents($image_data_or_url);
    } else {
        $image_data = $image_data_or_url;
    }
    if (!$image_data) return null;
    
    $ch = curl_init('https://api.imgbb.com/1/upload?key=' . $api_key);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['image' => base64_encode($image_data)]);
    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);
    
    return $result['data']['url'] ?? null;
}
?>