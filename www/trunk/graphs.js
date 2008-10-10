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

$(document).ready(function() {
	// Render the first graph.
	eval($("select option").val());
	$("select").change(function() {
		eval($("select option:selected").val());
	});
});

function top10committers() {
	$("#title").html("<h3>Top 10 Committers</h3>");
	$("#holder").width(800);
	$("#holder").height(500);
	$("#overview").hide();
	$("#list").hide();
	$.getJSON("generate_content.php?ds=top10Committers()", function(d1) {
		$.plot($("#holder"), d1,
			{
				bars: { show: true },
				legend: { show: false },
				xaxis: {
					ticks: function(axis) {
						var ret = [];
						var i = 0;
						for (i = 0; i < d1.length; i++) {
							ret.push([i + .5, d1[i].label]);
						}
						return ret;
					}
				}
			}
		);
	});
}

/* Taken practically verbatim from Flot examples. */
function commitsOverTime() {
	$("#title").html("<h3>Commits Over Time</h3>");
	$("#title").append("(Click and drag around in the graph)");
	$("#holder").width(800);
	$("#holder").height(500);
	$("#overview").show();
	$("#list").hide();
	$.getJSON("generate_content.php?ds=commitsOverTime()", function(d2) {
		var options = {
			points: { show: true },
			xaxis: { mode: "time" },
			selection: { mode: "x" }
		};

		var plot = $.plot($("#holder"), [d2], options );

		var overview = $.plot($("#overview"), [d2], {
			lines: { show: true, linewidth: 1 },
			shadowSize: 0,
			xaxis: { ticks: [], mode: "time" },
			yaxis: { ticks: [], min: 0, max: 500 },
			selection: { mode: "x" }
		});

		// now connect the two
		var internalSelection = false;

		$("#holder").bind("selected", function (event, area) {
			// do the zooming
			plot = $.plot($("#holder"), [d2],
			$.extend(true, {}, options, {
				xaxis: { min: area.x1, max: area.x2 }
			}));

			if (internalSelection)
				return; // prevent eternal loop
			internalSelection = true;
			overview.setSelection(area);
			internalSelection = false;
		});

		$("#overview").bind("selected", function (event, area) {
			if (internalSelection)
				return;
			internalSelection = true;
			plot.setSelection(area);
			internalSelection = false;
		});
	});
}

function commitsOverTimeByCommitter() {
	$("#title").html("<h3>Commits Over Time by Committer</h3>");
	$("#title").append("(Data is aggregated to commits per month)");
	// Because this is a two-stage operation (select then draw graph)
	// the previous graph is left behind.  Flot does not have a "clear"
	// function so we replace the div with a blank one that is sized
	// appropriately.  This is the only graph which needs this.
	$("#holder").replaceWith('<div id="holder" style="width:800;height:500"></div"');
	$("#list").html('<select multiple size="20" id="committers"></select><br>');
	$("#list").append('<input type="button" value="Draw Graph!"/>');
	$("#list").show();
	$("#overview").hide();
	$.getJSON("generate_content.php?ds=commitsOverTimeByCommitter()", function(d3) {
		$.each(d3, function(key, val) {
			$("#committers").append('<option value="' + key + '">' + key + '</option>');
		});

		$("input").bind("click", function updateGraph() {
			var data = [];

			$(":selected").each(function () {
				var key = $(this).attr("value");
				if (key && d3[key])
					data.push(d3[key]);
			});

			if (data.length > 0)
				$.plot($("#holder"), data,
					{
						lines: { show: true, fill: true },
						xaxis: { mode: "time" },
						selection: { mode: "x" }
					}
				);
		});
	});
}

function portsByCategory() {
	$("#title").html("<h3>Top 10 Categories by Port Count</h3>");
	$("#title").append("(Data is aggregated to commits per month)");
	$("#holder").width(800);
	$("#holder").height(500);
	$("#list").hide();
	$("#overview").hide();
	$.getJSON("generate_content.php?ds=portsByCategory()", function(d4) {
		$.plot($("#holder"), d4,
			{
				bars: { show: true },
				legend: { show: false },
				xaxis: {
					ticks: function(axis) {
						var ret = [];
						var i = 0;
						for (i = 0; i < d4.length; i++) {
							ret.push([i + .5, d4[i].label]);
						}
						return ret;
					}
				}
			}
		);
	});
}

/* Taken practically verbatim from Flot examples. */
function brokenPorts() {
	$("#title").html("<h3>Broken/Expired/Forbidden/New Ports</h3>");
	$("#title").append("(Limited to last 90 days)");
	$("#holder").width(800);
	$("#holder").height(500);
	$("#list").show();
	$("#overview").hide();
	$.getJSON("generate_content.php?ds=brokenPorts()", function(d5) {
		var i = 0;
		$.each(d5, function(key, val) {
			val.color = i;
			++i;
		});
    
		var listContainer = $("#list");
		listContainer.html('');
		$.each(d5, function(key, val) {
			listContainer.append('<input type="checkbox" name="' + key + '" checked="checked" >' + val.label + '</input><br>');
		});
		listContainer.find("input").click(plotAccordingToChoices);

		function plotAccordingToChoices() {
			var data = [];

			listContainer.find("input:checked").each(function () {
				var key = $(this).attr("name");
				if (key && d5[key])
					data.push(d5[key]);
			});

			if (data.length > 0)
				$.plot($("#holder"), data,
					{
						yaxis: { min: 0 },
						xaxis: { mode: "time" }
					}
				);
		}

		plotAccordingToChoices();
	});
}

function portCount() {
	$("#title").html("<h3>Port Count</h3>");
	$("#holder").width(800);
	$("#holder").height(500);
	$("#overview").hide();
	$("#list").hide();
	$.getJSON("generate_content.php?ds=portCount()", function(d6) {
		$.plot($("#holder"), [d6],
			{
				xaxis: { mode: "time" }
			}
		);
	});
}
