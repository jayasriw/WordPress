(function ($) {
	"use strict";

	var ajax_url = civi_template_vars.ajax_url;
	var colorAccent = $(".civi-nav-dashboard").data("accent") || "rgb(0, 116, 86)";
	var colorSecondary = $(".civi-nav-dashboard").data("secondary") || "rgb(237,0,6)";

	$(document).ready(function () {
		//Jobs
		$('select[name="chart-date"]').on('change', function () {
			var jobs_id = $("#civi-dashboard_chart").data("jobs-id");
			var number_days = $(this).find("option:selected").val();
			$.ajax({
				type: "POST",
				url: ajax_url,
				dataType: "json",
				data: {
					action: "civi_chart_ajax",
					jobs_id: jobs_id,
					number_days: number_days,
                    security: civi_ajax_nonce.civi_chart_nonce,
				},
				beforeSend: function () {
					$(".civi-chart-wrapper")
						.find(".civi-loading-effect")
						.addClass("loading")
						.fadeIn();
				},
				success: function (response) {
					var ctx = document
						.getElementById("civi-dashboard_chart")
						.getContext("2d");
					if (
						window.dashboardChart !== undefined &&
						window.dashboardChart !== null
					) {
						window.dashboardChart.destroy();
					}
					var datasets = [
                        {
                            label: response.label_view,
                            data: response.values_view,
                            backgroundColor: colorAccent,
                            borderColor: colorAccent,
                        },
                        {
                            label: response.label_apply,
                            data: response.values_apply,
                            backgroundColor: colorSecondary,
                            borderColor: colorSecondary,
                        },
                    ];
					if (window.modifyDatasets) {
                        datasets = window.modifyDatasets(datasets);
                    }
					window.dashboardChart = new Chart(ctx, {
						type: "line",
						data: {
							labels: response.labels,
							datasets: datasets,
						},
						options: {
							tooltips: {
								enabled: true,
								mode: "x-axis",
								cornerRadius: 4,
							},
						},
					});
					$(".civi-chart-wrapper")
						.find(".civi-loading-effect")
						.removeClass("loading")
						.fadeOut();
				},
			});
		});

		if ($("#civi-dashboard_chart").length) {
			var $this = $("#civi-dashboard_chart"),
				labels = $this.data("labels"),
				values_view = $this.data("values_view"),
				label_view = $this.data("label_view"),
				values_apply = $this.data("values_apply"),
				label_apply = $this.data("label_apply");

			var ctx = document
				.getElementById("civi-dashboard_chart")
				.getContext("2d");
			if (
				window.dashboardChart !== undefined &&
				window.dashboardChart !== null
			) {
				window.dashboardChart.destroy();
			}
			var datasets = [
                {
                    label: label_view,
                    data: values_view,
                    backgroundColor: colorAccent,
                    borderColor: colorAccent,
                },
                {
                    label: label_apply,
                    data: values_apply,
                    backgroundColor: colorSecondary,
                    borderColor: colorSecondary,
                },
            ];
			if (window.modifyDatasets) {
                datasets = window.modifyDatasets(datasets);
            }
			window.dashboardChart = new Chart(ctx, {
				type: "line",
				data: {
					labels: labels,
					datasets: datasets,
				},
				options: {
					tooltips: {
						enabled: true,
						mode: "x-axis",
						cornerRadius: 4,
					},
				},
			});
		}

		//Employer
		$('select[name="chart_employer"]').on('change', function () {
			var number_days = $(this).find("option:selected").val();
			$.ajax({
				type: "POST",
				url: ajax_url,
				dataType: "json",
				data: {
					action: "civi_chart_employer_ajax",
					number_days: number_days,
                    security: civi_ajax_nonce.civi_chart_nonce,
				},
				beforeSend: function () {
					$(".civi-chart-employer")
						.find(".civi-loading-effect")
						.addClass("loading")
						.fadeIn();
				},
				success: function (response) {
					var ctx = document
						.getElementById("civi-dashboard_employer")
						.getContext("2d");
					if (
						window.dashboardChart !== undefined &&
						window.dashboardChart !== null
					) {
						window.dashboardChart.destroy();
					}
					var chartEl = document.getElementById("civi-dashboard_employer");
					chartEl.height = 265;
					window.dashboardChart = new Chart(ctx, {
						type: "line",
						data: {
							labels: response.labels_view,
							datasets: [
								{
									label: response.label_view,
									data: response.values_view,
									backgroundColor: colorAccent,
									borderColor: colorAccent,
								},
							],
						},
						options: {
							tooltips: {
								enabled: true,
								mode: "x-axis",
								cornerRadius: 4,
							},
							plugins: {
								legend: {
									display: false,
								},
							},
						},
					});
					$(".civi-chart-employer")
						.find(".civi-loading-effect")
						.removeClass("loading")
						.fadeOut();
				},
			});
		});

		if ($("#civi-dashboard_employer").length) {
			var $this = $("#civi-dashboard_employer"),
				labels = $this.data("labels"),
				values = $this.data("values"),
				label = $this.data("label");

			var ctx = document
				.getElementById("civi-dashboard_employer")
				.getContext("2d");
			if (
				window.dashboardChart !== undefined &&
				window.dashboardChart !== null
			) {
				window.dashboardChart.destroy();
			}
			var chartEl = document.getElementById("civi-dashboard_employer");
			chartEl.height = 265;
			window.dashboardChart = new Chart(ctx, {
				type: "line",
				data: {
					labels: labels,
					datasets: [
						{
							label: label,
							data: values,
							backgroundColor: colorAccent,
							borderColor: colorAccent,
						},
					],
				},
				options: {
					tooltips: {
						enabled: true,
						mode: "x-axis",
						cornerRadius: 4,
					},
					plugins: {
						legend: {
							display: false,
						},
					},
				},
			});
		}

		//Candidate
		$('select[name="chart_candidate"]').on('change', function () {
			var number_days = $(this).find("option:selected").val();
			$.ajax({
				type: "POST",
				url: ajax_url,
				dataType: "json",
				data: {
					action: "civi_chart_candidate_ajax",
					number_days: number_days,
                    security: civi_ajax_nonce.civi_chart_nonce,
				},
				beforeSend: function () {
					$(".civi-chart-candidate")
						.find(".civi-loading-effect")
						.addClass("loading")
						.fadeIn();
				},
				success: function (response) {
					var ctx = document
						.getElementById("civi-dashboard_candidate")
						.getContext("2d");
					if (
						window.dashboardChart !== undefined &&
						window.dashboardChart !== null
					) {
						window.dashboardChart.destroy();
					}
					var chartEl = document.getElementById("civi-dashboard_candidate");
					chartEl.height = 280;
					window.dashboardChart = new Chart(ctx, {
						type: "line",
						data: {
							labels: response.labels_view,
							datasets: [
								{
									label: response.label_view,
									data: response.values_view,
									backgroundColor: colorAccent,
									borderColor: colorAccent,
								},
							],
						},
						options: {
							tooltips: {
								enabled: true,
								mode: "x-axis",
								cornerRadius: 4,
							},
							plugins: {
								legend: {
									display: false,
								},
							},
						},
					});
					$(".civi-chart-candidate")
						.find(".civi-loading-effect")
						.removeClass("loading")
						.fadeOut();
				},
			});
		});

		if ($("#civi-dashboard_candidate").length) {
			var $this = $("#civi-dashboard_candidate"),
				labels = $this.data("labels"),
				values = $this.data("values"),
				label = $this.data("label");

			var ctx = document
				.getElementById("civi-dashboard_candidate")
				.getContext("2d");
			if (
				window.dashboardChart !== undefined &&
				window.dashboardChart !== null
			) {
				window.dashboardChart.destroy();
			}
			var chartEl = document.getElementById("civi-dashboard_candidate");
			chartEl.height = 280;
			window.dashboardChart = new Chart(ctx, {
				type: "line",
				data: {
					labels: labels,
					datasets: [
						{
							label: label,
							data: values,
							backgroundColor: colorAccent,
							borderColor: colorAccent,
						},
					],
				},
				options: {
					tooltips: {
						enabled: true,
						mode: "x-axis",
						cornerRadius: 4,
					},
					plugins: {
						legend: {
							display: false,
						},
					},
				},
			});
		}
	});
})(jQuery);
