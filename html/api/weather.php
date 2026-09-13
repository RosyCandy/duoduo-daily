<?php
header('Content-Type: application/json');

$key = '60e3d480d1bf4117a0b0de4eb9f96e91';
$location = trim($_GET['location'] ?? 'CN101190401');
$city_name = '苏州';

if (!preg_match('/^CN\d+$/', $location)) {
    $lookup = fetch_url('https://qc4ewtyfrv.re.qweatherapi.com/geo/v2/city/lookup?location=' . rawurlencode($location) . '&key=' . $key);
    $lookup_data = json_decode($lookup, true);
    if (!$lookup_data || ($lookup_data['code'] ?? '') !== '200' || empty($lookup_data['location'][0]['id'])) {
        echo json_encode(['error' => '未找到该城市']);
        exit;
    }
    $location = $lookup_data['location'][0]['id'];
    $city_name = $lookup_data['location'][0]['name'];
} else if ($location !== 'CN101190401') {
    $lookup = fetch_url('https://qc4ewtyfrv.re.qweatherapi.com/geo/v2/city/lookup?location=' . rawurlencode($location) . '&key=' . $key);
    $lookup_data = json_decode($lookup, true);
    $city_name = $lookup_data['location'][0]['name'] ?? $city_name;
}

function fetch_url($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

$now_res = fetch_url("https://qc4ewtyfrv.re.qweatherapi.com/v7/weather/now?location={$location}&key={$key}");
$now_data = json_decode($now_res, true);

$forecast_res = fetch_url("https://qc4ewtyfrv.re.qweatherapi.com/v7/weather/3d?location={$location}&key={$key}");
$forecast_data = json_decode($forecast_res, true);

if (!$now_data || $now_data['code'] !== '200') {
    echo json_encode(['error' => '天气获取失败', 'raw' => $now_data]);
    exit;
}

echo json_encode([
    'city' => $city_name,
    'now' => $now_data['now'],
    'forecast' => $forecast_data['daily'] ?? []
]);