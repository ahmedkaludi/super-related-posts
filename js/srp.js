jQuery(document).ready(function($){
    $.ajax({
        type: "POST",    
        url:srp_localize_front_data.ajax_url,                    
        dataType: "json",
        data:{
              action:"srp_update_post_views_ajax",
              post_id:srp_localize_front_data.post_id, 
              srp_security_nonce:srp_localize_front_data.srp_security_nonce
            },
        success:function(response){                                                             
        },
        error: function(response){                            
        }
        });

});