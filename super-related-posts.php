<?php
/*
Plugin Name: Super Related Posts
Description: Add a highly configurable list of related posts to any posts. Related posts can be based on any combination of word usage in the content, title, or tags.
Version: 1.0
Text Domain: super-related-posts
Author: Magazine3
Author URI: https://magazine3.company/
Donate link: https://www.paypal.me/Kaludi/25
License: GPL2
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'SRPP_VERSION' ) ) {
    define( 'SRPP_VERSION', '1.0' );
}

define('SRPP_DIR_NAME', dirname( __FILE__ ));
define('SRPP_PLUGIN_URI', plugin_dir_url(__FILE__));
function super_related_posts($args = '') {
	echo SuperRelatedPosts::execute($args);
}

function super_related_posts_mark_current(){
	global $post, $sprp_current_ID;
	$sprp_current_ID = $post->ID;
}

define ('POST_PLUGIN_LIBRARY', true);

if (!defined('SRP_LIBRARY')) require(SRPP_DIR_NAME.'/includes/common_functions.php');
if (!defined('SRP_OT_LIBRARY')) require(SRPP_DIR_NAME.'/includes/output_tags.php');
if (!defined('ASRP_LIBRARY')) require(SRPP_DIR_NAME.'/admin/admin_common_functions.php');
if (!defined('SRP_ADMIN_SUBPAGES_LIBRARY')) require(SRPP_DIR_NAME.'/admin/admin-subpages.php');

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

    if ($page == 'settings' && $current_screen->id == 'settings_page_super-related-posts') {
      return true;
    }

    return false;
  } // is_plugin_admin_page

  // add settings link to plugins page
  static function plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=super-related-posts') . '" title="Settings for Super Related Posts">Settings</a>';

    array_unshift($links, $settings_link);

    return $links;
  } // plugin_action_links


	static function execute($args='', $default_output_template='<li>{imagesrc_shareaholic}</li>', $option_key='super-related-posts'){
		global $table_prefix, $wpdb, $wp_version, $sprp_current_ID, $srp_execute_sql_1, $srp_execute_result;
		$start_time = srp_microtime();
											
		$postid = srp_current_post_id($sprp_current_ID);
		$cache_key = $option_key.$postid.$args.'re1';
		$result = srp_cache_fetch($postid, $cache_key);
		if ($result !== false) {
			return $result . sprintf("<!-- Super Related Posts took %.3f ms (cached) -->", 1000 * (srp_microtime() - $start_time));
		}

		$table_name = $table_prefix . 'super_related_posts';
		// First we process any arguments to see if any defaults have been overridden
		$options = srp_parse_args($args);
		// Next we retrieve the stored options and use them unless a value has been overridden via the arguments
		$options = srp_set_options($option_key, $options, $default_output_template);
		
		if (0 < $options['limit']) {
			$match_tags = ($options['match_tags'] !== 'false' && $wp_version >= 2.3);			
			$match_category = ($options['match_cat'] === 'true');
			$sort_by       = $options['sort_by_1'];			
			$check_age = ('none' !== $options['age1']['direction']);			
			$limit = '0'.', '.$options['limit'];
			$des = isset($options['re_design_1']) ? $options['re_design_1'] : 'd1';
												
			$sql = "SELECT ID, post_title FROM `$wpdb->posts` p ";
			$sql .= " inner join `$table_name` sp on p.ID=sp.pID ";	
			$cat_ids = $tag_ids = array();
			if ($match_category){
				$cat_ids = where_match_category();
								
			}	
			if ($match_tags){			
				$tag_ids     = where_match_tags();				
			}
			
			if($cat_ids){				
				$wp_term_re   = $table_prefix.'term_relationships';
				$wp_terms     = $table_prefix.'terms';
				$wp_term_taxo = $table_prefix.'term_taxonomy';

				$sql .="inner join `$wp_term_re` tt on tt.object_id = p.ID
						inner join `$wp_terms` te on tt.term_taxonomy_id = te.term_id
						inner join `$wp_term_taxo` tte on tte.term_taxonomy_id = te.term_id			
						and tte.taxonomy = 'category'
						and tt.term_taxonomy_id = $cat_ids[0] "; 				
			}

			if($tag_ids){				
				$wp_term_re   = $table_prefix.'term_relationships';
				$wp_terms     = $table_prefix.'terms';
				$wp_term_taxo = $table_prefix.'term_taxonomy';

				$sql .="inner join `$wp_term_re` tr on tr.object_id = p.ID
						inner join `$wp_terms` t on tr.term_taxonomy_id = t.term_id
						inner join `$wp_term_taxo` tts on tts.term_taxonomy_id = t.term_id			
						and tts.taxonomy = 'post_tag'
						and tr.term_taxonomy_id = $tag_ids[0]"; 				
			}
			
			if($sort_by == 'recent'){
				$sql .= " ORDER BY id DESC LIMIT $limit";	
			}else{
				if ($check_age) {				
					$sql .= ' AND '.where_check_age($options['age1']['direction'], $options['age1']['length'], $options['age1']['duration']);				
				}
				$sql .= " ORDER BY sp.views DESC LIMIT $limit";	
			}					
							
			$cpost_id 		   = where_omit_post($sprp_current_ID);			
			
			$srp_execute_sql_1 = $sql;			
			$results = array();
						
			$fetch_result = $wpdb->get_results($sql);
			if(!empty($fetch_result)){
				foreach ($fetch_result as $value) {					
					if($value->ID == $cpost_id) {
						continue;
					}
					$results[] = $value;
				}
			}			
			
			$srp_execute_result = $results;			
		} else {
			$results = false;
		}
	    if ($results) {
			
			$translations = srp_prepare_template($options['output_template']);
			foreach ($results as $result) {
				$items[] = srp_expand_template($result, $options['output_template'], $translations, $option_key);
			}
			if ($options['sort']['by1'] !== '') $items = srp_sort_items($options['sort'], $results, $option_key, $items);
			$output = implode(($options['divider']) ? $options['divider'] : "\n", $items);
			//Output
			$output = '<div class="sprp '.$des.'"><h2>Related Content</h2><ul>' . $output . '</ul></div>';
		} else {
			// if we reach here our query has produced no output ... so what next?
			if ($options['no_text'] !== 'false') {
				$output = ''; // we display nothing at all
			} else {
				// we display the blank message, with tags expanded if necessary
				$translations = srp_prepare_template($options['none_text']);
				$output = $options['prefix'] . srp_expand_template(array(), $options['none_text'], $translations, $option_key) . $options['suffix'];
			}
		}
		
		if($output){
			srp_cache_store($postid, $cache_key, $output);
		}
		
		return ($output) ? $output . sprintf("<!-- Super Related Posts took %.3f ms -->", 1000 * (srp_microtime() - $start_time)) : '';
	}

	static function execute2($args='', $default_output_template='<li>{link}</li>', $option_key='super-related-posts'){
		
		global $table_prefix, $wpdb, $wp_version, $sprp_current_ID, $srp_execute_sql_1, $srp_execute_sql_2, $srp_execute_result;
		$start_time = srp_microtime();
		$postid = srp_current_post_id($sprp_current_ID);	

		$cache_key = $option_key.$postid.$args.'re2';
		$result = srp_cache_fetch($postid, $cache_key);
		if ($result !== false) {
			return $result . sprintf("<!-- Super Related Posts took %.3f ms (cached) -->", 1000 * (srp_microtime() - $start_time));
		}

		$table_name = $table_prefix . 'super_related_posts';
		// First we process any arguments to see if any defaults have been overridden
		$options = srp_parse_args($args);
		// Next we retrieve the stored options and use them unless a value has been overridden via the arguments
		$options = srp_set_options($option_key, $options, $default_output_template);
		if (0 < $options['limit_2']) {
			$match_tags = ($options['match_tags_2'] !== 'false' && $wp_version >= 2.3);			
			$match_category = ($options['match_cat_2'] === 'true');
			$sort_by       = $options['sort_by_2'];				
			$use_tag_str = ('' != trim($options['tag_str_2']) && $wp_version >= 2.3);
			$check_age = ('none' !== $options['age2']['direction']);
			$check_custom = (trim($options['custom']['key']) !== '');
			$limit = '0'.', '.$options['limit_2'];
			$des = isset($options['re_design_2']) ? $options['re_design_2'] : 'd1';

			
			$sql = "SELECT ID, post_title FROM `$wpdb->posts` p ";
			$sql .= " inner join `$table_name` sp on p.ID=sp.pID ";	
			$cat_ids = $tag_ids = array();
			if ($match_category){
				$cat_ids = where_match_category();
								
			}	
			if ($match_tags){			
				$tag_ids     = where_match_tags();				
			}
			
			if($cat_ids){				
				$wp_term_re   = $table_prefix.'term_relationships';
				$wp_terms     = $table_prefix.'terms';
				$wp_term_taxo = $table_prefix.'term_taxonomy';

				$sql .="inner join `$wp_term_re` tt on tt.object_id = p.ID
						inner join `$wp_terms` te on tt.term_taxonomy_id = te.term_id
						inner join `$wp_term_taxo` tte on tte.term_taxonomy_id = te.term_id			
						and tte.taxonomy = 'category'
						and tt.term_taxonomy_id = $cat_ids[0] "; 				
			}

			if($tag_ids){				
				$wp_term_re   = $table_prefix.'term_relationships';
				$wp_terms     = $table_prefix.'terms';
				$wp_term_taxo = $table_prefix.'term_taxonomy';

				$sql .="inner join `$wp_term_re` tr on tr.object_id = p.ID
						inner join `$wp_terms` t on tr.term_taxonomy_id = t.term_id
						inner join `$wp_term_taxo` tts on tts.term_taxonomy_id = t.term_id			
						and tts.taxonomy = 'post_tag'
						and tr.term_taxonomy_id = $tag_ids[0]"; 				
			}
			
			if($sort_by == 'recent'){
				$sql .= " ORDER BY id DESC LIMIT $limit";	
			}else{
				if ($check_age) {				
					$sql .= ' AND '.where_check_age($options['age1']['direction'], $options['age1']['length'], $options['age1']['duration']);				
				}
				$sql .= " ORDER BY sp.views DESC LIMIT $limit";	
			}					
							
			$cpost_id 		   = where_omit_post($sprp_current_ID);			
			if($srp_execute_sql_1 === $sql){				
				$sql =  strstr($sql, 'LIMIT', true);
				$sql.= "LIMIT ".($options['limit']+1).",".$options['limit_2']; 
			}
			
			$srp_execute_sql_2 = $sql;					
			$results = array();
			$fetch_result = $wpdb->get_results($sql);
			if(!empty($fetch_result)){
				foreach ($fetch_result as $value) {					
					if($value->ID == $cpost_id) {
						continue;
					}
					$results[] = $value;
				}
			}
						
			
		} else {
			$results = false;
		}
	    if ($results) {
			$translations = srp_prepare_template($options['output_template']);
			foreach ($results as $result) {
				$items[] = srp_expand_template($result, $options['output_template'], $translations, $option_key);
			}
			if ($options['sort']['by1'] !== '') $items = srp_sort_items($options['sort'], $results, $option_key, $items);
			$output = implode(($options['divider']) ? $options['divider'] : "\n", $items);
			//Output
			$output = '<div class="sprp '.$des.'"><h2>Related Content</h2><ul>' . $output . '</ul></div>';
		} else {
			// if we reach here our query has produced no output ... so what next?
			if ($options['no_text'] !== 'false') {
				$output = ''; // we display nothing at all
			} else {
				// we display the blank message, with tags expanded if necessary
				$translations = srp_prepare_template($options['none_text']);
				$output = $options['prefix'] . srp_expand_template(array(), $options['none_text'], $translations, $option_key) . $options['suffix'];
			}
		}
		if($output){
			srp_cache_store($postid, $cache_key, $output);
		}		
		return ($output) ? $output . sprintf("<!-- Super Related Posts took %.3f ms -->", 1000 * (srp_microtime() - $start_time)) : '';
	}

	static function execute3($args='', $default_output_template='<li>{link}</li>', $option_key='super-related-posts'){
		global $table_prefix, $wpdb, $wp_version, $sprp_current_ID, $srp_execute_sql_1, $srp_execute_sql_2, $srp_execute_sql_3, $srp_execute_result;
		$start_time = srp_microtime();		
		$postid = srp_current_post_id($sprp_current_ID);

		$cache_key = $option_key.$postid.$args.'re3';
		$result = srp_cache_fetch($postid, $cache_key);
		if ($result !== false)
		{
			return $result . sprintf("<!-- Super Related Posts took %.3f ms (cached) -->", 1000 * (srp_microtime() - $start_time));
		}
		
		$table_name = $table_prefix . 'super_related_posts';
		// First we process any arguments to see if any defaults have been overridden
		$options = srp_parse_args($args);
		// Next we retrieve the stored options and use them unless a value has been overridden via the arguments
		$options = srp_set_options($option_key, $options, $default_output_template);
		if (0 < $options['limit_3']) {
			$match_tags = ($options['match_tags_3'] !== 'false' && $wp_version >= 2.3);			
			$sort_by       = $options['sort_by_3'];
			$match_category = ($options['match_cat_3'] === 'true');			
			$check_age = ('none' !== $options['age3']['direction']);			
			$limit = '0'.', '.$options['limit_3'];
			$des = isset($options['re_design_3']) ? $options['re_design_3'] : 'd1';

			
			$sql = "SELECT ID, post_title FROM `$wpdb->posts` p ";
			$sql .= " inner join `$table_name` sp on p.ID=sp.pID ";	
			$cat_ids = $tag_ids = array();
			if ($match_category){
				$cat_ids = where_match_category();
								
			}	
			if ($match_tags){			
				$tag_ids     = where_match_tags();				
			}
			
			if($cat_ids){				
				$wp_term_re   = $table_prefix.'term_relationships';
				$wp_terms     = $table_prefix.'terms';
				$wp_term_taxo = $table_prefix.'term_taxonomy';

				$sql .="inner join `$wp_term_re` tt on tt.object_id = p.ID
						inner join `$wp_terms` te on tt.term_taxonomy_id = te.term_id
						inner join `$wp_term_taxo` tte on tte.term_taxonomy_id = te.term_id			
						and tte.taxonomy = 'category'
						and tt.term_taxonomy_id = $cat_ids[0] "; 				
			}

			if($tag_ids){				
				$wp_term_re   = $table_prefix.'term_relationships';
				$wp_terms     = $table_prefix.'terms';
				$wp_term_taxo = $table_prefix.'term_taxonomy';

				$sql .="inner join `$wp_term_re` tr on tr.object_id = p.ID
						inner join `$wp_terms` t on tr.term_taxonomy_id = t.term_id
						inner join `$wp_term_taxo` tts on tts.term_taxonomy_id = t.term_id			
						and tts.taxonomy = 'post_tag'
						and tr.term_taxonomy_id = $tag_ids[0]"; 				
			}
			
			if($sort_by == 'recent'){
				$sql .= " ORDER BY id DESC LIMIT $limit";	
			}else{
				if ($check_age) {				
					$sql .= ' AND '.where_check_age($options['age1']['direction'], $options['age1']['length'], $options['age1']['duration']);				
				}
				$sql .= " ORDER BY sp.views DESC LIMIT $limit";	
			}					
							
			$cpost_id 		   = where_omit_post($sprp_current_ID);			

			if($sql === $srp_execute_sql_1 || $sql === $srp_execute_sql_2){							
				$sql =  strstr($sql, 'LIMIT', true);
				$sql.= "LIMIT ".($options['limit'] + $options['limit_2'] + 1).",".$options['limit_3']; 
			}
			$srp_execute_sql_3 = $sql;			
			$results = array();
			$fetch_result = $wpdb->get_results($sql);
			if(!empty($fetch_result)){
				foreach ($fetch_result as $value) {					
					if($value->ID == $cpost_id) {
						continue;
					}
					$results[] = $value;
				}
			}			
			
			$srp_execute_result = $results;			
						
		} else {
			$results = false;
		}
	    if ($results) {
			$translations = srp_prepare_template($options['output_template']);
			foreach ($results as $result) {
				$items[] = srp_expand_template($result, $options['output_template'], $translations, $option_key);
			}
			if ($options['sort']['by1'] !== '') $items = srp_sort_items($options['sort'], $results, $option_key, $items);
			$output = implode(($options['divider']) ? $options['divider'] : "\n", $items);
			//Output
			$output = '<div class="sprp '.$des.'"><h2>Related Content</h2><ul>' . $output . '</ul></div>';
		} else {
			// if we reach here our query has produced no output ... so what next?
			if ($options['no_text'] !== 'false') {
				$output = ''; // we display nothing at all
			} else {
				// we display the blank message, with tags expanded if necessary
				$translations = srp_prepare_template($options['none_text']);
				$output = $options['prefix'] . srp_expand_template(array(), $options['none_text'], $translations, $option_key) . $options['suffix'];
			}
		}
		if($output){
			srp_cache_store($postid,$cache_key, $output);
		}
				
		return ($output) ? $output . sprintf("<!-- Super Related Posts took %.3f ms -->", 1000 * (srp_microtime() - $start_time)) : '';
	}

  // save some info
  static function activate() {
    $options = get_option('super_related_posts_meta', array());

    if (empty($options['first_version'])) {
      $options['first_version'] = SuperRelatedPosts::get_plugin_version();
      $options['first_install'] = current_time('timestamp');
      update_option('super_related_posts_meta', $options);
    }
  } // activate

} // SuperRelatedPosts class

function sp_terms_by_freq($ID, $num_terms = 20) {
	if (!$ID) return array('', '', '');
	global $wpdb, $table_prefix;
	$table_name = $table_prefix . 'super_related_posts';	
	$results = $wpdb->get_results("SELECT title, tags FROM $table_name WHERE pID=$ID LIMIT 1", ARRAY_A);
	if ($results) {		
		$res[] = $results[0]['title'];
		$res[] = $results[0]['tags'];
 	}
	return $res;
}

function sp_save_index_entry($postID) {
	global $wpdb, $table_prefix;
	$table_name = $table_prefix . 'super_related_posts';
	$post = $wpdb->get_row("SELECT post_content, post_date, post_title, post_type FROM $wpdb->posts WHERE ID = $postID", ARRAY_A);
	if ($post['post_type'] === 'revision') return $postID;
	//extract its terms
	$options = get_option('super-related-posts');
	$utf8 = (isset($options['utf8']) && $options['utf8'] === 'true');
	$cjk = (isset($options['cjk']) && $options['cjk'] === 'true');
	$use_stemmer = '';
	if(isset($options['use_stemmer'])){
		$use_stemmer = $options['use_stemmer'];
	}	
	$title = sp_get_title_terms($post['post_title'], $utf8, $use_stemmer, $cjk);
	$tags = sp_get_tag_terms($postID, $utf8);
	$sdate  = date("Ymd",strtotime($post['post_date']));	
	//check to see if the field is set
	$pid = $wpdb->get_var("SELECT pID FROM $table_name WHERE pID=$postID limit 1");
	//then insert if empty
	if (is_null($pid)) {
		$wpdb->query("INSERT INTO $table_name (pID, title, tags, spost_date) VALUES ($postID, \"$title\", \"$tags\", \"$sdate\")");
	} else {
		$wpdb->query("UPDATE $table_name SET title=\"$title\", tags=\"$tags\", spost_date=\"$sdate\" WHERE pID=$postID" );
	}
	return $postID;
}

function sp_delete_index_entry($postID) {
	global $wpdb, $table_prefix;
	$table_name = $table_prefix . 'super_related_posts';
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
	require(SRPP_DIR_NAME.'/admin/super-related-posts-admin.php');
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
	load_plugin_textdomain('super_related_posts');

  SuperRelatedPosts::get_plugin_version();

	$options = get_option('super-related-posts');
	if (isset($options['content_filter']) && $options['content_filter'] === 'true' && function_exists('srp_register_content_filter')) srp_register_content_filter('SuperRelatedPosts');
	$condition = 'true';
	$condition = (stristr($condition, "return")) ? $condition : "return ".$condition;
	$condition = rtrim($condition, '; ') . ';';

	srp_register_post_filter('append', 'super-related-posts', 'SuperRelatedPosts', $condition);
	
	srp_register_post_filter_2('append', 'super-related-posts', 'SuperRelatedPosts', $condition);
	
	srp_register_post_filter_3('append', 'super-related-posts', 'SuperRelatedPosts', $condition);

	//install the actions to keep the index up to date
	add_action('save_post', 'sp_save_index_entry', 1);
	add_action('delete_post', 'sp_delete_index_entry', 1);
	if ($wp_db_version < 3308 ) {
		add_action('edit_post', 'sp_save_index_entry', 1);
		add_action('publish_post', 'sp_save_index_entry', 1);
	}
	add_action( 'admin_enqueue_scripts', 'super_related_posts_wp_admin_style' );

  // aditional links in plugin description
  add_filter('plugin_action_links_' . basename(dirname(__FILE__)) . '/' . basename(__FILE__),
             array('SuperRelatedPosts', 'plugin_action_links'));
} // init

add_action ('init', 'super_related_posts_init', 1);
register_activation_hook(__FILE__, array('SuperRelatedPosts', 'activate'));

add_action('wp_enqueue_scripts', 'sprp_front_css_and_js');

function sprp_front_css_and_js(){

	wp_register_style( 'super-related-posts', plugins_url('', __FILE__) . '/css/super-related-posts.css', false, SuperRelatedPosts::$version );
	wp_enqueue_style( 'super-related-posts' );

	$local = array(     		   
		'ajax_url'                     => admin_url( 'admin-ajax.php' ),            
		'srp_security_nonce'           => wp_create_nonce('srp_ajax_check_nonce'),
		'post_id'                      => get_the_ID()
	);            

	$local = apply_filters('srp_front_data',$local,'srp_localize_front_data');

	wp_register_script( 'srp-front-js', SRPP_PLUGIN_URI . 'js/srp.js', array('jquery'), SuperRelatedPosts::$version , true );                        
	wp_localize_script( 'srp-front-js', 'srp_localize_front_data', $local );        
	wp_enqueue_script( 'srp-front-js');
}

add_action( 'wp_ajax_nopriv_srp_update_post_views_ajax', 'srp_update_post_views_via_ajax');  
add_action( 'wp_ajax_srp_update_post_views_ajax', 'srp_update_post_views_via_ajax') ;  

function srp_update_post_views_via_ajax(){

	 if ( ! isset( $_POST['srp_security_nonce'] ) ){
		return; 
	 }
	 
	 if ( !wp_verify_nonce( $_POST['srp_security_nonce'], 'srp_ajax_check_nonce' ) ){
		return;  
	 }
   
	if(isset($_POST['post_id'])){

	   $post_id = intval($_POST['post_id']);	   

	   try{
    
		global $wpdb;

		$count = $wpdb->get_var($wpdb->prepare( "SELECT views FROM {$wpdb->prefix}super_related_posts WHERE pID = %d ", $post_id) );
		$count++;	
		$wpdb->query($wpdb->prepare(
			"UPDATE {$wpdb->prefix}super_related_posts SET `views` = '{$count}' WHERE (`pID` = %d)",
			$post_id			
		));				
		if($wpdb->last_error){            
			echo json_encode(array('status' => 'error', 'message' => $wpdb->last_error));
		}else{
			echo json_encode(array('status' => 'Post Views Updated'));            
		}
		
		} catch (\Exception $ex) {
			echo json_encode(array('status' => 'error', 'message' => $ex->getMessage()));			
		}

	}
	
	wp_die();
					
}