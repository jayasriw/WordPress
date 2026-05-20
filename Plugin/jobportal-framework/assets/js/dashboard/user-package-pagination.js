(function() {
    'use strict';

    /**
     * User Package Pagination
     * Handles items per page selection and localStorage persistence
     */
    function initUserPackagePagination() {
        var select = document.getElementById('history-per-page-select');
        if (!select) {
            return;
        }

        var storageKey = select.closest('.pagination-dashboard').dataset.storageKey || 'user_package_history_per_page';

        // Load from localStorage on first visit (no URL param)
        var savedPerPage = localStorage.getItem(storageKey);
        var urlParams = new URLSearchParams(window.location.search);

        if (savedPerPage && !urlParams.has('history_per_page')) {
            urlParams.set('history_per_page', savedPerPage);
            urlParams.set('history_paged', '1'); // Reset to page 1
            window.location.href = window.location.pathname + '?' + urlParams.toString();
            return;
        }

        // Handle select change
        select.addEventListener('change', function() {
            var perPage = this.value;
            localStorage.setItem(storageKey, perPage);

            var urlParams = new URLSearchParams(window.location.search);
            urlParams.set('history_per_page', perPage);
            urlParams.set('history_paged', '1'); // Reset to page 1
            window.location.href = window.location.pathname + '?' + urlParams.toString();
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUserPackagePagination);
    } else {
        initUserPackagePagination();
    }
})();

