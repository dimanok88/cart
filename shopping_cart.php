<?php
/* -----------------------------------------------------------------------------------------
   $Id: shopping_cart.php 1299 2007-02-06 19:20:03 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(shopping_cart.php,v 1.71 2003/02/14); www.oscommerce.com 
   (c) 2003	 nextcommerce (shopping_cart.php,v 1.24 2003/08/17); www.nextcommerce.org
   (c) 2004	 xt:Commerce (shopping_cart.php,v 1.24 2003/08/17); xt-commerce.com

   Released under the GNU General Public License 
   --------------------------------------------------------------
   Third Party contributions:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
$cart_empty = false;
require ("includes/application_top.php");

// create template elements
$vamTemplate = new vamTemplate;
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');
// include needed functions
require_once (DIR_FS_INC.'vam_array_to_string.inc.php');
require_once (DIR_FS_INC.'vam_image_submit.inc.php');
require_once (DIR_FS_INC.'vam_recalculate_price.inc.php');
require_once (DIR_FS_INC.'get_cross_sell_name.inc.php');

$breadcrumb->add(NAVBAR_TITLE_SHOPPING_CART, vam_href_link(FILENAME_SHOPPING_CART));

require (DIR_WS_INCLUDES.'header.php');
include (DIR_WS_MODULES.'gift_cart.php');

if ($_SESSION['cart']->count_contents() > 0) {

  $vamTemplate->assign('info_message', $_SESSION['error_cart_msg']);

	$vamTemplate->assign('FORM_ACTION', vam_draw_form('cart_quantity', vam_href_link(FILENAME_SHOPPING_CART, 'action=update_product')));
	$vamTemplate->assign('FORM_END', '</form>');
	$hidden_options = '';
	$_SESSION['any_out_of_stock'] = 0;

	$products = $_SESSION['cart']->get_products();
	for ($i = 0, $n = sizeof($products); $i < $n; $i ++) {
		// Push all attributes information in an array
		if (isset ($products[$i]['attributes'])) {
			while (list ($option, $value) = each($products[$i]['attributes'])) {
				//$hidden_options .= vam_draw_hidden_field('id['.$products[$i]['id'].']['.$option.']', $value);
				$attributes = vam_db_query("select popt.products_options_name, popt.products_options_type, poval.products_options_values_name, pa.options_values_price, pa.price_prefix,pa.attributes_stock,pa.products_attributes_id,pa.attributes_model
				                                      from ".TABLE_PRODUCTS_OPTIONS." popt, ".TABLE_PRODUCTS_OPTIONS_VALUES." poval, ".TABLE_PRODUCTS_ATTRIBUTES." pa
				                                      where pa.products_id = '".$products[$i]['id']."'
				                                       and pa.options_id = '".$option."'
				                                       and pa.options_id = popt.products_options_id
				                                       and pa.options_values_id = '".$value."'
				                                       and pa.options_values_id = poval.products_options_values_id
				                                       and popt.language_id = '".(int) $_SESSION['languages_id']."'
				                                       and poval.language_id = '".(int) $_SESSION['languages_id']."'");
				$attributes_values = vam_db_fetch_array($attributes);

				if($attributes_values['products_options_type']=='2' || $attributes_values['products_options_type']=='3'){
					$hidden_options .= vam_draw_hidden_field('id[' . $products[$i]['id'] . '][txt_' . $option . '_'.$value.']',  $products[$i]['attributes_values'][$option]);
				    $attr_value = $products[$i]['attributes_values'][$option];
				}else{
					$hidden_options .= vam_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
				    $attr_value = $attributes_values['products_options_values_name'];
				}

				$products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
				$products[$i][$option]['options_values_id'] = $value;
				$products[$i][$option]['products_options_values_name'] = $attr_value;
				$products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];
				$products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];
				$products[$i][$option]['weight_prefix'] = $attributes_values['weight_prefix'];
				$products[$i][$option]['options_values_weight'] = $attributes_values['options_values_weight'];
				$products[$i][$option]['attributes_stock'] = $attributes_values['attributes_stock'];
				$products[$i][$option]['products_attributes_id'] = $attributes_values['products_attributes_id'];
				$products[$i][$option]['products_attributes_model'] = $attributes_values['products_attributes_model'];
			}
		}
	}

	$vamTemplate->assign('HIDDEN_OPTIONS', $hidden_options);
	require (DIR_WS_MODULES.'order_details_cart.php');
	include (DIR_WS_MODULES.'cross_selling_cart.php');

$_SESSION['allow_checkout'] = 'true';
	if (STOCK_CHECK == 'true') {
		if ($_SESSION['any_out_of_stock'] == 1) {
			if (STOCK_ALLOW_CHECKOUT == 'true') {
				// write permission in session
				$_SESSION['allow_checkout'] = 'true';

				$vamTemplate->assign('info_message', OUT_OF_STOCK_CAN_CHECKOUT);

			} else {
				$_SESSION['allow_checkout'] = 'false';
				$vamTemplate->assign('info_message', OUT_OF_STOCK_CANT_CHECKOUT);

			}
		} else {
			$_SESSION['allow_checkout'] = 'true';
		}
	}
// minimum/maximum order value
$checkout = true;
if ($_SESSION['cart']->show_total() > 0 ) {
 if ($_SESSION['cart']->show_total() < $_SESSION['customers_status']['customers_status_min_order'] ) {
  $_SESSION['allow_checkout'] = 'false';
  $more_to_buy = $_SESSION['customers_status']['customers_status_min_order'] - $_SESSION['cart']->show_total();
  $order_amount=$vamPrice->Format($more_to_buy, true);
  $min_order=$vamPrice->Format($_SESSION['customers_status']['customers_status_min_order'], true);
  $vamTemplate->assign('info_message_1', MINIMUM_ORDER_VALUE_NOT_REACHED_1);
  $vamTemplate->assign('info_message_2', MINIMUM_ORDER_VALUE_NOT_REACHED_2);
  $vamTemplate->assign('order_amount', $order_amount);
  $vamTemplate->assign('min_order', $min_order);
 }
 if  ($_SESSION['customers_status']['customers_status_max_order'] != 0) {
  if ($_SESSION['cart']->show_total() > $_SESSION['customers_status']['customers_status_max_order'] ) {
  $_SESSION['allow_checkout'] = 'false';
  $less_to_buy = $_SESSION['cart']->show_total() - $_SESSION['customers_status']['customers_status_max_order'];
  $max_order=$vamPrice->Format($_SESSION['customers_status']['customers_status_max_order'], true);
  $order_amount=$vamPrice->Format($less_to_buy, true);
  $vamTemplate->assign('info_message_1', MAXIMUM_ORDER_VALUE_REACHED_1);
  $vamTemplate->assign('info_message_2', MAXIMUM_ORDER_VALUE_REACHED_2);
  $vamTemplate->assign('order_amount', $order_amount);
  $vamTemplate->assign('min_order', $max_order);
  }
 }
}
	if ($_GET['info_message'])
		$vamTemplate->assign('info_message', str_replace('+', ' ', htmlspecialchars($_GET['info_message'])));
	$vamTemplate->assign('BUTTON_RELOAD', vam_image_submit('button_update_cart.gif', IMAGE_BUTTON_UPDATE_CART));
	$vamTemplate->assign('BUTTON_CHECKOUT', '<a href="'.vam_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL').'">'.vam_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT).'</a>');		
} else {

	// empty cart
	$cart_empty = true;
	if ($_GET['info_message'])
		$vamTemplate->assign('info_message', str_replace('+', ' ', htmlspecialchars($_GET['info_message'])));
	$vamTemplate->assign('cart_empty', $cart_empty);
	$vamTemplate->assign('BUTTON_CONTINUE', '<a href="'.vam_href_link(FILENAME_DEFAULT).'">'.vam_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');

}
$vamTemplate->assign('language', $_SESSION['language']);
$vamTemplate->caching = 0;
$main_content = $vamTemplate->fetch(CURRENT_TEMPLATE.'/module/shopping_cart.html');
$vamTemplate->assign('main_content', $main_content);

$vamTemplate->assign('language', $_SESSION['language']);
$vamTemplate->caching = 0;
if (!defined(RM)) $vamTemplate->load_filter('output', 'note');
$template = (file_exists('templates/'.CURRENT_TEMPLATE.'/'.FILENAME_SHOPPING_CART.'.html') ? CURRENT_TEMPLATE.'/'.FILENAME_SHOPPING_CART.'.html' : CURRENT_TEMPLATE.'/index.html');
$vamTemplate->display($template);
include ('includes/application_bottom.php');
?>