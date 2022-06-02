<?php

// Admin stuff for Super Related Posts Plugin, Version 2.6.2.0

add_action('admin_menu', 'srp_option_menu', 1);

function srp_option_menu() {
	add_options_page(__('Super Related Posts Options', 'similar_posts'), __('Super Related Posts', 'similar_posts'), 'edit_theme_options', 'similar-posts', 'srp_options_page');
}

function srp_options_page(){
	echo '<div class="wrap"><h2>';
	_e('Super Related Posts ', 'similar_posts');
	echo '</h2></div>';
	$m = new srp_admin_subpages();
	$m->add_subpage('Related Post1', 'related_post1', 'srp_rp1_options_subpage');
	$m->add_subpage('Related Post2', 'related_post2', 'srp_rp2_options_subpage');
	$m->add_subpage('Related Post3', 'related_post3', 'srp_rp3_options_subpage');
	$m->display();
}


function srp_rp1_options_subpage(){
	global $wpdb, $wp_version;
	$options = get_option('similar-posts');
	$num = 1;
	if (isset($_POST['update_options'])) {
		check_admin_referer('similar-posts-update-options');
		if (defined('POC_CACHE_4')) poc_cache_flush();
		// Fill up the options with the values chosen...
		$options = ppl_options_from_post($options, array('limit', 'age', 'match_cat', 'match_tags', 'pstn_rel_1', 'para_rel_1', 're_design_1', 'adv_filter_check_1', 'excluded_posts', 'included_posts', 'excluded_authors', 'included_authors', 'excluded_cats', 'included_cats', 'tag_str', 'custom'));
		update_option('similar-posts', $options);
		// Show a message to say we've done something
		echo '<div class="updated settings-error notice"><p>' . __('<b>Settings saved.</b>', 'similar_posts') . '</p></div>';
	}
	//now we drop into html to display the option page form
	?>
		<div class="wrap similarposts-tab-content">

		<form method="post" action="">

		<table class="optiontable form-table">
			<?php
				ppl_display_limit($options['limit']);
				ppl_display_age($options['age']);
				ppl_display_match_cat($options['match_cat']);
				ppl_display_match_tags($options['match_tags']);
				sprp_position_related_i($options['pstn_rel_1'], $num);
				sprp_paragraph_i($options['para_rel_1'], $options['pstn_rel_1'], $num);
				sprp_design_related_i($options['re_design_1'], $num);
			?>
		</table>
		<table class="optiontable form-table">
			<?php sprp_adv_filter_switch($options['adv_filter_check_1'], $num); ?>
		</table>
		<?php
			$hide_filter = (isset($options['adv_filter_check_1']) && $options['adv_filter_check_1'] == 1) ? '' : 'style="display:none"';
		?>
		<table id="filter_options" class="optiontable form-table" <?php echo $hide_filter; ?>>
			<?php
				ppl_display_excluded_posts($options['excluded_posts']);
				ppl_display_included_posts($options['included_posts']);
				ppl_display_authors($options['excluded_authors'], $options['included_authors']);
				ppl_display_cats($options['excluded_cats'], $options['included_cats']);
				ppl_display_tag_str($options['tag_str']);
				ppl_display_custom($options['custom']);
			?>
		</table>

		<div class="submit"><input type="submit" class="button button-primary" name="update_options" value="<?php _e('Save Settings', 'similar_posts') ?>" /></div>
		<?php if (function_exists('wp_nonce_field')) wp_nonce_field('similar-posts-update-options'); ?>
		</form>
	</div>
	<?php
}

