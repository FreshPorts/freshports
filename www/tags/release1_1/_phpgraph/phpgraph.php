<?php
include './_phpgraph/image.php';

function err($file, $line, $msg) {
	print("Error <b>$file</b> line <b>$line</b>: $msg"."<br>\n");
}

class graph {
	// public
	var $type;		// bar
	var $margin;	// margins, array
	var $bar_space;	// distance between bars
	var $barcolor;
	// private
	var $x_scale;	// bar width
	var $y_scale;
	// external object pointers
	var $title;
	var $x;
	var $y;
	var $legend;

	function graph(&$im, $type, $data, $title_text) {
		if (sizeof($data) < 2)
			err(__FILE__, __LINE, 'data passed to graph() must have at least 2 arrays, found '.sizeof($data));

		$this->im = $im;

		$this->margin['top'] = 15;
		$this->margin['left'] = 15;
		$this->margin['right'] = 15;
		$this->margin['bottom'] = 15;
		$this->bar_space = 0;

#		$def_font = "/usr/X11R6/lib/X11/fonts/TrueType/arial.ttf";
		$def_font = "/usr/local/etc/freshports/ttf/arial.ttf";

		$this->title = new text($title_text);
		$this->title->font = $def_font;
		$this->title->fontsize = 18;		// should be dynamic, ugh
		$this->title->color = $this->im->color['black'];
		$this->title->text_padding = 10;
		
		$xdata = current($data);
		$this->x = new xaxis($xdata);
		$this->x->color = $this->im->color['black'];
		$this->x->label->font = $def_font;
		$this->x->label->color = $this->im->color['white'];
		$this->x->label->format = array("shadow");
		$this->x->label->text_padding = 15;

		// shift hack
		next($data);
		while (list(,$d) = each($data))
			$ydata[] = $d;
			
		$this->y = new yaxis($ydata);	// $ydata is multi-dim
		$this->y->color = $this->im->color['black'];
		$this->y->label->font = $def_font;
		$this->y->label->color = $this->im->color['black'];
		$this->y->label->text_padding = 15;

	}

	function get_xmargin() {
		$this->margin['x'] = $this->margin['left'] + $this->margin['right'];
		return $this->margin['x'];
	}
	 
	function get_ymargin() {
		$this->margin['y'] = $this->margin['top'] + $this->margin['bottom'];
		return $this->margin['y'];
	}



	function calc_scale() {
		$this->title->calc_bbox();

		$this->x->scale = floor(($this->im->height - $this->get_ymargin() -  $this->title->text_padding - $this->title->bbox_height)/sizeof($this->x->data) - $this->bar_space);

		// conversion to percent
		$this->x->label->text_padding = $this->x->label->text_padding * .01 * $this->x->scale * 2;	
		$this->y->label->text_padding = $this->y->label->text_padding * .01 * $this->x->scale * 2;	

		for ($fontsize=8, $fontheight=0; $fontheight < ($this->x->scale - $this->x->label->text_padding); ++$fontsize) {
			$bbox = imageTTFbbox($fontsize, 0, $this->x->label->font, "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890-");
			$fontheight = abs($bbox[7] - $bbox[1]);
			$this->x->label->fontsize = $fontsize;
			$this->y->label->fontsize = $fontsize;
		}

		$y_largest_datum = $this->y->get_largest_datum();
		if ($y_largest_datum == 0 || $y_largest_datum == "" || !isset($y_largest_datum))
			err(__FILE__, __LINE__, "divide by zero; caught");
		$this->y->scale = ($this->im->width - $this->get_xmargin() - $this->y->sizeof_longest_datum() - $this->y->label->text_padding) / $this->y->get_largest_datum();

	}


	function draw_title() {
		$this->title->calc_bbox();

		$t_x = ($this->im->width - $this->title->bbox_width)/2;
		$t_y = $this->margin['top'];

		$this->title->draw(&$this->im, $t_x, $t_y);
	}

