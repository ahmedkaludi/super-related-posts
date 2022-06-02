<?php
/*
Plugin Name: Super Related Posts
Plugin URI: https://ampforwp.com/
Description: Displays a highly configurable list of related posts. Similarity can be based on any combination of word usage in the content, title, or tags.
Version: 1
Author: AMPforWP Team
Author URI: https://ampforwp.com/
Text Domain: super-related-posts
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'SUPER_RELATED_POSTS_VERSION' ) ) {
    define( 'SUPER_RELATED_POSTS_VERSION', '1' );
}

define('SPRP_DIR_NAME', dirname( __FILE__ ));
define('SPRP_PLUGIN_URI', plugin_dir_url(__FILE__));

function super_related_posts($args = '') {
	echo SuperRelatedPosts::execute($args);
}

function super_related_posts_mark_current(){
	global $post, $sprp_current_ID;
	$sprp_current_ID = $post->ID;
}

define ('POST_PLUGIN_LIBRARY', true);

if (!defined('CF_LIBRARY')) require(SPRP_DIR_NAME.'/includes/common_functions.php');
if (!defined('OT_LIBRARY')) require(SPRP_DIR_NAME.'/includes/output_tags.php');
if (!defined('ACF_LIBRARY')) require(SPRP_DIR_NAME.'/admin/admin_common_functions.php');
if (!defined('SRP_ADMIN_SUBPAGES_LIBRARY')) require(SPRP_DIR_NAME.'/admin/admin-subpages.php');

if (!defined('DSEP')) define('DSEP', DIRECTORY_SEPARATOR);
if (!defined('POST_PLUGIN_LIBRARY')) SuperRelatedPosts::install_post_plugin_library();

$sprp_current_ID = -1;

class SuperRelatedPosts {
  static $version = 0;

  static function get_plugin_version() {
    $plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');
    SuperRelatedPosts::$version = $plugin_data['version'];

    return $plugin_data['version'];
  } // get_plugin_version

  // check if plugin's admin page is shown
  static function is_plugin_admin_page($page = 'settings') {
    $current_screen = get_current_screen();

    if ($page == 'settings' && $current_screen->id == 'settings_page_similar-posts') {
      return true;
    }

    return false;
  } // is_plugin_admin_page

  // add settings link to plugins page
  static function plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=similar-posts') . '" title="Settings for Super Related Posts">Settings</a>';

    array_unshift($links, $settings_link);

    return $links;
  } // plugin_action_links


	static function execute($args='', $default_output_template='<li>{imagesrc_shareaholic}</li>', $option_key='similar-posts'){
		global $table_prefix, $wpdb, $wp_version, $sprp_current_ID;
		$start_time = ppl_microtime();
		$postid = ppl_current_post_id($sprp_current_ID);
		if (defined('POC_CACHE_4')) {
			$cache_key = $option_key.$postid.$args;
			$result = poc_cache_fetch($cache_key);
			if ($result !== false) return $result . sprintf("<!-- Super Related Posts took %.3f ms (cached) -->", 1000 * (ppl_microtime() - $start_time));
		}
		$table_name = $table_prefix . 'similar_posts';
		// First we process any arguments to see if any defaults have been overridden
		$options = ppl_parse_args($args);
		// Next we retrieve the stored options and use them unless a value has been overridden via the arguments
		$options = srp_set_options($option_key, $options, $default_output_template);
		if (0 < $options['limit']) {
			$match_tags = ($options['match_tags'] !== 'false' && $wp_version >= 2.3);
			$exclude_cats = ($options['excluded_cats'] !== '');
			$include_cats = ($options['included_cats'] !== '');
			$exclude_authors = ($options['excluded_authors'] !== '');
			$include_authors = ($options['included_authors'] !== '');
			$exclude_posts = (trim($options['excluded_posts']) !== '');
			$include_posts = (trim($options['included_posts']) !== '');
			$match_category = ($options['match_cat'] === 'true');
			$use_tag_str = ('' != trim($options['tag_str']) && $wp_version >= 2.3);
			$check_age = ('none' !== $options['age']['direction']);
			$check_custom = (trim($options['custom']['key']) !== '');
			$limit = $options['skip'].', '.$options['limit'];
			$des = isset($options['re_design_1']) ? $options['re_design_1'] : 'd1';

			list( $contentterms, $titleterms, $tagterms) = sp_terms_by_freq($postid, $options['num_terms']);
	 		// these should add up to 1.0
			$weight_content = $options['weight_content'];
			$weight_title = $options['weight_title'];
			$weight_tags = $options['weight_tags'];
			// below a threshold we ignore the weight completely and save some effort
			if ($weight_content < 0.001) $weight_content = (int) 0;
			if ($weight_title < 0.001) $weight_title = (int) 0;
			if ($weight_tags < 0.001) $weight_tags = (int) 0;

			$count_content = substr_count($contentterms, ' ') + 1;
			$count_title = substr_count($titleterms, ' ') + 1;
			$count_tags  = substr_count($tagterms, ' ') + 1;
			if ($weight_content) $weight_content = 57.0 * $weight_content / $count_content;
			if ($weight_title) $weight_title = 18.0 * $weight_title / $count_title;
			if ($weight_tags) $weight_tags = 24.0 * $weight_tags / $count_tags;
			if ($options['hand_links'] === 'true') {
				// check custom field for manual links
				$forced_ids = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $postid AND meta_key = 'sp_similar' ") ;
			} else {
				$forced_ids = '';
			}
			// the workhorse...
			$sql = "SELECT *, ";
			$sql .= score_fulltext_match($table_name, $weight_title, $titleterms, $weight_content, $contentterms, $weight_tags, $tagterms, $forced_ids);

			if ($check_custom) $sql .= "LEFT JOIN $wpdb->postmeta ON post_id = ID ";

			// build the 'WHERE' clause
			$where = array();
			$where[] = where_fulltext_match($weight_title, $titleterms, $weight_content, $contentterms, $weight_tags, $tagterms);
			if (!function_exists('get_post_type')) {
				$where[] = where_hide_future();
			}
			if ($match_category) $where[] = where_match_category();
			if ($match_tags) $where[] = where_match_tags($options['match_tags']);
			if ($include_cats) $where[] = where_included_cats($options['included_cats']);
			if ($exclude_cats) $where[] = where_excluded_cats($options['excluded_cats']);
			if ($exclude_authors) $where[] = where_excluded_authors($options['excluded_authors']);
			if ($include_authors) $where[] = where_included_authors($options['included_authors']);
			if ($exclude_posts) $where[] = where_excluded_posts(trim($options['excluded_posts']));
			if ($include_posts) $where[] = where_included_posts(trim($options['included_posts']));
			if ($use_tag_str) $where[] = where_tag_str($options['tag_str']);
			$where[] = where_omit_post($sprp_current_ID);
			if ($check_age) $where[] = where_check_age($options['age']['direction'], $options['age']['length'], $options['age']['duration']);
			if ($check_custom) $where[] = where_check_custom($options['custom']['key'], $options['custom']['op'], $options['custom']['value']);
			$sql .= "WHERE ".implode(' AND ', $where);
			if ($check_custom) $sql .= " GROUP BY $wpdb->posts.ID";
			$sql .= " ORDER BY score DESC, post_date DESC LIMIT $limit";
			//echo $sql;
			$results = $wpdb->get_results($sql);
		} else {
			$results = false;
		}
	    if ($results) {
			$translations = ppl_prepare_template($options['output_template']);
			foreach ($results as $result) {
				$items[] = ppl_expand_template($result, $options['output_template'], $translations, $option_key);
			}
			if ($options['sort']['by1'] !== '') $items = ppl_sort_items($options['sort'], $results, $option_key, $items);
			$output = implode(($options['divider']) ? $options['divider'] : "\n", $items);
			//Output
			$output = '<div class="sprp '.$des.'"><h2>Related Content</h2><ul>' . $output . '</ul></div>';
		} else {
			// if we reach here our query has produced no output ... so what next?
			if ($options['no_text'] !== 'false') {
				$output = ''; // we display nothing at all
			} else {
				// we display the blank message, with tags expanded if necessary
				$translations = ppl_prepare_template($options['none_text']);
				$output = $options['prefix'] . ppl_expand_template(array(), $options['none_text'], $translations, $option_key) . $options['suffix'];
			}
		}
		if (defined('POC_CACHE_4')) poc_cache_store($cache_key, $output);
		return ($output) ? $output . sprintf("<!-- Super Related Posts took %.3f ms -->", 1000 * (ppl_microtime() - $start_time)) : '';
	}

	static function execute2($args='', $default_output_template='<li>{link}</li>', $option_key='similar-posts'){
		global $table_prefix, $wpdb, $wp_version, $sprp_current_ID;
		$start_time = ppl_microtime();
		$postid = ppl_current_post_id($sprp_current_ID);
		if (defined('POC_CACHE_4')) {
			$cache_key = $option_key.$postid.$args;
			$result = poc_cache_fetch($cache_key);
			if ($result !== false) return $result . sprintf("<!-- Super Related Posts took %.3f ms (cached) -->", 1000 * (ppl_microtime() - $start_time));
		}
		$table_name = $table_prefix . 'similar_posts';
		// First we process any arguments to see if any defaults have been overridden
		$options = ppl_parse_args($args);
		// Next we retrieve the stored options and use them unless a value has been overridden via the arguments
		$options = srp_set_options($option_key, $options, $default_output_template);
		if (0 < $options['limit_2']) {
			$match_tags = ($options['match_tags_2'] !== 'false' && $wp_version >= 2.3);
			$exclude_cats = ($options['excluded_cats'] !== '');
			$include_cats = ($options['included_cats'] !== '');
			$exclude_authors = ($options['excluded_authors'] !== '');
			$include_authors = ($options['included_authors'] !== '');
			$exclude_posts = (trim($options['excluded_posts_2']) !== '');
			$include_posts = (trim($options['included_posts_2']) !== '');
			$match_category = ($options['match_cat_2'] === 'true');
			$use_tag_str = ('' != trim($options['tag_str_2']) && $wp_version >= 2.3);
			$check_age = ('none' !== $options['age']['direction']);
			$check_custom = (trim($options['custom']['key']) !== '');
			$limit = $options['limit'].', '.$options['limit_2'];
			$des = isset($options['re_design_2']) ? $options['re_design_2'] : 'd1';

			list( $contentterms, $titleterms, $tagterms) = sp_terms_by_freq($postid, $options['num_terms']);
	 		// these should add up to 1.0
			$weight_content = $options['weight_content'];
			$weight_title = $options['weight_title'];
			$weight_tags = $options['weight_tags'];
			// below a threshold we ignore the weight completely and save some effort
			if ($weight_content < 0.001) $weight_content = (int) 0;
			if ($weight_title < 0.001) $weight_title = (int) 0;
			if ($weight_tags < 0.001) $weight_tags = (int) 0;

			$count_content = substr_count($contentterms, ' ') + 1;
			$count_title = substr_count($titleterms, ' ') + 1;
			$count_tags  = substr_count($tagterms, ' ') + 1;
			if ($weight_content) $weight_content = 57.0 * $weight_content / $count_content;
			if ($weight_title) $weight_title = 18.0 * $weight_title / $count_title;
			if ($weight_tags) $weight_tags = 24.0 * $weight_tags / $count_tags;
			if ($options['hand_links'] === 'true') {
				// check custom field for manual links
				$forced_ids = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $postid AND meta_key = 'sp_similar' ") ;
			} else {
				$forced_ids = '';
			}
			// the workhorse...
			$sql = "SELECT *, ";
			$sql .= score_fulltext_match($table_name, $weight_title, $titleterms, $weight_content, $contentterms, $weight_tags, $tagterms, $forced_ids);

			if ($check_custom) $sql .= "LEFT JOIN $wpdb->postmeta ON post_id = ID ";

			// build the 'WHERE' clause
			$where = array();
			$where[] = where_fulltext_match($weight_title, $titleterms, $weight_content, $contentterms, $weight_tags, $tagterms);
			if (!function_exists('get_post_type')) {
				$where[] = where_hide_future();
			}
			if ($match_category) $where[] = where_match_category();
			if ($match_tags) $where[] = where_match_tags($options['match_tags_2']);
			if ($include_cats) $where[] = where_included_cats($options['included_cats']);
			if ($exclude_cats) $where[] = where_excluded_cats($options['excluded_cats']);
			if ($exclude_authors) $where[] = where_excluded_authors($options['excluded_authors']);
			if ($include_authors) $where[] = where_included_authors($options['included_authors']);
			if ($exclude_posts) $where[] = where_excluded_posts(trim($options['excluded_posts_2']));
			if ($include_posts) $where[] = where_included_posts(trim($options['included_posts_2']));
			if ($use_tag_str) $where[] = where_tag_str($options['tag_str_2']);
			$where[] = where_omit_post($sprp_current_ID);
			if ($check_age) $where[] = where_check_age($options['age']['direction'], $options['age']['length'], $options['age']['duration']);
			if ($check_custom) $where[] = where_check_custom($options['custom']['key'], $options['custom']['op'], $options['custom']['value']);
			$sql .= "WHERE ".implode(' AND ', $where);
			if ($check_custom) $sql .= " GROUP BY $wpdb->posts.ID";
			$sql .= " ORDER BY score DESC, post_date DESC LIMIT $limit";
			//echo $sql;
			$results = $wpdb->get_results($sql);
		} else {
			$results = false;
		}
	    if ($results) {
			$translations = ppl_prepare_template($options['output_template']);
			foreach ($results as $result) {
				$items[] = ppl_expand_template($result, $options['output_template'], $translations, $option_key);
			}
			if ($options['sort']['by1'] !== '') $items = ppl_sort_items($options['sort'], $results, $option_key, $items);
			$output = implode(($options['divider']) ? $options['divider'] : "\n", $items);
			//Output
			$output = '<div class="sprp '.$des.'"><h2>Related Content</h2><ul>' . $output . '</ul></div>';
		} else {
			// if we reach here our query has produced no output ... so what next?
			if ($options['no_text'] !== 'false') {
				$output = ''; // we display nothing at all
			} else {
				// we display the blank message, with tags expanded if necessary
				$translations = ppl_prepare_template($options['none_text']);
				$output = $options['prefix'] . ppl_expand_template(array(), $options['none_text'], $translations, $option_key) . $options['suffix'];
			}
		}
		if (defined('POC_CACHE_4')) poc_cache_store($cache_key, $output);
		return ($output) ? $output . sprintf("<!-- Super Related Posts took %.3f ms -->", 1000 * (ppl_microtime() - $start_time)) : '';
	}

	static function execute3($args='', $default_output_template='<li>{link}</li>', $option_key='similar-posts'){
		global $table_prefix, $wpdb, $wp_version, $sprp_current_ID;
		$start_time = ppl_microtime();
		$postid = ppl_current_post_id($sprp_current_ID);
		if (defined('POC_CACHE_4')) {
			$cache_key = $option_key.$postid.$args;
			$result = poc_cache_fetch($cache_key);
			if ($result !== false) return $result . sprintf("<!-- Super Related Posts took %.3f ms (cached) -->", 1000 * (ppl_microtime() - $start_time));
		}
		$table_name = $table_prefix . 'similar_posts';
		// First we process any arguments to see if any defaults have been overridden
		$options = ppl_parse_args($args);
		// Next we retrieve the stored options and use them unless a value has been overridden via the arguments
		$options = srp_set_options($option_key, $options, $default_output_template);
		if (0 < $options['limit_3']) {
			$match_tags = ($options['match_tags_3'] !== 'false' && $wp_version >= 2.3);
			$exclude_cats = ($options['excluded_cats'] !== '');
			$include_cats = ($options['included_cats'] !== '');
			$exclude_authors = ($options['excluded_authors'] !== '');
			$include_authors = ($options['included_authors'] !== '');
			$exclude_posts = (trim($options['excluded_posts_3']) !== '');
			$include_posts = (trim($options['included_posts_3']) !== '');
			$match_category = ($options['match_cat_3'] === 'true');
			$use_tag_str = ('' != trim($options['tag_str_3']) && $wp_version >= 2.3);
			$check_age = ('none' !== $options['age']['direction']);
			$check_custom = (trim($options['custom']['key']) !== '');
			$limit = $options['limit_2'].', '.$options['limit_3'];
			$des = isset($options['re_design_3']) ? $options['re_design_3'] : 'd1';

			list( $contentterms, $titleterms, $tagterms) = sp_terms_by_freq($postid, $options['num_terms']);
	 		// these should add up to 1.0
			$weight_content = $options['weight_content'];
			$weight_title = $options['weight_title'];
			$weight_tags = $options['weight_tags'];
			// below a threshold we ignore the weight completely and save some effort
			if ($weight_content < 0.001) $weight_content = (int) 0;
			if ($weight_title < 0.001) $weight_title = (int) 0;
			if ($weight_tags < 0.001) $weight_tags = (int) 0;

			$count_content = substr_count($contentterms, ' ') + 1;
			$count_title = substr_count($titleterms, ' ') + 1;
			$count_tags  = substr_count($tagterms, ' ') + 1;
			if ($weight_content) $weight_content = 57.0 * $weight_content / $count_content;
			if ($weight_title) $weight_title = 18.0 * $weight_title / $count_title;
			if ($weight_tags) $weight_tags = 24.0 * $weight_tags / $count_tags;
			if ($options['hand_links'] === 'true') {
				// check custom field for manual links
				$forced_ids = $wpdb->get_var("SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $postid AND meta_key = 'sp_similar' ") ;
			} else {
				$forced_ids = '';
			}
			// the workhorse...
			$sql = "SELECT *, ";
			$sql .= score_fulltext_match($table_name, $weight_title, $titleterms, $weight_content, $contentterms, $weight_tags, $tagterms, $forced_ids);

			if ($check_custom) $sql .= "LEFT JOIN $wpdb->postmeta ON post_id = ID ";

			// build the 'WHERE' clause
			$where = array();
			$where[] = where_fulltext_match($weight_title, $titleterms, $weight_content, $contentterms, $weight_tags, $tagterms);
			if (!function_exists('get_post_type')) {
				$where[] = where_hide_future();
			}
			if ($match_category) $where[] = where_match_category();
			if ($match_tags) $where[] = where_match_tags($options['match_tags_3']);
			if ($include_cats) $where[] = where_included_cats($options['included_cats']);
			if ($exclude_cats) $where[] = where_excluded_cats($options['excluded_cats']);
			if ($exclude_authors) $where[] = where_excluded_authors($options['excluded_authors']);
			if ($include_authors) $where[] = where_included_authors($options['included_authors']);
			if ($exclude_posts) $where[] = where_excluded_posts(trim($options['excluded_posts_3']));
			if ($include_posts) $where[] = where_included_posts(trim($options['included_posts_3']));
			if ($use_tag_str) $where[] = where_tag_str($options['tag_str_3']);
			$where[] = where_omit_post($sprp_current_ID);
			if ($check_age) $where[] = where_check_age($options['age']['direction'], $options['age']['length'], $options['age']['duration']);
			if ($check_custom) $where[] = where_check_custom($options['custom']['key'], $options['custom']['op'], $options['custom']['value']);
			$sql .= "WHERE ".implode(' AND ', $where);
			if ($check_custom) $sql .= " GROUP BY $wpdb->posts.ID";
			$sql .= " ORDER BY score DESC, post_date DESC LIMIT $limit";
			//echo $sql;
			$results = $wpdb->get_results($sql);
		} else {
			$results = false;
		}
	    if ($results) {
			$translations = ppl_prepare_template($options['output_template']);
			foreach ($results as $result) {
				$items[] = ppl_expand_template($result, $options['output_template'], $translations, $option_key);
			}
			if ($options['sort']['by1'] !== '') $items = ppl_sort_items($options['sort'], $results, $option_key, $items);
			$output = implode(($options['divider']) ? $options['divider'] : "\n", $items);
			//Output
			$output = '<div class="sprp '.$des.'"><h2>Related Content</h2><ul>' . $output . '</ul></div>';
		} else {
			// if we reach here our query has produced no output ... so what next?
			if ($options['no_text'] !== 'false') {
				$output = ''; // we display nothing at all
			} else {
				// we display the blank message, with tags expanded if necessary
				$translations = ppl_prepare_template($options['none_text']);
				$output = $options['prefix'] . ppl_expand_template(array(), $options['none_text'], $translations, $option_key) . $options['suffix'];
			}
		}
		if (defined('POC_CACHE_4')) poc_cache_store($cache_key, $output);
		return ($output) ? $output . sprintf("<!-- Super Related Posts took %.3f ms -->", 1000 * (ppl_microtime() - $start_time)) : '';
	}

  // save some info
  static function activate() {
	$timestamp = wp_next_scheduled( 'wi_create_minutely_backup' );
	if( $timestamp == false ){
		//Schedule the event for right now, then to repeat daily using the hook 'wi_create_daily_backup'
		wp_schedule_event( time(), 'minutely', 'wi_create_minutely_backup' );
	}
	// wp_clear_scheduled_hook( 'super_related_posts_60_seconds_cron_func' );
    // if ( ! wp_next_scheduled( 'super_related_posts_60_seconds_cron_func' ) ) {
	// 	wp_schedule_event( time(), 'minutely', 'super_related_posts_60_seconds_cron_func' );
	// }
    $options = get_option('similar_posts_meta', array());

    if (empty($options['first_version'])) {
      $options['first_version'] = SuperRelatedPosts::get_plugin_version();
      $options['first_install'] = current_time('timestamp');
      update_option('similar_posts_meta', $options);
    }
  } // activate

} // SuperRelatedPosts class

function sp_terms_by_freq($ID, $num_terms = 20) {
	if (!$ID) return array('', '', '');
	global $wpdb, $table_prefix;
	$table_name = $table_prefix . 'similar_posts';
	$terms = '';
	$results = $wpdb->get_results("SELECT title, content, tags FROM $table_name WHERE pID=$ID LIMIT 1", ARRAY_A);
	if ($results) {
		$word = strtok($results[0]['content'], ' ');
		$n = 0;
		$wordtable = array();
		while ($word !== false) {
			if(!array_key_exists($word,$wordtable)){
				$wordtable[$word]=0;
			}
			$wordtable[$word] += 1;
			$word = strtok(' ');
		}
		arsort($wordtable);
		if ($num_terms < 1) $num_terms = 1;
		$wordtable = array_slice($wordtable, 0, $num_terms);

		foreach ($wordtable as $word => $count) {
			$terms .= ' ' . $word;
		}

		$res[] = $terms;
		$res[] = $results[0]['title'];
		$res[] = $results[0]['tags'];
 	}
	return $res;
}

function sp_save_index_entry($postID) {
	global $wpdb, $table_prefix;
	$table_name = $table_prefix . 'similar_posts';
	$post = $wpdb->get_row("SELECT post_content, post_title, post_type FROM $wpdb->posts WHERE ID = $postID", ARRAY_A);
	if ($post['post_type'] === 'revision') return $postID;
	//extract its terms
	$options = get_option('similar-posts');
	$utf8 = ($options['utf8'] === 'true');
	$cjk = ($options['cjk'] === 'true');
	$content = sp_get_post_terms($post['post_content'], $utf8, $options['use_stemmer'], $cjk);
	$title = sp_get_title_terms($post['post_title'], $utf8, $options['use_stemmer'], $cjk);
	$tags = sp_get_tag_terms($postID, $utf8);
	//check to see if the field is set
	$pid = $wpdb->get_var("SELECT pID FROM $table_name WHERE pID=$postID limit 1");
	//then insert if empty
	if (is_null($pid)) {
		$wpdb->query("INSERT INTO $table_name (pID, content, title, tags) VALUES ($postID, \"$content\", \"$title\", \"$tags\")");
	} else {
		$wpdb->query("UPDATE $table_name SET content=\"$content\", title=\"$title\", tags=\"$tags\" WHERE pID=$postID" );
	}
	return $postID;
}

function sp_delete_index_entry($postID) {
	global $wpdb, $table_prefix;
	$table_name = $table_prefix . 'similar_posts';
	$wpdb->query("DELETE FROM $table_name WHERE pID = $postID ");
	return $postID;
}

function sp_clean_words($text) {
	$text = strip_tags($text);
	$text = strtolower($text);
	$text = str_replace("’", "'", $text); // convert MSWord apostrophe
	$text = preg_replace(array('/\[(.*?)\]/', '/&[^\s;]+;/', '/‘|’|—|“|”|–|…/', "/'\W/"), ' ', $text); //anything in [..] or any entities or MS Word droppings
	return $text;
}

function sp_mb_clean_words($text) {
	mb_regex_encoding('UTF-8');
	mb_internal_encoding('UTF-8');
	$text = strip_tags($text);
	$text = mb_strtolower($text);
	$text = str_replace("’", "'", $text); // convert MSWord apostrophe
	$text = preg_replace(array('/\[(.*?)\]/u', '/&[^\s;]+;/u', '/‘|’|—|“|”|–|…/u', "/'\W/u"), ' ', $text); //anything in [..] or any entities
	return 	$text;
}

function sp_mb_str_pad($text, $n, $c) {
	mb_internal_encoding('UTF-8');
	$l = mb_strlen($text);
	if ($l > 0 && $l < $n) {
		$text .= str_repeat($c, $n-$l);
	}
	return $text;
}

function sp_cjk_digrams($string) {
	mb_internal_encoding("UTF-8");
    $strlen = mb_strlen($string);
	$ascii = '';
	$prev = '';
	$result = array();
	for ($i = 0; $i < $strlen; $i++) {
		$c = mb_substr($string, $i, 1);
		// single-byte chars get combined
		if (strlen($c) > 1) {
			if ($ascii) {
				$result[] = $ascii;
				$ascii = '';
				$prev = $c;
			} else {
				$result[] = sp_mb_str_pad($prev.$c, 4, '_');
				$prev = $c;
			}
		} else {
			$ascii .= $c;
		}
    }
	if ($ascii) $result[] = $ascii;
    return implode(' ', $result);
}

function sp_get_post_terms($text, $utf8, $use_stemmer, $cjk) {
	global $overusedwords;
	if ($utf8) {
		mb_regex_encoding('UTF-8');
		mb_internal_encoding('UTF-8');
		$wordlist = mb_split("\W+", sp_mb_clean_words($text));
		$words = '';
		foreach ($wordlist as $word) {
			if ( mb_strlen($word) > 3 && !isset($overusedwords[$word])) {
				switch ($use_stemmer) {
				case 'true':
					$words .= sp_mb_str_pad(stem($word), 4, '_') . ' ';
					break;
				case 'fuzzy':
					$words .= sp_mb_str_pad(metaphone($word), 4, '_') . ' ';
					break;
				case 'false':
				default:
					$words .= $word . ' ';
				}
			}
		}
	} else {
		$wordlist = str_word_count(sp_clean_words($text), 1);
		$words = '';
		foreach ($wordlist as $word) {
			if ( strlen($word) > 3 && !isset($overusedwords[$word])) {
				switch ($use_stemmer) {
				case 'true':
					$words .= str_pad(stem($word), 4, '_') . ' ';
					break;
				case 'fuzzy':
					$words .= str_pad(metaphone($word), 4, '_') . ' ';
					break;
				case 'false':
				default:
					$words .= $word . ' ';
				}
			}
		}
	}
	if ($cjk) $words = sp_cjk_digrams($words);
	return $words;
}

$tinywords = array('the' => 1, 'and' => 1, 'of' => 1, 'a' => 1, 'for' => 1, 'on' => 1);

function sp_get_title_terms($text, $utf8, $use_stemmer, $cjk) {
	global $tinywords;
	if ($utf8) {
		mb_regex_encoding('UTF-8');
		mb_internal_encoding('UTF-8');
		$wordlist = mb_split("\W+", sp_mb_clean_words($text));
		$words = '';
		foreach ($wordlist as $word) {
			if (!isset($tinywords[$word])) {
				switch ($use_stemmer) {
				case 'true':
					$words .= sp_mb_str_pad(stem($word), 4, '_') . ' ';
					break;
				case 'fuzzy':
					$words .= sp_mb_str_pad(metaphone($word), 4, '_') . ' ';
					break;
				case 'false':
				default:
					$words .= sp_mb_str_pad($word, 4, '_') . ' ';
				}
			}
		}
	} else {
		$wordlist = str_word_count(sp_clean_words($text), 1);
		$words = '';
		foreach ($wordlist as $word) {
			if (!isset($tinywords[$word])) {
				switch ($use_stemmer) {
				case 'true':
					$words .= str_pad(stem($word), 4, '_') . ' ';
					break;
				case 'fuzzy':
					$words .= str_pad(metaphone($word), 4, '_') . ' ';
					break;
				case 'false':
				default:
					$words .= str_pad($word, 4, '_') . ' ';
				}
			}
		}
	}
	if ($cjk) $words = sp_cjk_digrams($words);
	return $words;
}

function sp_get_tag_terms($ID, $utf8) {
	global $wpdb;
	if (!function_exists('get_object_term_cache')) return '';
	$tags = array();
	$query = "SELECT t.name FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = 'post_tag' AND tr.object_id = '$ID'";
	$tags = $wpdb->get_col($query);
	if (!empty ($tags)) {
		if ($utf8) {
			mb_internal_encoding('UTF-8');
			foreach ($tags as $tag) {
				$newtags[] = sp_mb_str_pad(mb_strtolower(str_replace('"', "'", $tag)), 4, '_');
			}
		} else {
			foreach ($tags as $tag) {
				$newtags[] = str_pad(strtolower(str_replace('"', "'", $tag)), 4, '_');
			}
		}
		$newtags = str_replace(' ', '_', $newtags);
		$tags = implode (' ', $newtags);
	} else {
		$tags = '';
	}
	return $tags;
}

if ( is_admin() ) {
	require(SPRP_DIR_NAME.'/admin/super-related-posts-admin.php');
}

function sp_is_user_allowed_to_add_php_code() {
	// If File Editing in Admin Area is disabled via override
	if ( defined( 'DISALLOW_FILE_EDIT' ) && true === DISALLOW_FILE_EDIT) {
		return false;
	 }

	// If current user has been given adequate permission
	if ( current_user_can( 'unfiltered_html' ) && current_user_can( 'edit_plugins' ) ) {
	 	return true;
	 }

    // Default to no edit
	return false;
}

global $overusedwords;
if(is_array($overusedwords)) {
	$overusedwords = array_flip($overusedwords);
}

function super_related_posts_wp_admin_style() {
  if (SuperRelatedPosts::is_plugin_admin_page('settings')) {
        wp_register_style( 'super-related-posts-admin', plugins_url('', __FILE__) . '/css/super-related-posts-admin.css', false, SuperRelatedPosts::$version );
        wp_enqueue_style( 'super-related-posts-admin' );
  }
}

function super_related_posts_init () {
	global $overusedwords, $wp_db_version;
	load_plugin_textdomain('similar_posts');

  SuperRelatedPosts::get_plugin_version();

	$options = get_option('similar-posts');
	if ($options['content_filter'] === 'true' && function_exists('ppl_register_content_filter')) ppl_register_content_filter('SuperRelatedPosts');
	$condition = 'true';
	$condition = (stristr($condition, "return")) ? $condition : "return ".$condition;
	$condition = rtrim($condition, '; ') . ';';

	ppl_register_post_filter('append', 'similar-posts', 'SuperRelatedPosts', $condition);
	
	ppl_register_post_filter_2('append', 'similar-posts', 'SuperRelatedPosts', $condition);
	
	ppl_register_post_filter_3('append', 'similar-posts', 'SuperRelatedPosts', $condition);

	//install the actions to keep the index up to date
	// add_action('save_post', 'sp_save_index_entry', 1);
	// add_action('delete_post', 'sp_delete_index_entry', 1);
	// if ($wp_db_version < 3308 ) {
	// 	add_action('edit_post', 'sp_save_index_entry', 1);
	// 	add_action('publish_post', 'sp_save_index_entry', 1);
	// }
	add_action( 'admin_enqueue_scripts', 'super_related_posts_wp_admin_style' );

  // aditional links in plugin description
  add_filter('plugin_action_links_' . basename(dirname(__FILE__)) . '/' . basename(__FILE__),
             array('SuperRelatedPosts', 'plugin_action_links'));
} // init

add_action ('init', 'super_related_posts_init', 1);
register_activation_hook(__FILE__, array('SuperRelatedPosts', 'activate'));

add_action('wp_enqueue_scripts', 'sprp_front_css');
function sprp_front_css(){
	wp_register_style( 'super-related-posts', plugins_url('', __FILE__) . '/css/super-related-posts.css', false, SuperRelatedPosts::$version );
	wp_enqueue_style( 'super-related-posts' );
}

add_action('wi_create_daily_backup', 'abcdefghijk');

function abcdefghijk(){
	error_log('write to error log');
}