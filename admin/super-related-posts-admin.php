<?php

// Admin stuff for Super Related Posts Plugin, Version 2.6.2.0

add_action('admin_menu', 'srp_option_menu', 1);

function srp_option_menu() {
	add_options_page(__('Super Related Posts Options', 'super_related_posts'), __('Super Related Posts', 'super_related_posts'), 'edit_theme_options', 'super-related-posts', 'srp_options_page');
}

function srp_options_page(){
	echo '<div class="wrap"><h2>';
	esc_html__('Super Related Posts ', 'super_related_posts');
	echo '</h2></div>';
	$m = new srp_admin_subpages();
	$m->add_subpage('Related Post1',  'related_post1', 'srp_rp1_options_subpage');
	$m->add_subpage('Related Post2',  'related_post2', 'srp_rp2_options_subpage');
	$m->add_subpage('Related Post3',  'related_post3', 'srp_rp3_options_subpage');
	$m->add_subpage('Posts Caching', 'posts_caching', 'srp_pi_options_subpage');
	$m->display();
}


function srp_rp1_options_subpage(){
	global $wpdb, $wp_version;
	$options = get_option('super-related-posts');
	$num = 1;
	if (isset($_POST['update_options'])) {
		check_admin_referer('super-related-posts-update-options');
		srp_cache_flush();
		// Fill up the options with the values chosen...
		$options = srp_options_from_post($options, array('age1','sort_by_1','display_status_1', 'limit', 'match_cat', 'match_tags', 'pstn_rel_1', 'para_rel_1', 're_design_1', 'adv_filter_check_1', 'excluded_posts', 'included_posts', 'excluded_authors', 'included_authors', 'excluded_cats', 'included_cats', 'tag_str', 'custom'));
		update_option('super-related-posts', $options);
		// Show a message to say we've done something
		echo '<div class="updated settings-error notice"><p>' . __('<b>Settings saved.</b>', 'super_related_posts') . '</p></div>';
	}
	//now we drop into html to display the option page form
	?>
		<div class="wrap superrelatedposts-tab-content">
			<?php if(get_option('srp_posts_caching_status') != 'finished'){ ?>
				<div><strong>To work this plugin faster you need to cache the posts</strong> <a href="<?php echo esc_url(admin_url( 'options-general.php?page=super-related-posts&subpage=posts_caching' )) ?>">Start Caching</a></div>	
			<?php } ?>
		<form method="post" action="">

		<table class="optiontable form-table">
			<?php		
			
			srp_display_status($options['display_status_1'], $num);		
			srp_display_limit($options['limit']);			
			srp_sort_post_by_recent_popular_i($options['sort_by_1'], $num);
			srp_display_age($options['age1'], $options['sort_by_1'], $num);
			srp_display_match_cat($options['match_cat']);
			srp_display_match_tags($options['match_tags']);
			sprp_position_related_i($options['pstn_rel_1'], $num);						
			sprp_paragraph_i($options['para_rel_1'], $options['pstn_rel_1'], $num);
			sprp_design_related_i($options['re_design_1'], $num);			
				
			?>
		</table>
		<table class="optiontable form-table">
			<?php 
				if(isset($options['adv_filter_check_1'])){
					sprp_adv_filter_switch($options['adv_filter_check_1'], $num);
				}
				
			 ?>
		</table>
		<?php
			$hide_filter = (isset($options['adv_filter_check_1']) && $options['adv_filter_check_1'] == 1) ? '' : 'style="display:none"';
		?>
		<table id="filter_options" class="optiontable form-table" <?php echo $hide_filter; ?>>
			<?php
				srp_display_excluded_posts($options['excluded_posts']);
				srp_display_included_posts($options['included_posts']);
				srp_display_authors($options['excluded_authors'], $options['included_authors']);
				srp_display_cats($options['excluded_cats'], $options['included_cats']);
				srp_display_tag_str($options['tag_str']);
				srp_display_custom($options['custom']);
			?>
		</table>

		<div class="submit"><input type="submit" class="button button-primary" name="update_options" value="<?php echo esc_html__('Save Settings', 'super_related_posts') ?>" /></div>
		<?php if (function_exists('wp_nonce_field')) wp_nonce_field('super-related-posts-update-options'); ?>
		</form>
	</div>
	<?php
}

