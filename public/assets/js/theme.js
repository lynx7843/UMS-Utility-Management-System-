(function () {
    var KEY = 'ums-theme';
    var root = document.documentElement;

    function stored() {
        try { return localStorage.getItem(KEY); } catch (e) { return null; }
    }

    function apply(theme) {
        root.setAttribute('data-theme', theme);
        try { localStorage.setItem(KEY, theme); } catch (e) {}
    }

    // Apply before first paint to avoid a flash; dark is the default.
    root.setAttribute('data-theme', stored() === 'light' ? 'light' : 'dark');

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'theme-toggle';

        function refresh() {
            var light = root.getAttribute('data-theme') === 'light';
            btn.textContent = light ? '☾' : '☀';
            btn.title = btn.ariaLabel = light ? 'Switch to dark theme' : 'Switch to light theme';
        }

        btn.addEventListener('click', function () {
            apply(root.getAttribute('data-theme') === 'light' ? 'dark' : 'light');
            refresh();
        });

        refresh();
        document.body.appendChild(btn);
    });
})();
