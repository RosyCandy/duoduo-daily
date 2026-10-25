<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$messages = $input['messages'] ?? [];

$api_key = getenv('DASHSCOPE_API_KEY') ?: '';
if ($api_key === '') {
    http_response_code(503);
    echo json_encode(['reply' => 'AI 服务尚未配置，请联系管理员。'], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = [
    'model' => 'qwen-turbo',
    'messages' => $messages
];

$ch = curl_init('https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$res = curl_exec($ch);
curl_close($ch);

$result = json_decode($res, true);
$reply = $result['choices'][0]['message']['content'] ?? '抱歉，出错了';

echo json_encode(['reply' => $reply]);