function srp_rp2_options_subpage(){
	global $wpdb, $wp_version;
	$options = get_option('super-related-posts');
	$num = 2;
	if (isset($_POST['update_options'])) {
		check_admin_referer('super-related-posts-update-options');
		srp_cache_flush();
		// Fill up the options with the values chosen...
		$options = srp_options_from_post($options, array('sort_by_2','display_status_2', 'limit_2', 'age2', 'match_cat_2', 'match_tags_2', 'pstn_rel_2', 'para_rel_2', 're_design_2', 'position', 'adv_filter_check_2', 'excluded_posts_2', 'included_posts_2', 'excluded_authors', 'included_authors', 'excluded_cats', 'included_cats', 'tag_str_2', 'custom'));
		update_option('super-related-posts', $options);
		// Show a message to say we've done something
		echo '<div class="updated settings-error notice"><p>' . __('<b>Settings saved.</b>', 'super_related_posts') . '</p></div>';
	}
	//now we drop into html to display the option page form
	?>
		<div class="wrap superrelatedposts-tab-content">
		<?php if(get_option('srp_posts_caching_status') != 'finished'){ ?>
				<div><strong>To work this plugin faster you need to cache the posts</strong> <a href="<?php echo esc_url(admin_url( 'options-general.php?page=super-related-posts&subpage=posts_caching' )) ?>">Start Caching</a></div>	
		<?php } ?>
		<form method="post" action="">

		<table class="optiontable form-table">
			<?php
				srp_display_status($options['display_status_2'], $num);	
				srp_display_limit_i($options['limit_2'], $num);				
				srp_sort_post_by_recent_popular_i($options['sort_by_2'], $num);
				srp_display_age($options['age2'], $options['sort_by_2'], $num);
				srp_display_match_cat_i($options['match_cat_2'], $num);
				srp_display_match_tags_i($options['match_tags_2'], $num);
				sprp_position_related_i($options['pstn_rel_2'], $num);
				sprp_paragraph_i($options['para_rel_2'], $options['pstn_rel_2'], $num);
				sprp_design_related_i($options['re_design_2'], $num);
				
			?>
		</table>
		<table class="optiontable form-table">
			<?php 
				if(isset($options['adv_filter_check_2'])){
					sprp_adv_filter_switch($options['adv_filter_check_2'], $num); 
				}				
			?>
		</table>
		<?php
			$hide_filter = (isset($options['adv_filter_check_2']) && $options['adv_filter_check_2'] == 1) ? '' : 'style="display:none"';
		?>
		<table id="filter_options" class="optiontable form-table" <?php echo $hide_filter; ?>>
			<?php
				if(isset($options['excluded_posts_2'])){
					srp_display_excluded_posts_i($options['excluded_posts_2'], $num);
				}
				if(isset($options['included_posts_2'])){
					srp_display_included_posts_i($options['included_posts_2'], $num);
				}
				srp_display_authors($options['excluded_authors'], $options['included_authors']);
				srp_display_cats($options['excluded_cats'], $options['included_cats']);
				if(isset($options['tag_str_2'])){
					srp_display_tag_str_i($options['tag_str_2'], $num);
				}
				if(isset($options['custom'])){
					srp_display_custom($options['custom']);
				}
				
			?>
		</table>

		<div class="submit"><input type="submit" class="button button-primary" name="update_options" value="<?php echo esc_html__('Save Settings', 'super_related_posts') ?>" /></div>
		<?php if (function_exists('wp_nonce_field')) wp_nonce_field('super-related-posts-update-options'); ?>
		</form>
	</div>
	<?php
}

