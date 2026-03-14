/**
 * Toggle "Site under update" notice based on admin setting.
 * Fetches /data/services.json and hides the notice when show_update_notice !== '1'.
 */
(function () {
    fetch('/data/services.json?v=' + Date.now())
        .then(function (r) { return r.json(); })
        .then(function (d) {
            var show = (d.settings && d.settings.show_update_notice) === '1';
            if (!show) {
                var el = document.getElementById('site-update-notice');
                if (el) el.style.display = 'none';
            }
        })
        .catch(function () {});
})();
