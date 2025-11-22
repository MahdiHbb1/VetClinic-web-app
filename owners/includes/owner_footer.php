    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="text-center text-gray-600 text-sm">
                <p>&copy; <?= date('Y') ?> VetClinic. All rights reserved.</p>
                <p class="mt-2">
                    <i class="fas fa-phone mr-2"></i>Emergency Hotline: +62 123-456-7890
                </p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/enhanced-ui.js"></script>
    <script src="../../assets/js/owner_portal.js"></script>
    
    <script>
        // ===========================
        // OWNER PORTAL DARK MODE
        // ===========================
        
        // Override toggleDarkMode to ensure it works properly in owner portal
        window.toggleDarkMode = function() {
            const html = document.documentElement;
            const body = document.body;
            const toggle = document.getElementById('darkModeToggle');
            const isDark = html.getAttribute('data-theme') === 'dark';
            
            if (isDark) {
                // Switch to light mode
                html.removeAttribute('data-theme');
                body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                if (toggle) {
                    toggle.classList.remove('active');
                }
            } else {
                // Switch to dark mode
                html.setAttribute('data-theme', 'dark');
                body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                if (toggle) {
                    toggle.classList.add('active');
                }
            }
        };
        
        // Initialize theme immediately (before DOMContentLoaded)
        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.body.setAttribute('data-theme', 'dark');
            }
        })();
        
        // Apply saved theme state to toggle UI on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const toggle = document.getElementById('darkModeToggle');
            
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.body.setAttribute('data-theme', 'dark');
                if (toggle) {
                    toggle.classList.add('active');
                }
            } else {
                // Ensure light mode is properly set
                document.documentElement.removeAttribute('data-theme');
                document.body.removeAttribute('data-theme');
                if (toggle) {
                    toggle.classList.remove('active');
                }
            }
        });
        
        // Owner user menu dropdown toggle
        document.addEventListener('DOMContentLoaded', function() {
            const ownerMenuButton = document.getElementById('ownerMenuButton');
            const ownerMenu = document.getElementById('ownerMenu');
            
            if (ownerMenuButton && ownerMenu) {
                ownerMenuButton.addEventListener('click', function(event) {
                    event.stopPropagation();
                    ownerMenu.classList.toggle('hidden');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (!ownerMenuButton.contains(event.target) && !ownerMenu.contains(event.target)) {
                        ownerMenu.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>
