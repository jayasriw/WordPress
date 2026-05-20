(function ($) {
	"use strict";

	var company_related = $(".company-related-details");
	var pagination = company_related.find(".jobportal-pagination li .page-numbers");

	var ajax_url = jobportal_company_related_vars.ajax_url;

	$(document).ready(function () {
		$("body").on("click", ".jobportal-pagination a.page-numbers", function (e) {
			e.preventDefault();
			company_related
				.find(".jobportal-pagination li .page-numbers")
				.removeClass("current");
			$(this).addClass("current");
			var paged = $(this).text();
			var current_page = 1;
			if (
				company_related
					.find(".jobportal-pagination")
					.find('input[name="paged"]')
					.val()
			) {
				current_page = $(".jobportal-pagination").find('input[name="paged"]').val();
			}
			if ($(this).hasClass("next")) {
				paged = parseInt(current_page) + 1;
			}
			if ($(this).hasClass("prev")) {
				paged = parseInt(current_page) - 1;
			}
			company_related
				.find(".jobportal-pagination")
				.find('input[name="paged"]')
				.val(paged);
			ajax_load();
		});

		function ajax_load() {
			var paged = 1;
			var item_amount = company_related.find('input[name="item_amount"]').val();
			var company_id = company_related.find('input[name="company_id"]').val();
			paged = company_related
				.find(".jobportal-pagination")
				.find('input[name="paged"]')
				.val();

			$.ajax({
				dataType: "json",
				url: ajax_url,
				data: {
					action: "jobportal_company_related",
					item_amount: item_amount,
					company_id: company_id,
					paged: paged,
				},
				beforeSend: function () {
					company_related
						.find(".jobportal-loading-effect")
						.addClass("loading")
						.fadeIn();
				},
				success: function (data) {
					company_related.find(".pagination").html(data.pagination);
					company_related
						.find(".jobportal-loading-effect")
						.removeClass("loading")
						.fadeOut();
					company_related
						.find(".related-inner .related-company")
						.html(data.company_html);
				},
			});
		}
	});
})(jQuery);