function srp_pi_options_subpage(){

	
	global $wpdb, $table_prefix;
	$srp_table = $table_prefix . 'super_related_posts';
	$wpp_table = $table_prefix . 'posts';

	$cache_count = $wpdb->get_var("SELECT COUNT(*) FROM `$srp_table`");

	$not_in = array(
		'revision', 
		'wp_global_styles', 
		'attachment',
		'elementor_library',
		'mgmlp_media_folder',
		'custom_css',
		'nav_menu_item',
		'oembed_cache'
	);
	$sql = "SELECT COUNT(*) FROM `$wpp_table` WHERE post_status='publish' AND post_type NOT IN("."'".implode("', '",$not_in)."'".")";
	$posts_count = $wpdb->get_var($sql);	
	$percentage = round( (($cache_count / $posts_count) * 100), 2);	
	$caching_status = get_option('srp_posts_caching_status');
	
	?>
	<div class="wrap superrelatedposts-tab-content">	
	<?php 
		if($caching_status != 'finished'){
			echo '<div id="srp-percentage-div"><p> '.esc_html($percentage).'% is completed. Please start again to finish</p></div>';	
		}
	?>			
	<div class="srp_progress_bar srp_dnone">
        <div class="srp_progress_bar_body" style="width: 50%;">50%</div>
    </div>
	<table class="optiontable form-table">
	<tr valign="top">
		<th scope="row"><label for=""><?php echo esc_html__('Cache Posts:', 'super-related-posts') ?></label></th>
		<td>
		<?php if($caching_status != 'finished'){ ?>	
			<button type="button" id="start-caching-btn" class="button button-primary"><?php esc_html_e( 'Start Caching', 'super-related-posts' )?></button>
		<?php }else{ ?>	
			<button type="button" id="start-caching-btn" class="button button-primary" disabled><?php esc_html_e( 'Start Caching', 'super-related-posts' )?></button>
		<?php } ?>	
			
		</td>
	</tr>
	</table>
	</div>
	<?php
}

