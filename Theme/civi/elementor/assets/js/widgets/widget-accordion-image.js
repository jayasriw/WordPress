(function ($) {
	"use strict";

	var CiviAccordionHandler = function ($scope, $) {
		var $list_img = $scope.find( '.right img' ),
			$element = $scope.find(".civi-accordion-image");

		$element
			.children(".accordion-section:first-child")
			.children(".accordion-content")
			.css("display", "block");

		$element
			.children(".accordion-section:first-child")
			.addClass("active");

		$element.on("click", ".accordion-header", function (e) {
			e = e || window.event;
			e.preventDefault();
			e.stopPropagation();

			var section = $(this).parent(".accordion-section"),
				index = $(this).parent(".accordion-section").index();
				index++;

			var current = $scope.find(".right img:nth-child(" + index + ")");
			var prevall = current.prevAll().get();
			var nextall = current.nextAll().get();

			if( prevall.length > 0 ){
				var reversedprevall = prevall.reverse();
				$.each(reversedprevall, function(index, value) {
					index++;
					var top = index * 100 * -1;
					$(this).css( 'transform', 'translateY(' + top + '%)' );
				});
			}
			if( nextall.length > 0 ){
				$.each(nextall, function(index, value) {
					index++;
					var top = index * 100;
					$(this).css( 'transform', 'translateY(' + top + '%)' );
				});
			}
			current.css( 'transform', 'translateY(0)' );
			$list_img.css( 'opacity', '0' );
			current.css( 'opacity', '1' );


			if (section.hasClass("active")) {
				section.removeClass("active");
				section.children(".accordion-content").slideUp(300);
			} else {
				var parent = $(this).parents(".civi-accordion-image").first();
				if (!parent.data("multi-open")) {
					parent
						.children(".active")
						.removeClass("active")
						.children(".accordion-content")
						.slideUp(300);
				}
				section.addClass("active");
				section.children(".accordion-content").slideDown(300);
			}
		});

		$list_img.each( function(index) {
			if( index > 0 ){
				var top = index * 100;
				$( this ).css( 'transform', 'translateY(' + top + '%)' );
			}
		});
	};

	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/civi-accordion-image.default",
			CiviAccordionHandler
		);
	});
})(jQuery);
