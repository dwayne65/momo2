<?php
require_once 'config.php';

// Set headers for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow authenticated requests for API
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$endpoint = $request[1] ?? ''; // Assuming URL structure: /api/endpoint

$conn = getDBConnection();

switch ($endpoint) {
    case 'users':
        handleUsers($method, $conn);
        break;

    case 'payments':
        handlePayments($method, $conn);
        break;

    case 'transfers':
        handleTransfers($method, $conn);
        break;

    case 'groups':
        handleGroups($method, $conn);
        break;

    case 'verify-user':
        handleVerifyUser($method);
        break;

    case 'process-payment':
        handleProcessPayment($method);
        break;

    case 'process-transfer':
        handleProcessTransfer($method);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

function handleUsers($method, $conn) {
    switch ($method) {
        case 'GET':
            $stmt = $conn->query("SELECT id, first_name, last_name, birth_date, gender, is_active, phone, created_at FROM users ORDER BY created_at DESC");
            $users = $stmt->fetchAll();
            echo json_encode($users);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['phone'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Phone number required']);
                return;
            }

            // Check if user exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
            $stmt->execute([$data['phone']]);
            $existing = $stmt->fetch();

            if ($existing) {
                echo json_encode($existing);
                return;
            }

            // Verify via API
            try {
                $response = apiCall(
                    MOPAY_API_BASE . '/customer-info?phone=' . urlencode($data['phone']),
                    'GET',
                    null,
                    ['Authorization: Bearer ' . MOPAY_API_TOKEN]
                );

                if ($response['status'] === 200 && $response['data']) {
                    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, birth_date, gender, is_active, phone) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $response['data']['firstName'],
                        $response['data']['lastName'],
                        $response['data']['birthDate'],
                        $response['data']['gender'],
                        $response['data']['isActive'],
                        $data['phone']
                    ]);

                    $user = [
                        'id' => $conn->lastInsertId(),
                        'firstName' => $response['data']['firstName'],
                        'lastName' => $response['data']['lastName'],
                        'birthDate' => $response['data']['birthDate'],
                        'gender' => $response['data']['gender'],
                        'isActive' => $response['data']['isActive'],
                        'phone' => $data['phone'],
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    echo json_encode($user);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'User verification failed']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handlePayments($method, $conn) {
    switch ($method) {
        case 'GET':
            $stmt = $conn->query("SELECT * FROM payments ORDER BY created_at DESC");
            $payments = $stmt->fetchAll();
            echo json_encode($payments);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['phone']) || !isset($data['amount'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Phone and amount required']);
                return;
            }

            // Process payment via API
            $cleanPhone = ltrim($data['phone'], '+');
            $paymentData = [
                'amount' => $data['amount'],
                'currency' => 'RWF',
                'phone' => $cleanPhone,
                'payment_mode' => 'momo',
                'message' => $data['message'] ?? 'Payment',
                'callback_url' => APP_URL . '/callback.php',
                'transfers' => [[
                    'phone' => $cleanPhone,
                    'amount' => $data['amount'],
                    'message' => $data['message'] ?? 'Payment'
                ]]
            ];

            try {
                $response = apiCall(
                    MOPAY_API_BASE . '/initiate-payment',
                    'POST',
                    $paymentData,
                    [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . MOPAY_API_TOKEN
                    ]
                );

                if ($response['status'] === 200 && isset($response['data'])) {
                    $stmt = $conn->prepare("INSERT INTO payments (phone, amount, currency, reference, status, transaction_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $data['phone'],
                        $data['amount'],
                        'RWF',
                        $response['data']['reference'] ?? 'REF-' . time(),
                        $response['data']['status'] ?? 'completed',
                        $response['data']['transaction_id'] ?? null
                    ]);

                    echo json_encode([
                        'id' => $conn->lastInsertId(),
                        'reference' => $response['data']['reference'] ?? 'REF-' . time(),
                        'status' => $response['data']['status'] ?? 'completed'
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Payment failed']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handleTransfers($method, $conn) {
    switch ($method) {
        case 'GET':
            $stmt = $conn->query("SELECT * FROM transfers ORDER BY created_at DESC");
            $transfers = $stmt->fetchAll();
            echo json_encode($transfers);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['receiver_phone']) || !isset($data['amount'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Receiver phone and amount required']);
                return;
            }

            // Process transfer via API
            $transferData = [
                'phone' => $data['receiver_phone'],
                'amount' => $data['amount'],
                'message' => $data['message'] ?? 'Transfer'
            ];

            try {
                $response = apiCall(
                    MOPAY_API_BASE . '/transfer',
                    'POST',
                    $transferData,
                    [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . MOPAY_API_TOKEN
                    ]
                );

                if ($response['status'] === 200 && isset($response['data'])) {
                    $stmt = $conn->prepare("INSERT INTO transfers (sender_phone, receiver_phone, amount, message, status, transaction_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $data['sender_phone'] ?? 'ADMIN',
                        $data['receiver_phone'],
                        $data['amount'],
                        $data['message'] ?? '',
                        $response['data']['status'] ?? 'completed',
                        $response['data']['transaction_id'] ?? null
                    ]);

                    echo json_encode([
                        'id' => $conn->lastInsertId(),
                        'status' => $response['data']['status'] ?? 'completed'
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Transfer failed']);
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handleGroups($method, $conn) {
    switch ($method) {
        case 'GET':
            $stmt = $conn->query("
                SELECT g.*, COUNT(gm.user_id) as member_count
                FROM groups g
                LEFT JOIN group_members gm ON g.id = gm.group_id
                GROUP BY g.id
                ORDER BY g.created_at DESC
            ");
            $groups = $stmt->fetchAll();
            echo json_encode($groups);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!$data || !isset($data['group_name'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Group name required']);
                return;
            }

            $stmt = $conn->prepare("INSERT INTO groups (group_name) VALUES (?)");
            $stmt->execute([$data['group_name']]);

            echo json_encode([
                'id' => $conn->lastInsertId(),
                'group_name' => $data['group_name'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

function handleVerifyUser($method) {
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['phone'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Phone number required']);
        return;
    }

    try {
        $response = apiCall(
            MOPAY_API_BASE . '/customer-info?phone=' . urlencode($data['phone']),
            'GET',
            null,
            ['Authorization: Bearer ' . MOPAY_API_TOKEN]
        );

        if ($response['status'] === 200 && $response['data']) {
            echo json_encode($response['data']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'User verification failed']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleProcessPayment($method) {
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['phone']) || !isset($data['amount'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Phone and amount required']);
        return;
    }

    $cleanPhone = ltrim($data['phone'], '+');
    $paymentData = [
        'amount' => $data['amount'],
        'currency' => $data['currency'] ?? 'RWF',
        'phone' => $cleanPhone,
        'payment_mode' => $data['payment_mode'] ?? 'momo',
        'message' => $data['message'] ?? 'Payment',
        'callback_url' => $data['callback_url'] ?? APP_URL . '/callback.php',
        'transfers' => $data['transfers'] ?? [[
            'phone' => $cleanPhone,
            'amount' => $data['amount'],
            'message' => $data['message'] ?? 'Payment'
        ]]
    ];

    try {
        $response = apiCall(
            MOPAY_API_BASE . '/initiate-payment',
            'POST',
            $paymentData,
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . MOPAY_API_TOKEN
            ]
        );

        echo json_encode($response['data'] ?? ['error' => 'Payment processing failed']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleProcessTransfer($method) {
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['phone']) || !isset($data['amount'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Phone and amount required']);
        return;
    }

    $transferData = [
        'phone' => $data['phone'],
        'amount' => $data['amount'],
        'message' => $data['message'] ?? 'Transfer'
    ];

    try {
        $response = apiCall(
            MOPAY_API_BASE . '/transfer',
            'POST',
            $transferData,
            [
                'Content-Type: application/json',
                'Authorization: Bearer ' . MOPAY_API_TOKEN
            ]
        );

        echo json_encode($response['data'] ?? ['error' => 'Transfer processing failed']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
