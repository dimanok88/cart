<?php
/* --------------------------------------------------------------
   $Id: articles.php 1125 2007-03-09 11:13:01Z VaM $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(configuration.php,v 1.40 2002/12/29); www.oscommerce.com 

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

defined( '_VALID_VAM' ) or die( 'Direct Access to this location is not allowed.' );

  require_once (DIR_FS_INC.'vam_string_to_int.inc.php');

// Parse and secure the tPath parameter values
  function vam_parse_topic_path($tPath) {
// make sure the topic IDs are integers
    $tPath_array = array_map('vam_string_to_int', explode('_', $tPath));

// make sure no duplicate topic IDs exist which could lock the server in a loop
    $tmp_array = array();
    $n = sizeof($tPath_array);
    for ($i=0; $i<$n; $i++) {
      if (!in_array($tPath_array[$i], $tmp_array)) {
        $tmp_array[] = $tPath_array[$i];
      }
    }

    return $tmp_array;
  }

  function vam_get_topic_name($topic_id, $language_id) {
    $topic_query = vam_db_query("select topics_name from " . TABLE_TOPICS_DESCRIPTION . " where topics_id = '" . (int)$topic_id . "' and language_id = '" . (int)$language_id . "'");
    $topic = vam_db_fetch_array($topic_query);

    return $topic['topics_name'];
  }

  function vam_get_topic_tree($parent_id = '0', $spacing = '', $exclude = '', $topic_tree_array = '', $include_itself = false) {
    global $languages_id;

    if (!is_array($topic_tree_array)) $topic_tree_array = array();
    if ( (sizeof($topic_tree_array) < 1) && ($exclude != '0') ) $topic_tree_array[] = array('id' => '0', 'text' => TEXT_TOP);

    if ($include_itself) {
      $topic_query = vam_db_query("select cd.topics_name from " . TABLE_TOPICS_DESCRIPTION . " cd where cd.language_id = '" . (int)$_SESSION['languages_id'] . "' and cd.topics_id = '" . (int)$parent_id . "'");
      $topic = vam_db_fetch_array($topic_query);
      $topic_tree_array[] = array('id' => $parent_id, 'text' => $topic['topics_name']);
    }

    $topics_query = vam_db_query("select c.topics_id, cd.topics_name, c.parent_id from " . TABLE_TOPICS . " c, " . TABLE_TOPICS_DESCRIPTION . " cd where c.topics_id = cd.topics_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' and c.parent_id = '" . (int)$parent_id . "' order by c.sort_order, cd.topics_name");
    while ($topics = vam_db_fetch_array($topics_query)) {
      if ($exclude != $topics['topics_id']) $topic_tree_array[] = array('id' => $topics['topics_id'], 'text' => $spacing . $topics['topics_name']);
      $topic_tree_array = vam_get_topic_tree($topics['topics_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $topic_tree_array);
    }

    return $topic_tree_array;
  }

  function vam_generate_topic_path($id, $from = 'topic', $topics_array = '', $index = 0) {
    global $languages_id;

    if (!is_array($topics_array)) $topics_array = array();

    if ($from == 'article') {
      $topics_query = vam_db_query("select topics_id from " . TABLE_ARTICLES_TO_TOPICS . " where articles_id = '" . (int)$id . "'");
      while ($topics = vam_db_fetch_array($topics_query)) {
        if ($topics['topics_id'] == '0') {
          $topics_array[$index][] = array('id' => '0', 'text' => TEXT_TOP);
        } else {
          $topic_query = vam_db_query("select cd.topics_name, c.parent_id from " . TABLE_TOPICS . " c, " . TABLE_TOPICS_DESCRIPTION . " cd where c.topics_id = '" . (int)$topics['topics_id'] . "' and c.topics_id = cd.topics_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
          $topic = vam_db_fetch_array($topic_query);
          $topics_array[$index][] = array('id' => $topics['topics_id'], 'text' => $topic['topics_name']);
          if ( (vam_not_null($topic['parent_id'])) && ($topic['parent_id'] != '0') ) $topics_array = vam_generate_topic_path($topic['parent_id'], 'topic', $topics_array, $index);
          $topics_array[$index] = array_reverse($topics_array[$index]);
        }
        $index++;
      }
    } elseif ($from == 'topic') {
      $topic_query = vam_db_query("select cd.topics_name, c.parent_id from " . TABLE_TOPICS . " c, " . TABLE_TOPICS_DESCRIPTION . " cd where c.topics_id = '" . (int)$id . "' and c.topics_id = cd.topics_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
      $topic = vam_db_fetch_array($topic_query);
      $topics_array[$index][] = array('id' => $id, 'text' => $topic['topics_name']);
      if ( (vam_not_null($topic['parent_id'])) && ($topic['parent_id'] != '0') ) $topics_array = vam_generate_topic_path($topic['parent_id'], 'topic', $topics_array, $index);
    }

    return $topics_array;
  }

  function vam_output_generated_topic_path($id, $from = 'topic') {
    $calculated_topic_path_string = '';
    $calculated_topic_path = vam_generate_topic_path($id, $from);
    for ($i=0, $n=sizeof($calculated_topic_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_topic_path[$i]); $j<$k; $j++) {
        $calculated_topic_path_string .= $calculated_topic_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
      }
      $calculated_topic_path_string = substr($calculated_topic_path_string, 0, -16) . '<br>';
    }
    $calculated_topic_path_string = substr($calculated_topic_path_string, 0, -4);

    if (strlen($calculated_topic_path_string) < 1) $calculated_topic_path_string = TEXT_TOP;

    return $calculated_topic_path_string;
  }

////
// Generate a path to topics
  function vam_get_topic_path($current_topic_id = '') {
    global $tPath_array;

    if (vam_not_null($current_topic_id)) {
      $cp_size = sizeof($tPath_array);
      if ($cp_size == 0) {
        $tPath_new = $current_topic_id;
      } else {
        $tPath_new = '';
        $last_topic_query = vam_db_query("select parent_id from " . TABLE_TOPICS . " where topics_id = '" . (int)$tPath_array[($cp_size-1)] . "'");
        $last_topic = vam_db_fetch_array($last_topic_query);

        $current_topic_query = vam_db_query("select parent_id from " . TABLE_TOPICS . " where topics_id = '" . (int)$current_topic_id . "'");
        $current_topic = vam_db_fetch_array($current_topic_query);

        if ($last_topic['parent_id'] == $current_topic['parent_id']) {
          for ($i=0; $i<($cp_size-1); $i++) {
            $tPath_new .= '_' . $tPath_array[$i];
          }
        } else {
          for ($i=0; $i<$cp_size; $i++) {
            $tPath_new .= '_' . $tPath_array[$i];
          }
        }
        $tPath_new .= '_' . $current_topic_id;

        if (substr($tPath_new, 0, 1) == '_') {
          $tPath_new = substr($tPath_new, 1);
        }
      }
    } else {
      $tPath_new = implode('_', $tPath_array);
    }

    return 'tPath=' . $tPath_new;
  }

  function vam_get_generated_topic_path_ids($id, $from = 'topic') {
    $calculated_topic_path_string = '';
    $calculated_topic_path = vam_generate_topic_path($id, $from);
    for ($i=0, $n=sizeof($calculated_topic_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_topic_path[$i]); $j<$k; $j++) {
        $calculated_topic_path_string .= $calculated_topic_path[$i][$j]['id'] . '_';
      }
      $calculated_topic_path_string = substr($calculated_topic_path_string, 0, -1) . '<br>';
    }
    $calculated_topic_path_string = substr($calculated_topic_path_string, 0, -4);

    if (strlen($calculated_topic_path_string) < 1) $calculated_topic_path_string = TEXT_TOP;

    return $calculated_topic_path_string;
  }

////
// Return the authors URL in the needed language
// TABLES: authors_info
  function vam_get_author_url($author_id, $language_id) {
    $author_query = vam_db_query("select authors_url from " . TABLE_AUTHORS_INFO . " where authors_id = '" . (int)$author_id . "' and languages_id = '" . (int)$language_id . "'");
    $author = vam_db_fetch_array($author_query);

    return $author['authors_url'];
  }

////
// Return the authors description in the needed language
// TABLES: authors_info
  function vam_get_author_description($author_id, $language_id) {
    $author_query = vam_db_query("select authors_description from " . TABLE_AUTHORS_INFO . " where authors_id = '" . (int)$author_id . "' and languages_id = '" . (int)$language_id . "'");
    $author = vam_db_fetch_array($author_query);

    return $author['authors_description'];
  }

////
// Sets the status of an article
  function vam_set_article_status($articles_id, $status) {
    if ($status == '1') {
      return vam_db_query("update " . TABLE_ARTICLES . " set articles_status = '1', articles_last_modified = now() where articles_id = '" . (int)$articles_id . "'");
    } elseif ($status == '0') {
      return vam_db_query("update " . TABLE_ARTICLES . " set articles_status = '0', articles_last_modified = now() where articles_id = '" . (int)$articles_id . "'");
    } else {
      return -1;
    }
  }

  function vam_get_articles_name($article_id, $language_id = 0) {
    global $languages_id;

    if ($language_id == 0) $language_id = $_SESSION['languages_id'];
    $article_query = vam_db_query("select articles_name from " . TABLE_ARTICLES_DESCRIPTION . " where articles_id = '" . (int)$article_id . "' and language_id = '" . (int)$language_id . "'");
    $article = vam_db_fetch_array($article_query);

    return $article['articles_name'];
  }

  function vam_get_articles_head_title_tag($article_id, $language_id = 0) {
    global $languages_id;

    if ($language_id == 0) $language_id = $_SESSION['languages_id'];
    $article_query = vam_db_query("select articles_head_title_tag from " . TABLE_ARTICLES_DESCRIPTION . " where articles_id = '" . (int)$article_id . "' and language_id = '" . (int)$language_id . "'");
    $article = vam_db_fetch_array($article_query);

    return $article['articles_head_title_tag'];
  }

  function vam_get_articles_description($article_id, $language_id) {
    $article_query = vam_db_query("select articles_description from " . TABLE_ARTICLES_DESCRIPTION . " where articles_id = '" . (int)$article_id . "' and language_id = '" . (int)$language_id . "'");
    $article = vam_db_fetch_array($article_query);

    return $article['articles_description'];
  }

  function vam_get_articles_head_desc_tag($article_id, $language_id) {
    $article_query = vam_db_query("select articles_head_desc_tag from " . TABLE_ARTICLES_DESCRIPTION . " where articles_id = '" . (int)$article_id . "' and language_id = '" . (int)$language_id . "'");
    $article = vam_db_fetch_array($article_query);

    return $article['articles_head_desc_tag'];
  }

  function vam_get_articles_head_keywords_tag($article_id, $language_id) {
    $article_query = vam_db_query("select articles_head_keywords_tag from " . TABLE_ARTICLES_DESCRIPTION . " where articles_id = '" . (int)$article_id . "' and language_id = '" . (int)$language_id . "'");
    $article = vam_db_fetch_array($article_query);

    return $article['articles_head_keywords_tag'];
  }

  function vam_get_articles_url($article_id, $language_id) {
    $article_query = vam_db_query("select articles_url from " . TABLE_ARTICLES_DESCRIPTION . " where articles_id = '" . (int)$article_id . "' and language_id = '" . (int)$language_id . "'");
    $article = vam_db_fetch_array($article_query);

    return $article['articles_url'];
  }


////
// Count how many articles exist in a topic
// TABLES: articles, articles_to_topics, topics
  function vam_articles_in_topic_count($topics_id, $include_deactivated = false) {
    $articles_count = 0;

    if ($include_deactivated) {
      $articles_query = vam_db_query("select count(*) as total from " . TABLE_ARTICLES . " p, " . TABLE_ARTICLES_TO_TOPICS . " p2c where p.articles_id = p2c.articles_id and p2c.topics_id = '" . (int)$topics_id . "'");
    } else {
      $articles_query = vam_db_query("select count(*) as total from " . TABLE_ARTICLES . " p, " . TABLE_ARTICLES_TO_TOPICS . " p2c where p.articles_id = p2c.articles_id and p.articles_status = '1' and p2c.topics_id = '" . (int)$topics_id . "'");
    }

    $articles = vam_db_fetch_array($articles_query);

    $articles_count += $articles['total'];

    $childs_query = vam_db_query("select topics_id from " . TABLE_TOPICS . " where parent_id = '" . (int)$topics_id . "'");
    if (vam_db_num_rows($childs_query)) {
      while ($childs = vam_db_fetch_array($childs_query)) {
        $articles_count += vam_articles_in_topic_count($childs['topics_id'], $include_deactivated);
      }
    }

    return $articles_count;
  }

////
// Count how many subtopics exist in a topic
// TABLES: topics
  function vam_childs_in_topic_count($topics_id) {
    $topics_count = 0;

    $topics_query = vam_db_query("select topics_id from " . TABLE_TOPICS . " where parent_id = '" . (int)$topics_id . "'");
    while ($topics = vam_db_fetch_array($topics_query)) {
      $topics_count++;
      $topics_count += vam_childs_in_topic_count($topics['topics_id']);
    }

    return $topics_count;
  }

  function vam_remove_topic($topic_id) {
    $topic_image_query = vam_db_query("select topics_image from " . TABLE_TOPICS . " where topics_id = '" . (int)$topic_id . "'");
    $topic_image = vam_db_fetch_array($topic_image_query);

    $duplicate_image_query = vam_db_query("select count(*) as total from " . TABLE_TOPICS . " where topics_image = '" . vam_db_input($topic_image['topics_image']) . "'");
    $duplicate_image = vam_db_fetch_array($duplicate_image_query);

    if ($duplicate_image['total'] < 2) {
      if (file_exists(DIR_FS_CATALOG_IMAGES . $topic_image['topics_image'])) {
        @unlink(DIR_FS_CATALOG_IMAGES . $topic_image['topics_image']);
      }
    }

    vam_db_query("delete from " . TABLE_TOPICS . " where topics_id = '" . (int)$topic_id . "'");
    vam_db_query("delete from " . TABLE_TOPICS_DESCRIPTION . " where topics_id = '" . (int)$topic_id . "'");
    vam_db_query("delete from " . TABLE_ARTICLES_TO_TOPICS . " where topics_id = '" . (int)$topic_id . "'");

    if (USE_CACHE == 'true') {
      vam_reset_cache_block('topics');
      vam_reset_cache_block('also_purchased');
    }
  }

  function vam_remove_article($article_id) {
    vam_db_query("delete from " . TABLE_ARTICLES . " where articles_id = '" . (int)$article_id . "'");
    vam_db_query("delete from " . TABLE_ARTICLES_TO_TOPICS . " where articles_id = '" . (int)$article_id . "'");
    vam_db_query("delete from " . TABLE_ARTICLES_DESCRIPTION . " where articles_id = '" . (int)$article_id . "'");

    if (USE_CACHE == 'true') {
      vam_reset_cache_block('topics');
      vam_reset_cache_block('also_purchased');
    }
  }

// Topics Description contribution
  function vam_get_topic_heading_title($topic_id, $language_id) {
    $topic_query = vam_db_query("select topics_heading_title from " . TABLE_TOPICS_DESCRIPTION . " where topics_id = '" . $topic_id . "' and language_id = '" . $language_id . "'");
    $topic = vam_db_fetch_array($topic_query);
    return $topic['topics_heading_title'];
  }

  function vam_get_topic_description($topic_id, $language_id) {
    $topic_query = vam_db_query("select topics_description from " . TABLE_TOPICS_DESCRIPTION . " where topics_id = '" . $topic_id . "' and language_id = '" . $language_id . "'");
    $topic = vam_db_fetch_array($topic_query);
    return $topic['topics_description'];
  }
?>
