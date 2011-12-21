<?php
/* -----------------------------------------------------------------------------------------
   $Id: ipayment.php 998 2007-02-06 21:07:20 VaM $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ipayment.php,v 1.32 2003/01/29); www.oscommerce.com 
   (c) 2003	 nextcommerce (ipayment.php,v 1.9 2003/08/24); www.nextcommerce.org
   (c) 2004	 xt:Commerce (ipayment.php,v 1.9 2003/08/23); xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class ipayment {
	var $code, $title, $description, $enabled;

	function ipayment() {
		global $order;

		$this->code = 'ipayment';
		$this->title = MODULE_PAYMENT_IPAYMENT_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_IPAYMENT_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_PAYMENT_IPAYMENT_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_IPAYMENT_STATUS == 'True') ? true : false);
		$this->info = MODULE_PAYMENT_IPAYMENT_TEXT_INFO;
		if ((int) MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID;
		}

		if (is_object($order))
			$this->update_status();

		$this->form_action_url = 'https://ipayment.de/merchant/'.MODULE_PAYMENT_IPAYMENT_ID.'/processor.php';
	}

	function update_status() {
		global $order;

		if (($this->enabled == true) && ((int) MODULE_PAYMENT_IPAYMENT_ZONE > 0)) {
			$check_flag = false;
			$check_query = vam_db_query("select zone_id from ".TABLE_ZONES_TO_GEO_ZONES." where geo_zone_id = '".MODULE_PAYMENT_IPAYMENT_ZONE."' and zone_country_id = '".$order->billing['country']['id']."' order by zone_id");
			while ($check = vam_db_fetch_array($check_query)) {
				if ($check['zone_id'] < 1) {
					$check_flag = true;
					break;
				}
				elseif ($check['zone_id'] == $order->billing['zone_id']) {
					$check_flag = true;
					break;
				}
			}

			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}

	function javascript_validation() {
		$js = '  if (payment_value == "'.$this->code.'") {'."\n".'    var cc_owner = document.getElementById("checkout_payment").ipayment_cc_owner.value;'."\n".'    var cc_number = document.getElementById("checkout_payment").ipayment_cc_number.value;'."\n".'    if (cc_owner == "" || cc_owner.length < '.CC_OWNER_MIN_LENGTH.') {'."\n".'      error_message = error_message + "'.MODULE_PAYMENT_IPAYMENT_TEXT_JS_CC_OWNER.'";'."\n".'      error = 1;'."\n".'    }'."\n".'    if (cc_number == "" || cc_number.length < '.CC_NUMBER_MIN_LENGTH.') {'."\n".'      error_message = error_message + "'.MODULE_PAYMENT_IPAYMENT_TEXT_JS_CC_NUMBER.'";'."\n".'      error = 1;'."\n".'    }'."\n".'  }'."\n";

		return $js;
	}

	function selection() {
		global $order;

		for ($i = 1; $i < 13; $i ++) {
			$expires_month[] = array ('id' => sprintf('%02d', $i), 'text' => strftime('%B', mktime(0, 0, 0, $i, 1, 2000)));
		}

		$today = getdate();
		for ($i = $today['year']; $i < $today['year'] + 10; $i ++) {
			$expires_year[] = array ('id' => strftime('%y', mktime(0, 0, 0, 1, 1, $i)), 'text' => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)));
		}

		$selection = array ('id' => $this->code, 'module' => $this->title, 'description' => $this->info, 'fields' => array (array ('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_OWNER, 'field' => vam_draw_input_field('ipayment_cc_owner', $order->billing['firstname'].' '.$order->billing['lastname'])), array ('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_NUMBER, 'field' => vam_draw_input_field('ipayment_cc_number')), array ('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_EXPIRES, 'field' => vam_draw_pull_down_menu('ipayment_cc_expires_month', $expires_month).'&nbsp;'.vam_draw_pull_down_menu('ipayment_cc_expires_year', $expires_year)), array ('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_CHECKNUMBER, 'field' => vam_draw_input_field('ipayment_cc_checkcode', '', 'size="4" maxlength="3"').'&nbsp;<small>'.MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_CHECKNUMBER_LOCATION.'</small>')));

		return $selection;
	}

	function pre_confirmation_check() {
		return false;
	}

	function confirmation() {

		$confirmation = array ('title' => $this->title.': '.$this->cc_card_type,
							'fields' => array (array ('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_OWNER,
												 'field' => $_POST['ipayment_cc_owner']),
												  array ('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_NUMBER,
												   'field' => substr($_POST['ipayment_cc_number'], 0, 4).str_repeat('X', (strlen($_POST['ipayment_cc_number']) - 8)).substr($_POST['ipayment_cc_number'], -4)),
												    array ('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_EXPIRES,
												     'field' => strftime('%B, %Y', mktime(0, 0, 0, $_POST['ipayment_cc_expires_month'], 1, '20'.$_POST['ipayment_cc_expires_year'])))));

		if (vam_not_null($_POST['ipayment_cc_checkcode'])) {
			$confirmation['fields'][] = array ('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_CHECKNUMBER, 'field' => $_POST['ipayment_cc_checkcode']);
		}

		return $confirmation;
	}

	function process_button() {
		global $order, $vamPrice;

		switch (MODULE_PAYMENT_IPAYMENT_CURRENCY) {
			case 'Always EUR' :
				$trx_currency = 'EUR';
				break;
			case 'Always USD' :
				$trx_currency = 'USD';
				break;
			case 'Either EUR or USD, else EUR' :
				if (($_SESSION['currency'] == 'EUR') || ($_SESSION['currency'] == 'USD')) {
					$trx_currency = $_SESSION['currency'];
				} else {
					$trx_currency = 'EUR';
				}
				break;
			case 'Either EUR or USD, else USD' :
				if (($_SESSION['currency'] == 'EUR') || ($_SESSION['currency'] == 'USD')) {
					$trx_currency = $_SESSION['currency'];
				} else {
					$trx_currency = 'USD';
				}
				break;
		}
		if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
			$total = $order->info['total'] + $order->info['tax'];
		} else {
			$total = $order->info['total'];
		}
		if ($_SESSION['currency'] == $trx_currency) {
			$amount = round($total, $vamPrice->get_decimal_places($trx_currency));
		} else {
			$amount = round($vamPrice->CalculateCurrEx($total, $trx_currency), $vamPrice->get_decimal_places($trx_currency));
		}
		$process_button_string = vam_draw_hidden_field('silent', '1').vam_draw_hidden_field('trx_paymenttyp', 'cc').vam_draw_hidden_field('trxuser_id', MODULE_PAYMENT_IPAYMENT_USER_ID).vam_draw_hidden_field('trxpassword', MODULE_PAYMENT_IPAYMENT_PASSWORD).vam_draw_hidden_field('item_name', STORE_NAME).vam_draw_hidden_field('trx_currency', $trx_currency).vam_draw_hidden_field('trx_amount', round($amount * 100, 0)).vam_draw_hidden_field('cc_expdate_month', $_POST['ipayment_cc_expires_month']).vam_draw_hidden_field('cc_expdate_year', $_POST['ipayment_cc_expires_year']).vam_draw_hidden_field('cc_number', $_POST['ipayment_cc_number']).vam_draw_hidden_field('cc_checkcode', $_POST['ipayment_cc_checkcode']).vam_draw_hidden_field('addr_name', $_POST['ipayment_cc_owner']).vam_draw_hidden_field('addr_email', $order->customer['email_address']).vam_draw_hidden_field('redirect_url', vam_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', true)).vam_draw_hidden_field('silent_error_url', vam_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code.'&ipayment_cc_owner='.urlencode($_POST['ipayment_cc_owner']), 'SSL', true));

		return $process_button_string;
	}

	function before_process() {
		return false;
	}

	function after_process() {
		global $insert_id;
		if ($this->order_status)
			vam_db_query("UPDATE ".TABLE_ORDERS." SET orders_status='".$this->order_status."' WHERE orders_id='".$insert_id."'");

	}

	function get_error() {

		$error = array ('title' => IPAYMENT_ERROR_HEADING, 'error' => ((isset ($_GET['error'])) ? stripslashes(urldecode($_GET['error'])) : IPAYMENT_ERROR_MESSAGE));

		return $error;
	}

	function check() {
		if (!isset ($this->_check)) {
			$check_query = vam_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_IPAYMENT_STATUS'");
			$this->_check = vam_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function install() {
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_IPAYMENT_STATUS', 'True', '6', '1', 'vam_cfg_select_option(array(\'True\', \'False\'), ', now())");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_IPAYMENT_ALLOWED', '', '6', '0', now())");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_IPAYMENT_ID', '99999', '6', '2', now())");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_IPAYMENT_USER_ID', '99999', '6', '3', now())");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_IPAYMENT_PASSWORD', '0', '6', '4', now())");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_IPAYMENT_CURRENCY', 'Either EUR or USD, else EUR','6', '5', 'vam_cfg_select_option(array(\'Always EUR\', \'Always USD\', \'Either EUR or USD, else EUR\', \'Either EUR or USD, else USD\'), ', now())");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_IPAYMENT_SORT_ORDER', '0', '6', '0', now())");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_IPAYMENT_ZONE', '0', '6', '2', 'vam_get_zone_class_title', 'vam_cfg_pull_down_zone_classes(', now())");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID', '0','6', '0', 'vam_cfg_pull_down_order_statuses(', 'vam_get_order_status_name', now())");
	}

	function remove() {
		vam_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '", $this->keys())."')");
	}

	function keys() {
		return array ('MODULE_PAYMENT_IPAYMENT_STATUS', 'MODULE_PAYMENT_IPAYMENT_ALLOWED', 'MODULE_PAYMENT_IPAYMENT_ID', 'MODULE_PAYMENT_IPAYMENT_USER_ID', 'MODULE_PAYMENT_IPAYMENT_PASSWORD', 'MODULE_PAYMENT_IPAYMENT_CURRENCY', 'MODULE_PAYMENT_IPAYMENT_ZONE', 'MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID', 'MODULE_PAYMENT_IPAYMENT_SORT_ORDER');
	}
}
?>