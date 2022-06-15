<?php

/*
	Library for the Recent Posts, Random Posts, Recent Comments, and Super Related Posts plugins
	-- provides the routines which the plugins share
*/

define('SRP_LIBRARY', true);

function srp_get_transient_keys_with_prefix( $prefix ) {

	global $wpdb;

	$prefix = stripslashes($wpdb->esc_like( '_transient_' . $prefix ));	
	$sql    = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
	$keys   = $wpdb->get_results( $wpdb->prepare( $sql, $prefix . '%' ), ARRAY_A );	
	
	if ( is_wp_error( $keys ) ) {
		return [];
	}

	return array_map( function( $key ) {
		return str_replace('_transient_', '', $key['option_name']);
	}, $keys );
}

function srp_cache_flush(){
	$prefix = 'super-related-posts';
	foreach ( srp_get_transient_keys_with_prefix( $prefix ) as $key ) {				
		delete_transient( trim($key) );
	}
}

function srp_cache_fetch($cache_key){	
	return get_transient($cache_key);
}

function srp_cache_store($cache_key, $output){

	set_transient( $cache_key, $output, 24 * 7 * HOUR_IN_SECONDS );
}

function srp_parse_args($args) {
	// 	$args is of the form 'key1=val1&key2=val2'
	//	The code copes with null values, e.g., 'key1=&key2=val2'
	//	and arguments with embedded '=', e.g. 'output_template=<li class="stuff">{...}</li>'.
	$result = array();
	if($args){
		// the default separator is '&' but you may wish to include the character in a title, say,
		// so you can specify an alternative separator by making the first character of $args
		// '&' and the second character your new separator...
		if (substr($args, 0, 1) === '&') {
			$s = substr($args, 1, 1);
			$args = substr($args, 2);
		} else {
			$s = '&';
		}
		// separate the arguments into key=value pairs
		$arguments = explode($s, $args);
		foreach($arguments as $arg){
			if($arg){
				// find the position of the first '='
				$i = strpos($arg, '=');
				// if not a valid format ('key=value) we ignore it
				if ($i){
					$key = substr($arg, 0, $i);
					$val = substr($arg, $i+1);
					$result[$key]=$val;
				}
			}
		}
	}
	return $result;
}

