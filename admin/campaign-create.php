<?php
/**
 * Create Campaign Page
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

requireAuth();

$user = getCurrentUser();

// Only active (approved) users can create campaigns
if ($user['status'] !== 'active') {
    $_SESSION['flash_error'] = 'Your account must be approved by an administrator before you can create campaigns.';
    header('Location: ' . SITE_URL . '/admin/');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $beneficiaryName = trim($_POST['beneficiary_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $goalAmount = floatval($_POST['goal_amount'] ?? 0);
    $paypalEmail = trim($_POST['paypal_email'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';
    $hasMediaUpload = !empty($_FILES['media']['name'][0]);
    $uploadDir = __DIR__ . '/../uploads/campaigns/';

    if (!validateCSRFToken($csrf)) {
        $error = 'Invalid request.';
    } elseif (empty($title) || empty($beneficiaryName) || empty($description) || $goalAmount <= 0 || empty($paypalEmail)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($paypalEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid PayPal email.';
    } elseif ($hasMediaUpload && !is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        $error = 'Unable to create upload directory. Please check folder permissions.';
    } elseif ($hasMediaUpload && !is_writable($uploadDir)) {
        $error = 'Upload folder is not writable. Set write permission on uploads/campaigns and try again.';
    } else {
        $db = getDB();
        $hexId = generateHexId();

        // Create campaign
        $stmt = $db->prepare("INSERT INTO campaigns (hex_id, user_id, title, beneficiary_name, description, goal_amount, paypal_email) VALUES (:hex, :uid, :title, :beneficiary, :desc, :goal, :paypal)");
        $stmt->execute([
            'hex' => $hexId,
            'uid' => $user['id'],
            'title' => $title,
            'beneficiary' => $beneficiaryName,
            'desc' => $description,
            'goal' => $goalAmount,
            'paypal' => $paypalEmail,
        ]);
        $campaignId = $db->lastInsertId();

        // Handle file uploads
        if ($hasMediaUpload) {
            $fileCount = count($_FILES['media']['name']);
            $uploadFailed = false;
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['media']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['media']['tmp_name'][$i];
                    $origName = $_FILES['media']['name'][$i];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                    // Validate file type
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];
                    if (!in_array($ext, $allowed)) continue;

                    $fileType = in_array($ext, ['mp4', 'webm']) ? 'video' : 'image';
                    $newName = uniqid('media_') . '.' . $ext;

                    if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
                        $stmt = $db->prepare("INSERT INTO campaign_media (campaign_id, file_path, file_type, sort_order) VALUES (:cid, :path, :type, :sort)");
                        $stmt->execute([
                            'cid' => $campaignId,
                            'path' => $newName,
                            'type' => $fileType,
                            'sort' => $i,
                        ]);
                    } else {
                        $uploadFailed = true;
                    }
                } elseif ($_FILES['media']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    $uploadFailed = true;
                }
            }

            if ($uploadFailed) {
                $_SESSION['upload_warning'] = 'Campaign created, but one or more media files failed to upload. Please edit the campaign and try uploading again.';
            }
        }

        header('Location: ' . SITE_URL . '/admin/?created=1');
        exit;
    }
}

$csrf = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Campaign - FundACause</title>
    <meta name="description" content="Create a new fundraising campaign on FundACause with your cause details, goal, and media.">
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
    <div class="max-w-4xl mx-auto px-4 flex justify-between items-center h-16">
        <a href="<?php echo SITE_URL; ?>/admin/" class="flex items-center space-x-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="text-gray-600">Back to Dashboard</span>
        </a>
        <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="text-sm text-red-500 hover:text-red-700">Logout</a>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Create New Campaign</h1>

    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm"><?php echo h($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf); ?>">

        <!-- Title -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Campaign Title *</label>
            <input type="text" name="title" required maxlength="255"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                   placeholder="e.g. Help Hasan's family survive" value="<?php echo h($_POST['title'] ?? ''); ?>">

             <label class="block text-sm font-medium text-gray-700 mb-2 mt-4">Who is this campaign for? *</label>
             <input type="text" name="beneficiary_name" required maxlength="255"
                 class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                 placeholder="e.g. Hasan Ibrahim" value="<?php echo h($_POST['beneficiary_name'] ?? ''); ?>">
        </div>

        <!-- Description -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
            <textarea name="description" required rows="8"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition resize-y"
                      placeholder="Describe the cause, why it matters, and how funds will be used..."><?php echo h($_POST['description'] ?? ''); ?></textarea>
            <p class="text-xs text-gray-400 mt-1">You can use line breaks for paragraphs.</p>
        </div>

        <!-- Goal & PayPal -->
        <div class="bg-white rounded-xl shadow-sm p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fundraising Goal ($) *</label>
                <input type="number" name="goal_amount" required min="1" step="0.01"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                       placeholder="10000" value="<?php echo h($_POST['goal_amount'] ?? ''); ?>">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PayPal Email *</label>
                <input type="email" name="paypal_email" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition"
                       placeholder="your@paypal-email.com" value="<?php echo h($_POST['paypal_email'] ?? ''); ?>">
                <p class="text-xs text-gray-400 mt-1">Donations will be sent to this PayPal address.</p>
            </div>
        </div>

        <!-- Media Upload -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Photos / Videos</label>
            <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-brand-400 transition"
                 onclick="document.getElementById('mediaInput').click()">
                <svg class="w-10 h-10 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="text-gray-500 mb-1">Click to upload or drag and drop</p>
                <p class="text-xs text-gray-400">JPG, PNG, GIF, WebP, MP4, WebM (Max 10MB each)</p>
            </div>
            <input type="file" id="mediaInput" name="media[]" multiple accept="image/*,video/*" class="hidden" onchange="previewFiles(this)">
            <div id="previewContainer" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4"></div>
        </div>

        <button type="submit"
                class="w-full bg-brand-500 text-white py-3 rounded-lg font-semibold hover:bg-brand-600 transition focus:ring-4 focus:ring-brand-200 text-lg">
            Launch Campaign
        </button>
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
        const badge = document.createElement('span');
        badge.className = 'absolute top-1 right-1 bg-black/60 text-white text-xs px-1.5 py-0.5 rounded';
        badge.textContent = (i + 1);
        div.appendChild(badge);
        container.appendChild(div);
    });
}

// Drag and drop
const dz = document.getElementById('dropZone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('border-brand-400', 'bg-brand-50'); });
dz.addEventListener('dragleave', () => { dz.classList.remove('border-brand-400', 'bg-brand-50'); });
dz.addEventListener('drop', e => {
    e.preventDefault();
    dz.classList.remove('border-brand-400', 'bg-brand-50');
    const input = document.getElementById('mediaInput');
    input.files = e.dataTransfer.files;
    previewFiles(input);
});
</script>

</body>
</html>
