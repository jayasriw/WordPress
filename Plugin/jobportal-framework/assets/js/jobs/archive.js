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

	// Cache for circular marker icons to prevent memory exhaustion on iOS
	var markerIconCache = {};

	// Function to create circular marker icon
	function createCircularMarkerIcon(imageUrl, size = 40) {
		// Check cache first to avoid regenerating icons
		var cacheKey = imageUrl + '_' + size;
		if (markerIconCache[cacheKey]) {
			return Promise.resolve(markerIconCache[cacheKey]);
		}

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

				// Add border
				ctx.restore();
				ctx.beginPath();
				ctx.arc(size / 2, size / 2, size / 2 - 1.5, 0, Math.PI * 2);
				ctx.strokeStyle = '#EEEEEE';
				ctx.lineWidth = 0;
				ctx.stroke();

				// Convert to data URL and cache it
			var dataUrl = canvas.toDataURL();
			markerIconCache[cacheKey] = dataUrl;
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
		item_amount = jobportal_template_vars.item_amount,
		map_effects = jobportal_template_vars.map_effects,
		default_lat = jobportal_template_vars.default_lat,
		default_lng = jobportal_template_vars.default_lng,
		default_icon = jobportal_template_vars.marker_default_icon,
		map_api_key = jobportal_template_vars.map_api_key,
		not_jobs = jobportal_template_vars.not_jobs;

	var markers = [];
	var jobportal_map;
	var jobs_maps_filter;
	var menu_filter_wrap = $(".jobportal-menu-filter");
	var googleMapsLoaded = false;

	// Only take visible one
	menu_filter_wrap.each(function () {
		if ($(this).closest(".archive-filter").is(":visible")) {
			menu_filter_wrap = $(this);
		}
	});

	var mapType = $(".maptype").data("maptype");
	if (mapType == "google_map") {
		jobs_maps_filter = $("#jobs-map-filter");
	} else if (mapType == "openstreetmap") {
		jobs_maps_filter = $("#maps");
	} else {
		jobs_maps_filter = $("#map");
	}
	var has_map = "";

	if (jobs_maps_filter.length) {
		has_map = "yes";
	}
	var ajax_call = false;
	var is_mobile = false;
	var is_clearing_filter = false;
	if (
		/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
			navigator.userAgent
		)
	) {
		is_mobile = true;
	}

	// Helper functions for Google Maps asynchronous loading
	function checkGoogleMapsLoaded() {
		return typeof google !== 'undefined' && typeof google.maps !== 'undefined';
	}

	function waitForGoogleMaps() {
		return new Promise((resolve) => {
			if (checkGoogleMapsLoaded()) {
				resolve();
			} else {
				const checkInterval = setInterval(() => {
					if (checkGoogleMapsLoaded()) {
						clearInterval(checkInterval);
						resolve();
					}
				}, 100);
			}
		});
	}

	function loadGoogleMapsAPI() {
		if (googleMapsLoaded) {
			return Promise.resolve();
		}

		return new Promise((resolve) => {
			if (checkGoogleMapsLoaded()) {
				googleMapsLoaded = true;
				resolve();
			} else {
				waitForGoogleMaps().then(() => {
					googleMapsLoaded = true;
					resolve();
				});
			}
		});
	}

	var jobportal_hover_map_effects = function () {
		if (map_effects !== "" && has_map) {
			$(".map-event .area-jobs .jobportal-jobs-item").each(function (i) {
				var title = $(this).find(".btn-add-to-wishlist").data("jobs-id");

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
							if (typeof infowindow !== 'undefined') {
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

	function debounce(func, wait) {
		let timeout;
		return function () {
			const context = this, args = arguments;
			clearTimeout(timeout);
			timeout = setTimeout(function () {
				func.apply(context, args);
			}, wait);
		};
	}

	JOBS.elements = {
		init: function () {
			this.waypoints();
			this.jobs_layout();
			this.slider_range_salary();
			this.pagination();
			this.filter_single();
			this.filter_clear_top();
			this.filter_clear();
			this.display_clear();
			this.get_city_from_result();
			this.search_cate_location();
			this.preview_job();
			this.preview_job_tab();
			this.view_phone_number();

			if ($(window).width() > 992) {
				$(".btn-canvas-filter.hidden-md-up").remove();
				$("select.hidden-md-up").remove();
			}

			if (
				typeof jobs_maps_filter !== "undefined" &&
				jobs_maps_filter.length > 0
			) {
				JOBS.elements.ajax_load();
			}

			// Unified debounce function for all filter-related AJAX calls
			const triggerAjax = debounce(function () {
				if (window.is_clearing_filter) return;
				$(".jobportal-pagination").find('input[name="paged"]').val(1);
				$(".form-jobs-top-filter .btn-top-filter").removeData("clicked");
				ajax_call = true;
				JOBS.elements.ajax_load(ajax_call);
			}, 600);

			// Text, textarea, select, checkbox, radio, and salary slider events
			menu_filter_wrap.on(
				"input keyup change",
				"input[type='text'], textarea, select, input[type='checkbox'], input[type='radio']",
				triggerAjax
			);

			// Salary range slider
			if (typeof JOBS.elements.slider_range_salary === 'function') {
				$("#slider-range-salary").on("slidechange", triggerAjax);
			}

			// Top sort dropdown
			$(".archive-jobs select.sort-by").on("change", triggerAjax);

			// Salary min/max inputs
			menu_filter_wrap.find('input[name="jobs_filter_salary_min"], input[name="jobs_filter_salary_max"]').on("keyup", triggerAjax);

			// Job rate and posting date filters
			menu_filter_wrap.find('select[name="jobs_filter_rate"], select[name="jobs_posting_date"]').on("change", triggerAjax);

			// Top filter button
			$(".form-jobs-top-filter .btn-top-filter").on("click", function (e) {
				e.preventDefault();
				triggerAjax();
			});

			// Search input handling
			let searchTimer = null;
			$(".jobportal-ajax-search").on("input keyup", "input", function () {
				var $this = $(this);
				clearTimeout(searchTimer);
				if ($this.val()) {
					searchTimer = setTimeout(function () {
						if ($this.is(":focus")) {
							if ($this.attr("name") == "s") {
								JOBS.elements.ajax_search($this);
							}
							if ($this.attr("name") == "jobs-location-top") {
								var $input = $this
									.closest(".jobportal-ajax-search")
									.find('input[name="s"]');
								JOBS.elements.ajax_search($input, "hide");
								JOBS.elements.ajax_search_location($this, "show");
							}
						}
					}, 200);
				}
			});

			$(".jobportal-ajax-search").on("blur", "input", function () {
				clearTimeout(searchTimer);
				setTimeout(function () {
					$(".form-field .area-result").hide();
					$(".form-field .focus-result").show();
				}, 150);
			});

			$(".jobportal-ajax-search").on("focus", "input", function () {
				var $this = $(this);
				$(".form-field .area-result").hide();
				if ($this.val()) {
					$this.closest(".area-search").find(".focus-result").hide();
					$this.closest(".form-field").find(".area-result").show();
				} else {
					$this.closest(".form-field").find(".focus-result").show();
					$this.closest(".form-field").find(".area-result").hide();
				}
			});

			$("body").on("click", ".jobportal-filter-search-map .btn-close", function (e) {
				e.preventDefault();
				$("body").css("overflow", "inherit");
				$(".jobportal-filter-search-map").fadeOut();
				ajax_call = false;
			});

			$('.btn-hide-map input[type="checkbox"]').on("change", function () {
				var elem = $(".archive-layout .inner-content");
				var ltf = $(".layout-top-filter .nav-bar");
				if ($(this).is(":checked")) {
					$("input[value='hide_map']").prop("checked", false);
				} else {
					$("input[value='hide_map']").prop("checked", true);
				}
				if (elem.hasClass("has-map")) {
					elem.removeClass("has-map").addClass("no-map");
					ltf.removeClass("has-map").addClass("no-map");
				} else {
					elem.removeClass("no-map").addClass("has-map");
					ltf.removeClass("no-map").addClass("has-map");
				}
				triggerAjax();
			});

			$(".locations-filter select").on("change", triggerAjax);

			$(".toggle-select").on("click", ".toggle-show", function () {
				$(this).closest(".toggle-select").find(".toggle-list").slideToggle();
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
			const form = $(".archive-layout .jobportal-form-location"),
				input = form.find(".archive-search-location"),
				field_select = form.find(".jobportal-select2");

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

			// Update input when selecting location
			field_select.on("select2:select", function (e) {
				const data = e.params.data;
				input.val(data.text).trigger("change");
			});

			// Handle geolocation click
			const locationBtn = form.find(".icon-location svg");

			locationBtn.on("click", () => {
				const mapType = typeof jobportal_template_vars !== 'undefined' ? jobportal_template_vars.map_type : 'mapbox';
				const map_api_key = typeof jobportal_template_vars !== 'undefined' ? jobportal_template_vars.map_api_key : '';

				if ("geolocation" in navigator) {
					navigator.geolocation.getCurrentPosition(
						(position) => {
							const latitude = position.coords.latitude;
							const longitude = position.coords.longitude;

							let url = "";
							if (mapType === "google_map") {
								url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${map_api_key}`;
							} else if (mapType === "openstreetmap") {
								url = `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=jsonv2`;
							} else {
								url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${longitude},${latitude}.json?access_token=${map_api_key}`;
							}

							$.ajax({
								url: url,
								type: "GET",
								success: (result) => {
									const city = JOBS.elements.get_city_from_result(result, mapType);

									if (city) {
										input.val(city).trigger("change");
									}
								},
								error: (error) => {
									// Geocoding API error
								},
							});
						},
						(error) => {
							// Geolocation error
						},
						{
							enableHighAccuracy: true,
							timeout: 10000,
							maximumAge: 0,
						}
					);
				} else {
					// Browser does not support geolocation
				}
			});
		},

		waypoints: function () {
			var $elem = $(".offset-item");

			var waypoints = $elem.waypoint(
				function (direction) {
					var _self = this.element ? this.element : this;
					var $self = $(_self);
					$self.addClass("animate");
				},
				{
					offset: "85%",
					triggerOnce: true,
				}
			);
		},

		slider_range_salary: function () {
			var archive_jobs = $(".archive-jobs");
			var $sliderContainer = $("#range-slider-salary");

			var min = parseInt($sliderContainer.data("salary-min")) || 0;
			var max = parseInt($sliderContainer.data("salary-max")) || 500;
			var maxRaw = parseInt($sliderContainer.data("salary-max-raw")) || max;
			var step = 1;
			var defaultMin = min;
			var defaultMax = max;
			var timers = {};

			// Hàm format số chuẩn
			function formatNumber(n) {
				var thousand_separator = $sliderContainer.data("thousand-separator") || ',';
				// Handle empty thousand separator
				if (!thousand_separator || thousand_separator.trim() === '') {
					thousand_separator = ' ';
				}
				return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, thousand_separator);
			}

			function renderAmountDisplay(val1, val2) {
				var formatted1 = formatNumber(val1);
				var formatted2 = formatNumber(val2);
				var currencySign = $sliderContainer.data("currency-sign") || '$';
				var currencyPosition = $sliderContainer.data("currency-position") || 'before';
				var display1 = currencyPosition === 'before' ? currencySign + formatted1 : formatted1 + currencySign;
				var display2 = currencyPosition === 'before' ? currencySign + formatted2 : formatted2 + currencySign;
				if (val1 === val2) return display1;
				return display1 + ' - ' + display2;
			}

			$("#slider-range-salary").slider({
				range: true,
				min: min,
				max: max,
				step: step,
				values: [defaultMin, defaultMax],
				slide: function (event, ui) {
					$("#salary-amount").val(renderAmountDisplay(ui.values[0], ui.values[1]));
				},
				change: function (event, ui) {
					var values_start = ui.values[0];
					var values_end = ui.values[1];

					if (values_end === max) {
						values_end = maxRaw;
					}

					$("#jobs_filter_salary_min").val(values_start);
					$("#jobs_filter_salary_max").val(values_end);

					if (values_start !== min || values_end !== maxRaw) {
						archive_jobs.addClass("filter-active");
					} else {
						archive_jobs.removeClass("filter-active");
					}
				},
			});

			// Init display
			var initMin = $("#slider-range-salary").slider("values", 0);
			var initMax = $("#slider-range-salary").slider("values", 1);
			$("#salary-amount").val(renderAmountDisplay(initMin, initMax));
			$("#jobs_filter_salary_min").val(initMin);
			$("#jobs_filter_salary_max").val(initMax === max ? maxRaw : initMax);
		},

		pagination: function () {
			var type_pagination = $(".jobportal-pagination").attr("data-type");

			$("body").on(
				"click",
				".jobportal-pagination.ajax-call a.page-numbers",
				function (e) {
					$(".jobportal-pagination .pagination").addClass("active");
					$(".jobportal-pagination li .page-numbers").removeClass("current");
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
					$(".jobportal-pagination").find('input[name="paged"]').val(paged);

					if (type_pagination !== "loadpage") {
						e.preventDefault();
						ajax_call = true;
						if (type_pagination == "number") {
							JOBS.elements.ajax_load(ajax_call, "pagination");
						} else {
							JOBS.elements.ajax_load(ajax_call, "loadmore");
						}
					}
				}
			);

			if (type_pagination == "loadpage") {
				var pageNumbers = $(".jobportal-pagination").find(
					".pagination a.page-numbers"
				);
				pageNumbers.each(function () {
					var text = $(this).text();
					var href = $(this).closest(".pagi-loadpage").data("archive");
					if (text !== "") {
						var link = href + "/page/" + text + "/?nagi-paged=" + text;
						$(this).attr("href", link);
					}
				});
			}
		},

		removeClassStartingWith: function (node, begin) {
			node.removeClass(function (index, className) {
				return (
					className.match(new RegExp("\\b" + begin + "\\S+", "g")) || []
				).join(" ");
			});
		},

		jobs_layout: function () {
			$(".jobs-layout a").on("click", function (event) {
				event.preventDefault();
				var layout = $(this).attr("data-layout");
				var type_pagination = $(".jobportal-pagination").attr("data-type");
				if (type_pagination == "loadmore") {
					$(".jobportal-pagination").find('input[name="paged"]').val(1);
				}
				$(this).closest(".jobs-layout").find(">a").removeClass("active");
				$(this).addClass("active");
				JOBS.elements.removeClassStartingWith(
					$(".archive-layout>.inner-content"),
					"layout-"
				);
				$(this).closest(".inner-content").addClass(layout);

				$(".form-jobs-top-filter .btn-top-filter").removeData("clicked");

				$(".area-jobs .jobportal-jobs-item").each(function () {
					JOBS.elements.removeClassStartingWith($(this), "layout-");
					$(this).addClass(layout);
				});

				ajax_call = true;
				JOBS.elements.ajax_load(ajax_call);
			});
		},

		display_clear: function () {
			var archive_jobs = $(".archive-jobs");
			var hasFilter = false;

			// Keyword
			var $keyword = $('.jobportal-menu-filter input[name="s"]');
			if ($keyword.length && $keyword.val() && $keyword.val().trim() !== '') hasFilter = true;

			// Location
			var $location = $('.jobportal-menu-filter input[name="jobs_location"]');
			if ($location.length && $location.val() && $location.val().trim() !== '') hasFilter = true;

			// Salary
			var $salaryMin = $('.jobportal-menu-filter input[name="jobs_filter_salary_min"]');
			var $salaryMax = $('.jobportal-menu-filter input[name="jobs_filter_salary_max"]');
			var $sliderContainer = $("#range-slider-salary");
			var minSalary = parseInt($sliderContainer.data("salary-min")) || 0;
			var maxSalaryRaw = parseInt($sliderContainer.data("salary-max-raw")) || (parseInt($sliderContainer.data("salary-max")) || 500);
			if (
				$salaryMin.length && $salaryMax.length &&
				(
					($salaryMin.val() !== "" && parseInt($salaryMin.val()) !== minSalary) ||
					($salaryMax.val() !== "" && parseInt($salaryMax.val()) !== maxSalaryRaw)
				)
			) {
				hasFilter = true;
			}

			// Rate select
			var $rate = $('.jobportal-menu-filter select[name="jobs_filter_rate"]');
			if ($rate.length && $rate.val() && $rate.val() !== "month" && $rate.val() !== "") hasFilter = true;

			// Posting date
			var $posting = $('.jobportal-menu-filter select[name="jobs_posting_date"]');
			if ($posting.length && $posting.val() && $posting.val() !== "") hasFilter = true;

			// Custom fields (text/textarea/select)
			$('.jobportal-menu-filter .entry-filter-custom input[type="text"], .jobportal-menu-filter .entry-filter-custom textarea, .jobportal-menu-filter .entry-filter-custom select').each(function () {
				if ($(this).val() !== "" && $(this).val() !== null) hasFilter = true;
			});

			// Custom fields & taxonomy checkbox
			if ($('.jobportal-menu-filter input[type="checkbox"]:checked').length > 0) hasFilter = true;

			// Taxonomy select (single/multi)
			$('.jobportal-menu-filter select').each(function () {
				var $el = $(this);
				var name = $el.attr('name');
				var val = $el.val();
				if (name === "jobs_filter_rate" || name === "jobs_posting_date") return;
				if (Array.isArray(val)) {
					if (val.some(function (v) { return v !== "" && v !== "all"; })) hasFilter = true;
				} else {
					if (val && val !== "" && val !== null && val !== "all") hasFilter = true;
				}
			});

			// Country/State/City selects
			var $country = $("select.jobportal-select-country");
			if ($country.length && $country.val() && $country.val() !== "") hasFilter = true;

			if (hasFilter) {
				archive_jobs.find(".jobportal-clear-filter").show();
				$(".jobportal-nav-filter").addClass("active");
			} else {
				archive_jobs.find(".jobportal-clear-filter").hide();
				$(".jobportal-nav-filter").removeClass("active");
			}
		},

		filter_clear_top: function () {
			$(".jobportal-clear-top-filter").on("click", function () {
				$('.form-jobs-top-filter input[name="jobs_filter_search"]').val("");
				$('.form-jobs-top-filter input[name="jobs-search-location"]').val("");

				$(".form-jobs-top-filter .jobportal-select2").val("");
				$(".form-jobs-top-filter .jobportal-select2").select2("destroy");

				// Reinitialize select2
				$(".form-jobs-top-filter .jobportal-select2").each(function () {
					var option = $(this).find("option");
					if (typeof theme_vars !== 'undefined' && theme_vars.enable_search_box_dropdown == 1) {
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

				$(".jobportal-form-location .icon-arrow i").on("click", function () {
					var options = $(this)
						.closest(".jobportal-form-location")
						.find("select.jobportal-select2 option");
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
				JOBS.elements.ajax_load(ajax_call);
			});
		},

		setupSelect2Events: function () {
			$(".select2.select2-container").off("click.customSelect2");
			$(".jobportal-form-location .icon-arrow i").off("click.customSelect2");

			$(".select2.select2-container").on("click.customSelect2", function () {
				var options = $(this).prev().find("option");
				options.each(function () {
					var option_val = $(this).val();
					var level = $(this).attr("data-level");
					if (option_val && level) {
						$('.select2-results li[id$="' + option_val + '"]').attr("data-level", level);
					}
				});
			});

			$(".jobportal-form-location .icon-arrow i").on("click.customSelect2", function () {
				var options = $(this)
					.closest(".jobportal-form-location")
					.find("select.jobportal-select2 option");
				options.each(function () {
					var option_val = $(this).val();
					var level = $(this).attr("data-level");
					if (option_val && level) {
						$('.select2-results li[id$="' + option_val + '"]').attr("data-level", level);
					}
				});
			});
		},

		filter_clear: function () {
			$(".jobportal-clear-filter").on("click", function () {
				is_clearing_filter = true;

				$(".jobportal-menu-filter ul.filter-control li").removeClass("active");
				$('.jobportal-menu-filter input[type="checkbox"]').prop("checked", false);

				var $slider = $("#slider-range-salary");
				var $sliderContainer = $("#range-slider-salary");

				if ($slider.length && $sliderContainer.length) {
					var min = parseInt($sliderContainer.data("salary-min")) || 0;
					var max = parseInt($sliderContainer.data("salary-max")) || 500;
					var maxRaw = parseInt($sliderContainer.data("salary-max-raw")) || max;

					$slider.slider("values", [min, max]);
					$("#jobs_filter_salary_min").val(min);
					$("#jobs_filter_salary_max").val(maxRaw);

					var currencySign = $sliderContainer.data("currency-sign") || '$';
					var currencyPosition = $sliderContainer.data("currency-position") || 'before';
					var separator = $sliderContainer.data("thousand-separator") || ',';
					// Handle empty thousand separator
					if (!separator || separator.trim() === '') {
						separator = ' ';
					}

					function formatCurrency(amount) {
						var formatted = amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, separator);
						return currencyPosition === 'before' ? currencySign + formatted : formatted + currencySign;
					}

					var displayText = min === max ? formatCurrency(min) : formatCurrency(min) + ' - ' + formatCurrency(max);
					$("#salary-amount").val(displayText);
				}
				$('.jobportal-menu-filter select[name="jobs_filter_rate"]').val(null).trigger("change");
				$(".jobportal-menu-filter .jobportal-select2").val(null).trigger('change');
				$('.jobportal-menu-filter .entry-filter-custom input[type="text"], .jobportal-menu-filter .entry-filter-custom textarea').val("");
				$(".jobportal-menu-filter .entry-filter-custom select").val(null).trigger("change");
				$('.jobportal-menu-filter .entry-filter-custom input[type="checkbox"]').prop("checked", false);
				$(".jobportal-pagination").find('input[name="paged"]').val(1);

				setTimeout(function () {
					ajax_call = true;
					JOBS.elements.ajax_load(ajax_call);
					is_clearing_filter = false;
				}, 100);
			});
		},

		filter_single: function () {
			$(".jobportal-menu-filter ul.filter-control a").on("click", function (e) {
				e.preventDefault();
				$(".jobportal-pagination").find('input[name="paged"]').val(1);
				if ($(this).parent().hasClass("active")) {
					$(this)
						.parents(".jobportal-menu-filter ul.filter-control")
						.find("li")
						.removeClass("active");
					$(this).closest(".entry-filter").removeClass("open");
				} else {
					$(this)
						.parents(".jobportal-menu-filter ul.filter-control")
						.find("li")
						.removeClass("active");
					$(this).parent().addClass("active");
					$(this).closest(".entry-filter").addClass("open");
				}
				ajax_call = true;
				JOBS.elements.ajax_load(ajax_call);
			});
		},

		ajax_load: function (ajax_call, pagination) {
			var title,
				sort_by,
				types,
				has_map_val,
				experience,
				career,
				skills,
				gender,
				location,
				qualification,
				radius_cities,
				categories,
				current_term,
				type_term,
				salary_min,
				salary_max,
				data_salary_max,
				jobs_layout,
				location_country,
				location_state,
				location_city,
				search_fields_sidebar,
				jobs_posting_date;

			var jobs_custom_fields = {};
			if (
				window.jobportal_custom_filter_fields &&
				Array.isArray(window.jobportal_custom_filter_fields)
			) {
				window.jobportal_custom_filter_fields.forEach(function (field_id) {
					jobs_custom_fields[field_id] = "";
				});
			}

			var paged = 1;
			var map_html = $(".maptype").clone();

			paged = $(".jobportal-pagination").find('input[name="paged"]').val();
			title = $('input[name="jobs_filter_search"]').val();
			current_term = $('input[name="current_term"]').val();
			type_term = $('input[name="type_term"]').val();
			has_map_val = $('input[name="has_map"]').val();
			(jobs_layout = $(".jobs-layout a.active").attr("data-layout")),
				(jobs_posting_date = $(
					".jobportal-menu-filter select[name='jobs_posting_date']"
				).val());

			search_fields_sidebar = $('input[name="search_fields_sidebar"]').val();
			var result_fields = $.parseJSON(search_fields_sidebar);

			location = $('input[name="jobs-search-location"]').val();
			location_country = $("select.jobportal-select-country").val();
			location_state = $("select.jobportal-select-state").val();
			location_city = $("select.jobportal-select-city").val();
			radius_cities = $(".jobportal-form-location")
				.find('input[name="jobs_number_radius"]')
				.val();

			if (window.jobportal_custom_filter_fields && Array.isArray(window.jobportal_custom_filter_fields)) {
				window.jobportal_custom_filter_fields.forEach(function (field_id) {
					const checkboxes = document.querySelectorAll('[name="' + field_id + '[]"]:checked');
					if (checkboxes.length > 0) {
						const values = Array.from(checkboxes).map(chk => chk.value);
						if (values.length > 0) {
							jobs_custom_fields[field_id] = values;
						}
					} else {
						const el = document.querySelector('[name="' + field_id + '"]');
						if (el && el.value && el.value.trim() !== '') {
							jobs_custom_fields[field_id] = el.value.trim();
						}
					}
				});
			}

			if (result_fields.hasOwnProperty("jobs-categories")) {
				categories = $('input[name="jobs-categories_id[]"]:checked')
					.map(function () {
						return $(this).val();
					})
					.get();
			} else {
				categories = $('select[name="jobs-categories"]').val();
			}

			if (result_fields.hasOwnProperty("jobs-skills")) {
				skills = $('input[name="jobs-skills_id[]"]:checked')
					.map(function () {
						return $(this).val();
					})
					.get();
			} else {
				skills = $('select[name="jobs-skills"]').val();
			}

			if (result_fields.hasOwnProperty("jobs-type")) {
				types = $('input[name="jobs-type_id[]"]:checked')
					.map(function () {
						return $(this).val();
					})
					.get();
			} else {
				types = $('select[name="jobs-type[]"]').val() || [];
				types = Array.isArray(types) ? types : [types];
			}

			if (result_fields.hasOwnProperty("jobs-experience")) {
				experience = $('input[name="jobs-experience_id[]"]:checked')
					.map(function () {
						return $(this).val();
					})
					.get();
			} else {
				experience = $('select[name="jobs-experience"]').val();
			}

			if (result_fields.hasOwnProperty("jobs-career")) {
				career = $('input[name="jobs-career_id[]"]:checked')
					.map(function () {
						return $(this).val();
					})
					.get();
			} else {
				career = $('select[name="jobs-career"]').val();
			}

			if (result_fields.hasOwnProperty("jobs-gender")) {
				gender = $('input[name="jobs-gender_id[]"]:checked')
					.map(function () {
						return $(this).val();
					})
					.get();
			} else {
				gender = $('select[name="jobs-gender"]').val();
			}

			if (result_fields.hasOwnProperty("jobs-qualification")) {
				qualification = $('input[name="jobs-qualification_id[]"]:checked')
					.map(function () {
						return $(this).val();
					})
					.get();
			} else {
				qualification = $('select[name="jobs-qualification"]').val();
			}

			sort_by = menu_filter_wrap
				.find(".sort-by.filter-control li.active a")
				.data("sort");

			var select_sort = $('.archive-layout select[name="sort_by"]').val();
			if (select_sort) {
				sort_by = select_sort;
			}

			salary_min = menu_filter_wrap
				.find('input[name="jobs_filter_salary_min"]')
				.val();
			salary_max = menu_filter_wrap
				.find('input[name="jobs_filter_salary_max"]')
				.val();

			data_salary_max = menu_filter_wrap
				.find('#range-slider-salary').data('salary-max-raw');

			var maptype = $(".maptype").data("maptype");

			if (maptype == "google_map") {
				var marker_cluster = null,
					googlemap_default_zoom = jobportal_template_vars.googlemap_default_zoom,
					not_found = jobportal_template_vars.not_found,
					clusterIcon = jobportal_template_vars.clusterIcon,
					google_map_style = jobportal_template_vars.google_map_style,
					google_map_type = jobportal_template_vars.google_map_type,
					pin_cluster_enable = jobportal_template_vars.pin_cluster_enable;

				var infowindow;
				try {
					infowindow = new google.maps.InfoWindow({
						maxWidth: 370,
					});
				} catch (error) {
					// Google Maps API not loaded yet, will be initialized later
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
					loadGoogleMapsAPI().then(function () {
						// Ensure Google Maps API is fully loaded before accessing ControlPosition
						if (typeof google !== 'undefined' && google.maps && google.maps.ControlPosition) {
							// Declare jobportal_search_map_option at function scope so it's accessible elsewhere
							window.jobportal_search_map_option = {
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

							// Initialize InfoWindow if not already done
							if (!infowindow) {
								try {
									infowindow = new google.maps.InfoWindow({
										maxWidth: 370,
									});
								} catch (error) {
									// Error initializing InfoWindow
								}
							}

							jobportal_map = new google.maps.Map(
								document.getElementById("jobs-map-filter"),
								window.jobportal_search_map_option
							);

							// Ensure infowindow is properly initialized before calling jobportal_my_location
							if (infowindow) {
								// Initialize map only - markers will be added when data is loaded via AJAX
								// My location
								jobportal_my_location(jobportal_map);
							}
						}
					});
				}

				var jobportal_add_markers = function (props, map) {
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
							url: ".jobs-" + prop.id,
							map: map,
							jobs: prop.jobs,
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
						contentString.innerHTML = prop.jobs;

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
							$(".area-jobs .jobportal-jobs-item").removeClass("highlight");
							if (
								elem.length > 0 &&
								click_marker &&
								$(".archive-jobs.map-event").length > 0
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
					// Ensure Google Maps API is fully loaded before accessing ControlPosition
					if (typeof google !== 'undefined' && google.maps && google.maps.ControlPosition) {
						map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(
							centerControlDiv
						);
					}

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
			} else if (maptype == "openstreetmap") {
				// Begin Openstreetmap

				var jobportal_osm_add_markers = function (props) {
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
								jobs: prop.jobs,
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
								markers.bindPopup(marker.properties.jobs);
							} else {
								markers.bindPopup();
							}

							el.addEventListener("click", function (e) {
								flyToStore(marker);
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

				var jobportal_mapbox_add_markers = function (props) {
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
								jobs: prop.jobs,
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
							/* Add a GeoJSON source containing jobs coordinates and information. */
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
							.setHTML(currentFeature.properties.jobs)
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
					jobportal_hover_map_effects();
				}

				// End Mapbox
			}

			JOBS.elements.display_clear();

			var page_item = $(".area-jobs").attr("data-item-amount");

			if (page_item) {
				item_amount = page_item;
			}

			var type_pagination = $(".jobportal-pagination").attr("data-type");
			$(".area-jobs .jobportal-jobs-item").addClass("skeleton-loading");

			var data = {
				action: "jobportal_jobs_archive_ajax",
				paged: paged,
				title: title,
				item_amount: item_amount,
				sort_by: sort_by,
				categories: categories,
				types: types,
				has_map_val: has_map,
				skills: skills,
				experience: experience,
				career: career,
				gender: gender,
				qualification: qualification,
				salary_min: salary_min,
				salary_max: salary_max,
				data_salary_max: data_salary_max,
				current_term: current_term,
				type_term: type_term,
				location: location,
				location_country: location_country,
				location_state: location_state,
				location_city: location_city,
				radius_cities: radius_cities,
				jobs_layout: jobs_layout,
				jobs_posting_date: jobs_posting_date,
			};

			Object.keys(jobs_custom_fields).forEach(function (field_id) {
				data['jobs_custom_field_' + field_id] = jobs_custom_fields[field_id];
			});

			$.ajax({
				dataType: "json",
				url: ajax_url,
				data: data,
				beforeSend: function () {
					$(".jobportal-filter-search-map .jobportal-loading-effect").fadeIn();
					if ($(".form-jobs-top-filter .btn-top-filter").data("clicked")) {
						$(".btn-top-filter .btn-loading").fadeIn();
					}
				},
				success: function (data) {
					$(".btn-top-filter .btn-loading").fadeOut();
					if (maptype == "google_map") {
						if (has_map) {
							loadGoogleMapsAPI().then(function () {
								// Initialize InfoWindow if not already done
								if (!infowindow) {
									try {
										infowindow = new google.maps.InfoWindow({
											maxWidth: 370,
										});
									} catch (error) {
										// Error initializing InfoWindow in AJAX
									}
								}

								jobportal_map = new google.maps.Map(
									document.getElementById("jobs-map-filter"),
									window.jobportal_search_map_option
								);

								google.maps.event.trigger(jobportal_map, "resize");
								if (data.success === true) {
									if (data.jobs) {
										var count_jobs = data.jobs.length;
									}
								}

								if (count_jobs == 1) {
									var boundsListener = google.maps.event.addListener(
										jobportal_map,
										"bounds_changed",
										function (event) {
											this.setZoom(parseInt(googlemap_default_zoom));
											google.maps.event.removeListener(boundsListener);
										}
									);
								}

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
							});
						}

						if (data.success === true) {
							if (has_map) {
								loadGoogleMapsAPI().then(function () {
									markers.forEach(function (marker) {
										marker.setMap(null);
									});

									markers = [];
									jobportal_add_markers(data.jobs, jobportal_map);

									// Ensure infowindow is available before calling jobportal_my_location
									if (infowindow) {
										jobportal_my_location(jobportal_map);
									}
									jobportal_map.fitBounds(
										markers.reduce(function (bounds, marker) {
											return bounds.extend(marker.getPosition());
										}, new google.maps.LatLngBounds())
									);
								});
							}

							if (ajax_call == true) {
								if (
									data.pagination_type == "number" ||
									pagination !== "loadmore"
								) {
									$(".area-jobs").html(data.jobs_html);
									$(".filter-neighborhood").html(data.filter_html);
									$(".jobportal-pagination .pagination").html(data.pagination);
									$(".archive-layout .result-count").html(data.count_post);
								} else {
									$(".area-jobs").append(data.jobs_html);
									$(".filter-neighborhood").html(data.filter_html);
									if (data.hidden_pagination) {
										$(".jobportal-pagination .pagination").html("");
									}
									$(".jobportal-pagination .pagination").removeClass("active");
								}

								JOBS.elements.waypoints();
							}

							if (has_map) {
								loadGoogleMapsAPI().then(function () {
									google.maps.event.trigger(jobportal_map, "resize");

									if (jobportal_template_vars.map_pin_cluster != 0) {
										marker_cluster = new MarkerClusterer(jobportal_map, markers, {
											gridSize: 60,
											styles: [
												{
													url: clusterIcon,
													width: 66,
													height: 65,
													textColor: "#fff",
												},
											],
										});
									}
								});
							}
						} else {
							if (ajax_call == true) {
								if (
									data.pagination_type == "number" ||
									pagination !== "loadmore"
								) {
									$(".area-jobs").html(
										'<div class="jobportal-ajax-result">' + not_jobs + "</div>"
									);
									$(".archive-layout .result-count").html(data.count_post);
									$(".jobportal-pagination .pagination").html("");
								} else {
									$(".area-jobs").append(data.jobs_html);
									if (data.hidden_pagination) {
										$(".jobportal-pagination .pagination").html("");
									}
									$(".jobportal-pagination .pagination").removeClass("active");
								}
							}
						}

						if (has_map) {
							loadGoogleMapsAPI().then(function () {
								jobportal_map.fitBounds(
									markers.reduce(function (bounds, marker) {
										return bounds.extend(marker.getPosition());
									}, new google.maps.LatLngBounds())
								);
								google.maps.event.trigger(jobportal_map, "resize");
							});
						}

						$(".area-jobs .jobportal-jobs-item").removeClass("skeleton-loading");
					} else if (maptype == "openstreetmap") {
						$(".jobportal-filter-search-map .jobportal-loading-effect").fadeOut();
						$(".area-jobs .jobportal-jobs-item").removeClass("skeleton-loading");
						if (has_map) {
							jobportal_osm_add_markers(data.jobs);
						}
						if (data.success === true) {
							if (ajax_call == true) {
								if (
									data.pagination_type == "number" ||
									pagination !== "loadmore"
								) {
									$(".area-jobs").html(data.jobs_html);
									$(".filter-neighborhood").html(data.filter_html);
									$(".jobportal-pagination .pagination").html(data.pagination);
									$(".archive-layout .result-count").html(data.count_post);
								} else {
									$(".area-jobs").append(data.jobs_html);
									$(".filter-neighborhood").html(data.filter_html);
									if (data.hidden_pagination) {
										$(".jobportal-pagination .pagination").html("");
									}
									$(".jobportal-pagination .pagination").removeClass("active");
								}

								JOBS.elements.waypoints();
							}
						} else {
							if (ajax_call == true) {
								if (
									data.pagination_type == "number" ||
									pagination !== "loadmore"
								) {
									$(".area-jobs").html(
										'<div class="jobportal-ajax-result">' + not_jobs + "</div>"
									);
									$(".archive-layout .result-count").html(data.count_post);
									$(".jobportal-pagination .pagination").html("");
								} else {
									$(".area-jobs").append(data.jobs_html);
									if (data.hidden_pagination) {
										$(".jobportal-pagination .pagination").html("");
									}
									$(".jobportal-pagination .pagination").removeClass("active");
								}
							}
						}
					} else {
						$(".jobportal-filter-search-map .jobportal-loading-effect").fadeOut();
						$(".area-jobs .jobportal-jobs-item").removeClass("skeleton-loading");
						if (has_map) {
							jobportal_mapbox_add_markers(data.jobs);
						}
						if (data.success === true) {
							if (ajax_call == true) {
								if (
									data.pagination_type == "number" ||
									pagination !== "loadmore"
								) {
									$(".area-jobs").html(data.jobs_html);
									$(".filter-neighborhood").html(data.filter_html);
									$(".jobportal-pagination .pagination").html(data.pagination);
									$(".archive-layout .result-count").html(data.count_post);
								} else {
									$(".area-jobs").append(data.jobs_html);
									$(".filter-neighborhood").html(data.filter_html);
									if (data.hidden_pagination) {
										$(".jobportal-pagination .pagination").html("");
									}
									$(".jobportal-pagination .pagination").removeClass("active");
								}

								JOBS.elements.waypoints();
							}
						} else {
							if (ajax_call == true) {
								if (
									data.pagination_type == "number" ||
									pagination !== "loadmore"
								) {
									$(".area-jobs").html(
										'<div class="jobportal-ajax-result">' + not_jobs + "</div>"
									);
									$(".archive-layout .result-count").html(data.count_post);
									$(".jobportal-pagination .pagination").html("");
								} else {
									$(".area-jobs").append(data.jobs_html);
									if (data.hidden_pagination) {
										$(".jobportal-pagination .pagination").html("");
									}
									$(".jobportal-pagination .pagination").removeClass("active");
								}
							}
						}
					}

					if (!is_mobile) {
						jobportal_hover_map_effects();
					}
					JOBS.elements.preview_job();

					// Scroll to first item after pagination
					if (pagination === "pagination") {
						JOBS.elements.scroll_to(".area-jobs .jobportal-jobs-item:first");
					}
				},
			});
		},

		preview_job: function () {
			// iOS Safari fix: Remove previously bound click handlers to prevent memory leak
			$(".inner-content.layout-full .jobportal-jobs-item").off("click");
			$(".inner-content.layout-full .jobportal-jobs-item").each(function () {
				var _this = $(this);
				var wrapper = $(".col-right.preview-job-wrapper");
				if ($(window).width() > 991) {
					_this.on("click", function (e) {
						e.preventDefault();
						$(".inner-content.layout-full .jobportal-jobs-item").removeClass(
							"active"
						);
						_this.addClass("active");
						var id = $(this).attr("data-jobid");
						$.ajax({
							url: ajax_url,
							type: "POST",
							cache: false,
							dataType: "json",
							data: {
								id: id,
								layout: "layout-full",
								action: "preview_job",
							},
							beforeSend: function () {
								wrapper
									.find(".block-jobs-warrper")
									.addClass("skeleton-loading");
							},
							success: function (data) {
								// iOS Safari fix: Cleanup previous event bindings to prevent memory leaks
								$(".toggle-social").off("click", ".btn-share");
								$(".jobportal-select2").each(function() {
									if ($(this).data('select2')) {
										try { $(this).select2('destroy'); } catch(e) {}
									}
								});
								wrapper.text("");
								wrapper
									.find(".block-jobs-warrper")
									.removeClass("skeleton-loading");
								wrapper.append(data.content);
								var top = $(".inner-content.layout-full .col-right").offset()
									.top;
								$("html, body").animate({ scrollTop: top }, "slow");
								$(".toggle-social").on("click", ".btn-share", function (e) {
									e.preventDefault();
									$(this).parent().toggleClass("active");
									$(this).parent().find(".social-share").slideToggle(300);
								});
								var $form_popup = $(".form-popup-apply");
								var $btn_close = $form_popup.find(".btn-close");
								var $bg_overlay = $form_popup.find(".bg-overlay");
								var $btn_cancel = $form_popup.find(".button-cancel");

								var select2 = "";
								$(".jobportal-select2").each(function () {
									var option = $(this).find("option");
									if (theme_vars.enable_search_box_dropdown == 1) {
										if (option.length > theme_vars.limit_search_box) {
											select2 = $(this).select2();
										} else {
											select2 = $(this).select2({
												minimumResultsForSearch: -1,
											});
										}
									} else {
										select2 = $(this).select2({
											minimumResultsForSearch: -1,
										});
									}
								});

								$(".jobportal-select2.prefix-code").each(function () {
									var group = $(this).closest(".tel-group");
									var rendered = $(this).find("option:selected").val();
									group
										.find(".select2-selection__rendered")
										.removeClass(function (index, className) {
											var classNames = className.split(" ");
											return classNames
												.filter(function (name) {
													return name !== "select2-selection__rendered";
												})
												.join(" ");
										})
										.addClass(rendered);
								});

								$(".jobportal-select2.prefix-code").on(
									"select2:select",
									function () {
										var group = $(this).closest(".tel-group");
										var rendered = $(this).find("option:selected").val();
										var code = $(this)
											.find("option:selected")
											.attr("data-dial-code");
										group
											.find(".select2-selection__rendered")
											.removeClass(function (index, className) {
												var classNames = className.split(" ");
												return classNames
													.filter(function (name) {
														return name !== "select2-selection__rendered";
													})
													.join(" ");
											})
											.addClass(rendered);
										group.find('input[type="tel"]').val(code);
									}
								);

								$form_popup.each(function () {
									var $form_popup_id = $("#" + $(this).attr("id"));
									var $btn_popup = $(
										".jobportal-button-apply." + $(this).attr("id")
									);
									function open_popup(e) {
										e.preventDefault();
										$form_popup_id.css({ opacity: "1", visibility: "unset" });
									}

									function close_popup(e) {
										e.preventDefault();
										$form_popup_id.css({ opacity: "0", visibility: "hidden" });
									}
									$btn_popup.on("click", open_popup);
									$bg_overlay.on("click", close_popup);
									$btn_close.on("click", close_popup);
									$btn_cancel.on("click", close_popup);
								});

								var ajax_url = jobportal_template_vars.ajax_url,
									apply_saved = jobportal_template_vars.apply_saved,
									not_file = jobportal_template_vars.not_file,
									$form_popup = $(".form-popup-apply"),
									title = jobportal_upload_cv_vars.title,
									cv_file = jobportal_upload_cv_vars.cv_file,
									cv_max_file_size = jobportal_upload_cv_vars.cv_max_file_size,
									text = jobportal_upload_cv_vars.text,
									url = jobportal_upload_cv_vars.url,
									upload_nonce = jobportal_upload_cv_vars.upload_nonce;

								$form_popup.each(function () {
									var $btn_submit = $(
										"#" + $(".btn-submit-apply-jobs").attr("id")
									);
									var $btn_popup = $(
										".jobportal-button-apply." + $(this).attr("id")
									);
									var apply_form = $("#" + $(this).attr("id"));
									$btn_submit.on("click", function (e) {
										e.preventDefault();
										var $this = $(this),
											email = apply_form
												.find('input[name="apply_email"]')
												.val(),
											message = apply_form
												.find('textarea[name="apply_message"]')
												.val(),
											phone = apply_form
												.find('input[name="apply_phone"]')
												.val(),
											candidate_id = $btn_popup.data("candidate_id"),
											jobs_id = $btn_popup.data("jobs_id"),
											cv_url = apply_form
												.find('input[name="jobs_cv_url"]')
												.val(),
											type_apply = apply_form
												.find('input[name="type_apply"]')
												.val();

										$.ajax({
											type: "POST",
											url: ajax_url,
											dataType: "json",
											data: {
												action: "jobs_add_to_apply",
												jobs_id: jobs_id,
												candidate_id: candidate_id,
												email: email,
												phone: phone,
												message: message,
												cv_url: cv_url,
												type_apply: type_apply,
												security: theme_vars.job_apply_nonce,
											},
											beforeSend: function () {
												$this.find(".btn-loading").fadeIn();
											},
											success: function (data) {
												if (data.success == true) {
													apply_form.find(".message_error").addClass("true");
													apply_form.find(".message_error").text(data.message);
													$(
														".jobportal-button-apply[data-jobs_id =" + jobs_id + "]"
													).html(apply_saved);
													location.reload();
												} else {
													$(".message_error").text(data.message);
												}
												$this.find(".btn-loading").fadeOut();
											},
										});
									});
								});

								var featured_image = function () {
									var uploader_featured_image = new plupload.Uploader({
										browse_button: "jobportal_select_cv",
										file_data_name: "jobportal_thumbnail_upload_file",
										drop_element: "jobportal_select_cv",
										container: "jobportal_cv_plupload_container",
										url: url,
										filters: {
											mime_types: [
												{
													title: title,
													extensions: cv_file,
												},
											],
											max_file_size: cv_max_file_size,
											prevent_duplicates: true,
										},
									});
									uploader_featured_image.init();

									uploader_featured_image.bind(
										"UploadProgress",
										function (up, file) {
											$("#jobportal_select_cv i").removeClass(
												"far fa-arrow-from-bottom large"
											);
											$("#jobportal_select_cv i").addClass(
												"fal fa-spinner fa-spin large"
											);
										}
									);

									uploader_featured_image.bind(
										"FilesAdded",
										function (up, files) {
											var maxfiles = 1;
											up.refresh();
											uploader_featured_image.start();
										}
									);
									uploader_featured_image.bind("Error", function (up, err) {
										document.getElementById("cv_errors_log").innerHTML +=
											"Error #" + err.code + ": " + err.message + "<br/>";
									});

									uploader_featured_image.bind(
										"FileUploaded",
										function (up, file, ajax_response) {
											var response = $.parseJSON(ajax_response.response);
											if (response.success) {
												$(".cv_url").val(response.url);
												$("#jobportal_drop_cv").attr(
													"data-attachment-id",
													response.attachment_id
												);
												$("#jobportal_drop_cv").append(
													'<a class="icon cv-icon-delete" data-jobs-id="0"  data-attachment-id="' +
													response.attachment_id +
													'" href="#" ><i class="far fa-trash-alt large"></i></a>'
												);
												var $html =
													'<i class="far fa-arrow-from-bottom large"></i><span>' +
													response.title +
													"</span>";
												$("#jobportal_select_cv i").addClass(
													"far fa-arrow-from-bottom large"
												);
												$("#jobportal_select_cv").html($html);
												$("#cv_url-error").hide();
												$("#candidate-profile-form")
													.find(".point-mark")
													.trigger('change');
											}
										}
									);
								};
								featured_image();
								var jobportal_jobs_thumb_event = function ($type) {
									$("body").on("click", ".cv-icon-delete", function (e) {
										e.preventDefault();
										var $this = $(this),
											icon_delete = $this,
											thumbnail = $this.closest(".media-thumb-wrap"),
											jobs_id = $this.data("jobs-id"),
											attachment_id = $this.data("attachment-id");
										icon_delete.html(
											'<i class="fal fa-spinner fa-spin large"></i>'
										);

										$.ajax({
											type: "post",
											url: ajax_url,
											dataType: "json",
											data: {
												action: "jobportal_thumbnail_remove_ajax",
												jobs_id: jobs_id,
												attachment_id: attachment_id,
												type: $type,
												removeNonce: upload_nonce,
											},
											beforeSend: function () {
												icon_delete.html(
													'<i class="fal fa-spinner fa-spin large"></i>'
												);
											},
											success: function (response) {
												if (response.success) {
													$("#cv_url-error").show();
													$(".jobportal_cv_file").show();
												}
												$("#jobportal_select_cv").html(text);
											$("#jobportal_drop_cv").attr("data-attachment-id", "");
											$("#candidate-profile-form")
												.find(".point-mark")
												.trigger('change');
												icon_delete.remove();
											},
											error: function () {
												icon_delete.html(
													'<i class="far fa-trash-alt large"></i>'
												);
											},
										});
									});
								};
								jobportal_jobs_thumb_event("thumb");
								GLF.element.slick_carousel();
								JOBS.elements.preview_job_tab();
							},
						});
					});
				}
			});
		},

		preview_job_tab: function () {
			// iOS Safari fix: Bind the company-overview click handler once, not inside tab click handler
			$("body").off("click", ".company-overview .content a").on("click", ".company-overview .content a", function (e) {
				e.preventDefault();
				$(this).parent().addClass("is-active");
				$(this).remove();
			});

			$(".preview-tabs").each(function () {
				var _this = $(this),
					nav = _this.find(".tab-nav li a"),
					content = _this.find(".tab-content");

				// iOS Safari fix: Remove previous handlers before binding new ones
				nav.off("click").on("click", function (e) {
					e.preventDefault();
					var id = $(this).attr("href");

					nav.removeClass("is-active");
					content.removeClass("is-active");
					$(id).addClass("is-active");
					$(this).addClass("is-active");
					JOBS.elements.view_phone_number();
				});
			});
		},

		view_phone_number: function () {
			$(".company-phone").each(function () {
				var phone = $(this).find("a").attr("data-phone");
				var text = $(this).find("a").text();
				var el = $(this).find("a");
				var icon = $(this).find("i");
				var icon_view = "fa-eye";
				var icon_close = "fa-eye-slash";

				icon.on("click", function () {
					if (el.text() == text) {
						el.text(phone);
					} else {
						el.text(text);
					}
					if ($(this).hasClass(icon_view)) {
						$(this).removeClass(icon_view);
						$(this).addClass(icon_close);
					} else {
						$(this).removeClass(icon_close);
						$(this).addClass(icon_view);
					}
				});
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

	JOBS.onReady = {
		init: function () {
			JOBS.element.init();
		},
	};

	JOBS.onLoad = {
		init: function () { },
	};

	JOBS.onScroll = {
		init: throttle(function () {
			JOBS.elements.waypoints();
		}, 100), // Throttle scroll events for better performance
	};

	$(window).on('scroll', JOBS.onScroll.init);

	$(document).ready(function () {
		JOBS.elements.init();
	});

	$(window).on('load', JOBS.onLoad.init);
})(jQuery);
