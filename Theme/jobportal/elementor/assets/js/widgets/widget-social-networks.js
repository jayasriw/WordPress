(function ($) {
	"use strict";

	var JobPortalSocialNetworksHandler = function ($scope, $) {
		var $element = $scope.find(".jobportal-social-networks"),
			$heading = $element.find(".heading"),
			$list_inner = $element.find(".list-inner");

		if (window.matchMedia("(max-width: 576px)").matches) {
			$heading.on('click', function () {
				$list_inner.slideToggle();
				$(this).find("i").toggleClass("far fa-chevron-up far fa-chevron-down");
			});
		}
	};

	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/jobportal-social-networks.default",
			JobPortalSocialNetworksHandler
		);
	});
})(jQuery);
