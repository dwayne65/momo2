<?php
require_once 'config.php';
requireLogin();

$message = '';
$success = false;

// Handle group creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_group'])) {
    $group_name = sanitize($_POST['group_name'] ?? '');

    if (empty($group_name)) {
        $message = 'Please enter a group name';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO groups (group_name) VALUES (?)");
        $stmt->execute([$group_name]);
        $message = 'Group created successfully!';
        $success = true;
    }
}

// Handle adding member to group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $group_id = intval($_POST['group_id'] ?? 0);
    $user_phone = sanitize($_POST['user_phone'] ?? '');

    if ($group_id <= 0 || empty($user_phone)) {
        $message = 'Please select a group and enter a user phone number';
    } else {
        $conn = getDBConnection();

        // Find user by phone
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$user_phone]);
        $user = $stmt->fetch();

        if (!$user) {
            $message = 'User not found. Please verify the user first.';
        } else {
            // Check if already a member
            $stmt = $conn->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
            $stmt->execute([$group_id, $user['id']]);
            $existing = $stmt->fetch();

            if ($existing) {
                $message = 'User is already a member of this group';
            } else {
                $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
                $stmt->execute([$group_id, $user['id']]);
                $message = 'Member added to group successfully!';
                $success = true;
            }
        }
    }
}

// Get all groups with member counts
$conn = getDBConnection();
$groups = $conn->query("
    SELECT g.*, COUNT(gm.user_id) as member_count
    FROM groups g
    LEFT JOIN group_members gm ON g.id = gm.group_id
    GROUP BY g.id
    ORDER BY g.created_at DESC
")->fetchAll();

// Get all users for member addition
$users = $conn->query("SELECT id, first_name, last_name, phone FROM users ORDER BY first_name, last_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groups - <?php echo APP_NAME; ?></title>
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
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Groups</h2>
            <p class="text-gray-600">Manage user groups for organized transactions</p>
        </div>

        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-md <?php echo $success ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Create Group -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-plus-circle text-purple-600 mr-2"></i>
                    Create New Group
                </h3>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="create_group" value="1">
                    <div>
                        <label for="group_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Group Name
                        </label>
                        <input
                            type="text"
                            id="group_name"
                            name="group_name"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Enter group name"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition duration-200"
                    >
                        <i class="fas fa-plus mr-2"></i>
                        Create Group
                    </button>
                </form>
            </div>

            <!-- Add Member to Group -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user-plus text-blue-600 mr-2"></i>
                    Add Member to Group
                </h3>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="add_member" value="1">
                    <div>
                        <label for="group_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Group
                        </label>
                        <select
                            id="group_id"
                            name="group_id"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Choose a group...</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?php echo $group['id']; ?>"><?php echo htmlspecialchars($group['group_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="user_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            User Phone Number
                        </label>
                        <input
                            type="tel"
                            id="user_phone"
                            name="user_phone"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., +250788123456"
                        >
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                    >
                        <i class="fas fa-user-plus mr-2"></i>
                        Add Member
                    </button>
                </form>
            </div>
        </div>

        <!-- Groups List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Groups</h3>

            <?php if (empty($groups)): ?>
                <p class="text-gray-600">No groups created yet</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($groups as $group): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-bold text-gray-800"><?php echo htmlspecialchars($group['group_name']); ?></h4>
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full">
                                    <?php echo $group['member_count']; ?> members
                                </span>
                            </div>

                            <p class="text-sm text-gray-600 mb-3">
                                Created: <?php echo formatDate($group['created_at']); ?>
                            </p>

                            <!-- Group Members -->
                            <?php
                            $members = $conn->prepare("
                                SELECT u.first_name, u.last_name, u.phone
                                FROM group_members gm
                                JOIN users u ON gm.user_id = u.id
                                WHERE gm.group_id = ?
                                ORDER BY u.first_name, u.last_name
                                LIMIT 3
                            ");
                            $members->execute([$group['id']]);
                            $memberList = $members->fetchAll();
                            ?>

                            <?php if (!empty($memberList)): ?>
                                <div class="space-y-1">
                                    <p class="text-xs text-gray-500 font-medium">Members:</p>
                                    <?php foreach ($memberList as $member): ?>
                                        <p class="text-sm text-gray-700">
                                            <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                            <span class="text-gray-500">(<?php echo htmlspecialchars($member['phone']); ?>)</span>
                                        </p>
                                    <?php endforeach; ?>
                                    <?php if ($group['member_count'] > 3): ?>
                                        <p class="text-xs text-gray-500">...and <?php echo $group['member_count'] - 3; ?> more</p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">No members yet</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
