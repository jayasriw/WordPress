(function ($) {
	"use strict";

	$.fn.CiviSwiper = function (options) {
		var defaults = {};
		var settings = $.extend({}, defaults, options);

		var $swiper;

		this.each(function () {
			var $slider = $(this);
			var $sliderInner = $slider.children(".swiper-inner").first();
			var sliderSettings = $slider.data();

			var $sliderContainer = $sliderInner.children(".swiper-container").first(),
				lgItems = sliderSettings.lgItems ? sliderSettings.lgItems : 1,
				mdItems = sliderSettings.mdItems ? sliderSettings.mdItems : lgItems,
				smItems = sliderSettings.smItems ? sliderSettings.smItems : mdItems,
				lgGutter = sliderSettings.lgGutter ? sliderSettings.lgGutter : 0,
				mdGutter = sliderSettings.mdGutter ? sliderSettings.mdGutter : lgGutter,
				smGutter = sliderSettings.smGutter ? sliderSettings.smGutter : mdGutter,
				speed = sliderSettings.speed ? sliderSettings.speed : 1000;

			// Normalize slide per view, reset fake view to exist view.
			lgItems = "auto-fixed" === lgItems ? "auto" : lgItems;
			mdItems = "auto-fixed" === mdItems ? "auto" : mdItems;
			smItems = "auto-fixed" === smItems ? "auto" : smItems;

			var swiperOptions = $.extend(
				{},
				{
					// init: false is no longer needed in Swiper 8
					watchSlidesProgress: true, // Changed from watchSlidesVisibility
					slidesPerView: smItems,
					spaceBetween: smGutter,
					breakpoints: {
						// when window width is >=
						767: {
							slidesPerView: mdItems,
							spaceBetween: mdGutter,
						},
						1024: {
							slidesPerView: lgItems,
							spaceBetween: lgGutter,
						},
					},
				},
				settings
			);

			if (sliderSettings.slidesPerGroup == "inherit") {
				swiperOptions.slidesPerGroup = smItems;

				swiperOptions.breakpoints[767].slidesPerGroup = mdItems;
				swiperOptions.breakpoints[1024].slidesPerGroup = lgItems;
			}

			swiperOptions.watchOverflow = true;

			if (sliderSettings.slideColumns) {
				swiperOptions.slidesPerColumn = sliderSettings.slideColumns;
				swiperOptions.slidesPerColumnFill = 'row'; // Default value for Swiper 8
			}

			if (sliderSettings.initialSlide) {
				swiperOptions.initialSlide = sliderSettings.initialSlide;
			}

			if (sliderSettings.autoHeight) {
				swiperOptions.autoHeight = true;
			}

			if (
				typeof sliderSettings.simulateTouch !== "undefined" &&
				!sliderSettings.simulateTouch
			) {
				swiperOptions.simulateTouch = false;
			}

			if (speed) {
				swiperOptions.speed = speed;
			}

			// Maybe: fade, flip
			if (sliderSettings.effect) {
				swiperOptions.effect = sliderSettings.effect;

				if ('fade' === sliderSettings.effect) {
					if ("custom" === sliderSettings.fadeEffect) {
						swiperOptions.fadeEffect = {
							crossFade: false,
						};
					} else {
						swiperOptions.fadeEffect = {
							crossFade: true,
						};
					}
				}
			}

			if (sliderSettings.loop) {
				swiperOptions.loop = true;

				if (sliderSettings.loopedSlides) {
					swiperOptions.loopedSlides = sliderSettings.loopedSlides;
				}
			}

			if (sliderSettings.centered) {
				swiperOptions.centeredSlides = true;
			}

			if (sliderSettings.autoplay) {
				swiperOptions.autoplay = {
					delay: sliderSettings.autoplay,
					disableOnInteraction: false,
				};
			}

			if (sliderSettings.freeMode) {
				swiperOptions.freeMode = {
					enabled: true // Swiper 8 requires enabled property
				};
			}

			var $wrapControls;

			if (sliderSettings.wrapControls) {
				var $wrapControlsWrap = $('<div class="swiper-controls-wrap"></div>');
				$wrapControls = $('<div class="swiper-controls"></div>');

				$wrapControlsWrap.append($wrapControls);
				$slider.append($wrapControlsWrap);
			}

			if (sliderSettings.nav) {
				let navSelectors = {};

				if (sliderSettings.customNav && sliderSettings.customNav !== "") {
					navSelectors.nextEl = '#' + sliderSettings.customNav + ' .slider-next-btn';
					navSelectors.prevEl = '#' + sliderSettings.customNav + ' .slider-prev-btn';
				} else {
					var $swiperPrev = $(
						'<div class="swiper-nav-button swiper-button-prev"><i class="nav-button-icon"></i><span class="nav-button-text">' +
							$civiSwiper.prevText +
							"</span></div>"
					);
					var $swiperNext = $(
						'<div class="swiper-nav-button swiper-button-next"><i class="nav-button-icon"></i><span class="nav-button-text">' +
							$civiSwiper.nextText +
							"</span></div>"
					);

					// Gán class duy nhất để tránh xung đột
					var uniqueId = 'civi-swiper-' + Math.random().toString(36).substr(2, 5);
					$swiperPrev.addClass(uniqueId + '-prev');
					$swiperNext.addClass(uniqueId + '-next');

					var $swiperNavButtons = $('<div class="swiper-nav-buttons"></div>');
					$swiperNavButtons.append($swiperPrev).append($swiperNext);

					var $swiperNavButtonsWrap = $(
						'<div class="swiper-nav-buttons-wrap"></div>'
					);

					if ("grid" == sliderSettings.navAlignedBy) {
						$swiperNavButtonsWrap.append(
							'<div class="container"><div class="row"><div class="col-sm-12"></div></div></div>'
						);
						$swiperNavButtonsWrap.find(".col-sm-12").append($swiperNavButtons);
					} else {
						$swiperNavButtonsWrap.append($swiperNavButtons);
					}

					if ($wrapControls) {
						$wrapControls.append($swiperNavButtonsWrap);
					} else {
						$sliderInner.append($swiperNavButtonsWrap);
					}

					navSelectors.nextEl = '.' + uniqueId + '-next';
					navSelectors.prevEl = '.' + uniqueId + '-prev';
				}

				swiperOptions.navigation = navSelectors;
			}

			if (sliderSettings.pagination) {
				var $swiperPaginationWrap = $(
					'<div class="swiper-pagination-wrap"><div class="swiper-pagination-inner"></div></div>'
				);
				var $swiperPagination = $('<div class="swiper-pagination"></div>');

				$swiperPaginationWrap
					.find(".swiper-pagination-inner")
					.append($swiperPagination);

				var $swiperPaginationContainerWrap = $(
					'<div class="swiper-pagination-container"></div>'
				);

				if ("grid" == sliderSettings.paginationAlignedBy) {
					$swiperPaginationContainerWrap.append(
						'<div class="container"><div class="row"><div class="col-sm-12"></div></div></div>'
					);
					$swiperPaginationContainerWrap
						.find(".col-sm-12")
						.append($swiperPaginationWrap);
				} else {
					$swiperPaginationContainerWrap.append($swiperPaginationWrap);
				}

				if ($wrapControls) {
					$wrapControls.append($swiperPaginationContainerWrap);
				} else {
					$slider.append($swiperPaginationContainerWrap);
				}

				var paginationType = "bullets";

				if (sliderSettings.paginationType) {
					paginationType = sliderSettings.paginationType;
				}

				swiperOptions.pagination = {
					el: $swiperPagination[0], // Swiper 8 expects DOM element
					type: paginationType,
					clickable: true,
				};

				if ($slider.hasClass("pagination-style-04")) {
					var $swiperAltArrows = $(
						'<div class="swiper-alt-arrow-button swiper-alt-arrow-prev" data-action="prev"></div><div class="swiper-alt-arrow-button swiper-alt-arrow-next" data-action="next"></div>'
					);

					$swiperPaginationWrap
						.find(".swiper-pagination-inner")
						.append($swiperAltArrows);

					swiperOptions.pagination.renderCustom = function (
						swiper,
						current,
						total
					) {
						// Convert to string.
						var currentStr = current.toString();
						var totalStr = total.toString();

						return (
							'<div class="fraction"><div class="current">' +
							currentStr +
							'</div><div class="separator">/</div><div class="total">' +
							totalStr +
							"</div></div>"
						);
					};
				} else if ($slider.hasClass("pagination-style-03")) {
					swiperOptions.pagination.renderCustom = function (
						swiper,
						current,
						total
					) {
						// Convert to string.
						var currentStr = current.toString();
						var totalStr = total.toString();

						// Add leading 0.
						currentStr = currentStr.padStart(2, "0");
						totalStr = totalStr.padStart(2, "0");

						return (
							'<div class="fraction"><div class="current">' +
							currentStr +
							'</div><div class="separator"></div><div class="total">' +
							totalStr +
							"</div></div>"
						);
					};
				} else if ($slider.hasClass("pagination-style-06")) {
					swiperOptions.pagination.renderCustom = function (
						swiper,
						current,
						total
					) {
						// Convert to string.
						var currentStr = current.toString();
						var totalStr = total.toString();

						// Add leading 0.
						currentStr = currentStr.padStart(2, "0");
						totalStr = totalStr.padStart(2, "0");

						return (
							'<div class="fraction"><div class="current">' +
							currentStr +
							'<div class="separator">/</div></div><div class="total">' +
							totalStr +
							"</div></div>"
						);
					};
				}
			}

			if (sliderSettings.scrollbar) {
				var $scrollbar = $('<div class="swiper-scrollbar"></div>');
				$sliderContainer.prepend($scrollbar);

				swiperOptions.scrollbar = {
					el: $scrollbar[0], // Swiper 8 expects DOM element
					draggable: true,
				};

				swiperOptions.loop = false;
			}

			if (sliderSettings.mousewheel) {
				swiperOptions.mousewheel = {
					enabled: true,
				};
			}

			if (sliderSettings.vertical) {
				swiperOptions.direction = "vertical";
			}

			if (sliderSettings.slideToClickedSlide) {
				swiperOptions.slideToClickedSlide = true;
				swiperOptions.touchRatio = 0.2;
			}

			if (sliderSettings.layerTransition) {
				// Event listeners for Swiper 8
				swiperOptions.on = {
					init: function () {
						var index = this.activeIndex;
						var slides = $(this.wrapperEl).find('.swiper-slide');
						var currentSlide = slides.eq(index);
						currentSlide.addClass('animated');
					},
					slideChangeTransitionStart: function () {
						var slides = $(this.wrapperEl).find('.swiper-slide');
						slides.removeClass('animated');
					},
					slideChangeTransitionEnd: function () {
						var index = this.activeIndex;
						var slides = $(this.wrapperEl).find('.swiper-slide');
						var currentSlide = slides.eq(index);
						currentSlide.addClass('animated');
					}
				};
			}

			// Initialize Swiper directly without init: false
			$swiper = new Swiper($sliderContainer[0], swiperOptions); // Pass DOM element, not jQuery object

			if ($slider.hasClass("pagination-style-04")) {
				$slider.on("click", ".swiper-alt-arrow-button", function () {
					var action = $(this).data("action");

					switch (action) {
						case "prev":
							$swiper.slidePrev();
							break;
						case "next":
							$swiper.slideNext();
							break;
					}
				});
			}

			// Disabled auto play when focus.
			/*if ( sliderSettings.autoplay ) {
					$sliderContainer.hover( function() {
						$swiper.autoplay.stop();
					}, function() {
						$swiper.autoplay.start();
					} );
				}*/

			$(document).trigger("CiviSwiperInit", [$swiper, $slider, swiperOptions]);
		});

		return $swiper;
	};
})(jQuery);