<?php

/*
	Library for the Recent Posts, Random Posts, Recent Comments, and Super Related Posts Posts plugins
	-- provides the admin routines which the plugins share
*/

define('ASRP_LIBRARY', true);

function srp_options_from_post($options, $args) {
	foreach ($args as $arg) {
		switch ($arg) {
		case 'limit':
		case 'skip':
		    $options[$arg] = srp_check_cardinal($_POST[$arg]);
			break;
		case 'excluded_cats':
		case 'included_cats':
			if (isset($_POST[$arg])) {
				// get the subcategories too
				if (function_exists('get_term_children')) {
					$catarray = $_POST[$arg];
					foreach ($catarray as $cat) {
						$catarray = array_merge($catarray, get_term_children($cat, 'category'));
					}
					$_POST[$arg] = array_unique($catarray);
				}
				$options[$arg] = implode(',', $_POST[$arg]);
			} else {
				$options[$arg] = '';
			}
			break;
		case 'excluded_authors':
		case 'included_authors':
			if (isset($_POST[$arg])) {
				$options[$arg] = implode(',', $_POST[$arg]);
			} else {
				$options[$arg] = '';
			}
			break;
		case 'excluded_posts':
		case 'included_posts':
			$check = explode(',', rtrim($_POST[$arg]));
			$ids = array();
			foreach ($check as $id) {
				$id = srp_check_cardinal($id);
				if ($id !== 0) $ids[] = $id;
			}
			$options[$arg] = implode(',', array_unique($ids));
			break;
		case 'stripcodes':
			$st = explode("\n", trim($_POST['starttags']));
			$se = explode("\n", trim($_POST['endtags']));
			if (count($st) != count($se)) {
				$options['stripcodes'] = array(array());
			} else {
				$num = count($st);
				for ($i = 0; $i < $num; $i++) {
					$options['stripcodes'][$i]['start'] = $st[$i];
					$options['stripcodes'][$i]['end'] = $se[$i];
				}
			}
			break;
		case 'age1':
			$options['age1'] = array();
			$options['age1']['direction'] = $_POST['age1-direction'];
			$options['age1']['length'] = srp_check_cardinal($_POST['age1-length']);
			$options['age1']['duration'] = $_POST['age1-duration'];
				break;
		case 'age2':
			$options['age2'] = array();
			$options['age2']['direction'] = $_POST['age2-direction'];
			$options['age2']['length'] = srp_check_cardinal($_POST['age2-length']);
			$options['age2']['duration'] = $_POST['age2-duration'];
				break;
		case 'age3':
			$options['age3'] = array();
			$options['age3']['direction'] = $_POST['age3-direction'];
			$options['age3']['length'] = srp_check_cardinal($_POST['age3-length']);
			$options['age3']['duration'] = $_POST['age3-duration'];
				break;
		case 'custom':
			$options['custom']['key'] = $_POST['custom-key'];
			$options['custom']['op'] = $_POST['custom-op'];
			$options['custom']['value'] = $_POST['custom-value'];
			break;
		case 'sort':
			$options['sort']['by1'] = $_POST['sort-by1'];
			$options['sort']['order1'] = $_POST['sort-order1'];
			if ($options['sort']['order1'] === 'SORT_ASC') $options['sort']['order1'] = SORT_ASC; else $options['sort']['order1'] = SORT_DESC;
			$options['sort']['case1'] = $_POST['sort-case1'];
			$options['sort']['by2'] = $_POST['sort-by2'];
			$options['sort']['order2'] = $_POST['sort-order2'];
			if ($options['sort']['order2'] === 'SORT_ASC') $options['sort']['order2'] = SORT_ASC; else $options['sort']['order2'] = SORT_DESC;
			$options['sort']['case2'] = $_POST['sort-case2'];
			if ($options['sort']['by1'] === '') {
				$options['sort']['order1'] = SORT_ASC;
				$options['sort']['case1'] = 'false';
				$options['sort']['by2'] = '';
			}
			if ($options['sort']['by2'] === '') {
				$options['sort']['order2'] = SORT_ASC;
				$options['sort']['case2'] = 'false';
			}
			break;
		case 'num_terms':
			$options['num_terms'] = $_POST['num_terms'];
			if ($options['num_terms'] < 1) $options['num_terms'] = 20;
			break;
		default:
			$options[$arg] = isset( $_POST[ $arg ] ) ? trim( $_POST[ $arg ] ) : '';
		}
	}
	return $options;
}

