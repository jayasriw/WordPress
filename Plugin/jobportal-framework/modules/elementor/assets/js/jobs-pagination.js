(function ($) {
    "use strict";

    var JobPortalPaginationHandler = function ($scope, $) {
        var $element = $scope.find(".jobportal-jobs");

        var widgetId = $scope.data('id') || Math.random().toString(36).substr(2, 9);
        $element.attr('data-widget-id', widgetId);

        var ajax_url = jobportal_template_vars.ajax_url;

        $element.on("click", ".jobportal-pagination a.page-numbers", function (e) {
            e.preventDefault();

            var $currentWidget = $(this).closest('.jobportal-jobs');

            $currentWidget.find(".jobportal-pagination li .page-numbers").removeClass("current");
            $(this).addClass("current");

            var paged = $(this).text();
            var current_page = 1;

            if ($currentWidget.find('input[name="paged"]').val()) {
                current_page = $currentWidget.find(".jobportal-pagination").find('input[name="paged"]').val();
            }

            if ($(this).hasClass("next")) {
                paged = parseInt(current_page) + 1;
            }
            if ($(this).hasClass("prev")) {
                paged = parseInt(current_page) - 1;
            }

            $currentWidget.find(".jobportal-pagination")
                .find('input[name="paged"]')
                .val(paged);

            ajax_load($currentWidget);
        });

        function ajax_load($currentWidget) {
            var paged = 1;
            var layout = $currentWidget.find('input[name="layout"]').val();
            var type_pagination = $currentWidget.find(".jobportal-pagination").attr("data-type");
            var item_amount = $currentWidget.find('input[name="item_amount"]').val();
            var include_ids = $currentWidget.find('input[name="include_ids"]').val();
            var type_query = $currentWidget.find('input[name="type_query"]').val();
            var orderby = $currentWidget.find('input[name="orderby"]').val();
            var settings = $currentWidget.find('input[name="settings"]').val();
            paged = $currentWidget.find(".jobportal-pagination").find('input[name="paged"]').val();

            var jobs_categories = [];
            var jobs_skills = [];
            var jobs_type = [];
            var jobs_location = [];
            var jobs_career = [];
            var jobs_experience = [];

            $currentWidget.find("input[name='jobs-categories']:checked").each(function () {
                jobs_categories.push($(this).val());
            });
            $currentWidget.find("input[name='jobs-skills']:checked").each(function () {
                jobs_skills.push($(this).val());
            });
            $currentWidget.find("input[name='jobs-type']:checked").each(function () {
                jobs_type.push($(this).val());
            });
            $currentWidget.find("input[name='jobs-location']:checked").each(function () {
                jobs_location.push($(this).val());
            });
            $currentWidget.find("input[name='jobs-career']:checked").each(function () {
                jobs_career.push($(this).val());
            });
            $currentWidget.find("input[name='jobs-experience']:checked").each(function () {
                jobs_experience.push($(this).val());
            });

            $.ajax({
                dataType: "json",
                url: ajax_url,
                data: {
                    action: "jobportal_el_jobs_pagination_ajax",
                    widget_id: widgetId,
                    layout: layout,
                    item_amount: item_amount,
                    type_pagination: type_pagination,
                    include_ids: include_ids,
                    type_query: type_query,
                    orderby: orderby,
                    settings: settings,
                    jobs_categories: jobs_categories,
                    jobs_skills: jobs_skills,
                    jobs_type: jobs_type,
                    jobs_location: jobs_location,
                    jobs_career: jobs_career,
                    jobs_experience: jobs_experience,
                    paged: paged,
                },
                beforeSend: function () {
                    $currentWidget.find(".jobportal-jobs-item").addClass("skeleton-loading");
                    if (type_pagination == "loadmore") {
                        $currentWidget.find(".btn-loading").fadeIn();
                    }
                },
                success: function (data) {
                    $currentWidget.find(".pagination").html(data.pagination);
                    $currentWidget.find(".jobportal-jobs-item").removeClass("skeleton-loading");

                    if (type_pagination == "number") {
                        $currentWidget.find(".elementor-grid").html(data.jobs_html);
                    } else {
                        $currentWidget.find(".elementor-grid").append(data.jobs_html);
                        $currentWidget.find(".btn-loading").fadeOut();
                        if (data.hidden_pagination) {
                            $currentWidget.find(".jobportal-pagination .pagination").html("");
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.log('Ajax error:', error);
                }
            });
        }
    };

    $(window).on("elementor/frontend/init", function () {
        elementorFrontend.hooks.addAction("frontend/element_ready/jobportal-jobs.default",
            JobPortalPaginationHandler
        );
    });
})(jQuery);
