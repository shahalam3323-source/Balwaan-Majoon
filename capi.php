<?php
// Disable error display (production mein)
ini_set('display_errors', 0);
error_reporting(0);

// Allow CORS (optional – agar alag domain se request aaye)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get raw POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Required fields check
if (!isset($input['event_name']) || !isset($input['event_data'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Meta API configuration
$pixel_id = '1997936307798671';
$access_token = 'EAAP0aqEdD0sBQZCKXtgEXEzgPmGRwDO3yMOCZAjtQjDPs0rkvUks0Lh9Q4UwL2GEKxd4ar8eeT7kr6sR7RdUpZBQBUFILLZCGxBnjPrcE0mZCRZCXlyLvFvSXG68Ocm54tQU7cDHRbnKY9s1g38bGfoE4wtl8f57I0ENapYE3TZA4JQOqSnAz0XdZCQoroWAVoVS5gZDZD'; // 🔑 यहाँ अपना token डालो

// Prepare event data for Meta
$events = [
    [
        'event_name' => $input['event_name'],
        'event_time' => time(),
        'event_id' => $input['event_data']['event_id'] ?? uniqid(),
        'action_source' => 'website',
        'event_source_url' => $input['event_source_url'] ?? 'https://balwaangold.com/checkout.html',
        'user_data' => [
            'client_ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'client_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'em' => hash('sha256', $input['user_data']['email'] ?? ''),
            'ph' => hash('sha256', $input['user_data']['phone'] ?? ''),
            'fn' => hash('sha256', $input['user_data']['first_name'] ?? ''),
        ],
        'custom_data' => $input['event_data']
    ]
];

// Send to Meta
$ch = curl_init("https://graph.facebook.com/v18.0/{$pixel_id}/events");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'data' => $events,
    'EAAP0aqEdD0sBQZCKXtgEXEzgPmGRwDO3yMOCZAjtQjDPs0rkvUks0Lh9Q4UwL2GEKxd4ar8eeT7kr6sR7RdUpZBQBUFILLZCGxBnjPrcE0mZCRZCXlyLvFvSXG68Ocm54tQU7cDHRbnKY9s1g38bGfoE4wtl8f57I0ENapYE3TZA4JQOqSnAz0XdZCQoroWAVoVS5gZDZD' => $access_token
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Return response to browser
http_response_code($http_code);
echo $response;
?>