function srp_check_cardinal($string) {
	$value = intval($string);
	return ($value > 0) ? $value : 0;
}

function srp_display_available_tags($plugin_name) {
	?>
		<h3><?php _e('Available Tags', 'post_plugin_library'); ?></h3>
		<ul style="list-style-type: none;">
		<li title="">{author}</li>
		<li title="">{authorurl}</li>
		<li title="">{categoryid}</li>
		<li title="">{categorylinks}</li>
		<li title="">{categorynames}</li>
		<li title="">{commentcount}</li>
		<li title="">{custom}</li>
		<li title="">{date}</li>
		<li title="">{dateedited}</li>
		<li title="">{excerpt}</li>
		<li title="">{fullpost}</li>
		<li title="">{gravatar}</li>
		<li title="">{if}</li>
		<li title="">{image}</li>
		<li title="">{imagealt}</li>
		<li title="">{imagesrc}</li>
		<li title="">{link}</li>
		<li title="">{php}</li>
		<li title="">{postid}</li>
		<li title="">{postviews}</li>
		<?php if ($plugin_name === 'super-related-posts') { ?>
			<li title="">{score}</li>
		<?php } ?>
		<li title="">{snippet}</li>
		<li title="">{tags}</li>
		<li title="">{taglinks}</li>
		<li title="">{title}</li>
		<li title="">{time}</li>
		<li title="">{timeedited}</li>
		<li title="">{totalpages}</li>
		<li title="">{totalposts}</li>
		<li title="">{url}</li>
		</ul>
	<?php
}

function srp_display_available_comment_tags() {
	?>
		<ul style="list-style-type: none;">
		<li title="">{commentexcerpt}</li>
		<li title="">{commentsnippet}</li>
		<li title="">{commentdate}</li>
		<li title="">{commenttime}</li>
		<li title="">{commentdategmt}</li>
		<li title="">{commenttimegmt}</li>
		<li title="">{commenter}</li>
		<li title="">{commenterip}</li>
		<li title="">{commenterurl}</li>
		<li title="">{commenterlink}</li>
		<li title="">{commenturl}</li>
		<li title="">{commentpopupurl}</li>
		<li title="">{commentlink}</li>
		<li title="">{commentlink2}</li>
		</ul>
	<?php
}

/*

	inserts a form button to submit a bug report to my web site

*/
function get_plugin_version($prefix) {
	$plugin_version = str_replace('-', '_', $prefix) . '_version';
	global $$plugin_version;
	return ${$plugin_version};
}



/*

	inserts a form button to completely remove the plugin and all its options etc.

*/

function srp_confirm_eradicate() {
 return (isset($_POST['eradicate-check']) && 'yes'===$_POST['eradicate-check']);
}

function srp_deactivate_plugin($plugin_file) {
	$current = get_option('active_plugins');
	$plugin_file = substr($plugin_file, strlen(WP_PLUGIN_DIR)+1);
	$plugin_file = str_replace('\\', '/', $plugin_file);
	if (in_array($plugin_file, $current)) {
		array_splice($current, array_search($plugin_file, $current), 1);
		update_option('active_plugins', $current);
	}
}


/*

	For the display of the option pages

*/

function srp_display_limit($limit) {
	?>
	<tr valign="top">
		<th scope="row"><label for="limit"><?php _e('Number of posts to show:', 'post_plugin_library') ?></label></th>
		<td><input min="1" name="limit" type="number" id="limit" style="width: 60px;" value="<?php echo $limit; ?>" size="2" /></td>
	</tr>
	<?php
}

function sprp_display_shortcode($num) {
	?>
	<tr valign="top">
		<th scope="row"><label for="limit_<?php echo $num; ?>"><?php _e('Shortcode:', 'post_plugin_library') ?></label></th>
		<td><strong>[super-related-posts]</strong></td>
	</tr>
	<?php
}

function srp_display_limit_i($limit, $num) {
	?>
	<tr valign="top">
		<th scope="row"><label for="limit_<?php echo $num; ?>"><?php _e('Number of posts to show:', 'post_plugin_library') ?></label></th>
		<td><input min="1" name="limit_<?php echo $num; ?>" type="number" id="limit_<?php echo $num; ?>" style="width: 60px;" value="<?php echo $limit; ?>" size="2" /></td>
	</tr>
	<?php
}

