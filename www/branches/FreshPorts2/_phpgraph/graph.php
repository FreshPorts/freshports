<?
// sample graph

include 'phpgraph.php';

$data = array(
	array("openssh", "apache13-php4", "apache13", "mysql322-server", "bash2", "samba", "openssl", "mysql322-client", "vim5", "nmap", "ssh", "apache13-php3", "enlightenment", "netscape47-communicator", "windomaker", "XFree86-4", "ssh2", "bzip2", "apache13-modssl", "lynx"),
	array(44, 37, 32, 32, 31, 28, 28, 27, 27, 26, 26, 24, 24, 24, 24, 24, 23, 22, 21, 21)
);
//	array(22, 18, 16, 16, 15, 14, 14, 13, 13, 13, 13, 12, 12, 12, 12, 12, 11, 11, 10, 10)


$im = new image("png", 500, 400, array(197,194,197));
$g = new graph(&$im, "bar", $data, "Top 20 Most Watched Ports");


$g->im->draw_border(5);


// These all have defaults

# where are the fonts?  include a trailing /   
$FontDirectory = "/usr/local/etc/freshports/ttf/";

// title = text_obj
$g->title->font = "/usr/local/etc/freshports/ttf/Arialb.ttf";
$g->title->fontsize = 18;
$g->title->color = $g->im->color['black'];
#$g->title->format = array("shadow");

// x_label = text_obj
$g->x->label->font = "/usr/local/etc/freshports/ttf/trebucbd.ttf";
$g->x->label->color = $g->im->color['white'];
$g->x->label->format = array("shadow");
$g->x->label->text_padding = 15;	// in percent

// y_label = text_obj
$g->y->label->font = "/usr/local/etc/freshports/ttf/trebucbd.ttf";
$g->y->label->color = $g->im->color['black'];

$g->y->color = $g->im->newcolor(173,0,64);		// this would be the bar color


$g->draw();

$g->save();

?>
