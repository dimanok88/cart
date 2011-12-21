<?php
/* -----------------------------------------------------------------------------------------
   $Id: advanced_search_result.php 1141 2007-02-06 19:20:03 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(advanced_search_result.php,v 1.68 2003/05/14); www.oscommerce.com 
   (c) 2003	 nextcommerce (advanced_search_result.php,v 1.17 2003/08/21); www.nextcommerce.org
   (c) 2004	 xt:Commerce (advanced_search_result.php,v 1.17 2003/08/21); xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
// create template elements
$vamTemplate = new vamTemplate;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
// include needed functions
require_once (DIR_FS_INC.'vam_parse_search_string.inc.php');
require_once (DIR_FS_INC.'vam_get_subcategories.inc.php');
require_once (DIR_FS_INC.'vam_get_currencies_values.inc.php');

/*
 * check search entry
 */

$error = 0; // reset error flag to false
$errorno = 0;
$keyerror = 0;

if (isset ($_GET['keywords']) && empty ($_GET['keywords'])) {
	$keyerror = 1;
}

if ((isset ($_GET['keywords']) && empty ($_GET['keywords'])) && (isset ($_GET['pfrom']) && empty ($_GET['pfrom'])) && (isset ($_GET['pto']) && empty ($_GET['pto']))) {
	$errorno += 1;
	$error = 1;
}
elseif (isset ($_GET['keywords']) && empty ($_GET['keywords']) && !(isset ($_GET['pfrom'])) && !(isset ($_GET['pto']))) {
	$errorno += 1;
	$error = 1;
}

if (strlen($_GET['keywords']) < 3 && strlen($_GET['keywords']) > 0 && $error == 0) {
	$errorno += 1;
	$error = 1;
	$keyerror = 1;
}

if (strlen($_GET['pfrom']) > 0) {
	$pfrom_to_check = vam_db_input($_GET['pfrom']);
	if (!settype($pfrom_to_check, "double")) {
		$errorno += 10000;
		$error = 1;
	}
}

if (strlen($_GET['pto']) > 0) {
	$pto_to_check = $_GET['pto'];
	if (!settype($pto_to_check, "double")) {
		$errorno += 100000;
		$error = 1;
	}
}

if (strlen($_GET['pfrom']) > 0 && !(($errorno & 10000) == 10000) && strlen($_GET['pto']) > 0 && !(($errorno & 100000) == 100000)) {
	if ($pfrom_to_check > $pto_to_check) {
		$errorno += 1000000;
		$error = 1;
	}
}

if (strlen($_GET['keywords']) > 0) {
	if (!vam_parse_search_string(stripslashes($_GET['keywords']), $search_keywords)) {
		$errorno += 10000000;
		$error = 1;
		$keyerror = 1;
	}
}

