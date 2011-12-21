<?php
/* -----------------------------------------------------------------------------------------
   $Id: last_viewed.php 1292 2007-02-07 12:30:44 VaM $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2004	 xt:Commerce (last_viewed.php,v 1.4 2003/08/13); xt-commerce.com 
   (c) 2004	 xt:Commerce (last_viewed.php,v 1.4 2003/08/13); xt-commerce.com 

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

$box = new vamTemplate;
$box->assign('tpl_path','templates/'.CURRENT_TEMPLATE.'/');
$box_content='';

if (isset ($_SESSION[tracking][products_history][0])) {
  // include needed functions
  require_once(DIR_FS_INC . 'vam_rand.inc.php');
  require_once(DIR_FS_INC . 'vam_get_path.inc.php');
  require_once(DIR_FS_INC . 'vam_get_products_name.inc.php');	
$max = count($_SESSION[tracking][products_history]);
$max--;
$random_last_viewed = vam_rand(0,$max);

  //fsk18 lock
  $fsk_lock='';
  if ($_SESSION['customers_status']['customers_fsk18_display']=='0') {
  $fsk_lock=' and p.products_fsk18!=1';
  }
     if (GROUP_CHECK=='true') {
       $group_check=" and p.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";

  }

    
                                  
       $random_query = "select p.products_id,
                                           pd.products_name,
                                           p.products_price,
                                           p.products_tax_class_id,
                                           p.products_image,
                                           p2c.categories_id,
                                           p.products_vpe,
				                           p.products_vpe_status,
				                           p.products_vpe_value,
                                           cd.categories_name 
                                           from 
                                           " . TABLE_PRODUCTS . " p,
                                           " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                           " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c,
                                           " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                           where p.products_status = '1'                                                                                               
                                           and p.products_id = '".(int)$_SESSION[tracking][products_history][$random_last_viewed]."'
                                           and pd.products_id = '".(int)$_SESSION[tracking][products_history][$random_last_viewed]."'
                                           and p2c.products_id = '".(int)$_SESSION[tracking][products_history][$random_last_viewed]."'
                                           and pd.language_id = '" . $_SESSION['languages_id'] . "'
                                           and cd.categories_id = p2c.categories_id
                                           ".$group_check."
                                           ".$fsk_lock."
                                           and cd.language_id = '" . $_SESSION['languages_id'] . "'";

    $random_query = vamDBquery($random_query);
    $random_product = vam_db_fetch_array($random_query,true);

    $random_products_price = $vamPrice->GetPrice($random_product['products_id'],$format=true,1,$random_product['products_tax_class_id'],$random_product['products_price']);

    $category_path = vam_get_path($random_product['categories_id']);

if ($random_product['products_name']!='') {

    $box->assign('box_content',$product->buildDataArray($random_product));
    
    $box->assign('MY_PAGE', 'TEXT_MY_PAGE');
    $box->assign('WATCH_CATGORY', 'TEXT_WATCH_CATEGORY');
    $box->assign('MY_PERSONAL_PAGE',vam_href_link(FILENAME_ACCOUNT));
	$box->assign('CATEGORY_LINK',vam_href_link(FILENAME_DEFAULT, vam_category_link($random_product['categories_id'],$random_product['categories_name'])));
    $box->assign('CATEGORY_NAME',$random_product['categories_name']);
    $box->assign('language', $_SESSION['language']);

       	  // set cache ID
  if (!CacheCheck()) {
  $box->caching = 0;
  $box_last_viewed= $box->fetch(CURRENT_TEMPLATE.'/boxes/box_last_viewed.html');
  } else {
  $box->caching = 1;	
  $box->cache_lifetime=CACHE_LIFETIME;
  $box->cache_modified_check=CACHE_CHECK;
  $cache_id = $_SESSION['language'].$random_product['products_id'].$_SESSION['customers_status']['customers_status_name'];
  $box_last_viewed= $box->fetch(CURRENT_TEMPLATE.'/boxes/box_last_viewed.html',$cache_id);
  }
    $vamTemplate->assign('box_LAST_VIEWED',$box_last_viewed);
 }
}
    ?>