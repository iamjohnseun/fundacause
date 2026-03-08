<?php
/**
 * Privacy Policy Page
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

define('PAGE_TITLE', 'Privacy Policy - ' . SITE_NAME);
define('PAGE_DESCRIPTION', 'Review how FundACause collects, uses, and protects personal information for accounts, campaigns, and donations.');
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1">
    <section class="bg-gray-800 text-white py-12">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-3xl font-bold">Privacy Policy</h1>
            <p class="text-gray-400 mt-2">Last updated: <?php echo date('F j, Y'); ?></p>
        </div>
    </section>

    <section class="py-12">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm p-8 sm:p-12 space-y-6 text-gray-700 leading-relaxed">
                <h2 class="text-xl font-bold text-gray-800">1. Information We Collect</h2>
                <p>We collect information you provide when creating an account or campaign:</p>
                <ul class="list-disc pl-6 space-y-1">
                    <li>Account information: username, email, and hashed password</li>
                    <li>Campaign data: title, description, images, and PayPal email</li>
                    <li>Donation information: donor name, email, and transaction details</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-800">2. How We Use Your Information</h2>
                <ul class="list-disc pl-6 space-y-1">
                    <li>To operate and maintain your account and campaigns</li>
                    <li>To process and track donations</li>
                    <li>To display campaign information publicly</li>
                    <li>To communicate important updates about the platform</li>
                </ul>

                <h2 class="text-xl font-bold text-gray-800">3. Information Sharing</h2>
                <p>We do not sell or rent your personal information. Campaign information (title, description, images, progress) is displayed publicly. Donor names may be displayed on campaign pages unless the donor chooses to remain anonymous.</p>

                <h2 class="text-xl font-bold text-gray-800">4. PayPal Payments</h2>
                <p>All payment processing is handled by PayPal. When you make a donation, you are redirected to PayPal where their privacy policy applies. We do not store payment card details.</p>

                <h2 class="text-xl font-bold text-gray-800">5. Data Security</h2>
                <p>We implement appropriate security measures including password hashing, CSRF protection, and secure session management. However, no method of electronic transmission is 100% secure.</p>

                <h2 class="text-xl font-bold text-gray-800">6. Cookies</h2>
                <p>We use session cookies to maintain your login state. These are essential for the platform to function and are not used for tracking.</p>

                <h2 class="text-xl font-bold text-gray-800">7. Your Rights</h2>
                <p>You may request access to, correction of, or deletion of your personal data by contacting us. Deleting your account will remove all associated campaigns and data.</p>

                <h2 class="text-xl font-bold text-gray-800">8. Changes to This Policy</h2>
                <p>We may update this privacy policy from time to time. We will notify users of significant changes through the platform.</p>

                <h2 class="text-xl font-bold text-gray-800">9. Contact Us</h2>
                <p>For privacy-related questions, contact us at <a href="mailto:privacy@fundacause.com" class="text-brand-600 hover:underline">privacy@fundacause.com</a>.</p>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
