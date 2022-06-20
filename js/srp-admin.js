jQuery(document).ready(function($){

    function srp_start_caching_ajax(current){
        current.addClass('updating-message');
        $.get( ajaxurl,{                    
            action:"srp_start_posts_caching", 
            srp_security_nonce:srp_localize_data.srp_security_nonce
            }, function(response) {
                $("#srp-percentage-div").addClass('srpp_dnone');
                current.removeClass('updating-message');                 
                if(response.status === 'continue'){
                    $(".srpp_progress_bar").removeClass('srpp_dnone');
                    $(".srpp_progress_bar_body").css("width", response.percentage);
                    $(".srpp_progress_bar_body").text(response.percentage);
                    srp_start_caching_ajax(current);
                }
                if(response.status === 'finished'){                                           
                    $(".srpp_progress_bar_body").css("width", response.percentage);
                    $(".srpp_progress_bar_body").text(response.percentage);
                    $(".srpp_progress_bar").addClass('srpp_dnone');         
                    alert('Cached Successfully');        
                }                
        },'json')
        .done(function() {        
            console.log( "second success" );
        })
        .fail(function() {
            current.removeClass('updating-message');             
            alert('Process broke. Click on Start again');        
        })
        .always(function() {
            //current.removeClass('updating-message'); 
            console.log( "finished" );
        });

    }

    $("#start-caching-btn").click(function(){
            var current = $(this);
            srp_start_caching_ajax(current);
    });
            
});