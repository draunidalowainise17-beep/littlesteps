document.addEventListener('DOMContentLoaded', function () {

    // =====================
    // SIDEBAR TOGGLE
    // =====================
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const body = document.body;

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            body.classList.toggle('sidebar-collapsed');
        });
    }

    // =====================
    // DARK MODE TOGGLE
    // =====================
    const darkToggle  = document.getElementById('darkModeToggle');
    const darkSwitch  = document.getElementById('darkModeSwitch');

    // Restore saved preference
    if (localStorage.getItem('adminDarkMode') === 'on') {
        body.classList.add('dark');
    }

    function toggleDarkMode() {
        body.classList.toggle('dark');
        if (body.classList.contains('dark')) {
            localStorage.setItem('adminDarkMode', 'on');
        } else {
            localStorage.setItem('adminDarkMode', 'off');
        }
    }

    if (darkToggle) darkToggle.addEventListener('click', toggleDarkMode);
    if (darkSwitch) darkSwitch.addEventListener('click', toggleDarkMode);

});