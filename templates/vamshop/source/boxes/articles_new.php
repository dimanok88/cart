<?php
/* -----------------------------------------------------------------------------------------
   $Id: articles_new.php 1262 2007-07-25 12:30:44 VaM $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

$box = new vamTemplate;
$box->assign('tpl_path','templates/'.CURRENT_TEMPLATE.'/');

$sql = "select a.articles_id, ad.articles_name, ad.articles_description from " . TABLE_ARTICLES . " a left join " . TABLE_ARTICLES_DESCRIPTION . " ad on ad.articles_id = a.articles_id where a.articles_status = '1' and ad.language_id = '" . (int)$_SESSION['languages_id'] . "' ORDER BY articles_date_added DESC LIMIT " . MAX_NEW_ARTICLES_PER_PAGE . "";

$articles_content = array();
$articles_query = vamDBquery($sql);
while ($articles = vam_db_fetch_array($articles_query,true)) {

		$SEF_parameter = '';
		if (SEARCH_ENGINE_FRIENDLY_URLS == 'true')
			$SEF_parameter = '&article='.vam_cleanName($articles['articles_name']);

    $articles_content[]=array(
        'ARTICLES_NAME' => $articles['articles_name'],
        'ARTICLES_CONTENT' => $articles['articles_description'],
        'ARTICLES_URL'    => vam_href_link(FILENAME_ARTICLE_INFO, 'articles_id=' . $articles['articles_id'] . $SEF_parameter)
        );
}

if (sizeof($articles_content) > 0) {
    $box->assign('language', $_SESSION['language']);
    $box->assign('articles_content',$articles_content);
    // set cache ID
    if (USE_CACHE=='false') {
        $box->caching = 0;
        $articiles_new= $box->fetch(CURRENT_TEMPLATE.'/boxes/box_articles_new.html');
    } else {
        $box->caching = 1;
        $box->cache_lifetime=CACHE_LIFETIME;
        $box->cache_modified_check=CACHE_CHECK;
        $cache_id = $_SESSION['language'].$_SESSION['customers_status']['customers_status_id'];
        $articiles_new = $box->fetch(CURRENT_TEMPLATE.'/boxes/box_articles_new.html',$cache_id);
    }
    $vamTemplate->assign('box_ARTICLESNEW',$articiles_new);
}
?>