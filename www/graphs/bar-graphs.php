<?php
	#
	# $Id: bar-graphs.php,v 1.2 2006-12-17 12:06:25 dan Exp $
	#


// bar graph for Fresh Ports 2
// written by Marcin Gryszkalis <mg@cerint.pl> or <dagoon@math.uni.lodz.pl>
// v.1.1 2002-04-04

class dg_BarGraph {
	// width is set, height is calculated
	var $width;

	var $gradient1;
	var $gradient2;
	var $gradient_map_r; // 64 elements
	var $gradient_map_g;
	var $gradient_map_b;

	var $bar_width;
	var $bar_space_width; // space above and below bar

	var $title;				// title text
	var $title_size;
	var $title_color;
	var $title_bgcolor;
	var $title_margin;

	var $background_color;
	var $ticks_color;

	var $x_ticks_num;

	var $margin_t;
	var $margin_b;
	var $margin_l;
	var $margin_r;

	var $font_name;
	var $label_size;
	var $label_color;

	// two sets of data, x must be numeric
	var $values;
	var $labels;
	var $urls;

	var $footer;
	var $footer_size;
	var $footer_color;
	var $footer_bgcolor;
	var $footer_margin;

	var $axis_height;
	var $axis_color;
	var $axis_label_color;
	var $axis_grid_color;
	var $axis_size; // font size

	var $axis_label;	
	
	// main constructor
	function dg_BarGraph() {
		$this->width = 400;

		$this->gradient1 = array(0,100,210);
		$this->gradient2 = array(150,0,100);

		$this->bar_width       = 20;
		$this->bar_space_width = 5;

		$this->title	         = "default title";
		$this->title_size       = 18;
		$this->title_color      = array(255,255,255);
		$this->title_bgcolor    = array(160,0,0);
		$this->title_margin     = 4;

		$this->footer           = "default footer";
		$this->footer_size      = 10;
		$this->footer_color     = array(255,255,255);
		$this->footer_bgcolor   = array(160,0,0);
		$this->footer_margin    = 1;

		$this->background_color = array(233,233,233);
		$this->ticks_color      = array(200,200,200);

		$this->x_ticks_num      = 5;

		$this->margin_t         = 4;
		$this->margin_b         = 4;
		$this->margin_l         = 200;
		$this->margin_r         = 16;

		$this->font_name        = $_SERVER['DOCUMENT_ROOT'] . '/graphs/tahoma.ttf';
		$this->font_name_bold   = $_SERVER['DOCUMENT_ROOT'] . '/graphs/tahomabd.ttf';

		$this->label_size       = 12;
		$this->label_color      = array(0,0,0);

		$this->axis_height      = 30;
		$this->axis_color       = array(0,0,0);
		$this->axis_label_color = array(0,0,0);
		$this->axis_grid_color  = array(140,140,140);
		$this->axis_size        = 1; // font number!

		$this->axis_label = 'height of the tree :)';
		
		// two sets of data, values must be numeric
		$this->values = array(0);
		$this->labels = array('default');
	}

	function calculate_gradients() {
		$dr = ($this->gradient2[0] - $this->gradient1[0]) / 64;
		$dg = ($this->gradient2[1] - $this->gradient1[1]) / 64;
		$db = ($this->gradient2[2] - $this->gradient1[2]) / 64;

		for ($i=0; $i<64; $i++) {
			$this->gradient_map_r[$i] = $this->gradient1[0] + $i * $dr;
			$this->gradient_map_g[$i] = $this->gradient1[1] + $i * $dg;
			$this->gradient_map_b[$i] = $this->gradient1[2] + $i * $db;
		}
	}

	function _allocate_color($im, $colorarray) {
		return imagecolorallocate($im, 
			$colorarray[0], 
			$colorarray[1], 
			$colorarray[2]);
	}
	// ------------ MAIN SHOW METHOD -----------------

