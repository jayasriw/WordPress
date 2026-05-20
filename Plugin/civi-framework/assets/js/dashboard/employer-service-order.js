(function ($) {
    "use strict";

    var service_dashboard = $(".civi-service-order"),
        form_refund = $("#form-service-order-refund"),
        form_view_reason = $("#form-service-view-reason"),
        ajax_url = civi_service_order_vars.ajax_url,
        not_service = civi_service_order_vars.not_service;

    $(document).ready(function () {
        service_dashboard
            .find(".select-pagination")
            .on('change', function () {
                var number = "";
                $(".select-pagination option:selected").each(function () {
                    number += $(this).val() + " ";
                });
                $(this).attr("value");
            })
            .trigger("change");

        service_dashboard.find("select.search-control").on("change", function () {
            $(".civi-pagination").find('input[name="paged"]').val(1);
            ajax_load();
        });

        service_dashboard.find("input.search-control").on("input", function () {
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

        service_dashboard.find("input.service-search-control").on('keyup',
            delay(function () {
                $(".civi-pagination").find('input[name="paged"]').val(1);
                ajax_load();
            }, 1000)
        );

        $("body").on("click", ".service-control .btn-completed", function (e) {
            e.preventDefault();
            var item_id = $(this).attr("order-id");
            ajax_load(item_id, "completed");
        });

        $("body").on("click", ".service-control .btn-order-refund", function (e) {
            var item_id = $(this).attr("order-id");
            form_refund.find('#btn-service-refund').attr("order-id",item_id);
        });

        $("body").on("click", "#btn-service-refund", function (e) {
            e.preventDefault();
            var item_id = $(this).attr("order-id"),
                content_refund = form_refund.find('textarea[name="service_content_refund"]').val(),
                service_payment = form_refund.find('select[name="service_payment"]').val();
            ajax_load(item_id, "refund", content_refund,service_payment);
        });

        $("body").on("click", ".service-control .btn-view-reason", function () {
            var content_refund = $(this).data("content-refund");
            form_view_reason.find('.content-refund-reason').text(content_refund);
        });

        $("body").on("click", ".civi-pagination a.page-numbers", function (e) {
            e.preventDefault();
            $(".civi-pagination li .page-numbers").removeClass("current");
            $(this).addClass("current");
            var paged = $(this).text();
            var current_page = 1;
            if (
                service_dashboard
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
            service_dashboard
                .find(".civi-pagination")
                .find('input[name="paged"]')
                .val(paged);

            ajax_load();
        });

        var paged = 1;
        service_dashboard.find(".select-pagination").attr("data-value", paged);

        function ajax_load(item_id = "", action_click = "", content_refund = "", service_payment = "") {
            var paged = 1;
            var height = service_dashboard.find("#service-order").height();
            var service_search = service_dashboard.find('input[name="service_search"]').val(),
                service_status = service_dashboard.find('select[name="service_status"]').val(),
                item_amount = service_dashboard.find('select[name="item_amount"]').val(),
                service_sort_by = service_dashboard.find('select[name="service_sort_by"]').val();
            paged = $(".civi-pagination").find('input[name="paged"]').val();

            $.ajax({
                dataType: "json",
                url: ajax_url,
                data: {
                    action: "civi_employer_order_service",
                    item_amount: item_amount,
                    paged: paged,
                    service_search: service_search,
                    service_status: service_status,
                    service_sort_by: service_sort_by,
                    item_id: item_id,
                    content_refund: content_refund,
                    service_payment: service_payment,
                    action_click: action_click,
                    security: civi_template_vars.ajax_nonce,
                },
                beforeSend: function () {
                    if(action_click !== "refund"){
                        service_dashboard
                            .find(".civi-loading-effect")
                            .addClass("loading")
                            .fadeIn();
                        service_dashboard.find("#service-order").height(height);
                    }
                },
                success: function (data) {
                    if (data.success === true) {
                        var $items_pagination = service_dashboard.find(".items-pagination"),
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

                        service_dashboard.find(".pagination").html(data.pagination);
                        service_dashboard.find("#service-order tbody").fadeOut("fast", function () {
                            service_dashboard.find("#service-order tbody").html(data.service_html);
                            service_dashboard.find("#service-order tbody").fadeIn(300);
                        });
                        service_dashboard.find("#service-order").css("height", "auto");
                    } else {
                        service_dashboard
                            .find("#service-order tbody")
                            .html('<span class="not-service">' + not_service + "</span>");
                    }
                    service_dashboard
                        .find(".civi-loading-effect")
                        .removeClass("loading")
                        .fadeOut();

                    if(action_click === "refund"){
                        window.location.reload();
                    }
                },
            });
        }
    });
})(jQuery);
