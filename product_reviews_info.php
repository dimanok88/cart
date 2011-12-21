<?php
/* -----------------------------------------------------------------------------------------
   $Id: product_reviews_info.php 1238 2007-02-06 19:20:03 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_reviews_info.php,v 1.47 2003/02/13); www.oscommerce.com
   (c) 2003	 nextcommerce (product_reviews_info.php,v 1.12 2003/08/17); www.nextcommerce.org 
   (c) 2004	 xt:Commerce (product_reviews_info.php,v 1.12 2003/08/17); xt-commerce.com 

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');
// create template elements
$vamTemplate = new vamTemplate;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
// include needed functions
require_once (DIR_FS_INC.'vam_break_string.inc.php');
require_once (DIR_FS_INC.'vam_date_long.inc.php');


// lets retrieve all $HTTP_GET_VARS keys and values..
$get_params = vam_get_all_get_params(array ('reviews_id'));
$get_params = substr($get_params, 0, -1); //remove trailing &

$reviews_query = "select rd.reviews_text, r.reviews_rating, r.reviews_id, r.products_id, r.customers_name, r.date_added, r.last_modified, r.reviews_read, p.products_id, pd.products_name, p.products_image from ".TABLE_REVIEWS." r left join ".TABLE_PRODUCTS." p on (r.products_id = p.products_id) left join ".TABLE_PRODUCTS_DESCRIPTION." pd on (p.products_id = pd.products_id and pd.language_id = '".(int) $_SESSION['languages_id']."'), ".TABLE_REVIEWS_DESCRIPTION." rd where r.reviews_id = '".(int) $_GET['reviews_id']."' and r.reviews_id = rd.reviews_id and p.products_status = '1'";
$reviews_query = vam_db_query($reviews_query);

if (!vam_db_num_rows($reviews_query))
	vam_redirect(vam_href_link(FILENAME_REVIEWS));
$reviews = vam_db_fetch_array($reviews_query);

$breadcrumb->add(NAVBAR_TITLE_PRODUCT_REVIEWS, vam_href_link(FILENAME_PRODUCT_REVIEWS, $get_params));

vam_db_query("update ".TABLE_REVIEWS." set reviews_read = reviews_read+1 where reviews_id = '".$reviews['reviews_id']."'");

$reviews_text = vam_break_string(htmlspecialchars($reviews['reviews_text']), 60, '-<br />');

require (DIR_WS_INCLUDES.'header.php');

$vamTemplate->assign('PRODUCTS_NAME', $reviews['products_name']);
$vamTemplate->assign('AUTHOR', $reviews['customers_name']);
$vamTemplate->assign('DATE', vam_date_long($reviews['date_added']));
$vamTemplate->assign('REVIEWS_TEXT', nl2br($reviews_text));
$vamTemplate->assign('RATING', vam_image('templates/'.CURRENT_TEMPLATE.'/img/stars_'.$reviews['reviews_rating'].'.gif', sprintf(TEXT_OF_5_STARS, $reviews['reviews_rating'])));
$vamTemplate->assign('PRODUCTS_LINK', vam_href_link(FILENAME_PRODUCT_INFO, vam_product_link($reviews['products_id'], $reviews['products_name'])));
$vamTemplate->assign('BUTTON_BACK', '<a href="'.vam_href_link(FILENAME_PRODUCT_REVIEWS, $get_params).'">'.vam_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
$vamTemplate->assign('BUTTON_BUY_NOW', '<a href="'.vam_href_link(FILENAME_DEFAULT, 'action=buy_now&BUYproducts_id='.$reviews['products_id']).'">'.vam_image_button('button_in_cart.gif', IMAGE_BUTTON_IN_CART) . '</a>');

$products_image = DIR_WS_THUMBNAIL_IMAGES.$reviews['products_image'];
if (!is_file($products_image)) $products_image = DIR_WS_THUMBNAIL_IMAGES.'../noimage.gif';
$image = vam_image($products_image, $reviews['products_name'], '', '', 'hspace="5" vspace="5"');
$vamTemplate->assign('IMAGE', $image);

$vamTemplate->assign('language', $_SESSION['language']);

// set cache ID
 if (!CacheCheck()) {
	$vamTemplate->caching = 0;
	$main_content = $vamTemplate->fetch(CURRENT_TEMPLATE.'/module/product_reviews_info.html');
} else {
	$vamTemplate->caching = 1;
	$vamTemplate->cache_lifetime = CACHE_LIFETIME;
	$vamTemplate->cache_modified_check = CACHE_CHECK;
	$cache_id = $_SESSION['language'].$reviews['reviews_id'];
	$main_content = $vamTemplate->fetch(CURRENT_TEMPLATE.'/module/product_reviews_info.html', $cache_id);
}

$vamTemplate->assign('language', $_SESSION['language']);
$vamTemplate->assign('main_content', $main_content);
$vamTemplate->caching = 0;
if (!defined(RM)) $vamTemplate->load_filter('output', 'note');
$template = (file_exists('templates/'.CURRENT_TEMPLATE.'/'.FILENAME_PRODUCT_REVIEWS_INFO.'.html') ? CURRENT_TEMPLATE.'/'.FILENAME_PRODUCT_REVIEWS_INFO.'.html' : CURRENT_TEMPLATE.'/index.html');
$vamTemplate->display($template);
include ('includes/application_bottom.php');
?>