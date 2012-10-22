<?php

/**
 * Simple library for drawing a graph using force.js
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
		$this->nodes = array();
		$this->edges = array();
	}

	// Adds a node with given name, font-size, and URL
	// If it already exists, it is updated, not replaced.
	function addNode($name, $url=null, $size=null) {
		if (!$this->nodes[$name]) {
			$this->nodes[$name] = array();
		}
		$node = &$this->nodes[$name];
		if ($size) {
			$node["size"] = $size;
		}
		if ($url) {
			$node["url"] = $url;
		}
	}

	// Adds an edge between nodes $name1 and $name2 ($weight optional)
	function addEdge($name1, $name2, $weight=null) {
		if (!$this->edges[$name1]) {
			$this->edges[$name1] = array();
		}
		if ($weight == null) {
			$weight = 1;
		}
		$this->edges[$name1][$name2] = $weight;
	}

	// Renders the javascript to display the graph on the canvas
	function draw() {
		$nodes = json_encode($this->nodes);
		$edges = json_encode($this->edges);
		$output = "<script type=\"text/javascript\">\n";
		$output .= "drawGraph(\"$this->id\", $this->width, $this->height, $nodes, $edges);\n";
		$output .= "</script>\n";
		return $output;
	}
}
