<?php
/**
 * Terms of Service Page
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

define('PAGE_TITLE', 'Terms of Service - ' . SITE_NAME);
define('PAGE_DESCRIPTION', 'Read FundACause terms of service, including campaign guidelines, donation terms, and account responsibilities.');
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1">
    <section class="bg-gray-800 text-white py-12">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-3xl font-bold">Terms of Service</h1>
            <p class="text-gray-400 mt-2">Last updated: <?php echo date('F j, Y'); ?></p>
        </div>
    </section>

    <section class="py-12">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm p-8 sm:p-12 space-y-6 text-gray-700 leading-relaxed">
                <h2 class="text-xl font-bold text-gray-800">1. Acceptance of Terms</h2>
                <p>By accessing and using FundACause, you accept and agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our platform.</p>

                <h2 class="text-xl font-bold text-gray-800">2. Platform Description</h2>
                <p>FundACause is a crowdfunding platform that enables users to create fundraising campaigns and accept donations through PayPal. We provide the technology platform but do not directly process or hold funds.</p>

                <h2 class="text-xl font-bold text-gray-800">3. User Accounts</h2>
                <p>To create campaigns, you must register for an account. You are responsible for maintaining the confidentiality of your login credentials and for all activities that occur under your account.</p>

                <h2 class="text-xl font-bold text-gray-800">4. Campaign Guidelines</h2>
                <ul class="list-disc pl-6 space-y-2">
                    <li>Campaigns must be for legitimate purposes and accurately describe the cause.</li>
                    <li>Campaign creators must use funds as described in their campaign.</li>
                    <li>Fraudulent campaigns are strictly prohibited and may result in account termination.</li>
                    <li>We reserve the right to remove campaigns that violate our guidelines.</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-800">5. Donations</h2>
                <p>Donations are processed through PayPal and go directly to the campaign creator's PayPal account. FundACause is not responsible for how funds are used after donation. All donations are made at the donor's own risk.</p>

                <h2 class="text-xl font-bold text-gray-800">6. Content</h2>
                <p>Users are responsible for all content they upload, including images, videos, and text. Content must not violate any laws, infringe on intellectual property, or contain harmful material.</p>

                <h2 class="text-xl font-bold text-gray-800">7. Limitation of Liability</h2>
                <p>FundACause is provided "as is" without warranties of any kind. We are not liable for any damages arising from the use of our platform, including but not limited to financial losses from donations.</p>

                <h2 class="text-xl font-bold text-gray-800">8. Modifications</h2>
                <p>We reserve the right to modify these terms at any time. Continued use of the platform after modifications constitute acceptance of the updated terms.</p>

                <h2 class="text-xl font-bold text-gray-800">9. Contact</h2>
                <p>For questions regarding these terms, please contact us at <a href="mailto:support@fundacause.com" class="text-brand-600 hover:underline">support@fundacause.com</a>.</p>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
