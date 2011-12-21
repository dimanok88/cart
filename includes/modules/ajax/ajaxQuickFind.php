<?php
/* -----------------------------------------------------------------------------------------
   $Id: ajaxQuickFind.php 1243 2009-02-06 20:41:56 VaM $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2006	 Andrew Berezin (ajaxQuickFind.php,v 1.9 2003/08/17); zen-cart.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

	define("AJAX_QUICKSEARCH_RESULT", 'text'); // dropdown or text
	define("AJAX_QUICKSEARCH_DROPDOWN_SIZE", 5);
	define("AJAX_QUICKSEARCH_LIMIT", 15);

	$q = addslashes(preg_replace("%[^0-9a-zA-Zа-яА-Я\s]%iu", "", $_REQUEST['keywords']) );

	$out = "";
	if(isset($q) && vam_not_null($q)) {

		$searchwords = explode(" ",$q);
		$nosearchwords = sizeof($searchwords);
		foreach($searchwords as $key => $value) {
			if ($value == '')
				unset($searchwords[$key]);
		}
		$searchwords = array_values($searchwords);
		$nosearchwords = sizeof($searchwords);
		foreach($searchwords as $key => $value) {
			$booltje = '+' . $searchwords[$key] . '*';
			$searchwords[$key] = $booltje;
		}
		$q = implode(" ",$searchwords);

		$products_query = vam_db_query("select pd.products_id, pd.products_name, pd.products_keywords, p.products_model
							from " . TABLE_PRODUCTS_DESCRIPTION . " pd
							inner join " . TABLE_PRODUCTS . " p
							on (p.products_id = pd.products_id)
							where (match (pd.products_name) against ('" . $q . "' in boolean mode)
							or match (p.products_model) against ('" . $q . "' in boolean mode) or match (pd.products_keywords) against ('" . $q . "' in boolean mode)" .
							($_REQUEST['search_in_description'] == '1' ? "or match (pd.products_description) against ('" . $q . "' in boolean mode)" : "") . ")
							and p.products_status = '1'
							and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
							order by pd.products_name asc
							limit " . AJAX_QUICKSEARCH_LIMIT);

		if(vam_db_num_rows($products_query)) {
			$out .= sprintf(TEXT_AJAX_QUICKSEARCH_TOP, AJAX_QUICKSEARCH_LIMIT) . '<br />';
			$dropdown = array();
			$out .= '<ul class="ajaxQuickFind">';
			while($products = vam_db_fetch_array($products_query)) {
				$out .= '<li class="ajaxQuickFind"><a href="' . vam_href_link(FILENAME_PRODUCT_INFO, vam_product_link($products['products_id'], $products['products_name']), 'NONSSL', false) . '">' . $products['products_name'] . '</a></li>' . "\n";
				$dropdown[] = array('id' => $products['products_id'],
														'text' => $products['products_name']);
			}
			$out .= '</ul>' . "\n";
			if(AJAX_QUICKSEARCH_RESULT == 'dropdown') {
				$out .= vam_draw_pull_down_menu('AJAX_QUICKSEARCH_pid', $dropdown, '', 'onChange="this.form.submit();" size="' . AJAX_QUICKSEARCH_DROPDOWN_SIZE . '" class="ajaxQuickFind"') . vam_hide_session_id();
			}
		}
	}
	echo $out;
?>