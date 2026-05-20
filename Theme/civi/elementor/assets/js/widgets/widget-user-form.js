(function ($) {
	"use strict";

	var CiviUserFormHandler = function ($scope, $) {
		$( '.el-uf-nav a' ).on( 'click', function(e) {
			e.preventDefault();
			var data = $( this ).attr( 'data-form' );
			var form = $( this ).closest( '.el-user-form' );
			var item = form.find( '.el-uf-item' );

			$( '.el-uf-nav a' ).removeClass('active');
			$( this ).addClass( 'active' );
			item.removeClass( 'active' );
			form.find( '.el-uf-item.' + data ).addClass( 'active' );
		});
	};

	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/civi-user-form.default",
			CiviUserFormHandler
		);
	});
})(jQuery);