function srp_display_unique($unique) {
	?>
	<tr valign="top">
		<th scope="row"><label for="unique"><?php _e('Show just one comment per post?', 'post_plugin_library') ?></label></th>
		<td>
		<select name="unique" id="unique" >
		<option <?php if($unique == 'false') { echo 'selected="selected"'; } ?> value="false">No</option>
		<option <?php if($unique == 'true') { echo 'selected="selected"'; } ?> value="true">Yes</option>
		</select>
		</td>
	</tr>
	<?php
}

function srp_display_just_current_post($just_current_post) {
	?>
	<tr valign="top">
		<th scope="row"><label for="just_current_post"><?php _e('Show just the current post?', 'post_plugin_library') ?></label></th>
		<td>
		<select name="just_current_post" id="just_current_post" >
		<option <?php if($just_current_post == 'false') { echo 'selected="selected"'; } ?> value="false">No</option>
		<option <?php if($just_current_post == 'true') { echo 'selected="selected"'; } ?> value="true">Yes</option>
		</select>
		</td>
	</tr>
	<?php
}

function srp_sort_post_by_recent_popular_i($sort_by, $num) {
	?>
	<tr valign="top">
		<th scope="row"><label for="sort_by_<?php echo $num; ?>"><?php _e('Sort post\'s by', 'post_plugin_library') ?></label></th>
		<td>
			<select name="sort_by_<?php echo $num; ?>" id="sort_by_<?php echo $num; ?>">			
				<option <?php if($sort_by == 'recent') { echo 'selected="selected"'; } ?> value="recent">Recent</option>
				<option <?php if($sort_by == 'popular') { echo 'selected="selected"'; } ?> value="popular">Popular</option>
			</select>
		</td>
	</tr>
	<?php
}

function srp_display_match_cat($match_cat) {
	?>
	<tr valign="top">
		<th scope="row"><label for="match_cat"><?php _e('Match the current post\'s category?', 'post_plugin_library') ?></label></th>
		<td>
			<select name="match_cat" id="match_cat">			
			<option <?php if($match_cat == 'true') { echo 'selected="selected"'; } ?> value="true">Yes</option>
			<option <?php if($match_cat == 'false') { echo 'selected="selected"'; } ?> value="false">No</option>
			</select>
		</td>
	</tr>
	<?php
}

function srp_display_match_cat_i($match_cat, $num) {
	?>
	<tr valign="top">
		<th scope="row"><label for="match_cat_<?php echo $num; ?>"><?php _e('Match the current post\'s category?', 'post_plugin_library') ?></label></th>
		<td>
			<select name="match_cat_<?php echo $num; ?>" id="match_cat_<?php echo $num; ?>">			
			<option <?php if($match_cat == 'true') { echo 'selected="selected"'; } ?> value="true">Yes</option>
			<option <?php if($match_cat == 'false') { echo 'selected="selected"'; } ?> value="false">No</option>
			</select>
		</td>
	</tr>
	<?php
}

function srp_display_match_tags($match_tags) {
	global $wp_version;
	?>
	<tr valign="top">
		<th scope="row"><label for="match_tags"><?php _e('Match the current post\'s tags?', 'post_plugin_library') ?></label></th>
		<td>
			<select name="match_tags" id="match_tags" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> >
			<option <?php if($match_tags == 'false') { echo 'selected="selected"'; } ?> value="false">No</option>
			<option <?php if($match_tags == 'any') { echo 'selected="selected"'; } ?> value="any">Any tag</option>
			<option <?php if($match_tags == 'all') { echo 'selected="selected"'; } ?> value="all">Every tag</option>
			</select>
		</td>
	</tr>
	<?php
}

function srp_display_match_tags_i($match_tags, $num) {
	global $wp_version;
	?>
	<tr valign="top">
		<th scope="row"><label for="match_tags_<?php echo $num; ?>"><?php _e('Match the current post\'s tags?', 'post_plugin_library') ?></label></th>
		<td>
			<select name="match_tags_<?php echo $num; ?>" id="match_tags_<?php echo $num; ?>" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> >
			<option <?php if($match_tags == 'false') { echo 'selected="selected"'; } ?> value="false">No</option>
			<option <?php if($match_tags == 'any') { echo 'selected="selected"'; } ?> value="any">Any tag</option>
			<option <?php if($match_tags == 'all') { echo 'selected="selected"'; } ?> value="all">Every tag</option>
			</select>
		</td>
	</tr>
	<?php
}

