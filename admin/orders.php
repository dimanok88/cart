<?php
/* --------------------------------------------------------------
   $Id: orders.php 1189 2010-04-24 11:13:01Z VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(orders.php,v 1.109 2003/05/28); www.oscommerce.com
   (c) 2003	 nextcommerce (orders.php,v 1.19 2003/08/24); www.nextcommerce.org
   (c) 2004	 xt:Commerce (orders.php,v 1.19 2003/08/24); xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:
   OSC German Banktransfer v0.85a       	Autor:	Dominik Guder <osc@guder.org>
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   credit card encryption functions for the catalog module
   BMC 2003 for the CC CVV Module

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require ('includes/application_top.php');
require_once(DIR_FS_CATALOG.'includes/external/phpmailer/class.phpmailer.php');
require_once (DIR_FS_INC.'vam_php_mail.inc.php');
require_once (DIR_FS_INC.'vam_add_tax.inc.php');
require_once (DIR_FS_INC.'changedataout.inc.php');
require_once (DIR_FS_INC.'vam_validate_vatid_status.inc.php');
require_once (DIR_FS_INC.'vam_get_attributes_model.inc.php');

// initiate template engine for mail
$vamTemplate = new vamTemplate;
require (DIR_WS_CLASSES.'currencies.php');
$currencies = new currencies();

if ((($_GET['action'] == 'edit') || ($_GET['action'] == 'update_order')) && ($_GET['oID'])) {
	$oID = vam_db_prepare_input($_GET['oID']);

	$orders_query = vam_db_query("select orders_id from ".TABLE_ORDERS." where orders_id = '".vam_db_input($oID)."'");
	$order_exists = true;
	if (!vam_db_num_rows($orders_query)) {
		$order_exists = false;
		$messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
	}
}

require (DIR_WS_CLASSES.'order.php');
if ((($_GET['action'] == 'edit') || ($_GET['action'] == 'update_order')) && ($order_exists)) {
	$order = new order($oID);

  $order_payment = $order->info['payment_class'];
  
  require(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/payment/' . $order_payment .'.php');
  $order_payment_text = constant(MODULE_PAYMENT_.strtoupper($order_payment)._TEXT_TITLE);

      $shipping_method_query = vam_db_query("select title from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . vam_db_input($oID) . "' and class = 'ot_shipping'");
      $shipping_method = vam_db_fetch_array($shipping_method_query);

  $order_shipping_text = ((substr($shipping_method['title'], -1) == ':') ? substr(strip_tags($shipping_method['title']), 0, -1) : strip_tags($shipping_method['title']));

}

  $lang_query = vam_db_query("select languages_id from " . TABLE_LANGUAGES . " where directory = '" . $order->info['language'] . "'");
  $lang = vam_db_fetch_array($lang_query);
  $lang=$lang['languages_id'];

if (!isset($lang)) $lang=$_SESSION['languages_id'];
$orders_statuses = array ();
$orders_status_array = array ();
$orders_status_query = vam_db_query("select orders_status_id, orders_status_name from ".TABLE_ORDERS_STATUS." where language_id = '".$lang."'");
while ($orders_status = vam_db_fetch_array($orders_status_query)) {
	$orders_statuses[] = array ('id' => $orders_status['orders_status_id'], 'text' => $orders_status['orders_status_name']);
	$orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
}

// Start Batch Update Status v0.4
if (isset($_POST['submit']) && isset($_POST['multi_orders'])){
 if (($_POST['submit'] == BUTTON_SUBMIT)&&(isset($_POST['new_status']))&&(!isset($_POST['delete_orders']))){ // Fair enough, let's update ;)
  $status = vam_db_prepare_input($_POST['new_status']);
  $comments = vam_db_prepare_input($_POST['comments']);
  if ($status == '') { // New status not selected
     vam_redirect(vam_href_link(FILENAME_ORDERS),vam_get_all_get_params());
  }
  foreach ($_POST['multi_orders'] as $this_orderID){
    $order_updated = false;
    $check_status_query = vam_db_query("select customers_name, customers_email_address, orders_status, date_purchased from " . TABLE_ORDERS . " where orders_id = '" . (int)$this_orderID . "'");
    $check_status = vam_db_fetch_array($check_status_query);

    if ($check_status['orders_status'] != $status) {
       vam_db_query("update " . TABLE_ORDERS . " set orders_status = '" . vam_db_input($status) . "', last_modified = now() where orders_id = '" . (int)$this_orderID . "'");
       $customer_notified ='0';
          if (isset($_POST['notify'])) {
            $notify_comments = '';

				// assign language to template for caching
				$vamTemplate->assign('language', $_SESSION['language']);
				$vamTemplate->caching = false;

				$vamTemplate->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
				$vamTemplate->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

				$vamTemplate->assign('NAME', $check_status['customers_name']);
				$vamTemplate->assign('ORDER_NR', $this_orderID);
				$vamTemplate->assign('ORDER_LINK', vam_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id='.$_POST['multi_orders'], 'SSL'));
				$vamTemplate->assign('ORDER_DATE', vam_date_long($check_status['date_purchased']));
				$vamTemplate->assign('ORDER_STATUS', $orders_status_array[$status]);

				$html_mail = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$_SESSION['language'].'/change_order_mail.html');
				$txt_mail = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$_SESSION['language'].'/change_order_mail.txt');

            // create subject
           $billing_subject = str_replace('{$nr}', $this_orderID, EMAIL_BILLING_SUBJECT);

				vam_php_mail(EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, $check_status['customers_email_address'], $check_status['customers_name'], '', EMAIL_BILLING_REPLY_ADDRESS, EMAIL_BILLING_REPLY_ADDRESS_NAME, '', '', $billing_subject, $html_mail, $txt_mail);

            $customer_notified = '1';
          }
          vam_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . (int)$this_orderID . "', '" . vam_db_input($status) . "', now(), '" . vam_db_input($customer_notified) . "', '" . vam_db_input($comments)  . "')");
          $order_updated = true;

		     // denuz added accumulated discount

        $changed = false;
       
        $check_group_query = vam_db_query("select customers_status_id from " . TABLE_CUSTOMERS_STATUS_ORDERS_STATUS . " where orders_status_id = " . $status);
        if (vam_db_num_rows($check_group_query)) {
           while ($groups = vam_db_fetch_array($check_group_query)) {
              // calculating total customers purchase
              // building query
              $customer_query = vam_db_query("select c.* from " . TABLE_CUSTOMERS . " as c, " . TABLE_ORDERS . " as o where o.customers_id = c.customers_id and o.orders_id = " . (int)$this_orderID );
              $customer = vam_db_fetch_array($customer_query);
			  unset($customer_id1);
			     if ($customer['customers_status'] == '0') {
              $customer_id1 = 0;
              } else {
              $customer_id1 = $customer['customers_id'];
              }
              $statuses_groups_query = vam_db_query("select orders_status_id from " . TABLE_CUSTOMERS_STATUS_ORDERS_STATUS . " where customers_status_id = " . $groups['customers_status_id']);
              $purchase_query = "select sum(ot.value) as total from " . TABLE_ORDERS_TOTAL . " as ot, " . TABLE_ORDERS . " as o where ot.orders_id = o.orders_id and o.customers_id = " . $customer_id1 . " and ot.class = 'ot_total' and (";
              $statuses = vam_db_fetch_array($statuses_groups_query);
              $purchase_query .= " o.orders_status = " . $statuses['orders_status_id'];
              while ($statuses = vam_db_fetch_array($statuses_groups_query)) {
                  $purchase_query .= " or o.orders_status = " . $statuses['orders_status_id'];
              }
              $purchase_query .=");";
                  
              $total_purchase_query = vam_db_query($purchase_query);
              $total_purchase = vam_db_fetch_array($total_purchase_query);
              $customers_total = $total_purchase['total'];

              // looking for current accumulated limit & discount
              $acc_query = vam_db_query("
			  select cg.customers_status_accumulated_limit,
			  cg.customers_status_name,
			  cg.customers_status_discount
			  from " . TABLE_CUSTOMERS_STATUS . " as cg,
			  " . TABLE_CUSTOMERS . " as c
			  where cg.customers_status_id = c.customers_status
			  and c.customers_id = " .$customer_id1);
              $current_limit = @mysql_result($acc_query, 0, "customers_status_accumulated_limit");
              $current_discount = @mysql_result($acc_query, 0, "customers_status_discount");
              $current_group = @mysql_result($acc_query, 0, "customers_status_name");
                                                                                                                                                                                                 
              // ok, looking for available group

			     if ($customer['customers_status'] > '0') {

              $groups_query = vam_db_query("select customers_status_discount, customers_status_id, customers_status_name, customers_status_accumulated_limit from " . TABLE_CUSTOMERS_STATUS . " where customers_status_accumulated_limit < " . $customers_total . " and customers_status_discount >= " . $current_discount . " and customers_status_accumulated_limit >= " . $current_limit . " and customers_status_id = " . $groups['customers_status_id'] . " order by customers_status_accumulated_limit DESC");
            
               }
               
              if (vam_db_num_rows($groups_query)) {
                 // new group found
                 $customers_groups_id = @mysql_result($groups_query, 0, "customers_status_id");
                 $customers_groups_name = @mysql_result($groups_query, 0, "customers_status_name");
                 $limit = @mysql_result($groups_query, 0, "customers_status_accumulated_limit");
                 $current_discount = @mysql_result($groups_query, 0, "customers_status_discount");
    
                 // updating customers group

                 vam_db_query("update " . TABLE_CUSTOMERS . " set customers_status = " . $customers_groups_id . " where customers_id = " .$customer_id1);

                 $changed = true;
             }
           }

           $groups_query = vam_db_query("select cg.* from " . TABLE_CUSTOMERS_STATUS . " as cg, " . TABLE_CUSTOMERS . " as c where c.customers_status = cg.customers_status_id and c.customers_id = " .$customer_id1);
           $customers_groups_id = @mysql_result($groups_query, 0, "customers_status_id");
           $customers_groups_name = @mysql_result($groups_query, 0, "customers_status_name");
           $limit = @mysql_result($groups_query, 0, "customers_status_accumulated_limit");
           $current_discount = @mysql_result($groups_query, 0, "customers_status_discount");

			if ($customer['customers_status'] != $customers_groups_id) {           

           if ($changed) {

             // send emails

				// assign language to template for caching

				$vamTemplate->assign('language', $_SESSION['language']);
				$vamTemplate->caching = false;

				// set dirs manual

				$vamTemplate->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
				$vamTemplate->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

				$vamTemplate->assign('CUSTOMERNAME', $check_status['customers_name']);
				$vamTemplate->assign('EMAIL', $check_status['customers_email_address']);
				$vamTemplate->assign('GROUPNAME', $customers_groups_name);
				$vamTemplate->assign('GROUPDISCOUNT', $current_discount);
				$vamTemplate->assign('ACCUMULATED_LIMIT', $currencies->display_price($limit, 0));

            //email to admin

				$html_mail_admin = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$_SESSION['language'].'/accumulated_discount_admin.html');
				$txt_mail_admin = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$_SESSION['language'].'/accumulated_discount_admin.txt');

				vam_php_mail(EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, '', EMAIL_BILLING_REPLY_ADDRESS, EMAIL_BILLING_REPLY_ADDRESS_NAME, '', '', EMAIL_ACC_SUBJECT, $html_mail_admin, $txt_mail_admin);

            //email to customer

				$html_mail_customer = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$_SESSION['language'].'/accumulated_discount_customer.html');
				$txt_mail_customer = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$_SESSION['language'].'/accumulated_discount_customer.txt');

				vam_php_mail(EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, $check_status['customers_email_address'], $check_status['customers_name'], '', EMAIL_BILLING_REPLY_ADDRESS, EMAIL_BILLING_REPLY_ADDRESS_NAME, '', '', EMAIL_ACC_SUBJECT, $html_mail_customer, $txt_mail_customer);

           }
          }
        }
        // eof denuz added accumulated discount

    }
        if ($order_updated == true) {
         $messageStack->add_session(BUS_ORDER . $this_orderID . ' ' . BUS_SUCCESS, 'success');
        } else {
          $messageStack->add_session(BUS_ORDER . $this_orderID . ' ' . BUS_WARNING, 'warning');
        }
  } // End foreach ID loop
 }

// /delete orders
 
 if (($_POST['submit'] == BUTTON_SUBMIT)&&(isset($_POST['delete_orders']))){

  foreach ($_POST['multi_orders'] as $this_orderID){

    $orders_deleted = false;

		  vam_db_query("delete from " . TABLE_ORDERS . " where orders_id = '" . (int)$this_orderID . "'");
		  vam_db_query("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$this_orderID . "'");
		  vam_db_query("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int)$this_orderID . "'");
		  vam_db_query("delete from " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id = '" . (int)$this_orderID . "'");
		  vam_db_query("delete from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$this_orderID . "'");
		  vam_db_query("delete from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$this_orderID . "'");
		  vam_db_query("delete from " . TABLE_PERSONS . " where orders_id = '" . (int)$this_orderID . "'");
		  vam_db_query("delete from " . TABLE_COMPANIES . " where orders_id = '" . (int)$this_orderID . "'");

          $orders_deleted = true;

        if ($orders_deleted == true) {
         $messageStack->add_session(BUS_ORDER . $this_orderID . ' ' . BUS_DELETE_SUCCESS, 'success');
        } else {
          $messageStack->add_session(BUS_ORDER . $this_orderID . ' ' . BUS_DELETE_WARNING, 'warning');
        }
  } // End foreach ID loop
 }

// /delete orders

   vam_redirect(vam_href_link(FILENAME_ORDERS),vam_get_all_get_params());
}

// End Batch Update Status v0.4

switch ($_GET['action']) {
	case 'update_order' :
		$oID = vam_db_prepare_input($_GET['oID']);
		$status = vam_db_prepare_input($_POST['status']);
		$comments = vam_db_prepare_input($_POST['comments']);
	//	$order = new order($oID);
		$order_updated = false;
		$check_status_query = vam_db_query("select customers_name, customers_email_address, orders_status, date_purchased from ".TABLE_ORDERS." where orders_id = '".vam_db_input($oID)."'");
		$check_status = vam_db_fetch_array($check_status_query);
		if ($check_status['orders_status'] != $status || $comments != '') {
			vam_db_query("update ".TABLE_ORDERS." set orders_status = '".vam_db_input($status)."', last_modified = now() where orders_id = '".vam_db_input($oID)."'");

			$customer_notified = '0';
			if ($_POST['notify'] == 'on') {
				$notify_comments = '';
				if ($_POST['notify_comments'] == 'on') {
					//$notify_comments = sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments)."\n\n";
					$notify_comments = $comments;
				} else {
					$notify_comments = '';
				}

				// assign language to template for caching
				$vamTemplate->assign('language', $_SESSION['language']);
				$vamTemplate->caching = false;

				$vamTemplate->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
				$vamTemplate->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

				$vamTemplate->assign('NAME', $check_status['customers_name']);
				$vamTemplate->assign('ORDER_NR', $oID);
				$vamTemplate->assign('ORDER_LINK', vam_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id='.$oID, 'SSL'));
				$vamTemplate->assign('ORDER_DATE', vam_date_long($check_status['date_purchased']));
				$vamTemplate->assign('NOTIFY_COMMENTS', $notify_comments);
				$vamTemplate->assign('ORDER_STATUS', $orders_status_array[$status]);

				$html_mail = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/change_order_mail.html');
				$txt_mail = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/change_order_mail.txt');

            // create subject
           $billing_subject = str_replace('{$nr}', $oID, EMAIL_BILLING_SUBJECT);

				vam_php_mail(EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, $check_status['customers_email_address'], $check_status['customers_name'], '', EMAIL_BILLING_REPLY_ADDRESS, EMAIL_BILLING_REPLY_ADDRESS_NAME, '', '',  $billing_subject, $html_mail, $txt_mail);
				$customer_notified = '1';
			}

			vam_db_query("insert into ".TABLE_ORDERS_STATUS_HISTORY." (orders_id, orders_status_id, date_added, customer_notified, comments) values ('".vam_db_input($oID)."', '".vam_db_input($status)."', now(), '".$customer_notified."', '".vam_db_input($comments)."')");

			$order_updated = true;
		}

		if ($order_updated) {
			$messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
		} else {
			$messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
		}

        // denuz added accumulated discount

        $changed = false;
        
        $check_group_query = vam_db_query("select customers_status_id from " . TABLE_CUSTOMERS_STATUS_ORDERS_STATUS . " where orders_status_id = " . $status);
        if (vam_db_num_rows($check_group_query)) {
           while ($groups = vam_db_fetch_array($check_group_query)) {
              // calculating total customers purchase
              // building query
              $customer_query = vam_db_query("select c.* from " . TABLE_CUSTOMERS . " as c, " . TABLE_ORDERS . " as o where o.customers_id = c.customers_id and o.orders_id = " . (int)$oID);
              $customer = vam_db_fetch_array($customer_query);
			     if ($customer['customers_status'] == '0') {
              $customer_id2 = 0;
              } else {
              $customer_id2 = $customer['customers_id'];
              }
              $statuses_groups_query = vam_db_query("select orders_status_id from " . TABLE_CUSTOMERS_STATUS_ORDERS_STATUS . " where customers_status_id = " . $groups['customers_status_id']);
              $purchase_query = "select sum(ot.value) as total from " . TABLE_ORDERS_TOTAL . " as ot, " . TABLE_ORDERS . " as o where ot.orders_id = o.orders_id and o.customers_id = " . $customer_id2 . " and ot.class = 'ot_total' and (";
              $statuses = vam_db_fetch_array($statuses_groups_query);
              $purchase_query .= " o.orders_status = " . $statuses['orders_status_id'];
              while ($statuses = vam_db_fetch_array($statuses_groups_query)) {
                  $purchase_query .= " or o.orders_status = " . $statuses['orders_status_id'];
              }
              $purchase_query .=");";
                   
              $total_purchase_query = vam_db_query($purchase_query);
              $total_purchase = vam_db_fetch_array($total_purchase_query);
              $customers_total = $total_purchase['total'];

              // looking for current accumulated limit & discount
              $acc_query = vam_db_query("select cg.customers_status_accumulated_limit, cg.customers_status_name, cg.customers_status_discount from " . TABLE_CUSTOMERS_STATUS . " as cg, " . TABLE_CUSTOMERS . " as c where cg.customers_status_id = c.customers_status and c.customers_id = " . $customer_id2);
              $current_limit = @mysql_result($acc_query, 0, "customers_status_accumulated_limit");
              $current_discount = @mysql_result($acc_query, 0, "customers_status_discount");
              $current_group = @mysql_result($acc_query, "customers_status_name");

			     if ($customer['customers_status'] > '0') {
                                                                                                                                                                                                 
              // ok, looking for available group
              $groups_query = vam_db_query("select customers_status_discount, customers_status_id, customers_status_name, customers_status_accumulated_limit from " . TABLE_CUSTOMERS_STATUS . " where customers_status_accumulated_limit < " . $customers_total . " and customers_status_discount >= " . $current_discount . " and customers_status_accumulated_limit >= " . $current_limit . " and customers_status_id = " . $groups['customers_status_id'] . " order by customers_status_accumulated_limit DESC");

              }

              if (vam_db_num_rows($groups_query)) {
                 // new group found
                 $customers_groups_id = @mysql_result($groups_query, 0, "customers_status_id");
                 $customers_groups_name = @mysql_result($groups_query, 0, "customers_status_name");
                 $limit = @mysql_result($groups_query, 0, "customers_status_accumulated_limit");
                 $current_discount = @mysql_result($groups_query, 0, "customers_status_discount");
    
                 // updating customers group
                 vam_db_query("update " . TABLE_CUSTOMERS . " set customers_status = " . $customers_groups_id . " where customers_id = " . $customer_id2);
                 $changed = true;
             }
           }
           $groups_query = vam_db_query("select cg.* from " . TABLE_CUSTOMERS_STATUS . " as cg, " . TABLE_CUSTOMERS . " as c where c.customers_status = cg.customers_status_id and c.customers_id = " . $customer_id2);
           $customers_groups_id = @mysql_result($groups_query, 0, "customers_status_id");
           $customers_groups_name = @mysql_result($groups_query, 0, "customers_status_name");
           $limit = @mysql_result($groups_query, 0, "customers_status_accumulated_limit");
           $current_discount = @mysql_result($groups_query, 0, "customers_status_discount");

			if ($customer['customers_status'] != $customers_groups_id) {           
			
           if ($changed) {

             // send emails

				// assign language to template for caching

				$vamTemplate->assign('language', $_SESSION['language']);
				$vamTemplate->caching = false;

				// set dirs manual

				$vamTemplate->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
				$vamTemplate->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

				$vamTemplate->assign('CUSTOMERNAME', $check_status['customers_name']);
				$vamTemplate->assign('EMAIL', $check_status['customers_email_address']);
				$vamTemplate->assign('GROUPNAME', $customers_groups_name);
				$vamTemplate->assign('GROUPDISCOUNT', $current_discount);
				$vamTemplate->assign('ACCUMULATED_LIMIT', $currencies->display_price($limit, 0));
				

            //email to admin
            
				$html_mail_admin = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/accumulated_discount_admin.html');
				$txt_mail_admin = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/accumulated_discount_admin.txt');

				vam_php_mail(EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER, '', EMAIL_BILLING_REPLY_ADDRESS, EMAIL_BILLING_REPLY_ADDRESS_NAME, '', '', EMAIL_ACC_SUBJECT, $html_mail_admin, $txt_mail_admin);

            //email to customer

				$html_mail_customer = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/accumulated_discount_customer.html');
				$txt_mail_customer = $vamTemplate->fetch(CURRENT_TEMPLATE.'/admin/mail/'.$order->info['language'].'/accumulated_discount_customer.txt');

				vam_php_mail(EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, $check_status['customers_email_address'], $check_status['customers_name'], '', EMAIL_BILLING_REPLY_ADDRESS, EMAIL_BILLING_REPLY_ADDRESS_NAME, '', '', EMAIL_ACC_SUBJECT, $html_mail_customer, $txt_mail_customer);

           }
          }
        }
        
        // eof denuz added accumulated discount


		vam_redirect(vam_href_link(FILENAME_ORDERS, vam_get_all_get_params(array ('action')).'action=edit'));
		break;
	case 'deleteconfirm' :
		$oID = vam_db_prepare_input($_GET['oID']);

		vam_remove_order($oID, $_POST['restock']);

		vam_redirect(vam_href_link(FILENAME_ORDERS, vam_get_all_get_params(array ('oID', 'action'))));
		break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script type="text/javascript" src="includes/general.js"></script>
<script type="text/javascript" src="includes/javascript/categories.js"></script>
<?php if (ENABLE_TABS == 'true') { ?>
		<link type="text/css" href="../jscript/jquery/plugins/ui/css/smoothness/jquery-ui-1.7.2.custom.css" rel="stylesheet" />
		<script type="text/javascript" src="../jscript/jquery/jquery.js"></script>
		<script type="text/javascript" src="../jscript/jquery/plugins/ui/jquery-ui-1.7.2.custom.min.js"></script>
		<script type="text/javascript">
			$(function(){
				$('#tabs').tabs({ fx: { opacity: 'toggle', duration: 'fast' } });
			});
		</script>
<?php } ?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php

require (DIR_WS_INCLUDES.'header.php');
?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<?php if (ADMIN_DROP_DOWN_NAVIGATION == 'false') { ?>
    <td width="<?php echo BOX_WIDTH; ?>" align="left" valign="top">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </td>
<?php } ?>
<!-- body_text //-->
    <td class="boxCenter" valign="top">
     
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php

if (($_GET['action'] == 'edit') && ($order_exists)) {
	//    $order = new order($oID);
?>
      <tr>
      <td width="100%">
 <?php echo '<a class="button" href="' . vam_href_link(FILENAME_ORDERS, vam_get_all_get_params(array('action'))) . '"><span>' . BUTTON_BACK . '</span></a>'; ?>
 <!-- Bestellbearbeitung Anfang -->
   <a class="button" href="<?php echo vam_href_link(FILENAME_ORDERS_EDIT, 'oID='.$_GET['oID'].'&cID=' . $order->customer['ID']);?>"><span><?php echo BUTTON_EDIT ?></span></a>
<!-- Bestellbearbeitung Ende -->
 </td>

      </tr>
</table>
<div id="tabs">

			<ul>
				<li><a href="#summary"><?php echo TEXT_ORDER_SUMMARY; ?></a></li>
				<li><a href="#payment"><?php echo TEXT_ORDER_PAYMENT; ?></a></li>
				<li><a href="#products"><?php echo TEXT_ORDER_PRODUCTS; ?></a></li>
<?php if (ENABLE_MAP_TAB == 'true') { ?>
				<li><a href="#map" id="getmap"><?php echo TEXT_ORDER_MAP; ?></a></li>
<?php } ?>
				<li><a href="#status"><?php echo TEXT_ORDER_STATUS; ?></a></li>
			</ul>

        <div id="summary">

          <table border="0">

          <tr>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><b><?php echo ENTRY_CUSTOMER; ?></b></td>
                <td class="main"><?php echo vam_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'); ?></td>
              </tr>
            </table></td>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><b><?php echo ENTRY_SHIPPING_ADDRESS; ?></b></td>
                <td class="main"><?php echo vam_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'); ?></td>
              </tr>
            </table></td>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><b><?php echo ENTRY_BILLING_ADDRESS; ?></b></td>
                <td class="main"><?php echo vam_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'); ?></td>
              </tr>
            </table></td>
          </tr>
          
          <tr>
          <td colspan="3">
          <table width="100%" border="0" cellspacing="0" cellpadding="0">

              <tr>
                <td class="main"><b><?php echo TABLE_HEADING_DATE_PURCHASED; ?>:</b></td>
                <td class="main"><?php echo vam_date_long($order->info['date_purchased']); ?></td>
              </tr>
	          <tr>
	            <td class="main"><b><?php echo ENTRY_ORDER_NUMBER; ?></b></td>
	            <td class="main"><?php echo $oID; ?></td>
	          </tr>
                    
          <tr>
            <?php if ($order->customer['csID']!='') { ?>
                <tr>
                <td class="main" valign="top" bgcolor="#FFCC33"><b><?php echo ENTRY_CID; ?></b></td>
                <td class="main" bgcolor="#FFCC33"><?php echo $order->customer['csID']; ?></td>
              </tr>
            <?php } ?>
              <tr>
                <td class="main" valign="top"><b><?php echo CUSTOMERS_MEMO; ?></b></td>
<?php

	// memoquery
	$memo_query = vam_db_query("SELECT count(*) as count FROM ".TABLE_CUSTOMERS_MEMO." where customers_id='".$order->customer['ID']."'");
	$memo_count = vam_db_fetch_array($memo_query);
?>
                <td class="main"><b><?php echo $memo_count['count'].'</b>'; ?>  <a style="cursor:hand" onClick="javascript:window.open('<?php echo vam_href_link(FILENAME_POPUP_MEMO,'ID='.$order->customer['ID']); ?>', 'popup', 'scrollbars=yes, width=500, height=500')">(<?php echo DISPLAY_MEMOS; ?>)</a></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_TELEPHONE; ?></b></td>
                <td class="main"><?php echo $order->customer['telephone']; ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_EMAIL_ADDRESS; ?></b></td>
                <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_CUSTOMERS_VAT_ID; ?></b></td>
                <td class="main"><?php echo $order->customer['vat_id']; ?></td>
              </tr>
              <tr>
                <td class="main" valign="top"><b><?php echo IP; ?></b></td>
                <td class="main"><b><?php echo $order->customer['cIP']; ?></b></td>
              </tr>
	          <tr>
	            <td class="main"><b><?php echo ENTRY_ORIGINAL_REFERER; ?></b></td>
	            <td class="main"><?php echo $order->customer['orig_reference']; ?></td>
	          </tr>

              <?php echo vam_get_extra_fields_order($order->customer['ID'],$_SESSION['languages_id']) ?>

             </table>
             </td>
             </tr>

</table>

</div>

        <div id="payment">

          <table border="0">

      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
        <tr>
            <td class="main"><b><?php echo ENTRY_LANGUAGE; ?></b></td>
            <td class="main"><?php echo $order->info['language']; ?></td>
          </tr>
          <tr>
            <td class="main"><b><?php echo ENTRY_PAYMENT_METHOD; ?></b></td>
            <td class="main"><?php echo $order_payment_text; ?></td>
          </tr>
<?php if ($order->info['shipping_class'] != '') { ?>
          <tr>
            <td class="main"><b><?php echo ENTRY_SHIPPING_METHOD; ?></b></td>
            <td class="main"><?php echo $order_shipping_text; ?></td>
          </tr>
<?php } ?>
<?php

	if ((($order->info['cc_type']) || ($order->info['cc_owner']) || ($order->info['cc_number']))) {
?>
          <tr>
            <td colspan="2"><?php echo vam_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
            <td class="main"><?php echo $order->info['cc_type']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
            <td class="main"><?php echo $order->info['cc_owner']; ?></td>
          </tr>
<?php

		// BMC CC Mod Start
		if ($order->info['cc_number'] != '0000000000000000') {
			if (strtolower(CC_ENC) == 'true') {
				$cipher_data = $order->info['cc_number'];
				$order->info['cc_number'] = changedataout($cipher_data, CC_KEYCHAIN);
			}
		}
		// BMC CC Mod End
?>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
            <td class="main"><?php echo $order->info['cc_number']; ?></td>
          </tr>
          <tr>
          <td class="main"><?php echo ENTRY_CREDIT_CARD_CVV; ?></td>
          <td class="main"><?php echo $order->info['cc_cvv']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
            <td class="main"><?php echo $order->info['cc_expires']; ?></td>
          </tr>
<?php

	}

	// begin modification for banktransfer
	$banktransfer_query = vam_db_query("select banktransfer_prz, banktransfer_status, banktransfer_owner, banktransfer_number, banktransfer_bankname, banktransfer_blz, banktransfer_fax from " . TABLE_BANKTRANSFER . " where orders_id = '".vam_db_input($_GET['oID'])."'");
	$banktransfer = vam_db_fetch_array($banktransfer_query);
	if (($banktransfer['banktransfer_bankname']) || ($banktransfer['banktransfer_blz']) || ($banktransfer['banktransfer_number'])) {
?>
          <tr>
            <td colspan="2"><?php echo vam_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANK_NAME; ?></td>
            <td class="main"><?php echo $banktransfer['banktransfer_bankname']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANK_BLZ; ?></td>
            <td class="main"><?php echo $banktransfer['banktransfer_blz']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANK_NUMBER; ?></td>
            <td class="main"><?php echo $banktransfer['banktransfer_number']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANK_OWNER; ?></td>
            <td class="main"><?php echo $banktransfer['banktransfer_owner']; ?></td>
          </tr>
<?php

		if ($banktransfer['banktransfer_status'] == 0) {
?>
          <tr>
            <td class="main"><?php echo TEXT_BANK_STATUS; ?></td>
            <td class="main"><?php echo "OK"; ?></td>
          </tr>
<?php

		} else {
?>
          <tr>
            <td class="main"><?php echo TEXT_BANK_STATUS; ?></td>
            <td class="main"><?php echo $banktransfer['banktransfer_status']; ?></td>
          </tr>
<?php

			switch ($banktransfer['banktransfer_status']) {
				case 1 :
					$error_val = TEXT_BANK_ERROR_1;
					break;
				case 2 :
					$error_val = TEXT_BANK_ERROR_2;
					break;
				case 3 :
					$error_val = TEXT_BANK_ERROR_3;
					break;
				case 4 :
					$error_val = TEXT_BANK_ERROR_4;
					break;
				case 5 :
					$error_val = TEXT_BANK_ERROR_5;
					break;
				case 8 :
					$error_val = TEXT_BANK_ERROR_8;
					break;
				case 9 :
					$error_val = TEXT_BANK_ERROR_9;
					break;
			}
?>
          <tr>
            <td class="main"><?php echo TEXT_BANK_ERRORCODE; ?></td>
            <td class="main"><?php echo $error_val; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANK_PRZ; ?></td>
            <td class="main"><?php echo $banktransfer['banktransfer_prz']; ?></td>
          </tr>
<?php

		}
	}
	if ($banktransfer['banktransfer_fax']) {
?>
          <tr>
            <td class="main"><?php echo TEXT_BANK_FAX; ?></td>
            <td class="main"><?php echo $banktransfer['banktransfer_fax']; ?></td>
          </tr>
<?php

	}
	// end modification for banktransfer
?>
        </table></td>
      </tr>
      <tr>
        <td><?php echo vam_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>

</table>

</div>

        <div id="products">

          <table border="0" width="100%">

      <tr>
        <td><table border="0" width="100%" cellspacing="2" cellpadding="0" class="contentListingTable">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
<?php

	if ($order->products[0]['allow_tax'] == 1) {
?>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
<?php

	}
?>
            <td class="dataTableHeadingContent" align="right"><?php

	echo TABLE_HEADING_TOTAL_INCLUDING_TAX;
	if ($order->products[$i]['allow_tax'] == 1) {
		echo ' (excl.)';
	}
?></td>
          </tr>
<?php

	for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {

				$products_id_order=$order->products[$i]['id'];
				
				echo '          <tr class="dataTableRow">'."\n".'            <td class="dataTableContent" valign="top" align="right">'.$order->products[$i]['qty'].'&nbsp;x&nbsp;</td>'."\n".'            <td class="dataTableContent" valign="top"><a href="'.vam_href_link(FILENAME_CATEGORIES, 'pID='.$products_id_order.'&action=new_product').'">'.$order->products[$i]['name'].'</a>';

    if (sizeof($order->products[$i]['attributes']) > 0) {
        echo '<br /><small>';
        for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j ++) {
            echo '&nbsp;<i> - '.$order->products[$i]['attributes'][$j]['option'].': '.$order->products[$i]['attributes'][$j]['value'].';</i><br />';
        }
       
        echo '</small>';
    }

		echo '            </td>'."\n".'            <td class="dataTableContent" valign="top">';

		if ($order->products[$i]['model'] != '') {
			echo $order->products[$i]['model'];
		} else {
			echo '<br />';
		}

		// attribute models
		if (sizeof($order->products[$i]['attributes']) > 0) {
			for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j ++) {

				$model = vam_get_attributes_model($order->products[$i]['id'], $order->products[$i]['attributes'][$j]['value'],$order->products[$i]['attributes'][$j]['option']);
				if ($model != '') {
					echo $model.'<br />';
				} else {
					echo '<br />';
				}
			}
		}

		echo '&nbsp;</td>'."\n".'            <td class="dataTableContent" align="right" valign="top">'.format_price($order->products[$i]['final_price'] / $order->products[$i]['qty'], 1, $order->info['currency'], $order->products[$i]['allow_tax'], $order->products[$i]['tax']).'</td>'."\n";

		if ($order->products[$i]['allow_tax'] == 1) {
			echo '<td class="dataTableContent" align="right" valign="top">';
			echo vam_display_tax_value($order->products[$i]['tax']).'%';
			echo '</td>'."\n";
			echo '<td class="dataTableContent" align="right" valign="top"><b>';

			echo format_price($order->products[$i]['final_price'] / $order->products[$i]['qty'], 1, $order->info['currency'], 0, 0);

			echo '</b></td>'."\n";
		}
		echo '            <td class="dataTableContent" align="right" valign="top"><b>'.format_price(($order->products[$i]['final_price']), 1, $order->info['currency'], 0, 0).'</b></td>'."\n";
		echo '          </tr>'."\n";
	}
?>
          <tr>
            <td align="right" colspan="10"><table border="0" cellspacing="0" cellpadding="2">
<?php

	for ($i = 0, $n = sizeof($order->totals); $i < $n; $i ++) {
		echo '              <tr>'."\n".'                <td align="right" class="smallText">'.$order->totals[$i]['title'].'</td>'."\n".'                <td align="right" class="smallText">'.$order->totals[$i]['text'].'</td>'."\n".'              </tr>'."\n";
	}
?>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo vam_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      
</table>
      
</div>

<?php if (ENABLE_MAP_TAB == 'true') { ?>

			<div id="map">
			
    			<?php
    			
    			$street_address = (!isset($order->delivery["street_address"])) ? null : $order->delivery["street_address"];
    			$city = (!isset($order->delivery["city"])) ? null : $order->delivery["city"] . ', ';
    			$postcode = (!isset($order->delivery["postcode"])) ? null : $order->delivery["postcode"] . ', ';
    			$state = (!isset($order->delivery["state"])) ? null : $order->delivery["state"] . ', ';
    			$country = (!isset($order->delivery["country"])) ? null : $order->delivery["country"] . ', ';
    			$ship_address = $postcode . $city . $street_address;
    			
    			?>

    <script type="text/javascript">

        // Флаг, обозачающий произошла ли ошибка при загрузке API
        var flagApiFault = 0;
			
        // Функция для обработки ошибок при загрузке API
        function apifault (err) {
            // Создание обработчика для события window.onLoad
            // Отображаем сообщение об ошибке в контейнере над картой
            window.onload = function () {
                var errorContainer = document.getElementById("error");
                errorContainer.innerHTML = "<?php echo MAP_API_KEY_ERROR; ?> \"" + err + "\"";
                errorContainer.style.display = "";
            }
            flagApiFault = 1;
        }
        
    </script>
    <script src="http://api-maps.yandex.ru/1.1/index.xml?key=<?php echo MAP_API_KEY; ?>&onerror=apifault" type="text/javascript"></script>
    <script type="text/javascript">

	$(document).ready(function(){
			$("#getmap").click(function() {
			
			
        if (!flagApiFault) {
        // Создает обработчик события window.onLoad
        YMaps.jQuery(function () {
            // Создает экземпляр карты и привязывает его к созданному контейнеру
            var map = new YMaps.Map(YMaps.jQuery("#YMapsID")[0]);

                    map.addControl(new YMaps.TypeControl());
                    map.addControl(new YMaps.ToolBar());
                    map.addControl(new YMaps.Zoom());
                    map.addControl(new YMaps.ScaleLine());
                    
            var geocoder = new YMaps.Geocoder("<?php echo $ship_address; ?>");
            
            map.addOverlay(geocoder);
				
            // По завершению геокодирования инициализируем карту первым результатом
            YMaps.Events.observe(geocoder, geocoder.Events.Load, function (geocoder) {
                if (geocoder.length()) {
                    map.setBounds(geocoder.get(0).getBounds());
                }
            });


            
        })
        }
        		})
        		
        	});
    </script>

    <div id="error" style="display:none"></div>
    <div id="YMapsID" style="width:100%;height:350px"></div>
    			
			</div>

<?php } ?>

        <div id="status">
      
          <table border="0" width="100%">

      <tr>
        <td class="main"><table border="0" width="100%" cellspacing="2" cellpadding="0" class="contentListingTable">
          <tr>
            <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_ADDED; ?></td>
            <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></td>
            <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
            <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_COMMENTS; ?></td>
          </tr>
<?php

	$orders_history_query = vam_db_query("select orders_status_id, date_added, customer_notified, comments from ".TABLE_ORDERS_STATUS_HISTORY." where orders_id = '".vam_db_input($oID)."' order by date_added");
	if (vam_db_num_rows($orders_history_query)) {

	$rows = 0;
			
		while ($orders_history = vam_db_fetch_array($orders_history_query)) {
			
	$rows++;

        if (($rows/2) == floor($rows/2)) {
          $class = "even";
        } else {
          $class = "odd";
        }				
			
			echo '          <tr>'."\n".'            <td class="dataTableContent-'.$class.'" align="center">'.vam_datetime_short($orders_history['date_added']).'</td>'."\n".'            <td class="dataTableContent-'.$class.'" align="center">';
			if ($orders_history['customer_notified'] == '1') {
				echo vam_image(DIR_WS_ICONS.'tick.gif', ICON_TICK)."</td>\n";
			} else {
				echo vam_image(DIR_WS_ICONS.'cross.gif', ICON_CROSS)."</td>\n";
			}
			echo '            <td class="dataTableContent-'.$class.'">';
			if($orders_history['orders_status_id']!='0') {
				echo $orders_status_array[$orders_history['orders_status_id']];
			}else{
				echo '<font color="#FF0000">'.TEXT_VALIDATING.'</font>';
			}
			echo '</td>'."\n".'            <td class="dataTableContent-'.$class.'">'.nl2br(vam_db_output($orders_history['comments'])).'&nbsp;</td>'."\n".'          </tr>'."\n";
		}
	} else {
		echo '          <tr>'."\n".'            <td class="smallText" colspan="5">'.TEXT_NO_ORDER_HISTORY.'</td>'."\n".'          </tr>'."\n";
	}
?>
        </table></td>
      </tr>
      <tr>
        <td class="main"><br /><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
      </tr>
      <tr>
        <td><?php echo vam_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr><?php echo vam_draw_form('status', FILENAME_ORDERS, vam_get_all_get_params(array('action')) . 'action=update_order'); ?>
        <td class="main"><?php echo vam_draw_textarea_field('comments', 'soft', '60', '5'); ?></td>
      </tr>
      <tr>
        <td><?php echo vam_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><b><?php echo ENTRY_STATUS; ?></b> <?php echo vam_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status']); ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_NOTIFY_CUSTOMER; ?></b> <?php echo vam_draw_checkbox_field('notify', '', true); ?></td>
                <td class="main"><b><?php echo ENTRY_NOTIFY_COMMENTS; ?></b> <?php echo vam_draw_checkbox_field('notify_comments', '', true); ?></td>
              </tr>
            </table></td>
            <td valign="top"><span class="button"><button type="submit" value="<?php echo BUTTON_UPDATE; ?>"><?php echo BUTTON_UPDATE; ?></button></span></td>
          </tr>
        </table></td>
      </form></tr>
      
</table>
      
</div>

</div>

<table width="100%" border="0" cellspacing="0" cellpadding="2">
      
      <tr>
        <td align="right">
        <br />
<?php
	if (ACTIVATE_GIFT_SYSTEM == 'true') {
		echo '<a class="button" href="'.vam_href_link(FILENAME_GV_MAIL, vam_get_all_get_params(array ('cID', 'action')).'cID='.$order->customer['ID']).'"><span>'.BUTTON_SEND_COUPON.'</span></a>';
	}
?>
   <a class="button" href="Javascript:void()" onclick="window.open('<?php echo vam_href_link(FILENAME_PRINT_ORDER,'oID='.$_GET['oID']); ?>', 'popup', 'toolbar=0, width=640, height=600')"><span><?php echo BUTTON_INVOICE; ?></span></a>
   <a class="button" href="Javascript:void()" onclick="window.open('<?php echo vam_href_link(FILENAME_PRINT_PACKINGSLIP,'oID='.$_GET['oID']); ?>', 'popup', 'toolbar=0, width=640, height=600')"><span><?php echo BUTTON_PACKINGSLIP; ?></span></a>
   <a class="button" href="<?php echo vam_href_link(FILENAME_ORDERS, 'page='.$_GET['page'].'&oID='.$_GET['oID']).'"><span>'.BUTTON_BACK;?></span></a>
       </td>
      </tr>

</table>
      
<?php

}
elseif ($_GET['action'] == 'custom_action') {

	include ('orders_actions.php');

} else {
?>
      <tr>
        <td width="100%">
        
          <table border="0" width="100%" cellspacing="0" cellpadding="0" class="pageHead">
        <tr>
         <td class="pageHeading" align="left">
         <h1 class="contentBoxHeading"><?php echo HEADING_TITLE; ?></h1>   
         </td>
         <td align="right">

             <?php echo vam_draw_form('status', FILENAME_ORDERS, '', 'get'); ?>
                <?php echo HEADING_TITLE_STATUS . ' ' . vam_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), $_GET['status'], 'onChange="this.form.submit();"').vam_draw_hidden_field(vam_session_name(), vam_session_id()); ?>
              </form>
              
         </td>
         <td align="right">
              <?php echo vam_draw_form('orders', FILENAME_ORDERS, '', 'get'); ?>
                <?php echo HEADING_TITLE_SEARCH . ' ' . vam_draw_input_field('oID', '', 'size="12"') . vam_draw_hidden_field('action', 'edit').vam_draw_hidden_field(vam_session_name(), vam_session_id()); ?>
              </form>
         </td>
       </tr>
       </table>

        
        </td>
      </tr>
      
      <tr>
        <td>
        
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading" align="left"><?php echo '<a class="button" href="' . vam_href_link(FILENAME_CREATE_ACCOUNT) . '"><span>' . BUTTON_CREATE_ACCOUNT . '</span></a>'; ?>&nbsp;<?php echo '<a class="button" href="' . vam_href_link(FILENAME_EXPORTORDERS) . '"><span>' . BUTTON_ORDERS_EXPORT . '</span></a>'; ?></td>
            <td class="smallText" align="right"></tr>
        </table></td>
      </tr>
      
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top">
<?php
echo vam_draw_form('multi_action_form', FILENAME_ORDERS,vam_get_all_get_params());
?>
           <table border="0" width="100%" cellspacing="2" cellpadding="0" class="contentListingTable">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><input type="checkbox" onClick="javascript:CheckAll(this.checked);"></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_NUMBER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <?php if (AFTERBUY_ACTIVATED=='true') { ?>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_AFTERBUY; ?></td>
                <?php } ?>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php

	if ($_GET['cID']) {
		$cID = vam_db_prepare_input($_GET['cID']);
		$orders_query_raw = "select o.orders_id, o.afterbuy_success, o.afterbuy_id, o.customers_name, o.customers_id, o.payment_method, o.shipping_method, o.shipping_class, o.date_purchased, o.last_modified, o.currency, o.currency_value, o.orders_status, s.orders_status_name, ot.text as order_total from ".TABLE_ORDERS." o left join ".TABLE_ORDERS_TOTAL." ot on (o.orders_id = ot.orders_id), ".TABLE_ORDERS_STATUS." s where o.customers_id = '".vam_db_input($cID)."' and (o.orders_status = s.orders_status_id and s.language_id = '".$_SESSION['languages_id']."' and ot.class = 'ot_total') or (o.orders_status = '0' and ot.class = 'ot_total' and  s.orders_status_id = '1' and s.language_id = '".$_SESSION['languages_id']."') order by orders_id DESC";
	}
	elseif ($_GET['status']=='0') {
			$orders_query_raw = "select o.orders_id, o.afterbuy_success, o.afterbuy_id, o.customers_name, o.payment_method, o.shipping_method, o.shipping_class, o.date_purchased, o.last_modified, o.currency, o.currency_value, o.orders_status, ot.text as order_total from ".TABLE_ORDERS." o left join ".TABLE_ORDERS_TOTAL." ot on (o.orders_id = ot.orders_id) where o.orders_status = '0' and ot.class = 'ot_total' order by o.orders_id DESC";
	}
	elseif ($_GET['status']) {
			$status = vam_db_prepare_input($_GET['status']);
			$orders_query_raw = "select o.orders_id, o.afterbuy_success, o.afterbuy_id, o.customers_name, o.payment_method, o.shipping_method, o.shipping_class, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from ".TABLE_ORDERS." o left join ".TABLE_ORDERS_TOTAL." ot on (o.orders_id = ot.orders_id), ".TABLE_ORDERS_STATUS." s where o.orders_status = s.orders_status_id and s.language_id = '".$_SESSION['languages_id']."' and s.orders_status_id = '".vam_db_input($status)."' and ot.class = 'ot_total' order by o.orders_id DESC";
	} else {
		$orders_query_raw = "select o.orders_id, o.orders_status, o.afterbuy_success, o.afterbuy_id, o.customers_name, o.payment_method, o.shipping_method, o.shipping_class, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from ".TABLE_ORDERS." o left join ".TABLE_ORDERS_TOTAL." ot on (o.orders_id = ot.orders_id), ".TABLE_ORDERS_STATUS." s where (o.orders_status = s.orders_status_id and s.language_id = '".$_SESSION['languages_id']."' and ot.class = 'ot_total') or (o.orders_status = '0' and ot.class = 'ot_total' and  s.orders_status_id = '1' and s.language_id = '".$_SESSION['languages_id']."') order by o.orders_id DESC";
	}
	$orders_split = new splitPageResults($_GET['page'], MAX_DISPLAY_ADMIN_PAGE, $orders_query_raw, $orders_query_numrows);
	$orders_query = vam_db_query($orders_query_raw);
	while ($orders = vam_db_fetch_array($orders_query)) {
		if (((!$_GET['oID']) || ($_GET['oID'] == $orders['orders_id'])) && (!$oInfo)) {
			$oInfo = new objectInfo($orders);
		}

        if ( (is_object($oInfo)) && ($orders['orders_id'] == $oInfo->orders_id) ) {
            echo '<tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'">' . "\n";
        } else {
            echo '<tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
        }

?>
                <td class="dataTableContent" align="center"><input type="checkbox" name="multi_orders[]" value="<?php echo $orders['orders_id'];?>"></td>
                <td class="dataTableContent"><?php echo '<a href="' . vam_href_link(FILENAME_ORDERS, vam_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders['orders_id'] . '&action=edit') . '">' . vam_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;<a href="' . vam_href_link(FILENAME_ORDERS, vam_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders['orders_id']) . '">' . $orders['customers_name'] . '</a>'; ?></td>
                <td class="dataTableContent" align="right"><?php echo $orders['orders_id']; ?></td>
                <td class="dataTableContent" align="right"><?php echo strip_tags($orders['order_total']); ?></td>
                <td class="dataTableContent" align="center"><?php echo vam_datetime_short($orders['date_purchased']); ?></td>
                <td class="dataTableContent" align="right"><?php if($orders['orders_status']!='0') { echo $orders['orders_status_name']; }else{ echo '<font color="#FF0000">'.TEXT_VALIDATING.'</font>';}?></td>
                <?php if (AFTERBUY_ACTIVATED=='true') { ?>
                <td class="dataTableContent" align="right"><?php

		if ($orders['afterbuy_success'] == 1) {
			echo $orders['afterbuy_id'];
		} else {
			echo 'TRANSMISSION_ERROR';
		}
?></td>
                <?php } ?>
                <td class="dataTableContent" align="right"><?php if ( (is_object($oInfo)) && ($orders['orders_id'] == $oInfo->orders_id) ) { echo vam_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . vam_href_link(FILENAME_ORDERS, vam_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '">' . vam_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php

	}
?>
<?php
echo '<tr class="dataTableContent"><td colspan="7">' . BUS_HEADING_TITLE . ': ' . vam_draw_pull_down_menu('new_status', array_merge(array(array('id' => '', 'text' => BUS_TEXT_NEW_STATUS)), $orders_statuses), '', '') . vam_draw_checkbox_field('notify','1',true) . ' ' . BUS_NOTIFY_CUSTOMERS . '</td></tr>';
echo '<tr class="dataTableContent" align="left"><td colspan="7" nobr="nobr" align="left">' .
BUS_DELETE_ORDERS . ': ' . vam_draw_checkbox_field('delete_orders','1') . '</td></tr>';
echo '<tr class="dataTableContent" align="center"><td colspan="7" nobr="nobr" align="left">' .
     '<a class="button" href="javascript:SwitchCheck()"><span>' . BUTTON_REVERSE_SELECTION . '</span></a>&nbsp;<span class="button"><button type="submit" name="submit" value="' . BUTTON_SUBMIT . '">' . BUTTON_SUBMIT . '</button></span></td></tr>';
?>
</form>
              <tr>
                <td colspan="7"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_ADMIN_PAGE, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                    <td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_ADMIN_PAGE, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], vam_get_all_get_params(array('page', 'oID', 'action'))); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php

	$heading = array ();
	$contents = array ();
	switch ($_GET['action']) {
		case 'delete' :
			$heading[] = array ('text' => '<b>'.TEXT_INFO_HEADING_DELETE_ORDER.'</b>');

			$contents = array ('form' => vam_draw_form('orders', FILENAME_ORDERS, vam_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=deleteconfirm'));
			$contents[] = array ('text' => TEXT_INFO_DELETE_INTRO.'<br /><br /><b>'.$cInfo->customers_firstname.' '.$cInfo->customers_lastname.'</b>');
			$contents[] = array ('text' => '<br />'.vam_draw_checkbox_field('restock').' '.TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
			$contents[] = array ('align' => 'center', 'text' => '<br /><span class="button"><button type="submit" value="'. BUTTON_DELETE .'">' . BUTTON_DELETE . '</button></span><a class="button" href="'.vam_href_link(FILENAME_ORDERS, vam_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id).'"><span>' . BUTTON_CANCEL . '</span></a>');
			break;
		default :
			if (is_object($oInfo)) {
				$heading[] = array ('text' => '<b>['.$oInfo->orders_id.']&nbsp;&nbsp;'.vam_datetime_short($oInfo->date_purchased).'</b>');

				$contents[] = array ('align' => 'center', 'text' => '<a class="button" href="'.vam_href_link(FILENAME_ORDERS, vam_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=edit').'"><span>'.BUTTON_EDIT.'</span></a> <a class="button" href="'.vam_href_link(FILENAME_ORDERS, vam_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=delete').'"><span>'.BUTTON_DELETE.'</span></a>&nbsp;<a class="button" href="'.vam_href_link(FILENAME_PRINT_ORDER,'oID='.$oInfo->orders_id).'" target="_blank"><span>'.BUTTON_INVOICE.'</span></a>&nbsp;<a class="button" href="'.vam_href_link(FILENAME_PRINT_PACKINGSLIP,'oID='.$oInfo->orders_id).'" target="_blank"><span>'.BUTTON_PACKINGSLIP.'</span></a>');
				if (AFTERBUY_ACTIVATED == 'true') {
					$contents[] = array ('align' => 'center', 'text' => '<a class="button" href="'.vam_href_link(FILENAME_ORDERS, vam_get_all_get_params(array ('oID', 'action')).'oID='.$oInfo->orders_id.'&action=afterbuy_send').'"><span>'.BUTTON_AFTERBUY_SEND.'</span></a>');

				}
				//$contents[] = array('align' => 'center', 'text' => '');

  $order_payment = $oInfo->payment_method;
  
  require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $order_payment .'.php');
  $order_payment_text = constant(MODULE_PAYMENT_.strtoupper($order_payment)._TEXT_TITLE);

				$contents[] = array ('text' => '<br />'.TEXT_DATE_ORDER_CREATED.' '.vam_date_short($oInfo->date_purchased));
				if (vam_not_null($oInfo->last_modified))
					$contents[] = array ('text' => TEXT_DATE_ORDER_LAST_MODIFIED.' '.vam_date_short($oInfo->last_modified));
				$contents[] = array ('text' => '<br />'.TEXT_INFO_PAYMENT_METHOD.' '.$order_payment_text);
				$contents[] = array ('text' => '<br />'.ENTRY_SHIPPING_METHOD.' '.$oInfo->shipping_method);
				// elari added to display product list for selected order
				$order = new order($oInfo->orders_id);
				$contents[] = array ('text' => $order->customer['email_address']);
				$contents[] = array ('text' => $order->customer['telephone']);
				$contents[] = array ('text' => vam_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'));
				$contents[] = array ('text' => '<br />'.sizeof($order->products).TEXT_PRODUCTS);
				for ($i = 0; $i < sizeof($order->products); $i ++) {

					$products_id_order=$order->products[$i]['id'];
					
					$rest_order_query = vam_db_query("SELECT products_quantity FROM products WHERE products_id = '".$products_id_order."'");
					$rest_order = vam_db_fetch_array($rest_order_query);
					$rest_order_quantity=$rest_order['products_quantity'];
					
					$contents[] = array ('text' => $order->products[$i]['qty'].'&nbsp;x&nbsp;<a href="'.vam_href_link(FILENAME_CATEGORIES, 'pID='.$products_id_order.'&action=new_product').'">'.$order->products[$i]['name'].' ('.$order->products[$i]['model'].') ('.TEXT_QTY.$rest_order_quantity.TEXT_UNITS.')</a>');

					if (sizeof($order->products[$i]['attributes']) > 0) {
						for ($j = 0; $j < sizeof($order->products[$i]['attributes']); $j ++) {
							$contents[] = array ('text' => '<small>&nbsp;<i> - '.$order->products[$i]['attributes'][$j]['option'].': '.$order->products[$i]['attributes'][$j]['value'].'</i></small></nobr>');
						}
					}
				}
				// elari End add display products
			}
			break;
	}

	if ((vam_not_null($heading)) && (vam_not_null($contents))) {
		echo '            <td width="25%" valign="top">'."\n";

		$box = new box;
		echo $box->infoBox($heading, $contents);

		echo '            </td>'."\n";
	}
?>
          </tr>
        </table></td>
      </tr>
<?php

}
?>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php

require (DIR_WS_INCLUDES.'footer.php');
?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>