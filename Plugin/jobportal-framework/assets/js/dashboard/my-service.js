(function ($) {
    "use strict";

    var my_service = $(".my-service");

    var ajax_url = jobportal_candidate_service_vars.ajax_url,
        not_service = jobportal_candidate_service_vars.not_service;

    $(document).ready(function () {
        my_service
            .find(".select-pagination")
            .on('change', function () {
                var number = "";
                $(".select-pagination option:selected").each(function () {
                    number += $(this).val() + " ";
                });
                $(this).attr("value");
            })
            .trigger("change");

        my_service.find("select.search-control").on("change", function () {
            $(".jobportal-pagination").find('input[name="paged"]').val(1);
            ajax_load();
        });

        my_service.find("input.search-control").on("input", function () {
            $(".jobportal-pagination").find('input[name="paged"]').val(1);
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

        my_service.find("input.service-search-control").on('keyup',
            delay(function () {
                $(".jobportal-pagination").find('input[name="paged"]').val(1);
                ajax_load();
            }, 1000)
        );

        $("body").on("click", ".service-control .btn-show", function (e) {
            e.preventDefault();
            var item_id = $(this).attr("service-id");
            ajax_load(item_id, "show");
        });

        $("body").on("click", ".service-control .btn-pause", function (e) {
            e.preventDefault();
            var item_id = $(this).attr("service-id");
            ajax_load(item_id, "pause");
        });

        $("body").on("click", ".service-control .btn-featured", function (e) {
            e.preventDefault();
            var item_id = $(this).attr("service-id");
            ajax_load(item_id, "featured");
        });

        $("body").on("click", ".jobportal-pagination a.page-numbers", function (e) {
            e.preventDefault();
            $(".jobportal-pagination li .page-numbers").removeClass("current");
            $(this).addClass("current");
            var paged = $(this).text();
            var current_page = 1;
            if (
                my_service
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
            my_service
                .find(".jobportal-pagination")
                .find('input[name="paged"]')
                .val(paged);

            ajax_load();
        });

        var paged = 1;
        my_service.find(".select-pagination").attr("data-value", paged);

        function ajax_load(item_id = "", action_click = "") {
            var paged = 1;
            var height = my_service.find("#my-service").height();
            var service_search = my_service.find('input[name="service_search"]').val(),
                service_status = my_service.find('select[name="service_status"]').val(),
                item_amount = my_service.find('select[name="item_amount"]').val(),
                service_sort_by = my_service.find('select[name="service_sort_by"]').val();
            paged = $(".jobportal-pagination").find('input[name="paged"]').val();

            $.ajax({
                dataType: "json",
                url: ajax_url,
                data: {
                    action: "jobportal_filter_my_service",
                    item_amount: item_amount,
                    paged: paged,
                    service_search: service_search,
                    service_status: service_status,
                    service_sort_by: service_sort_by,
                    item_id: item_id,
                    action_click: action_click,
                    security: jobportal_template_vars.ajax_nonce,
                },
                beforeSend: function () {
                    my_service
                        .find(".jobportal-loading-effect")
                        .addClass("loading")
                        .fadeIn();
                    my_service.find("#my-service").height(height);
                },
                success: function (data) {
                    if (data.success === true) {
                        var $items_pagination = my_service.find(".items-pagination"),
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

                        my_service.find(".pagination").html(data.pagination);
                        my_service.find("#my-service tbody").fadeOut("fast", function () {
                            my_service.find("#my-service tbody").html(data.service_html);
                            my_service.find("#my-service tbody").fadeIn(300);
                        });
                        my_service.find("#my-service").css("height", "auto");
                    } else {
                        my_service
                            .find("#my-service tbody")
                            .html('<span class="not-service">' + not_service + "</span>");
                    }
                    my_service
                        .find(".jobportal-loading-effect")
                        .removeClass("loading")
                        .fadeOut();
                },
            });
        }
    });
})(jQuery);
