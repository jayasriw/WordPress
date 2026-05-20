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

  var ajax_url = civi_template_vars.ajax_url,
    map_effects = civi_template_vars.map_effects,
    map_type = civi_template_vars.map_type,
    map_api_key = civi_template_vars.map_api_key,
    default_lat = civi_template_vars.default_lat,
    default_lng = civi_template_vars.default_lng,
    default_icon = civi_template_vars.marker_default_icon,
    item_amount = civi_candidate_archive_vars.item_amount,
    not_candidate = civi_candidate_archive_vars.not_candidate;

  var ajax_call = false;
  var menu_filter_wrap = $(".civi-menu-filter");
  var markers = [];
  //map
  var mapType = $(".maptype").data("maptype");
  var is_mobile = false;
  var has_map = "";
  var civi_map;
  var googleMapsLoaded = false;
  var infowindow; // Declare infowindow at higher scope

  if (mapType == "google_map") {
    var candidate_maps_filter = $("#jobs-map-filter");
  } else if (mapType == "openstreetmap") {
    var candidate_maps_filter = $("#maps");
  } else {
    var candidate_maps_filter = $("#map");
  }

  if (candidate_maps_filter.length) {
    has_map = "yes";
  }

  if (
    /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
      navigator.userAgent
    )
  ) {
    is_mobile = true;
  }

  var checkGoogleMapsLoaded = function () {
    if (typeof google !== 'undefined' && google.maps && google.maps.InfoWindow) {
      googleMapsLoaded = true;
      return true;
    }
    return false;
  };

  var waitForGoogleMaps = function (resolve) {
    var checkInterval = setInterval(function () {
      if (checkGoogleMapsLoaded()) {
        clearInterval(checkInterval);
        resolve();
      }
    }, 100);
  };

  var loadGoogleMapsAPI = function () {
    if (checkGoogleMapsLoaded()) {
      return Promise.resolve();
    }
    return new Promise((resolve, reject) => {
      if (document.querySelector('script[src*="maps.googleapis.com"]')) {
        waitForGoogleMaps(resolve);
      } else {
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
  };

  var civi_hover_map_effects = function () {
    if (map_effects !== "" && has_map) {
      $(".map-event .area-candidates .civi-candidates-item").each(function () {
        var title = $(this).find(".add-follow-candidate").data("candidate-id");

        if (mapType == "google_map") {
          $(this).on("mouseenter", function () {
            if (map_effects == "popup") {
              $('div[title="marker' + title + '"]')
                .trigger("click")
                .css("z-index", "2");
            } else if (map_effects == "shine") {
              $('div[title="marker' + title + '"]')
                .trigger("click")
                .addClass("mouseenter");
            }
          });

          $(this).on("mouseleave", function () {
            if (map_effects == "popup") {
              $('div[title="marker' + title + '"]').css("z-index", "0");
              if (typeof infowindow !== 'undefined' && infowindow) {
                infowindow.open(null, null);
              }
            } else if (map_effects == "shine") {
              $('div[title="marker' + title + '"]')
                .trigger("click")
                .removeClass("mouseenter");
            }
          });
        } else if (mapType == "openstreetmap") {
          $(this).on("mouseenter", function () {
            if (map_effects == "popup") {
              $(".marker-" + title)
                .trigger("click")
                .css("z-index", "2");
            } else if (map_effects == "shine") {
              $(".marker-" + title)
                .trigger("click")
                .addClass("mouseenter");
            }
          });

          $(this).on("mouseleave", function () {
            if (map_effects == "popup") {
              $(".marker-" + title).css("z-index", "0");
              $(".leaflet-popup-close-button").trigger("click");
            } else if (map_effects == "shine") {
              $(".marker-" + title)
                .trigger("click")
                .removeClass("mouseenter");
            }
          });
        } else {
          $(this).on("mouseenter", function () {
            if (map_effects == "popup") {
              $("#marker-" + title)
                .trigger("click")
                .css("z-index", "2");
            } else if (map_effects == "shine") {
              $("#marker-" + title)
                .trigger("click")
                .addClass("mouseenter");
            }
          });

          $(this).on("mouseleave", function () {
            if (map_effects == "popup") {
              $(".marker-" + title).css("z-index", "0");
              $(".mapboxgl-popup-close-button").trigger("click");
            } else if (map_effects == "shine") {
              $("#marker-" + title)
                .trigger("click")
                .removeClass("mouseenter");
            }
          });
        }
      });
    }
  };

  CANDIDATE.elements = {
    init: function () {
      this.candidate_layout();
      this.pagination();
      this.display_clear();
      this.filter_clear_top();
      this.get_city_from_result();
      this.search_cate_location();
      this.filter_clear();

      $(".civi-menu-filter").on("input", "input.input-control", function () {
        $(".civi-pagination").find('input[name="paged"]').val(1);
        $(".form-candidate-top-filter .btn-top-filter").removeData("clicked");
        ajax_call = true;
        CANDIDATE.elements.ajax_load(ajax_call);
      });

      $(".archive-layout select.sort-by").on("change", function () {
        $(".civi-pagination").find('input[name="paged"]').val(1);
        $(".form-candidate-top-filter .btn-top-filter").removeData("clicked");
        ajax_call = true;
        CANDIDATE.elements.ajax_load(ajax_call);
      });

      $(".form-candidate-top-filter .btn-top-filter").on("click", function (e) {
        e.preventDefault();
        $(".civi-pagination").find('input[name="paged"]').val(1);
        $(this).data("clicked", true);
        ajax_call = true;
        CANDIDATE.elements.ajax_load(ajax_call);
      });

      if (candidate_maps_filter.length > 0) {
        loadGoogleMapsAPI().then(function () {
          CANDIDATE.elements.ajax_load();
        });
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
        CANDIDATE.elements.ajax_load(ajax_call);
      });

      $(".locations-filter select").on("change", function () {
        ajax_call = true;
        CANDIDATE.elements.ajax_load(ajax_call);
      });
    },

    pagination: function () {
      var type_pagination = $(".civi-pagination").attr("data-type");

      $("body").on(
        "click",
        ".civi-pagination.ajax-call a.page-numbers",
        function (e) {
          e.preventDefault();
          $(".civi-pagination .pagination").addClass("active");
          $(".civi-pagination li .page-numbers").removeClass("current");
          $(this).addClass("current");
          var paged = $(this).text();
          var current_page = 1;
          if ($(".civi-pagination").find('input[name="paged"]').val()) {
            current_page = $(".civi-pagination")
              .find('input[name="paged"]')
              .val();
          }
          if ($(this).hasClass("next")) {
            paged = parseInt(current_page) + 1;
          }
          if ($(this).hasClass("prev")) {
            paged = parseInt(current_page) - 1;
          }
          $(".civi-pagination").find('input[name="paged"]').val(paged);

          if (type_pagination !== "loadpage") {
            e.preventDefault();
            ajax_call = true;
            if (type_pagination == "number") {
              CANDIDATE.elements.ajax_load(ajax_call, "pagination");
            } else {
              CANDIDATE.elements.ajax_load(ajax_call, "loadmore");
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

    candidate_layout: function () {
      $(".candidate-layout a").on("click", function (event) {
        event.preventDefault();
        var layout = $(this).attr("data-layout");
        var type_pagination = $(".civi-pagination").attr("data-type");
        if (type_pagination == "loadmore") {
          $(".civi-pagination").find('input[name="paged"]').val(1);
        }
        $(this).closest(".candidate-layout").find("a").removeClass("active");
        $(this).addClass("active");
        CANDIDATE.elements.removeClassStartingWith(
          $(".archive-layout>.inner-content"),
          "layout-"
        );
        $(this).closest(".inner-content").addClass(layout);

        $(".form-candidate-top-filter .btn-top-filter").removeData("clicked");

        $(".area-candidates .civi-candidates-item").each(function () {
          CANDIDATE.elements.removeClassStartingWith($(this), "layout-");
          $(this).addClass(layout);
        });

        ajax_call = true;
        CANDIDATE.elements.ajax_load(ajax_call);
      });
    },

    display_clear: function () {
      var archive_candidate = $(".archive-candidate");
      var has_active_filters = (
        $(".civi-menu-filter ul.filter-control li.active").length > 0 ||
        $('.civi-menu-filter input[type="checkbox"]:checked').length > 0 ||
        ($("select.civi-select-country").val() && $("select.civi-select-country").val() !== "")
      );
      $(".civi-menu-filter .entry-filter").removeClass("open");
      $('.civi-menu-filter input[type="checkbox"]:checked').closest(".entry-filter").addClass("open");
      if ($("select.civi-select-country").val() && $("select.civi-select-country").val() !== "") {
        $("select.civi-select-country").closest(".entry-filter").addClass("open");
      }
      if (has_active_filters) {
        $(".civi-nav-filter").addClass("active");
        archive_candidate.find(".civi-clear-filter").show();
      } else {
        $(".civi-nav-filter").removeClass("active");
        archive_candidate.find(".civi-clear-filter").hide();
      }
    },

    filter_clear_top: function () {
      $(".civi-clear-top-filter").on("click", function () {
        $('.form-candidate-top-filter input[name="candidate_filter_search').val(
          ""
        );
        $(
          '.form-candidate-top-filter input[name="candidate-search-location"]'
        ).val("");
        $(".form-candidate-top-filter .civi-select2").val("");
        $(".form-candidate-top-filter .civi-select2").select2("destroy");
        $(".form-candidate-top-filter .civi-select2").each(function () {
          var option = $(this).find("option");
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

        $(".select2.select2-container").on("click", function () {
          var options = $(this).prev().find("option");
          options.each(function () {
            var option_val = $(this).val();
            var level = $(this).attr("data-level");
            $('.select2-results li[id$="' + option_val + '"]').attr(
              "data-level",
              level
            );
          });
        });
        $(".civi-form-location .icon-arrow i").on("click", function () {
          var options = $(this)
            .closest(".civi-form-location")
            .find("select.civi-select2 option");
          options.each(function () {
            var option_val = $(this).val();
            var level = $(this).attr("data-level");
            $('.select2-results li[id$="' + option_val + '"]').attr(
              "data-level",
              level
            );
          });
        });
        GLF.element.clean_archive_url();
        ajax_call = true;
        CANDIDATE.elements.ajax_load(ajax_call);
      });
    },

    filter_clear: function () {
      $(".civi-clear-filter").on("click", function () {
        $(".civi-menu-filter ul.filter-control li").removeClass("active");
        $('.civi-menu-filter input[type="checkbox"]').prop("checked", false);
        $(".civi-menu-filter .civi-select2").val("");
        $(".civi-menu-filter .civi-select2").select2("destroy");
        $(".civi-menu-filter .civi-select2").select2();
        ajax_call = true;
        CANDIDATE.elements.ajax_load(ajax_call);
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
      const form = $(".archive-layout .civi-form-location");
      if (!form.length) return;

      const input = form.find(".archive-search-location");
      const field_select = form.find(".civi-select2");

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

      $("body").on("mousedown", ".civi-form-location .icon-arrow i", function (e) {
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

      // GeoLocation
      const locationBtn = form.find(".icon-location svg");
      if (!locationBtn.length) return;

      locationBtn.on("click", () => {
        const map_type = typeof civi_template_vars !== 'undefined' ? civi_template_vars.map_type : 'mapbox';
        const map_api_key = typeof civi_template_vars !== 'undefined' ? civi_template_vars.map_api_key : '';

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
                  const city = CANDIDATE.elements.get_city_from_result(result, map_type);
                  if (city) {
                    input.val(city).trigger("change");
                  } else {
                    console.warn("City not found from result.");
                  }
                },
                error: (error) => {
                  console.error("AJAX error:", error);
                },
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

    ajax_load: function (ajax_call, pagination) {
      var title,
        sort_by,
        categories,
        location,
        rating,
        current_term,
        type_term,
        candidate_layout,
        candidate_yoe_id,
        candidate_qualification_id,
        candidate_ages_id,
        candidate_skills_id,
        candidate_languages_id,
        search_fields_sidebar,
        location_country,
        location_state,
        location_city,
        radius_cities,
        candidate_gender;
      var paged = 1;

      paged = $(".civi-pagination").find('input[name="paged"]').val();
      title = $('input[name="candidate_filter_search"]').val();
      current_term = $('input[name="current_term"]').val();
      type_term = $('input[name="type_term"]').val();
      candidate_layout = $(".candidate-layout a.active").attr("data-layout");
      sort_by = menu_filter_wrap
        .find(".sort-by.filter-control li.active a")
        .data("sort");
      var select_sort = $('.archive-layout select[name="sort_by"]').val();
      if (select_sort) {
        sort_by = select_sort;
      }

      search_fields_sidebar = $('input[name="search_fields_sidebar"]').val();
      var result_fields = $.parseJSON(search_fields_sidebar);

      location = $('input[name="candidate-search-location"]').val();
      location_country = $("select.civi-select-country").val();
      location_state = $("select.civi-select-state").val();
      location_city = $("select.civi-select-city").val();
      radius_cities = $(".civi-form-location")
        .find('input[name="candidate_number_radius"]')
        .val();

      if (result_fields.hasOwnProperty("candidate_categories")) {
        categories = $('input[name="candidate_categories_id[]"]:checked')
          .map(function () {
            return $(this).val();
          })
          .get();
      } else {
        categories = $('select[name="candidate_categories"]').val();
      }

      if (result_fields.hasOwnProperty("candidate_rating")) {
        rating = $('input[name="candidate_rating[]"]:checked')
          .map(function () {
            return $(this).val();
          })
          .get();
      } else {
        rating = $('select[name="candidate_rating"]').val();
      }

      if (result_fields.hasOwnProperty("candidate_yoe")) {
        candidate_yoe_id = $('input[name="candidate_yoe_id[]"]:checked')
          .map(function () {
            return $(this).val();
          })
          .get();
      } else {
        candidate_yoe_id = $('select[name="candidate_yoe"]').val();
      }

      if (result_fields.hasOwnProperty("candidate_qualification")) {
        candidate_qualification_id = $(
          'input[name="candidate_qualification_id[]"]:checked'
        )
          .map(function () {
            return $(this).val();
          })
          .get();
      } else {
        candidate_qualification_id = $(
          'select[name="candidate_qualification"]'
        ).val();
      }

      if (result_fields.hasOwnProperty("candidate_ages")) {
        candidate_ages_id = $('input[name="candidate_ages_id[]"]:checked')
          .map(function () {
            return $(this).val();
          })
          .get();
      } else {
        candidate_ages_id = $('select[name="candidate_ages"]').val();
      }

      if (result_fields.hasOwnProperty("candidate_skills")) {
        candidate_skills_id = $('input[name="candidate_skills_id[]"]:checked')
          .map(function () {
            return $(this).val();
          })
          .get();
      } else {
        candidate_skills_id = $('select[name="candidate_skills"]').val();
      }

      if (result_fields.hasOwnProperty("candidate_languages")) {
        candidate_languages_id = $(
          'input[name="candidate_languages_id[]"]:checked'
        )
          .map(function () {
            return $(this).val();
          })
          .get();
      } else {
        candidate_languages_id = $('select[name="candidate_languages"]').val();
      }

      if (result_fields.hasOwnProperty("candidate_gender")) {
        candidate_gender = $('input[name="candidate_gender_id[]"]:checked')
          .map(function () {
            return $(this).val();
          })
          .get();
      } else {
        candidate_gender = $('select[name="candidate_gender"]').val();
      }

      //Map
      var map_html = $(".maptype").clone();
      if (mapType == "google_map") {
        var marker_cluster = null,
          googlemap_default_zoom = civi_template_vars.googlemap_default_zoom,
          not_found = civi_template_vars.not_found,
          clusterIcon = civi_template_vars.clusterIcon,
          google_map_style = civi_template_vars.google_map_style,
          google_map_type = civi_template_vars.google_map_type,
          pin_cluster_enable = civi_template_vars.pin_cluster_enable;
        // Initialize infowindow if not already initialized
        if (!infowindow && typeof google !== 'undefined' && google.maps && google.maps.InfoWindow) {
          infowindow = new google.maps.InfoWindow({
            maxWidth: 370,
          });
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
          var civi_search_map_option = {
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

        var civi_add_markers = function (props, map) {
          $.each(props, function (i, prop) {
            var latlng = new google.maps.LatLng(prop.lat, prop.lng),
              marker_url = prop.marker_icon,
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
              url: ".candidate-" + prop.id,
              map: map,
              candidate: prop.candidate,
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
            contentString.className = "civi-marker";
            contentString.innerHTML = prop.candidate;

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
              $(".area-candidate .civi-candidate-item").removeClass(
                "highlight"
              );
              if (
                elem.length > 0 &&
                click_marker &&
                $(".archive-candidate.map-event").length > 0
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
        };

        var civi_my_location = function (map) {
          // Ensure Google Maps API is loaded before proceeding
          if (!checkGoogleMapsLoaded()) {
            loadGoogleMapsAPI().then(function () {
              civi_my_location(map);
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

            controlUI.addEventListener("click", () => {
              var current_location = new google.maps.Marker({
                position: my_location,
                map,
                icon: default_icon,
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
          civi_hover_map_effects();
        }
      } else if (mapType == "openstreetmap") {
        var civi_osm_add_markers = function (props, maps) {
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
                candidate: prop.candidate,
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

              if (map_effects == "popup") {
                markers.bindPopup(marker.properties.candidate);
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
            civi_hover_map_effects();
          }
        };

        // End Openstreetmap
      } else {
        // Begin Mapbox

        var civi_mapbox_add_markers = function (props, map) {
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
                candidate: prop.candidate,
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
              /* Add a GeoJSON source containing candidate coordinates and information. */
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
              .setHTML(currentFeature.properties.candidate)
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
                if (map_effects == "popup") {
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
          civi_hover_map_effects();
        }
        // End Mapbox
      }

      CANDIDATE.elements.display_clear();
      var type_pagination = $(".civi-pagination").attr("data-type");
      $(".area-candidates .civi-candidates-item").addClass("skeleton-loading");

      $.ajax({
        dataType: "json",
        url: ajax_url,
        data: {
          action: "civi_candidate_archive_ajax",
          paged: paged,
          title: title,
          item_amount: item_amount,
          sort_by: sort_by,
          current_term: current_term,
          type_term: type_term,
          rating: rating,
          location: location,
          location_country: location_country,
          location_state: location_state,
          location_city: location_city,
          radius_cities: radius_cities,
          categories: categories,
          candidate_layout: candidate_layout,
          candidate_yoe_id: candidate_yoe_id,
          candidate_qualification_id: candidate_qualification_id,
          candidate_ages_id: candidate_ages_id,
          candidate_skills_id: candidate_skills_id,
          candidate_languages_id: candidate_languages_id,
          candidate_gender: candidate_gender,
          has_map_val: has_map,
        },
        beforeSend: function () {
          $(".civi-filter-search-map .civi-loading-effect").fadeIn();
          if ($(".form-candidate-top-filter .btn-top-filter").data("clicked")) {
            $(".btn-top-filter .btn-loading").fadeIn();
          }
        },
        success: function (data) {
          $(".btn-top-filter .btn-loading").fadeOut();
          $(".civi-filter-search-map .civi-loading-effect").fadeOut();
          $(".area-candidates .civi-candidates-item").removeClass(
            "skeleton-loading"
          );
          if (data.success === true) {
            if (ajax_call == true) {
              if (
                data.pagination_type == "number" ||
                pagination !== "loadmore"
              ) {
                $(".area-candidates").html(data.candidate_html);
                $(".filter-neighborhood").html(data.filter_html);
                $(".civi-pagination .pagination").html(data.pagination);
                $(".archive-layout .result-count").html(data.count_post);
              } else {
                $(".area-candidates").append(data.candidate_html);
                $(".filter-neighborhood").html(data.filter_html);
                if (data.hidden_pagination) {
                  $(".civi-pagination .pagination").html("");
                }
                $(".civi-pagination .pagination").removeClass("active");
              }
            }
          } else {
            if (ajax_call == true) {
              if (
                data.pagination_type == "number" ||
                pagination !== "loadmore"
              ) {
                $(".area-candidates").html(
                  '<div class="civi-ajax-result">' + not_candidate + "</div>"
                );
                $(".archive-layout .result-count").html(data.count_post);
                $(".civi-pagination .pagination").html("");
              } else {
                $(".area-candidates").append(data.candidate_html);
                if (data.hidden_pagination) {
                  $(".civi-pagination .pagination").html("");
                }
                $(".civi-pagination .pagination").removeClass("active");
              }
            }
          }
          if (data.tax_with_count) {
            $(".civi-menu-filter li input + label span.count").text("(0)");
            if (data.tax_with_count != "not_found") {
              $.each(data.tax_with_count, function (index, value) {
                $(
                  '.civi-menu-filter li input[value="' +
                  index +
                  '"] + label span.count'
                ).text("(" + value + ")");
              });
            }
          }
          if (!is_mobile) {
            civi_hover_map_effects();
          }
          if (has_map) {
            loadGoogleMapsAPI().then(function () {
              if (mapType == "google_map") {
                civi_map = new google.maps.Map(
                  document.getElementById("jobs-map-filter"),
                  civi_search_map_option
                );

                if (google_map_style !== "") {
                  var styles = JSON.parse(google_map_style);
                  civi_map.setOptions({ styles: styles });
                }

                var mapPosition = new google.maps.LatLng(
                  default_lat || "-37.9721047",
                  default_lng || "144.7228153"
                );
                civi_map.setCenter(mapPosition);
                civi_map.setZoom(parseInt(googlemap_default_zoom));
                google.maps.event.addListener(
                  civi_map,
                  "tilesloaded",
                  function () {
                    $(".civi-filter-search-map .civi-loading-effect").fadeOut();
                  }
                );

                markers.forEach(function (marker) {
                  marker.setMap(null);
                });

                markers = [];
                civi_add_markers(data.candidate, civi_map);
                civi_my_location(civi_map);
                civi_map.fitBounds(
                  markers.reduce(function (bounds, marker) {
                    return bounds.extend(marker.getPosition());
                  }, new google.maps.LatLngBounds())
                );
              } else if (mapType == "openstreetmap") {
                civi_osm_add_markers(data.candidate, maps);
              } else {
                civi_mapbox_add_markers(data.candidate, map);
              }
            });
          }
          // Scroll to first item after pagination
          if (pagination === "pagination") {
            CANDIDATE.elements.scroll_to(".area-candidates .civi-candidates-item:first");
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

  CANDIDATE.onReady = {
    init: function () {
      CANDIDATE.elements.init();
    },
  };

  CANDIDATE.onLoad = {
    init: function () { },
  };

  $(document).ready(function () {
    CANDIDATE.elements.init();
  });

  $(window).on('load', CANDIDATE.onLoad.init);
})(jQuery);