function srp_set_options($option_key, $arg, $default_output_template) {
	$options = get_option($option_key);	
	// deal with compound options
	if (isset($arg['custom-key'])) {$arg['custom']['key'] = $arg['custom-key']; unset($arg['custom-key']);}
	if (isset($arg['custom-op'])) {$arg['custom']['op'] = $arg['custom-op']; unset($arg['custom-op']);}
	if (isset($arg['custom-value'])) {$arg['custom']['value'] = $arg['custom-value']; unset($arg['custom-value']);}

	if (isset($arg['age-direction_1'])) {$arg['age_1']['direction'] = $arg['age-direction_1']; unset($arg['age-direction_1']);}
	if (isset($arg['age-length_1'])) {$arg['age_1']['length'] = $arg['age-length_1']; unset($arg['age-length_1']);}
	if (isset($arg['age-duration_1'])) {$arg['age_1']['duration'] = $arg['age-duration_1']; unset($arg['age-duration_1']);}

	if (isset($arg['sort-by1'])) {$arg['sort']['by1'] = $arg['sort-by1']; unset($arg['sort-by1']);}
	if (isset($arg['sort-by2'])) {$arg['sort']['by2'] = $arg['sort-by2']; unset($arg['sort-by2']);}
	// then fill in the defaults
	if (!isset($arg['limit'])) $arg['limit'] = stripslashes(@$options['limit']);
	if (!isset($arg['limit_2'])) $arg['limit_2'] = stripslashes(@$options['limit_2']);
	if (!isset($arg['limit_3'])) $arg['limit_3'] = stripslashes(@$options['limit_3']);
	if (!isset($arg['skip'])) $arg['skip'] = stripslashes(@$options['skip']);
	$arg['omit_current_post'] = 'true';
	if (!isset($arg['just_current_post'])) $arg['just_current_post'] = @$options['just_current_post'];
	if (!isset($arg['tag_str'])) $arg['tag_str'] = stripslashes(@$options['tag_str']);
	if (!isset($arg['tag_str_2'])) $arg['tag_str_2'] = stripslashes(@$options['tag_str']);
	if (!isset($arg['tag_str_3'])) $arg['tag_str_3'] = stripslashes(@$options['tag_str']);
	if (!isset($arg['excluded_cats'])) $arg['excluded_cats'] = stripslashes(@$options['excluded_cats']);
	if (!isset($arg['included_cats'])) $arg['included_cats'] = stripslashes(@$options['included_cats']);
	if (!isset($arg['excluded_authors'])) $arg['excluded_authors'] = stripslashes(@$options['excluded_authors']);
	if (!isset($arg['included_authors'])) $arg['included_authors'] = stripslashes(@$options['included_authors']);

	if (!isset($arg['display_status_1'])) $arg['display_status_1'] = stripslashes(@$options['display_status_1']);
	if (!isset($arg['display_status_2'])) $arg['display_status_2'] = stripslashes(@$options['display_status_2']);
	if (!isset($arg['display_status_3'])) $arg['display_status_3'] = stripslashes(@$options['display_status_3']);

	if (!isset($arg['sort_by_1'])) $arg['sort_by_1'] = stripslashes(@$options['sort_by_1']);
	if (!isset($arg['sort_by_2'])) $arg['sort_by_2'] = stripslashes(@$options['sort_by_2']);
	if (!isset($arg['sort_by_3'])) $arg['sort_by_3'] = stripslashes(@$options['sort_by_3']);

	if (!isset($arg['adv_filter_check'])) $arg['adv_filter_check'] = stripslashes(@$options['adv_filter_check']);
	if (!isset($arg['adv_filter_check_2'])) $arg['adv_filter_check_2'] = stripslashes(@$options['adv_filter_check']);
	if (!isset($arg['adv_filter_check_3'])) $arg['adv_filter_check_3'] = stripslashes(@$options['adv_filter_check']);
	if (!isset($arg['excluded_posts'])) $arg['excluded_posts'] = stripslashes(@$options['excluded_posts']);
	if (!isset($arg['excluded_posts_2'])) $arg['excluded_posts_2'] = stripslashes(@$options['excluded_posts']);
	if (!isset($arg['excluded_posts_3'])) $arg['excluded_posts_3'] = stripslashes(@$options['excluded_posts']);
	if (!isset($arg['included_posts'])) $arg['included_posts'] = stripslashes(@$options['included_posts']);
	if (!isset($arg['included_posts_2'])) $arg['included_posts_2'] = stripslashes(@$options['included_posts']);
	if (!isset($arg['included_posts_3'])) $arg['included_posts_3'] = stripslashes(@$options['included_posts']);
	if (!isset($arg['re_design_1'])) $arg['re_design_1'] = stripslashes(@$options['re_design_1']);
	if (!isset($arg['re_design_2'])) $arg['re_design_2'] = stripslashes(@$options['re_design_2']);
	if (!isset($arg['re_design_3'])) $arg['re_design_3'] = stripslashes(@$options['re_design_3']);
	if (!isset($arg['stripcodes'])) $arg['stripcodes'] = @$options['stripcodes'];
	$arg['output_template'] = $default_output_template;
	if (!isset($arg['match_cat'])) $arg['match_cat'] = @$options['match_cat'];
	if (!isset($arg['match_cat_2'])) $arg['match_cat_2'] = @$options['match_cat'];
	if (!isset($arg['match_cat_3'])) $arg['match_cat_3'] = @$options['match_cat'];
	if (!isset($arg['match_tags'])) $arg['match_tags'] = @$options['match_tags'];
	if (!isset($arg['match_tags_2'])) $arg['match_tags_2'] = @$options['match_tags_2'];
	if (!isset($arg['match_tags_3'])) $arg['match_tags_3'] = @$options['match_tags_3'];
	if (!isset($arg['match_author'])) $arg['match_author'] = @$options['match_author'];
	if (!isset($arg['age'])) $arg['age'] = @$options['age'];
	if (!isset($arg['custom'])) $arg['custom'] = @$options['custom'];
	if (!isset($arg['sort'])) $arg['sort'] = @$options['sort'];
	if (!isset($arg['status'])) $arg['status'] = @$options['status'];

	// just for recent_posts
	if (!isset($arg['date_modified'])) $arg['date_modified'] = @$options['date_modified'];

	// just for recent_comments
	if (!isset($arg['group_by'])) $arg['group_by'] = @$options['group_by'];
	if (!isset($arg['group_template'])) $arg['group_template'] = stripslashes(@$options['group_template']);
	if (!isset($arg['show_type'])) $arg['show_type'] = @$options['show_type'];
	if (!isset($arg['no_author_comments'])) $arg['no_author_comments'] = @$options['no_author_comments'];
	if (!isset($arg['no_user_comments'])) $arg['no_user_comments'] = @$options['no_user_comments'];
	if (!isset($arg['unique'])) $arg['unique'] = @$options['unique'];

	// just for super_related_posts[feed]
	if (!isset($arg['combine'])) $arg['combine'] = @$options['crossmatch'];
	if (!isset($arg['weight_content'])) $arg['weight_content'] = @$options['weight_content'];
	if (!isset($arg['weight_title'])) $arg['weight_title'] = @$options['weight_title'];
	if (!isset($arg['weight_tags'])) $arg['weight_tags'] = @$options['weight_tags'];
	if (!isset($arg['num_terms'])) $arg['num_terms'] = stripslashes(@$options['num_terms']);
	if (!isset($arg['term_extraction'])) $arg['term_extraction'] = @$options['term_extraction'];
	if (!isset($arg['hand_links'])) $arg['hand_links'] = @$options['hand_links'];

	// just for other_posts
	if (!isset($arg['orderby'])) $arg['orderby'] = stripslashes(@$options['orderby']);
	if (!isset($arg['orderby_order'])) $arg['orderby_order'] = @$options['orderby_order'];
	if (!isset($arg['orderby_case'])) $arg['orderby_case'] = @$options['orderby_case'];

	// the last options cannot be set via arguments
	$arg['stripcodes'] = @$options['stripcodes'];
	$arg['utf8'] = @$options['utf8'];
	$arg['cjk'] = @$options['cjk'];
	$arg['use_stemmer'] = @$options['use_stemmer'];
	$arg['batch'] = @$options['batch'];
	$arg['exclude_users'] = @$options['exclude_users'];
	$arg['count_home'] = @$options['count_home'];
	$arg['count_feed'] = @$options['count_feed'];
	$arg['count_single'] = @$options['count_single'];
	$arg['count_archive'] = @$options['count_archive'];
	$arg['count_category'] = @$options['count_category'];
	$arg['count_page'] = @$options['count_page'];
	$arg['count_search'] = @$options['count_search'];

	return $arg;
}

