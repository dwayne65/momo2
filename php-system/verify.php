<?php
require_once 'config.php';
requireLogin();

$message = '';
$userData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize($_POST['phone'] ?? '');

    if (empty($phone)) {
        $message = 'Please enter a phone number';
    } else {
        // Check if user already exists in database
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            $userData = $existingUser;
            $message = 'User already verified in system';
        } else {
            // Call MOPAY API to verify user
            try {
                $response = apiCall(
                    MOPAY_API_BASE . '/customer-info?phone=' . urlencode($phone),
                    'GET',
                    null,
                    ['Authorization: Bearer ' . MOPAY_API_TOKEN]
                );

                if ($response['status'] === 200 && $response['data']) {
                    // Save user to database
                    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, birth_date, gender, is_active, phone) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $response['data']['firstName'],
                        $response['data']['lastName'],
                        $response['data']['birthDate'],
                        $response['data']['gender'],
                        $response['data']['isActive'],
                        $phone
                    ]);

                    $userData = [
                        'id' => $conn->lastInsertId(),
                        'firstName' => $response['data']['firstName'],
                        'lastName' => $response['data']['lastName'],
                        'birthDate' => $response['data']['birthDate'],
                        'gender' => $response['data']['gender'],
                        'isActive' => $response['data']['isActive'],
                        'phone' => $phone,
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    $message = 'User verified and saved successfully!';
                } else {
                    $message = 'Failed to verify user. Please check the phone number.';
                }
            } catch (Exception $e) {
                $message = 'API error: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify User - <?php echo APP_NAME; ?></title>
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
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Verify User</h2>
            <p class="text-gray-600">Verify and register mobile money users</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Verification Form -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user-check text-blue-600 mr-2"></i>
                    User Verification
                </h3>

                <?php if ($message): ?>
                    <div class="mb-4 p-4 rounded-md <?php echo strpos($message, 'successfully') !== false ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., +250788123456"
                            value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                    >
                        <i class="fas fa-search mr-2"></i>
                        Verify User
                    </button>
                </form>
            </div>

            <!-- User Details Modal -->
            <?php if ($userData): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        User Verified
                    </h3>

                    <div class="space-y-4">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">First Name</p>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($userData['firstName'] ?? $userData['first_name']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Last Name</p>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($userData['lastName'] ?? $userData['last_name']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Birth Date</p>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($userData['birthDate'] ?? $userData['birth_date']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Gender</p>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($userData['gender']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Active</p>
                                    <p class="font-medium text-gray-800"><?php echo ($userData['isActive'] ?? $userData['is_active']) ? 'Yes' : 'No'; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Phone Number</p>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($userData['phone']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Users List -->
        <div class="mt-8 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Verified Users</h3>

            <?php
            $conn = getDBConnection();
            $stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
            $users = $stmt->fetchAll();
            ?>

            <?php if (empty($users)): ?>
                <p class="text-gray-600">No users verified yet</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Name</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Phone</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Gender</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Status</th>
                                <th class="text-left p-3 text-sm font-medium text-gray-600">Verified Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="p-3 font-medium"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td class="p-3"><?php echo htmlspecialchars($user['gender']); ?></td>
                                    <td class="p-3">
                                        <span class="px-2 py-1 text-xs rounded-full <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="p-3 text-sm text-gray-600"><?php echo formatDate($user['created_at']); ?></td>
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