function sprp_position_related_i($pstn_rel, $num) {
	global $wp_version;
	?>
	<tr valign="top">
		<th scope="row"><label for="pstn_rel_<?php echo $num; ?>"><?php _e('Position:', 'post_plugin_library') ?></label></th>
		<td>
			<select name="pstn_rel_<?php echo $num; ?>" id="pstn_rel_<?php echo $num; ?>" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> >
			<option <?php if($pstn_rel == 'atc') { echo 'selected="selected"'; } ?> value="atc">After the Content</option>
			<option <?php if($pstn_rel == 'ibc') { echo 'selected="selected"'; } ?> value="ibc">In Between Content</option>
			<option <?php if($pstn_rel == 'sc') { echo 'selected="selected"'; } ?> value="sc">Shortcode</option>
			</select>
		</td>
	</tr>
	<?php
}

function sprp_design_related_i($design, $num) {
	global $wp_version;
	?>
	<tr valign="top">
		<th scope="row"><label for="re_design_<?php echo $num; ?>"><?php _e('Design:', 'post_plugin_library') ?></label></th>
		<td>
			<select name="re_design_<?php echo $num; ?>" id="re_design_<?php echo $num; ?>" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> >
			<option <?php if($design == 'd1') { echo 'selected="selected"'; } ?> value="d1">Design 1</option>
			<option <?php if($design == 'd2') { echo 'selected="selected"'; } ?> value="d2">Design 2</option>
			<option <?php if($design == 'd3') { echo 'selected="selected"'; } ?> value="d3">Design 3</option>
			</select>
		</td>
	</tr>
	<?php
}

function sprp_paragraph_i($para, $pos, $num) {
	?>
	<tr valign="top" <?php if($pos != 'ibc') { echo 'style="display:none"'; } ?>>
		<th scope="row"><label for="para_rel_<?php echo $num; ?>"><?php _e('After Number of paragraphs?', 'post_plugin_library') ?></label></th>
		<td><input min="1" name="para_rel_<?php echo $num; ?>" type="number" id="para_rel_<?php echo $num; ?>" style="width: 60px;" value="<?php echo $para; ?>" size="2" /></td>
	</tr>
	<?php
}

function sprp_adv_filter_switch($filter_check, $num){
	?>
	<tr valign="top">
	    <th scope="row"><label for="adv_filter_check_<?php echo $num; ?>" class="adv_filter_check_label">Advanced Filter Options</label></th>
	    <td>
	      <label class="switch">
	        <input type="checkbox" id="adv_filter_check_<?php echo $num; ?>" name="adv_filter_check_<?php echo $num; ?>" value="1" <?php if( $filter_check == 1 ){echo 'checked'; } ?> >
	        <span class="slider round"></span>
	      </label>            
	    </td>
	</tr>
	<?php 
}
function srp_display_status($filter_check, $num){
	?>
	<tr valign="top">
	    <th scope="row"><label for="display_status_<?php echo $num; ?>" class="display_status_label">On/Off</label></th>
	    <td>
	      <label class="switch">
	        <input type="checkbox" id="display_status_<?php echo $num; ?>" name="display_status_<?php echo $num; ?>" value="1" <?php if( $filter_check == 1 ){echo 'checked'; } ?> >
	        <span class="slider round"></span>
	      </label>            
	    </td>
	</tr>
	<?php 
}

function srp_display_tag_str($tag_str) {
	global $wp_version;
	?>
	<tr valign="top">
		<th scope="row"><label for="tag_str"><?php _e('Match posts with tags:<br />(a,b matches posts with either tag, a+b only matches posts with both tags)', 'post_plugin_library') ?></label></th>
		<td><input name="tag_str" type="text" id="tag_str" value="<?php echo $tag_str; ?>" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> size="40" /></td>
	</tr>
	<?php
}

function srp_display_tag_str_i($tag_str, $num) {
	global $wp_version;
	?>
	<tr valign="top">
		<th scope="row"><label for="tag_str_<?php echo $num; ?>"><?php _e('Match posts with tags:<br />(a,b matches posts with either tag, a+b only matches posts with both tags)', 'post_plugin_library') ?></label></th>
		<td><input name="tag_str_<?php echo $num; ?>" type="text" id="tag_str_<?php echo $num; ?>" value="<?php echo $tag_str; ?>" <?php if ($wp_version < 2.3) echo 'disabled="true"'; ?> size="40" /></td>
	</tr>
	<?php
}