	function draw() {
		$this->draw_title();
		$this->calc_scale();

		$y_label_field_width = $this->y->sizeof_longest_datum();

		$py = $this->margin['top'] + $this->title->bbox_height + $this->title->text_padding;

		reset($this->x->data);
		reset($this->y->data);

		while(list($x, $xlabel) = $this->x->get_datum()) {
			$ydata = $this->y->get_data($x);


      		$ylabel = $ydata[0]; 
        
 
			$px = $this->margin['left'] + $y_label_field_width;

				
			$this->y->label->calc_bbox($ylabel);
			$this->y->label->draw(&$this->im,
				$px - $this->y->label->bbox_width,
				$py + ($this->x->scale - $this->y->label->bbox_height)/2,
				$ylabel
			);

			$px += $this->y->label->text_padding;
			$px2 = $px + ($ydata[0] * $this->y->scale-1);

			$py = $py;
			$py2 = $py + ($this->x->scale-1);

			// bar
			imagefilledrectangle($this->im->im,		$px+( 1),	$py+( 1),		$px2+( 0),	$py2+( 1),	$this->im->color['white']);
			imagefilledrectangle($this->im->im,		$px+( 0),	$py+( 0),		$px2+(-1),	$py2+( 0),	$this->im->color['black']);
			imagefilledrectangle($this->im->im,		$px+( 1),	$py+( 1),		$px2+(-3),	$py2+(-2),	$this->im->color['white']);
			imagefilledrectangle($this->im->im,		$px+( 2),	$py+( 2),		$px2+(-3),	$py2+(-2),	$this->y->color);

			$this->x->label->calc_bbox($xlabel);
			$this->x->label->draw(&$this->im,
				$px + $this->x->label->text_padding,
				$py + ($this->x->scale - $this->x->label->bbox_height)/2,
				$xlabel
			);
			// get ready for next datum
			$py += ($this->x->scale+$this->bar_space);

		}
		$this->im->draw();
	}


	function save($filename) {
		$this->im->save($filename);
	}


	function destroy() {
		$this->im->destroy();
	}
}


?><?php

class obj {
	// public
	var $color;
	var $px,$py;
	// private
}

class axis extends obj {
	// public
	var $label_padding;	
	// internal
	var $scale;
	// private
	// external object pointers
	var $label;

	function axis() {
		$this->scale = 0;
		$this->label = new text;
		$this->label_padding = 5;
	}
}

class xaxis extends axis {
	// public
	var $data;
	var $barcolor;

	function xaxis($data) {
		$this->axis();	// parent constructor
		$this->data = $data;
	}

	function get_datum() {
		$x = key($this->data);
		if (list(,$datum) = each ($this->data))
			return(array($x, $datum));
		else
			return(FALSE);
	}

	function sizeof_longest_datum() {
		reset($this->data);
		if (is_string($this->data[0])) {
			$datum = $this->get_datum();
				if (strlen($datum) > strlen($longest)) 
					$longest = $datum;
		} elseif (is_int($data[0])) {
			//hack, assumes all numbers have equal width
			rsort($data);
			$longest = $data[0];
		} else
			err(__FILE__, __LINE__, 'sizeof_longest_datum: gettype($data[0]) = ('.gettype($data[0]).') not expected');
		$bbox = imageTTFbbox($this->label->fontsize, 0, $this->label->font, $longest);
		return(abs($bbox[4] - $bbox[7]));
	}

	function get_largest_datum() {
		reset($this->data);
		$data = $this->data;
		if (is_int($data[0])) {
			rsort($data);
			$largest = $data[0];
		} else
			err(__FILE__, __LINE__, 'get_largest_datum: gettype($data[0]) = ('.gettype($data[0]).') not expected');
		return($largest);
	}

}
class yaxis extends axis {
	// public
	var $data = array();
	var $barcolor = array();

	function yaxis($data) {
		$this->axis();	// parent constructor
		if (!is_array($data[0]))
			err(__FILE__, __LINE__, 'yaxis constructor: $data not in correct format array(array()..))');
		$this->data = $data;
	}

	// return all y-values for x
	function get_data($x) {
		for($n=0; $n<=sizeof($this->data)-1;++$n)	// number of y-values
			$data[] = $this->data[$n][$x];
		return $data;
	}

