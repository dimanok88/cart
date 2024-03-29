<?php
/* -----------------------------------------------------------------------------------------
   $Id: upcoming_products.php 1243 2007-02-06 20:41:56 VaM $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(upcoming_products.php,v 1.23 2003/02/12); www.oscommerce.com 
   (c) 2003	 nextcommerce (upcoming_products.php,v 1.7 2003/08/22); www.nextcommerce.org
   (c) 2004	 xt:Commerce (upcoming_products.php,v 1.7 2003/08/22); xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

$module = new vamTemplate;
$module->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
// include needed functions
require_once (DIR_FS_INC.'vam_date_short.inc.php');
$module_content = array ();

//fsk18 lock
$fsk_lock = '';
if ($_SESSION['customers_status']['customers_fsk18_display'] == '0')
	$fsk_lock = ' and p.products_fsk18!=1';

if (GROUP_CHECK == 'true')
	$group_check = "and p.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";

$expected_query = vamDBquery("select p.products_id,
                                  pd.products_name,
                                  products_date_available as date_expected from ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd
                                  where to_days(products_date_available) >= to_days(now())
                                  and p.products_id = pd.products_id
                                  ".$group_check."
                                  ".$fsk_lock."
                                  and pd.language_id = '".(int) $_SESSION['languages_id']."'
                                  order by ".EXPECTED_PRODUCTS_FIELD." ".EXPECTED_PRODUCTS_SORT."
                                  limit ".MAX_DISPLAY_UPCOMING_PRODUCTS);
if (vam_db_num_rows($expected_query,true) > 0) {

	$row = 0;
	while ($expected = vam_db_fetch_array($expected_query,true)) {
		$row ++;
		$module_content[] = array ('PRODUCTS_LINK' => vam_href_link(FILENAME_PRODUCT_INFO, vam_product_link($expected['products_id'], $expected['products_name'])), 'PRODUCTS_NAME' => $expected['products_name'], 'PRODUCTS_DATE' => vam_date_short($expected['date_expected']));

	}

	$module->assign('language', $_SESSION['language']);
	$module->assign('module_content', $module_content);
	// set cache ID

	$module->caching = 0;
	$module = $module->fetch(CURRENT_TEMPLATE.'/module/upcoming_products.html');

	$default->assign('MODULE_upcoming_products', $module);
}
?>