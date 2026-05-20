(function ($) {
	"use strict";

	var JobPortalGridHandler = function ($scope, $) {
		var $element = $scope.find(".jobportal-grid-wrapper");

		$element.JobPortalGridLayout();
	};

	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/jobportal-image-gallery.default",
			JobPortalGridHandler
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/jobportal-testimonial-grid.default",
			JobPortalGridHandler
		);
	});
})(jQuery);
