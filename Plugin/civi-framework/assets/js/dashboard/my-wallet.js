var MESSAGES = MESSAGES || {};
(function ($) {
    "use strict";
    var ajax_url = civi_my_wallet_vars.ajax_url,
        not_wallet = civi_my_wallet_vars.not_wallet,
        my_wallet = $(".civi-service-withdraw"),
        form_popup = $('#form-service-withdraw');

    MESSAGES = {
        init: function () {
            this.submit_withdraw();
            this.my_wallet();
        },

        submit_withdraw: function () {
            $("body").on("click", "#btn-submit-withdraw", function (e) {
                e.preventDefault();
                var $this = $(this),
                    withdraw_payment = form_popup.find('select[name="withdraw_payment"]').val(),
                    withdraw_price = form_popup.find('input[name="withdraw_price"]').val();

                    $.ajax({
                    type: "POST",
                    url: ajax_url,
                    dataType: "json",
                    data: {
                        action: "civi_submit_withdraw",
                        withdraw_payment: withdraw_payment,
                        withdraw_price: withdraw_price,
                        nonce: civi_ajax_nonce.withdraw,
                    },
                    beforeSend: function () {
                        $this.find(".btn-loader").html('<i class="fal fa-spinner fa-spin"></i>');
                    },
                    success: function (data) {
                        if (data.success == true) {
                            location.reload();
                            form_popup.find(".civi-message-error").addClass("true");
                        }
                        form_popup.find(".civi-message-error").text(data.message);
                        $this.find(".btn-loader").html('<i class="fas fa-arrow-to-bottom"></i>');
                    },
                });
            });
        },

        my_wallet: function () {
            my_wallet
                .find(".select-pagination")
                .on('change', function () {
                    var number = "";
                    $(".select-pagination option:selected").each(function () {
                        number += $(this).val() + " ";
                    });
                    $(this).attr("value");
                })
                .trigger("change");

            my_wallet.find("select.search-control").on("change", function () {
                $(".civi-pagination").find('input[name="paged"]').val(1);
                ajax_load();
            });

            my_wallet.find("input.search-control").on("input", function () {
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

            my_wallet.find("input.service-search-control").on('keyup',
                delay(function () {
                    $(".civi-pagination").find('input[name="paged"]').val(1);
                    ajax_load();
                }, 1000)
            );

            $("body").on("click", ".civi-pagination a.page-numbers", function (e) {
                e.preventDefault();
                $(".civi-pagination li .page-numbers").removeClass("current");
                $(this).addClass("current");
                var paged = $(this).text();
                var current_page = 1;
                if (
                    my_wallet
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
                my_wallet
                    .find(".civi-pagination")
                    .find('input[name="paged"]')
                    .val(paged);

                ajax_load();
            });

            var paged = 1;
            my_wallet.find(".select-pagination").attr("data-value", paged);

            function ajax_load() {
                var paged = 1;
                var height = my_wallet.find("#my-wallet").height();
                var wallet_status = my_wallet.find('select[name="wallet_status"]').val(),
                    wallet_method = my_wallet.find('select[name="wallet_method"]').val(),
                    item_amount = my_wallet.find('select[name="item_amount"]').val(),
                    wallet_sort_by = my_wallet.find('select[name="wallet_sort_by"]').val();
                paged = $(".civi-pagination").find('input[name="paged"]').val();

                $.ajax({
                    dataType: "json",
                    url: ajax_url,
                    data: {
                        action: "civi_candidate_wallet_service",
                        item_amount: item_amount,
                        paged: paged,
                        wallet_status: wallet_status,
                        wallet_method: wallet_method,
                        wallet_sort_by: wallet_sort_by,
                        security: civi_template_vars.ajax_nonce,
                    },
                    beforeSend: function () {
                        my_wallet
                            .find(".civi-loading-effect")
                            .addClass("loading")
                            .fadeIn();
                        my_wallet.find("#my-wallet").height(height);
                    },
                    success: function (data) {
                        if (data.success === true) {
                            var $items_pagination = my_wallet.find(".items-pagination"),
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

                            my_wallet.find(".pagination").html(data.pagination);
                            my_wallet.find("#my-wallet tbody").fadeOut("fast", function () {
                                my_wallet.find("#my-wallet tbody").html(data.wallet_html);
                                my_wallet.find("#my-wallet tbody").fadeIn(300);
                            });
                            my_wallet.find("#my-wallet").css("height", "auto");
                        } else {
                            my_wallet
                                .find("#my-wallet tbody")
                                .html('<span class="not-service">' + not_wallet + "</span>");
                        }
                        my_wallet
                            .find(".civi-loading-effect")
                            .removeClass("loading")
                            .fadeOut();
                    },
                });
            }
        },
    };

    $(document).ready(function () {
        MESSAGES.init();
    });

})(jQuery);
