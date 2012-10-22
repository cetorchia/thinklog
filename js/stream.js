/**
 * Draws a stream graph. Adapted from (c) Michael Bostock:
 *   https://github.com/mbostock/d3/blob/8b775c032c3738f7a8f04444165ad0936572afed/examples/stream/stream.js
 * @param elementName
 * @param width, height
 * @param m The number of data (X values) per layer
 * @param keywordHistory a map e.g. { 'keyword': [2, 0, 19, ...], ...}
 */
function drawStream(elementName, width, height, m, keywordHistory)
{
	var n = Object.keys(keywordHistory).length;
	if (n == 0) {
		return;
	}

	// Generate the data layers (arrays) from the keywordHistory object
	layers = [];
	keywords = [];
	for (keyword in keywordHistory) {
		a = keywordHistory[keyword].map(function(value, i) {
			return {x:i, y:value};
		});
		layers.push(a);
		keywords.push(keyword);
	}

	// Generate the stream graph
	var stackData = d3.layout.stack().offset("wiggle")(layers);

	var color = d3.interpolateRgb("#aad", "#556");

	var mx = m - 1,
	    my = d3.max(stackData, function(d) {
	      return d3.max(d, function(d) {
		return d.y0 + d.y;
	      });
	    });

	var area = d3.svg.area()
	    .x(function(d) { return d.x * width / mx; })
	    .y0(function(d) { return height - d.y0 * height / my; })
	    .y1(function(d) { return height - (d.y + d.y0) * height / my; });

	var vis = d3.select("#" + elementName)
	  .append("svg")
	    .attr("width", width)
	    .attr("height", height);

	vis.selectAll("path")
	    .data(stackData)
	  .enter().append("path")
	    .style("fill", function() { return color(Math.random()); })
	    .attr("d", area)
            .on("click", function(d, i) { window.open('./?q='+keywords[i], "_self"); })
	  .append("title")
	    .text(function(d, i) { return keywords[i]; });
}