	function sizeof_longest_datum() {
		$longest = "";
		// the y axis can have many datasets
		reset($this->data);
		while ($datas=current($this->data)) {
			next($this->data);
			reset($datas);
			if (is_string($datas[0])) {
				while(list(,$data) = each ($datas)) {
					if (strlen($data) > strlen($longest)) 
						$longest = $data;
				}
			} elseif (is_int($datas[0])) {
				//hack, assumes all numbers have equal width
				rsort($datas);
				if ($datas[0] > $longest)
					$longest = $datas[0];
			} else
				err(__FILE__, __LINE__, 'sizeof_longest_datum: gettype($datas[0]) = ('.gettype($datas[0]).') not expected');
			
		}
		$bbox = imageTTFbbox($this->label->fontsize, 0, $this->label->font, $longest);
		return(abs($bbox[4] - $bbox[7]));
	}

	function get_largest_datum() {
		$largest = 0;
		// the y axis can have many datasets
		reset($this->data);
		while ($datas=current($this->data)) {
			next($this->data);
			reset($datas);
			if (is_int($datas[0])) {
				rsort($datas);
				if ($datas[0] > $largest)
					$largest = $datas[0];
			} else
				err(__FILE__, __LINE__, 'get_largest_datum: gettype($datas[0]) = ('.gettype($datas[0]).') not expected');

		return($largest);
		}
	}

}

?><?php

class text extends obj {
	// public
	var $text;
	var $text_padding;
	var $font;
	var $fontsize;
	// read-only
	var $bbox_width;
	var $bbox_height;

	function text($text="") {
		$this->text = $text;
		$this->text_padding = 5;
		$this->font = "";
		$this->fontsize = 0;
	}

	function calc_bbox($text="") {
		if ($text == "")
			$text = $this->text;
		
		list(,$ll_y, , , $ur_x, , $ul_x, $ul_y) = imageTTFbbox($this->fontsize, 0, $this->font, $text);
		$this->bbox_height = $ll_y-$ul_y;
		$this->bbox_width = $ur_x-$ul_x;
	}

	function draw(&$im, $px="", $py="", $text="") {
		if ($text == "")
			$text = $this->text;

		$this->calc_bbox();
		if ($py == "")
			if ($px == "") {
				$px = $this->px;
				$py = $this->py;
			} else
				err(__FILE__, __LINE__, "draw: number of args");

		// weird but essential hack, I hate TTF
		$bbox = imageTTFbbox($this->fontsize, 0, $this->font, "ABC");
		$baseline = abs($bbox[7] - $bbox[1]);

		// wants baseline, from upper-left
		$px = $px;
		$py = $py + $baseline;

		if (isset($this->format)) {
			reset($this->format);
			while(list(, $val) = each($this->format)) {
				switch ($val) {
					case "bleed":	// Bleed the font, print 5 times
						for($i=-1;$i<2;++$i) {
							imageTTFtext($im->im, $this->fontsize, 0, $px+$i, $py, $im->color, $this->font, $text);
							imageTTFtext($im->im, $this->fontsize, 0, $px, $py+$i, $im->color, $this->font, $text);
						}
					break;

					case "shadow":
						imageTTFtext($im->im, $this->fontsize, 0, $px+1, $py+1, $im->color['black'], $this->font, $text);
						imageTTFtext($im->im, $this->fontsize, 0, $px, $py, $this->color, $this->font, $text);
						imageTTFtext($im->im, $this->fontsize, 0, $px, $py, $this->color, $this->font, $text);
					break;

					case "":
						// default format
						imageTTFtext($im->im, $this->fontsize, 0, $px, $py, $this->color, $this->font, $text);
					break;

					default:
						// invalid format
						imageTTFtext($im->im, $this->fontsize, 0, $px, $py, $this->color, $this->font, "FORMAT ERROR");
					break;
				}
			}
		} else {
			// no format
			imageTTFtext($im->im, $this->fontsize, 0, $px, $py, $this->color, $this->font, $text);
		}
		
	}
}

?>