function srp_prepare_template($template) {
	// Now we process the output_template to find the embedded tags which are to be replaced
	// with values taken from the database.
	// A tag is of the form, {tag:ext}, where the tag part will be evaluated and replaced
	// and the optional ext part provides extra data pertinent to that tag


	preg_match_all('/{((?:[^{}]|{[^{}]*})*)}/', $template, $matches);
	$translations = array();
	if(is_array($matches)){

		foreach($matches[1] as $match) {
			if(strpos($match,':')!==false){
				list($tag, $ext) = explode(':', $match, 2);
			} else {
				$tag = $match;
				$ext = false;
			}
			$action = output_tag_action($tag);
			if (function_exists($action)) {
				// store the action that instantiates the tag
				$translations['acts'][] = $action;
				// add the tag in a form ready to use in translation later
				$translations['fulltags'][] = '{'.$match.'}';
				// the extra data if any
				$translations['exts'][] = $ext;
			}
		}
	}
	return $translations;
}

function srp_expand_template($result, $template, $translations, $option_key) {
	global $wpdb, $wp_version;
	$replacements = array();

	if(array_key_exists('fulltags',$translations)){
		$numtags = count($translations['fulltags']);
		for ($i = 0; $i < $numtags; $i++) {
			$fulltag = $translations['fulltags'][$i];
			$act = $translations['acts'][$i];
			$ext = $translations['exts'][$i];
			$replacements[$fulltag] = $act($option_key, $result, $ext);
		}
	}
	// Replace every valid tag with its value
	$tmp = strtr($template, $replacements)."\n";
	return $tmp;
}


function srp_sort_items($sort, $results, $option_key, $items) {
	$translations1 = srp_prepare_template($sort['by1']);
	foreach ($results as $result) {
		$key1 = srp_expand_template($result, $sort['by1'], $translations1, $option_key);
		if ($sort['case1'] !== 'false') $key1 = strtolower($key1);
		$keys1[] = $key1;
	}
	if ($sort['by2'] !== '') {
		$translations2 = srp_prepare_template($sort['by2']);
		foreach ($results as $result) {
			$key2 = srp_expand_template($result, $sort['by2'], $translations2, $option_key);
			if ($sort['case2'] !== 'false') $key2 = strtolower($key2);
			$keys2[] = $key2;
		}
	}
	if (!empty($keys2)) {
		array_multisort($keys1, intval($sort['order1']), $keys2, intval($sort['order2']), $results, $items);
	} else {
		array_multisort($keys1, intval($sort['order1']), $results, $items);
	}
	// merge the group titles into the items
	if ($group_template) {
		$group_translations = srp_prepare_template($group_template);
		$prev_key = '';
		$insertions = 0;
		foreach ($keys1 as $n => $key) {
			if ($prev_key !== $key) {
				array_splice($items, $n+$insertions, 0, srp_expand_template($results[$n], $group_template, $group_translations, $option_key));
				$insertions++;
			}
			$prev_key = $key;
		}
	}
	return $items;
}

