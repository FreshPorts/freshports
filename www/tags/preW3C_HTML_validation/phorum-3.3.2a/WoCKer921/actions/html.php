<?php check_security(); ?>
<?php
  $PHORUM['default_body_color']=$new_default_body_color;
  $PHORUM['default_body_link_color']=$new_default_body_link_color;
  $PHORUM['default_body_vlink_color']=$new_default_body_vlink_color;
  $PHORUM['default_body_alink_color']=$new_default_body_alink_color;
  $PHORUM['default_table_width']=$new_default_table_width;
  $PHORUM['default_table_header_color']=$new_default_table_header_color;
  $PHORUM['default_table_header_font_color']=$new_default_table_header_font_color;
  $PHORUM['default_table_body_color_1']=$new_default_table_body_color_1;
  $PHORUM['default_table_body_font_color_1']=$new_default_table_body_font_color_1;
  $PHORUM['default_table_body_color_2']=$new_default_table_body_color_2;
  $PHORUM['default_table_body_font_color_2']=$new_default_table_body_font_color_2;
  $PHORUM['default_nav_color']=$new_default_nav_color;
  $PHORUM['default_nav_font_color']=$new_default_nav_font_color;
  writefile('all');
  QueMessage("The HTML properties have been updated.");
?>