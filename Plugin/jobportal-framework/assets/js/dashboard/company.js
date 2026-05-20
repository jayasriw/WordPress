(function ($) {
  "use strict";

  var company_dashboard = $(".company-dashboard");

  var ajax_url = jobportal_company_dashboard_vars.ajax_url,
    not_company = jobportal_company_dashboard_vars.not_company;

  $(document).ready(function () {
    company_dashboard
      .find(".select-pagination")
      .on('change', function () {
        var number = "";
        $(".select-pagination option:selected").each(function () {
          number += $(this).val() + " ";
        });
        $(this).attr("value");
      })
      .trigger("change");

    company_dashboard.find("select.search-control").on("change", function () {
      $(".jobportal-pagination").find('input[name="paged"]').val(1);
      ajax_load();
    });

    company_dashboard.find("input.search-control").on("input", function () {
      $(".jobportal-pagination").find('input[name="paged"]').val(1);
      ajax_load();
    });

    $("body").on("click", ".company-control .btn-delete", function (e) {
      e.preventDefault();
      var delete_id = $(this).attr("company-id");
      ajax_load(delete_id, "delete");
    });

    $("body").on("click", ".jobportal-pagination a.page-numbers", function (e) {
      e.preventDefault();
      company_dashboard
        .find(".jobportal-pagination li .page-numbers")
        .removeClass("current");
      $(this).addClass("current");
      var paged = $(this).text();
      var current_page = 1;
      if ($(".jobportal-pagination").find('input[name="paged"]').val()) {
        current_page = $(".jobportal-pagination").find('input[name="paged"]').val();
      }
      if ($(this).hasClass("next")) {
        paged = parseInt(current_page) + 1;
      }
      if ($(this).hasClass("prev")) {
        paged = parseInt(current_page) - 1;
      }
      company_dashboard
        .find(".jobportal-pagination")
        .find('input[name="paged"]')
        .val(paged);

      ajax_load();
    });

    var paged = 1;
    company_dashboard.find(".select-pagination").attr("data-value", paged);

    function ajax_load(item_id = "", action_click = "") {
      var paged = 1;
      var height = company_dashboard.find("#company-dashboard").height();
      var item_amount = company_dashboard
        .find('select[name="item_amount"]')
        .val();
      paged = company_dashboard
        .find(".jobportal-pagination")
        .find('input[name="paged"]')
        .val();

      $.ajax({
        type: 'POST',
        dataType: "json",
        url: ajax_url,
        headers: {
          'Cache-Control': 'no-cache, no-store, must-revalidate'
        },
        data: {
          action: "jobportal_filter_company_dashboard",
          item_amount: item_amount,
          paged: paged,
          item_id: item_id,
          action_click: action_click,
          security: jobportal_company_dashboard_vars.delete_company,
        },
        beforeSend: function () {
          company_dashboard
            .find(".jobportal-loading-effect")
            .addClass("loading")
            .fadeIn();
          company_dashboard.find("#company-dashboard").height(height);
        },
        success: function (data) {
          if (data.success === true) {
            var $items_pagination = company_dashboard.find(".items-pagination"),
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

            company_dashboard.find(".pagination").html(data.pagination);
            company_dashboard
              .find("#my-company tbody")
              .fadeOut("fast", function () {
                company_dashboard
                  .find("#my-company tbody")
                  .html(data.company_html);
                company_dashboard.find("#my-company tbody").fadeIn(300);
              });
            company_dashboard.find("#company-dashboard").css("height", "auto");
          } else {
            if (paged > 1) {
              paged = paged - 1;
              company_dashboard.find(".jobportal-pagination").find('input[name="paged"]').val(paged);
              ajax_load();
              return;
            }
            company_dashboard
              .find("#my-company tbody")
              .html('<span class="not-company">' + not_company + "</span>");
          }
          company_dashboard
            .find(".jobportal-loading-effect")
            .removeClass("loading")
            .fadeOut();
        },
      });
    }
  });
})(jQuery);
