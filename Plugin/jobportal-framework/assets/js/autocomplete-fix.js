/**
 * Autocomplete Fix for JobPortal Framework
 */

(function($) {
    'use strict';

    window.civiAutocompleteReady = false;
    window.civiAutocompleteInitialized = false;

    function initAutocomplete() {
        if (!window.civiAutocompleteReady) {
            setTimeout(initAutocomplete, 100);
            return;
        }

        if (window.civiAutocompleteInitialized) {
            return;
        }

        var input = document.getElementById('search-location');
        if (!input) {
            return;
        }

        if (typeof google === 'undefined' || typeof google.maps === 'undefined' || typeof google.maps.places === 'undefined') {
            return;
        }

        try {
            var autocompleteOptions = {
                types: ['geocode', 'establishment'],
                componentRestrictions: { country: [] },
                fields: ['address_components', 'geometry', 'icon', 'name', 'formatted_address']
            };

            var autocomplete = new google.maps.places.Autocomplete(input, autocompleteOptions);

            autocomplete.addListener('place_changed', function() {
                var place = autocomplete.getPlace();

                if (!place.geometry) {
                    showError('Address not found. Please try again.');
                    return;
                }

                updateLocationFields(place);
            });

            $(input).on('input', function() {
                var value = $(this).val();
                if (value.length > 2) {
                    $(this).addClass('search-loading');
                } else {
                    $(this).removeClass('search-loading');
                }
            });

            window.civiAutocompleteInitialized = true;

        } catch (error) {
            showError('Unable to initialize autocomplete. Please try again.');
        }
    }

    function updateLocationFields(place) {
        var lat = place.geometry.location.lat();
        var lng = place.geometry.location.lng();
        var address = place.formatted_address || '';

        $('input[name="jobportal_map_location"]').val(lat + ',' + lng);
        $('input[name="jobportal_longtitude"]').val(lng);
        $('input[name="jobportal_latitude"]').val(lat);
        $('input[name="jobportal_map_address"]').val(address);

        updateMapMarker(place);
    }

    function updateMapMarker(place) {
        var mapElement = document.getElementById('map');
        if (!mapElement) {
            return;
        }

        if (typeof window.civiMap === 'undefined' || !window.civiMap) {
            setTimeout(function() {
                updateMapMarker(place);
            }, 500);
            return;
        }

        try {
            var map = window.civiMap;
            var marker = window.civiMarker;
            var infowindow = window.civiInfoWindow;

            if (map && marker) {
                if (infowindow) {
                    infowindow.close();
                }

                marker.setVisible(false);

                if (!place.geometry) {
                    return;
                }

                marker.setPosition(place.geometry.location);
                marker.setVisible(true);

                if (place.geometry.viewport) {
                    map.fitBounds(place.geometry.viewport);
                } else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(15);
                }

                if (infowindow) {
                    var address = place.formatted_address || place.name || '';
                    infowindow.setContent(address);
                    infowindow.open(map, marker);
                }
            }
        } catch (error) {
            // Silent error handling
        }
    }

    function showError(message) {
        var input = $('#search-location');
        input.removeClass('search-success').addClass('search-error');

        var errorDiv = $('#search-location-error');
        if (errorDiv.length === 0) {
            errorDiv = $('<div id="search-location-error" class="error-message" style="color: #dc3545; font-size: 12px; margin-top: 5px;"></div>');
            input.after(errorDiv);
        }
        errorDiv.text(message);
    }

    function showSuccess(message) {
        var input = $('#search-location');
        input.removeClass('search-error').addClass('search-success');

        $('#search-location-error').remove();

        var successDiv = $('<div class="success-message" style="color: #28a745; font-size: 12px; margin-top: 5px;"></div>');
        input.after(successDiv);
        successDiv.text(message);

        setTimeout(function() {
            successDiv.fadeOut(function() {
                $(this).remove();
                input.removeClass('search-success');
            });
        }, 3000);
    }

    function checkAndInit() {
        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
            window.civiAutocompleteReady = true;
            initAutocomplete();
        } else {
            setTimeout(checkAndInit, 500);
        }
    }

    $(document).ready(function() {
        checkAndInit();

        $(document).on('civiGoogleMapsReady', function() {
            setTimeout(checkAndInit, 100);
        });
    });

    window.civiAutocompleteFix = {
        init: initAutocomplete,
        check: checkAndInit
    };

})(jQuery);
