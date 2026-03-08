<?php
/**
 * Admin User Management Page
 * Only accessible by users with role = 'admin'
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

requireAdmin();

$user = getCurrentUser();
$db = getDB();

// Handle inline status toggle (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    $targetId = intval($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if (validateCSRFToken($csrf) && $targetId && $targetId !== $user['id']) {
        if ($action === 'activate') {
            $db->prepare("UPDATE users SET status = 'active' WHERE id = :id")->execute(['id' => $targetId]);
        } elseif ($action === 'disable') {
            $db->prepare("UPDATE users SET status = 'disabled' WHERE id = :id")->execute(['id' => $targetId]);
        } elseif ($action === 'make_admin') {
            $db->prepare("UPDATE users SET role = 'admin' WHERE id = :id")->execute(['id' => $targetId]);
        } elseif ($action === 'remove_admin') {
            $db->prepare("UPDATE users SET role = 'user' WHERE id = :id")->execute(['id' => $targetId]);
        }
    }
    header('Location: ' . SITE_URL . '/admin/users.php');
    exit;
}

// Fetch all users
$stmt = $db->query("SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - FundACause</title>
    <meta name="description" content="Manage user accounts, approve registrations, and control access on FundACause.">
    <link rel="icon" type="image/svg+xml" href="<?php echo SITE_URL; ?>/assets/logo.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef9ff', 100: '#d8f1ff', 200: '#b9e7ff', 300: '#89d9ff',
                            400: '#52c2ff', 500: '#2aa3ff', 600: '#1484f5', 700: '#0d6de1',
                            800: '#1158b6', 900: '#144b8f', 950: '#112f57',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Admin Nav -->
<nav class="bg-white shadow-sm">
    <div class="max-w-6xl mx-auto px-4 flex justify-between items-center h-16">
        <a href="<?php echo SITE_URL; ?>/admin/" class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="text-gray-600">Back to Dashboard</span>
        </a>
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-500 hidden sm:inline">Hi, <?php echo h($user['username']); ?></span>
            <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="text-sm text-red-500 hover:text-red-700 transition">Logout</a>
        </div>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Manage Users</h1>

    <!-- Pending Approval Banner -->
    <?php
    $pendingCount = 0;
    foreach ($users as $u) {
        if ($u['status'] === 'pending') $pendingCount++;
    }
    ?>
    <?php if ($pendingCount > 0): ?>
        <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-lg mb-6 text-sm">
            <strong><?php echo $pendingCount; ?></strong> user<?php echo $pendingCount > 1 ? 's' : ''; ?> awaiting approval.
        </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">User</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Registered</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-gray-50 transition <?php echo $u['status'] === 'pending' ? 'bg-amber-50/40' : ''; ?>">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo h($u['username']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo h($u['email']); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php echo $u['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700'; ?>">
                                    <?php echo ucfirst($u['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-amber-100 text-amber-800',
                                    'disabled' => 'bg-red-100 text-red-800',
                                ];
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColors[$u['status']] ?? ''; ?>">
                                    <?php echo ucfirst($u['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap">
                                <?php echo date('M j, Y', strtotime($u['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($u['id'] !== $user['id']): ?>
                                    <div class="flex items-center justify-end gap-2 flex-wrap">
                                        <?php if ($u['status'] !== 'active'): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <input type="hidden" name="action" value="activate">
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition font-medium">
                                                    Activate
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($u['status'] !== 'disabled'): ?>
                                            <form method="POST" class="inline" onsubmit="return confirm('Disable this user? They will not be able to log in or create campaigns.')">
                                                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <input type="hidden" name="action" value="disable">
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition font-medium">
                                                    Disable
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($u['role'] !== 'admin'): ?>
                                            <form method="POST" class="inline" onsubmit="return confirm('Promote this user to admin?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <input type="hidden" name="action" value="make_admin">
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition font-medium">
                                                    Make Admin
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" class="inline" onsubmit="return confirm('Remove admin privileges from this user?')">
                                                <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <input type="hidden" name="action" value="remove_admin">
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition font-medium">
                                                    Remove Admin
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">Current user</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
