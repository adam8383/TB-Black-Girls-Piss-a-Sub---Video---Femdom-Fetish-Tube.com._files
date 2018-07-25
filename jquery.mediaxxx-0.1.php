var post_timers         = new Array();
function destroy( selector )
{
    $(selector).fadeOut();
}

function user_posting(selector, content, notimeout)
{
    $(selector).removeClass().addClass('posting');
    $(selector).html(content);
    $(selector).fadeIn();
    if (typeof notimeout=="undefined") {
        if ( typeof post_timers[0] == "number" ) {
            clearTimeout(post_timers[0]);
      }
      post_timers[0] = setTimeout("destroy('" + selector + "')", 3000);
    }
}

function user_posting_load(selector, content)
{
    user_posting(selector, '<img src="' + base_url + '/images/ajax_loader_share.gif" />&nbsp;' + content, 1);
}

function user_response(selector, content)
{
    $(selector).removeClass().addClass('response');
    $(selector).html(content);
    $(selector).fadeIn();
    if ( typeof post_timers[0] == "number" ) {
        clearTimeout(post_timers[0]);
    }
    post_timers[0] = setTimeout("destroy('" + selector + "')", 2000);
}

function reset_chars_counter()
{
    $('#chars_left').html('1000 chars left');
}

function insert_media(type, page)
{    
    var media_content   = $('#media_content');
    user_posting('#media_message', 'Loading...');
    $.post(base_url + '/ajax/insert_' + type, { page: page },
    function (response) {
        if ( response.status == 0 ) {
            user_posting('#media_message', response.msg);
        } else {
            $(media_content).html(response.code);
            $(media_content).fadeIn();
        }
    }, 'json');
}

