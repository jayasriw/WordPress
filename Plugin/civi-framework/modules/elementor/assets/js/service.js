(function ($) {
    "use strict";

    var CiviServiceHandler = function ($scope, $) {
		$( '.service-tabs' ).each( function() {
			var $this = $( this ),
				$tabs_nav = $this.find( '.service-nav li a' ),
				$tabs_content = $this.find( '.service-tab-content' );

			$tabs_nav.on( 'click', function( e ) {
				e.preventDefault();
				var id = $( this ).attr( 'href' );
				$tabs_nav.removeClass( 'active' );
				$( this ).addClass( 'active' );
				$tabs_content.removeClass( 'active' );
				$( id ).addClass( 'active' );
				$scope.find(".elementor-carousel").slick("refresh");
			} );
		} );
    };

    $(window).on("elementor/frontend/init", function () {
        elementorFrontend.hooks.addAction("frontend/element_ready/civi-service.default",
			CiviServiceHandler
        );
    });
})(jQuery);
