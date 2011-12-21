<?php
/* -----------------------------------------------------------------------------------------
   $Id: logoff.php 1071 2007-02-06 19:20:03 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(logoff.php,v 1.12 2003/02/13); www.oscommerce.com 
   (c) 2003	 nextcommerce (logoff.php,v 1.16 2003/08/17); www.nextcommerce.org
   (c) 2004	 xt:Commerce (logoff.php,v 1.16 2003/08/17); xt-commerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
// create template elements
$vamTemplate = new vamTemplate;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$breadcrumb->add(NAVBAR_TITLE_LOGOFF);

//delete Guests from Database   

if (($_SESSION['account_type'] == 1) && (DELETE_GUEST_ACCOUNT == 'true')) {
	vam_db_query("delete from ".TABLE_CUSTOMERS." where customers_id = '".$_SESSION['customer_id']."'");
	vam_db_query("delete from ".TABLE_ADDRESS_BOOK." where customers_id = '".$_SESSION['customer_id']."'");
	vam_db_query("delete from ".TABLE_CUSTOMERS_INFO." where customers_info_id = '".$_SESSION['customer_id']."'");
}

vam_session_destroy();

unset ($_SESSION['customer_id']);
unset ($_SESSION['customer_default_address_id']);
unset ($_SESSION['customer_first_name']);
unset ($_SESSION['customer_last_name']);
unset ($_SESSION['customer_email_address']);
unset ($_SESSION['customer_country_id']);
unset ($_SESSION['customer_zone_id']);
unset ($_SESSION['comments']);
unset ($_SESSION['user_info']);
unset ($_SESSION['customers_status']);
unset ($_SESSION['selected_box']);
unset ($_SESSION['navigation']);
unset ($_SESSION['shipping']);
unset ($_SESSION['payment']);
unset ($_SESSION['ccard']);
// GV Code Start
unset ($_SESSION['gv_id']);
unset ($_SESSION['cc_id']);
// GV Code End
$_SESSION['cart']->reset();
// write customers status guest in session again
require (DIR_WS_INCLUDES.'write_customers_status.php');

require (DIR_WS_INCLUDES.'header.php');

$vamTemplate->assign('BUTTON_CONTINUE', '<a href="'.vam_href_link(FILENAME_DEFAULT).'">'.vam_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');
$vamTemplate->assign('language', $_SESSION['language']);

$vamTemplate->caching = 0;
$main_content = $vamTemplate->fetch(CURRENT_TEMPLATE.'/module/logoff.html');

$vamTemplate->assign('language', $_SESSION['language']);
$vamTemplate->assign('main_content', $main_content);
$vamTemplate->caching = 0;
if (!defined(RM)) $vamTemplate->load_filter('output', 'note');
$template = (file_exists('templates/'.CURRENT_TEMPLATE.'/'.FILENAME_LOGOFF.'.html') ? CURRENT_TEMPLATE.'/'.FILENAME_LOGOFF.'.html' : CURRENT_TEMPLATE.'/index.html');
$vamTemplate->display($template);
include ('includes/application_bottom.php');
?>