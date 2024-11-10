<?php

class ItemsPerPage {

  var $Choices = array();

  function __construct() {
    $this->Choices = array(10  => 10, 
                           20  => 20, 
                           30  => 30, 
                           50  => 50, 
                           100 => 100);

  }
  
  function DDLB_Choices($Name = 'page_size', $selected = '', $ChoiceSuffix = '') {
  	# return the HTML which forms a dropdown list box.
	# optionally, select the item identified by $selected.

	$Debug = 0;

	$HTML = '<select name="' . htmlentities($Name);

	$HTML .= '" title="select a page size"';

	$HTML .= ">\n";

	if ($Debug) {
		echo "$NumRows rows found!<br>";
		echo "selected = '$selected'<br>";
	}

	foreach ($this->Choices as $choice => $value) {
		$HTML .= '<option value="' . htmlspecialchars($value) . '"';
		if ($value == $selected) {
			$HTML .= ' selected';
		}
		$HTML .= '>' . htmlspecialchars($choice);
		if ($ChoiceSuffix) {
		  $HTML .=  ' ' . htmlspecialchars($ChoiceSuffix);
        }
		$HTML .= "</option>\n";
	}
	
	$HTML .= '</select>';
	
	return $HTML;
  }

}
