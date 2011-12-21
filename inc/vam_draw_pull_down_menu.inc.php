<?php
/* -----------------------------------------------------------------------------------------
   $Id: vam_draw_pull_down_menu.inc.php 899 2007-02-07 10:51:57 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (vam_draw_pull_down_menu.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2004 xt:Commerce (vam_draw_pull_down_menu.inc.php,v 1.3 2004/08/25); xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
// Output a form pull down menu
  function vam_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . vam_parse_input_field_data($name, array('"' => '&quot;')) . '"';

    if (vam_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && isset($GLOBALS[$name])) $default = $GLOBALS[$name];

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . vam_parse_input_field_data($values[$i]['id'], array('"' => '&quot;')) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . vam_parse_input_field_data($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }
  
    function vam_draw_pull_down_menuNote($data, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . vam_parse_input_field_data($data['name'], array('"' => '&quot;')) . '"';

    if (vam_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && isset($GLOBALS[$data['name']])) $default = $GLOBALS[$data['name']];

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . vam_parse_input_field_data($values[$i]['id'], array('"' => '&quot;')) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . vam_parse_input_field_data($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>'.$data['text'];

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

 ?>