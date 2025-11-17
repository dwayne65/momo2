<?php
require_once 'config.php';
requireLogin();

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize($_POST['phone'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);

    if (empty($phone) || $amount <= 0) {
        $message = 'Please fill all fields with valid data';
    } else {
        // Clean phone number
        $cleanPhone = ltrim($phone, '+');

        // Prepare payment data
        $paymentData = [
            'amount' => $amount,
            'currency' => 'RWF',
            'phone' => $cleanPhone,
            'payment_mode' => 'momo',
            'message' => 'Payment',
            'callback_url' => APP_URL . '/callback.php',
            'transfers' => [[
                'phone' => $cleanPhone,
                'amount' => $amount,
                'message' => 'Payment'
            ]]
        ];

        try {
            // Call MOPAY API
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
                // Save to database
                $conn = getDBConnection();
                $stmt = $conn->prepare("INSERT INTO payments (phone, amount, currency, reference, status, transaction_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $phone,
                    $amount,
                    'RWF',
                    $response['data']['reference'] ?? 'REF-' . time(),
                    $response['data']['status'] ?? 'completed',
                    $response['data']['transaction_id'] ?? null
                ]);

                $message = 'Payment processed successfully!';
                $success = true;
            } else {
                $message = 'Payment failed. Please try again.';
            }
        } catch (Exception $e) {
            $message = 'API error: ' . $e->getMessage();
        }
    }
}

// Get payments for display
$conn = getDBConnection();
$stmt = $conn->query("SELECT * FROM payments ORDER BY created_at DESC");
$payments = $stmt->fetchAll();

// Calculate totals
$totalPayments = $conn->query("SELECT COUNT(*) as count FROM payments")->fetch()['count'];
$totalAmount = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'")->fetch()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-mobile-alt text-2xl text-blue-600 mr-3"></i>
                    <h1 class="text-xl font-bold text-gray-800"><?php echo APP_NAME; ?></h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Payments</h2>
            <p class="text-gray-600">Process mobile money payments</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Payment Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-credit-card text-green-600 mr-2"></i>
                    Process Payment
                </h3>

                <?php if ($message): ?>
                    <div class="mb-4 p-4 rounded-md <?php echo $success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="e.g., +250788123456"
                        >
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Amount (RWF)
                        </label>
                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            step="0.01"
                            min="0"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="0.00"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200"
                    >
                        <i class="fas fa-paper-plane mr-2"></i>
                        Process Payment
                    </button>
                </form>
            </div>

            <!-- Payment Summary -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                    Payment Summary
                </h3>

                <div class="space-y-4">
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Total Payments</p>
                        <p class="text-3xl font-bold text-green-600"><?php echo formatCurrency($totalAmount); ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Total Transactions</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $totalPayments; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Payment History</h3>

            <?php if (empty($payments)): ?>
                <p class="text-gray-600">No payments yet</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Phone</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Amount</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Reference</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 font-medium"><?php echo htmlspecialchars($payment['phone']); ?></td>
                                    <td class="p-3 font-bold text-green-600"><?php echo formatCurrency($payment['amount']); ?></td>
                                    <td class="p-3 text-sm text-gray-600"><?php echo htmlspecialchars($payment['reference']); ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 text-xs rounded-full <?php
                                            echo $payment['status'] === 'completed' ? 'bg-green-100 text-green-800' :
                                                 ($payment['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                        ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-600"><?php echo formatDate($payment['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
