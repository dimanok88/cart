<?php
/*
  $Id: ask_a_question.php,v 1.42 2003/06/11 17:35:01 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
  Credits: Marg Davison, Loпc Richard, FaNaTiC, C. Bouwmeester
  Anpassungen fьr XT:Commerce 3.0.4 SP1: 2005/2006 BSB Beratung+Software Bleicher
  ASK_A_QUESTION.GIF Grafikdesign (c) 2005/2005 BSB Beratung+Software Bleicher

*/
include ('includes/application_top.php');

// include needed functions
require_once(DIR_FS_INC.'vam_validate_email.inc.php');
require_once (DIR_FS_INC.'vam_image_button.inc.php');
require_once (DIR_FS_INC.'vam_random_charcode.inc.php');
require_once (DIR_FS_INC.'vam_render_vvcode.inc.php');

// create smarty elements
$vamTemplate = new vamTemplate;

$vamTemplate->assign('language', $_SESSION['language']);

if (isset ($_POST['action']) && ($_POST['action'] == 'process')) {

include ('includes/header.php');

$product_info_query = vam_db_query("select * FROM ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd where p.products_status = '1' and p.products_id = '".(int)$_POST['products_id']."' and pd.products_id = p.products_id and pd.language_id = '".(int)$_SESSION['languages_id']."'");
$product_info = vam_db_fetch_array($product_info_query);

	$error = false;

	if (isset($_SESSION['customer_id'])) { 
		$firstname = $_SESSION['customer_first_name'];
		$lastname = $_SESSION['customer_last_name'];
		$email_address =$_SESSION['customer_email_address'];
		$message = vam_db_input($_POST['message_body']);
		$to_email_address = $email_address;
		$to_name = $firstname .' '. $lastname;
  } else {    
		$firstname = vam_db_input($_POST['firstname']);
		$lastname = vam_db_input($_POST['lastname']);
		$email_address = vam_db_input($_POST['email_address']);
		$message = vam_db_input($_POST['message_body']);
		$to_email_address = $email_address;
		$to_name = $firstname .' '. $lastname;
	}
	
	if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
		$error = true;
		$messageStack->add('ask_a_question', ENTRY_FIRST_NAME_ERROR);
	}

	if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
		$error = true;
		$messageStack->add('ask_a_question', ENTRY_LAST_NAME_ERROR);
	}

	if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
		$error = true;
		$messageStack->add('ask_a_question', ENTRY_EMAIL_ADDRESS_ERROR);
	}
	elseif (vam_validate_email($email_address) == false) {
		$error = true;
		$messageStack->add('ask_a_question', ENTRY_EMAIL_ADDRESS_ERROR);
	} 

	if (($_POST['captcha'] != $_SESSION['vvcode'])) {
		$error = true;
        $messageStack->add('ask_a_question', TEXT_WRONG_CODE);
	}

	if ($message == '') {
		$error = true;
		$messageStack->add('ask_a_question', TEXT_MESSAGE_ERROR);
	}

	if ($messageStack->size('ask_a_question') > 0) {
$vamTemplate->assign('error', $messageStack->output('ask_a_question'));
	}

		if ($error == false) {
		$vamTemplate->assign('PRODUCTS_NAME', $product_info['products_name']);
		$vamTemplate->assign('PRODUCTS_MODEL', $product_info['products_model']);
		$vamTemplate->assign('TEXT_MESSAGE', $_POST['message_body']);
		$vamTemplate->assign('TEXT_FIRSTNAME', $firstname);
		$vamTemplate->assign('TEXT_LASTNAME', $lastname);
		$vamTemplate->assign('TEXT_EMAIL', $email_address);
		$vamTemplate->assign('TEXT_EMAIL_SUCCESSFUL', sprintf(TEXT_EMAIL_SUCCESSFUL_SENT, $product_info['products_name']));
		$vamTemplate->assign('PRODUCT_LINK', vam_href_link(FILENAME_PRODUCT_INFO, vam_product_link($product_info['products_id'], $product_info['products_name'])));
		$vamTemplate->caching = 0;
		$html_mail = $vamTemplate->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/ask_a_question.html');
		$vamTemplate->caching = 0;
		$txt_mail = $vamTemplate->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/ask_a_question.txt');
	// send mail to admin
	vam_php_mail($to_email_address, EMAIL_SUPPORT_NAME, EMAIL_SUPPORT_ADDRESS, STORE_NAME, EMAIL_SUPPORT_FORWARDING_STRING, $to_email_address, $to_name, '', '', NAVBAR_TITLE_ASK, $html_mail, $txt_mail);
	// send mail to customer
	vam_php_mail(EMAIL_SUPPORT_ADDRESS, EMAIL_SUPPORT_NAME, $to_email_address, $to_name, EMAIL_SUPPORT_FORWARDING_STRING, EMAIL_SUPPORT_REPLY_ADDRESS, EMAIL_SUPPORT_REPLY_ADDRESS_NAME, '', '', NAVBAR_TITLE_ASK, $html_mail, $txt_mail);

if (!CacheCheck()) {
	$vamTemplate->caching = 0;
	$vamTemplate->display(CURRENT_TEMPLATE.'/module/ask_a_question_ok.html');
} else {
	$vamTemplate->caching = 1;
	$vamTemplate->cache_lifetime = CACHE_LIFETIME;
	$vamTemplate->cache_modified_check = CACHE_CHECK;
	$cache_id = $_SESSION['language'];
	$vamTemplate->display(CURRENT_TEMPLATE.'/module/ask_a_question_ok.html', $cache_id);
		}
	}else{
$vamTemplate->assign('PRODUCTS_NAME', $product_info['products_name']);
$vamTemplate->assign('PRODUCTS_MODEL', $product_info['products_model']);

$vamTemplate->assign('FORM_ACTION', vam_draw_form('ask_a_question', vam_href_link(FILENAME_ASK_PRODUCT_QUESTION, 'products_id='.$_GET['products_id'].'')).vam_draw_hidden_field('action', 'process').vam_draw_hidden_field('products_id', $_GET['products_id']));
$vamTemplate->assign('CAPTCHA_IMG', vam_image(HTTP_SERVER . DIR_WS_CATALOG . FILENAME_DISPLAY_CAPTCHA, 'captcha'));    
$vamTemplate->assign('CAPTCHA_INPUT', vam_draw_input_field('captcha', '', 'size="6" maxlength="6"', 'text', false));

        if (isset($_SESSION['customer_id'])) { 
		//-> registered user********************************************************
$vamTemplate->assign('INPUT_FIRSTNAME', $_SESSION['customer_first_name']);
$vamTemplate->assign('INPUT_LASTNAME', $_SESSION['customer_last_name']);
$vamTemplate->assign('INPUT_EMAIL', $_SESSION['customer_email_address']);
        }else{
		//-> guest *********************************************************  
$vamTemplate->assign('INPUT_FIRSTNAME', vam_draw_input_fieldNote(array ('name' => 'firstname', 'text' => '&nbsp;'. (vam_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_FIRST_NAME_TEXT.'</span>' : ''))));
$vamTemplate->assign('INPUT_LASTNAME', vam_draw_input_fieldNote(array ('name' => 'lastname', 'text' => '&nbsp;'. (vam_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_LAST_NAME_TEXT.'</span>' : ''))));
$vamTemplate->assign('INPUT_EMAIL', vam_draw_input_fieldNote(array ('name' => 'email_address', 'text' => '&nbsp;'. (vam_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : ''))));
        }
$vamTemplate->assign('INPUT_TEXT', vam_draw_textarea_field('message_body', 'soft', 50, 15, stripslashes($_POST['message_body'])));
$vamTemplate->assign('FORM_END', '</form>');
$vamTemplate->assign('BUTTON_SUBMIT', vam_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$vamTemplate->assign('BUTTON_CONTINUE', '<a href="javascript:window.close()">'.vam_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');

// set cache ID
 if (!CacheCheck()) {
	$vamTemplate->caching = 0;
	$vamTemplate->display(CURRENT_TEMPLATE.'/module/ask_a_question.html');
} else {
	$vamTemplate->caching = 1;
	$vamTemplate->cache_lifetime = CACHE_LIFETIME;
	$vamTemplate->cache_modified_check = CACHE_CHECK;
	$cache_id = $_SESSION['language'];
	$vamTemplate->display(CURRENT_TEMPLATE.'/module/ask_a_question.html', $cache_id);
	}
}
}else{

$product_info_query = vam_db_query("select * FROM ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd where p.products_status = '1' and p.products_id = '".(int)$_GET['products_id']."' and pd.products_id = p.products_id and pd.language_id = '".(int)$_SESSION['languages_id']."'");
$product_info = vam_db_fetch_array($product_info_query);

include ('includes/header.php');

$breadcrumb->add(NAVBAR_TITLE_ASK, vam_href_link(FILENAME_ASK_PRODUCT_QUESTION, 'products_id='.$product->data['products_id'], 'SSL'));

$vamTemplate->assign('PRODUCTS_NAME', $product_info['products_name']);
$vamTemplate->assign('PRODUCTS_MODEL', $product_info['products_model']);
$vamTemplate->assign('CAPTCHA_IMG', vam_image(HTTP_SERVER . DIR_WS_CATALOG . FILENAME_DISPLAY_CAPTCHA, 'captcha'));    
$vamTemplate->assign('CAPTCHA_INPUT', vam_draw_input_field('captcha', '', 'size="6" maxlength="6"', 'text', false));

$vamTemplate->assign('FORM_ACTION', vam_draw_form('ask_a_question', vam_href_link(FILENAME_ASK_PRODUCT_QUESTION, 'products_id='.$_GET['products_id'].'')).vam_draw_hidden_field('action', 'process').vam_draw_hidden_field('products_id', $_GET['products_id']));
        if (isset($_SESSION['customer_id'])) { 
		//-> registered user********************************************************
$vamTemplate->assign('INPUT_FIRSTNAME', $_SESSION['customer_first_name']);
$vamTemplate->assign('INPUT_LASTNAME', $_SESSION['customer_last_name']);
$vamTemplate->assign('INPUT_EMAIL', $_SESSION['customer_email_address']);
        }else{
		//-> guest *********************************************************  
$vamTemplate->assign('INPUT_FIRSTNAME', vam_draw_input_fieldNote(array ('name' => 'firstname', 'text' => '&nbsp;'. (vam_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_FIRST_NAME_TEXT.'</span>' : ''))));
$vamTemplate->assign('INPUT_LASTNAME', vam_draw_input_fieldNote(array ('name' => 'lastname', 'text' => '&nbsp;'. (vam_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">'.ENTRY_LAST_NAME_TEXT.'</span>' : ''))));
$vamTemplate->assign('INPUT_EMAIL', vam_draw_input_fieldNote(array ('name' => 'email_address', 'text' => '&nbsp;'. (vam_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">'.ENTRY_EMAIL_ADDRESS_TEXT.'</span>' : ''))));
        }
$vamTemplate->assign('INPUT_TEXT', vam_draw_textarea_field('message_body', 'soft', 50, 15, stripslashes($_POST['message_body'])));
$vamTemplate->assign('FORM_END', '</form>');
$vamTemplate->assign('BUTTON_SUBMIT', vam_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
$vamTemplate->assign('BUTTON_CONTINUE', '<a href="javascript:window.close()">'.vam_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');

	$vamTemplate->caching = 0;
	$vamTemplate->display(CURRENT_TEMPLATE.'/module/ask_a_question.html');
}
?>