var JOBS = JOBS || {};
(function ($) {
	"use strict";

	JOBS = {
		init: function () {
			this.toggle_insights();
			this.toggle_insights_sidebar();
			this.toggle_overview();
			this.toggle_review();
			this.apply_popup();
			this.civi_alert_message();
			this.civi_crop_image();
			this.popup_job_alerts();
			this.handling_closing_date();
		},

		toggle_insights: function () {
			var $show = $(".show-more-insights");
			var $hide = $(".hide-all-insights");
			var $wrapper = $(".jobs-insights-details");
			$wrapper.addClass("on");
			$show.on('click', function (e) {
				e.preventDefault();
				$wrapper.removeClass("on");
				$hide.show();
				$(this).hide();
			});
			$hide.on('click', function (e) {
				e.preventDefault();
				$wrapper.addClass("on");
				$show.show();
				$(this).hide();
			});
		},

		toggle_insights_sidebar: function () {
			var $show = $(".show-more-insights-sidebar");
			var $hide = $(".hide-all-insights-sidebar");
			var $wrapper = $(".jobs-insights-sidebar");
			$wrapper.addClass("on");
			$show.on('click', function (e) {
				e.preventDefault();
				$wrapper.removeClass("on");
				$hide.show();
				$(this).hide();
			});
			$hide.on('click', function (e) {
				e.preventDefault();
				$wrapper.addClass("on");
				$show.show();
				$(this).hide();
			});
		},

		toggle_overview: function () {
			var $show = $(".show-more-description");
			var $hide = $(".hide-all-description");
			var $wrapper = $(".civi-description-details");
			var $height_des = $(".civi-description").height();
			$wrapper.addClass("on");
			$show.on('click', function (e) {
				e.preventDefault();
				$wrapper.removeClass("on");
				$hide.show();
				$(this).hide();
			});
			$hide.on('click', function (e) {
				e.preventDefault();
				$wrapper.addClass("on");
				$show.show();
				$(this).hide();
			});
			if ($height_des < 330) {
				$wrapper.find(".toggle-description").hide();
			}
		},

		toggle_review: function () {
			var $show = $(".show-more-review");
			var $hide = $(".hide-all-review");
			var $wrapper = $(".civi-review-details");
			var $height_des = $(".civi-review").height();
			$wrapper.addClass("on");
			$show.on('click', function (e) {
				e.preventDefault();
				$wrapper.removeClass("on");
				$hide.show();
				$(this).hide();
			});
			$hide.on('click', function (e) {
				e.preventDefault();
				$wrapper.addClass("on");
				$show.show();
				$(this).hide();
			});
			if ($height_des < 120) {
				$wrapper.find(".toggle-review").hide();
			}
		},

		apply_popup: function () {
			var $form_popup = $('.form-popup-apply');
			var $bottom_bar = $('.civi-apply-bottombar');
			var $btn_close = $form_popup.find(".btn-close");
			var $bg_overlay = $form_popup.find(".bg-overlay");
			var $btn_cancel = $form_popup.find(".button-cancel");

			$form_popup.each(function () {
				var $form_popup_id = $('#' + ($(this).attr('id')));
				var $btn_popup = $('.civi-button-apply.' + ($(this).attr('id')));

				function open_popup(e) {
					e.preventDefault();
					$form_popup_id.css({ opacity: "1", visibility: "unset" });
					if ($bottom_bar.length) {
						$bottom_bar.css({ opacity: "0", visibility: "hidden" });
					}
				}

				function close_popup(e) {
					e.preventDefault();
					$form_popup_id.css({ opacity: "0", visibility: "hidden" });
					if ($bottom_bar.length) {
						$bottom_bar.css({ opacity: "1", visibility: "visible" });
					}
				}

				$btn_popup.on('click', open_popup);
				$bg_overlay.on('click', close_popup);
				$btn_close.on('click', close_popup);
				$btn_cancel.on('click', close_popup);
			});
		},

		civi_alert_message: function () {
			$("body").on("click", ".btn-add-to-message", function (e) {
				e.preventDefault();
				var $text = $(this).data("text");
				var $html = '<div class="civi_alert_message fadeInRight">' + $text + "</div>";
				$("body").find(".civi_alert_message").remove();
				$("body").append($html).fadeIn(500);
				setTimeout(function () {
					$("body").find(".civi_alert_message").removeClass("fadeInRight").addClass("fadeOutRight show");
				}, 2000);
			});
		},

		civi_crop_image: function () {
			var $crop_image = $('.civi_crop_image img'),
				$height_image = $crop_image.attr('height'),
				$width_image = $crop_image.attr('width');
			$crop_image.css({ 'height': $height_image, 'width': $width_image, 'object-fit': 'cover' });
		},

		popup_job_alerts: function () {
			$('.alert-form').each(function () {
				var _this = $(this);
				if (sessionStorage.getItem("hide-alert-form") == 'true') {
					_this.fadeOut(0);
				} else {
					_this.fadeIn(0);
					var close = _this.find('.close');
					close.on('click', function (e) {
						e.preventDefault();
						sessionStorage.setItem("hide-alert-form", 'true');
						_this.fadeOut(0);
					});
				}
			});
		},

		handling_closing_date: function () {
			const $closingDateInput = $('#jobs_closing_date');
			const $daysClosingInput = $('#jobs_days_closing');
			const $remainingDaysText = $('#remaining_days_text');

			if (!$closingDateInput.length) {
				return;
			}

			// Constants
			const DATE_FORMATS = {
				ISO: /^\d{4}-\d{2}-\d{2}$/,
				SLASH: /^\d{1,2}\/\d{1,2}\/\d{4}$/,
				DASH: /^\d{1,2}-\d{1,2}-\d{4}$/
			};

			const MS_PER_DAY = 1000 * 3600 * 24;

			/**
			 * Parse date từ string với nhiều format khác nhau
			 * @param {string} dateString
			 * @returns {Date|null}
			 */
			function parseDateFromString(dateString) {
				if (!dateString || typeof dateString !== 'string') {
					return null;
				}

				const trimmed = dateString.trim();
				if (!trimmed) {
					return null;
				}

				let dateObj = null;

				try {
					// Format ISO: YYYY-MM-DD (ưu tiên)
					if (DATE_FORMATS.ISO.test(trimmed)) {
						const [year, month, day] = trimmed.split('-').map(Number);
						dateObj = new Date(year, month - 1, day);
					}
					// Format slash: DD/MM/YYYY hoặc MM/DD/YYYY
					else if (DATE_FORMATS.SLASH.test(trimmed)) {
						dateObj = parseSlashFormat(trimmed);
					}
					// Format dash: DD-MM-YYYY
					else if (DATE_FORMATS.DASH.test(trimmed)) {
						const [day, month, year] = trimmed.split('-').map(Number);
						dateObj = new Date(year, month - 1, day);
					}
					// Fallback to native Date parsing
					else {
						dateObj = new Date(trimmed);
					}

					// Validate date
					if (!dateObj || isNaN(dateObj.getTime())) {
						return null;
					}

					return dateObj;
				} catch (e) {
					console.error('Error parsing date:', e);
					return null;
				}
			}

			/**
			 * Parse format DD/MM/YYYY hoặc MM/DD/YYYY
			 * @param {string} dateString
			 * @returns {Date|null}
			 */
			function parseSlashFormat(dateString) {
				const parts = dateString.split('/').map(Number);
				if (parts.length !== 3) {
					return null;
				}

				const [part1, part2, part3] = parts;

				// Xác định format dựa vào giá trị
				if (part1 > 12) {
					// Chắc chắn là DD/MM/YYYY
					return new Date(part3, part2 - 1, part1);
				} else if (part2 > 12) {
					// Chắc chắn là MM/DD/YYYY
					return new Date(part3, part1 - 1, part2);
				} else {
					// Ambiguous - thử DD/MM/YYYY trước (format phổ biến hơn)
					const dateDDMM = new Date(part3, part2 - 1, part1);
					if (dateDDMM.getDate() === part1 && dateDDMM.getMonth() === part2 - 1) {
						return dateDDMM;
					}

					// Fallback to MM/DD/YYYY
					const dateMMDD = new Date(part3, part1 - 1, part2);
					return dateMMDD;
				}
			}

			/**
			 * Tính số ngày còn lại từ hôm nay đến ngày được chọn
			 * @param {Date} selectedDate
			 * @returns {number}
			 */
			function calculateDaysRemaining(selectedDate) {
				const today = new Date();
				today.setHours(0, 0, 0, 0);
				selectedDate.setHours(0, 0, 0, 0);

				const timeDiff = selectedDate.getTime() - today.getTime();
				return Math.round(timeDiff / MS_PER_DAY);
			}

			/**
			 * Lấy text hiển thị số ngày còn lại
			 * @param {number} daysDiff
			 * @returns {string}
			 */
			function getExpiryText(daysDiff) {
				const hasI18n = typeof civi_jobs_vars !== 'undefined';

				if (daysDiff <= 0) {
					return hasI18n ? civi_jobs_vars.expires_past_date : 'Expires in past date';
				} else if (daysDiff === 1) {
					return hasI18n ? civi_jobs_vars.expires_one_day : 'Expires in 1 day';
				} else {
					return hasI18n
						? civi_jobs_vars.expires_many_days.replace('%d', daysDiff)
						: `Expires in ${daysDiff} days`;
				}
			}

			/**
			 * Update UI với số ngày còn lại
			 */
			function calculateAndUpdateDays() {
				let selectedDate = null;

				// Ưu tiên lấy Date object từ Flatpickr instance (chính xác hơn)
				const fpInstance = $closingDateInput.data('flatpickr');
				if (fpInstance && fpInstance.selectedDates && fpInstance.selectedDates.length > 0) {
					selectedDate = fpInstance.selectedDates[0];
				}

				// Fallback: parse từ string value nếu không có Date object
				if (!selectedDate) {
					const dateValue = $closingDateInput.val();
					if (!dateValue) {
						return;
					}
					selectedDate = parseDateFromString(dateValue);
				}

				if (!selectedDate || isNaN(selectedDate.getTime())) {
					if ($remainingDaysText.length) {
						$remainingDaysText.text('Invalid date');
					}
					return;
				}

				const daysDiff = calculateDaysRemaining(selectedDate);

				// Update hidden input
				if ($daysClosingInput.length) {
					$daysClosingInput.val(Math.max(0, daysDiff));
				}

				// Update display text
				if ($remainingDaysText.length) {
					$remainingDaysText.text(getExpiryText(daysDiff));
				}
			}

			/**
			 * Khởi tạo giá trị cho Flatpickr từ attribute value
			 */
			function initializeFlatpickrValue() {
				const fpInstance = $closingDateInput.data('flatpickr');
				const rawValue = $closingDateInput.attr('value');

				if (!fpInstance || !rawValue) {
					return false;
				}

				try {
					const dateToSet = parseDateFromString(rawValue);

					if (!dateToSet || isNaN(dateToSet.getTime())) {
						return false;
					}

					const currentDate = fpInstance.selectedDates?.[0];

					// Check if current date is different from date to set
					const shouldUpdate = !currentDate ||
						currentDate.getFullYear() !== dateToSet.getFullYear() ||
						currentDate.getMonth() !== dateToSet.getMonth() ||
						currentDate.getDate() !== dateToSet.getDate();

					if (shouldUpdate) {
						// Set date with Date object - Flatpickr will automatically format according to config
						fpInstance.setDate(dateToSet, true);

						// Ensure input displays the correct format
						setTimeout(function() {
							if (!fpInstance.selectedDates || fpInstance.selectedDates.length === 0) {
								// Retry with raw value string if Date object doesn't work
								fpInstance.setDate(rawValue, true);
							}
							calculateAndUpdateDays();
						}, 100);
					} else {
						// Trigger update UI even if date doesn't need to be updated
						setTimeout(calculateAndUpdateDays, 50);
					}

					return true;

				} catch (e) {
					console.error('Error initializing flatpickr date:', e);
					return false;
				}
			}

			/**
			 * Retry initialization với exponential backoff
			 */
			function retryFlatpickrInit(maxRetries = 3, delay = 200) {
				let attempts = 0;

				function attempt() {
					attempts++;
					const success = initializeFlatpickrValue();

					if (!success && attempts < maxRetries) {
						const nextDelay = delay * Math.pow(1.5, attempts - 1);
						setTimeout(attempt, nextDelay);
					}
				}

				attempt();
			}

			// Event listeners
			$closingDateInput.on('change', calculateAndUpdateDays);

			// Initialize with retry logic
			retryFlatpickrInit();
		}
	};
	$(document).ready(function () {
		JOBS.init();
	});
})(jQuery);