	function show($file = '-') {
		$map = '';
		header('Content-type: image/png');

		$this->calculate_gradients();

		// count left margin [THIS IS NEW PIECE OF CODE]
		$maxw = 0;
		for ($i = 0; $i < count($this->values); $i++) {
			$box = imagettfbbox(
				$this->label_size,
				0,
				$this->font_name,
				$this->labels[$i]);

			$w = abs($box[2] - $box[0]);

			if ($w > $maxw) $maxw = $w;
		}

		$maxw += 8;

		if ($maxw > $this->margin_l)
		$this->margin_l = $maxw;

		// [END OF NEW PIECE OF CODE]

		// calculate sizes
		$field_width = $this->width - $this->margin_l - $this->margin_r;
				
		$field_height = count($this->values)
			* ($this->bar_width + 2 * $this->bar_space_width);

		// calc title height
		$box = imagettfbbox(
			$this->title_size,
			0,
			$this->font_name,
			$this->title);

		$title_height = abs($box[7] - $box[1]) + 2 * $this->title_margin;
		$title_width = abs($box[2] - $box[0]);

		// calc footer height
		$box = imagettfbbox(
			$this->footer_size,
			0,
			$this->font_name_bold,
			$this->footer);

		$footer_height = abs($box[7] - $box[1]) + 2 * $this->footer_margin;
		$footer_width = abs($box[2] - $box[0]);

		// total height
		$height = 
			$field_height
			+ $this->margin_t
			+ $this->margin_b
			+ $title_height
			+ $footer_height
			+ $this->axis_height;

		$im = imagecreate($this->width,$height);

		// colors
		$c_bg       = $this->_allocate_color($im, $this->background_color);
		$c_g1       = $this->_allocate_color($im, $this->gradient1);
		$c_titlebg  = $this->_allocate_color($im, $this->title_bgcolor);
		$c_title    = $this->_allocate_color($im, $this->title_color);
		$c_footerbg = $this->_allocate_color($im, $this->footer_bgcolor);
		$c_footer   = $this->_allocate_color($im, $this->footer_color);
		$c_label    = $this->_allocate_color($im, $this->label_color);
		$c_axis     = $this->_allocate_color($im, $this->axis_color);
		$c_axisgr   = $this->_allocate_color($im, $this->axis_grid_color);
		$c_axislbl  = $this->_allocate_color($im, $this->axis_label_color);
		$c_black    = $this->_allocate_color($im, array(0,0,0));

		for ($i=0; $i<64; $i++) {
			$c_gradient[$i] 
				= $this->_allocate_color($im, array(
					$this->gradient_map_r[$i],
					$this->gradient_map_g[$i],
					$this->gradient_map_b[$i]));
		}

		// draw title
		imagefilledrectangle($im,
			0,
			0,
			$this->width,
			$title_height,
			$c_titlebg);

		imagettftext($im,
			$this->title_size,
			0,
			($this->width - $title_width) / 2,
			$title_height-$this->title_margin,
			$c_title,
			$this->font_name,
			$this->title);

		// draw footer
		imagefilledrectangle($im,
			0,
			$height - $footer_height - 1,
			$this->width,
			$height - 1,
			$c_footerbg);

		imagettftext($im,
			$this->footer_size,
			0,
			4, //($this->width - $footer_width) / 2,
			$height - $this->footer_margin - 3, // should be -1 but...
			$c_footer,
			$this->font_name_bold,
			$this->footer);

		// axes
		imageline($im,
			$this->margin_l,
			$this->margin_t + $title_height + 1,
			$this->margin_l,
			$height - $footer_height - $this->axis_height - 1,
			$c_axis);

		imageline($im,
			$this->width - $this->margin_r,
			$height - $footer_height - $this->axis_height - 1,
			$this->margin_l,
			$height - $footer_height - $this->axis_height - 1,
			$c_axis);

		// axis label
		imagestring($im,
			$this->axis_size,
			$this->margin_l + (($field_width-imagefontwidth($this->axis_size)*strlen($this->axis_label))/2),
			$height - $footer_height - $this->axis_height - 1 + 15,
			$this->axis_label,
			$c_axislbl);
			
		// calculate how many ticks we'll need
		$max = max($this->values);
		$t = 1000000000;
		while ($t>0.5) {
			if ($max >= $t) {
				$rmax = ceil($max/$t)*$t; // take first point above max
				break;
			}
			$t /= 10;
		}
		
		if ($rmax==0) { // just for sanity 
			$rmax = 1;
			$max  = 0;
			$t    = 1;
		}
		
		$steps = ($rmax/$t); // how many ticks
		$d = $field_width/$steps; // space between ticks in pixels

		for ($i=1; $i<=$steps; $i++) {
			// grid
			imageline($im,
				$this->margin_l + $i*$d,
				$this->margin_t + $title_height + 1,
				$this->margin_l + $i*$d,
				$height - $footer_height - $this->axis_height - 1 + 3,
				$c_axisgr);

			// label
			$v = (int)($i * $t);
			$w = imagefontwidth($this->axis_size) * strlen($v);

			imagestring($im,
				$this->axis_size,
				$this->margin_l + $i*$d - $w/2,
				$height - $footer_height - $this->axis_height - 1 + 5,
				$v,
				$c_black);

		}
	
		// bars
		$x = $this->margin_t;
		for ($i = 0; $i < count($this->values); $i++) {
			if ($this->values[$i] >= 0) {
			
				$v = ($this->values[$i] * $field_width)/$rmax;

				if ($v) {
					$gd = 64 / $v;
				} else {
					$gd = 0;
				}

				$x1 = $this->margin_l;
			
				$y1 = $this->margin_t 
					+ $title_height
					+ $i * (2 * $this->bar_space_width + $this->bar_width)
					+ $this->bar_space_width;
			
				$x2 = $this->margin_l + $v;
			
				$y2 = $this->margin_t 
					+ $title_height
					+ $i * (2 * $this->bar_space_width + $this->bar_width)
					+ $this->bar_space_width
					+ $this->bar_width;

				// draw bar
				for ($j=0; $j<$v; $j++) {
					imageline($im, 
						$x1+$j, $y1, 
						$x1+$j, $y2, 
						$c_gradient[$gd*$j]);
				}

				imagerectangle($im, $x1, $y1, $x2, $y2,	$c_black);

			}

			// label
			$box = imagettfbbox(
				$this->label_size,
				0,
				$this->font_name,
				$this->labels[$i]);

			$h = abs($box[7] - $box[1]) + 2 * $this->title_margin;
			$w = abs($box[2] - $box[0]);

			imagettftext($im,
				$this->label_size,
				0,
				$this->margin_l - $w - 5,
				$y2 - (($this->bar_width - $h) / 2),
				$c_axislbl,
				$this->font_name,
				$this->labels[$i]);


			// map
			$map .= ($y1.":".$this->urls[$i]."\n");
	
		}

		
		$map .= ($y2.":"."end-of-file-marker\n");

		if ($file == "-") {
			imagepng($im);
		} else {
			imagepng($im, $file);
		}

		return $map;
	}

}

?>