// the $post global can be overwritten by the use of $wp_query so we go back to the source
// note the addition of a 'manual overide' allowing the current posts to me marked by super_related_posts_mark_current for example
function srp_current_post_id ($manual_current_ID = -1) {
	$the_ID = -1;
	if ($manual_current_ID > 0) {
		$the_ID = $manual_current_ID;
	} else if (isset($GLOBALS['wp_the_query'])) {
		$the_ID = $GLOBALS['wp_the_query']->post->ID;
		if (!$the_ID) {
			$the_ID = $GLOBALS['wp_the_query']->posts[0]->ID;
		}
	} else {
		$the_ID = $GLOBALS['post']->ID;
	}
	return $the_ID;
}


/*

	Functions to fill in the WHERE part of the workhorse SQL

*/

function where_match_tags($match_tags) {
	global $wpdb, $wp_version;
	$args = array('fields' => 'ids');
	$tag_ids = wp_get_object_terms(srp_current_post_id(), 'post_tag', $args);
	if ( is_array($tag_ids) && count($tag_ids) > 0 )  {
		if ($match_tags === 'any') {
			$ids = get_objects_in_term($tag_ids, 'post_tag');
		} else {
			$ids = array();
			foreach ($tag_ids as $tag_id){
				if (count($ids) > 0) {
					$ids = array_intersect($ids, get_objects_in_term($tag_id, 'post_tag'));
				} else {
					$ids = get_objects_in_term($tag_id, 'post_tag');
				}
			}
		}
		if ( is_array($ids) && count($ids) > 0 ) {
			$ids = array_unique($ids);
			$out_posts = "'" . implode("', '", $ids) . "'";
			$sql = "$wpdb->posts.ID IN ($out_posts)";
		} else {
			$sql = "1 = 2";
		}
	} else {
		$sql = "1 = 2";
	}
	return $sql;
}

function where_show_status($status, $include_inherit='false') {
	$set = array();
	$status = (array) $status;
	// a quick way of allowing for attachments having status=inherit
	if ($include_inherit === 'true') $status['inherit'] = 'true';
	foreach ($status as $name => $state) {
		if ($state === 'true') $set[] = "'$name'";
	}
	if ($set) {
		$result = implode(',', $set);
		return "post_status IN ($result)";
	} else {
		return "1 = 2";
	}
}

// a replacement, for WP < 2.3, ONLY category children
if (!function_exists('get_term_children')) {
	function get_term_children($term, $taxonomy) {
		if ($taxonomies !== 'category') return array();
		return get_categories('child_of='.$term);
	}
}


// a replacement, for WP < 2.3, ONLY to get posts with given category IDs
if (!function_exists('get_objects_in_term')) {
	function get_objects_in_term($terms, $taxonomies) {
		global $wpdb;
		if ($taxonomies !== 'category') return array();
		$terms = "'" . implode("', '", $terms) . "'";
		$object_ids = $wpdb->get_col("SELECT post_id FROM $wpdb->post2cat WHERE category_id IN ($terms)");
		if (!$object_ids) return array();
		return $object_ids;
	}
}

function where_match_category($limit) {
	global $wpdb, $wp_version, $table_prefix;
	$cat_ids = '';
	foreach(get_the_category() as $cat) {
		if ($cat->cat_ID) $cat_ids .= $cat->cat_ID . ',';
	}
	$cat_ids = rtrim($cat_ids, ',');
	$catarray = explode(',', $cat_ids);
	
	foreach ( $catarray as $cat ) {
		$catarray = array_merge($catarray, get_term_children($cat, 'category'));
	}
	
	$catarray = array_unique($catarray);

	global $srp_filter_ids;

	if(!empty($catarray) && empty($srp_filter_ids)){

		foreach ( $catarray as $value ) {
			
			$wp_posts_t   = $table_prefix.'posts';
			$wp_term_re   = $table_prefix.'term_relationships';
			$wp_terms     = $table_prefix.'terms';
			$wp_term_taxo = $table_prefix.'term_taxonomy';

			$sql = "select p.id from `$wp_posts_t` p
			inner join `$wp_term_re` tr on tr.object_id = p.ID
			inner join `$wp_terms` t on tr.term_taxonomy_id = t.term_id
			inner join `$wp_term_taxo` tt on tt.term_taxonomy_id = t.term_id
			where p.post_status ='publish'
			and tt.taxonomy = 'category'
			and t.term_id = $value 
			ORDER BY p.id DESC LIMIT $limit";

			$results = $wpdb->get_results($sql, ARRAY_A);
				
			if(!empty($results)){
				foreach ($results as $rval) {
					$srp_filter_ids[] = $rval['id'];
				}
			}
		}						
	}			
	
	$srp_filter_ids = array_unique($srp_filter_ids);
	if ( is_array($srp_filter_ids) && count($srp_filter_ids) > 0 ) {
		$out_posts = "'" . implode("', '", $srp_filter_ids) . "'";
		$sql = "$wpdb->posts.ID IN ($out_posts)";
	} else {
		$sql = "1 = 2";
	}	
	return $sql;
}

