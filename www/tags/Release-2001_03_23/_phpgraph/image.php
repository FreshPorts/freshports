<?

class image {
	var $im;		// image handle
	var $type;		// jpeg, png
	var $width;
	var $height;
	var $color;		// array to store colors

	function image ($type, $width, $height, $bgcol="", $transcol="") { 
		if ($bgcol == "")
			$bgcol=array(255,255,255);
		$this->type = $type;
		$this->width = $width;
		$this->height = $height;
		$this->im = imagecreate($this->width, $this->height);

		// Just some default colors
		$this->color['white'] = ImageColorAllocate($this->im, 255,255,255);
		$this->color['black'] = ImageColorAllocate($this->im, 0,0,0);
		$this->color['darkgrey'] = ImageColorAllocate($this->im, 131,129,131);

		// Assign Backgound and Fill
		$this->color['bgcolor'] = ImageColorAllocate($this->im, $bgcol[0], $bgcol[1], $bgcol[2]);
		imagefill($this->im, 0, 0, $this->color['bgcolor']);

		if ($transcol != "") {
			$this->color['trans'] = ImageColorAllocate($this->im, $transcol[0], $transcol[1], $transcol[2]);
			ImageColorTransparent($this->im, $this->color['trans']);
		}
	}

	function newcolor($r, $g, $b, $name="") {
		if ($name=="")
			return(ImageColorAllocate($this->im, $r,$g,$b));
		else
			return($this->color[$name] = ImageColorAllocate($this->im, $r,$g,$b));
	}

	function draw_border($borderwidth) {
		$r_edge = $this->width - 1;		// right edge
		$b_edge = $this->height - 1;	// bottom edge

		$border_color = $this->color['black'];
		$topleft_color = $this->color['white'];
		$botright_color = $this->color['darkgrey'];

		$border = array(
				0, 0,
				$r_edge, 0,
				$r_edge, $b_edge,
				0, $b_edge
		);
		$topleft = array(
				0, 0,
				$r_edge, 0,
				$r_edge - $borderwidth, $borderwidth,
				$borderwidth, $borderwidth,
				$borderwidth, $b_edge-$borderwidth,
				0, $b_edge
		);
		$botright = array(
				$borderwidth, $b_edge-$borderwidth,
				$r_edge-$borderwidth, $b_edge-$borderwidth,
				$r_edge-$borderwidth, $borderwidth,
				$r_edge, 0,
				$r_edge, $b_edge,
				0, $b_edge
		);
		imagefilledpolygon($this->im, $topleft, 6, $topleft_color);
		imagefilledpolygon($this->im, $botright, 6, $botright_color);
		imagepolygon($this->im, $border, 4 , $border_color);
	}

	function draw() {
		header("Content-type: image/" . $this->type);
		$image_func = "image" . $this->type;
		$image_func($this->im);
//		imagedestroy($this->im);
	}

	function save($filename) {
		ImagePng($this->im, $filename);
	}

	function destroy() {
		imagedestroy($this->im);
	}
}

?>
