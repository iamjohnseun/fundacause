<!-- Footer -->
<footer class="bg-gray-800 text-gray-300 mt-auto">
    <div class="max-w-6xl mx-auto px-4 py-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Brand -->
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-brand-400 to-brand-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-white">Fund<span class="text-brand-400">ACause</span></span>
                </div>
                <p class="text-sm text-gray-400">Helping people raise funds and donate to causes that solve real-life issues.</p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-white font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?php echo SITE_URL; ?>/" class="hover:text-brand-400 transition">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/mission" class="hover:text-brand-400 transition">Our Mission</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/admin/login.php" class="hover:text-brand-400 transition">Start a Campaign</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h3 class="text-white font-semibold mb-4">Legal</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?php echo SITE_URL; ?>/terms" class="hover:text-brand-400 transition">Terms of Service</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/privacy" class="hover:text-brand-400 transition">Privacy Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-700 mt-8 pt-6 text-center text-sm text-gray-500">
            &copy; <?php echo date('Y'); ?> FundACause. All rights reserved.
        </div>
    </div>
</footer>

</body>
</html>