function where_included_cats($included_cats) {
	global $wpdb, $wp_version;
	$catarray = explode(',', $included_cats);
	foreach ( $catarray as $cat ) {
		$catarray = array_merge($catarray, get_term_children($cat, 'category'));
	}
	$catarray = array_unique($catarray);
	$ids = get_objects_in_term($catarray, 'category');
	if ( is_array($ids) && count($ids) > 0 ) {
		$ids = array_unique($ids);
		$in_posts = "'" . implode("', '", $ids) . "'";
		$sql = "ID IN ($in_posts)";
	} else {
		$sql = "1 = 2";
	}
	return $sql;
}

function where_excluded_cats($excluded_cats) {
	global $wpdb, $wp_version;
	$catarray = explode(',', $excluded_cats);
	foreach ( $catarray as $cat ) {
		$catarray = array_merge($catarray, get_term_children($cat, 'category'));
	}
	$catarray = array_unique($catarray);
	$ids = get_objects_in_term($catarray, 'category');
	if ( is_array($ids) && count($ids) > 0 ) {
		$out_posts = "'" . implode("', '", $ids) . "'";
		$sql = "$wpdb->posts.ID NOT IN ($out_posts)";
	} else {
		$sql = "1 = 1";
	}
	return $sql;
}

function where_excluded_authors($excluded_authors){
	return "post_author NOT IN ( $excluded_authors )";
}

function where_included_authors($included_authors){
	return "post_author IN ( $included_authors )";
}

function where_excluded_posts($excluded_posts) {
	return "ID NOT IN ( $excluded_posts )";
}

function where_included_posts($included_posts) {
	return "ID IN ( $included_posts )";
}

function where_tag_str($tag_str) {
	global $wpdb;
	if ( strpos($tag_str, ',') !== false ) {
		$intags = explode(',', $tag_str);
		foreach ( (array) $intags as $tag ) {
			$tags[] = sanitize_term_field('name', $tag, 0, 'post_tag', 'db');
		}
		$tag_type = 'any';
	} else if ( strpos($tag_str, '+') !== false ) {
		$intags = explode('+', $tag_str);
		foreach ( (array) $intags as $tag ) {
			$tags[] = sanitize_term_field('name', $tag, 0, 'post_tag', 'db');
		}
		$tag_type = 'all';
	} else {
		$tags[] = sanitize_term_field('name', $tag_str, 0, 'post_tag', 'db');
		$tag_type = 'any';
	}
	$ids = array();
	if ($tag_type == 'any') {
		foreach ($tags as $tag){
			if (is_term($tag, 'post_tag')) {
				$t = get_term_by('name', $tag, 'post_tag');
				$ids = array_merge($ids, get_objects_in_term($t->term_id, 'post_tag'));
			}
		}
	} else {
		foreach ($tags as $tag){
			if (is_term($tag, 'post_tag')) {
				$t = get_term_by('name', $tag, 'post_tag');
				if (count($ids) > 0) {
					$ids = array_intersect($ids, get_objects_in_term($t->term_id, 'post_tag'));
				} else {
					$ids = get_objects_in_term($t->term_id, 'post_tag');
				}
			}
		}
	}
	if ( is_array($ids) && count($ids) > 0 ) {
		$ids = array_unique($ids);
		$out_posts = "'" . implode("', '", $ids) . "'";
		$sql .= "$wpdb->posts.ID IN ($out_posts)";
	} else $sql .= "1 = 2";
	return $sql;
}

// note the addition of a 'manual overide' allowing the current posts to me marked by super_related_posts_mark_current for example
function where_omit_post($manual_current_ID = -1) {
	$postid = srp_current_post_id($manual_current_ID);
	if ($postid <= 1) $postid = -1;
	return "ID != $postid";
}

function where_just_post() {
	$postid = srp_current_post_id();
	if ($postid <= 1) $postid = -1;
	return "ID = $postid";
}

function where_hide_future() {
	// from wp 2.1 future posts are taken care of by post status
	$time_difference = get_option('gmt_offset');
	$now = gmdate("Y-m-d H:i:s",(time()+($time_difference*3600)));
	$sql = "post_date <= '$now'";
	return $sql;
}