function srp_display_excluded_posts($excluded_posts) {
	?>
	<tr valign="top">
		<th scope="row"><label for="excluded_posts"><?php _e('Posts to exclude:', 'post_plugin_library') ?></label></th>
		<td><input name="excluded_posts" type="text" id="excluded_posts" value="<?php echo $excluded_posts; ?>" size="40" /> <?php _e('comma-separated IDs', 'post_plugin_library'); ?></td>
	</tr>
	<?php
}

function srp_display_excluded_posts_i($excluded_posts, $num) {
	?>
	<tr valign="top">
		<th scope="row"><label for="excluded_posts_<?php echo $num; ?>"><?php _e('Posts to exclude:', 'post_plugin_library') ?></label></th>
		<td><input name="excluded_posts_<?php echo $num; ?>" type="text" id="excluded_posts_<?php echo $num; ?>" value="<?php echo $excluded_posts; ?>" size="40" /> <?php _e('comma-separated IDs', 'post_plugin_library'); ?></td>
	</tr>
	<?php
}

function srp_display_included_posts($included_posts) {
	?>
	<tr valign="top">
		<th scope="row"><label for="included_posts"><?php _e('Posts to include:', 'post_plugin_library') ?></label></th>
		<td><input name="included_posts" type="text" id="included_posts" value="<?php echo $included_posts; ?>" size="40" /> <?php _e('comma-separated IDs', 'post_plugin_library'); ?></td>
	</tr>
	<?php
}

function srp_display_included_posts_i($included_posts, $num) {
	?>
	<tr valign="top">
		<th scope="row"><label for="included_posts_<?php echo $num; ?>"><?php _e('Posts to include:', 'post_plugin_library') ?></label></th>
		<td><input name="included_posts_<?php echo $num; ?>" type="text" id="included_posts_<?php echo $num; ?>" value="<?php echo $included_posts; ?>" size="40" /> <?php _e('comma-separated IDs', 'post_plugin_library'); ?></td>
	</tr>
	<?php
}

function srp_display_authors($excluded_authors, $included_authors) {
	global $wpdb;
	?>
	<tr valign="top">
		<th scope="row"><?php _e('Authors to exclude/include:', 'post_plugin_library') ?></th>
		<td>
			<table class="superrelatedposts-inner-table">
			<?php
				$users = $wpdb->get_results("SELECT ID, user_login FROM $wpdb->users ORDER BY user_login");
				if ($users) {
					$excluded = explode(',', $excluded_authors);
					$included = explode(',', $included_authors);
					echo "\n\t<tr valign=\"top\"><td><strong>Author</strong></td><td><strong>Exclude</strong></td><td><strong>Include</strong></td></tr>";
					foreach ($users as $user) {
						if (false === in_array($user->ID, $excluded)) {
							$ex_ischecked = '';
						} else {
							$ex_ischecked = 'checked';
						}
						if (false === in_array($user->ID, $included)) {
							$in_ischecked = '';
						} else {
							$in_ischecked = 'checked';
						}
						echo "\n\t<tr valign=\"top\"><td>$user->user_login</td><td><input type=\"checkbox\" name=\"excluded_authors[]\" value=\"$user->ID\" $ex_ischecked /></td><td><input type=\"checkbox\" name=\"included_authors[]\" value=\"$user->ID\" $in_ischecked /></td></tr>";
					}
				}
			?>
			</table>
		</td>
	</tr>
	<?php
}

