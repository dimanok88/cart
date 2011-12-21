<?php
/* -----------------------------------------------------------------------------------------
   $Id: shop_content.php 1303 2007-02-06 19:20:03 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(conditions.php,v 1.21 2003/02/13); www.oscommerce.com 
   (c) 2003	 nextcommerce (shop_content.php,v 1.1 2003/08/19); www.nextcommerce.org
   (c) 2003	 xt:Commerce (shop_content.php,v 1.1 2003/08/19); xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');
// create template elements
$vamTemplate = new vamTemplate;
// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

// include needed functions
require_once (DIR_FS_INC.'vam_validate_email.inc.php');

if (GROUP_CHECK == 'true') {
	$group_check = "and group_ids LIKE '%c_".$_SESSION['customers_status']['customers_status_id']."_group%'";
}

$shop_content_query = vam_db_query("SELECT
                     content_id,
                     content_title,
                     content_group,
                     content_heading,
                     content_text,
                     content_file
                     FROM ".TABLE_CONTENT_MANAGER."
                     WHERE content_group='".(int) $_GET['coID']."' ".$group_check."
                     AND languages_id='".(int) $_SESSION['languages_id']."'");
$shop_content_data = vam_db_fetch_array($shop_content_query);

$shop_content_sub_pages_query = vam_db_query("SELECT
                     content_id,
                     content_title,
                     content_group,
                     content_heading,
                     content_text,
                     content_file
                     FROM ".TABLE_CONTENT_MANAGER."
                     WHERE parent_id='" . $shop_content_data['content_id'] . "' ".$group_check."
                     AND languages_id='".(int) $_SESSION['languages_id']."'");

  $sub_pages_content = array();

      while ($shop_content_sub_pages_data = vam_db_fetch_array($shop_content_sub_pages_query )) {

          $sub_pages_content[]=array(
              'PAGE_ID' => $shop_content_sub_pages_data['content_id'],
              'PAGE_TITLE' => $shop_content_sub_pages_data['content_title'],
              'PAGE_HEADING'      => $shop_content_sub_pages_data['content_heading'],
              'PAGE_CONTENT'    => vam_date_short($one['content_text']),
              'PAGE_LINK'    => vam_href_link(FILENAME_CONTENT, 'coID='.$shop_content_sub_pages_data['content_group'])
              );
      }

  $vamTemplate->assign('sub_pages_content',$sub_pages_content);

$breadcrumb->add($shop_content_data['content_title'], vam_href_link(FILENAME_CONTENT,'coID='.(int) $_GET['coID']));

if ($_GET['coID'] != 7) {
	require (DIR_WS_INCLUDES.'header.php');
}
if ($_GET['coID'] == 7 && $_GET['action'] == 'success') {
	require (DIR_WS_INCLUDES.'header.php');
}

$vamTemplate->assign('CONTENT_HEADING', $shop_content_data['content_heading']);

if ($_GET['coID'] == 7) {

	$error = false;
	if (isset ($_GET['action']) && ($_GET['action'] == 'send')) {
		if (vam_validate_email(trim($_POST['email']))) {

			vam_php_mail($_POST['email'], $_POST['name'], CONTACT_US_EMAIL_ADDRESS, CONTACT_US_NAME, CONTACT_US_FORWARDING_STRING, $_POST['email'], $_POST['name'], '', '', CONTACT_US_EMAIL_SUBJECT, nl2br($_POST['message_body']), $_POST['message_body']);

			if (!isset ($mail_error)) {
				vam_redirect(vam_href_link(FILENAME_CONTENT, 'action=success&coID='.(int) $_GET['coID']));
			} else {
				$vamTemplate->assign('error_message', $mail_error);

			}
		} else {
			// error report hier einbauen
			$vamTemplate->assign('error_message', ERROR_MAIL);
			$error = true;
		}

	}

	$vamTemplate->assign('CONTACT_HEADING', $shop_content_data['content_title']);
	if (isset ($_GET['action']) && ($_GET['action'] == 'success')) {
		$vamTemplate->assign('success', '1');
		$vamTemplate->assign('BUTTON_CONTINUE', '<a href="'.vam_href_link(FILENAME_DEFAULT).'">'.vam_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE).'</a>');

	} else {
		if ($shop_content_data['content_file'] != '') {
			ob_start();
			$file_name = basename ($shop_content_data['content_file']);
    		$isTextFile = strpos($file_name, '.txt');
			if ($isTextFile)
				echo '';
			include (DIR_FS_CATALOG.'media/content/'.$shop_content_data['content_file']);
			if ($isTextFile)
				echo '';
		$contact_content = ob_get_contents();
		ob_end_clean();
		} else {
			$contact_content = $shop_content_data['content_text'];
		}
		require (DIR_WS_INCLUDES.'header.php');
		$vamTemplate->assign('CONTACT_CONTENT', $contact_content);
		$vamTemplate->assign('FORM_ACTION', vam_draw_form('contact_us', vam_href_link(FILENAME_CONTENT, 'action=send&coID='.(int) $_GET['coID'])));
		$vamTemplate->assign('INPUT_NAME', vam_draw_input_field('name', ($error ? $_POST['name'] : $first_name)));
		$vamTemplate->assign('INPUT_EMAIL', vam_draw_input_field('email', ($error ? $_POST['email'] : $email_address)));
		$vamTemplate->assign('INPUT_TEXT', vam_draw_textarea_field('message_body', 'soft', 50, 15, $_POST[''],''));
		$vamTemplate->assign('BUTTON_SUBMIT', vam_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
		$vamTemplate->assign('FORM_END', '</form>');
	}

	$vamTemplate->assign('language', $_SESSION['language']);

	$vamTemplate->caching = 0;
	$main_content = $vamTemplate->fetch(CURRENT_TEMPLATE.'/module/contact_us.html');

} else {

	if ($shop_content_data['content_file'] != '') {

		ob_start();

		$file_name = basename ($shop_content_data['content_file']);
		$isTextFile = strpos($file_name, '.txt');
		if ($isTextFile)
			echo '';
		include (DIR_FS_CATALOG.'media/content/'.$shop_content_data['content_file']);
		if ($isTextFile)
			echo '';
		$vamTemplate->assign('file', ob_get_contents());
		ob_end_clean();

	} else {
		$content_body = $shop_content_data['content_text'];
	}
	$vamTemplate->assign('CONTENT_BODY', $content_body);

	$vamTemplate->assign('BUTTON_CONTINUE', '<a href="javascript:history.back(1)">'.vam_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
	$vamTemplate->assign('language', $_SESSION['language']);

	// set cache ID
	 if (!CacheCheck()) {
		$vamTemplate->caching = 0;
		$main_content = $vamTemplate->fetch(CURRENT_TEMPLATE.'/module/content.html');
	} else {
		$vamTemplate->caching = 1;
		$vamTemplate->cache_lifetime = CACHE_LIFETIME;
		$vamTemplate->cache_modified_check = CACHE_CHECK;
		$cache_id = $_SESSION['language'].$shop_content_data['content_id'];
		$main_content = $vamTemplate->fetch(CURRENT_TEMPLATE.'/module/content.html', $cache_id);
	}

}

$vamTemplate->assign('language', $_SESSION['language']);
$vamTemplate->assign('main_content', $main_content);
$vamTemplate->caching = 0;
if (!defined(RM)) $vamTemplate->load_filter('output', 'note');
$template = (file_exists('templates/'.CURRENT_TEMPLATE.'/'.FILENAME_CONTENT.'_'.$_GET['coID'].'.html') ? CURRENT_TEMPLATE.'/'.FILENAME_CONTENT.'_'.$_GET['coID'].'.html' : CURRENT_TEMPLATE.'/index.html');
$vamTemplate->display($template);
include ('includes/application_bottom.php');
?>