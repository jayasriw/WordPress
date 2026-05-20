(function ($) {
	"use strict";

	var applicants_dashboard = $(".applicants-dashboard");

	var ajax_url = civi_applicants_dashboard_vars.ajax_url,
		not_applicants = civi_applicants_dashboard_vars.not_applicants,
		ajax_nonce = civi_applicants_dashboard_vars.ajax_nonce;

	$(document).ready(function () {

        $('body').on("click", "#btn-mees-applicants", function () {
            var item_id = $(this).attr("data-id");
        	$('#form-messages-applicants .content-mess').text( $(this).data('mess'));
        	$(this).find('.fa-facebook-messenger').addClass('active');
			$('.btn-realy-mess').attr('data-id',$(this).data("id")),
			$('.btn-realy-mess').attr('data-apply',$(this).data("apply")),
			$('.btn-realy-mess').attr('data-mess',$(this).data("mess")),
            $('.btn-realy-mess').attr('data-jobs-id',$(this).data("jobs-id"));
            read_mess_ajax_load(item_id);
            return false;
        });

        function read_mess_ajax_load(item_id = "") {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajax_url,
                data: {
                    action: "civi_read_mess_ajax_load",
                    item_id: item_id,
                    security: ajax_nonce,
                },
                beforeSend: function () {
                },
                success: function (data) {
                },
            });
        }

        $('body').on("click", ".btn-realy-mess", function (e) {
            e.preventDefault();
            var item_id = $(this).attr("data-id"),
             	title = $(this).attr("data-apply"),
             	content = $(this).attr("data-mess"),
				jobs_id = $(this).attr("data-jobs-id");

            realy_mess_ajax_load(item_id,title,content,jobs_id);
        });

        function realy_mess_ajax_load(item_id = "",title = "",content = "",jobs_id = "") {
        	var link_mess = $('input[name="link_mess"]').val();

            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajax_url,
                data: {
                    action: "civi_realy_mess_ajax_load",
                    item_id: item_id,
                    title: title,
                    content: content,
                    jobs_id: jobs_id,
                    security: ajax_nonce,
                },
                beforeSend: function () {
                    $('.btn-realy-mess').find('.btn-loading').fadeIn();
                },
                success: function (data) {
                    $('.btn-realy-mess').find('.btn-loading').fadeOut();
                    if (data.success === true) {
                        window.location.href = link_mess;
                    }
                },
            });
        }

		applicants_dashboard
			.find(".select-pagination")
			.on('change', function () {
				var number = "";
				$(".select-pagination option:selected").each(function () {
					number += $(this).val() + " ";
				});
				$(this).attr("value");
			})
			.trigger("change");

		applicants_dashboard
			.find("select.search-control")
			.on("change", function () {
				$(".civi-pagination").find('input[name="paged"]').val(1);
				ajax_load();
			});

		function delay(callback, ms) {
			var timer = 0;
			return function () {
				var context = this,
					args = arguments;
				clearTimeout(timer);
				timer = setTimeout(function () {
					callback.apply(context, args);
				}, ms || 0);
			};
		}

		applicants_dashboard.find("input.search-control").on('keyup',
			delay(function () {
				$(".civi-pagination").find('input[name="paged"]').val(1);
				ajax_load();
			}, 1000)
		);

		$("body").on("click", ".applicants-control .btn-approved", function (e) {
			e.preventDefault();
			var item_id = $(this).attr("applicants-id");
			ajax_load(item_id, "approved");
		});

		$("body").on("click", ".applicants-control .btn-rejected", function (e) {
			e.preventDefault();
			var item_id = $(this).attr("applicants-id");
			ajax_load(item_id, "rejected");
		});

		$("body").on("click", ".civi-pagination a.page-numbers", function (e) {
			e.preventDefault();
			applicants_dashboard
				.find(".civi-pagination li .page-numbers")
				.removeClass("current");
			$(this).addClass("current");
			var paged = $(this).text();
			var current_page = 1;
			if (
				applicants_dashboard
					.find(".civi-pagination")
					.find('input[name="paged"]')
					.val()
			) {
				current_page = $(".civi-pagination").find('input[name="paged"]').val();
			}
			if ($(this).hasClass("next")) {
				paged = parseInt(current_page) + 1;
			}
			if ($(this).hasClass("prev")) {
				paged = parseInt(current_page) - 1;
			}
			applicants_dashboard
				.find(".civi-pagination")
				.find('input[name="paged"]')
				.val(paged);

			ajax_load();
		});

		var paged = 1;
		applicants_dashboard.find(".select-pagination").attr("data-value", paged);

		function ajax_load(item_id = "", action_click = "") {
			var paged = 1,
				height = applicants_dashboard.find("#applicants-dashboard").height(),
				applicants_search = applicants_dashboard
					.find('input[name="applicants_search"]')
					.val(),
				item_amount = applicants_dashboard
					.find('select[name="item_amount"]')
					.val(),
				applicants_filter_jobs = applicants_dashboard
					.find('select[name="applicants_filter_jobs"]')
					.val(),
				applicants_sort_by = applicants_dashboard
					.find('select[name="applicants_sort_by"]')
					.val();
			paged = $(".civi-pagination").find('input[name="paged"]').val();

			var applicants_jobs_id = applicants_dashboard
				.find('input[name="applicants_jobs_id"]')
				.val();

			$.ajax({
				type: 'POST',
				dataType: "json",
				url: ajax_url,
				headers: {
					'Cache-Control': 'no-cache, no-store, must-revalidate'
				},
				data: {
					action: "civi_filter_applicants_dashboard",
					item_amount: item_amount,
					paged: paged,
					applicants_search: applicants_search,
					applicants_sort_by: applicants_sort_by,
					applicants_filter_jobs: applicants_filter_jobs,
					applicants_jobs_id: applicants_jobs_id,
					item_id: item_id,
					action_click: action_click,
					security: ajax_nonce,
				},
				beforeSend: function () {
					applicants_dashboard
						.find(".civi-loading-effect")
						.addClass("loading")
						.fadeIn();
					applicants_dashboard.find("#applicants-dashboard").height(height);
				},
				success: function (data) {
					if (data.success === true) {
						var $items_pagination =
								applicants_dashboard.find(".items-pagination"),
							select_item = $items_pagination
								.find('select[name="item_amount"] option:selected')
								.val(),
							max_number = data.total_post,
							value_first = select_item * paged + 1 - select_item,
							value_last = select_item * paged;
						if (max_number < value_first) {
							value_first = select_item * (paged - 1) + 1;
						}
						if (max_number < value_last) {
							value_last = max_number;
						}
						$(".num-first").text(value_first);
						$(".num-last").text(value_last);

						if (max_number > select_item) {
							$items_pagination.closest(".pagination-dashboard").show();
							$items_pagination.find(".num-total").html(data.total_post);
						} else {
							$items_pagination.closest(".pagination-dashboard").hide();
						}

						applicants_dashboard.find(".pagination").html(data.pagination);
						applicants_dashboard
							.find("#my-applicants tbody")
							.fadeOut("fast", function () {
								applicants_dashboard
									.find("#my-applicants tbody")
									.html(data.applicants_html);
								applicants_dashboard.find("#my-applicants tbody").fadeIn(300);
							});
						applicants_dashboard
							.find("#applicants-dashboard")
							.css("height", "auto");
					} else {
						if (paged > 1) {
							paged = paged - 1;
							applicants_dashboard.find(".civi-pagination").find('input[name="paged"]').val(paged);
							ajax_load();
							return;
						}
						applicants_dashboard
							.find("#my-applicants tbody")
							.html(
								'<span class="not-applicants">' + not_applicants + "</span>"
							);
					}
					applicants_dashboard
						.find(".civi-loading-effect")
						.removeClass("loading")
						.fadeOut();
				},
			});
		}
	});
})(jQuery);
