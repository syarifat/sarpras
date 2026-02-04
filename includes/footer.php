<?php

/**
 * Footer Template
 * Sarpras Management System
 */
?>
</main>
</div>

<!-- Sidebar Toggle Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('-translate-x-full');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1024) {
                    if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                        sidebar.classList.add('-translate-x-full');
                    }
                }
            });
        }

        // Highlight active menu item - find best match
        const currentPath = window.location.pathname;
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        let bestMatch = null;
        let bestMatchLength = 0;

        sidebarLinks.forEach(link => {
            const href = link.getAttribute('href');
            // Check if current path starts with or equals the link href
            if (currentPath === href || currentPath.startsWith(href.replace(/\/$/, '') + '/') || currentPath === href.replace(/\/$/, '')) {
                // Prefer longer (more specific) matches
                if (href.length > bestMatchLength) {
                    bestMatch = link;
                    bestMatchLength = href.length;
                }
            }
        });

        if (bestMatch) {
            bestMatch.classList.add('active');
        }
    });
</script>

<footer class="lg:ml-64 bg-white border-t py-4 px-6 mt-auto">
    <div class="text-center text-sm text-gray-500">
        &copy; <?= date('Y') ?> Sarpras Management System - SMK
    </div>
</footer>
</body>

</html>