jQuery(document).ready(function($){

    function srpp_start_caching_ajax(current){
        current.addClass('updating-message');
        $.get( ajaxurl,{                    
            action:"srpp_start_posts_caching", 
            srp_security_nonce:srp_localize_data.srp_security_nonce
            }, function(response) {
                $("#srp-percentage-div").addClass('srpp_dnone');
                current.removeClass('updating-message');                 
                if(response.status === 'continue'){
                    $(".srpp_progress_bar").removeClass('srpp_dnone');
                    $(".srpp_progress_bar_body").css("width", response.percentage);
                    $(".srpp_progress_bar_body").text(response.percentage);
                    srpp_start_caching_ajax(current);
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
            srpp_start_caching_ajax(current);
    });

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