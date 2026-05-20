(function ($) {
  "use strict";
  function get_city_from_result(result, map_type) {
    let city = "";

    if (map_type === "google_map") {
      if (result.status === "OK" && result.results.length > 0) {
        const components = result.results[0].address_components;
        const types_priority = [
          "locality",
          "administrative_area_level_1",
          "administrative_area_level_2",
          "sublocality",
        ];

        for (const type of types_priority) {
          const comp = components.find((c) => c.types.includes(type));
          if (comp) {
            city = comp.long_name;
            break;
          }
        }
      }
    } else if (map_type === "mapbox") {
      const features = result.features || [];
      const cityFeature =
        features.find((f) => f.place_type.includes("place")) ||
        features.find((f) => f.place_type.includes("region")) ||
        features.find((f) => f.place_type.includes("district"));
      city = cityFeature?.text || "";
    } else if (map_type === "openstreetmap") {
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
  }

  const HorizontalSearchHandler = function ($scope, $) {
    const map_api_key = civi_template_vars.map_api_key;
    const map_type = civi_template_vars.map_type;

    const search_form = $scope.find(".civi-form-location"),
      input = search_form.find(".input-search-location"),
      archive_input = search_form.find(".archive-search-location"),
      field_select = search_form.find(".civi-select2");

    if (!search_form.length) return;

    function fixDropdownPosition() {
      setTimeout(function() {
        const formGroup = search_form.closest('.form-group');
        if (!formGroup.length) return;

        const inputField = archive_input.length ? archive_input : input;
        const formGroupWidth = formGroup.outerWidth();
        const inputWidth = inputField.outerWidth();
        const targetWidth = formGroupWidth > 0 ? formGroupWidth : (inputWidth > 0 ? inputWidth : 300);
        
        const dropdown = $('.select2-dropdown:visible').last();
        if (!dropdown.length) return;

        const dropdownParent = dropdown.parent();
        const inputOffset = inputField.offset();
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

    if (archive_input.length && !archive_input.hasClass('civi-ajax-ui')) {
      const available = archive_input.data("key");
      if (available) {
        archive_input.autocomplete({
          source: available,
          minLength: 0,
          autoFocus: true,
          focus: true
        }).focus(function() {
          $(this).data("uiAutocomplete").search($(this).val());
          const select2_container = search_form.find(".select2.select2-container");
          if (!select2_container.hasClass("select2-container--open")) {
            field_select.select2("open");
          }
        });
      }
    }

    $("body").on("mousedown", ".civi-form-location .icon-arrow i", function (e) {
      e.preventDefault();
      const select2_container = search_form.find(".select2.select2-container");
      if (select2_container.hasClass("select2-container--open")) {
        field_select.select2("close");
      } else {
        const currentInputValue = archive_input.length ? archive_input.val() : input.val();
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

    // Geo Location
    const locationBtn = search_form.find(".icon-location svg");
    if (!locationBtn.length) return;

    locationBtn.on("click", () => {
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
                const city = typeof get_city_from_result === "function"
                  ? get_city_from_result(result, map_type)
                  : "";

                if (city) {
                  input.val(city).trigger("change");
                  if (archive_input.length) {
                    archive_input.val(city).trigger("change");
                  }
                } else {
                  console.warn("City not found from geocoding result.");
                }
              },
              error: (error) => {
                console.error("Geocoding AJAX error:", error);
              },
            });
          },
          (error) => {
            console.error("Geolocation error:", error.message);
            if (error.code === error.PERMISSION_DENIED) {
              alert("Please allow location access to use this feature.");
            }
          },
          {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0,
          }
        );
      } else {
        alert("Geolocation is not supported by your browser");
      }
    });
  };

  // Elementor hook
  $(window).on("elementor/frontend/init", function () {
    elementorFrontend.hooks.addAction(
      "frontend/element_ready/civi-search-horizontal.default",
      HorizontalSearchHandler
    );
    elementorFrontend.hooks.addAction(
      "frontend/element_ready/civi-search-vertical.default",
      HorizontalSearchHandler
    );
  });
})(jQuery);
