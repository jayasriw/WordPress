(function ($) {
	"use strict";

	var CiviModernTabsHandler = function ($scope, $) {

	};

	$(window).on('load', function() {
		function activeTab(obj) {
			$(obj).closest(".civi-modern-tabs").find('ul li').removeClass("active");
			$(obj).addClass("active");
			var id = $(obj).find("a").attr("href");
			$(obj).closest(".civi-modern-tabs").find(".modern-tabs-item").hide();
			$(id).show();
		}
		$(".nav-modern-tabs li").on('click', function () {
			activeTab(this);
			return false;
		});
		setTimeout(function () {
			activeTab($(".nav-modern-tabs li:first-child"));
        	$('.content-modern-tabs .modern-tabs-item:first-child').show();
		}, 1000);
	});


	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/civi-modern-tabs.default",
			CiviModernTabsHandler
		);
	});
})(jQuery);
