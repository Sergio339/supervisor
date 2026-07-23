<?php
header('Content-Type: application/json');

// Deployed Google Apps Script Web App URL
$scriptUrl = 'https://script.google.com/macros/s/AKfycbwIUiDZr5e4AkFp3RYSqfATzsU7uxc9_S3ssKic2SzxKxIi8DM0TLz9_2J30TXSQq8/exec';
$action = $_GET['action'] ?? '';

if ($action === 'getUserData') {
    $response = @file_get_contents($scriptUrl . '?action=getUserData');
    echo $response ?: json_encode([]);
    exit;
}

if ($action === 'saveCensusData') {
    $inputData = json_decode(file_get_contents('php://input'), true);
    $userId = trim($inputData['userId'] ?? '');
    $houseNo = trim($inputData['houseNo'] ?? '');

    if (empty($userId) || empty($houseNo)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $payload = json_encode([
        'action' => 'saveCensusData',
        'userId' => $userId,
        'houseNo' => $houseNo
    ]);

    // Use stream context to cleanly bypass cURL redirection HTTP 405 limitations
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n" .
                        "Content-Length: " . strlen($payload) . "\r\n",
            'content' => $payload,
            'follow_location' => true
        ]
    ];
    
    $context = stream_context_create($options);
    $response = @file_get_contents($scriptUrl, false, $context);

    if ($response === false) {
        $error = error_get_last();
        $errorMessage = isset($error['message']) ? $error['message'] : 'Unknown stream error';
        
        echo json_encode([
            'success' => false, 
            'message' => 'Exact Connection Error: ' . $errorMessage
        ]);
        exit;
    }

    echo $response;
    exit;
}