
$(document).ready(function(){
    var rating_text     = $('#rating_text').html();
    var rating_current  = $("input[id='current_rating']").val();
    $("#share_video a").click(function(event) {
        event.preventDefault();
        $("#share_video_box").slideToggle(); 
    });
    $("#flag_video a").click(function(event) {
        event.preventDefault();
        $("#flag_video_box").slideToggle();
    });

    $("#embed_video a").click(function(event) {
        event.preventDefault();
        $("#embed_video_box").slideToggle();
    });
    
    $("#close_flag").click(function(event) {
        event.preventDefault();
        $("#flag_video_box").fadeOut();
    });
    
    $("#close_share").click(function(event) {
        event.preventDefault();
        $("#share_video_box").fadeOut();
    });
    
    $("#close_embed").click(function(event) {
        event.preventDefault();
        $("#embed_video_box").fadeOut();
    });
    
    $("#close_favorite").click(function(event) {
        event.preventDefault();
        $("#favorite_video_box").fadeOut();
    });
    
    $("textarea[id='video_embed_code']").click(function() {
        $(this).select();
        $(this).focus();
    });
    
    $("a[id*='favorite_video_']").click(function(event) {
        event.preventDefault();
        var fav_id      = $(this).attr('id');
        var id_split    = fav_id.split('_');
        var video_id    = id_split[2];
        user_posting('#response_message', 'Favoriting...');
        $.post(base_url + '/ajax/favorite_video', { video_id: video_id },
        function (response) {
            if ( response.status == 0 ) {
                user_posting('#response_message', response.msg);
            } else {
                user_response('#response_message', response.msg);
            }    
        }, 'json');                                                
    });
    
    $("[id*='star_']").click(function(event) { 
        event.preventDefault();
        var star_id     = $(this).attr("id");
        var id_split    = star_id.split('_');
        var rating      = id_split[2];
        var video_id    = id_split[3];
        $("#rating_text").html('Thanks for rating!');
        $.post(base_url + '/ajax/rate_video', { video_id: video_id, rating: rating },
        function (response) {
            $("#rating").html(response.rating_code);
            $("#rating_text").html(response.msg);
        }, "json");            
    });
    
    $("[id*='star_']").mouseover(function(){
        var star_id     = $(this).attr('id');
        var id_split    = star_id.split('_');
        var rating      = id_split[2];
        var video_id    = id_split[3];
        for ( var i = 1; i<=5; i++ ) {
            var star_sel = $("a[id='star_video_" + i + "_" + video_id + "']");
            if ( i <= rating )
                $(star_sel).removeClass().addClass('full');
            else
                $(star_sel).removeClass();
        }
        if ( rating == 1 ) {
            $('#rating_text').html('Lame');
        } else if ( rating == 2 ) {
            $('#rating_text').html('Bleh');
        } else if ( rating == 3 ) {
            $('#rating_text').html('Alright');
        } else if ( rating == 4 ) {
            $('#rating_text').html('Good');
        } else if ( rating == 5 ) {
            $('#rating_text').html('Awesome');
        }
    });
    
    $("ul[id='rating_container_video']").mouseout(function(){
        var star_id     = $("[id*='star_video_1_']").attr('id');
        var id_split    = star_id.split('_');
        var video_id    = id_split[3];
        for ( var i = 0; i < 5; i++ ) {
            var star        = i+1;
            var star_sel    = $("a[id='star_video_" + star + "_" + video_id + "']");
            if ( rating_current >= i+1 ) {
                $(star_sel).removeClass().addClass('full');
            } else if ( rating_current >= i+0.5 ) {
                $(star_sel).removeClass().addClass('half');
            } else {
                $(star_sel).removeClass();
            }     
        }
        $('#rating_text').html(rating_text);
    });
    
    $("a#show_related_videos").click(function(event) {
        event.preventDefault();
        $("#video_comments").hide();
        $("#related_videos").fadeIn();
    });

    $("a#show_comments").click(function(event) {
        event.preventDefault();
        $("#related_videos").hide();
        $("#video_comments").fadeIn();
    });
    
    $("input[id*='post_video_comment_']").click(function() {
        var video_msg   = $("#post_message");
        var input_id    = $(this).attr('id');
        var id_split    = input_id.split('_');
        var video_id    = id_split[3];                    
        var comment     = $("textarea[id='video_comment']").val();
        if ( comment == '' ) {
            video_msg.fadeIn();
            return false;
        }
                    
        video_msg.hide();
        user_posting_load('#video_response', 'Posting...', 1);
        reset_chars_counter();
        $.post(base_url + '/ajax/video_comment', { video_id: video_id, comment: comment },
        function(response) {
            if ( response.msg != '' ) {
                $("textarea[id='video_comment']").val('');
                user_posting('#video_response', response.msg);
            } else {
                $(".no_comments").hide();
                $("textarea[id='video_comment']").val('');
                var bDIV = $("#comments_delimiter");
                var cDIV = document.createElement("div");
                $(cDIV).html(response.code);
                $(bDIV).after(cDIV);
                user_response('#video_response', 'Comment successfully posted!');
            }
        }, "json");
    });
    
    $("a[id*='p_video_comments_']").livequery('click', function(event) {
        event.preventDefault();
        var page_id     = $(this).attr('id');
        var id_split    = page_id.split('_');
        var video_id    = id_split[3];
        var page        = id_split[4];
        $.post(base_url + '/ajax/video_pagination', { video_id: video_id, page: page },
        function(response) {
            if ( response != '' ) {
                var comments_id = $('#video_comments_' + video_id);
                $(comments_id).hide();
                $(comments_id).html(response);
                $(comments_id).fadeIn();
            }
        });
    });
        
   
    
    $("a[id*='_related_videos_']").livequery('click', function(event) {
        event.preventDefault();
        var bar_id      = $(this).attr('id');
        var id_split    = bar_id.split('_');
        var move        = id_split[0];
        var video_id    = id_split[3];
        var page        = $("input[id='current_page_related_videos']").val();
        var prev_bar    = $('#prev_related_videos_' + video_id);
        var next_bar    = $('#next_related_videos_' + video_id);
        $('.center_related').fadeIn();
        $.post(base_url + '/ajax/related_videos', { video_id: video_id, page: page, move: move },
        function(response) {
            if ( response.status == '1' ) {
                var related_div = $('#related_videos_container');
                $(related_div).hide();
                $(related_div).html(response.videos);
                $(related_div).show();
                $("input[id='current_page_related_videos']").val(response.page);
                if ( response.pages <= 1 ) {
                    $(prev_bar).hide();
                    $(next_bar).hide();
                }
            
                if ( response.page > 1 ) {
                    $(prev_bar).show();
                } else {
                    $(prev_bar).hide();
                }
            
                if ( response.page >= response.pages ) {
                    $(next_bar).hide();
                } else {
                    $(next_bar).show();
                }
            }
            $('.center_related').hide();
        }, 'json');
    });
});