function where_fulltext_match($weight_title, $titleterms, $contentterms, $weight_tags, $tagterms) {
	$wsql = array();
	if ($weight_title) $wsql[] = "MATCH (`title`) AGAINST ( \"$titleterms\" )";	
	if ($weight_tags) $wsql[] = "MATCH (`tags`) AGAINST ( \"$tagterms\" )";
	return '(' . implode(' OR ', $wsql) . ') ' ;
}

function where_author_comments() {
	$author_email = get_the_author_email();
	return "'$author_email' != comment_author_email";
}

function where_user_comments() {
	return "user_id = 0";
}

function score_fulltext_match($table_name, $weight_title, $titleterms, $contentterms, $weight_tags, $tagterms, $forced_ids='') {
	global $wpdb;
	$wsql = array();
	if ($weight_title) $wsql[] = "(".number_format($weight_title, 4, '.', '')." * (MATCH (`title`) AGAINST ( \"$titleterms\" )))";	
	if ($weight_tags) $wsql[] = "(".number_format($weight_tags, 4, '.', '')." * (MATCH (`tags`) AGAINST ( \"$tagterms\" )))";
	if ($forced_ids) {
		// apply a delta function to boost the score for certain IDs
		$fIDs = explode(',', $forced_ids);
		foreach($fIDs as $fID) {
			$wsql[] = "100 * (1 - SIGN(ID ^ $fID))"; // the previous delta was $wsql[] = "100*EXP(-10*POW((ID-$fID),2))";

		}
	}
	return '(' . implode(' + ', $wsql) . "  ) as score FROM `$table_name` LEFT JOIN `$wpdb->posts` ON `pID` = `ID` ";
}

function where_comment_type($comment_type) {
	if ($comment_type === 'comments') $sql = "comment_type = ''";
	elseif ($comment_type === 'trackbacks') $sql = "comment_type != ''";
	return $sql;
}

function where_check_age($direction, $length, $duration) {
	global $wp_version;
	if ('none' === $direction) return '';
	$age = "DATE_SUB(CURDATE(), INTERVAL $length $duration)";
	// we only filter out posts based on age, not pages
	if ('before' === $direction) {
		if (function_exists('get_post_type')) {
			return "(post_date <= $age OR post_type='page')";
		} else {
			return "(post_date <= $age OR post_status='static')";
		}
	} else {
		if (function_exists('get_post_type')) {
			return "(post_date >= $age OR post_type='page')";
		} else {
			return "(post_date >= $age OR post_status='static')";
		}
	}
}

function where_check_custom($key, $op, $value) {
	if ($op === 'EXISTS') {
		return "meta_key = '$key'";
	} else {
		return "(meta_key = '$key' && meta_value $op '$value')";
	}
}

/*

	End of SQL functions

*/

function srp_microtime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

/*

	Some routines to handle appending output

*/

// array of what to append to posts
global $srp_filter_data;
$srp_filter_data = array();

// each plugin calls this on startup to have content scanned for its own tag
function srp_register_post_filter($type, $key, $class, $condition='') {

	global $srp_filter_data;
	$options = get_option($key);	

	$srp_filter_arr = [];
	
	if(isset($options[$type . '_priority'])){
		$srp_filter_arr['priority'] = $options[$type . '_priority'];
	}	
	if(isset($options[$type . '_parameters'])){
		$srp_filter_arr['parameters'] = $options[$type . '_parameters'];
	}
	$srp_filter_arr['type']  = $type;
	$srp_filter_arr['class'] = $class;
	$srp_filter_arr['key']   = $key;
	$srp_filter_arr['condition'] = stripslashes($condition);

	if(isset($options['pstn_rel_1'])){
		$srp_filter_arr['position'] = $options['pstn_rel_1'];
	}
	if(isset($options['para_rel_1'])){
		$srp_filter_arr['paragraph'] = $options['para_rel_1'];	
	}
	if(isset($options['re_design_1'])){
		$srp_filter_arr['design'] = $options['re_design_1'];		
	}			

	$srp_filter_data [] = $srp_filter_arr;
	sort($srp_filter_data);
}

