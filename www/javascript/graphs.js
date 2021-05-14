/*
 *  $Id: graphs.js,v 1.1 2008-10-10 19:44:15 dan Exp $
 *
 * Copyright (c) 2008 Wesley Shields <wxs@FreeBSD.org>
 *
 * This code uses jquery and flot to generate the graphs.
 * It executes a function from the dropdown choice, which
 * generates a request for a JSON object (generate_content.php)
 * Upon receiving the JSON object it uses flot to generate the
 * graph.
 */
(function drawGraphs(){
	var DATASOURCE_URI = "generate_content.php?ds=";
	var TOP10_GRAPH = function (data) {
		return {
			series: {
				bars: { show: true, align: "center" }
			},
			legend: { show: false },
			grid: { hoverable: true },
			xaxis: {
				showTickLabels: "all",
				showMinorTicks: false,
				ticks: function () {
					var ret = [];
					for (var i = 0; i < data.length; i++) {
						ret.push([i, data[i].label]);
					}
					return ret;
				},
			}
		};
	};
	var graphs = {
		"top10committers()": {
			title: 'Top 10 Comitters',
			opts: TOP10_GRAPH
		},
		"top10committers_src()": {
			title: 'Top 10 Comitters - src',
			opts: TOP10_GRAPH
		},
		"top10committers_doc()": {
			title: 'Top 10 Comitters - doc',
			opts: TOP10_GRAPH
		},
		"top10committers_ports()": {
			title: 'Top 10 Comitters - ports',
			opts: TOP10_GRAPH
		},
		"portsByCategory()": {
			title: 'Top 10 Categories by Port Count',
			subtitle: '(Data is aggregated to commits per month)',
			opts: TOP10_GRAPH
		},
		"portCount()": {
			title: 'Port Count',
			subtitle: '(Data is aggregated to commits per month)',
			opts: {
				xaxis: { mode: "time", timeformat: "%Y-%m", timeBase: "milliseconds" }
			}
		},
		"commitsOverTimeByCommitter()": {
			title: 'Commits Over Time by Committer',
			subtitle: '(Data is aggregated to commits per month)',
			list: '<select multiple size="20" id="committers"></select><br><input type="button" value="Draw Graph!"/>',
			opts: function(data) {
				$.each(data, function(key) {
					$("#committers").append('<option value="' + key + '">' + key + '</option>');
				});
				$("input").bind("click", function updateGraph() {
					var committerData = [];

					$("#committers :selected").each(function () {
						var key = $(this).attr("value");
						if (key && data[key])
							committerData.push(data[key]);
					});

					if (committerData.length > 0)
						$.plot($("#holder"), committerData,
							{
								series: {
									lines: { show: true }
								},
								xaxis:     { mode: "time", timeformat: "%Y-%m", timeBase: "milliseconds" },
								selection: { mode: "x" },
								legend:    { show: true, sorted: true, position: "ne" },
								grid:      { hoverable: true }
							}
						);
				});

				return data;
			}
		},
		"brokenPorts()": {
			title: 'Broken/Expired/Forbidden/New Ports',
			subtitle: '(Limited to last 90 days)',
			list: '<!--  filter list -->',
			opts: function(data) {
				var options = {
					series: {
						lines: { show: true }
					},
					yaxis:  { min: 0 },
					xaxis:  { mode: "time", timeformat: "%b %e", timeBase: "milliseconds" },
					legend: { show: true, position: "ne", sorted: true }
				};

				var listContainer = $("#list");
				$.each(data, function(i, val) {
					val.color = i;
					listContainer.append('<label><input type="checkbox" name="' + val.label + '" checked="checked" />' + val.label + '</label><br>');
					++i;
				});

				listContainer.find("input").click(function plotAccordingToChoices() {
					var series = [];

					listContainer.find("input:checked").each(function () {
						var key = $(this).attr("name");
						$.each(data, function(_, val) {
							if (val.label === key)
								series.push(val);
						});
					});

					if (series.length > 0)
						$.plot($("#holder"), series, options);
				});

				return options;
			}
		},
		"commitsOverTime()": {
			title: 'Commits Over Time',
			subtitle: '(Click and drag around in the graph)',
			overview: true,
			opts: function(data) {
				var options = {
					series: {
						lines: { show: true }
					},
					xaxis: { mode: "time", timeformat: "%Y-%m", timeBase: "milliseconds" },
					selection: { mode: "x" }
				};

				var overview = $.plot($("#overview"), data, {
					series: {
						lines: { show: true, linewidth: 1 }
					},
					shadowSize: 0,
					xaxis: { ticks: [], mode: "time", timeBase: "milliseconds" },
					yaxis: { ticks: [], min: 0, max: 500 },
					selection: { mode: "x" }
				});

				// now connect the two
				var internalSelection = false;

				$("#holder").bind("selected", function (_, area) {
					// do the zooming
					plot = $.plot($("#holder"), data,
					$.extend(true, {}, options, {
						xaxis: { min: area.x1, max: area.x2 }
					}));

					if (internalSelection)
						return; // prevent eternal loop
					internalSelection = true;
					overview.setSelection(area);
					internalSelection = false;
				});

				$("#overview").bind("selected", function (_, area) {
					if (internalSelection)
						return;
					internalSelection = true;
					plot.setSelection(area);
					internalSelection = false;
				});

				return options;
			}
		}
	};

	function changeGraph() {
		var fn = $("select").val();
		var report = graphs[fn];
		if (!report) return;

		$("#title").html(["<h3>", report.title, "</h3>"].join(""));
		if (report.subtitle) $("#title").append(report.subtitle);
		$("#overview").hide();
		$("#list").hide();
		if (!report.opts) return;

		$("#holder").hide();
		$.getJSON(DATASOURCE_URI + fn, function(data) {
			report.overview && $("#overview").show();
			report.list && $("#list").html(report.list).show();

			var opts = typeof report.opts === "function" ? report.opts(data) : report.opts;
			$.plot($("#holder").show(), data, opts);
		});
	}

	$(document).ready(function(){
		$("select").change(changeGraph);
		// Render the first graph.
		changeGraph();
	});
})();