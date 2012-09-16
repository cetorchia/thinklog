<?php

/**
 * HTML elements you can use in your PHP programs!
 * (c) 2010 Carlos E. Torchia; under GPL v2; use at your own risk!
 */

	class	HtmlElement {

		protected	$content;
		protected	$tag;
		protected	$attributes;
		protected	$needsClosure;

		//
		// Accessors and the like.
		//

		public function	setTag($tag) { $this->tag=$tag; }
		public function	getTag() { return($this->tag); }

		public function	setContent($content) { $this->content=$content; }
		public function	addContent($content) { $this->setContent($this->getContent().$content."\n"); }
		public function	getContent() { return($this->content); }

		public function	setAttributes($attributes) { $this->attributes=$attributes; }
		public function	getAttributes() { return($this->attributes); }

		public function	set($name,$value) {
			if(isset($name)&&isset($value)) {
				$this->attributes[$name]=$value;
			}
		}
		public function	get($name) { return($this->attributes[$name]); }

		public function	setNeedsClosure($needsClosure) { $this->needsClosure=$needsClosure; }
		public function	getNeedsClosure() { return($this->needsClosure); }

		//
		// Constructor
		//

		public function	__construct($tag=null) {
			$this->setTag($tag);
			$this->setNeedsClosure(true);
			$this->setAttributes(array());
			$this->setContent("");
		}

		//
		// Returns the element's HTML
		//

		public function	draw()
		{

			$output="";

			//
			// Get the different attributes of this element,
			// as well as its content.
			//

			$tag = $this->getTag();
			$content = $this->getContent();
			$attributes = $this->getAttributes();

			//
			// Produce the output of the tag.
			//

			if(isset($tag)) {

				$output.="<$tag";

				//
				// Produce output for tag's attributes.
				//

				if(isset($attributes)) {
					foreach($attributes as $name => $value) {
						$output.=" $name=\"".htmlentities($value)."\"";
					}
				}

				//
				// Produce output for tag's content
				//

				// Add the content

				if(isset($content)&&($content!='')) {
					$output.="> $content";
					$output.=$this->getNeedsClosure()?"</$tag>\n":"";
				}

				else if($this->getNeedsClosure()) {
					$output.=" />\n";
				}

				else {
					$output.=">\n";
				}

			}

			return($output);

		}

		public function	__toString() {
			return($this->draw());
		}
	}

	class	Anchor extends HtmlElement
	{

		public function	__construct($href=null,$content=null) {
			parent::__construct('a');
			$this->set('href',$href);
			$this->setContent($content);
		}

	}

	class	Body extends HtmlElement
	{

		public function	__construct($content=null) {
			parent::__construct('body');
			$this->setContent($content);
		}

	}

	class	Cell extends HtmlElement
	{

		public function	__construct($content=null) {
			parent::__construct('td');
			$this->setContent($content);
		}

	}

	class	Input extends HtmlElement
	{

		public function	__construct($type=null,$name=null,$value=null) {
			parent::__construct('input');
			$this->set('type',$type);
			$this->set('name',$name);
			$this->set('value',$value);
		}

	}

	class	Checkbox extends Input
	{

		public function	__construct($name=null,$value=null,$checked=false) {
			parent::__construct('checkbox',$name,$value);
			$this->set('checked',$checked?'checked':null);
		}

	}

	class	Div extends HtmlElement
	{

		public function	__construct($content=null) {
			parent::__construct('div');
			$this->setContent($content);
		}

	}

	class	Form extends HtmlElement
	{

		public function	__construct($content=null) {
			parent::__construct('form');
			$this->setContent($content);
		}

	}

	class	Heading extends HtmlElement
	{

		public function	__construct($level,$content=null) {
			parent::__construct("h$level");
			$this->setContent($content);
		}

	}

	class	Head extends HtmlElement
	{

		public function	__construct($content=null) {
			parent::__construct('p');
			$this->setContent($content);
		}

	}

	class	Html extends HtmlElement
	{

		public function	__construct($content=null) {
			parent::__construct('html');
			$this->setContent($content);
		}

	}

	class	Image extends HtmlElement
	{

		public function	__construct($src=null,$title=null) {
			parent::__construct('img');
			$this->set('src',$src);
			$this->set('title',$title);
		}

	}

	class	LineBreak extends HtmlElement
	{

		public function	__construct() {
			parent::__construct('br');
		}

	}

	class	Link extends HtmlElement
	{

		public function	__construct($rel=null,$type=null,$href=null) {
			parent::__construct('link');
			$this->set('rel',$rel);
			$this->set('type',$type);
			$this->set('href',$href);
		}

	}

	class	ListElement extends HtmlElement
	{

		public function	__construct($content=null) {
			parent::__construct('li');
			$this->setContent($content);
		}

	}

	class	Meta extends HtmlElement
	{

		public function	__construct($type=null,$name=null,$content=null) {
			parent::__construct('meta');
			$this->set('content',$content);
			if(isset($type)&&(($type=='http-equiv')||($type=='name'))) {
				$this->set($type,$name);
			}
		}

	}

	class	Paragraph extends HtmlElement
	{

		public function	__construct($content=null) {
			parent::__construct('p');
			$this->setContent($content);
		}

	}

	class	Row extends HtmlElement
	{

		public function	__construct($content=null) {
			parent::__construct('tr');
			$this->setContent($content);
		}

	}

	class	Span extends HtmlElement
	{

		public function	__construct($style=null,$content=null) {
			parent::__construct('span');
			$this->set('style',$style);
			$this->setContent($content);
		}

	}

	class	Submit extends Input
	{

		public function	__construct($name=null,$value=null) {
			parent::__construct('submit',$name,$value);
		}

	}

	class	Table extends HtmlElement
	{

		public function	__construct($content=null) {
			parent::__construct('table');
			$this->setContent($content);
		}

	}

	class	TextArea extends HtmlElement
	{

		public function	__construct($name=null,$rows=null,$columns=null,$readonly=false,$content=null) {
			parent::__construct('textarea');
			$this->set('name',$name);
			$this->set('rows',$rows);
			$this->set('cols',$columns);
			$this->set('readonly',$readonly?'readonly':null);
			$this->setContent($content);
		}

	}

	class	TextField extends Input
	{

		public function	__construct($name=null,$value=null,$maxlength=null) {
			parent::__construct('text',$name,$value);
			$this->set('maxlength',$maxlength);
		}

	}

	class Title extends HtmlElement
	{
		public function __construct($content=null)
		{
			parent::__construct("title");
			$this->setContent($content);
		}
	}

	class	UnorderedList extends HtmlElement
	{

		public function	__construct($content=null) {
			parent::__construct('ul');
			$this->setContent($content);
		}

	}
