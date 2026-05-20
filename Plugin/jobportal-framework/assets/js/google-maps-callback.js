window.civiGoogleMapsCallback = function() {
    window.civiGoogleMapsReady = true;

    if (typeof jQuery !== 'undefined') {
        jQuery(document).trigger('civiGoogleMapsReady');
    }
};

