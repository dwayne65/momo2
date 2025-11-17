<?php
require_once 'config.php';
requireLogin();

$conn = getDBConnection();

// Get statistics
$userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
$paymentCount = $conn->query("SELECT COUNT(*) as count FROM payments")->fetch()['count'];
$transferCount = $conn->query("SELECT COUNT(*) as count FROM transfers")->fetch()['count'];
$groupCount = $conn->query("SELECT COUNT(*) as count FROM groups")->fetch()['count'];

$totalPayments = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'")->fetch()['total'] ?? 0;
$totalTransfers = $conn->query("SELECT SUM(amount) as total FROM transfers WHERE status = 'completed'")->fetch()['total'] ?? 0;

// Recent activities
$recentPayments = $conn->query("SELECT * FROM payments ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentTransfers = $conn->query("SELECT * FROM transfers ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
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
                    <span class="text-gray-700">Welcome, <?php echo $_SESSION['username']; ?></span>
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
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Dashboard</h2>
            <p class="text-gray-600">Overview of your mobile money system</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $userCount; ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-credit-card text-green-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Payments</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo formatCurrency($totalPayments); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-exchange-alt text-yellow-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Transfers</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo formatCurrency($totalTransfers); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <i class="fas fa-users-cog text-purple-600 text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Groups</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $groupCount; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="verify.php" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                    <i class="fas fa-user-check text-blue-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">Verify User</span>
                </a>
                <a href="payments.php" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200">
                    <i class="fas fa-credit-card text-green-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">Process Payment</span>
                </a>
                <a href="transfers.php" class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition duration-200">
                    <i class="fas fa-exchange-alt text-yellow-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">Make Transfer</span>
                </a>
                <a href="groups.php" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-200">
                    <i class="fas fa-users-cog text-purple-600 text-2xl mb-2"></i>
                    <span class="text-sm font-medium text-gray-700">Manage Groups</span>
                </a>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Payments -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Payments</h3>
                <?php if (empty($recentPayments)): ?>
                    <p class="text-gray-600">No payments yet</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($recentPayments as $payment): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo $payment['phone']; ?></p>
                                    <p class="text-sm text-gray-600"><?php echo formatDate($payment['created_at']); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-green-600"><?php echo formatCurrency($payment['amount']); ?></p>
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $payment['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Transfers -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Transfers</h3>
                <?php if (empty($recentTransfers)): ?>
                    <p class="text-gray-600">No transfers yet</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($recentTransfers as $transfer): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo $transfer['sender_phone']; ?> → <?php echo $transfer['receiver_phone']; ?></p>
                                    <p class="text-sm text-gray-600"><?php echo formatDate($transfer['created_at']); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-yellow-600"><?php echo formatCurrency($transfer['amount']); ?></p>
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $transfer['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo ucfirst($transfer['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
