<?php
// claude_service.php - توليد نصوص إعلانية باستخدام Claude API الحقيقي

function generateAdCreatives($audience_description, $objective, $daily_budget, $platform) {
    $api_key = CLAUDE_API_KEY;
    
    // إذا لم يكن المفتاح حقيقياً، استخدم وضع المحاكاة المتقدم
    if ($api_key == 'YOUR_CLAUDE_API_KEY') {
        return simulateClaudeResponse($audience_description, $objective, $daily_budget, $platform);
    }
    
    // إعداد الطلب الحقيقي لـ Claude
    $prompt = "أنت خبير تسويق رقمي. اكتب 3 نصوص إعلانية قصيرة (max 35 كلمة لكل نص) باللغة العربية لجذب العملاء، بناءً على:
    
    وصف المنتج/الجمهور: {$audience_description}
    هدف الحملة: {$objective}
    الميزانية اليومية: \${$daily_budget}
    المنصة: {$platform}
    
    أيضًا، اكتب وصفًا للصورة باللغة الإنجليزية (image prompt, 20-40 كلمة) لإنشاء صورة إعلانية مناسبة.
    
    أعد النتيجة فقط بصيغة JSON كما يلي:
    {\"creatives\": [\"نص1\", \"نص2\", \"نص3\"], \"image_prompt\": \"English prompt for image\"}";
    
    $data = [
        'model' => 'claude-3-sonnet-20240229',
        'max_tokens' => 800,
        'temperature' => 0.7,
        'messages' => [['role' => 'user', 'content' => $prompt]]
    ];
    
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $api_key,
        'anthropic-version: 2023-06-01',
        'content-type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        $content = $result['content'][0]['text'] ?? '';
        preg_match('/\{.*\}/s', $content, $matches);
        if (isset($matches[0])) {
            $parsed = json_decode($matches[0], true);
            if ($parsed && isset($parsed['creatives'])) {
                $img_prompt = $parsed['image_prompt'] ?? "Professional marketing background for " . $audience_description;
                return ['success' => true, 'creatives' => $parsed['creatives'], 'image_prompt' => $img_prompt];
            }
        }
        return ['success' => false, 'error' => 'Claude رد بتنسيق خاطئ'];
    } else {
        return ['success' => false, 'error' => "HTTP $http_code: " . substr($response, 0, 200)];
    }
}

function simulateClaudeResponse($desc, $objective, $budget, $platform) {
    $short = mb_substr($desc, 0, 60);
    return [
        'success' => true,
        'creatives' => [
            "🔥 اكتشف $short – عرض خاص لفترة محدودة!",
            "✨ لا تفوت الفرصة: $short بجودة عالية وأسعار تنافسية.",
            "🚀 انضم الآن إلى آلاف العملاء الذين اختاروا $short. اطلب اليوم!"
        ],
        'image_prompt' => "Modern, vibrant advertising image for " . mb_substr($desc, 0, 80) . ", bright colors, clear CTA, professional photography style."
    ];
}
?>