<?php
/**
 * Our Mission Page
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

define('PAGE_TITLE', 'Our Mission - ' . SITE_NAME);
define('PAGE_DESCRIPTION', 'Learn about FundACause\'s mission to make fundraising simple, transparent, and accessible for everyone.');
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1">
    <!-- Hero -->
    <section class="bg-gradient-to-br from-brand-500 to-brand-700 text-white py-16">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold mb-4">Our Mission</h1>
            <p class="text-lg text-brand-100">Helping people with a platform to raise funds and donate to causes that solve real-life issues.</p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16">
        <div class="max-w-3xl mx-auto px-4 prose prose-lg">
            <div class="bg-white rounded-2xl shadow-sm p-8 sm:p-12 space-y-6 text-gray-700 leading-relaxed">
                <h2 class="text-2xl font-bold text-gray-800">Why FundACause Exists</h2>
                <p>
                    Every day, people around the world face challenges that require community support from medical emergencies 
                    and disaster relief to education and community development. We believe that everyone deserves the chance to 
                    ask for help and receive it.
                </p>

                <h2 class="text-2xl font-bold text-gray-800">What We Do</h2>
                <p>
                    FundACause provides a simple, transparent platform where anyone can create a fundraising campaign in minutes. 
                    Our mission is to remove barriers between those who need help and those who want to give. We handle the 
                    technology so you can focus on what matters. Making a real difference.
                </p>

                <h2 class="text-2xl font-bold text-gray-800">Our Values</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 not-prose">
                    <div class="p-4 bg-brand-50 rounded-xl">
                        <h3 class="font-semibold text-brand-700 mb-1">Transparency</h3>
                        <p class="text-sm text-gray-600">Every campaign shows real-time progress so donors know exactly where their money is going.</p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-xl">
                        <h3 class="font-semibold text-green-700 mb-1">Accessibility</h3>
                        <p class="text-sm text-gray-600">Anyone, anywhere can start a campaign or make a donation within seconds.</p>
                    </div>
                    <div class="p-4 bg-pink-50 rounded-xl">
                        <h3 class="font-semibold text-pink-700 mb-1">Compassion</h3>
                        <p class="text-sm text-gray-600">We exist because we believe in the power of human kindness and generosity.</p>
                    </div>
                    <div class="p-4 bg-amber-50 rounded-xl">
                        <h3 class="font-semibold text-amber-700 mb-1">Impact</h3>
                        <p class="text-sm text-gray-600">Every donation, no matter how small, contributes to solving real problems.</p>
                    </div>
                </div>

                <h2 class="text-2xl font-bold text-gray-800">How You Can Help</h2>
                <p>
                    Whether you're starting a campaign for a cause close to your heart, or donating to support someone in need, 
                    you are part of a community that believes in taking action. Together, we can fund the solutions that 
                    change lives.
                </p>

                <div class="text-center pt-4">
                    <a href="<?php echo SITE_URL; ?>/admin/login.php"
                       class="inline-block bg-brand-500 text-white px-8 py-3.5 rounded-full font-bold text-lg hover:bg-brand-600 transition shadow-lg no-underline">
                        Start a Campaign Today
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
