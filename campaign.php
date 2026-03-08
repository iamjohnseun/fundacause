<?php
/**
 * Campaign Public Page
 * Displays a campaign by its hex ID
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$hexId = $_GET['hex'] ?? '';

if (empty($hexId) || !preg_match('/^[a-f0-9]{8}$/i', $hexId)) {
    http_response_code(404);
    define('PAGE_TITLE', 'Not Found - ' . SITE_NAME);
    define('PAGE_DESCRIPTION', 'The requested campaign page could not be found on FundACause.');
    include __DIR__ . '/includes/header.php';
    echo '<div class="max-w-2xl mx-auto px-4 py-20 text-center"><h1 class="text-4xl font-bold text-gray-800 mb-4">Campaign Not Found</h1><p class="text-gray-500 mb-6">The campaign you\'re looking for doesn\'t exist or has been removed.</p><a href="' . SITE_URL . '/" class="text-brand-600 hover:underline">Go home &rarr;</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$campaign = getCampaignByHex($hexId);

if (!$campaign) {
    http_response_code(404);
    define('PAGE_TITLE', 'Not Found - ' . SITE_NAME);
    define('PAGE_DESCRIPTION', 'The requested campaign page could not be found on FundACause.');
    include __DIR__ . '/includes/header.php';
    echo '<div class="max-w-2xl mx-auto px-4 py-20 text-center"><h1 class="text-4xl font-bold text-gray-800 mb-4">Campaign Not Found</h1><p class="text-gray-500 mb-6">The campaign you\'re looking for doesn\'t exist or has been removed.</p><a href="' . SITE_URL . '/" class="text-brand-600 hover:underline">Go home &rarr;</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$media = getCampaignMedia($campaign['id']);
$donations = getCampaignDonations($campaign['id'], 20);
$pct = progressPercent($campaign['raised_amount'], $campaign['goal_amount']);
$mediaCount = count($media);

define('PAGE_TITLE', $campaign['title'] . ' - ' . SITE_NAME);
define('PAGE_DESCRIPTION', 'Support "' . $campaign['title'] . '" on FundACause and help this campaign reach its fundraising goal.');
include __DIR__ . '/includes/header.php';
?>

<!-- Campaign Content -->
<main class="flex-1 pb-24 md:pb-8">
    <div class="max-w-3xl mx-auto">

        <!-- Image/Video Carousel -->
        <?php if (!empty($media)): ?>
        <div class="relative bg-black mt-4 mx-4 sm:mx-0 rounded-[18px] overflow-hidden">
            <div class="swiper campaignSwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($media as $m): ?>
                        <div class="swiper-slide">
                            <?php if ($m['file_type'] === 'image'): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/campaigns/<?php echo h($m['file_path']); ?>"
                                     alt="<?php echo h($campaign['title']); ?>"
                                     class="w-full h-[300px] sm:h-[400px] md:h-[450px] object-cover">
                            <?php else: ?>
                                <video controls class="w-full h-[300px] sm:h-[400px] md:h-[450px] object-cover bg-black">
                                    <source src="<?php echo SITE_URL; ?>/uploads/campaigns/<?php echo h($m['file_path']); ?>">
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($mediaCount > 1): ?>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                <?php endif; ?>
            </div>
            <!-- Media Counter -->
            <?php if ($mediaCount > 1): ?>
                <div class="absolute bottom-4 right-4 bg-black/60 text-white text-sm px-3 py-1 rounded-full z-10" id="mediaCounter">
                    1 of <?php echo $mediaCount; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Campaign Info -->
        <div class="px-4 py-6">
            <!-- Title -->
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4"><?php echo h($campaign['title']); ?></h1>

            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div id="progressBar" class="h-3 rounded-full progress-bar" style="width: <?php echo $pct; ?>%; background: linear-gradient(90deg, #22c55e <?php echo min($pct, 100); ?>%, #e5e7eb <?php echo min($pct, 100); ?>%); background-color: #22c55e;"></div>
                </div>
            </div>

            <!-- Raised Amount -->
            <div class="flex items-baseline gap-2 mb-6">
                <span id="raisedAmount" class="text-3xl font-bold text-green-600"><?php echo formatMoney($campaign['raised_amount']); ?></span>
                <span class="text-gray-500">Raised of <?php echo formatMoney($campaign['goal_amount']); ?></span>
            </div>

            <!-- Organizer Info -->
            <div class="flex items-center gap-3 py-4 border-t border-b border-gray-200 mb-6">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-800"><?php echo h(($campaign['beneficiary_name'] ?? '') ?: 'Campaign Beneficiary'); ?></p>
                    <p class="text-sm text-gray-500">Created <?php echo timeAgo($campaign['created_at']); ?></p>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="flex items-center justify-between gap-4 mb-6">
                <div>
                    <span id="supporterCount" class="text-2xl font-bold text-gray-800"><?php echo number_format($campaign['supporter_count']); ?></span>
                    <p class="text-sm text-gray-500">Supporters</p>
                </div>
                <div>
                    <button onclick="shareLink()" class="flex items-center gap-2 border border-brand-500 text-brand-600 px-4 py-2 rounded-full hover:bg-brand-50 transition text-sm font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                        Share
                    </button>
                </div>
            </div>

            <!-- Description -->
            <div class="prose max-w-none mb-8">
                <div class="text-gray-700 leading-relaxed whitespace-pre-wrap"><?php echo h($campaign['description']); ?></div>
            </div>

            <!-- Recent Donations -->
            <?php if (!empty($donations)): ?>
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Supporters</h3>
                <div id="donationsList" class="space-y-3">
                    <?php foreach ($donations as $d): ?>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 bg-brand-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 text-sm"><?php echo h($d['donor_name']); ?></p>
                                <p class="text-xs text-gray-400"><?php echo timeAgo($d['created_at']); ?></p>
                            </div>
                            <span class="text-sm font-semibold text-green-600"><?php echo formatMoney($d['amount']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Floating Donate Button -->
<div class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 p-3 md:bg-transparent md:border-0 md:p-0 md:bottom-6 md:right-6 md:left-auto">
    <div class="max-w-3xl mx-auto md:max-w-none">
        <button onclick="openDonateModal()"
                class="w-full md:w-auto bg-gradient-to-r from-pink-500 to-rose-500 text-white py-3.5 px-8 rounded-full font-bold text-lg shadow-lg hover:shadow-xl hover:scale-105 transition-all flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            Donate Now
        </button>
    </div>
</div>

<!-- Donation Modal -->
<div id="donateModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" style="display:none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto overflow-hidden animate-in">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-brand-500 to-brand-600 text-white p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold">Make a Donation</h3>
                    <p class="text-brand-100 text-sm mt-1"><?php echo h($campaign['title']); ?></p>
                </div>
                <button onclick="closeDonateModal()" class="text-white/80 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <!-- Preset Amounts -->
            <label class="block text-sm font-medium text-gray-700 mb-3">Choose an amount</label>
            <div class="grid grid-cols-3 gap-2 mb-4">
                <button onclick="setAmount(10)" class="amt-btn border-2 border-gray-200 rounded-lg py-3 text-center font-semibold hover:border-brand-500 hover:text-brand-600 transition">$10</button>
                <button onclick="setAmount(25)" class="amt-btn border-2 border-gray-200 rounded-lg py-3 text-center font-semibold hover:border-brand-500 hover:text-brand-600 transition">$25</button>
                <button onclick="setAmount(50)" class="amt-btn border-2 border-gray-200 rounded-lg py-3 text-center font-semibold hover:border-brand-500 hover:text-brand-600 transition">$50</button>
                <button onclick="setAmount(100)" class="amt-btn border-2 border-gray-200 rounded-lg py-3 text-center font-semibold hover:border-brand-500 hover:text-brand-600 transition">$100</button>
                <button onclick="setAmount(250)" class="amt-btn border-2 border-gray-200 rounded-lg py-3 text-center font-semibold hover:border-brand-500 hover:text-brand-600 transition">$250</button>
                <button onclick="setAmount(500)" class="amt-btn border-2 border-gray-200 rounded-lg py-3 text-center font-semibold hover:border-brand-500 hover:text-brand-600 transition">$500</button>
            </div>

            <!-- Custom Amount -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Or enter a custom amount</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">$</span>
                    <input type="number" id="donateAmount" min="1" step="0.01" placeholder="0.00"
                           class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none text-lg font-semibold">
                </div>
            </div>

            <!-- Donor Info -->
            <div class="space-y-3 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Your Name (optional)</label>
                    <input type="text" id="donorName" placeholder="Anonymous"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email (optional)</label>
                    <input type="email" id="donorEmail" placeholder="your@email.com"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>
            </div>

            <!-- PayPal Button -->
            <button onclick="proceedToPayPal()"
                    id="paypalBtn"
                    class="w-full bg-[#0070ba] text-white py-3.5 rounded-lg font-bold text-lg hover:bg-[#005ea6] transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.076-.026.175-.041.254-.93 4.778-4.005 7.201-9.138 7.201h-2.19a.563.563 0 0 0-.556.479l-1.187 7.527h-.506l-.24 1.516a.56.56 0 0 0 .554.647h3.882c.46 0 .85-.334.922-.788.06-.26.76-4.852.81-5.164a.932.932 0 0 1 .92-.788h.58c3.76 0 6.705-1.528 7.565-5.946.36-1.847.174-3.388-.768-4.397z"/>
                </svg>
                Pay with PayPal
            </button>

            <p class="text-xs text-gray-400 text-center mt-3">You'll be redirected to PayPal to complete your payment securely.</p>
        </div>
    </div>
</div>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
// Initialize Swiper
const swiper = new Swiper('.campaignSwiper', {
    loop: <?php echo $mediaCount > 1 ? 'true' : 'false'; ?>,
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    on: {
        slideChange: function() {
            const counter = document.getElementById('mediaCounter');
            if (counter) {
                counter.textContent = (this.realIndex + 1) + ' of <?php echo $mediaCount; ?>';
            }
        }
    }
});

// Donate modal
function openDonateModal() {
    const modal = document.getElementById('donateModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDonateModal() {
    const modal = document.getElementById('donateModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Close modal on backdrop click
document.getElementById('donateModal').addEventListener('click', function(e) {
    if (e.target === this) closeDonateModal();
});

// Amount presets
function setAmount(val) {
    document.getElementById('donateAmount').value = val;
    document.querySelectorAll('.amt-btn').forEach(b => {
        b.classList.remove('border-brand-500', 'text-brand-600', 'bg-brand-50');
        b.classList.add('border-gray-200');
    });
    event.target.classList.add('border-brand-500', 'text-brand-600', 'bg-brand-50');
    event.target.classList.remove('border-gray-200');
}

// PayPal redirect
function proceedToPayPal() {
    const amount = parseFloat(document.getElementById('donateAmount').value);
    const donorName = document.getElementById('donorName').value.trim() || 'Anonymous';
    const donorEmail = document.getElementById('donorEmail').value.trim();

    if (!amount || amount <= 0) {
        alert('Please enter a valid donation amount.');
        return;
    }

    const paypalEmail = '<?php echo h($campaign['paypal_email']); ?>';
    const campaignTitle = '<?php echo addslashes(h($campaign['title'])); ?>';
    const hexId = '<?php echo h($campaign['hex_id']); ?>';

    // Store donor info for after PayPal return
    sessionStorage.setItem('donation_data', JSON.stringify({
        campaign_hex: hexId,
        amount: amount,
        donor_name: donorName,
        donor_email: donorEmail
    }));

    // Redirect to PayPal
    const paypalUrl = new URL('https://www.paypal.com/cgi-bin/webscr');
    paypalUrl.searchParams.set('cmd', '_donations');
    paypalUrl.searchParams.set('business', paypalEmail);
    paypalUrl.searchParams.set('item_name', 'Donation: ' + campaignTitle);
    paypalUrl.searchParams.set('amount', amount.toFixed(2));
    paypalUrl.searchParams.set('currency_code', 'USD');
    paypalUrl.searchParams.set('return', '<?php echo SITE_URL; ?>/donation-success.php?hex=' + hexId);
    paypalUrl.searchParams.set('cancel_return', '<?php echo SITE_URL; ?>/' + hexId);
    paypalUrl.searchParams.set('no_shipping', '1');

    window.location.href = paypalUrl.toString();
}

// Share
async function shareLink() {
    const url = window.location.href;
    const title = '<?php echo addslashes(h($campaign['title'])); ?>';

    if (navigator.share) {
        try {
            await navigator.share({ title, url });
            return;
        } catch (err) {
            if (err && err.name === 'AbortError') return;
        }
    }

    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(url);
            alert('Link copied to clipboard!');
            return;
        } catch (err) {
        }
    }

    prompt('Copy this link:', url);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