function srp_rp2_options_subpage(){
	global $wpdb, $wp_version;
	$options = get_option('similar-posts');
	$num = 2;
	if (isset($_POST['update_options'])) {
		check_admin_referer('similar-posts-update-options');
		if (defined('POC_CACHE_4')) poc_cache_flush();
		// Fill up the options with the values chosen...
		$options = ppl_options_from_post($options, array('limit_2', 'age', 'match_cat_2', 'match_tags_2', 'pstn_rel_2', 'para_rel_2', 're_design_2', 'position', 'adv_filter_check_2', 'excluded_posts_2', 'included_posts_2', 'excluded_authors', 'included_authors', 'excluded_cats', 'included_cats', 'tag_str_2', 'custom'));
		update_option('similar-posts', $options);
		// Show a message to say we've done something
		echo '<div class="updated settings-error notice"><p>' . __('<b>Settings saved.</b>', 'similar_posts') . '</p></div>';
	}
	//now we drop into html to display the option page form
	?>
		<div class="wrap similarposts-tab-content">

		<form method="post" action="">

		<table class="optiontable form-table">
			<?php
				ppl_display_limit_i($options['limit_2'], $num);
				ppl_display_age($options['age']);
				ppl_display_match_cat_i($options['match_cat_2'], $num);
				ppl_display_match_tags_i($options['match_tags_2'], $num);
				sprp_position_related_i($options['pstn_rel_2'], $num);
				sprp_paragraph_i($options['para_rel_2'], $options['pstn_rel_2'], $num);
				sprp_design_related_i($options['re_design_2'], $num);
			?>
		</table>
		<table class="optiontable form-table">
			<?php sprp_adv_filter_switch($options['adv_filter_check_2'], $num); ?>
		</table>
		<?php
			$hide_filter = (isset($options['adv_filter_check_2']) && $options['adv_filter_check_2'] == 1) ? '' : 'style="display:none"';
		?>
		<table id="filter_options" class="optiontable form-table" <?php echo $hide_filter; ?>>
			<?php
				ppl_display_excluded_posts_i($options['excluded_posts_2'], $num);
				ppl_display_included_posts_i($options['included_posts_2'], $num);
				ppl_display_authors($options['excluded_authors'], $options['included_authors']);
				ppl_display_cats($options['excluded_cats'], $options['included_cats']);
				ppl_display_tag_str_i($options['tag_str_2'], $num);
				ppl_display_custom($options['custom']);
			?>
		</table>

		<div class="submit"><input type="submit" class="button button-primary" name="update_options" value="<?php _e('Save Settings', 'similar_posts') ?>" /></div>
		<?php if (function_exists('wp_nonce_field')) wp_nonce_field('similar-posts-update-options'); ?>
		</form>
	</div>
	<?php
}

