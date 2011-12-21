<?php
/* -----------------------------------------------------------------------------------------
   $Id: cash.php 1102 2007-02-06 21:07:20 VaM $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(moneyorder.php,v 1.10 2003/01/29); www.oscommerce.com
   (c) 2003	 nextcommerce (moneyorder.php,v 1.7 2003/08/24); www.nextcommerce.org
   (c) 2004	 xt:Commerce (moneyorder.php,v 1.7 2003/08/23); xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class cash {
	var $code, $title, $description, $enabled;

	function cash() {
		global $order;

		$this->code = 'cash';
		$this->title = MODULE_PAYMENT_CASH_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_CASH_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_PAYMENT_CASH_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_CASH_STATUS == 'True') ? true : false);
		$this->info = MODULE_PAYMENT_CASH_TEXT_INFO;
		if ((int) MODULE_PAYMENT_CASH_ORDER_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_CASH_ORDER_STATUS_ID;
		}

		if (is_object($order))
			$this->update_status();

		$this->email_footer = MODULE_PAYMENT_CASH_TEXT_EMAIL_FOOTER;
	}

	function update_status() {
		global $order;

		if ($_SESSION['shipping']['id'] != 'selfpickup_selfpickup') {
			$this->enabled = false;
		}

		if (($this->enabled == true) && ((int) MODULE_PAYMENT_CASH_ZONE > 0)) {
			$check_flag = false;
			$check_query = vam_db_query("select zone_id from ".TABLE_ZONES_TO_GEO_ZONES." where geo_zone_id = '".MODULE_PAYMENT_CASH_ZONE."' and zone_country_id = '".$order->billing['country']['id']."' order by zone_id");
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
		return false;
	}

	function selection() {
		return array ('id' => $this->code, 'module' => $this->title, 'description' => $this->info);
	}

	function pre_confirmation_check() {
		return false;
	}

	function confirmation() {
		return array ('title' => MODULE_PAYMENT_CASH_TEXT_DESCRIPTION);
	}

	function process_button() {
		return false;
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
		return false;
	}

	function check() {
		if (!isset ($this->_check)) {
			$check_query = vam_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key = 'MODULE_PAYMENT_CASH_STATUS'");
			$this->_check = vam_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function install() {
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PAYMENT_CASH_STATUS', 'True', '6', '1', 'vam_cfg_select_option(array(\'True\', \'False\'), ', now());");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_CASH_ALLOWED', '',   '6', '0', now())");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, date_added) values ('MODULE_PAYMENT_CASH_SORT_ORDER', '0', '6', '0', now())");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_PAYMENT_CASH_ZONE', '0',  '6', '2', 'vam_get_zone_class_title', 'vam_cfg_pull_down_zone_classes(', now())");
		vam_db_query("insert into ".TABLE_CONFIGURATION." ( configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) values ('MODULE_PAYMENT_CASH_ORDER_STATUS_ID', '0', '6', '0', 'vam_cfg_pull_down_order_statuses(', 'vam_get_order_status_name', now())");
	}

	function remove() {
		vam_db_query("delete from ".TABLE_CONFIGURATION." where configuration_key in ('".implode("', '", $this->keys())."')");
	}

	function keys() {
		return array ('MODULE_PAYMENT_CASH_STATUS', 'MODULE_PAYMENT_CASH_ALLOWED', 'MODULE_PAYMENT_CASH_ZONE', 'MODULE_PAYMENT_CASH_ORDER_STATUS_ID', 'MODULE_PAYMENT_CASH_SORT_ORDER');
	}
}
?>