$(document).ready(function(){

    $("textarea[id*='_comment']").keyup(function(){
        var textarea_id = $(this).attr('id');
        var chars_left  = 1000 - $("textarea[id='" + textarea_id + "']").val().length;
        if ( chars_left < 0 ) {
            chars_left = 0;
        }
        $('#chars_left').html(chars_left + ' chars left');
    });

    $("[id*='invite_as_friend_']").livequery('click', function(event) {
        event.preventDefault();
        $('#invite_message').fadeIn();
    });
    
    $("#close_invite_message").click(function(event) {
        event.preventDefault();
        $('#invite_message').fadeOut();
    });
    
    $("[id*='open_sendmymessage_']").livequery('click', function(event) {
        event.preventDefault();
        $('#sendmymessage').fadeIn();
    });
    
    $("#close_sendmymessage").click(function(event) {
        event.preventDefault();
        $('#sendmymessage').fadeOut();
    });
    
    $("a[id*='invitation_sent'], a[id*='friendship_removed_']").livequery('click', function(event) {
        event.preventDefault();
    });
    
    $("input[id='send_sendmymessage']").click(function(event) {
        var message         = $("textarea[id='sendmymessage_msg']").val();
        var subject         = $("input[id='sendmymessage_sub']").val();
        var user_id         = $("input[id='muser_id']").val();
        if ( subject.length > 200 ) {
            $('#sendmymessage_sub_error').html('Subject can contain maximum 100 characters!');
            $('#sendmymessage_sub_error').fadeIn();
            return false;
        }
        else if ( subject.length < 2 ) {
            $('#sendmymessage_sub_error').html('Subject cannot be less than 2 characters!');
            $('#sendmymessage_sub_error').fadeIn();
            return false;
        }
        else if ( message.length > 200 ) {
            $('#sendmymessage_error').html('Message can contain maximum 200 characters!');
            $("#sendmymessage_sub_error").fadeOut();
            $('#sendmymessage_error').fadeIn();
            return false;
        }
        else if ( message.length < 5 ) {
            $('#sendmymessage_error').html('Message cannot be less than 5 characters!');
            $("#sendmymessage_sub_error").fadeOut();
            $('#sendmymessage_error').fadeIn();
            return false;
        }
        
        user_posting_load('#user_message', 'Sending...');
        $.post(base_url + '/ajax/send_message', { user_id: user_id, subject: subject, message: message },
        function (response) {            
            $("#sendmymessage").fadeOut();
            $("#sendmymessage_error").html('');
            $("#sendmymessage_sub_error").html('');
            user_response('#user_message', response);
        });
    });
    
    $("input[id='send_friend_invite']").click(function(event) {
        var message         = $("textarea[id='invite_friend_message']").val();
        var user_id         = $("input[id='user_id']").val();
        if ( message.length > 200 ) {
            $('#invite_friend_error').html('Message can contain maximum 200 characters!');
            $('#invite_friend_error').fadeIn();
            return false;
        }
        
        user_posting_load('#user_message', 'Sending...');
        $.post(base_url + '/ajax/invite_friend', { user_id: user_id, message: message },
        function (response) {            
            $("#invite_message").fadeOut();
            $("#invite_message").html('');
            user_response('#user_message', response);
            $("li[id='add_friend']").html('<a href="#invite_friend" id="invition_sent_' + user_id + '">Friendship Sent</a>');
        });
    });
    
    $("a[id*='add_block_'],a[id*='remove_block_']").livequery('click', function(event) {
        event.preventDefault();
        var block_id    = $(this).attr('id');
        var id_split    = block_id.split('_');
        var action      = id_split[0];
        var user_id     = id_split[2];
        if ( action == 'add' ) {
            var ajax_act    = 'block';
            var block_msg   = 'Blocking...';
        } else {
            var ajax_act    = 'unblock';
            var block_msg   = 'Unblocking...';
        }
        user_posting('#user_message', block_msg);
        $.post(base_url + '/ajax/' + ajax_act + '_user', { user_id: user_id },
        function(response) {
            user_response('#user_message', response.msg);
            if ( response.status == 1 ) {
                if ( action == 'add' ) {
                    $("li[id='block_user']").html('<a href="#unblock_user" id="remove_block_' + user_id + '">Unblock User</a>');
                } else {
                    $("li[id='block_user']").html('<a href="#block_user" id="add_block_' + user_id + '">Block User</a>');
                }
            }
        }, 'json');
    });
    
    $("a[id*='block_username_'],a[id*='unblock_username_']").livequery('click', function(event) {
        event.preventDefault();
        var act_id      = $(this).attr('id');
        var id_split    = act_id.split('_');
        var action      = id_split[0];
        var user_id     = id_split[2];
        $.post(base_url + '/ajax/' + action + '_user', { user_id: user_id },
        function(response) {
            if (response.status == 1) {
                if ( action == 'block' ) {
                    $('#unblock_' + user_id).html('<a href="#unblock" id="unblock_username_' + user_id + '">Unblock</a>');
                } else {
                    $('#unblock_' + user_id).html('<a href="#block" id="block_username_' + user_id + '">Block</a>');
                }
            } 
        }, 'json');
    });
    
    $("a[id='open_report_user']").click(function(event) {
        event.preventDefault();
        $('#report_message').fadeIn();
    });
    
    $("a[id='close_report_message']").click(function(event) {
        event.preventDefault();
        $('#report_message').fadeOut();
    });
    
    $("input[id*='report_reason_']").click(function() {
        var click_id    = $(this).attr('id');
        if ( click_id == 'report_reason_4' ) {
            $('#other_message').show();
        } else {
            $('#other_message').hide();
        }
    });
    
    $("input[id*='send_flag_user']").click(function() {
        var click_id    = $(this).attr('id');
        var id_split    = click_id.split('_');
        var user_id     = id_split[3];
        var reason      = $("input[@name='report_reason']:checked").val();
        var other       = $("textarea[id='other_reason']").val();
        if ( other.length > 100 ) {
            user_posting('#user_message', 'Message can contain maximum 100 characters!');
            return false;
        }
        
        user_posting('#user_message', 'Posting...');
        $.post(base_url + '/ajax/report_user', { user_id: user_id, reason: reason, other: other },
        function(response) {
            if ( response.status == 0 ) {
                user_posting('#user_message', response.msg);
            } else {
                user_response('#user_message', 'User successfully flaged!');
                $('#report_message').fadeOut();
            }
        }, 'json');
    });

    $("[id*='remove_from_friends_']").livequery('click', function(event) {
        event.preventDefault();
        var remove_id   = $(this).attr('id');
        var id_split    = remove_id.split('_');
        var user_id     = id_split[3];
        user_posting('#user_message', 'Removing...');
        $.post(base_url + '/ajax/remove_friend', { user_id: user_id },
        function (response) {            
            user_response('#user_message', response);
            $("li[id='remove_friend']").html('<a href="#friendship_removed" id="friendship_removed_{$user.UID}">Friends Removed</a>');
        });        
    });

    $("[id*='subscribe_to_']").livequery('click', function(event) {
        event.preventDefault();
        var subscribe_id    = $(this).attr('id');
        var id_split        = subscribe_id.split('_');
        var user_id         = id_split[2];
        
        user_posting('#user_message', 'Subscribing...');        
        $.post(base_url + '/ajax/subscribe', { user_id: user_id },
        function (response) {
            user_response('#user_message', response);
            $("li[id='handle_subscription']").html('<a href="#unsubscribe_user" id="unsubscribe_from_' + user_id + '">Unsubscribe</a></li>');
        });        
    });                        

    $("[id*='unsubscribe_from_']").livequery('click', function(event) {
        event.preventDefault();
        var subscribe_id    = $(this).attr('id');
        var id_split        = subscribe_id.split('_');
        var user_id         = id_split[2];
        
        user_posting('#user_message', 'Unsubscribing...');        
        $.post(base_url + '/ajax/unsubscribe', { user_id: user_id },
        function (response) {
            user_response('#user_message', response);
            $("li[id='handle_subscription']").html('<a href="#subscribe_user" id="subscribe_to_' + user_id + '">Subscribe</a>');
        });        
    });






    $("#advanced_search").click(function(event){
        event.preventDefault();
        $("img[id='loading_advanced_search']").show();
        var search_type = $("select[id='search_type']").val();
        $.post(base_url + '/ajax/search', { search_type: search_type },
            function(response) {
                if ( response != '' ) {
                    $('#advanced_search_container').html(response);
                    $("#search_advanced").slideDown();
                }
        });
        $("img[id='loading_advanced_search']").hide();
    });
    
    
    
    
    
    
    
    
    
    
    
    $("a[id*='search_tab_']").livequery('click', function(event){
        event.preventDefault();
        var tab_clicked = $(this).attr('id');
        var tabs        = $("a[id*='search_tab_']");
        $.each(tabs, function() {
            var tab_current = $(this).attr('id');
            var tab_split   = tab_current.split('_');
            var search_type = tab_split[2];
            var container   = '#search_' + search_type;
            if ( tab_current == tab_clicked ) {
                $("select[id='search_type']").val(search_type);
                $("h2[id='advanced_search_title']").html('ADVANCED ' + search_type.toUpperCase() + ' SEARCH');
                $(container).show();
                $('#' + tab_current).removeClass().addClass('active');
            } else {
                $('#' + tab_current).removeClass();
                $(container).hide();
            }
        });
    });
    
    $("#close_advanced_search").livequery('click', function(event){
        event.preventDefault();
        $("#search_advanced").slideUp();
    });
    
    $("select[id='search_type']").livequery('change', function() {
        var search_type = $("select[id='search_type']").val();
        var tab_clicked = 'search_tab_' + search_type;
        var tabs        = $("a[id*='search_tab_']");
        $("h2[id='advanced_search_title']").html('ADVANCED ' + search_type.toUpperCase() + ' SEARCH');
        $.each(tabs, function() {
            var tab_current = $(this).attr('id');
            var tab_split   = tab_current.split('_');
            var container   = '#search_' + tab_split[2];
            if ( tab_current == tab_clicked ) {
                $(container).show();
                $('#' + tab_current).removeClass().addClass('active');
            } else {
                $('#' + tab_current).removeClass();
                $(container).hide();
            }            
        });
    });
    
    $("a[id*='attach_media_']").livequery('click', function(event) {
        event.preventDefault();
        var attach_id   = $(this).attr('id');
        var id_split    = attach_id.split('_');
        var media_type  = id_split[2];
        var media_id    = id_split[3];
        var attach_str  = '[' + media_type + '=' + media_id + ']';        
        var txtarea     = $("textarea[id='blog_content']");
        if ( $(txtarea).length ) {
            $.markItUp({ replaceWith: attach_str });
        } else {
            if ( $("textarea[id='photo_comment']").length ) {
                var txtbox = $("textarea[id='photo_comment']");
            } else if ( $("textarea[id='video_comment']"). length ) {
                var txtbox = $("textarea[id='video_comment']");
            } else if ( $("textarea[id='wall_comment']").length ) {
                var txtbox = $("textarea[id='wall_comment']");
            } else if ( $("textarea[id='blog_comment']") ) {
                var txtbox = $("textarea[id='blog_comment']");
            }
            
            $(txtbox).val($(txtbox).val() + attach_str);
        }
        $('#media_content').fadeOut(); 
    });
    
    $("a[id*='attach_mcp_']").livequery('click', function(event) {
        event.preventDefault();
        var click_id    = $(this).attr('id');
        var id_split    = click_id.split('_');
        var type        = id_split[2] + '_' + id_split[3];
        insert_media(type, 1);
    });
    
    $("a[id*='p_mc_']").livequery('click', function(event) {
        event.preventDefault();
        var page_id     = $(this).attr('id');
        var page_split  = page_id.split('_');
        var type        = page_split[2] + '_' + page_split[3];
        var page        = page_split[4];
        insert_media(type, page);
    });
    
    $("a[id*='delete_comment_']").livequery('click', function(event) {
        event.preventDefault();
        var click_id    = $(this).attr('id');
        var id_split    = click_id.split('_');
        var type        = id_split[2];
        var comment_id  = id_split[3];
        var parent_id   = id_split[4];
        user_posting('#delete_response_' + comment_id, 'Deleting...');
        $.post(base_url + '/ajax/' + type + '_comment_delete', { comment_id: comment_id, parent_id: parent_id },
        function(response) {
            if ( response.status == 0 ) {
                user_posting('#delete_response_' + comment_id, response.msg);
            } else {
                if ( type == 'wall' ) {
                    $('#wall_comment_' + comment_id).fadeOut();
                } else {
                    $('#' + type + '_comment_' + parent_id + '_' + comment_id).fadeOut();
                }
            } 
        }, 'json');
    });
    
    $("a[id*='report_spam_']").livequery('click', function(event) {
        event.preventDefault();
        var click_id    = $(this).attr('id');
        var id_split    = click_id.split('_');
        var type        = id_split[2];
        var comment_id  = id_split[3];
        var parent_id   = id_split[4];
        $.post(base_url + '/ajax/report_spam', { type: type, comment_id: comment_id, parent_id: parent_id },
        function(response) {
            if ( response.status == 0 ) {
                user_posting('#delete_response_' + comment_id, response.msg);
            } else {
                $("span[id='reported_spam_" + comment_id + "_" + parent_id + "']").html('Marked as spam!');
            }
        }, 'json');
    });
    
    $("a[id*='delete_photo_']").click(function(event) {
        event.preventDefault();
        var click_id    = $(this).attr('id');
        var id_split    = click_id.split('_');
        var photo_id    = id_split[2];
        user_posting('#delete_photo_message', 'Deleting...');
        $.post(base_url + '/ajax/delete_photo', { photo_id: photo_id },
        function(response) {
            if ( response.status == 0 ) {
                user_posting('#delete_photo_message', response.msg);
            } else {
                user_response('#delete_photo_message', 'Photo was deleted successfully!');
                $('#album_photo_' + photo_id).fadeOut();
            }
        }, 'json');
    });

    var rating_user_text     = $('#rating_text_user').html();
    var rating_user_current  = $("input[id='current_user_rating']").val();
            
    $("[id*='utar_']").click(function(event) {
        event.preventDefault();
        var star_id     = $(this).attr("id");
        var id_split    = star_id.split('_');
        var rating      = id_split[2];
        var user_id     = id_split[3];
        $("#rating_text_user").html('Thanks for rating!');
        $.post(base_url + '/ajax/rate_user', { user_id: user_id, rating: rating },
            function (response) {
                $("#rating_user").html(response.rating_code);
                $("#rating_text_user").html(response.msg);
        }, "json");            
    });

    $("[id*='utar_']").mouseover(function() {
        var star_id     = $(this).attr('id');
        var id_split    = star_id.split('_');
        var rating      = id_split[2];
        var user_id     = id_split[3];
        for ( var i = 1; i<=5; i++ ) {
            var star_sel = $("a[id='utar_user_" + i + "_" + user_id + "']")
            if ( i <= rating )
                $(star_sel).removeClass().addClass('full');
            else
                $(star_sel).removeClass();
        }
        if ( rating == 1 ) {
            $('#rating_text_user').html('Lame');
        } else if ( rating == 2 ) {
            $('#rating_text_user').html('Bleh');
        } else if ( rating == 3 ) {
            $('#rating_text_user').html('Alright');
        } else if ( rating == 4 ) {
            $('#rating_text_user').html('Good');
        } else if ( rating == 5 ) {
            $('#rating_text_user').html('Awesome');
        }
    });
    
    $("ul[id='rating_container_user']").mouseout(function(){
        var star_id     = $("[id*='utar_user_1']").attr('id');
        var id_split    = star_id.split('_');
        var user_id     = id_split[3];
        for ( var i = 0; i < 5; i++ ) {
            var star        = i+1;
            var star_sel    = $("a[id='utar_user_" + star + "_" + user_id + "']");
            if ( rating_user_current >= i+1 ) {
                $(star_sel).removeClass().addClass('full');
            } else if ( rating_user_current >= i+0.5 ) {
                $(star_sel).removeClass().addClass('half');
            } else {
                $(star_sel).removeClass();
            }     
        }
        $('#rating_text_user').html(rating_user_text);
    });
    
    $("input[id*='send_share_']").click(function(event) {
        event.preventDefault();
        var errors          = false;
        var share_id        = $(this).attr('id');
        var id_split        = share_id.split('_');
        var type            = id_split[2];
        var item_id         = id_split[3];
        var share_from      = $("input[id='share_from']").val();
        var share_to        = $("textarea[id='share_to']").val();
        var share_message   = $("textarea[id='share_message']").val();
        var share_from_err  = $("span[id='share_from_error']");
        var share_to_err    = $("span[id='share_to_error']");
        if ( share_from == '' ) {
            errors = true;
            $(share_from_err).html('Please enter your name!');
            $(share_from_err).fadeIn();
        } else {
            $(share_from_err).hide();
        }

        if ( share_to == '' ) {
            errors = true;
            $(share_to_err).html('Please enter at least one recipient email!<br />');
            $(share_to_err).fadeIn();
        } else {
            $(share_to_err).hide();
        }

        if ( errors ) {
            return false;
        }

        var selector = '#share_' + type + '_response';
        $(selector).removeClass().addClass('posting');
        $(selector).html('<img src="' + base_url + '/images/ajax_loader_share.gif" /> Sending...');
        $(selector).fadeIn();
        $.post(base_url + '/ajax/share_' + type, { item_id: item_id, from: share_from, to: share_to, message: share_message },
        function(response) {
            if ( response.status == 0 ) {
                user_posting('#share_' + type + '_response', response.msg);
            } else {
                user_response('#share_' + type + '_response', response.msg);
                setTimeout("destroy('#share_" + type + "_box')", 3500);
            }
        }, 'json');
    });
    
    $("input[id*='submit_flag_']").click(function(event) {
        event.preventDefault();
        var errors          = false;
        var click_id        = $(this).attr('id');
        var id_split        = click_id.split('_');
        var type            = id_split[2];
        var item_id         = id_split[3];
        var flag_id         = $("input[@name='flag_reason']:checked").val();
        var message         = $("textarea[id='flag_message']").val();
        
        if ( flag_id == '' ) {
            errors = true;
            $("span[id='flag_reason_error']").html('Please select a flag reason!');
            $("span[id='flag_reason_error']").fadeIn();
        } else {
            $("span[id='flag_reason_error']").hide();
        }

        if ( errors ) {
            return false;
        }

        user_posting('#flag_' + type + '_response', 'Flagging...');
        $.post(base_url + '/ajax/flag_' + type, { item_id: item_id, flag_id: flag_id, message: message },
        function(response) {
            if ( response.status == 0 ) {
                user_posting('#flag_' + type + '_response', response.msg);
            } else {
                user_response('#flag_' + type + '_response', response.msg);
                setTimeout("destroy('#flag_" + type + "_box')", 3500);
            }
        }, 'json');
    });
});