if ($error == 1 && $keyerror != 1) {

	vam_redirect(vam_href_link(FILENAME_ADVANCED_SEARCH, 'errorno='.$errorno.'&'.vam_get_all_get_params(array ('x', 'y'))));

} else {

	/*
	 *    search process starts here
	 */

	$breadcrumb->add(NAVBAR_TITLE1_ADVANCED_SEARCH, vam_href_link(FILENAME_ADVANCED_SEARCH));
	$breadcrumb->add(NAVBAR_TITLE2_ADVANCED_SEARCH, vam_href_link(FILENAME_ADVANCED_SEARCH_RESULT, 'keywords='.htmlspecialchars(vam_db_input($_GET['keywords'])).'&search_in_description='.vam_db_input($_GET['search_in_description']).'&categories_id='.(int)$_GET['categories_id'].'&inc_subcat='.vam_db_input($_GET['inc_subcat']).'&manufacturers_id='.(int)$_GET['manufacturers_id'].'&pfrom='.vam_db_input($_GET['pfrom']).'&pto='.vam_db_input($_GET['pto']).'&dfrom='.vam_db_input($_GET['dfrom']).'&dto='.vam_db_input($_GET['dto'])));

	require (DIR_WS_INCLUDES.'header.php');

	// define additional filters //

	//fsk18 lock
	if ($_SESSION['customers_status']['customers_fsk18_display'] == '0') {
		$fsk_lock = " AND p.products_fsk18 != '1' ";
	} else {
		unset ($fsk_lock);
	}

	//group check
	if (GROUP_CHECK == 'true') {
		$group_check = " AND p.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
	} else {
		unset ($group_check);
	}

	//manufacturers if set
	if (isset ($_GET['manufacturers_id']) && vam_not_null($_GET['manufacturers_id'])) {
		$manu_check = " AND p.manufacturers_id = '".(int)$_GET['manufacturers_id']."' "; 
	} else { $manu_check=''; }

	//include subcategories if needed
   $subcat_where='';
	if (isset ($_GET['categories_id']) && vam_not_null($_GET['categories_id'])) {
		if ($_GET['inc_subcat'] == '1') {
			$subcategories_array = array ();
			vam_get_subcategories($subcategories_array, (int)$_GET['categories_id']);
			$subcat_join = " LEFT OUTER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." AS p2c ON (p.products_id = p2c.products_id) ";
			$subcat_where = " AND p2c.categories_id IN ('".(int) $_GET['categories_id']."' ";
			foreach ($subcategories_array AS $scat) {
				$subcat_where .= ", '".$scat."'";
			}
			$subcat_where .= ") ";
		} else {
			$subcat_join = " LEFT OUTER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." AS p2c ON (p.products_id = p2c.products_id) ";
			$subcat_where = " AND p2c.categories_id = '".(int) $_GET['categories_id']."' ";
		}
	}

	if ($_GET['pfrom'] || $_GET['pto']) {
		$rate = vam_get_currencies_values($_SESSION['currency']);
		$rate = $rate['value'];
		if ($rate && $_GET['pfrom'] != '') {
			$pfrom = $_GET['pfrom'] / $rate;
		}
		if ($rate && $_GET['pto'] != '') {
			$pto = $_GET['pto'] / $rate;
		}
	}

	//price filters
	if (($pfrom != '') && (is_numeric($pfrom))) {
		$pfrom_check = " AND (IF(s.status = '1' AND p.products_id = s.products_id, s.specials_new_products_price, p.products_price) >= ".$pfrom.") ";
	} else {
		$pfrom_check='';
	}

	if (($pto != '') && (is_numeric($pto))) {
		$pto_check = " AND (IF(s.status = '1' AND p.products_id = s.products_id, s.specials_new_products_price, p.products_price) <= ".$pto." ) ";
	} else {
		$pto_check='';
	}

	//build query
	$select_str = "SELECT distinct
	                  p.products_id,
	                  p.products_price,
	                  p.products_model,
	                  p.products_quantity,
	                  p.products_shippingtime,
	                  p.products_fsk18,
	                  p.products_image,
	                  p.products_weight,
	                  p.products_tax_class_id,
	                  pd.products_name,
	                  pd.products_short_description,
	                  pd.products_description ";

	$from_str  = "FROM ".TABLE_PRODUCTS."  AS p LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." AS pd ON (p.products_id = pd.products_id) LEFT JOIN products_to_categories as p2c2 ON (p2c2.products_id=p.products_id) LEFT JOIN categories as c ON (c.categories_id=p2c2.categories_id)";
	$from_str .= $subcat_join;
	if (SEARCH_IN_ATTR == 'true') { $from_str .= " LEFT OUTER JOIN ".TABLE_PRODUCTS_ATTRIBUTES." AS pa ON (p.products_id = pa.products_id) LEFT OUTER JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." AS pov ON (pa.options_values_id = pov.products_options_values_id) "; }
	$from_str .= "LEFT OUTER JOIN ".TABLE_SPECIALS." AS s ON (p.products_id = s.products_id) AND s.status = '1'";
   $from_str .= " LEFT OUTER JOIN ".TABLE_PRODUCTS_TO_PRODUCTS_EXTRA_FIELDS." AS pe ON (p.products_id = pe.products_id)";

	if ((DISPLAY_PRICE_WITH_TAX == 'true') && ((isset ($_GET['pfrom']) && vam_not_null($_GET['pfrom'])) || (isset ($_GET['pto']) && vam_not_null($_GET['pto'])))) {
		if (!isset ($_SESSION['customer_country_id'])) {
			$_SESSION['customer_country_id'] = STORE_COUNTRY;
			$_SESSION['customer_zone_id'] = STORE_ZONE;
		}
		$from_str .= " LEFT OUTER JOIN ".TABLE_TAX_RATES." tr ON (p.products_tax_class_id = tr.tax_class_id) LEFT OUTER JOIN ".TABLE_ZONES_TO_GEO_ZONES." gz ON (tr.tax_zone_id = gz.geo_zone_id) ";
		$tax_where = " AND (gz.zone_country_id IS NULL OR gz.zone_country_id = '0' OR gz.zone_country_id = '".(int) $_SESSION['customer_country_id']."') AND (gz.zone_id is null OR gz.zone_id = '0' OR gz.zone_id = '".(int) $_SESSION['customer_zone_id']."')";
	} else {
		$tax_where='';
	}

	//where-string
	$where_str = " WHERE p.products_status = '1' "." AND c.categories_status=1 AND pd.language_id = '".(int) $_SESSION['languages_id']."'".$subcat_where.$fsk_lock.$manu_check.$group_check.$tax_where.$pfrom_check.$pto_check;

	//go for keywords... this is the main search process
	if (isset ($_GET['keywords']) && vam_not_null($_GET['keywords'])) {
		if (vam_parse_search_string(stripslashes($_GET['keywords']), $search_keywords)) {
			$where_str .= " AND ( ";
			for ($i = 0, $n = sizeof($search_keywords); $i < $n; $i ++) {
				switch ($search_keywords[$i]) {
					case '(' :
					case ')' :
					case 'and' :
					case 'or' :
						$where_str .= " ".$search_keywords[$i]." ";
						break;
					default :
						$where_str .= " ( ";
						$where_str .= "pd.products_keywords LIKE ('%".addslashes($search_keywords[$i])."%') ";
						if (SEARCH_IN_DESC == 'true') {
						   $where_str .= "OR pd.products_description LIKE ('%".addslashes($search_keywords[$i])."%') ";
						   $where_str .= "OR pd.products_short_description LIKE ('%".addslashes($search_keywords[$i])."%') ";
						}						
						$where_str .= "OR pd.products_name LIKE ('%".addslashes($search_keywords[$i])."%') ";
						$where_str .= "OR p.products_model LIKE ('%".addslashes($search_keywords[$i])."%') ";
                  $where_str .= " OR pe.products_extra_fields_value LIKE ('%".addslashes($search_keywords[$i])."%') ";
						if (SEARCH_IN_ATTR == 'true') {
						   $where_str .= "OR (pov.products_options_values_name LIKE ('%".addslashes($search_keywords[$i])."%') ";
						   $where_str .= "AND pov.language_id = '".(int) $_SESSION['languages_id']."')";
						}
						$where_str .= " ) ";
						break;
				}
			}
			$where_str .= " ) GROUP BY p.products_id ORDER BY p.products_id ";
		}
	}

	//glue together
	$listing_sql = $select_str.$from_str.$where_str;
	require (DIR_WS_MODULES.FILENAME_PRODUCT_LISTING);
}
$vamTemplate->assign('language', $_SESSION['language']);
$vamTemplate->caching = 0;
if (!defined(RM)) $vamTemplate->load_filter('output', 'note');
$template = (file_exists('templates/'.CURRENT_TEMPLATE.'/'.FILENAME_ADVANCED_SEARCH_RESULT.'.html') ? CURRENT_TEMPLATE.'/'.FILENAME_ADVANCED_SEARCH_RESULT.'.html' : CURRENT_TEMPLATE.'/index.html');
$vamTemplate->display($template);
include ('includes/application_bottom.php');
?>