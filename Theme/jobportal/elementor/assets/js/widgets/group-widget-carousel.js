(function ($) {
	"use strict";

	var SwiperHandler = function ($scope, $) {
		var $element = $scope.find(".jobportal-slider-widget");

		$element.JobPortalSwiper();
	};

	var SwiperLinkedHandler = function ($scope, $) {
		var $element = $scope.find(".jobportal-slider-widget");

		if ($scope.hasClass("jobportal-swiper-linked-yes")) {
			var thumbsSlider = $element.filter(".jobportal-thumbs-swiper").JobPortalSwiper();
			var mainSlider = $element.filter(".jobportal-main-swiper").JobPortalSwiper({
				thumbs: {
					swiper: thumbsSlider,
				},
			});
		} else {
			$element.JobPortalSwiper();
		}
	};

	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/jobportal-image-carousel.default",
			SwiperHandler
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/jobportal-modern-carousel.default",
			SwiperHandler
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/jobportal-modern-slider.default",
			SwiperHandler
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/jobportal-freelancer-carousel.default",
			SwiperHandler
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/jobportal-team-member-carousel.default",
			SwiperHandler
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/jobportal-testimonial.default",
			SwiperLinkedHandler
		);
	});
})(jQuery);
