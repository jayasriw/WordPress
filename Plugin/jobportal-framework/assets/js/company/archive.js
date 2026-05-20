(function ($) {
    "use strict";

    // Performance: Throttle function for scroll events
    function throttle(func, limit) {
        var inThrottle;
        return function() {
            var args = arguments;
            var context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(function() { inThrottle = false; }, limit);
            }
        };
    }

    function addMarkerClass(markerId, markerIcon) {
        var selectors = [
            'div[title="marker' + markerId + '"]',
            'div[aria-label="marker' + markerId + '"]',
            'div[role="button"][title*="marker' + markerId + '"]'
        ];

        var attempts = 0;
        var maxAttempts = 15;

        function tryAddClass() {
            var markerDiv = null;

            for (var i = 0; i < selectors.length; i++) {
                markerDiv = $(selectors[i]);
                if (markerDiv.length > 0) {
                    break;
                }
            }

            if (markerDiv && markerDiv.length) {
                markerDiv.addClass('marker');
                if (markerIcon) {
                    markerDiv.css('background-image', 'url(' + markerIcon + ')');
                }
                return true;
            } else if (attempts < maxAttempts) {
                attempts++;
                setTimeout(tryAddClass, 300);
            } else {
                var allMarkers = $('div[title*="marker"]');
                allMarkers.each(function () {
                });
            }
            return false;
        }

        tryAddClass();
    }

    function addClassToAllMarkers() {
        var allMarkers = $('div[title*="marker"]');

        allMarkers.each(function () {
            var $marker = $(this);
            if (!$marker.hasClass('marker')) {
                $marker.addClass('marker');
            }
        });


        var ariaMarkers = $('div[aria-label*="marker"]');
        ariaMarkers.each(function () {
            var $marker = $(this);
            if (!$marker.hasClass('marker')) {
                $marker.addClass('marker');

            }
        });
    }

    // Function to create circular marker icon
    function createCircularMarkerIcon(imageUrl, size = 40) {
        // Create canvas to draw circular image
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        canvas.width = size;
        canvas.height = size;

        // Create circular clipping path
        ctx.beginPath();
        ctx.arc(size / 2, size / 2, size / 2, 0, Math.PI * 2);
        ctx.closePath();
        ctx.clip();

        // Create image
        var img = new Image();
        img.crossOrigin = 'anonymous';

        return new Promise((resolve) => {
            img.onload = function () {
                // Draw image in circular area
                ctx.drawImage(img, 0, 0, size, size);

                // Convert to data URL (no border needed)
                var dataUrl = canvas.toDataURL();
                resolve(dataUrl);
            };

            img.onerror = function () {
                // Fallback to original image
                resolve(imageUrl);
            };

            img.src = imageUrl;
        });
    }

    var ajax_url = jobportal_template_vars.ajax_url,
        map_effects = jobportal_template_vars.map_effects,
        not_company = jobportal_company_archive_vars.not_company,
        map_api_key = jobportal_template_vars.map_api_key,
        map_type = jobportal_template_vars.map_type,
        default_lat = jobportal_template_vars.default_lat,
        default_lng = jobportal_template_vars.default_lng,
        default_icon = jobportal_template_vars.marker_default_icon,
        item_amount = jobportal_company_archive_vars.item_amount,
        range_min = jobportal_company_archive_vars.range_min,
        range_max = jobportal_company_archive_vars.range_max;

    var ajax_call = false;
    var googleMapsLoaded = false;

    var menu_filter_wrap = $(".jobportal-menu-filter");
    var archive_company = $(".archive-company");
    var jobportal_map;
    var markers = [];
    //map
    var mapType = $(".maptype").data("maptype");
    var is_mobile = false;
    var has_map = "";
    if (mapType == "google_map") {
        var company_maps_filter = $("#jobs-map-filter");
    } else if (mapType == "openstreetmap") {
        var company_maps_filter = $("#maps");
    } else {
        var company_maps_filter = $("#map");
    }

    if (company_maps_filter.length) {
        has_map = "yes";
    }

    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        is_mobile = true;
    }

    function checkGoogleMapsLoaded() {
        if (typeof google !== 'undefined' && google.maps && google.maps.InfoWindow) {
            googleMapsLoaded = true;
            return true;
        }
        return false;
    }

    function waitForGoogleMaps(callback) {
        if (checkGoogleMapsLoaded()) {
            callback();
        } else {
            setTimeout(function () {
                waitForGoogleMaps(callback);
            }, 100);
        }
    }

    // Load Google Maps API if not already loaded
    function loadGoogleMapsAPI() {
        if (checkGoogleMapsLoaded()) {
            return Promise.resolve();
        }

        return new Promise((resolve, reject) => {
            // Check if script has been added
            if (document.querySelector('script[src*="maps.googleapis.com"]')) {
                waitForGoogleMaps(resolve);
            } else {
                // Add Google Maps API script
                var script = document.createElement('script');
                script.src = 'https://maps.googleapis.com/maps/api/js?libraries=places&key=' + map_api_key + '&loading=async&callback=initGoogleMaps';
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);

                window.initGoogleMaps = function () {
                    googleMapsLoaded = true;
                    resolve();
                };

                script.onerror = reject;
            }
        });
    }

    var jobportal_hover_map_effects = function () {
        if (map_effects !== '' && has_map) {
            $(".map-event .area-company .jobportal-company-item").each(function () {
                var title = $(this).find(".add-follow-company").data("company-id");

                if (mapType == "google_map") {
                    $(this).on("mouseenter", function () {
                        if (map_effects == 'popup') {
                            $('div[title="marker' + title + '"]').trigger("click").css('z-index', '2');
                        } else if (map_effects == 'shine') {
                            $('div[title="marker' + title + '"]').trigger("click").addClass("mouseenter");
                        }
                    });

                    $(this).on("mouseleave", function () {
                        if (map_effects == 'popup') {
                            $('div[title="marker' + title + '"]').css('z-index', '0');
                            infowindow.open(null, null);
                        } else if (map_effects == 'shine') {
                            $('div[title="marker' + title + '"]').trigger("click").removeClass("mouseenter");
                        }
                    });
                } else if (mapType == "openstreetmap") {
                    $(this).on("mouseenter", function () {
                        if (map_effects == 'popup') {
                            $(".marker-" + title).trigger("click").css('z-index', '2');
                        } else if (map_effects == 'shine') {
                            $(".marker-" + title).trigger("click").addClass("mouseenter");
                        }
                    });

                    $(this).on("mouseleave", function () {
                        if (map_effects == 'popup') {
                            $(".marker-" + title).css('z-index', '0');
                            $(".leaflet-popup-close-button").trigger("click");
                        } else if (map_effects == 'shine') {
                            $(".marker-" + title).trigger("click").removeClass("mouseenter");
                        }
                    });
                } else {
                    $(this).on("mouseenter", function () {
                        if (map_effects == 'popup') {
                            $("#marker-" + title).trigger("click").css('z-index', '2');
                        } else if (map_effects == 'shine') {
                            $("#marker-" + title).trigger("click").addClass("mouseenter");
                        }
                    });

                    $(this).on("mouseleave", function () {
                        if (map_effects == 'popup') {
                            $(".marker-" + title).css('z-index', '0');
                            $(".mapboxgl-popup-close-button").trigger("click");
                        } else if (map_effects == 'shine') {
                            $("#marker-" + title).trigger("click").removeClass("mouseenter");
                        }
                    });
                }
            });
        }
    };

    COMPANY.elements = {
        init: function () {
            this.company_layout();
            this.slider_range();
            this.pagination();
            this.display_clear();
            this.get_city_from_result();
            this.search_cate_location();
            this.filter_clear_top();
            this.filter_clear();

            archive_company
                .find(".jobportal-menu-filter")
                .on("input", "input.input-control", function () {
                    $(".jobportal-pagination").find('input[name="paged"]').val(1);
                    $(".form-company-top-filter .btn-top-filter").removeData("clicked");
                    ajax_call = true;
                    COMPANY.elements.ajax_load(ajax_call);
                });

            archive_company.find("select.sort-by").on("change", function () {
                $(".jobportal-pagination").find('input[name="paged"]').val(1);
                $(".form-company-top-filter .btn-top-filter").removeData("clicked");
                ajax_call = true;
                COMPANY.elements.ajax_load(ajax_call);
            });

            archive_company
                .find(".form-company-top-filter .btn-top-filter")
                .on("click", function (e) {
                    e.preventDefault();
                    $(".jobportal-pagination").find('input[name="paged"]').val(1);
                    $(this).data("clicked", true);
                    ajax_call = true;
                    COMPANY.elements.ajax_load(ajax_call);
                });

            if (company_maps_filter.length > 0) {
                if (mapType == "google_map") {
                    loadGoogleMapsAPI().then(function () {
                        COMPANY.elements.ajax_load();
                    });
                } else {
                    COMPANY.elements.ajax_load();
                }
            }

            $('.btn-hide-map input[type="checkbox"]').on("change", function () {
                var elem = $(".archive-layout .inner-content");
                var ltf = $(".layout-top-filter .nav-bar");
                if ($(this).attr("checked")) {
                    $("input[value='hide_map']").prop("checked", false);
                } else {
                    $("input[value='hide_map']").prop("checked", true);
                }
                if (elem.hasClass("has-map")) {
                    elem.removeClass("has-map");
                    elem.addClass("no-map");
                    ltf.removeClass("has-map");
                    ltf.addClass("no-map");
                } else {
                    elem.removeClass("no-map");
                    elem.addClass("has-map");
                    ltf.removeClass("no-map");
                    ltf.addClass("has-map");
                }
                ajax_call = true;
                COMPANY.elements.ajax_load(ajax_call);
            });

            $('.locations-filter select').on("change", function () {
                ajax_call = true;
                COMPANY.elements.ajax_load(ajax_call);
            });
        },

        slider_range: function () {
            var archive_company = $(".archive-company");
            var min = parseInt(range_min);
            var max = parseInt(range_max);
            var timers = {};

            function delayShowData(type, values) {
                clearTimeout(timers[type]);
                timers[type] = setTimeout(function () {
                    $(".jobportal-pagination").find('input[name="paged"]').val(1);
                    $(this).data("clicked", true);
                    ajax_call = true;
                    COMPANY.elements.ajax_load(ajax_call);
                }, 500);
            }

            $("#slider-range").slider({
                range: true,
                min: min,
                max: max,
                step: 1,
                values: [min, max],
                slide: function (event, ui) {
                    $("#amount").val(ui.values[0] + " - " + ui.values[1]);
                },
                change: function (event, ui) {
                    var values_start = ui.values[0];
                    var values_end = ui.values[1];
                    if (values_start !== min || values_end !== max) {
                        archive_company.addClass("filter-active");
                    } else {
                        archive_company.removeClass("filter-active");
                    }
                },
                stop: function () {
                    delayShowData();
                },
            });
            $("#amount").val(
                $("#slider-range").slider("values", 0) +
                " - " +
                $("#slider-range").slider("values", 1)
            );
        },

        pagination: function () {
            var type_pagination = $(".jobportal-pagination").attr("data-type");

            $("body").on(
                "click",
                ".jobportal-pagination.ajax-call a.page-numbers",
                function (e) {
                    e.preventDefault();
                    archive_company
                        .find(".jobportal-pagination .pagination")
                        .addClass("active");
                    archive_company
                        .find(".jobportal-pagination li .page-numbers")
                        .removeClass("current");
                    $(this).addClass("current");
                    var paged = $(this).text();
                    var current_page = 1;
                    if ($(".jobportal-pagination").find('input[name="paged"]').val()) {
                        current_page = $(".jobportal-pagination")
                            .find('input[name="paged"]')
                            .val();
                    }
                    if ($(this).hasClass("next")) {
                        paged = parseInt(current_page) + 1;
                    }
                    if ($(this).hasClass("prev")) {
                        paged = parseInt(current_page) - 1;
                    }
                    archive_company
                        .find(".jobportal-pagination")
                        .find('input[name="paged"]')
                        .val(paged);

                    if (type_pagination !== "loadpage") {
                        e.preventDefault();
                        ajax_call = true;
                        if (type_pagination == "number") {
                            COMPANY.elements.ajax_load(ajax_call, "pagination");
                        } else {
                            COMPANY.elements.ajax_load(ajax_call, "loadmore");
                        }
                    }
                }
            );
        },

        removeClassStartingWith: function (node, begin) {
            node.removeClass(function (index, className) {
                return (
                    className.match(new RegExp("\\b" + begin + "\\S+", "g")) || []
                ).join(" ");
            });
        },

        company_layout: function () {
            archive_company.find(".company-layout a").on("click", function (event) {
                event.preventDefault();
                var layout = $(this).attr("data-layout");
                var type_pagination = $(".jobportal-pagination").attr("data-type");
                if (type_pagination == "loadmore") {
                    $(".jobportal-pagination").find('input[name="paged"]').val(1);
                }
                $(this).closest(".company-layout").find(">a").removeClass("active");
                $(this).addClass("active");
                COMPANY.elements.removeClassStartingWith(
                    $(".archive-layout>.inner-content"),
                    "layout-"
                );
                $(this).closest(".inner-content").addClass(layout);

                $(".form-company-top-filter .btn-top-filter").removeData("clicked");

                $(".area-company .jobportal-company-item").each(function () {
                    COMPANY.elements.removeClassStartingWith($(this), "layout-");
                    $(this).addClass(layout);
                });

                ajax_call = true;
                COMPANY.elements.ajax_load(ajax_call);
            });
        },

        get_city_from_result: function (result, mapType) {
            let city = "";

            if (mapType === "google_map") {
                if (result.status === "OK" && result.results.length > 0) {
                    const components = result.results[0].address_components;

                    const getComponent = (types) => {
                        const match = components.find(comp =>
                            types.some(type => comp.types.includes(type))
                        );
                        return match ? match.long_name : "";
                    };

                    city =
                        getComponent(["locality"]) ||
                        getComponent(["administrative_area_level_1"]) ||
                        getComponent(["administrative_area_level_2"]) ||
                        getComponent(["sublocality"]);
                }
            }

            else if (mapType === "mapbox") {
                if (result.features && result.features.length > 0) {
                    const getFeature = (type) => {
                        const match = result.features.find(f => f.place_type.includes(type));
                        return match ? match.text : "";
                    };

                    city =
                        getFeature("place") ||
                        getFeature("region") ||
                        getFeature("district");
                }
            }

            else if (mapType === "openstreetmap") {
                const address = result.address || {};
                city =
                    address.city ||
                    address.town ||
                    address.village ||
                    address.municipality ||
                    address.county ||
                    "";
            }

            return city;
        },

        search_cate_location: function () {
            const form = $(".archive-layout .jobportal-form-location");
            if (!form.length) return;

            const input = form.find(".archive-search-location");
            const field_select = form.find(".jobportal-select2");

            function fixDropdownPosition() {
                setTimeout(function() {
                    const formGroup = form.closest('.form-group');
                    if (!formGroup.length) return;

                    const formGroupWidth = formGroup.outerWidth();
                    const inputWidth = input.outerWidth();
                    const targetWidth = formGroupWidth > 0 ? formGroupWidth : (inputWidth > 0 ? inputWidth : 300);

                    const dropdown = $('.select2-dropdown:visible').last();
                    if (!dropdown.length) return;

                    const dropdownParent = dropdown.parent();
                    const inputOffset = input.offset();
                    const formGroupOffset = formGroup.offset();
                    const targetOffset = formGroupOffset || inputOffset;

                    let correctLeft = 0;
                    if (targetOffset && dropdownParent.length) {
                        const parentOffset = dropdownParent.offset();
                        if (parentOffset) {
                            correctLeft = targetOffset.left - parentOffset.left;
                        }
                    }

                    dropdown.css({
                        'left': correctLeft + 'px',
                        'width': targetWidth + 'px',
                        'min-width': targetWidth + 'px'
                    });
                }, 10);
            }

            $("body").on("mousedown", ".jobportal-form-location .icon-arrow i", function (e) {
                e.preventDefault();
                const select2_container = form.find(".select2.select2-container");
                if (select2_container.hasClass("select2-container--open")) {
                    field_select.select2("close");
                } else {
                    const currentInputValue = input.val();
                    if (currentInputValue) {
                        const matchingOption = field_select.find('option').filter(function() {
                            return $(this).text().trim() === currentInputValue.trim();
                        });
                        if (matchingOption.length) {
                            field_select.val(matchingOption.val()).trigger("change");
                        }
                    }
                    field_select.select2("open");
                }
            });

            field_select.on("select2:open", fixDropdownPosition);

            // Update input when item selected
            field_select.on("select2:select", function (e) {
                const data = e.params.data;
                input.val(data.text).trigger("change");
            });

            // Geolocation button
            const locationBtn = form.find(".icon-location svg");
            if (!locationBtn.length) return;

            locationBtn.on("click", () => {
                const map_type = typeof jobportal_template_vars !== 'undefined' ? jobportal_template_vars.map_type : 'mapbox';
                const map_api_key = typeof jobportal_template_vars !== 'undefined' ? jobportal_template_vars.map_api_key : '';

                // Check if we're in a secure context (HTTPS or localhost)
                const isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

                if (!isSecureContext) {
                    alert("Geolocation requires HTTPS. Please use a secure connection.");
                    return;
                }

                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const latitude = position.coords.latitude;
                            const longitude = position.coords.longitude;

                            let url = "";
                            if (map_type === "google_map") {
                                url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${map_api_key}`;
                            } else if (map_type === "mapbox") {
                                url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${longitude},${latitude}.json?access_token=${map_api_key}`;
                            } else if (map_type === "openstreetmap") {
                                url = `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=jsonv2`;
                            } else {
                                console.error("Invalid map_type:", map_type);
                                return;
                            }

                            $.ajax({
                                url: url,
                                type: "GET",
                                success: (result) => {
                                    const city = COMPANY.elements.get_city_from_result(result, map_type);
                                    if (city) {
                                        input.val(city).trigger("change");
                                    } else {
                                        console.warn("City not found from API response.");
                                    }
                                },
                                error: (error) => {
                                    console.error("AJAX Error:", error);
                                }
                            });
                        },
                        (error) => {
                            console.error("Geolocation error:", error.message);
                            if (error.code === 1) {
                                alert("Please allow location access to use this feature.");
                            } else if (error.code === 2) {
                                alert("Location unavailable. Please try again.");
                            } else if (error.code === 3) {
                                alert("Location request timed out. Please try again.");
                            }
                        },
                        {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0,
                        }
                    );
                } else {
                    alert("Your browser does not support geolocation.");
                }
            });
        },

        display_clear: function () {
            var archive_company = $(".archive-company");
            var has_filter_active = (
                $(".jobportal-menu-filter ul.filter-control li.active").length > 0 ||
                $('.jobportal-menu-filter input[type="checkbox"]:checked').length > 0 ||
                ($("select.jobportal-select-country").val() !== '' && $("select.jobportal-select-country").val() !== null)
            );

            $('.jobportal-menu-filter input[type="checkbox"]:checked').closest(".entry-filter").addClass("open");
            if ($("select.jobportal-select-country").val() !== '' && $("select.jobportal-select-country").val() !== null) {
                $("select.jobportal-select-country").closest(".entry-filter").addClass("open");
            }

            if (has_filter_active) {
                $(".jobportal-nav-filter").addClass("active");
                archive_company.find(".jobportal-clear-filter").show();
            } else {
                $(".jobportal-nav-filter").removeClass("active");
                archive_company.find(".jobportal-clear-filter").hide();
                $(".jobportal-menu-filter .entry-filter").removeClass("open");
            }
        },

        filter_clear_top: function () {
            archive_company.find(".jobportal-clear-top-filter").on("click", function () {
                $('.form-company-top-filter input[name="company_filter_search"]').val("");
                $('.form-company-top-filter input[name="company-search-location"]').val("");
                $(".form-company-top-filter .jobportal-select2").val("");
                $(".form-company-top-filter .jobportal-select2").select2("destroy");
                $(".form-company-top-filter .jobportal-select2").each(function () {
                    var option = $(this).find('option');
                    if (theme_vars.enable_search_box_dropdown == 1) {
                        if (option.length > theme_vars.limit_search_box) {
                            $(this).select2();
                        } else {
                            $(this).select2({
                                minimumResultsForSearch: -1,
                            });
                        }
                    } else {
                        $(this).select2({
                            minimumResultsForSearch: -1,
                        });
                    }
                });
                $('.select2.select2-container').on('click', function () {
                    var options = $(this).prev().find('option');
                    options.each(function () {
                        var option_val = $(this).val();
                        var level = $(this).attr('data-level');
                        $('.select2-results li[id$="' + option_val + '"]').attr('data-level', level);
                    });
                });
                $('.jobportal-form-location .icon-arrow i').on('click', function () {
                    var options = $(this).closest('.jobportal-form-location').find('select.jobportal-select2 option');
                    options.each(function () {
                        var option_val = $(this).val();
                        var level = $(this).attr('data-level');
                        $('.select2-results li[id$="' + option_val + '"]').attr('data-level', level);
                    });
                });
                GLF.element.clean_archive_url();
                ajax_call = true;
                COMPANY.elements.ajax_load(ajax_call);
            });
        },

        filter_clear: function () {
            archive_company.find(".jobportal-clear-filter").on("click", function () {
                $(".jobportal-menu-filter ul.filter-control li").removeClass("active");
                $('.jobportal-menu-filter input[type="checkbox"]').prop("checked", false);
                $('.jobportal-menu-filter select[name="company_filter_salary_min"]').prop(
                    "selectedIndex",
                    0
                );
                $('.jobportal-menu-filter select[name="company_filter_salary_max"]').prop(
                    "selectedIndex",
                    0
                );
                $('.jobportal-menu-filter select[name="company_filter_rate"]').prop(
                    "selectedIndex",
                    0
                );
                $("#slider-range").slider("values", 0, parseInt(range_min));
                $("#slider-range").slider("values", 1, parseInt(range_max));
                $("#amount").val(parseInt(range_min) + " - " + parseInt(range_max));
                $(".jobportal-menu-filter .jobportal-select2").val("");
                $(".jobportal-menu-filter .jobportal-select2").select2("destroy");
                $(".jobportal-menu-filter .jobportal-select2").select2();
                ajax_call = true;
                COMPANY.elements.ajax_load(ajax_call);
            });
        },

        ajax_load: function (ajax_call, pagination) {
            var title,
                sort_by,
                range_min,
                founded,
                range_max,
                categories,
                radius_cities,
                location,
                current_term,
                type_term,
                size,
                rating,
                search_fields_sidebar,
                location_country,
                location_state,
                location_city,
                company_layout;
            var paged = 1;

            paged = archive_company
                .find(".jobportal-pagination")
                .find('input[name="paged"]')
                .val();
            title = archive_company.find('input[name="company_filter_search"]').val();
            current_term = $('input[name="current_term"]').val();
            type_term = $('input[name="type_term"]').val();

            company_layout = archive_company
                .find(".company-layout a.active")
                .attr("data-layout");

            sort_by = menu_filter_wrap
                .find(".sort-by.filter-control li.active a")
                .data("sort");

            var select_sort = $('.archive-layout select[name="sort_by"]').val();
            if (select_sort) {
                sort_by = select_sort;
            }

            search_fields_sidebar = $('input[name="search_fields_sidebar"]').val();
            var result_fields = $.parseJSON(search_fields_sidebar);

            location = $('input[name="company-search-location"]').val();
            location_country = $('select.jobportal-select-country').val();
            location_state = $('select.jobportal-select-state').val();
            location_city = $('select.jobportal-select-city').val();
            radius_cities = $('.jobportal-form-location').find('input[name="company_number_radius"]').val();

            if (result_fields.hasOwnProperty('company-founded')) {
                range_min = archive_company.find("#slider-range").slider("values", 0);
                range_max = archive_company.find("#slider-range").slider("values", 1);
            } else {
                founded = archive_company.find('select[name="company-founded"]').val();
            }

            if (result_fields.hasOwnProperty('company-categories')) {
                categories = $('input[name="company-categories_id[]"]:checked').map(function () {
                    return $(this).val();
                }).get();
            } else {
                categories = $('select[name="company_categories"]').val();
            }

            if (result_fields.hasOwnProperty('company-size')) {
                size = $('input[name="company-size_id[]"]:checked').map(function () {
                    return $(this).val();
                }).get();
            } else {
                size = $('select[name="company-size"]').val();
            }

            if (result_fields.hasOwnProperty('company-rating')) {
                rating = $('input[name="company_rating[]"]:checked').map(function () {
                    return $(this).val();
                }).get();
            } else {
                rating = $('select[name="company-rating"]').val();
            }


            //Map
            var map_html = $(".maptype").clone();
            if (mapType == "google_map") {
                // Đảm bảo Google Maps API đã load
                if (!checkGoogleMapsLoaded()) {
                    return;
                }

                var marker_cluster = null,
                    googlemap_default_zoom = jobportal_template_vars.googlemap_default_zoom,
                    not_found = jobportal_template_vars.not_found,
                    clusterIcon = jobportal_template_vars.clusterIcon,
                    google_map_style = jobportal_template_vars.google_map_style,
                    google_map_type = jobportal_template_vars.google_map_type,
                    pin_cluster_enable = jobportal_template_vars.pin_cluster_enable;

                // Tạo InfoWindow sau khi đảm bảo API đã load
                var infowindow = null;
                try {
                    infowindow = new google.maps.InfoWindow({
                        maxWidth: 370,
                    });
                } catch (error) {
                    return;
                }

                var silver = [
                    {
                        featureType: "landscape",
                        elementType: "labels",
                        stylers: [
                            {
                                visibility: "off",
                            },
                        ],
                    },
                    {
                        featureType: "transit",
                        elementType: "labels",
                        stylers: [
                            {
                                visibility: "off",
                            },
                        ],
                    },
                    {
                        featureType: "poi",
                        elementType: "labels",
                        stylers: [
                            {
                                visibility: "off",
                            },
                        ],
                    },
                    {
                        featureType: "water",
                        elementType: "labels",
                        stylers: [
                            {
                                visibility: "off",
                            },
                        ],
                    },
                    {
                        featureType: "road",
                        elementType: "labels.icon",
                        stylers: [
                            {
                                visibility: "off",
                            },
                        ],
                    },
                    {
                        stylers: [
                            {
                                hue: "#00aaff",
                            },
                            {
                                saturation: -100,
                            },
                            {
                                gamma: 2.15,
                            },
                            {
                                lightness: 12,
                            },
                        ],
                    },
                    {
                        featureType: "road",
                        elementType: "labels.text.fill",
                        stylers: [
                            {
                                visibility: "on",
                            },
                            {
                                lightness: 24,
                            },
                        ],
                    },
                    {
                        featureType: "road",
                        elementType: "geometry",
                        stylers: [
                            {
                                lightness: 57,
                            },
                        ],
                    },
                ];

                if (has_map) {
                    var jobportal_search_map_option = {
                        scrollwheel: true,
                        scroll: { x: $(window).scrollLeft(), y: $(window).scrollTop() },
                        zoom: parseInt(googlemap_default_zoom),
                        mapTypeId: google_map_type,
                        draggable: true,
                        fullscreenControl: true,
                        styles: silver,
                        mapTypeControl: false,
                        zoomControlOptions: {
                            position: google.maps.ControlPosition.RIGHT_BOTTOM,
                        },
                        fullscreenControlOptions: {
                            position: google.maps.ControlPosition.RIGHT_BOTTOM,
                        },
                    };
                }

                var jobportal_add_markers = function (props, map) {
                    $.each(props, function (i, prop) {
                        // Check coordinate data
                        if (!prop.lat || !prop.lng || isNaN(parseFloat(prop.lat)) || isNaN(parseFloat(prop.lng))) {
                            return;
                        }

                        var latlng = new google.maps.LatLng(parseFloat(prop.lat), parseFloat(prop.lng)),
                            marker_url = prop.marker_icon || default_icon,
                            marker_size = new google.maps.Size(40, 40);
                        var marker_icon = {
                            url: marker_url,
                            size: marker_size,
                            scaledSize: new google.maps.Size(40, 40),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(20, 20),
                        };

                        var marker = new google.maps.Marker({
                            position: latlng,
                            url: ".company-" + prop.id,
                            map: map,
                            company: prop.company,
                            icon: marker_icon,
                            draggable: false,
                            title: "marker" + prop.id,
                            animation: google.maps.Animation.DROP,
                        });

                        // Option 2: Create circular marker using canvas (uncomment to use)
                        createCircularMarkerIcon(marker_url, 40).then(function (circularIconUrl) {
                            var circular_marker_icon = {
                                url: circularIconUrl,
                                size: marker_size,
                                scaledSize: new google.maps.Size(40, 40),
                                origin: new google.maps.Point(0, 0),
                                anchor: new google.maps.Point(20, 20),
                            };
                            marker.setIcon(circular_marker_icon);
                        });

                        setTimeout(function () {
                            addMarkerClass(prop.id, prop.marker_icon);
                        }, 500);


                        google.maps.event.addListenerOnce(map, 'idle', function () {
                            setTimeout(function () {
                                addClassToAllMarkers();
                                // Set background images for all markers
                                $.each(props, function (i, prop) {
                                    var markerDiv = $('div[title="marker' + prop.id + '"]');
                                    if (markerDiv.length) {
                                        markerDiv.css('background-image', 'url(' + prop.marker_icon + ')');
                                    }
                                });
                            }, 1000);
                        });

                        var prop_title = prop.data ? prop.data.post_title : prop.title;

                        var contentString = document.createElement("div");
                        contentString.className = "jobportal-marker";
                        contentString.innerHTML = prop.company;

                        var click_marker = false;

                        marker.addListener("mouseover", function () {
                            click_marker = true;
                        });

                        marker.addListener("mouseout", function () {
                            click_marker = false;
                        });

                        google.maps.event.addListener(marker, "click", function () {
                            infowindow.close();
                            infowindow.setContent(contentString);
                            infowindow.open(map, marker);

                            var scale = Math.pow(2, map.getZoom()),
                                offsety = 30 / scale || 0,
                                projection = map.getProjection(),
                                markerPosition = marker.getPosition(),
                                markerScreenPosition =
                                    projection.fromLatLngToPoint(markerPosition),
                                pointHalfScreenAbove = new google.maps.Point(
                                    markerScreenPosition.x,
                                    markerScreenPosition.y - offsety
                                ),
                                aboveMarkerLatLng =
                                    projection.fromPointToLatLng(pointHalfScreenAbove);
                            map.panTo(aboveMarkerLatLng);

                            var elem = $(marker.url);
                            $(".area-company .jobportal-company-item").removeClass("highlight");
                            if (
                                elem.length > 0 &&
                                click_marker &&
                                $(".archive-company.map-event").length > 0
                            ) {
                                elem.addClass("highlight");
                                $("html, body").animate(
                                    {
                                        scrollTop: elem.offset().top - 50,
                                    },
                                    500
                                );
                            }
                        });

                        markers.push(marker);
                    });

                    $('#marker-count').text(markers.length);
                };

                var jobportal_my_location = function (map) {
                    // Ensure Google Maps API is loaded before proceeding
                    if (!checkGoogleMapsLoaded()) {
                        loadGoogleMapsAPI().then(function () {
                            jobportal_my_location(map);
                        });
                        return;
                    }

                    // Ensure map is properly initialized
                    if (!map || typeof map.getCenter !== 'function') {
                        return;
                    }

                    var my_location = {};
                    var my_lat = "";
                    var my_lng = "";

                    // Get a safe default position for error handling
                    var defaultPosition = map.getCenter();
                    if (!defaultPosition) {
                        // Fallback to a default position if map center is not available
                        defaultPosition = { lat: 0, lng: 0 };
                    }

                    // Check if we're in a secure context (HTTPS or localhost)
                    var isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';

                    if (!isSecureContext) {
                        // Don't attempt geolocation in non-secure context
                        return;
                    }

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                var pos = {
                                    lat: position.coords.latitude,
                                    lng: position.coords.longitude,
                                };

                                my_lat = position.coords.latitude;
                                my_lng = position.coords.longitude;

                                my_location = {
                                    lat: parseFloat(my_lat),
                                    lng: parseFloat(my_lng),
                                };
                            },
                            function (error) {
                                handleLocationError(true, infowindow, defaultPosition, map);
                            }
                        );
                    } else {
                        // Browser doesn't support Geolocation
                        handleLocationError(false, infowindow, defaultPosition, map);
                    }

                    function CenterControl(controlDiv, map) {
                        // Set CSS for the control border.
                        const controlUI = document.createElement("div");
                        controlUI.style.backgroundColor = "#fff";
                        controlUI.style.border = "2px solid #fff";
                        controlUI.style.borderRadius = "3px";
                        controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
                        controlUI.style.cursor = "pointer";
                        controlUI.style.width = "40px";
                        controlUI.style.height = "40px";
                        controlUI.style.margin = "10px";
                        controlUI.style.textAlign = "center";
                        controlUI.title = "My location";
                        controlDiv.appendChild(controlUI);

                        // Set CSS for the control interior.
                        const controlText = document.createElement("div");
                        controlText.style.fontSize = "18px";
                        controlText.style.lineHeight = "37px";
                        controlText.style.paddingLeft = "5px";
                        controlText.style.paddingRight = "5px";
                        controlText.innerHTML = "<i class='fas fa-location'></i>";
                        controlUI.appendChild(controlText);

                        var marker_icon = {
                            url: default_icon,
                            scaledSize: new google.maps.Size(40, 40),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(7, 27),
                        };

                        // Setup the click event listeners: simply set the map to Chicago.
                        controlUI.addEventListener("click", () => {
                            var current_location = new google.maps.Marker({
                                position: my_location,
                                map,
                                icon: marker_icon,
                            });

                            infowindow.setPosition(my_location);
                            infowindow.setContent(
                                '<div class="default-result">Your location.</div>'
                            );
                            //infowindow.open(map);
                            map.panTo(my_location);
                        });
                    }

                    const centerControlDiv = document.createElement("div");
                    CenterControl(centerControlDiv, map);

                    centerControlDiv.index = 1;
                    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(
                        centerControlDiv
                    );

                    function handleLocationError(browserHasGeolocation, infowindow, pos, map) {
                        // Ensure all parameters are valid
                        if (!infowindow || !pos || !map) {
                            return;
                        }

                        try {
                            infowindow.setPosition(pos);

                            // Check if it's a secure context issue
                            var isSecureContext = window.isSecureContext || location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
                            var errorMessage = '';

                            if (!isSecureContext) {
                                errorMessage = "Geolocation requires HTTPS. Please use a secure connection.";
                            } else if (browserHasGeolocation) {
                                errorMessage = "Error: The Geolocation service failed.";
                            } else {
                                errorMessage = "Error: Your browser doesn't support geolocation.";
                            }

                            infowindow.setContent(errorMessage);
                            infowindow.open(map);
                        } catch (error) {
                            // Error in handleLocationError
                        }
                    }
                };

                if (!is_mobile) {
                    jobportal_hover_map_effects();
                }
            } else if (mapType == "openstreetmap") {

                var jobportal_osm_add_markers = function (props, maps) {
                    $(".maptype").remove();
                    $(map_html).insertAfter("#pac-input");

                    var osm_api = $("#maps").data("key");
                    var osm_level = $("#maps").data("level");
                    var osm_style = $("#maps").data("style");

                    var features_info = [];
                    var lng_args = [];
                    var lat_args = [];

                    $.each(props, function (i, prop) {
                        features_info.push({
                            type: "Feature",
                            geometry: {
                                type: "Point",
                                coordinates: [prop.lat, prop.lng],
                            },
                            properties: {
                                iconSize: [40, 40],
                                id: prop.id,
                                icon: prop.marker_icon,
                                company: prop.company,
                            },
                        });

                        lng_args.push(prop.lng);
                        lat_args.push(prop.lat);
                    });

                    var stores = {
                        type: "FeatureCollection",
                        features: features_info,
                    };

                    var sum_lng = 0;
                    for (var i = 0; i < lng_args.length; i++) {
                        sum_lng += parseInt(lng_args[i], 10);
                    }

                    var avg_lng = 0;

                    if (sum_lng / lng_args.length) {
                        avg_lng = sum_lng / lng_args.length;
                    }

                    var sum_lat = 0;
                    for (var i = 0; i < lat_args.length; i++) {
                        sum_lat += parseInt(lat_args[i], 10);
                    }

                    var avg_lat = 0;

                    if (sum_lat / lat_args.length) {
                        avg_lat = sum_lat / lat_args.length;
                    }

                    var container = L.DomUtil.get("maps");
                    if (container != null) {
                        container._leaflet_id = null;
                    }

                    $(".leaflet-map-pane").remove();
                    $(".leaflet-control-container").remove();

                    var osm_map = new L.map("maps");

                    osm_map.on("load", onMapLoad);

                    osm_map.setView([avg_lat, avg_lng], osm_level);

                    function onMapLoad() {
                        var titleLayer_id = "mapbox/" + osm_style;

                        L.tileLayer(
                            "https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=" +
                            osm_api,
                            {
                                attribution:
                                    'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                                id: titleLayer_id,
                                tileSize: 512,
                                zoomOffset: -1,
                                accessToken: osm_api,
                            }
                        ).addTo(osm_map);

                        /**
                         * Add all the things to the page:
                         * - The location listings on the side of the page
                         * - The markers onto the map
                         */
                        addMarkers();
                    }

                    function flyToStore(currentFeature) {
                        osm_map.flyTo(currentFeature.geometry.coordinates, osm_level);
                    }

                    /* This will let you use the .remove() function later on */
                    if (!("remove" in Element.prototype)) {
                        Element.prototype.remove = function () {
                            if (this.parentNode) {
                                this.parentNode.removeChild(this);
                            }
                        };
                    }

                    function addMarkers() {
                        /* For each feature in the GeoJSON object above: */
                        stores.features.forEach(function (marker) {
                            /* Create a div element for the marker. */
                            var el = document.createElement("div");
                            /* Assign a unique `id` to the marker. */
                            el.id = "marker-" + marker.properties.id;
                            /* Assign the `marker` class to each marker for styling. */
                            el.className = "marker";
                            el.style.backgroundImage = "url(" + marker.properties.icon + ")";
                            el.style.width = marker.properties.iconSize[0] + "px";
                            el.style.height = marker.properties.iconSize[1] + "px";
                            /**
                             * Create a marker using the div element
                             * defined above and add it to the map.
                             **/
                            properties: {
                            }

                            var icon = L.divIcon({
                                className: "marker-" + marker.properties.id,
                                html:
                                    '<div><img src="' +
                                    marker.properties.icon +
                                    '" alt=""></div>',
                                iconSize: [48, 48],
                            });

                            var markers = new L.marker(
                                [
                                    marker.geometry.coordinates[0],
                                    marker.geometry.coordinates[1],
                                ],
                                { icon: icon }
                            );

                            markers.addTo(osm_map);

                            if (map_effects == 'popup') {
                                markers.bindPopup(marker.properties.company);
                            } else {
                                markers.bindPopup();
                            }

                            el.addEventListener("click", function (e) {
                                /* Fly to the point */
                                flyToStore(marker);
                                /* Highlight listing in sidebar */
                                var activeItem = document.getElementsByClassName("active");
                                e.stopPropagation();
                                if (activeItem[0]) {
                                    activeItem[0].classList.remove("active");
                                }
                            });
                        });
                    }

                    if (!is_mobile) {
                        jobportal_hover_map_effects();
                    }

                };

                // End Openstreetmap
            } else {
                // Begin Mapbox

                var jobportal_mapbox_add_markers = function (props, map) {
                    var mapbox_api = $("#map").data("key");
                    var mapbox_level = $("#map").data("level");
                    var mapType = $("#map").data("type");
                    mapboxgl.accessToken = mapbox_api;
                    $(".mapboxgl-canary").remove();
                    $(".mapboxgl-canvas-container").remove();
                    $(".mapboxgl-control-container").remove();
                    var features_info = [];
                    var lng_args = [];
                    var lat_args = [];

                    $.each(props, function (i, prop) {
                        features_info.push({
                            type: "Feature",
                            geometry: {
                                type: "Point",
                                coordinates: [prop.lng, prop.lat],
                            },
                            properties: {
                                iconSize: [48, 48],
                                id: prop.id,
                                icon: prop.marker_icon,
                                company: prop.company,
                            },
                        });

                        lng_args.push(prop.lng);
                        lat_args.push(prop.lat);
                    });

                    var sum_lng = 0;
                    for (var i = 0; i < lng_args.length; i++) {
                        sum_lng += parseInt(lng_args[i], 10);
                    }

                    var avg_lng = 0;

                    if (sum_lng / lng_args.length) {
                        avg_lng = sum_lng / lng_args.length;
                    }

                    var sum_lat = 0;
                    for (var i = 0; i < lat_args.length; i++) {
                        sum_lat += parseInt(lat_args[i], 10);
                    }

                    var avg_lat = 0;

                    if (sum_lat / lat_args.length) {
                        avg_lat = sum_lat / lat_args.length;
                    }

                    var map = new mapboxgl.Map({
                        container: "map",
                        style: "mapbox://styles/mapbox/" + mapType,
                        zoom: mapbox_level,
                        center: [avg_lng, avg_lat],
                    });

                    map.addControl(new mapboxgl.NavigationControl());

                    var stores = {
                        type: "FeatureCollection",
                        features: features_info,
                    };

                    /**
                     * Wait until the map loads to make changes to the map.
                     */
                    map.on("load", function (e) {
                        /**
                         * This is where your '.addLayer()' used to be, instead
                         * add only the source without styling a layer
                         */
                        map.addLayer({
                            id: "locations",
                            type: "symbol",
                            /* Add a GeoJSON source containing company coordinates and information. */
                            source: {
                                type: "geojson",
                                data: stores,
                            },
                            layout: {
                                "icon-image": "",
                                "icon-allow-overlap": true,
                            },
                        });

                        /**
                         * Add all the things to the page:
                         * - The location listings on the side of the page
                         * - The markers onto the map
                         */
                        addMarkers();
                    });

                    function flyToStore(currentFeature) {
                        map.flyTo({
                            center: currentFeature.geometry.coordinates,
                            bearing: 0,
                            duration: 0,
                            speed: 0.2,
                            curve: 1,
                            easing: function (t) {
                                return t;
                            },
                        });
                    }

                    function createPopUp(currentFeature) {
                        var popUps = document.getElementsByClassName("mapboxgl-popup");
                        /** Check if there is already a popup on the map and if so, remove it */
                        if (popUps[0]) popUps[0].remove();

                        var popup = new mapboxgl.Popup({ closeOnClick: false })
                            .setLngLat(currentFeature.geometry.coordinates)
                            .setHTML(currentFeature.properties.company)
                            .addTo(map);
                    }

                    /* This will let you use the .remove() function later on */
                    if (!("remove" in Element.prototype)) {
                        Element.prototype.remove = function () {
                            if (this.parentNode) {
                                this.parentNode.removeChild(this);
                            }
                        };
                    }

                    map.on("click", function (e) {
                        /* Determine if a feature in the "locations" layer exists at that point. */
                        var features = map.queryRenderedFeatures(e.point, {
                            layers: ["locations"],
                        });

                        /* If yes, then: */
                        if (features.length) {
                            var clickedPoint = features[0];

                            /* Fly to the point */
                            flyToStore(clickedPoint);

                            /* Close all other popups and display popup for clicked store */
                            createPopUp(clickedPoint);
                        }
                    });

                    function addMarkers() {
                        /* For each feature in the GeoJSON object above: */
                        stores.features.forEach(function (marker) {
                            /* Create a div element for the marker. */
                            var el = document.createElement("div");
                            /* Assign a unique `id` to the marker. */
                            el.id = "marker-" + marker.properties.id;
                            /* Assign the `marker` class to each marker for styling. */
                            el.className = "marker";
                            el.style.backgroundImage = "url(" + marker.properties.icon + ")";
                            el.style.width = marker.properties.iconSize[0] + "px";
                            el.style.height = marker.properties.iconSize[1] + "px";
                            /**
                             * Create a marker using the div element
                             * defined above and add it to the map.
                             **/
                            new mapboxgl.Marker(el, { offset: [0, -23] })
                                .setLngLat(marker.geometry.coordinates)
                                .addTo(map);

                            el.addEventListener("click", function (e) {
                                /* Fly to the point */
                                flyToStore(marker);
                                /* Close all other popups and display popup for clicked store */
                                if (map_effects == 'popup') {
                                    createPopUp(marker);
                                }
                                /* Highlight listing in sidebar */
                                var activeItem = document.getElementsByClassName("active");
                                e.stopPropagation();
                                if (activeItem[0]) {
                                    activeItem[0].classList.remove("active");
                                }
                            });
                        });
                    }
                };

                if (!is_mobile) {
                    jobportal_hover_map_effects();
                }
                // End Mapbox
            }


            COMPANY.elements.display_clear();

            var type_pagination = $(".jobportal-pagination").attr("data-type");
            $(".area-company .jobportal-company-item").addClass("skeleton-loading");

            $.ajax({
                dataType: "json",
                url: ajax_url,
                data: {
                    action: "jobportal_company_archive_ajax",
                    paged: paged,
                    title: title,
                    item_amount: item_amount,
                    sort_by: sort_by,
                    current_term: current_term,
                    type_term: type_term,
                    size: size,
                    rating: rating,
                    range_min: range_min,
                    range_max: range_max,
                    founded: founded,
                    location: location,
                    location_country: location_country,
                    location_state: location_state,
                    location_city: location_city,
                    radius_cities: radius_cities,
                    categories: categories,
                    company_layout: company_layout,
                    has_map_val: has_map,
                },
                beforeSend: function () {
                    archive_company
                        .find(".jobportal-filter-search-map .jobportal-loading-effect")
                        .fadeIn();
                    if (
                        archive_company
                            .find(".form-company-top-filter .btn-top-filter")
                            .data("clicked")
                    ) {
                        archive_company.find(".btn-top-filter .btn-loading").fadeIn();
                    }
                    if (type_pagination == "loadmore") {
                        archive_company.find(".btn-loading").fadeIn();
                    }
                },
                success: function (data) {
                    archive_company.find(".btn-top-filter .btn-loading").fadeOut();
                    archive_company.find(".jobportal-filter-search-map .jobportal-loading-effect").fadeOut();
                    $(".area-company .jobportal-company-item").removeClass("skeleton-loading");

                    if (data.success === true) {
                        if (ajax_call == true) {
                            if (
                                data.pagination_type == "number" ||
                                pagination !== "loadmore"
                            ) {
                                archive_company.find(".area-company").html(data.company_html);
                                archive_company
                                    .find(".jobportal-pagination .pagination")
                                    .html(data.pagination);
                                archive_company.find(".result-count").html(data.count_post);
                            } else {
                                archive_company.find(".area-company").append(data.company_html);
                                if (data.hidden_pagination) {
                                    archive_company.find(".jobportal-pagination .pagination").html("");
                                }
                                archive_company.find(".btn-loading").fadeOut();
                                archive_company
                                    .find(".jobportal-pagination .pagination")
                                    .removeClass("active");
                            }
                        }
                    } else {
                        if (ajax_call == true) {
                            if (
                                data.pagination_type == "number" ||
                                pagination !== "loadmore"
                            ) {
                                archive_company
                                    .find(".area-company")
                                    .html(
                                        '<div class="jobportal-ajax-result">' + not_company + "</div>"
                                    );
                                archive_company.find(".result-count").html(data.count_post);
                                archive_company.find(".jobportal-pagination .pagination").html("");
                            } else {
                                archive_company.find(".area-company").append(data.company_html);
                                if (data.hidden_pagination) {
                                    $(".jobportal-pagination .pagination").html("");
                                }
                                archive_company
                                    .find(".jobportal-pagination .pagination")
                                    .removeClass("active");
                            }
                        }
                    }
                    if (data.tax_with_count) {
                        $('.jobportal-menu-filter li input + label span.count').text("(0)");
                        if (data.tax_with_count != 'not_found') {
                            $.each(data.tax_with_count, function (index, value) {
                                $('.jobportal-menu-filter li input[value="' + index + '"] + label span.count').text("(" + value + ")");
                            });
                        }
                    }
                    if (!is_mobile) {
                        jobportal_hover_map_effects();
                    }
                    if (has_map) {
                        if (mapType == "google_map") {
                            loadGoogleMapsAPI().then(function () {
                                jobportal_map = new google.maps.Map(
                                    document.getElementById("jobs-map-filter"),
                                    jobportal_search_map_option
                                );

                                if (google_map_style !== "") {
                                    var styles = JSON.parse(google_map_style);
                                    jobportal_map.setOptions({ styles: styles });
                                }

                                var mapPosition = new google.maps.LatLng(
                                    default_lat || "-37.9721047",
                                    default_lng || "144.7228153"
                                );
                                jobportal_map.setCenter(mapPosition);
                                jobportal_map.setZoom(parseInt(googlemap_default_zoom));
                                google.maps.event.addListener(
                                    jobportal_map,
                                    "tilesloaded",
                                    function () {
                                        $(".jobportal-filter-search-map .jobportal-loading-effect").fadeOut();
                                    }
                                );

                                markers.forEach(function (marker) {
                                    marker.setMap(null);
                                });

                                markers = [];
                                if (data.company && data.company.length > 0) {
                                    jobportal_add_markers(data.company, jobportal_map);
                                    jobportal_my_location(jobportal_map);
                                    if (markers.length > 0) {
                                        jobportal_map.fitBounds(
                                            markers.reduce(function (bounds, marker) {
                                                return bounds.extend(marker.getPosition());
                                            }, new google.maps.LatLngBounds())
                                        );
                                    }
                                }
                            });
                        } else if (mapType == "openstreetmap") {
                            jobportal_osm_add_markers(data.company, maps);
                        } else {
                            jobportal_mapbox_add_markers(data.company, map);
                        }
                    }
                    // Scroll to first item after pagination
                    if (pagination === "pagination") {
                        COMPANY.elements.scroll_to(".area-company .jobportal-company-item:first");
                    }
                },
            });
        },

        scroll_to: function (element) {
            var offset = $(element).offset().top;
            $("html, body").animate(
                {
                    scrollTop: offset - 100,
                },
                500
            );
        },
    };

    COMPANY.onReady = {
        init: function () {
            COMPANY.elements.init();
        },
    };

    COMPANY.onLoad = {
        init: function () {
        },
    };

    $(document).ready(function () {
        COMPANY.elements.init();
    });

    $(window).on('load', COMPANY.onLoad.init);
})(jQuery);
