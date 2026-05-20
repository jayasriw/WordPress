(function ($) {
	"use strict";

	var $widgetTemplate = "",
		$widgetArea = "",
		$widgetWrap = "";

	var JOBPORTAL_Widget_Areas = {
		init: function () {
			$widgetTemplate = $("#bin-add-widget-template");
			$widgetArea = $("#widgets-right");
			$widgetWrap = $(".sidebars-column-" + $widgetArea.children().length);

			JOBPORTAL_Widget_Areas.addFormHtml();
			JOBPORTAL_Widget_Areas.addDelButton();
			JOBPORTAL_Widget_Areas.deleteWidgetArea();
			JOBPORTAL_Widget_Areas.event();
		},
		addFormHtml: function () {
			$widgetWrap.append($widgetTemplate.html());
		},
		event: function () {},
		addDelButton: function () {
			$(".sidebar-bin-widgets-custom .widgets-sortables").append(
				'<span class="bin-widget-area-delete"></span>'
			);
		},
		deleteWidgetArea: function () {
			$widgetArea.on("click", ".bin-widget-area-delete", function () {
				if (!confirm(jobportal_widget_areas_variable.confirm_delete)) {
					return;
				}

				var $widget = $(this).parent(),
					widget_name = $widget.attr("id"),
					nonce = $('input[name="bin-widget-areas-nonce"]').val();

				$.ajax({
					type: "POST",
					url: jobportal_widget_areas_variable.ajax_url,
					data: {
						action: "jobportal_delete_widget_area",
						name: widget_name,
						_wpnonce: nonce,
					},

					success: function (response) {
						if (response.trim() == "widget-area-deleted") {
							$widget.parent().slideUp(200).remove();
						}
					},
				});
			});
		},
	};

	$(function () {
		JOBPORTAL_Widget_Areas.init();
	});
})(jQuery);
