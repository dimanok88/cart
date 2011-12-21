<?php
/* -----------------------------------------------------------------------------------------
   $Id: vam_draw_box_contents.inc.php 899 2007-02-07 10:51:57 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(output.php,v 1.3 2002/06/01); www.oscommerce.com 
   (c) 2003	 nextcommerce (vam_draw_box_contents.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2004 xt:Commerce (vam_draw_box_contents.inc.php,v 1.3 2004/08/25); xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
function vam_draw_box_contents($box_contents, $box_shadow_color = BOX_SHADOW, $box_background_color = BOX_BGCOLOR_CONTENTS) {
    $contents = '<table border="0" width="100%" cellspacing="0" cellpadding="1" bgcolor="' . $box_shadow_color . '">' . CR .
                 '  <tr>' . CR .
                 '    <td><table border="0" width="100%" cellspacing="0" cellpadding="4" bgcolor="' . $box_background_color . '">' . CR .
                 '      <tr>' . CR .
                 '        <td><img src="images/pixel_trans.gif" alt="" width="100%" height="5" /></td>' . CR .
                 '      </tr>';

    if (is_array($box_contents)) {
      for ($i=0; $i<sizeof($box_contents); $i++) {
        $contents .= vam_draw_box_content_bullet($box_contents[$i]['title'], $box_contents[$i]['link']);
      }
    } else {
      $contents .= '      <tr>' . CR .
                   '        <td class="infoboxText">' . $box_contents . '</td>' . CR .
                   '      </tr>' . CR;
    }

    $contents .= '      <tr>' . CR .
                 '        <td><img src="images/pixel_trans.gif" alt="" width="100%" height="5" /></td>' . CR .
                 '      </tr>' . CR .
                 '    </table></td>' . CR .
                 '  </tr>' . CR .
                 '</table>' . CR;

    return $contents;
  }
 ?>