function srp_rp3_options_subpage(){
	global $wpdb, $wp_version;
	$options = get_option('super-related-posts');
	$num = 3;
	
	if (isset($_POST['update_options'])) {
		check_admin_referer('super-related-posts-update-options');
		srp_cache_flush();
		// Fill up the options with the values chosen...
		$options = srp_options_from_post($options, array('sort_by_3', 'display_status_3', 'limit_3', 'age3', 'match_cat_3', 'match_tags_3', 'pstn_rel_3', 'para_rel_3', 're_design_3', 'adv_filter_check_3', 'excluded_posts_3', 'included_posts_3', 'excluded_authors', 'included_authors', 'excluded_cats', 'included_cats', 'tag_str_3', 'custom'));
		update_option('super-related-posts', $options);
		// Show a message to say we've done something
		echo '<div class="updated settings-error notice"><p>' . __('<b>Settings saved.</b>', 'super_related_posts') . '</p></div>';
	}
	//now we drop into html to display the option page form
	?>
		<div class="wrap superrelatedposts-tab-content">
		<?php if(get_option('srp_posts_caching_status') != 'finished'){ ?>
				<div><strong>To work this plugin faster you need to cache the posts</strong> <a href="<?php echo esc_url(admin_url( 'options-general.php?page=super-related-posts&subpage=posts_caching' )) ?>">Start Caching</a></div>	
		<?php } ?>
		<form method="post" action="">

		<table class="optiontable form-table">
			<?php
				srp_display_status($options['display_status_3'], $num);	
				sprp_display_shortcode($num);
				srp_display_limit_i($options['limit_3'], $num);				
				srp_sort_post_by_recent_popular_i($options['sort_by_3'], $num);
				srp_display_age($options['age3'], $options['sort_by_3'], $num);
				srp_display_match_cat_i($options['match_cat_3'], $num);
				srp_display_match_tags_i($options['match_tags_3'], $num);
				sprp_position_related_i($options['pstn_rel_3'], $num);
				sprp_paragraph_i($options['para_rel_3'], $options['pstn_rel_3'], $num);
				sprp_design_related_i($options['re_design_3'], $num);				
				
			?>
		</table>
		<table class="optiontable form-table">
			<?php 
				if(isset($options['adv_filter_check_3'])){
					sprp_adv_filter_switch($options['adv_filter_check_3'], $num);
				}				
			 ?>
		</table>
		<?php
			$hide_filter = (isset($options['adv_filter_check_3']) && $options['adv_filter_check_3'] == 1) ? '' : 'style="display:none"';
		?>
		<table id="filter_options" class="optiontable form-table" <?php echo $hide_filter; ?>>
			<?php
				if(isset($options['excluded_posts_3'])){
					srp_display_excluded_posts_i($options['excluded_posts_3'], $num);
				}
				if(isset($options['included_posts_3'])){
					srp_display_included_posts_i($options['included_posts_3'], $num);
				}								
				srp_display_authors($options['excluded_authors'], $options['included_authors']);
				srp_display_cats($options['excluded_cats'], $options['included_cats']);
				if(isset($options['tag_str_3'])){
					srp_display_tag_str_i($options['tag_str_3'], $num);
				}				
				srp_display_custom($options['custom']);
			?>
		</table>

		<div class="submit"><input type="submit" class="button button-primary" name="update_options" value="<?php echo esc_html__('Save Settings', 'super_related_posts') ?>" /></div>
		<?php if (function_exists('wp_nonce_field')) wp_nonce_field('super-related-posts-update-options'); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_footer', 'srp_admin_footer' );
function srp_admin_footer() {
	$current_screen = get_current_screen();
	if ( 'settings_page_super-related-posts' !== $current_screen->id && 'widgets' !== $current_screen->id ) {
		return;
	}
	?>
	<div id="file-editor-warning" class="notification-dialog-wrap file-editor-warning rrm-file-editor-warning" style="display:none;">
		<div class="notification-dialog-background"></div>
		<div class="notification-dialog">
			<div class="file-editor-warning-content">
				<div class="file-editor-warning-message">
					<h1><?php esc_html_e( 'Heads up!', 'super-related-posts' )?></h1>
					<p><?php esc_html_e( 'Editing this field can introduce issues that could break your site. Please proceed with great care.', 'super-related-posts' )?></p>
				</div>
				<p>
					<a id="file-editor-warning-go-back" class="button file-editor-warning-go-back" href="#"><?php esc_html_e( 'Go back', 'super-related-posts' )?></a>
					<button type="button" id="rrm-file-editor-warning-dismiss" class="file-editor-warning-dismiss button button-primary"><?php esc_html_e( 'I understand', 'super-related-posts' )?></button>
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

			$("#sort_by_1").change(function(){
			if($('#sort_by_1').val() == 'popular'){
		       $("#age1-direction").parents('tr').show();
		    }else{
			   $("#age1-direction").parents('tr').hide();
		    }
			});

			$("#sort_by_2").change(function(){
			if($('#sort_by_2').val() == 'popular'){
		       $("#age2-direction").parents('tr').show();
		    }else{
			   $("#age2-direction").parents('tr').hide();
		    }
			});

			$("#sort_by_3").change(function(){
			if($('#sort_by_3').val() == 'popular'){
		       $("#age3-direction").parents('tr').show();
		    }else{
			   $("#age3-direction").parents('tr').hide();
		    }
			});

		});
		</script>
	<?php
}

