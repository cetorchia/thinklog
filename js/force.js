/**
 * Generates an interactive force layout graph.
 *
 * Adapted from http://bl.ocks.org/1377729 by Moritz Stefaner or Michael Bostock.
 * Adaptations by Carlos Torchia for presenting keyword relationships
 * in Thinklog to
 * - Process a given set of nodes and edges for the graph
 * - Change charge so that nodes are closer together
 * - Given node info allows caller to specify different sizes and onclick URLs for each node
 * - Given weights allows caller to specify different edge thickness
 *
 * @param elementName Element to draw graph in
 * @param w, h Width and height
 * @param nodeInfo Node map i.e. node[name] = { 'size': 12, 'url': 'http://localhost'} or just {}
 * @param edges Edge map i.e. edges[u][v] = Weight of (u,v) (float, undefined if not adjacent)
 */
function drawGraph(elementName, w, h, nodeInfo, edges)
{
	var labelDistance = 0;

	var vis = d3.select("#" + elementName).append("svg:svg").attr("width", w).attr("height", h);

	var nodes = [];
	var labelAnchors = [];
	var labelAnchorLinks = [];
	var links = [];

	// Generate node label and anchor objects
	for(var name in nodeInfo) {
		var node = {
			label : name
		};
		nodes.push(node);
		labelAnchors.push({
			node : node
		});
		labelAnchors.push({
			node : node
		});
	};

	// Generate link objects
	for(var i = 0; i <= nodes.length - 1; i++) {
		var u = nodes[i].label;
		for(var j = 0; j <= nodes.length - 1; j++) {
			var v = nodes[j].label;
			if(u in edges && v in edges[u]) {
				links.push({
					source : i,
					target : j,
					weight : edges[u][v]
				});
			}
		}

		// Generate links for the node labels
		labelAnchorLinks.push({
			source : i * 2,
			target : i * 2 + 1,
			weight : 1
		});
	};

	//
	// Generate force layout graph
	//

	var force = d3.layout.force().size([w, h]).nodes(nodes).links(links).gravity(1).linkDistance(50).charge(-1000).linkStrength(function(x) {
		return 1;
	});

	force.start();

	var force2 = d3.layout.force().nodes(labelAnchors).links(labelAnchorLinks).gravity(0).linkDistance(0).linkStrength(8).charge(-100).size([w, h]);
	force2.start();

	var link = vis.selectAll("line.link").data(links).enter().append("svg:line").attr("class", "link").style("stroke", "#CCC").style("stroke-width", function(l) { return l.weight;});

	var node = vis.selectAll("g.node").data(force.nodes()).enter().append("svg:g").attr("class", "node");
	node.append("svg:circle").attr("r", function(n) {
		return nodeInfo[n.label].size ? nodeInfo[n.label].size+5 : 5;
	}).style("fill", "#555").style("stroke", "#FFF").style("stroke-width", 3);
	node.call(force.drag);

	var anchorLink = vis.selectAll("line.anchorLink").data(labelAnchorLinks);
	var anchorNode = vis.selectAll("g.anchorNode").data(force2.nodes()).enter().append("svg:g").attr("class", "anchorNode");
	anchorNode.append("svg:circle").attr("r", 0).style("fill", "#FFF");
		anchorNode.append("svg:text").text(function(d, i) {
		return i % 2 == 0 ? "" : d.node.label
	}).style("fill", "#555").style("font-family", "Arial").style("font-size", function(d) {
		var size = nodeInfo[d.node.label].size ? nodeInfo[d.node.label].size+12 : 12;
		return size;
	}).style("cursor", function(d) {
		return nodeInfo[d.node.label].url ? "hand" : "default";
	}).on("click", function(d) {
		if (nodeInfo[d.node.label].url) {
			window.open(nodeInfo[d.node.label].url, "_self");
		}
	});

	var updateLink = function() {
		this.attr("x1", function(d) {
			return d.source.x;
		}).attr("y1", function(d) {
			return d.source.y;
		}).attr("x2", function(d) {
			return d.target.x;
		}).attr("y2", function(d) {
			return d.target.y;
		});

	}

	var updateNode = function() {
		this.attr("transform", function(d) {
			return "translate(" + d.x + "," + d.y + ")";
		});

	}

	force.on("tick", function() {

		force2.start();

		node.call(updateNode);

		anchorNode.each(function(d, i) {
			if(i % 2 == 0) {
				d.x = d.node.x;
				d.y = d.node.y;
			} else {
				var b = this.childNodes[1].getBBox();

				var diffX = d.x - d.node.x;
				var diffY = d.y - d.node.y;

				var dist = Math.sqrt(diffX * diffX + diffY * diffY);

				var shiftX = b.width * (diffX - dist) / (dist * 2);
				shiftX = Math.max(-b.width, Math.min(0, shiftX));
				var shiftY = 5;
				this.childNodes[1].setAttribute("transform", "translate(" + shiftX + "," + shiftY + ")");
			}
		});

		anchorNode.call(updateNode);

		link.call(updateLink);
		anchorLink.call(updateLink);

	});
}
