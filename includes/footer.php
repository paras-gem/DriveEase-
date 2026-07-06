    </div> <!-- Close app-container -->
    
    <script>
        // Simple Theme Toggler
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        if (themeToggleBtn) {
            // Set initial icon based on theme
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const icon = themeToggleBtn.querySelector('i');
            if (icon) {
                icon.className = currentTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            }

            themeToggleBtn.addEventListener('click', () => {
                const curTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = curTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                // Update icon if exists
                if (icon) {
                    icon.className = newTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
                }
            });
        }
    </script>
</body>
</html>