// sets up the index for the blog
function save_index_entries ($start, $utf8=false, $use_stemmer='false', $batch=500, $cjk=false) {

	global $wpdb, $table_prefix;
	
	$table_name = $table_prefix.'super_related_posts';	
	$termcount = 0;	
	// in batches to conserve memory
	$not_in = array(
		'revision', 
		'wp_global_styles', 
		'attachment',
		'elementor_library',
		'mgmlp_media_folder',
		'custom_css',
		'nav_menu_item',
		'oembed_cache'
	);
	
	$sql = "SELECT `ID`, `post_title`, `post_date`, `post_content`, `post_type` FROM $wpdb->posts WHERE post_status='publish' AND post_type NOT IN("."'".implode("', '",$not_in)."'".") LIMIT $start, $batch";	
	$posts = $wpdb->get_results($sql, ARRAY_A);
	
	if($posts){
		
		$to_be_inserted = array();		

		foreach ($posts as $post) {
																
			$title  = srpp_get_title_terms($post['post_title'], $utf8, $use_stemmer, $cjk);
			$postID = $post['ID'];
			$tags   = srpp_get_tag_terms($postID, $utf8);
			$sdate  = date("Ymd",strtotime($post['post_date']));							
			$pid = $wpdb->get_var("SELECT pID FROM $table_name WHERE pID=$postID limit 1");
			
			if (is_null($pid)) {				
				$to_be_inserted[] = array(
					'pID'   	  => $postID,
					'title' 	  => $title,
					'tags'  	  => $tags,
					'spost_date'  => $sdate,
				);
			}else{
				//$wpdb->query("UPDATE $table_name SET title=\"$title\", tags=\"$tags\" WHERE pID=$postID" );
			}

			$termcount = $termcount + 1;
		}

		if(!empty($to_be_inserted)){

			$values = $place_holders = array();

			foreach($to_be_inserted as $data) {
				array_push( $values, $data['pID'], $data['title'], $data['tags'], $data['spost_date']);
				$place_holders[] = "( %d, %s, %s, %d)";
			}

			$query           = "INSERT INTO `$table_name` (`pID`, `title`, `tags`, `spost_date`) VALUES ";
			$query           .= implode( ', ', $place_holders );
			$sql             = $wpdb->prepare( "$query ", $values );

			$wpdb->query( $sql );

		}
		
		$start += $batch;
		update_option('srp_posts_offset', $start);
		update_option('srp_posts_caching_status', 'continue');
		if (!ini_get('safe_mode')) set_time_limit(30);
	}else{
		update_option('srp_posts_offset', 0);
		update_option('srp_posts_caching_status', 'finished');
	}
			
	unset($posts);	
	return $termcount;
}

add_action( 'wp_ajax_srp_start_posts_caching', 'srp_start_posts_caching'); 

function srp_start_posts_caching(){
			
	 if ( ! isset( $_GET['srp_security_nonce'] ) ){
		return; 
	 }
	 
	 if ( !wp_verify_nonce( $_GET['srp_security_nonce'], 'srp_ajax_check_nonce' ) ){
		return;  
	 }
	 
	 if(get_option('srp_posts_caching_status') == 'finished'){
		$status = array('status' => 'finished', 'percentage'=> "100%");
	 }else{

		global $wpdb, $table_prefix;		
		$wpp_table = $table_prefix . 'posts';
		
		global $posts_count;

		if(!$posts_count){

			$not_in = array(
				'revision', 
				'wp_global_styles', 
				'attachment',
				'elementor_library',
				'mgmlp_media_folder',
				'custom_css',
				'nav_menu_item',
				'oembed_cache'
			);

			$sql = "SELECT COUNT(*) FROM `$wpp_table` WHERE post_status='publish' AND post_type NOT IN("."'".implode("', '",$not_in)."'".")";
			$posts_count = $wpdb->get_var($sql);
		}

		$start = 0;
	  	if(get_option('srp_posts_offset')){
			$start = get_option('srp_posts_offset');
		}
	
		$percentage = round( (($start / $posts_count) * 100), 2);					
		
	 	$result = save_index_entries ($start, true, 'false', 500, true);		
		if($result > 0){
			$status = array('status' => 'continue', 'percentage' => $percentage."%");
		}else{
			$status = array('status' => 'finished', 'percentage' => "100%");
		}
	 }	 	 	 	 

	 echo wp_json_encode($status);
	 wp_die();           
}