function srp_post_filter_1($content) {
	global $srp_filter_data;
	foreach ($srp_filter_data as $data) {
		if($data['position'] == 'atc'){
			$content .= call_user_func_array(array($data['class'], 'execute'), array($data['parameters'], '<li>{link}</li>', $data['key']));
		}elseif($data['position'] == 'ibc'){
			$closing_p = '</p>';
			$paragraphs = explode( $closing_p, $content );
			$paragraph_id = $data['paragraph'];
			foreach ($paragraphs as $index => $paragraph) {
				if ( trim( $paragraph ) ) {
					$paragraphs[$index] .= $closing_p;
				}
				$pos = strpos($paragraph, '<p');
				if ( $paragraph_id == $index + 1 && $pos !== false ) {
					$paragraphs[$index] .= call_user_func_array(array($data['class'], 'execute'), array($data['parameters'], '<li>{link}</li>', $data['key']));
				}
			}
			$content = implode( '', $paragraphs );
		}
	}
	return $content;
}

function srp_register_post_filter_2($type, $key, $class, $condition='') {

	global $srp_filter_data2;
	$options = get_option($key);			

	$srp_filter_arr = [];

	if(isset($options[$type . '_priority'])){
		$srp_filter_arr['priority'] = $options[$type . '_priority'];
	}	
	if(isset($options[$type . '_parameters'])){
		$srp_filter_arr['parameters'] = $options[$type . '_parameters'];
	}
	$srp_filter_arr['type']  = $type;
	$srp_filter_arr['class'] = $class;
	$srp_filter_arr['key']   = $key;
	$srp_filter_arr['condition'] = stripslashes($condition);

	if(isset($options['pstn_rel_2'])){
		$srp_filter_arr['position'] = $options['pstn_rel_2'];
	}
	if(isset($options['para_rel_2'])){
		$srp_filter_arr['paragraph'] = $options['para_rel_2'];	
	}
	if(isset($options['re_design_2'])){
		$srp_filter_arr['design'] = $options['re_design_2'];		
	}									
	$srp_filter_data2 [] = $srp_filter_arr;	
	sort($srp_filter_data2);
}

function srp_post_filter_2($content) {
	global $srp_filter_data2;
	foreach ($srp_filter_data2 as $data) {
		if($data['position'] == 'atc'){
			$content .= call_user_func_array(array($data['class'], 'execute2'), array($data['parameters'], '<li>{link}</li>', $data['key']));
		}elseif($data['position'] == 'ibc'){
			$closing_p = '</p>';
			$paragraphs = explode( $closing_p, $content );
			$paragraph_id = $data['paragraph'];
			foreach ($paragraphs as $index => $paragraph) {
				if ( trim( $paragraph ) ) {
					$paragraphs[$index] .= $closing_p;
				}
				$pos = strpos($paragraph, '<p');
				if ( $paragraph_id == $index + 1 && $pos !== false ) {
					$paragraphs[$index] .= call_user_func_array(array($data['class'], 'execute2'), array($data['parameters'], '<li>{link}</li>', $data['key']));
				}
			}
			$content = implode( '', $paragraphs );
		}
	}
	return $content;
}

function srp_register_post_filter_3($type, $key, $class, $condition='') {

	global $srp_filter_data3;
	$options = get_option($key);		
	if(isset($options[$type . '_priority'])){
		$srp_filter_arr['priority'] = $options[$type . '_priority'];
	}	
	if(isset($options[$type . '_parameters'])){
		$srp_filter_arr['parameters'] = $options[$type . '_parameters'];
	}
	$srp_filter_arr['type']  = $type;
	$srp_filter_arr['class'] = $class;
	$srp_filter_arr['key']   = $key;
	$srp_filter_arr['condition'] = stripslashes($condition);

	if(isset($options['pstn_rel_3'])){
		$srp_filter_arr['position'] = $options['pstn_rel_3'];
	}
	if(isset($options['para_rel_3'])){
		$srp_filter_arr['paragraph'] = $options['para_rel_3'];	
	}
	if(isset($options['re_design_3'])){
		$srp_filter_arr['design'] = $options['re_design_3'];		
	}
	$srp_filter_data3 [] = $srp_filter_arr;	
	sort($srp_filter_data3);
}

function srp_post_filter_3($content) {
	global $srp_filter_data3;
	foreach ($srp_filter_data3 as $data) {
		if($data['position'] == 'atc'){
			$content .= call_user_func_array(array($data['class'], 'execute3'), array($data['parameters'], '<li>{link}</li>', $data['key']));
		}elseif($data['position'] == 'ibc'){
			$closing_p = '</p>';
			$paragraphs = explode( $closing_p, $content );
			$paragraph_id = $data['paragraph'];
			foreach ($paragraphs as $index => $paragraph) {
				if ( trim( $paragraph ) ) {
					$paragraphs[$index] .= $closing_p;
				}
				$pos = strpos($paragraph, '<p');
				if ( $paragraph_id == $index + 1 && $pos !== false ) {
					$paragraphs[$index] .= call_user_func_array(array($data['class'], 'execute3'), array($data['parameters'], '<li>{link}</li>', $data['key']));
				}
			}
			$content = implode( '', $paragraphs );
		}
	}
	return $content;
}