function srp_display_cats($excluded_cats, $included_cats) {
	global $wpdb;
	?>
	<tr valign="top">
		<th scope="row"><?php _e('Categories to exclude/include:', 'post_plugin_library') ?></th>
		<td>
			<table class="superrelatedposts-inner-table">
			<?php
				if (function_exists("get_categories")) {
					$categories = get_categories();//('&hide_empty=1');
				} else {
					//$categories = $wpdb->get_results("SELECT * FROM $wpdb->categories WHERE category_count <> 0 ORDER BY cat_name");
					$categories = $wpdb->get_results("SELECT * FROM $wpdb->categories ORDER BY cat_name");
				}
				if ($categories) {
					echo "\n\t<tr valign=\"top\"><td><strong>Category</strong></td><td><strong>Exclude</strong></td><td><strong>Include</strong></td></tr>";
					$excluded = explode(',', $excluded_cats);
					$included = explode(',', $included_cats);
					$level = 0;
					$cats_added = array();
					$last_parent = 0;
					$cat_parent = 0;
					foreach ($categories as $category) {
						$category->cat_name = esc_html($category->cat_name);
						if (false === in_array($category->cat_ID, $excluded)) {
							$ex_ischecked = '';
						} else {
							$ex_ischecked = 'checked';
						}
						if (false === in_array($category->cat_ID, $included)) {
							$in_ischecked = '';
						} else {
							$in_ischecked = 'checked';
						}
						$last_parent = $cat_parent;
						$cat_parent = $category->category_parent;
						if ($cat_parent == 0) {
							$level = 0;
						} elseif ($last_parent != $cat_parent) {
							if (in_array($cat_parent, $cats_added)) {
								$level = $level - 1;
							} else {
								$level = $level + 1;
							}
							$cats_added[] = $cat_parent;
						}
						$pad = str_repeat('&nbsp;', 3*$level);
						echo "\n\t<tr valign=\"top\"><td>$pad$category->cat_name</td><td><input type=\"checkbox\" name=\"excluded_cats[]\" value=\"$category->cat_ID\" $ex_ischecked /></td><td><input type=\"checkbox\" name=\"included_cats[]\" value=\"$category->cat_ID\" $in_ischecked /></td></tr>";
					}
				}
			?>
			</table>
		</td>
	</tr>
	<?php
}

function srp_display_stripcodes($stripcodes) {
	?>
	<tr valign="top">
		<th scope="row"><?php _e('Other plugins\' tags to remove from snippet:', 'post_plugin_library') ?></th>
		<td>
			<table>
			<tr><td style="border-bottom-width: 0"><label for="starttags"><?php _e('opening', 'post_plugin_library') ?></label></td><td style="border-bottom-width: 0"><label for="endtags"><?php _e('closing', 'post_plugin_library') ?></label></td></tr>
			<tr valign="top"><td style="border-bottom-width: 0">
                <textarea name="starttags" id="starttags" rows="4" cols="20"><?php
				foreach ($stripcodes as $tag) {
					if(array_key_exists('start',$tag)){
						echo htmlspecialchars(stripslashes($tag['start']))."\n";
					}
				}
				?></textarea></td><td style="border-bottom-width: 0">

                <textarea name="endtags" id="endtags" rows="4" cols="20"><?php
				foreach ($stripcodes as $tag) {
					if(array_key_exists('end',$tag)){
						echo htmlspecialchars(stripslashes($tag['end']))."\n";
					}
				}
				?></textarea>
            </td></tr>
			</table>
		</td>
	</tr>
	<?php
}

function srp_display_age($age, $sort_by, $num) {
	
	?>
	<tr valign="top" <?php if($sort_by != 'popular') { echo 'style="display:none"'; } ?>>
		<th scope="row"><label for="age<?php echo $num; ?>-direction"><?php _e('Ignore posts:', 'post_plugin_library') ?></label></th>
		<td>
				<select name="age<?php echo $num; ?>-direction" id="age<?php echo $num; ?>-direction">
				<option <?php if(!empty($age['direction']) && $age['direction'] == 'before') { echo 'selected="selected"'; } ?> value="before">less than</option>
				<option <?php if(!empty($age['direction']) && $age['direction'] == 'after') { echo 'selected="selected"'; } ?> value="after">more than</option>
				<option <?php if(!empty($age['direction']) && $age['direction'] == 'none') { echo 'selected="selected"'; } ?> value="none">-----</option>
				</select>
				<input  name="age<?php echo $num; ?>-length" id="age<?php echo $num; ?>-length" value="<?php if( !empty($age['length']) ) echo $age['length']   ?>" style="vertical-align: middle; width: 60px;" type="number" size="4" min="1" />
				<select name="age<?php echo $num; ?>-duration" id="age<?php echo $num; ?>-duration">
				<option <?php if(!empty($age['duration']) && $age['duration'] == 'day') { echo 'selected="selected"'; } ?> value="day">day(s)</option>
				<option <?php if(!empty($age['duration']) && $age['duration'] == 'month') { echo 'selected="selected"'; } ?> value="month">month(s)</option>
				<option <?php if(!empty($age['duration']) && $age['duration'] == 'year') { echo 'selected="selected"'; } ?> value="year">year(s)</option>
				</select>
				old
		</td>
	</tr>
	<?php
}