// this function gets called when the plugin is installed to set up the index and default options
function super_related_posts_install() {
   	global $wpdb;

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$charset_collate = $engine = '';	
	
	if(!empty($wpdb->charset)) {
		$charset_collate .= " DEFAULT CHARACTER SET {$wpdb->charset}";
	} 
	if($wpdb->has_cap('collation') AND !empty($wpdb->collate)) {
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}

	$found_engine = $wpdb->get_var("SELECT ENGINE FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA` = '".DB_NAME."' AND `TABLE_NAME` = '{$wpdb->prefix}posts';");
        
	if(strtolower($found_engine) == 'innodb') {
		$engine = ' ENGINE=InnoDB';
	}

	$found_tables = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}super_related%';");	
    
    if(!in_array("{$wpdb->prefix}super_related_posts", $found_tables)) {
            
		dbDelta("CREATE TABLE `{$wpdb->prefix}super_related_posts` (
			`pID` bigint( 20 ) unsigned NOT NULL,
			`title` text NOT NULL,
			`tags` text NOT NULL,		
			`spost_date` bigint unsigned NOT NULL,	
			`views` bigint unsigned NOT NULL default 0,
			FULLTEXT KEY `title` ( `title` ) ,			
			FULLTEXT KEY `tags` ( `tags` ),
			KEY `views` ( `views` ),	
			KEY `spost_date` ( `spost_date` )			
		) ".$charset_collate.$engine.";");                
    }

	if(!in_array("{$wpdb->prefix}super_related_cached", $found_tables)) {
		
		dbDelta("CREATE TABLE `{$wpdb->prefix}super_related_cached` (
			`cpID` bigint( 20 ) unsigned NOT NULL,
			`ckey` varchar(60) NOT NULL default '',
			`cvalue` TEXT,									
			KEY `cpID` ( `cpID` ),	
			KEY `ckey` ( `ckey` )			
		) ".$charset_collate.$engine.";");
    }
	
	$options = (array) get_option('super-related-posts-feed');
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
	update_option('super-related-posts-feed', $options);

	$options = (array) get_option('super-related-posts');
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
	if (!isset($options['none_text'])) $options['none_text'] = __('None Found', 'super_related_posts');
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

	update_option('super-related-posts', $options);

 	// clear legacy custom fields
	$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = 'srpterms'");

	// clear legacy index
	$indices = $wpdb->get_results("SHOW INDEX FROM $wpdb->posts", ARRAY_A);
	foreach ($indices as $index) {
		if ($index['Key_name'] === 'post_super_related') {
			$wpdb->query("ALTER TABLE $wpdb->posts DROP INDEX post_super_related");
			break;
		}
	}

	$wpdb->show_errors($suppress);
	error_reporting($errorlevel);
}

if (!function_exists('srp_plugin_basename')) {
	if ( !defined('WP_PLUGIN_DIR') ) define( 'WP_PLUGIN_DIR', ABSPATH . 'wp-content/plugins' );
	function srp_plugin_basename($file) {
		$file = str_replace('\\','/',$file); // sanitize for Win32 installs
		$file = preg_replace('|/+|','/', $file); // remove any duplicate slash
		$plugin_dir = str_replace('\\','/',WP_PLUGIN_DIR); // sanitize for Win32 installs
		$plugin_dir = preg_replace('|/+|','/', $plugin_dir); // remove any duplicate slash
		$file = preg_replace('|^' . preg_quote($plugin_dir, '|') . '/|','',$file); // get relative path from plugins dir
		return $file;
	}
}

add_action('activate_super-related-posts/super-related-posts.php', 'super_related_posts_install');

add_action( 'admin_enqueue_scripts', 'srp_enqueue_style_js' );

function srp_enqueue_style_js( $hook ) {              		
	
	if( $hook == 'settings_page_super-related-posts'){

		$data = array(     			
			'ajax_url'                     => admin_url( 'admin-ajax.php' ),            
			'srp_security_nonce'         => wp_create_nonce('srp_ajax_check_nonce'),  		
		);
						
		$data = apply_filters('srp_localize_filter',$data,'srp_localize_data');					   
	
		wp_register_script( 'srp-admin-js', SRPP_PLUGIN_URI . 'js/srp-admin.js', array('jquery'), SuperRelatedPosts::$version , true );					
		wp_localize_script( 'srp-admin-js', 'srp_localize_data', $data );	
		wp_enqueue_script( 'srp-admin-js' );			

	}
	
}

add_action( 'admin_notices', 'srp_admin_notice' );

function srp_admin_notice(){
	
	if(get_option('srp_posts_caching_status') != 'finished'){
	?>
					<div class="notice notice-warning">
                    	<p>
                    		<strong><?php echo esc_html__( 'Welcome to Super Related Posts' , 'super-related-posts') ?></strong> <?php echo esc_html__( '- To work this plugin faster you need to cache the posts' , 'super-related-posts') ?>
						</p>
						<p>
						<a class="button button-primary" href="<?php echo esc_url(admin_url( 'options-general.php?page=super-related-posts&subpage=posts_caching' )); ?>">
						<?php echo esc_html__( 'Start Caching' , 'super-related-posts') ?></a>
						</p>
						</div>
                    
	<?php
	    
	}	

}