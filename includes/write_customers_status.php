<?php
/* -----------------------------------------------------------------------------------------
   $Id: write_customers_status.php 1117 2007-02-06 20:14:56 VaM $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (write_customers_status.php,v 1.8 2003/08/1); www.nextcommerce.org
   (c) 2004	 xt:Commerce (write_customers_status.php,v 1.8 2003/08/1); xt-commerce.com
   
   Released under the GNU General Public License
   --------------------------------------------------------------------------------------- 
   
   based on Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // write customers status in session
  if (isset($_SESSION['customer_id'])) {
    $customers_status_query_1 = vam_db_query("SELECT customers_status, customers_personal_discount FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '" . $_SESSION['customer_id'] . "'");
    $customers_status_value_1 = vam_db_fetch_array($customers_status_query_1);

    $customers_status_query = vam_db_query("SELECT
                                                *
                                            FROM
                                                " . TABLE_CUSTOMERS_STATUS . "
                                            WHERE
                                                customers_status_id = '" . $customers_status_value_1['customers_status'] . "' AND language_id = '" . $_SESSION['languages_id'] . "'");

    $customers_status_value = vam_db_fetch_array($customers_status_query);

    $_SESSION['customers_status'] = array();
    $_SESSION['customers_status']= array(
      'customers_status_id' => $customers_status_value_1['customers_status'],
      'customers_status_name' => $customers_status_value['customers_status_name'],
      'customers_status_image' => $customers_status_value['customers_status_image'],
      'customers_status_public' => $customers_status_value['customers_status_public'],
      'customers_status_min_order' => $customers_status_value['customers_status_min_order'],
      'customers_status_max_order' => $customers_status_value['customers_status_max_order'],
      'customers_status_discount' => ($customers_status_value_1['customers_personal_discount'] > 0 ? $customers_status_value_1['customers_personal_discount'] : $customers_status_value['customers_status_discount']),
      'customers_status_ot_discount_flag' => $customers_status_value['customers_status_ot_discount_flag'],
      'customers_status_ot_discount' => $customers_status_value['customers_status_ot_discount'],
      'customers_status_graduated_prices' => $customers_status_value['customers_status_graduated_prices'],
      'customers_status_show_price' => $customers_status_value['customers_status_show_price'],
      'customers_status_show_price_tax' => $customers_status_value['customers_status_show_price_tax'],
      'customers_status_add_tax_ot' => $customers_status_value['customers_status_add_tax_ot'],
      'customers_status_payment_unallowed' => $customers_status_value['customers_status_payment_unallowed'],
      'customers_status_shipping_unallowed' => $customers_status_value['customers_status_shipping_unallowed'],
      'customers_status_discount_attributes' => $customers_status_value['customers_status_discount_attributes'],
      'customers_fsk18' => $customers_status_value['customers_fsk18'],
      'customers_fsk18_display' => $customers_status_value['customers_fsk18_display'],
      'customers_status_write_reviews' => $customers_status_value['customers_status_write_reviews'],
      'customers_status_read_reviews' => $customers_status_value['customers_status_read_reviews']
    );
  } else {
    $customers_status_query = vam_db_query("SELECT
                                                *
                                            FROM
                                                " . TABLE_CUSTOMERS_STATUS . "
                                            WHERE
                                                customers_status_id = '" . DEFAULT_CUSTOMERS_STATUS_ID_GUEST . "' AND language_id = '" . $_SESSION['languages_id'] . "'");
    $customers_status_value = vam_db_fetch_array($customers_status_query);

    $_SESSION['customers_status'] = array();
    $_SESSION['customers_status']= array(
      'customers_status_id' => DEFAULT_CUSTOMERS_STATUS_ID_GUEST,
      'customers_status_name' => $customers_status_value['customers_status_name'],
      'customers_status_image' => $customers_status_value['customers_status_image'],
      'customers_status_discount' => $customers_status_value['customers_status_discount'],
      'customers_status_public' => $customers_status_value['customers_status_public'],
      'customers_status_min_order' => $customers_status_value['customers_status_min_order'],
      'customers_status_max_order' => $customers_status_value['customers_status_max_order'],
      'customers_status_ot_discount_flag' => $customers_status_value['customers_status_ot_discount_flag'],
      'customers_status_ot_discount' => $customers_status_value['customers_status_ot_discount'],
      'customers_status_graduated_prices' => $customers_status_value['customers_status_graduated_prices'],
      'customers_status_show_price' => $customers_status_value['customers_status_show_price'],
      'customers_status_show_price_tax' => $customers_status_value['customers_status_show_price_tax'],
      'customers_status_add_tax_ot' => $customers_status_value['customers_status_add_tax_ot'],
      'customers_status_payment_unallowed' => $customers_status_value['customers_status_payment_unallowed'],
      'customers_status_shipping_unallowed' => $customers_status_value['customers_status_shipping_unallowed'],
      'customers_status_discount_attributes' => $customers_status_value['customers_status_discount_attributes'],
      'customers_fsk18' => $customers_status_value['customers_fsk18'],
      'customers_fsk18_display' => $customers_status_value['customers_fsk18_display'],
      'customers_status_write_reviews' => $customers_status_value['customers_status_write_reviews'],
      'customers_status_read_reviews' => $customers_status_value['customers_status_read_reviews']
    );
  }

?>