<?php
/**
 * Edit Campaign Page
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$user = getCurrentUser();

// Only active (approved) users can edit campaigns
if ($user['status'] !== 'active') {
    $_SESSION['flash_error'] = 'Your account must be approved by an administrator before you can edit campaigns.';
    header('Location: ' . SITE_URL . '/admin/');
    exit;
}

$campaignId = intval($_GET['id'] ?? 0);

$db = getDB();
$stmt = $db->prepare("SELECT * FROM campaigns WHERE id = :id AND user_id = :uid");
$stmt->execute(['id' => $campaignId, 'uid' => $user['id']]);
$campaign = $stmt->fetch();

if (!$campaign) {
    header('Location: ' . SITE_URL . '/admin/');
    exit;
}

$media = getCampaignMedia($campaign['id']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $beneficiaryName = trim($_POST['beneficiary_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $goalAmount = floatval($_POST['goal_amount'] ?? 0);
    $paypalEmail = trim($_POST['paypal_email'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $csrf = $_POST['csrf_token'] ?? '';
    $hasMediaUpload = !empty($_FILES['media']['name'][0]);
    $uploadDir = __DIR__ . '/../uploads/campaigns/';

    if (!validateCSRFToken($csrf)) {
        $error = 'Invalid request.';
    } elseif (empty($title) || empty($beneficiaryName) || empty($description) || $goalAmount <= 0 || empty($paypalEmail)) {
        $error = 'Please fill in all required fields.';
    } elseif ($hasMediaUpload && !is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        $error = 'Unable to create upload directory. Please check folder permissions.';
    } elseif ($hasMediaUpload && !is_writable($uploadDir)) {
        $error = 'Upload folder is not writable. Set write permission on uploads/campaigns and try again.';
    } else {
        // Update campaign
        $stmt = $db->prepare("UPDATE campaigns SET title = :title, beneficiary_name = :beneficiary, description = :desc, goal_amount = :goal, paypal_email = :paypal, status = :status WHERE id = :id");
        $stmt->execute([
            'title' => $title,
            'beneficiary' => $beneficiaryName,
            'desc' => $description,
            'goal' => $goalAmount,
            'paypal' => $paypalEmail,
            'status' => $status,
            'id' => $campaign['id'],
        ]);

        // Delete selected media
        if (!empty($_POST['delete_media'])) {
            $deleteIds = array_map('intval', $_POST['delete_media']);
            foreach ($deleteIds as $mid) {
                $stmt = $db->prepare("SELECT file_path FROM campaign_media WHERE id = :id AND campaign_id = :cid");
                $stmt->execute(['id' => $mid, 'cid' => $campaign['id']]);
                $mf = $stmt->fetch();
                if ($mf) {
                    $filePath = __DIR__ . '/../uploads/campaigns/' . $mf['file_path'];
                    if (file_exists($filePath)) unlink($filePath);
                    $stmt2 = $db->prepare("DELETE FROM campaign_media WHERE id = :id");
                    $stmt2->execute(['id' => $mid]);
                }
            }
        }

        // Handle new uploads
        if ($hasMediaUpload) {
            $maxSort = $db->prepare("SELECT COALESCE(MAX(sort_order), 0) FROM campaign_media WHERE campaign_id = :cid");
            $maxSort->execute(['cid' => $campaign['id']]);
            $sortStart = $maxSort->fetchColumn() + 1;

            $fileCount = count($_FILES['media']['name']);
            $uploadFailed = false;
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['media']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['media']['tmp_name'][$i];
                    $origName = $_FILES['media']['name'][$i];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];
                    if (!in_array($ext, $allowed)) continue;

                    $fileType = in_array($ext, ['mp4', 'webm']) ? 'video' : 'image';
                    $newName = uniqid('media_') . '.' . $ext;

                    if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
                        $stmt = $db->prepare("INSERT INTO campaign_media (campaign_id, file_path, file_type, sort_order) VALUES (:cid, :path, :type, :sort)");
                        $stmt->execute([
                            'cid' => $campaign['id'],
                            'path' => $newName,
                            'type' => $fileType,
                            'sort' => $sortStart + $i,
                        ]);
                    } else {
                        $uploadFailed = true;
                    }
                } elseif ($_FILES['media']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    $uploadFailed = true;
                }
            }

            if ($uploadFailed) {
                $_SESSION['upload_warning'] = 'Campaign updated, but one or more media files failed to upload. Please check file size/type and folder permissions.';
            }
        }

        header('Location: ' . SITE_URL . '/admin/campaign-edit.php?id=' . $campaign['id'] . '&saved=1');
        exit;
    }
}

$csrf = generateCSRFToken();
// Reload media after potential changes
$media = getCampaignMedia($campaign['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Campaign - FundACause</title>
    <meta name="description" content="Update your FundACause campaign details, media, and fundraising information.">
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

<nav class="bg-white shadow-sm">
    <div class="max-w-4xl mx-auto px-4 flex justify-between items-center h-16">
        <a href="<?php echo SITE_URL; ?>/admin/" class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="text-gray-600">Back to Dashboard</span>
        </a>
        <a href="<?php echo SITE_URL; ?>/<?php echo h($campaign['hex_id']); ?>" target="_blank" class="text-sm text-brand-600 hover:underline">View Live Page &rarr;</a>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Campaign</h1>
    <p class="text-sm text-gray-400 mb-8">Hex ID: <?php echo h($campaign['hex_id']); ?> &middot; Created <?php echo timeAgo($campaign['created_at']); ?></p>

    <?php if (isset($_GET['saved'])): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">Campaign updated successfully!</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm"><?php echo h($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">

        <!-- Title & Status -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Campaign Title *</label>
                    <input type="text" name="title" required maxlength="255"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                           value="<?php echo h($campaign['title']); ?>">

                      <label class="block text-sm font-medium text-gray-700 mb-2 mt-4">Who is this campaign for? *</label>
                      <input type="text" name="beneficiary_name" required maxlength="255"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                          value="<?php echo h($campaign['beneficiary_name'] ?? ''); ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition">
                        <option value="active" <?php echo $campaign['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="paused" <?php echo $campaign['status'] === 'paused' ? 'selected' : ''; ?>>Paused</option>
                        <option value="completed" <?php echo $campaign['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
            <textarea name="description" required rows="8"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition resize-y"><?php echo h($campaign['description']); ?></textarea>
        </div>

        <!-- Goal & PayPal -->
        <div class="bg-white rounded-xl shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fundraising Goal ($) *</label>
                <input type="number" name="goal_amount" required min="1" step="0.01"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                       value="<?php echo h($campaign['goal_amount']); ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PayPal Email *</label>
                <input type="email" name="paypal_email" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                       value="<?php echo h($campaign['paypal_email']); ?>">
            </div>
        </div>

        <!-- Existing Media -->
        <?php if (!empty($media)): ?>
        <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Current Media (check to remove)</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <?php foreach ($media as $m): ?>
                    <label class="relative rounded-lg overflow-hidden bg-gray-100 aspect-square cursor-pointer group">
                        <?php if ($m['file_type'] === 'image'): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/campaigns/<?php echo h($m['file_path']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <video src="<?php echo SITE_URL; ?>/uploads/campaigns/<?php echo h($m['file_path']); ?>" class="w-full h-full object-cover"></video>
                        <?php endif; ?>
                        <input type="checkbox" name="delete_media[]" value="<?php echo $m['id']; ?>"
                               class="absolute top-2 right-2 w-5 h-5 rounded border-gray-300 text-red-600 focus:ring-red-500">
                        <div class="absolute inset-0 bg-red-500/0 group-has-[:checked]:bg-red-500/30 transition pointer-events-none"></div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- New Uploads -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Add More Photos / Videos</label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-brand-400 transition"
                 onclick="document.getElementById('mediaInput').click()">
                <p class="text-gray-500">Click to upload additional media</p>
            </div>
            <input type="file" id="mediaInput" name="media[]" multiple accept="image/*,video/*" class="hidden" onchange="previewFiles(this)">
            <div id="previewContainer" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4"></div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="flex-1 bg-brand-500 text-white py-3 rounded-lg font-semibold hover:bg-brand-600 transition">
                Save Changes
            </button>
            <a href="<?php echo SITE_URL; ?>/admin/"
               class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-center">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
function previewFiles(input) {
    const container = document.getElementById('previewContainer');
    container.innerHTML = '';
    Array.from(input.files).forEach((file, i) => {
        const div = document.createElement('div');
        div.className = 'relative rounded-lg overflow-hidden bg-gray-100 aspect-square';
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'w-full h-full object-cover';
            div.appendChild(img);
        } else {
            const vid = document.createElement('video');
            vid.src = URL.createObjectURL(file);
            vid.className = 'w-full h-full object-cover';
            div.appendChild(vid);
        }
        container.appendChild(div);
    });
}
</script>

</body>
</html>