function srp_display_custom($custom) {
	?>
	<tr valign="top">
		<th scope="row"><?php _e('Match posts by custom field:', 'post_plugin_library') ?></th>
		<td>
			<table>
			<tr><td style="border-bottom-width: 0">Field Name</td><td style="border-bottom-width: 0"></td><td style="border-bottom-width: 0">Field Value</td></tr>
			<tr>
			<td style="border-bottom-width: 0"><input name="custom-key" type="text" id="custom-key" value="<?php echo $custom['key']; ?>" size="20" /></td>
			<td style="border-bottom-width: 0">
				<select name="custom-op" id="custom-op">
				<option <?php if($custom['op'] == '=') { echo 'selected="selected"'; } ?> value="=">=</option>
				<option <?php if($custom['op'] == '!=') { echo 'selected="selected"'; } ?> value="!=">!=</option>
				<option <?php if($custom['op'] == '>') { echo 'selected="selected"'; } ?> value=">">></option>
				<option <?php if($custom['op'] == '>=') { echo 'selected="selected"'; } ?> value=">=">>=</option>
				<option <?php if($custom['op'] == '<') { echo 'selected="selected"'; } ?> value="<"><</option>
				<option <?php if($custom['op'] == '<=') { echo 'selected="selected"'; } ?> value="<="><=</option>
				<option <?php if($custom['op'] == 'LIKE') { echo 'selected="selected"'; } ?> value="LIKE">LIKE</option>
				<option <?php if($custom['op'] == 'NOT LIKE') { echo 'selected="selected"'; } ?> value="NOT LIKE">NOT LIKE</option>
				<option <?php if($custom['op'] == 'REGEXP') { echo 'selected="selected"'; } ?> value="REGEXP">REGEXP</option>
				<option <?php if($custom['op'] == 'EXISTS') { echo 'selected="selected"'; } ?> value="EXISTS">EXISTS</option>
				</select>
			</td>
			<td style="border-bottom-width: 0"><input name="custom-value" type="text" id="custom-value" value="<?php echo $custom['value']; ?>" size="20" /></td>
			</tr>
			</table>
		</td>
	</tr>
	<?php
}

