<?php
/* -----------------------------------------------------------------------------------------
   $Id: product_reviews.php 1238 2007-02-06 19:20:03 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_reviews.php,v 1.47 2003/02/13); www.oscommerce.com 
   (c) 2003	 nextcommerce (product_reviews.php,v 1.12 2003/08/17); www.nextcommerce.org
   (c) 2004	 xt:Commerce (product_reviews.php,v 1.12 2003/08/17); xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
// create template elements
$vamTemplate = new vamTemplate;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
// include needed functions
require_once (DIR_FS_INC.'vam_row_number_format.inc.php');
require_once (DIR_FS_INC.'vam_date_short.inc.php');

// lets retrieve all $HTTP_GET_VARS keys and values..
$get_params = vam_get_all_get_params();
$get_params_back = vam_get_all_get_params(array ('reviews_id')); // for back button
$get_params = substr($get_params, 0, -1); //remove trailing &
if (vam_not_null($get_params_back)) {
	$get_params_back = substr($get_params_back, 0, -1); //remove trailing &
} else {
	$get_params_back = $get_params;
}

$product_info_query = vam_db_query("select pd.products_name from ".TABLE_PRODUCTS_DESCRIPTION." pd left join ".TABLE_PRODUCTS." p on pd.products_id = p.products_id where pd.language_id = '".(int) $_SESSION['languages_id']."' and p.products_status = '1' and pd.products_id = '".(int) $_GET['products_id']."'");
if (!vam_db_num_rows($product_info_query))
	vam_redirect(vam_href_link(FILENAME_REVIEWS));
$product_info = vam_db_fetch_array($product_info_query);

$breadcrumb->add(NAVBAR_TITLE_PRODUCT_REVIEWS, vam_href_link(FILENAME_PRODUCT_REVIEWS, $get_params));

require (DIR_WS_INCLUDES.'header.php');

$vamTemplate->assign('PRODUCTS_NAME', $product_info['products_name']);

$data_reviews = array ();
$reviews_query = vam_db_query("select reviews_rating, reviews_id, customers_name, date_added, last_modified, reviews_read from ".TABLE_REVIEWS." where products_id = '".(int) $_GET['products_id']."' order by reviews_id DESC");
if (vam_db_num_rows($reviews_query)) {
	$row = 0;
	while ($reviews = vam_db_fetch_array($reviews_query)) {
		$row ++;
		$data_reviews[] = array ('ID' => $reviews['reviews_id'], 'AUTHOR' => '<a href="'.vam_href_link(FILENAME_PRODUCT_REVIEWS_INFO, $get_params.'&reviews_id='.$reviews['reviews_id']).'">'.$reviews['customers_name'].'</a>', 'DATE' => vam_date_short($reviews['date_added']), 'RATING' => vam_image('templates/'.CURRENT_TEMPLATE.'/img/stars_'.$reviews['reviews_rating'].'.gif', sprintf(BOX_REVIEWS_TEXT_OF_5_STARS, $reviews['reviews_rating'])), 'TEXT' => vam_break_string(htmlspecialchars($reviews['reviews_text']), 60, '-<br />'));

	}
}
$vamTemplate->assign('module_content', $data_reviews);
$vamTemplate->assign('BUTTON_BACK', '<a href="'.vam_href_link(FILENAME_PRODUCT_INFO, $get_params_back).'">'.vam_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
$vamTemplate->assign('BUTTON_WRITE', '<a href="'.vam_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, $get_params).'">'.vam_image_button('button_write_review.gif', IMAGE_BUTTON_WRITE_REVIEW).'</a>');

$vamTemplate->assign('language', $_SESSION['language']);

// set cache ID
 if (!CacheCheck()) {
	$vamTemplate->caching = 0;
	$main_content = $vamTemplate->fetch(CURRENT_TEMPLATE.'/module/product_reviews.html');
} else {
	$vamTemplate->caching = 1;
	$vamTemplate->cache_lifetime = CACHE_LIFETIME;
	$vamTemplate->cache_modified_check = CACHE_CHECK;
	$cache_id = $_SESSION['language'].$_GET['products_id'];
	$main_content = $vamTemplate->fetch(CURRENT_TEMPLATE.'/module/product_reviews.html', $cache_id);
}

$vamTemplate->assign('language', $_SESSION['language']);
$vamTemplate->assign('main_content', $main_content);
$vamTemplate->caching = 0;
if (!defined(RM)) $vamTemplate->load_filter('output', 'note');
$template = (file_exists('templates/'.CURRENT_TEMPLATE.'/'.FILENAME_PRODUCT_REVIEWS.'.html') ? CURRENT_TEMPLATE.'/'.FILENAME_PRODUCT_REVIEWS.'.html' : CURRENT_TEMPLATE.'/index.html');
$vamTemplate->display($template);
include ('includes/application_bottom.php');
?>