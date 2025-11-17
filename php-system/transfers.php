<?php
require_once 'config.php';
requireLogin();

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver = sanitize($_POST['receiver'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $message_text = sanitize($_POST['message'] ?? '');

    if (empty($receiver) || $amount <= 0) {
        $message = 'Please fill all required fields with valid data';
    } else {
        // Prepare transfer data
        $transferData = [
            'phone' => $receiver,
            'amount' => $amount,
            'message' => $message_text ?: 'Transfer'
        ];

        try {
            // Call MOPAY API
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
                // Save to database (assuming current user is admin, using a placeholder sender phone)
                $conn = getDBConnection();
                $stmt = $conn->prepare("INSERT INTO transfers (sender_phone, receiver_phone, amount, message, status, transaction_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    'ADMIN', // Placeholder sender
                    $receiver,
                    $amount,
                    $message_text,
                    $response['data']['status'] ?? 'completed',
                    $response['data']['transaction_id'] ?? null
                ]);

                $message = 'Transfer completed successfully!';
                $success = true;
            } else {
                $message = 'Transfer failed. Please try again.';
            }
        } catch (Exception $e) {
            $message = 'API error: ' . $e->getMessage();
        }
    }
}

// Get transfers for display
$conn = getDBConnection();
$stmt = $conn->query("SELECT * FROM transfers ORDER BY created_at DESC");
$transfers = $stmt->fetchAll();

// Calculate totals
$totalTransfers = $conn->query("SELECT COUNT(*) as count FROM transfers")->fetch()['count'];
$totalAmount = $conn->query("SELECT SUM(amount) as total FROM transfers WHERE status = 'completed'")->fetch()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfers - <?php echo APP_NAME; ?></title>
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
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Transfers</h2>
            <p class="text-gray-600">Send money to other mobile money users</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Transfer Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-exchange-alt text-yellow-600 mr-2"></i>
                    Make Transfer
                </h3>

                <?php if ($message): ?>
                    <div class="mb-4 p-4 rounded-md <?php echo $success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label for="receiver" class="block text-sm font-medium text-gray-700 mb-2">
                            Receiver Phone Number
                        </label>
                        <input
                            type="tel"
                            id="receiver"
                            name="receiver"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                            placeholder="0.00"
                        >
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                            Message (Optional)
                        </label>
                        <input
                            type="text"
                            id="message"
                            name="message"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                            placeholder="Optional message"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-yellow-600 text-white py-2 px-4 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition duration-200"
                    >
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Transfer
                    </button>
                </form>
            </div>

            <!-- Transfer Summary -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                    Transfer Summary
                </h3>

                <div class="space-y-4">
                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Total Transferred</p>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo formatCurrency($totalAmount); ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Total Transfers</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $totalTransfers; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transfer History -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Transfer History</h3>

            <?php if (empty($transfers)): ?>
                <p class="text-gray-600">No transfers yet</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Receiver</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Amount</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Message</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transfers as $transfer): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 font-medium"><?php echo htmlspecialchars($transfer['receiver_phone']); ?></td>
                                    <td class="p-3 font-bold text-yellow-600"><?php echo formatCurrency($transfer['amount']); ?></td>
                                    <td class="p-3 text-sm text-gray-600"><?php echo htmlspecialchars($transfer['message'] ?: 'N/A'); ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 text-xs rounded-full <?php
                                            echo $transfer['status'] === 'completed' ? 'bg-green-100 text-green-800' :
                                                 ($transfer['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                        ?>">
                                            <?php echo ucfirst($transfer['status']); ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-600"><?php echo formatDate($transfer['created_at']); ?></td>
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
