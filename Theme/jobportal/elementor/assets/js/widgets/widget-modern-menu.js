(function ($) {
	"use strict";

	var JobPortalModernMenuHandler = function ($scope, $) {
        $scope.find("ul.elementor-nav-menu>li.menu-item-has-children>a"
		).append(
			'<span class="chevron"><i class="far fa-chevron-down"></i></span>'
		);
	};

	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/jobportal-modern-menu.default",
			JobPortalModernMenuHandler
		);
	});
})(jQuery);
