<?php

/**
 * Simple library for drawing a graph using dracula_graph.
 * Precondition: dracula, raphael, and jquery scripts included already.
 */

class GraphDrawer {
	protected $id;
	protected $width, $height;
	protected $nodes;
	protected $edges;

	// @param $id Element ID of canvas
	function __construct($id, $width, $height) {
		$this->id = $id;
		$this->width = $width;
		$this->height = $height;
		$this->nodes = "";
		$this->edges = "";
	}

	// Adds a node with given id, font-size, and URL
	function addNode($id, $url=null, $size=null) {
		$content = "";
		if ($size) {
			$content .= "size: $size, ";
		}
		if ($url) {
			$content .= "url: \"$url\", ";
		}
		$this->nodes .= 'g.addNode("'.$id.'", {'.$content.'});'."\n";
	}

	// Adds an edge between $id1 and $id2 ($weight optional)
	function addEdge($id1, $id2, $weight=null) {
		if ($weight == null) {
			$weight = 1;
		}
		$this->edges .= 'g.addEdge("'.$id1.'", "'.$id2.'", {stroke:"#000", fill:"#000|'.$weight.'"});'."\n";
	}

	// Renders the javascript to display the graph on the canvas
	function draw() {
		$output = "<script type=\"text/javascript\">\n";
		$output .= "var g = new Graph();\n";
		$output .= $this->nodes;
		$output .= $this->edges;
		$output .= "var layouter = new Graph.Layout.Spring(g);\n";
		$output .= "var renderer = new Graph.Renderer.Raphael('{$this->id}', g, {$this->width}, {$this->height});\n";
		$output .= "layouter.layout();\n";
		$output .= "renderer.draw();\n";
		$output .= "</script>\n";
		return $output;
	}
}
