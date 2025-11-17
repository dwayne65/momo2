<?php
require_once 'config.php';
requireLogin();

$message = '';
$exportData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exportType = $_POST['export_type'] ?? '';
    $format = $_POST['format'] ?? 'csv';

    $conn = getDBConnection();

    switch ($exportType) {
        case 'users':
            $stmt = $conn->query("SELECT id, first_name, last_name, birth_date, gender, is_active, phone, created_at FROM users ORDER BY created_at DESC");
            $exportData = $stmt->fetchAll();
            break;

        case 'payments':
            $stmt = $conn->query("SELECT id, phone, amount, currency, reference, status, transaction_id, created_at FROM payments ORDER BY created_at DESC");
            $exportData = $stmt->fetchAll();
            break;

        case 'transfers':
            $stmt = $conn->query("SELECT id, sender_phone, receiver_phone, amount, message, status, transaction_id, created_at FROM transfers ORDER BY created_at DESC");
            $exportData = $stmt->fetchAll();
            break;

        case 'groups':
            $stmt = $conn->query("
                SELECT g.id, g.group_name, g.created_at, COUNT(gm.user_id) as member_count
                FROM groups g
                LEFT JOIN group_members gm ON g.id = gm.group_id
                GROUP BY g.id
                ORDER BY g.created_at DESC
            ");
            $exportData = $stmt->fetchAll();
            break;

        default:
            $message = 'Please select a valid export type';
            break;
    }

    if ($exportData && $format === 'csv') {
        // Generate CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $exportType . '_export_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        if (!empty($exportData)) {
            // Write headers
            fputcsv($output, array_keys($exportData[0]));

            // Write data
            foreach ($exportData as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    } elseif ($exportData && $format === 'json') {
        // Generate JSON
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $exportType . '_export_' . date('Y-m-d_H-i-s') . '.json"');

        echo json_encode($exportData, JSON_PRETTY_PRINT);
        exit;
    }
}

// Get statistics for display
$conn = getDBConnection();
$stats = [
    'users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch()['count'],
    'payments' => $conn->query("SELECT COUNT(*) as count FROM payments")->fetch()['count'],
    'transfers' => $conn->query("SELECT COUNT(*) as count FROM transfers")->fetch()['count'],
    'groups' => $conn->query("SELECT COUNT(*) as count FROM groups")->fetch()['count']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data - <?php echo APP_NAME; ?></title>
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
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Export Data</h2>
            <p class="text-gray-600">Export system data in CSV or JSON format</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md bg-yellow-100 text-yellow-800">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Export Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-download text-green-600 mr-2"></i>
                    Export Data
                </h3>

                <form method="POST" class="space-y-4">
                    <div>
                        <label for="export_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Data Type
                        </label>
                        <select
                            id="export_type"
                            name="export_type"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <option value="">Select data type...</option>
                            <option value="users">Users (<?php echo $stats['users']; ?> records)</option>
                            <option value="payments">Payments (<?php echo $stats['payments']; ?> records)</option>
                            <option value="transfers">Transfers (<?php echo $stats['transfers']; ?> records)</option>
                            <option value="groups">Groups (<?php echo $stats['groups']; ?> records)</option>
                        </select>
                    </div>

                    <div>
                        <label for="format" class="block text-sm font-medium text-gray-700 mb-2">
                            Export Format
                        </label>
                        <select
                            id="format"
                            name="format"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <option value="csv">CSV (Spreadsheet)</option>
                            <option value="json">JSON (Developer)</option>
                        </select>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200"
                    >
                        <i class="fas fa-download mr-2"></i>
                        Export Data
                    </button>
                </form>
            </div>

            <!-- Data Preview -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                    Data Summary
                </h3>

                <div class="space-y-4">
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Total Users</p>
                        <p class="text-3xl font-bold text-blue-600"><?php echo $stats['users']; ?></p>
                    </div>

                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Total Payments</p>
                        <p class="text-3xl font-bold text-green-600"><?php echo $stats['payments']; ?></p>
                    </div>

                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Total Transfers</p>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo $stats['transfers']; ?></p>
                    </div>

                    <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                        <p class="text-sm text-gray-600 mb-1">Total Groups</p>
                        <p class="text-3xl font-bold text-purple-600"><?php echo $stats['groups']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Information -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Export Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">CSV Format</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Compatible with Excel and Google Sheets</li>
                        <li>• Easy to read and analyze</li>
                        <li>• Includes column headers</li>
                        <li>• Best for data analysis</li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">JSON Format</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Developer-friendly format</li>
                        <li>• Preserves data types</li>
                        <li>• Easy to import into other systems</li>
                        <li>• Best for API integration</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