function sprp_shortcode_content1($arg) {
	global $srp_filter_data;
	foreach ($srp_filter_data as $data) {
		$content = call_user_func_array(array($data['class'], 'execute'), array($data['parameters'], '<li>{link}</li>', $data['key']));
	}
	return $content;
}

function sprp_shortcode_content2($arg) {
	global $srp_filter_data;
	foreach ($srp_filter_data as $data) {
		$content = call_user_func_array(array($data['class'], 'execute2'), array($data['parameters'], '<li>{link}</li>', $data['key']));
	}
	return $content;
}

function sprp_shortcode_content3($arg) {
	global $srp_filter_data;
	foreach ($srp_filter_data as $data) {
		$content = call_user_func_array(array($data['class'], 'execute3'), array($data['parameters'], '<li>{link}</li>', $data['key']));
	}
	return $content;
}

function srp_post_filter_init1() {

	if(!is_admin()){
		global $srp_options;
		if(!$srp_options){
			$srp_options = get_option('super-related-posts');
		}	
		if($srp_options['display_status_1'] == 1){
	
			global $srp_filter_data;
			if (!$srp_filter_data) return;
			if(isset($srp_filter_data[0]['position']) && $srp_filter_data[0]['position'] != 'sc'){
				add_filter('the_content', 'srp_post_filter_1', 5);
			}else{
				add_shortcode('super-related-posts', 'sprp_shortcode_content1');
			}
	
		}
	}				
}

function srp_post_filter_init2() {
	if(!is_admin()){
		global $srp_options;
		if(!$srp_options){
			$srp_options = get_option('super-related-posts');
		}	
		if($srp_options['display_status_2'] == 1){
			global $srp_filter_data2;
			if (!$srp_filter_data2) return;
			if(isset($srp_filter_data2[0]['position']) && $srp_filter_data2[0]['position'] != 'sc'){
				add_filter('the_content', 'srp_post_filter_2', 5);
			}else{
				add_shortcode('super-related-posts', 'sprp_shortcode_content2');
			}
		}
	}		
}

function srp_post_filter_init3() {

	if(!is_admin()){
		global $srp_options;
		if(!$srp_options){
			$srp_options = get_option('super-related-posts');
		}	
		if($srp_options['display_status_3'] == 1){
			global $srp_filter_data3;
			if (!$srp_filter_data3) return;
			if(isset($srp_filter_data3[0]['position']) && $srp_filter_data3[0]['position'] != 'sc'){
				add_filter('the_content', 'srp_post_filter_3', 5);
			}else{
				add_shortcode('super-related-posts', 'sprp_shortcode_content3');
			}
		}	
	}		
}

// watch out that the registration functions are called earlier
add_action ('init', 'srp_post_filter_init1');
add_action ('init', 'srp_post_filter_init2');
add_action ('init', 'srp_post_filter_init3');

/*

	Now some routines to handle content filtering

*/

// the '|'-separated list of valid content filter tags
global $srp_filter_tags;

// each plugin calls this on startup to have content scanned for its own tag
function srp_register_content_filter($tag) {
	global $srp_filter_tags;
	if (!$srp_filter_tags) {
		$srp_filter_tags = $tag;
	} else {
		$tags = explode('|', $srp_filter_tags);
		$tags[] = $tag;
		$tags = array_unique($tags);
		$srp_filter_tags = implode('|', $tags);
	}
}


function srp_do_replace($matches) {
	return call_user_func(array($matches[1], 'execute'), $matches[2]);
}

function srp_content_filter($content) {
	global $srp_filter_tags;
	// replaces every instance of "<!--RecentPosts-->", for example, with the output of the plugin
	// the filter tag can be followed by text which will be used as a parameter string to change the behaviour of the plugin
	return preg_replace_callback("/<!--($srp_filter_tags)\s*(.*)-->/", "srp_do_replace", $content);
}

function srp_content_filter_init() {
	global $srp_filter_tags;
	if (!$srp_filter_tags) return;
	add_filter( 'the_content',     'srp_content_filter', 5 );
	add_filter( 'the_content_rss', 'srp_content_filter', 5 );
	add_filter( 'the_excerpt',     'srp_content_filter', 5 );
	add_filter( 'the_excerpt_rss', 'srp_content_filter', 5 );
	add_filter( 'widget_text',     'srp_content_filter', 5 );
}

// watch out that the registration functions are called earlier
add_action ('init', 'srp_content_filter_init');
