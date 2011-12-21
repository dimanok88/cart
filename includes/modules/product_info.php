<?php
/* -----------------------------------------------------------------------------------------
   $Id: product_info.php 1317 2007-02-06 20:41:56 VaM $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_info.php,v 1.94 2003/05/04); www.oscommerce.com 
   (c) 2003      nextcommerce (product_info.php,v 1.46 2003/08/25); www.nextcommerce.org
   (c) 2004      xt:Commerce (product_info.php,v 1.46 2003/08/25); xt-commerce.com

   Released under the GNU General Public License 
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist
   New Attribute Manager v4b                            Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com   
   Cross-Sell (X-Sell) Admin 1                          Autor: Joshua Dechant (dreamscape)
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

//include needed functions
require_once (DIR_FS_INC.'vam_check_categories_status.inc.php');
require_once (DIR_FS_INC.'vam_get_products_mo_images.inc.php');
require_once (DIR_FS_INC.'vam_get_vpe_name.inc.php');
require_once (DIR_FS_INC.'get_cross_sell_name.inc.php');

$info = new vamTemplate;
$info->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
$group_check = '';

if (!is_object($product) || !$product->isProduct()) { // product not found in database

	$error = TEXT_PRODUCT_NOT_FOUND;
	include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);

} else {
	if (ACTIVATE_NAVIGATOR == 'true')
		include (DIR_WS_MODULES.'product_navigator.php');

	vam_db_query("update ".TABLE_PRODUCTS_DESCRIPTION." set products_viewed = products_viewed+1 where products_id = '".$product->data['products_id']."' and language_id = '".$_SESSION['languages_id']."'");

// Parameters start

    $parameters_query = vamDBquery("SELECT * FROM ".TABLE_PRODUCTS_PARAMETERS2PRODUCTS." LEFT JOIN ".TABLE_PRODUCTS_PARAMETERS." using(products_parameters_id) WHERE products_id = ".$product->data['products_id']." and products_parameters.products_parameters_id is not null and products_parameters_order >= 0 and products_parameters2products_order >= 0 order by IF(products_parameters2products_order > 0, products_parameters2products_order, products_parameters_order)");

    $parameters = array();
    $i = 0;
    while ($parameters_data = vam_db_fetch_array($parameters_query, true))
    {
        $i = $parameters_data['products_parameters_id'];

        $parameters[$i]["parameters_id"] = $parameters_data["products_parameters_id"];
        $parameters[$i]["parameters_name"] = $parameters_data["products_parameters_name"];
        $parameters[$i]["parameters_suff"] = $parameters_data["products_parameters_titlesuff"];
        $parameters[$i]["parameters_group"] = $parameters_data["products_parameters_group"];
        $parameters[$i]["is_group"] = $parameters_data["products_parameters_type"] == 'g';
        $parameters[$i]["parameters_value"] = $parameters_data["products_parameters2products_value"];
        $i++;
    }

    foreach($parameters as $i => $p)
    {
        foreach(array($product->data['products_id']) as $id)
        {
            if (!isset($parameters[$i]["parameters_values"][$id]))  $parameters[$i]["parameters_values"][$id] = "";
        }
    }


    $temp = array();
    foreach($parameters as $k => $p)
    {
        if ($p["parameters_group"] == 0) $temp[$p["parameters_id"]] = $p;
    }
    $parameters_query = vamDBquery("SELECT * FROM ".TABLE_PRODUCTS_PARAMETERS." WHERE products_parameters_type = 'g' order by products_parameters_order");
    while ($parameters_data = vam_db_fetch_array($parameters_query, true))
    {
        $parameters_data["is_group"] = $parameters_data["products_parameters_type"] == 'g';
        foreach($parameters as $k => $p)
        {
            if ($p["parameters_group"] == $parameters_data["products_parameters_id"])
            {
                if (!is_array($temp[$parameters_data["products_parameters_id"]]))
                {
                    $temp[$parameters_data["products_parameters_id"]] = array("parameters_id" => $parameters_data["products_parameters_id"],
                                                                              "parameters_name" => $parameters_data["products_parameters_name"],
                                                                              "is_group" => $parameters_data["is_group"]);
                }
                $temp[$p["parameters_id"]] = $p;
            }
        }
    }
    $parameters = $temp;
    $info->assign('parameters', $parameters);

// Parameters end

		$products_price = $vamPrice->GetPrice($product->data['products_id'], $format = true, 1, $product->data['products_tax_class_id'], $product->data['products_price'], 1);

		// check if customer is allowed to add to cart
		if ($_SESSION['customers_status']['customers_status_show_price'] != '0') {
			// fsk18
			if ($_SESSION['customers_status']['customers_fsk18'] == '1') {
				if ($product->data['products_fsk18'] == '0') {
					$info->assign('ADD_QTY', vam_draw_input_field('products_qty', '1', 'size="3"').' '.vam_draw_hidden_field('products_id', $product->data['products_id']));
					$info->assign('ADD_CART_BUTTON', vam_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART));
				}
			} else {
				$info->assign('ADD_QTY', vam_draw_input_field('products_qty', '1', 'size="3"').' '.vam_draw_hidden_field('products_id', $product->data['products_id']));
				$info->assign('ADD_CART_BUTTON', vam_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART));
			}
		}

		if ($product->data['products_fsk18'] == '1') {
			$info->assign('PRODUCTS_FSK18', 'true');
		}
		if (ACTIVATE_SHIPPING_STATUS == 'true') {
			$info->assign('SHIPPING_NAME', $main->getShippingStatusName($product->data['products_shippingtime']));
			$info->assign('SHIPPING_IMAGE', $main->getShippingStatusImage($product->data['products_shippingtime']));
		}
		$info->assign('FORM_ACTION', vam_draw_form('cart_quantity', vam_href_link(FILENAME_PRODUCT_INFO, vam_get_all_get_params(array ('action')).'action=add_product')));
		$info->assign('FORM_END', '</form>');
		$info->assign('PRODUCTS_PRICE', $products_price['formated']);
		$info->assign('PRODUCTS_PRICE_PLAIN', $products_price['plain']);
		if ($product->data['products_vpe_status'] == 1 && $product->data['products_vpe_value'] != 0.0 && $products_price['plain'] > 0)
			$info->assign('PRODUCTS_VPE', $vamPrice->Format($products_price['plain'] * (1 / $product->data['products_vpe_value']), true).TXT_PER.vam_get_vpe_name($product->data['products_vpe']));
		$info->assign('PRODUCTS_ID', $product->data['products_id']);
		$info->assign('PRODUCTS_NAME', vam_parse_input_field_data($product->data['products_name'], array('"' => '&quot;')));
		if ($_SESSION['customers_status']['customers_status_show_price'] != 0) {
			// price incl tax
			$tax_rate = $vamPrice->TAX[$product->data['products_tax_class_id']];				
			$tax_info = $main->getTaxInfo($tax_rate);
			$info->assign('PRODUCTS_TAX_INFO', $tax_info);
			$info->assign('PRODUCTS_SHIPPING_LINK',$main->getShippingLink());
		}
		$info->assign('PRODUCTS_MODEL', $product->data['products_model']);
		$info->assign('PRODUCTS_EAN', $product->data['products_ean']);
		$info->assign('PRODUCTS_QUANTITY', $product->data['products_quantity']);
		$info->assign('PRODUCTS_WEIGHT', $product->data['products_weight']);
		$info->assign('PRODUCTS_STATUS', $product->data['products_status']);
		$info->assign('PRODUCTS_ORDERED', $product->data['products_ordered']);
      $info->assign('PRODUCTS_PRINT', '<img src="templates/'.CURRENT_TEMPLATE.'/buttons/'.$_SESSION['language'].'/print.gif"  style="cursor:hand" onclick="javascript:window.open(\''.vam_href_link(FILENAME_PRINT_PRODUCT_INFO, 'products_id='.$product->data['products_id']).'\', \'popup\', \'toolbar=0, scrollbars=yes, width=640, height=600\')" alt="" />');
		$info->assign('PRODUCTS_PRINT_LINK', vam_href_link(FILENAME_PRINT_PRODUCT_INFO, 'products_id='.$product->data['products_id']));      
		$info->assign('PRODUCTS_DESCRIPTION', stripslashes($product->data['products_description']));
		$image = '';

		$info->assign('ASK_PRODUCT_QUESTION', '<img src="templates/'.CURRENT_TEMPLATE.'/buttons/'.$_SESSION['language'].'/button_ask_a_question.gif" style="cursor:hand" onclick="javascript:window.open(\''.vam_href_link(FILENAME_ASK_PRODUCT_QUESTION, 'products_id='.$product->data['products_id']).'\', \'popup\', \'toolbar=0, width=640, height=760\')" alt="" />');
		$info->assign('ASK_PRODUCT_QUESTION_LINK', vam_href_link(FILENAME_ASK_PRODUCT_QUESTION, 'products_id='.$product->data['products_id']));


		if ($product->data['products_keywords'] != '') {

		$products_tags = explode (",", $product->data['products_keywords']);

          	foreach ($products_tags as $tags) {
                $tags_data[] = array(
                'NAME' => trim($tags),
                'LINK' => vam_href_link(FILENAME_ADVANCED_SEARCH_RESULT, 'keywords='.trim($tags)));
        $info->assign('tags_data', $tags_data);
            }

		$info->assign('PRODUCTS_TAGS', $products_tags);

		}

$cat_query = vamDBquery("SELECT
                                 categories_name
                                 FROM ".TABLE_CATEGORIES_DESCRIPTION." 
                                 WHERE categories_id='".$current_category_id."'
                                 and language_id = '".(int) $_SESSION['languages_id']."'"
                                 );
$cat_data = vam_db_fetch_array($cat_query, true);
		
   $manufacturer_query = vamDBquery("select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, mi.manufacturers_url from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$_SESSION['languages_id'] . "'), " . TABLE_PRODUCTS . " p  where p.products_id = '" . $product->data['products_id'] . "' and p.manufacturers_id = m.manufacturers_id");
      $manufacturer = vam_db_fetch_array($manufacturer_query,true);

		$info->assign('CATEGORY', $cat_data['categories_name']);
      $info->assign('MANUFACTURER',$manufacturer['manufacturers_name']);

		if ($product->data['products_image'] != '')
			$image = DIR_WS_INFO_IMAGES.$product->data['products_image'];
	   
	   if (!file_exists($image)) $image = DIR_WS_INFO_IMAGES.'../noimage.gif';

		$info->assign('PRODUCTS_IMAGE', $image);

		$image_pop = DIR_WS_POPUP_IMAGES.$product->data['products_image'];
		$info->assign('PRODUCTS_POPUP_IMAGE', $image_pop);
		
		//mo_images - by Novalis@eXanto.de
		if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
			$connector = '/';
		}else{
			$connector = '&';
		}
		$products_popup_link = vam_href_link(FILENAME_POPUP_IMAGE, 'pID='.$product->data['products_id'].$connector.'imgID=0');
if (!is_file(DIR_WS_POPUP_IMAGES.$product->data['products_image'])) $products_popup_link = '';
$info->assign('PRODUCTS_POPUP_LINK', $products_popup_link);

		$mo_images = vam_get_products_mo_images($product->data['products_id']);
        if ($mo_images != false) {
    $info->assign('PRODUCTS_MO_IMAGES', $mo_images);
            foreach ($mo_images as $img) {
                $products_mo_popup_link = DIR_WS_POPUP_IMAGES . $img['image_name'];
if (!file_exists(DIR_WS_POPUP_IMAGES.$img['image_name'])) $products_mo_popup_link = '';
                $mo_img[] = array(
                'PRODUCTS_MO_IMAGE' => DIR_WS_INFO_IMAGES . $img['image_name'],
                'PRODUCTS_MO_POPUP_IMAGE' => $products_mo_popup_link,
                'PRODUCTS_MO_POPUP_LINK' => $products_mo_popup_link);
        $info->assign('mo_img', $mo_img);
            }
        }
		//mo_images EOF
		$discount = 0.00;
		if ($_SESSION['customers_status']['customers_status_public'] == 1 && $_SESSION['customers_status']['customers_status_discount'] != '0.00') {
			$discount = $_SESSION['customers_status']['customers_status_discount'];
			if ($product->data['products_discount_allowed'] < $_SESSION['customers_status']['customers_status_discount'])
				$discount = $product->data['products_discount_allowed'];
			if ($discount != '0.00')
				$info->assign('PRODUCTS_DISCOUNT', $discount.'%');
		}

		include (DIR_WS_MODULES.'product_attributes.php');
		include (DIR_WS_MODULES.'product_reviews.php');

		if (vam_not_null($product->data['products_url']))
			$info->assign('PRODUCTS_URL', sprintf(TEXT_MORE_INFORMATION, vam_href_link(FILENAME_REDIRECT, 'action=product&id='.$product->data['products_id'], 'NONSSL', true, false)));

			$info->assign('PRODUCTS_URL1', $product->data['products_url']);

		if ($product->data['products_date_available'] > date('Y-m-d H:i:s')) {
			$info->assign('PRODUCTS_DATE_AVIABLE', sprintf(TEXT_DATE_AVAILABLE, vam_date_long($product->data['products_date_available'])));

		} else {
			if ($product->data['products_date_added'] != '0000-00-00 00:00:00')
				$info->assign('PRODUCTS_ADDED', sprintf(TEXT_DATE_ADDED, vam_date_long($product->data['products_date_added'])));

		}

		if ($_SESSION['customers_status']['customers_status_graduated_prices'] == 1)
			include (DIR_WS_MODULES.FILENAME_GRADUATED_PRICE);

                      $extra_fields_query = vamDBquery("
                      SELECT pef.products_extra_fields_status as status, pef.products_extra_fields_name as name, ptf.products_extra_fields_value as value
                      FROM ". TABLE_PRODUCTS_EXTRA_FIELDS ." pef
             LEFT JOIN  ". TABLE_PRODUCTS_TO_PRODUCTS_EXTRA_FIELDS ." ptf
            ON ptf.products_extra_fields_id=pef.products_extra_fields_id
            WHERE ptf.products_id=". $product->data['products_id'] ." and ptf.products_extra_fields_value<>'' and (pef.languages_id='0' or pef.languages_id='".$_SESSION['languages_id']."')
            ORDER BY products_extra_fields_order");

  while ($extra_fields = vam_db_fetch_array($extra_fields_query,true)) {
        if (! $extra_fields['status'])  // show only enabled extra field
           continue;
  
  $extra_fields_data[] = array (
  'NAME' => $extra_fields['name'], 
  'VALUE' => $extra_fields['value']
  );
  
  }

  $info->assign('extra_fields_data', $extra_fields_data);

  $info->assign('info_message', $_SESSION['error_cart_msg']);

		include (DIR_WS_MODULES.FILENAME_PRODUCTS_MEDIA);
		include (DIR_WS_MODULES.FILENAME_ALSO_PURCHASED_PRODUCTS);
		include (DIR_WS_MODULES.FILENAME_CROSS_SELLING);
	
	if ($product->data['product_template'] == '' or $product->data['product_template'] == 'default') {
		$files = array ();
		if ($dir = opendir(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_info/')) {
			while ($file = readdir($dir)) {
				if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_info/'.$file) and ($file != "index.html") and (substr($file, 0, 1) !=".")) {
					$files[] = array ('id' => $file, 'text' => $file);
				} //if
			} // while
			closedir($dir);
		}
		$product->data['product_template'] = $files[0]['id'];
	}

$i = count($_SESSION['tracking']['products_history']);
	if ($i > 6) {
		array_shift($_SESSION['tracking']['products_history']);
		$_SESSION['tracking']['products_history'][6] = $product->data['products_id'];
		$_SESSION['tracking']['products_history'] = array_unique($_SESSION['tracking']['products_history']);
	} else {
		$_SESSION['tracking']['products_history'][$i] = $product->data['products_id'];
		$_SESSION['tracking']['products_history'] = array_unique($_SESSION['tracking']['products_history']);
	}
	
	$info->assign('language', $_SESSION['language']);
	// set cache ID
	 if (!CacheCheck()) {
		$info->caching = 0;
		$product_info = $info->fetch(CURRENT_TEMPLATE.'/module/product_info/'.$product->data['product_template']);
	} else {
		$info->caching = 1;
		$info->cache_lifetime = CACHE_LIFETIME;
		$info->cache_modified_check = CACHE_CHECK;
		$cache_id = $product->data['products_id'].$_SESSION['language'].$_SESSION['customers_status']['customers_status_name'].$_SESSION['currency'];
		$product_info = $info->fetch(CURRENT_TEMPLATE.'/module/product_info/'.$product->data['product_template'], $cache_id);
	}

}
$vamTemplate->assign('main_content', $product_info);
?>