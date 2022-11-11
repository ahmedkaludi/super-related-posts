<?php 
/**
 * Newsletter class
 *
 * @author   Magazine3
 * @category Admin
 * @path     admin_section/newsletter
 * @Version 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class srpwp_ads_newsletter {
        
	function __construct () {
		
                add_filter( 'srp_localize_filter',array($this,'srp_add_localize_footer_data'),10,2);
                add_action('wp_ajax_srp_subscribe_to_news_letter', array($this, 'srp_subscribe_to_news_letter'));
        }
        
        function srp_subscribe_to_news_letter(){

                if ( ! isset( $_POST['srp_security_nonce'] ) ){
                    return; 
                }
                if ( !wp_verify_nonce( $_POST['srp_security_nonce'], 'srp_ajax_check_nonce' ) ){
                   return;  
                }
                                
	        $name    = sanitize_text_field($_POST['name']);
                $email   = sanitize_text_field($_POST['email']);
                $website = sanitize_text_field($_POST['website']);
                
                if($email){
                        
                    $api_url = 'http://magazine3.company/wp-json/api/central/email/subscribe';

		    $api_params = array(
		        'name'    => $name,
		        'email'   => $email,
		        'website' => $website,
		        'type'    => 'suprp'
                    );
                    
		    $response = wp_remote_post( $api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
                    $response = wp_remote_retrieve_body( $response );                    
		    echo $response;

                }else{
                        echo 'Email id required';                        
                }                        

                wp_die();
        }
	        
        function srp_add_localize_footer_data($object, $object_name){
            
        $dismissed = explode (',', get_user_meta (wp_get_current_user()->ID, 'dismissed_wp_pointers', true));
        $do_tour   = !in_array ('srpwp_subscribe_pointer', $dismissed);
        if ($do_tour) {
                wp_enqueue_style ('wp-pointer');
                wp_enqueue_script ('wp-pointer');						
	}
                        
        if($object_name == 'srp_localize_data'){
                        
                global $current_user;                
		$tour     = array ();
                $tab      = isset($_GET['tab']) ? esc_attr($_GET['tab']) : '';                   
                
                if (!array_key_exists($tab, $tour)) {                
			                                           			            	
                        $object['do_tour']            = $do_tour;        
                        $object['get_home_url']       = get_home_url();                
                        $object['current_user_email'] = $current_user->user_email;                
                        $object['current_user_name']  = $current_user->display_name;        
			$object['displayID']          = '#menu-posts-srpwp';                        
                        $object['button1']            = saswp_t_string('No Thanks');
                        $object['button2']            = false;
                        $object['function_name']      = '';                        
		}
		                                                                                                                                                    
        }
        return $object;
         
    }
       
}
$srp_ads_newsletter = new srpwp_ads_newsletter();
?>