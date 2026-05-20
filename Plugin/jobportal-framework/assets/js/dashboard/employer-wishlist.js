(function ($) {
    "use strict";

    var employer_wishlist = $(".jobportal-employer-wishlist");

    var ajax_url = jobportal_employer_wishlist_vars.ajax_url,
        not_service = jobportal_employer_wishlist_vars.not_service;

    $(document).ready(function () {
        employer_wishlist.find("select-pagination").on('change', function () {
            var number = "";
            employer_wishlist.find(".select-pagination option:selected").each(function () {
                number += $(this).val() + " ";
            });
            $(this).attr("value");
        });

        employer_wishlist.find("select.search-control").on("change", function () {
            employer_wishlist.find(".jobportal-pagination").find('input[name="paged"]').val(1);
            ajax_load_wishlist();
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

        employer_wishlist.find("input.service-search-control").on('keyup',
            delay(function () {
                employer_wishlist.find(".jobportal-pagination").find('input[name="paged"]').val(1);
                ajax_load_wishlist();
            }, 1000)
        );

        $("body").on(
            "click",
            ".jobportal-employer-wishlist .service-control .btn-delete",
            function (e) {
                e.preventDefault();
                var delete_id = $(this).attr("service-id");
                ajax_load_wishlist(delete_id, "delete");
            }
        );

        $("body").on(
            "click",
            ".jobportal-employer-wishlist .jobportal-pagination a.page-numbers",
            function (e) {
                e.preventDefault();
                employer_wishlist
                    .find(".jobportal-pagination li .page-numbers")
                    .removeClass("current");
                $(this).addClass("current");
                var paged = $(this).text();
                var current_page = 1;
                if (
                    employer_wishlist.find(".jobportal-pagination").find('input[name="paged"]').val()
                ) {
                    current_page = employer_wishlist
                        .find(".jobportal-pagination")
                        .find('input[name="paged"]')
                        .val();
                }
                if ($(this).hasClass("next")) {
                    paged = parseInt(current_page) + 1;
                }
                if ($(this).hasClass("prev")) {
                    paged = parseInt(current_page) - 1;
                }
                employer_wishlist
                    .find(".jobportal-pagination")
                    .find('input[name="paged"]')
                    .val(paged);

                ajax_load_wishlist();
            }
        );

        var paged = 1;
        employer_wishlist.find(".select-pagination").attr("data-value", paged);

        function ajax_load_wishlist(item_id = "", action_click = "") {
            var paged = 1;
            var height = employer_wishlist.find("#employer-wishlist").height();
            var service_search = employer_wishlist.find('input[name="service_search"]').val(),
                item_amount = employer_wishlist.find('select[name="item_amount"]').val(),
                service_sort_by = employer_wishlist.find('select[name="service_sort_by"]').val();
            paged = employer_wishlist.find('.jobportal-pagination input[name="paged"]').val();

            $.ajax({
                dataType: "json",
                url: ajax_url,
                data: {
                    action: "jobportal_filter_employer_wishlist",
                    item_amount: item_amount,
                    paged: paged,
                    service_search: service_search,
                    service_sort_by: service_sort_by,
                    item_id: item_id,
                    action_click: action_click,
                    security: jobportal_template_vars.ajax_nonce,
                },
                beforeSend: function () {
                    employer_wishlist.find(".jobportal-loading-effect").addClass("loading").fadeIn();
                    employer_wishlist.find("#employer-wishlist").height(height);
                },
                success: function (data) {
                    if (data.success === true) {
                        var $items_pagination = employer_wishlist.find(".items-pagination"),
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
                        $items_pagination.find(".num-first").text(value_first);
                        $items_pagination.find(".num-last").text(value_last);

                        if (max_number > select_item) {
                            $items_pagination.closest(".pagination-dashboard").show();
                            $items_pagination.find(".num-total").html(data.total_post);
                        } else {
                            $items_pagination.closest(".pagination-dashboard").hide();
                        }

                        employer_wishlist.find(".pagination").html(data.pagination);
                        employer_wishlist.find("#employer-wishlist tbody").fadeOut("fast", function () {
                            employer_wishlist.find("#employer-wishlist tbody").html(data.service_html);
                            employer_wishlist.find("#employer-wishlist tbody").fadeIn(300);
                        });
                        employer_wishlist.find("#employer-wishlist").css("height", "auto");
                    } else {
                        employer_wishlist
                            .find("#employer-wishlist tbody")
                            .html('<span class="not-service">' + not_service + "</span>");
                    }
                    $(".tab-wishlist-item span").html("(" + data.total_post + ")");
                    employer_wishlist
                        .find(".jobportal-loading-effect")
                        .removeClass("loading")
                        .fadeOut();
                },
            });
        }
    });
})(jQuery);
