<?php
/* -----------------------------------------------------------------------------------------
   $Id: vam_db_connect.inc.php 1248 2007-02-07 10:51:57 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.19 2003/03/22); www.oscommerce.com 
   (c) 2003	 nextcommerce (vam_db_connect.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2004 xt:Commerce (vam_db_connect.inc.php,v 1.3 2004/08/25); xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
 //  include(DIR_WS_CLASSES.'/adodb/adodb.inc.php');
  function vam_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link', $use_pconnect = USE_PCONNECT, $new_link = false) {
    global $$link;

    if ($use_pconnect == 'true') {
     $$link = mysql_pconnect($server, $username, $password);
    } else {
$$link = @mysql_connect($server, $username, $password, $new_link);
    
   }

if ($$link){
   @mysql_select_db($database);
   @mysql_query("SET SQL_MODE= ''");
   @mysql_query("SET NAMES 'utf8' COLLATE 'utf8_general_ci'");
}

//Start VaM db-error processing
    if (!$$link) {
      vam_db_error("connect", mysql_errno(), mysql_error());
    }
//End VaM db-error processing

    return $$link;
  }
 ?>