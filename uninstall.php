<?php

	if (defined('ABSPATH') && defined('WP_UNINSTALL_PLUGIN')) {
		global $wpdb, $table_prefix;

		delete_option('similar-posts');
		delete_option('similar-posts-feed');
		delete_option('widget_rrm_similar_posts');
		delete_option('srp_entries_position');
		delete_option('srp_entries_status');

		$table_name = $table_prefix . 'similar_posts';
		$wpdb->query("DROP TABLE `$table_name`");
	}
