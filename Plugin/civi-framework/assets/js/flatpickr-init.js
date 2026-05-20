"use strict";

(function($) {
	if (typeof $ === "undefined" || typeof window.jQuery === "undefined") {
		return;
	}

	// Constants
	const DEFAULTS = {
		LOCALE: "en",
		DATE_FORMAT: "Y-m-d",
		SELECTOR: ".datepicker, input[type='date']",
		INIT_DELAY: 100,
		RETRY_DELAY: 100,
		DEBOUNCE_DELAY: 150
	};

	const DATE_FORMATS = {
		ISO: "Y-m-d",
		DMY_SLASH: "d/m/Y",
		MDY_SLASH: "m/d/Y",
		DMY_DOT: "d.m.Y",
		DMY_DASH: "d-m-Y",
		US_LONG: "F j, Y",
		JP_CN: "Y年m月d日",
		KO: "Y년 m월 d일"
	};

	// Locale groups để giảm repetition
	const LOCALE_GROUPS = {
		CJK: ["zh", "zh-cn", "zh-tw", "zh_CN", "zh_TW", "ja"],
		KOREAN: ["ko", "ko-kr", "ko_KR"],
		DMY_SLASH: ["vi", "vn", "th", "id", "fr", "es", "it", "pt", "tr", "ar", "he", "af", "sw", "bn"],
		DMY_DOT: ["de", "ru", "pl"],
		DMY_DASH: ["nl"],
		SPANISH: ["es-mx", "es-ar", "es-co", "es-cl", "es-pe", "es-ve", "es-cr", "es-ec", "es-gt", "es-pa", "es-do", "es-cu", "es-bo", "es-hn", "es-ni", "es-py", "es-sv", "es-uy"],
		PORTUGUESE: ["pt-br", "pt-pt", "pt_BR", "pt_PT"],
		AFRICAN: ["af-za", "zu", "xh", "en-ng", "en_NG", "en-zw", "en_ZW"],
		BENGALI: ["bn-bd", "bn_BD", "bd"]
	};

	/**
	 * Utility functions
	 */
	const Utils = {
		/**
		 * Debounce function để tránh call nhiều lần
		 */
		debounce: function(func, wait) {
			let timeout;
			return function executedFunction(...args) {
				const later = () => {
					clearTimeout(timeout);
					func(...args);
				};
				clearTimeout(timeout);
				timeout = setTimeout(later, wait);
			};
		},

		/**
		 * Get locale code từ nhiều nguồn
		 */
		getLocaleConfig: function() {
			try {
				// Priority 1: URL params
				const urlLocale = this.getUrlParam("flatpickr_locale");
				if (urlLocale) {
					return { locale: urlLocale, source: "url" };
				}

				// Priority 2: civi_flatpickr_vars
				if (typeof civi_flatpickr_vars !== "undefined") {
					return {
						locale: civi_flatpickr_vars.locale || DEFAULTS.LOCALE,
						wpLocale: civi_flatpickr_vars.wp_locale,
						dateFormat: civi_flatpickr_vars.date_format || DEFAULTS.DATE_FORMAT,
						source: "flatpickr_vars"
					};
				}

				// Priority 3: civi_template_vars
				if (typeof civi_template_vars !== "undefined") {
					return {
						locale: civi_template_vars.flatpickr_locale || DEFAULTS.LOCALE,
						wpLocale: civi_template_vars.wp_locale,
						dateFormat: civi_template_vars.date_format || DEFAULTS.DATE_FORMAT,
						source: "template_vars"
					};
				}

				// Priority 4: Browser locale
				const browserLocale = (navigator.language || navigator.userLanguage || "en").substring(0, 2);
				return {
					locale: browserLocale,
					wpLocale: browserLocale,
					dateFormat: DEFAULTS.DATE_FORMAT,
					source: "browser"
				};
			} catch (e) {
				console.error("CiviFlatpickr: Error reading locale config", e);
				return {
					locale: DEFAULTS.LOCALE,
					dateFormat: DEFAULTS.DATE_FORMAT,
					source: "fallback"
				};
			}
		},

		/**
		 * Get URL parameter
		 */
		getUrlParam: function(name) {
			try {
				if (typeof URLSearchParams !== "undefined") {
					const urlParams = new URLSearchParams(window.location.search);
					return urlParams.get(name);
				}
				const match = window.location.search.match(new RegExp(`[?&]${name}=([^&]*)`));
				return match ? match[1] : null;
			} catch (e) {
				return null;
			}
		},

		/**
		 * Check element should be initialized
		 */
		shouldInitElement: function($el) {
			return !$el.data("flatpickr") &&
				   !$el.data("flatpickr-initialized") &&
				   !$el.hasClass("no-flatpickr");
		},

		/**
		 * Parse date từ string
		 */
		parseDate: function(dateStr) {
			if (!dateStr) return null;

			try {
				const dateObj = new Date(dateStr);
				return !isNaN(dateObj.getTime()) ? dateObj : null;
			} catch (e) {
				return null;
			}
		}
	};

	/**
	 * Locale Manager - xử lý locale và format
	 */
	const LocaleManager = {
		/**
		 * Lấy format dựa trên locale
		 */
		getFormatByLocale: function(localeCode) {
			// Normalize locale code
			const normalizedLocale = localeCode.toLowerCase();

			// CJK locales
			if (LOCALE_GROUPS.CJK.some(l => normalizedLocale.startsWith(l))) {
				return DATE_FORMATS.JP_CN;
			}

			// Korean
			if (LOCALE_GROUPS.KOREAN.some(l => normalizedLocale.startsWith(l))) {
				return DATE_FORMATS.KO;
			}

			// DMY with slash
			if (LOCALE_GROUPS.DMY_SLASH.some(l => normalizedLocale.startsWith(l)) ||
				LOCALE_GROUPS.SPANISH.some(l => normalizedLocale === l) ||
				LOCALE_GROUPS.PORTUGUESE.some(l => normalizedLocale === l) ||
				LOCALE_GROUPS.AFRICAN.some(l => normalizedLocale === l) ||
				LOCALE_GROUPS.BENGALI.some(l => normalizedLocale === l)) {
				return DATE_FORMATS.DMY_SLASH;
			}

			// DMY with dot
			if (LOCALE_GROUPS.DMY_DOT.some(l => normalizedLocale.startsWith(l))) {
				return DATE_FORMATS.DMY_DOT;
			}

			// DMY with dash
			if (LOCALE_GROUPS.DMY_DASH.some(l => normalizedLocale.startsWith(l))) {
				return DATE_FORMATS.DMY_DASH;
			}

			// Default
			return DATE_FORMATS.ISO;
		},

		/**
		 * Get flatpickr locale object
		 */
		getFlatpickrLocale: function(localeCode, wpLocale) {
			try {
				if (localeCode === "en" || typeof flatpickr.l10ns === "undefined") {
					return null;
				}

				// Direct match
				if (flatpickr.l10ns[localeCode]) {
					return flatpickr.l10ns[localeCode];
				}

				// Chinese variations
				if (localeCode === "zh") {
					return flatpickr.l10ns["zh-cn"] || flatpickr.l10ns["zh_tw"] || null;
				}

				// Language family fallback
				const baseLocale = localeCode.split(/[-_]/)[0];
				if (flatpickr.l10ns[baseLocale]) {
					return flatpickr.l10ns[baseLocale];
				}

				// Try wpLocale
				if (wpLocale && flatpickr.l10ns[wpLocale]) {
					return flatpickr.l10ns[wpLocale];
				}

				return null;
			} catch (e) {
				console.warn("CiviFlatpickr: Error loading locale", localeCode, e);
				return null;
			}
		},

		/**
		 * Convert WP date format to Flatpickr format
		 */
		convertDateFormat: function(wpFormat) {
			if (!wpFormat) return DATE_FORMATS.ISO;

			// Common formats mapping
			const formatMap = {
				"F j, Y": DATE_FORMATS.US_LONG,
				"Y-m-d": DATE_FORMATS.ISO,
				"Y/m/d": DATE_FORMATS.ISO,
				"d/m/Y": DATE_FORMATS.DMY_SLASH,
				"d-m-Y": DATE_FORMATS.DMY_DASH,
				"m/d/Y": DATE_FORMATS.MDY_SLASH,
				"m-d-Y": DATE_FORMATS.MDY_SLASH,
				"d.m.Y": DATE_FORMATS.DMY_DOT
			};

			if (formatMap[wpFormat]) {
				return formatMap[wpFormat];
			}

			// Detect separator và order
			if (wpFormat.includes("/")) {
				return this.detectFormatOrder(wpFormat, "/");
			} else if (wpFormat.includes("-")) {
				return this.detectFormatOrder(wpFormat, "-");
			} else if (wpFormat.includes(".")) {
				return DATE_FORMATS.DMY_DOT;
			}

			return DATE_FORMATS.ISO;
		},

		/**
		 * Detect date format order (YMD, DMY, MDY)
		 */
		detectFormatOrder: function(format, separator) {
			const yPos = format.indexOf("Y");
			const mPos = format.indexOf("m");
			const dPos = format.indexOf("d");

			if (yPos < mPos && mPos < dPos) {
				return `Y${separator}m${separator}d`;
			} else if (dPos < mPos && mPos < yPos) {
				return `d${separator}m${separator}Y`;
			} else if (mPos < dPos && dPos < yPos) {
				return `m${separator}d${separator}Y`;
			}

			return DATE_FORMATS.ISO;
		},

		/**
		 * Get final date format
		 */
		getDateFormat: function(options) {
			const { localeCode, wpLocale, wpDateFormat, localeObj } = options;

			// Priority 1: Locale object has format
			if (localeObj?.dateFormat) {
				return localeObj.dateFormat;
			}

			// Priority 2: Locale-specific format
			const localeFormat = this.getFormatByLocale(localeCode);

			// Priority 3: For CJK languages, always use their format
			if (localeFormat === DATE_FORMATS.JP_CN || localeFormat === DATE_FORMATS.KO) {
				return localeFormat;
			}

			// Priority 4: Convert WP format
			const convertedFormat = this.convertDateFormat(wpDateFormat);

			// Priority 5: US locale with F j, Y format
			if (wpDateFormat === "F j, Y" && ["en", "en_US", "en_GB"].includes(localeCode)) {
				return DATE_FORMATS.US_LONG;
			}

			// Use converted format if compatible, otherwise use locale format
			return this.isFormatCompatible(wpDateFormat, localeFormat)
				? convertedFormat
				: localeFormat;
		},

		/**
		 * Check if formats are compatible
		 */
		isFormatCompatible: function(wpFormat, localeFormat) {
			// ISO vs DMY/MDY incompatible
			if (wpFormat === "Y-m-d" && (localeFormat.includes("d/m") || localeFormat.includes("m/d"))) {
				return false;
			}

			// DMY/MDY vs ISO incompatible
			if ((wpFormat.includes("d/m") || wpFormat.includes("m/d")) && localeFormat === "Y-m-d") {
				return false;
			}

			// CJK formats
			if (wpFormat.includes("年") || wpFormat.includes("월")) {
				return true;
			}

			return true;
		}
	};

	/**
	 * Main CiviFlatpickr object
	 */
	window.CiviFlatpickr = {
		initialized: false,
		initInProgress: false,

		/**
		 * Auto initialize
		 */
		autoInit: function() {
			// Check if flatpickr library loaded
			if (typeof flatpickr === "undefined") {
				if (!this.waitTimeout) {
					this.waitTimeout = setTimeout(() => {
						this.waitTimeout = null;
						this.autoInit();
					}, DEFAULTS.RETRY_DELAY);
				}
				return;
			}

			const config = Utils.getLocaleConfig();
			this.init({
				localeCode: config.locale,
				wpLocale: config.wpLocale,
				wpDateFormat: config.dateFormat || DEFAULTS.DATE_FORMAT,
				selector: DEFAULTS.SELECTOR
			});
		},

		/**
		 * Initialize flatpickr
		 */
		init: function(options) {
			// Validation
			if (typeof flatpickr === "undefined") {
				console.warn("CiviFlatpickr: flatpickr library not loaded");
				return;
			}

			if (typeof $ === "undefined") {
				console.warn("CiviFlatpickr: jQuery not loaded");
				return;
			}

			// Prevent concurrent init
			if (this.initInProgress) {
				return;
			}

			this.initInProgress = true;

			try {
				// Merge options
				const defaultOptions = {
					localeCode: DEFAULTS.LOCALE,
					wpLocale: DEFAULTS.LOCALE,
					wpDateFormat: DEFAULTS.DATE_FORMAT,
					selector: DEFAULTS.SELECTOR
				};

				options = $.extend({}, defaultOptions, options || {});

				// Get locale object
				const localeObj = LocaleManager.getFlatpickrLocale(
					options.localeCode,
					options.wpLocale
				);

				// Get date format
				const dateFormat = LocaleManager.getDateFormat({
					localeCode: options.localeCode,
					wpLocale: options.wpLocale,
					wpDateFormat: options.wpDateFormat,
					localeObj: localeObj
				});

				// Build flatpickr config
				const flatpickrConfig = this.buildFlatpickrConfig({
					dateFormat,
					localeObj,
					minDate: options.minDate,
					maxDate: options.maxDate
				});

				// Initialize elements
				this.initializeElements(options.selector, flatpickrConfig);

			} catch (e) {
				console.error("CiviFlatpickr: Error in init", e);
			} finally {
				this.initInProgress = false;
			}
		},

		/**
		 * Build flatpickr configuration
		 */
		buildFlatpickrConfig: function(options) {
			const { dateFormat, localeObj, minDate, maxDate } = options;

			const config = {
				dateFormat: dateFormat,
				allowInput: true,
				clickOpens: true,
				animate: true,
				monthSelectorType: "static",
				onChange: this.createOnChangeHandler(),
				onReady: this.createOnReadyHandler()
			};

			// Add locale
			if (localeObj) {
				config.locale = localeObj;

				if (localeObj.rtl === true) {
					config.wrap = true;
					config.position = "auto";
				}
			}

			// Add date constraints
			if (minDate) config.minDate = minDate;
			if (maxDate) config.maxDate = maxDate;

			return config;
		},

		/**
		 * Create onChange callback
		 */
		createOnChangeHandler: function() {
			return function(selectedDates, dateStr, instance) {
				try {
					const $input = $(instance.input);
					if ($input.length) {
						$input.val(dateStr).trigger("change");
					}
				} catch (e) {
					console.warn("CiviFlatpickr: Error in onChange", e);
				}
			};
		},

		/**
		 * Create onReady callback
		 */
		createOnReadyHandler: function() {
			return function(selectedDates, dateStr, instance) {
				try {
					const $input = $(instance.input);
					if (!$input.length) return;

					$input.data("flatpickr-initialized", true);

					// Set initial value if exists
					const originalValue = $input.attr('value') || $input.data('debug-value');
					if (originalValue && (!selectedDates || selectedDates.length === 0)) {
						setTimeout(() => {
							const dateObj = Utils.parseDate(originalValue);
							if (dateObj) {
								instance.setDate(dateObj, true);
							}
						}, DEFAULTS.INIT_DELAY);
					}
				} catch (e) {
					console.warn("CiviFlatpickr: Error in onReady", e);
				}
			};
		},

		/**
		 * Initialize elements
		 */
		initializeElements: function(selector, baseConfig) {
			try {
				const $elements = $(selector).filter(function() {
					return Utils.shouldInitElement($(this));
				});

				if ($elements.length === 0) {
					return;
				}

				$elements.each((index, element) => {
					try {
						this.initializeSingleElement(element, baseConfig);
					} catch (e) {
						console.warn("CiviFlatpickr: Error initializing element", element, e);
					}
				});
			} catch (e) {
				console.error("CiviFlatpickr: Error in initializeElements", e);
			}
		},

		/**
		 * Initialize single element
		 */
		initializeSingleElement: function(element, baseConfig) {
			const $input = $(element);
			const config = { ...baseConfig };

			// Handle input[type="date"]
			if ($input.attr("type") === "date") {
				const minDate = $input.attr("min");
				const maxDate = $input.attr("max");

				$input.attr("type", "text").addClass("datepicker");

				if (minDate) config.minDate = minDate;
				if (maxDate) config.maxDate = maxDate;
			}

			// Set default date
			const inputValue = $input.val();
			if (inputValue) {
				config.defaultDate = inputValue;
			}

			// Mobile / touch: native picker path can break inside overflow/transform; use flatpickr on body.
			const mobileLike =
				typeof window.matchMedia === "function" &&
				(window.matchMedia("(max-width: 782px)").matches ||
					window.matchMedia("(pointer: coarse)").matches);
			if (mobileLike && typeof document !== "undefined" && document.body) {
				config.disableMobile = true;
				config.appendTo = document.body;
			}

			// Initialize flatpickr
			const fpInstance = flatpickr(element, config);

			if (fpInstance) {
				$input.data("flatpickr", fpInstance);

				// Set value if not already set
				if (inputValue && !fpInstance.selectedDates.length) {
					setTimeout(() => {
						const dateObj = Utils.parseDate(inputValue);
						if (dateObj) {
							fpInstance.setDate(dateObj, true);
						}
					}, DEFAULTS.INIT_DELAY);
				}
			}
		}
	};

	/**
	 * DOM Observer - Quản lý việc observe DOM changes
	 */
	const DOMObserver = {
		observer: null,
		debouncedInit: null,

		init: function() {
			if (typeof MutationObserver === "undefined") {
				console.warn("CiviFlatpickr: MutationObserver not supported");
				return;
			}

			// Create debounced init function
			this.debouncedInit = Utils.debounce(() => {
				if (typeof window.CiviFlatpickr !== "undefined") {
					window.CiviFlatpickr.autoInit();
				}
			}, DEFAULTS.DEBOUNCE_DELAY);

			// Create observer
			this.observer = new MutationObserver((mutations) => {
				if (this.shouldInit(mutations)) {
					this.debouncedInit();
				}
			});

			// Start observing
			this.observer.observe(document.body, {
				childList: true,
				subtree: true
			});
		},

		shouldInit: function(mutations) {
			return mutations.some(mutation => {
				if (!mutation.addedNodes.length) return false;

				return Array.from(mutation.addedNodes).some(node => {
					if (node.nodeType !== 1) return false;

					const $node = $(node);
					return $node.is(DEFAULTS.SELECTOR) ||
						   $node.find(DEFAULTS.SELECTOR).length > 0;
				});
			});
		},

		disconnect: function() {
			if (this.observer) {
				this.observer.disconnect();
			}
		}
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function() {
		// Initial init
		if (typeof window.CiviFlatpickr !== "undefined") {
			window.CiviFlatpickr.autoInit();
		}

		// Setup DOM observer
		DOMObserver.init();

		// Handle AJAX complete
		const debouncedAjaxInit = Utils.debounce(() => {
			if (typeof window.CiviFlatpickr !== "undefined") {
				window.CiviFlatpickr.autoInit();
			}
		}, DEFAULTS.DEBOUNCE_DELAY);

		$(document).on("ajaxComplete", debouncedAjaxInit);
	});

})(jQuery);