function srp_rp3_options_subpage(){
	global $wpdb, $wp_version;
	$options = get_option('similar-posts');
	$num = 3;
	if (isset($_POST['update_options'])) {
		check_admin_referer('similar-posts-update-options');
		if (defined('POC_CACHE_4')) poc_cache_flush();
		// Fill up the options with the values chosen...
		$options = ppl_options_from_post($options, array('limit_3', 'age', 'match_cat_3', 'match_tags_3', 'pstn_rel_3', 'para_rel_3', 're_design_3', 'adv_filter_check_3', 'excluded_posts_3', 'included_posts_3', 'excluded_authors', 'included_authors', 'excluded_cats', 'included_cats', 'tag_str_3', 'custom'));
		update_option('similar-posts', $options);
		// Show a message to say we've done something
		echo '<div class="updated settings-error notice"><p>' . __('<b>Settings saved.</b>', 'similar_posts') . '</p></div>';
	}
	//now we drop into html to display the option page form
	?>
		<div class="wrap similarposts-tab-content">

		<form method="post" action="">

		<table class="optiontable form-table">
			<?php
				sprp_display_shortcode();
				ppl_display_limit_i($options['limit_3'], $num);
				ppl_display_age($options['age']);
				ppl_display_match_cat_i($options['match_cat_3'], $num);
				ppl_display_match_tags_i($options['match_tags_3'], $num);
				sprp_position_related_i($options['pstn_rel_3'], $num);
				sprp_paragraph_i($options['para_rel_3'], $options['pstn_rel_3'], $num);
				sprp_design_related_i($options['re_design_3'], $num);
			?>
		</table>
		<table class="optiontable form-table">
			<?php sprp_adv_filter_switch($options['adv_filter_check_3'], $num); ?>
		</table>
		<?php
			$hide_filter = (isset($options['adv_filter_check_3']) && $options['adv_filter_check_3'] == 1) ? '' : 'style="display:none"';
		?>
		<table id="filter_options" class="optiontable form-table" <?php echo $hide_filter; ?>>
			<?php
				ppl_display_excluded_posts_i($options['excluded_posts_3'], $num);
				ppl_display_included_posts_i($options['included_posts_3'], $num);
				ppl_display_authors($options['excluded_authors'], $options['included_authors']);
				ppl_display_cats($options['excluded_cats'], $options['included_cats']);
				ppl_display_tag_str_i($options['tag_str_3'], $num);
				ppl_display_custom($options['custom']);
			?>
		</table>

		<div class="submit"><input type="submit" class="button button-primary" name="update_options" value="<?php _e('Save Settings', 'similar_posts') ?>" /></div>
		<?php if (function_exists('wp_nonce_field')) wp_nonce_field('similar-posts-update-options'); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_footer', 'srp_admin_footer' );
function srp_admin_footer() {
	$current_screen = get_current_screen();
	if ( 'settings_page_similar-posts' !== $current_screen->id && 'widgets' !== $current_screen->id ) {
		return;
	}
	?>
	<div id="file-editor-warning" class="notification-dialog-wrap file-editor-warning rrm-file-editor-warning" style="display:none;">
		<div class="notification-dialog-background"></div>
		<div class="notification-dialog">
			<div class="file-editor-warning-content">
				<div class="file-editor-warning-message">
					<h1><?php esc_html_e( 'Heads up!', 'similar-posts' )?></h1>
					<p><?php esc_html_e( 'Editing this field can introduce issues that could break your site. Please proceed with great care.', 'similar-posts' )?></p>
				</div>
				<p>
					<a id="file-editor-warning-go-back" class="button file-editor-warning-go-back" href="#"><?php esc_html_e( 'Go back', 'similar-posts' )?></a>
					<button type="button" id="rrm-file-editor-warning-dismiss" class="file-editor-warning-dismiss button button-primary"><?php esc_html_e( 'I understand', 'similar-posts' )?></button>
				</p>
			</div>
		</div>
	</div>
	<script>
		jQuery(document).ready(function($){
			$("#adv_filter_check_1").click(function(){
			if($(this).is(':checked')){
				$("#filter_options").show();
			}else{
				$("#filter_options").hide();
			}
			});
			$("#adv_filter_check_2").click(function(){
			if($(this).is(':checked')){
				$("#filter_options").show();
			}else{
				$("#filter_options").hide();
			}
			});
			$("#adv_filter_check_3").click(function(){
			if($(this).is(':checked')){
				$("#filter_options").show();
			}else{
				$("#filter_options").hide();
			}
			});
			$("#pstn_rel_1").change(function(){
			if($('#pstn_rel_1').val() == 'ibc'){
		       $("#para_rel_1").parents('tr').show();
		    }else{
		       $("#para_rel_1").parents('tr').hide();
		    }
			});
			$("#pstn_rel_2").change(function(){
			if($('#pstn_rel_2').val() == 'ibc'){
		       $("#para_rel_2").parents('tr').show();
		    }else{
		       $("#para_rel_2").parents('tr').hide();
		    }
			});
			$("#pstn_rel_3").change(function(){
			if($('#pstn_rel_3').val() == 'ibc'){
		       $("#para_rel_3").parents('tr').show();
		    }else{
		       $("#para_rel_3").parents('tr').hide();
		    }
			});
		});
		</script>
	<?php
}


//Cron job functions starts here
// add_filter( 'cron_schedules', 'saswp_server_add_cron_recurrence_interval');
// function saswp_server_add_cron_recurrence_interval( $schedules ) {
	
//     $schedules['saswp_server_on_seconds'] = array(
//             'interval'  => 10,
//             'display'   => __( 'Every 5 Minutes', 'textdomain' )
//     );
    
//     return $schedules;
//   }

//add_action('wi_create_daily_backup', 'super_related_posts_save_entries');
  
  
function super_related_posts_save_entries(){
		error_log('running cron job');
//	if(get_option('srp_entries_status') != 'finish'){
		
		$start = 0;
		$batch = 50;

		if(get_option('srp_entries_position')){
			$start = get_option('srp_entries_position');
		}		
		save_index_entries($start,true, false, $batch, true);
//	}
	
}

// sets up the index for the blog
function save_index_entries ($start = 0, $utf8=false, $use_stemmer='false', $batch=100, $cjk=false) {
	
	global $wpdb, $table_prefix;	
	$table_name = $table_prefix.'similar_posts';	
	$termcount = 0;	
	// in batches to conserve memory	
		$posts = $wpdb->get_results("SELECT `ID`, `post_title`, `post_content`, `post_type` FROM $wpdb->posts LIMIT $start, $batch", ARRAY_A);
		
		if($posts){
			
			foreach ($posts as $post) {

				if ($post['post_type'] === 'revision') continue;

				$content = sp_get_post_terms($post['post_content'], $utf8, $use_stemmer, $cjk);
				$title = sp_get_title_terms($post['post_title'], $utf8, $use_stemmer, $cjk);
				$postID = $post['ID'];
				$tags = sp_get_tag_terms($postID, $utf8);
				
				$pid = $wpdb->get_var("SELECT pID FROM $table_name WHERE pID=$postID limit 1");

				if (is_null($pid)) {
					$wpdb->query("INSERT INTO `$table_name` (pID, content, title, tags) VALUES ($postID, \"$content\", \"$title\", \"$tags\")");
				} else {
					$wpdb->query("UPDATE $table_name SET content=\"$content\", title=\"$title\", tags=\"$tags\" WHERE pID=$postID" );
				}
				
				$termcount = $termcount + 1;
			}			
			update_option('srp_entries_position', ($start+$batch));
			unset($posts);	
		}else{
			update_option('srp_entries_position', 0);
			update_option('srp_entries_status', 'finish');
		}
		
	return $termcount;
}

// this function gets called when the plugin is installed to set up the index and default options
function super_related_posts_install() {
   	global $wpdb, $table_prefix;

	$table_name = $table_prefix . 'similar_posts';
	$errorlevel = error_reporting(0);
	$suppress = $wpdb->hide_errors();
	$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
			`pID` bigint( 20 ) unsigned NOT NULL ,
			`content` longtext NOT NULL ,
			`title` text NOT NULL ,
			`tags` text NOT NULL ,
			FULLTEXT KEY `title` ( `title` ) ,
			FULLTEXT KEY `content` ( `content` ) ,
			FULLTEXT KEY `tags` ( `tags` )
			) ENGINE = MyISAM CHARSET = utf8;";
	$wpdb->query($sql);
	// MySQL before 4.1 doesn't recognise the character set properly, so if there's an error we can try without
	if ($wpdb->last_error !== '') {
		$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
				`pID` bigint( 20 ) unsigned NOT NULL ,
				`content` longtext NOT NULL ,
				`title` text NOT NULL ,
				`tags` text NOT NULL ,
				FULLTEXT KEY `title` ( `title` ) ,
				FULLTEXT KEY `content` ( `content` ) ,
				FULLTEXT KEY `tags` ( `tags` )
				) ENGINE = MyISAM;";
		$wpdb->query($sql);
	}
	$options = (array) get_option('similar-posts-feed');
	// check each of the option values and, if empty, assign a default (doing it this long way
	// lets us add new options in later versions)
	if (!isset($options['limit'])) $options['limit'] = 5;
	if (!isset($options['skip'])) $options['skip'] = 0;
	if (!isset($options['age'])) {$options['age']['direction'] = 'none'; $options['age']['length'] = '0'; $options['age']['duration'] = 'month';}
	if (!isset($options['divider'])) $options['divider'] = '';
	if (!isset($options['omit_current_post'])) $options['omit_current_post'] = 'true';
	if ( isset($options['show_static'])) {$options['show_pages'] = $options['show_static']; unset($options['show_static']);};
	if (!isset($options['tag_str'])) $options['tag_str'] = '';
	if (!isset($options['excluded_cats'])) $options['excluded_cats'] = '';
	if ($options['excluded_cats'] === '9999') $options['excluded_cats'] = '';
	if (!isset($options['included_cats'])) $options['included_cats'] = '';
	if ($options['included_cats'] === '9999') $options['included_cats'] = '';
	if (!isset($options['excluded_authors'])) $options['excluded_authors'] = '';
	if ($options['excluded_authors'] === '9999') $options['excluded_authors'] = '';
	if (!isset($options['included_authors'])) $options['included_authors'] = '';
	if ($options['included_authors'] === '9999') $options['included_authors'] = '';
	if (!isset($options['included_posts'])) $options['included_posts'] = '';
	if (!isset($options['excluded_posts'])) $options['excluded_posts'] = '';
	if ($options['excluded_posts'] === '9999') $options['excluded_posts'] = '';
	if (!isset($options['stripcodes'])) $options['stripcodes'] = array(array());
	if (!isset($options['match_cat'])) $options['match_cat'] = 'false';
	if (!isset($options['match_tags'])) $options['match_tags'] = 'false';
	if (!isset($options['match_author'])) $options['match_author'] = 'false';
	if (!isset($options['custom'])) {$options['custom']['key'] = ''; $options['custom']['op'] = '='; $options['custom']['value'] = '';}
	if (!isset($options['sort'])) {$options['sort']['by1'] = ''; $options['sort']['order1'] = SORT_ASC; $options['sort']['case1'] = 'false';$options['sort']['by2'] = ''; $options['sort']['order2'] = SORT_ASC; $options['sort']['case2'] = 'false';}
	if (!isset($options['status'])) {$options['status']['publish'] = 'true'; $options['status']['private'] = 'false'; $options['status']['draft'] = 'false'; $options['status']['future'] = 'false';}
	if (!isset($options['group_template'])) $options['group_template'] = '';
	if (!isset($options['weight_content'])) $options['weight_content'] = 0.9;
	if (!isset($options['weight_title'])) $options['weight_title'] = 0.1;
	if (!isset($options['weight_tags'])) $options['weight_tags'] = 0.0;
	if (!isset($options['num_terms'])) $options['num_terms'] = 20;
	if (!isset($options['term_extraction'])) $options['term_extraction'] = 'frequency';
	if (!isset($options['hand_links'])) $options['hand_links'] = 'false';
	update_option('similar-posts-feed', $options);

	$options = (array) get_option('similar-posts');
	// check each of the option values and, if empty, assign a default (doing it this long way
	// lets us add new options in later versions)
	if (!isset($options['limit'])) $options['limit'] = 5;
	if (!isset($options['limit_2'])) $options['limit_2'] = 5;
	if (!isset($options['skip'])) $options['skip'] = 0;
	if (!isset($options['age'])) {$options['age']['direction'] = 'none'; $options['age']['length'] = '0'; $options['age']['duration'] = 'month';}
	if (!isset($options['divider'])) $options['divider'] = '';
	if (!isset($options['omit_current_post'])) $options['omit_current_post'] = 'true';
	if (!isset($options['show_private'])) $options['show_private'] = 'false';
	if (!isset($options['show_pages'])) $options['show_pages'] = 'false';
	if (!isset($options['show_attachments'])) $options['show_attachments'] = 'false';
	// show_static is now show_pages
	if ( isset($options['show_static'])) {$options['show_pages'] = $options['show_static']; unset($options['show_static']);};
	if (!isset($options['none_text'])) $options['none_text'] = __('None Found', 'similar_posts');
	if (!isset($options['no_text'])) $options['no_text'] = 'false';
	if (!isset($options['tag_str'])) $options['tag_str'] = '';
	if (!isset($options['excluded_cats'])) $options['excluded_cats'] = '';
	if ($options['excluded_cats'] === '9999') $options['excluded_cats'] = '';
	if (!isset($options['included_cats'])) $options['included_cats'] = '';
	if ($options['included_cats'] === '9999') $options['included_cats'] = '';
	if (!isset($options['excluded_authors'])) $options['excluded_authors'] = '';
	if ($options['excluded_authors'] === '9999') $options['excluded_authors'] = '';
	if (!isset($options['included_authors'])) $options['included_authors'] = '';
	if ($options['included_authors'] === '9999') $options['included_authors'] = '';
	if (!isset($options['included_posts'])) $options['included_posts'] = '';
	if (!isset($options['included_posts_2'])) $options['included_posts_2'] = '';
	if (!isset($options['excluded_posts'])) $options['excluded_posts'] = '';
	if (!isset($options['excluded_posts_2'])) $options['excluded_posts_2'] = '';
	if ($options['excluded_posts'] === '9999') $options['excluded_posts'] = '';
	if ($options['excluded_posts_2'] === '9999') $options['excluded_posts_2'] = '';
	if (!isset($options['stripcodes'])) $options['stripcodes'] = array(array());
	if (!isset($options['match_cat'])) $options['match_cat'] = 'false';
	if (!isset($options['match_cat_2'])) $options['match_cat_2'] = 'false';
	if (!isset($options['match_tags'])) $options['match_tags'] = 'false';
	if (!isset($options['match_tags_2'])) $options['match_tags_2'] = 'false';
	if (!isset($options['match_author'])) $options['match_author'] = 'false';
	if (!isset($options['content_filter'])) $options['content_filter'] = 'false';
	if (!isset($options['custom'])) {$options['custom']['key'] = ''; $options['custom']['op'] = '='; $options['custom']['value'] = '';}
	if (!isset($options['sort'])) {$options['sort']['by1'] = ''; $options['sort']['order1'] = SORT_ASC; $options['sort']['case1'] = 'false';$options['sort']['by2'] = ''; $options['sort']['order2'] = SORT_ASC; $options['sort']['case2'] = 'false';}
	if (!isset($options['status'])) {$options['status']['publish'] = 'true'; $options['status']['private'] = 'false'; $options['status']['draft'] = 'false'; $options['status']['future'] = 'false';}
	if (!isset($options['group_template'])) $options['group_template'] = '';
	if (!isset($options['weight_content'])) $options['weight_content'] = 0.9;
	if (!isset($options['weight_title'])) $options['weight_title'] = 0.1;
	if (!isset($options['weight_tags'])) $options['weight_tags'] = 0.0;
	if (!isset($options['num_terms'])) $options['num_terms'] = 20;
	if (!isset($options['term_extraction'])) $options['term_extraction'] = 'frequency';
	if (!isset($options['hand_links'])) $options['hand_links'] = 'false';
	if (!isset($options['utf8'])) $options['utf8'] = 'false';
	if (!function_exists('mb_internal_encoding')) $options['utf8'] = 'false';
	if (!isset($options['cjk'])) $options['cjk'] = 'false';
	if (!function_exists('mb_internal_encoding')) $options['cjk'] = 'false';
	if (!isset($options['use_stemmer'])) $options['use_stemmer'] = 'false';
	if (!isset($options['batch'])) $options['batch'] = '100';

	update_option('similar-posts', $options);

 	// initial creation of the index, if the table is empty
	// $num_index_posts = $wpdb->get_var("SELECT COUNT(*) FROM `$table_name`");
	// if ($num_index_posts == 0) save_index_entries (($options['utf8'] === 'true'), 'false', $options['batch'], ($options['cjk'] === 'true'));

	// deactivate legacy Super Related Posts Feed if present
	$current = get_option('active_plugins');
	if (in_array('Similar_Posts_Feed/similar-posts-feed.php', $current)) {
		array_splice($current, array_search('Similar_Posts_Feed/similar-posts-feed.php', $current), 1);
		update_option('active_plugins', $current);
	}
	unset($current);

 	// clear legacy custom fields
	$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = 'similarterms'");

	// clear legacy index
	$indices = $wpdb->get_results("SHOW INDEX FROM $wpdb->posts", ARRAY_A);
	foreach ($indices as $index) {
		if ($index['Key_name'] === 'post_similar') {
			$wpdb->query("ALTER TABLE $wpdb->posts DROP INDEX post_similar");
			break;
		}
	}

	$wpdb->show_errors($suppress);
	error_reporting($errorlevel);
}

if (!function_exists('ppl_plugin_basename')) {
	if ( !defined('WP_PLUGIN_DIR') ) define( 'WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins' );
	function ppl_plugin_basename($file) {
		$file = str_replace('\\','/',$file); // sanitize for Win32 installs
		$file = preg_replace('|/+|','/', $file); // remove any duplicate slash
		$plugin_dir = str_replace('\\','/',WP_PLUGIN_DIR); // sanitize for Win32 installs
		$plugin_dir = preg_replace('|/+|','/', $plugin_dir); // remove any duplicate slash
		$file = preg_replace('|^' . preg_quote($plugin_dir, '|') . '/|','',$file); // get relative path from plugins dir
		return $file;
	}
}

add_action('activate_super-related-posts/super-related-posts.php', 'super_related_posts_install');