function srp_display_orderby($options) {
	global $wpdb;
	$limit = 30;
	$keys = $wpdb->get_col( "
		SELECT meta_key
		FROM $wpdb->postmeta
		WHERE meta_key NOT LIKE '\_%'
		GROUP BY meta_key
		ORDER BY meta_id DESC
		LIMIT $limit" );
	$metaselect = "<select id='orderby' name='orderby'>\n\t<option value=''></option>";
	if ( $keys ) {
		natcasesort($keys);
		foreach ( $keys as $key ) {
			$key = esc_attr( $key );
			if ($options['orderby'] == $key) {
				$metaselect .= "\n\t<option selected='selected' value='$key'>$key</option>";
			} else {
				$metaselect .= "\n\t<option value='$key'>$key</option>";
			}
		}
		$metaselect .= "</select>";
	}

	?>
	<tr valign="top">
		<th scope="row"><?php _e('Select output by custom field:', 'post_plugin_library') ?></th>
		<td>
			<table>
			<tr><td style="border-bottom-width: 0">Field</td><td style="border-bottom-width: 0">Order</td><td style="border-bottom-width: 0">Case</td></tr>
			<tr>
			<td style="border-bottom-width: 0">
			<?php echo $metaselect;	?>
			</td>
			<td style="border-bottom-width: 0">
				<select name="orderby_order" id="orderby_order">
				<option <?php if($options['orderby_order'] == 'ASC') { echo 'selected="selected"'; } ?> value="ASC">ascending</option>
				<option <?php if($options['orderby_order'] == 'DESC') { echo 'selected="selected"'; } ?> value="DESC">descending</option>
				</select>
			</td>
			<td style="border-bottom-width: 0">
				<select name="orderby_case" id="orderby_case">
				<option <?php if($options['orderby_case'] == 'false') { echo 'selected="selected"'; } ?> value="false">case-sensitive</option>
				<option <?php if($options['orderby_case'] == 'true') { echo 'selected="selected"'; } ?> value="true">case-insensitive</option>
				<option <?php if($options['orderby_case'] == 'num') { echo 'selected="selected"'; } ?> value="num">numeric</option>
				</select>
			</td>
			</tr>
			</table>
		</td>
	</tr>
	<?php
}

// now for recent_comments

function srp_display_show_type($show_type) {
	?>
	<tr valign="top">
		<th scope="row" title=""><label for="show_type"><?php _e('Type of comment to show:', 'post_plugin_library') ?></label></th>
		<td>
			<select name="show_type" id="show_type">
			<option <?php if($show_type == 'all') { echo 'selected="selected"'; } ?> value="all">All kinds of comment</option>
			<option <?php if($show_type == 'comments') { echo 'selected="selected"'; } ?> value="comments">Just plain comments</option>
			<option <?php if($show_type == 'trackbacks') { echo 'selected="selected"'; } ?> value="trackbacks">Just trackbacks and pingbacks</option>
			</select>
		</td>
	</tr>
	<?php
}

function srp_display_group_by($group_by) {
	?>
	<tr valign="top">
		<th scope="row" title=""><?php _e('Type of grouping:', 'post_plugin_library') ?></th>
		<td>
			<select name="group_by" id="group_by">
			<option <?php if($group_by == 'post') { echo 'selected="selected"'; } ?> value="post">By Post</option>
			<option <?php if($group_by == 'none') { echo 'selected="selected"'; } ?> value="none">Ungrouped</option>
			<option <?php if($group_by == 'author') { echo 'selected="selected"'; } ?> value="author">By Commenter</option>
			</select>
			(overrides the sort criteria above)
		</td>
	</tr>
	<?php
}

function srp_display_no_author_comments($no_author_comments) {
	?>
	<tr valign="top">
		<th scope="row"><label for="no_author_comments"><?php _e('Omit comments by the post author?', 'post_plugin_library') ?></label></th>
		<td>
			<select name="no_author_comments" id="no_author_comments">
			<option <?php if($no_author_comments == 'false') { echo 'selected="selected"'; } ?> value="false">No</option>
			<option <?php if($no_author_comments == 'true') { echo 'selected="selected"'; } ?> value="true">Yes</option>
			</select>
		</td>
	</tr>
	<?php
}

function srp_display_no_user_comments($no_user_comments) {
	?>
	<tr valign="top">
		<th scope="row"><label for="no_user_comments"><?php _e('Omit comments by registered users?', 'post_plugin_library') ?></label></th>
		<td>
			<select name="no_user_comments" id="no_user_comments">
			<option <?php if($no_user_comments == 'false') { echo 'selected="selected"'; } ?> value="false">No</option>
			<option <?php if($no_user_comments == 'true') { echo 'selected="selected"'; } ?> value="true">Yes</option>
			</select>
		</td>
	</tr>
	<?php
}

function srp_display_date_modified($date_modified) {
	?>
	<tr valign="top">
		<th scope="row"><?php _e('Order by date of last edit rather than date of creation?', 'post_plugin_library') ?></th>
		<td>
			<select name="date_modified" id="date_modified">
			<option <?php if($date_modified == 'false') { echo 'selected="selected"'; } ?> value="false">No</option>
			<option <?php if($date_modified == 'true') { echo 'selected="selected"'; } ?> value="true">Yes</option>
			</select>
		</td>
	</tr>
	<?php
}

// 'borrowed', with adaptations, from Stephen Rider at http://striderweb.com/nerdaphernalia/
function srp_get_plugin_data($plugin_file) {
	// You can optionally pass a specific value to fetch, e.g. 'Version' -- but it's inefficient to do that multiple times
	// As of WP 2.5.1: 'Name', 'Title', 'Description', 'Author', 'Version'
	// As of WP 2.7-bleeding: 'Name', 'PluginURI', 'Description', 'Author', 'AuthorURI', 'Version', 'TextDomain', 'DomainPath'
	if(!function_exists( 'get_plugin_data' ) ) require_once( ABSPATH . 'wp-admin/includes/plugin.php');
	static $plugin_data;
	if(!$plugin_data) {
		$plugin_data = get_plugin_data($plugin_file);
		if (!isset($plugin_data['Title'])) {
			if ('' != $plugin_data['PluginURI'] && '' != $plugin_data['Name']) {
				$plugin_data['Title'] = '<a href="' . $plugin_data['PluginURI'] . '" title="'. __('Visit plugin homepage', 'post-plugin-library') . '">' . $plugin_data['Name'] . '</a>';
			} else {
				$plugin_data['Title'] = $name;
			}
		}
	}
	return $plugin_data;
}

