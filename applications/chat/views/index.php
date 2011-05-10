<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>C.C.R.I.E.</title>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/jquery.media.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                var lastAuthor = '';
                refresh = function(){
                    $.post("<?php echo $this->url('chat/index/post'); ?>", {message:'', last:$("input#lastMessage").val()}, writePosts);
                };

                makeDate = function(stamp)
                {
                    var regex=/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/;
                    var parts=stamp.replace(regex,"$1 $2 $3 $4 $5 $6").split(' ');
                    return new Date(parts[0],parts[1]-1,parts[2],parts[3],parts[4],parts[5]);
                };

                writePosts = function(data){
                            if(data.status == 'ok')
                            {
                                $("input#lastMessage").val(data.lastMessage);
                                $("div#availableUsers").empty().append(data.loggedIn);
                                for(i = 0; i < data.content.length; i++)
                                {
                                    var date = makeDate(data.content[i].t);
                                    if(lastAuthor != data.content[i].u)
                                    {
                                        $("dl#messageList").append('<dt><strong>'+data.content[i].u+'</strong> '+date.getHours()+':'+ (date.getMinutes() < 10 ? ('0'+date.getMinutes()) : date.getMinutes()) +'</dt><dd><p>'+data.content[i].m+'</p></dd>');
                                    }
                                    else
                                    {
                                        $("dl#messageList dd:last").append('<p>'+data.content[i].m+'</p>');
                                    }
                                    lastAuthor = data.content[i].u;
                                    
                                }
                                if(data.content.length > 0)
                                {
                                    document.getElementById('messagesWrapper').scrollTop=document.getElementById('messagesWrapper').scrollHeight;
                                    $.fn.media.defaults.flvPlayer = '/static/flvplayer.swf';
                                    $.fn.media.defaults.mp3Player = '/static/flvplayer.swf';
                                    $('a.mp3').media( { width: 320, height: 24 } );
                                    //$('a.youtube').media( { width: 320, height: 240} );
                                }
                            }
                            if(data.status == 'ko')
                            {
                                alert(data.content);
                            }
                        };

                $("form#sendMessage").bind("submit", function(){
                    var msg = $.trim($("input#message").val());
                    if(msg == '/cls')
                    {
                        $("dl#messageList").empty();
                        $("input#message").val("").focus();
                        lastAuthor = '';
                        return false;
                    }
                    if(msg.substr(0, 6) == '/title')
                    {
                        $("title").empty().append(msg.substr(6, 128));
                        $("input#message").val("").focus();
                        return false;
                    }
                    if(msg.substr(0, 5) == '/help')
                    {
                        alert('Shortcuts:\n\n/title change this window title\n/last 15 : last 15 messages\n/lasturl 15 : last 15 links\n/lastimg 15 : last 15 pictures\n/cls : clear screen\n/help : this help\n\nBBCode:\n\nLink: [url:http://www.acme.com]\nPicture: [img:http://www.acme.com/acme.jpg]\nMp3: [mp3:http://www.acme.com/test.mp3]');
                        $("input#message").val("").focus();
                        return false;
                    }
                    $.post("<?php echo $this->url('chat/index/post'); ?>", {message:$("input#message").val(), last:$("input#lastMessage").val()}, writePosts);
                    $("input#message").val("").focus();
                    document.getElementById("messagesWrapper").scrollTop = document.getElementById("messagesWrapper").scrollHeight;
                    return false;
                });
                resizeLayout = function(){
                    $("div#messagesWrapper").height($(window).height() - $("div#messageForm").height() - 10);
                    $("input#message").width($(window).width() - 40);
                };
                $(window).resize(resizeLayout);
                resizeLayout();
                $("input#message").focus();
                refresh();
                var refreshInterval = setInterval('refresh()',2000);
            });
        </script>
        <style type="text/css">
            *{margin:0;padding:0;}
            body{font-family:Geneva,Arial,sans-serif;font-size:14px;}
            a{color:#606060;text-decoration:none;border-bottom:1px dotted #606060;}
            a:hover{color:black;border-bottom:1px solid black;}
            dl{padding:20px;}
            dt{color:#724D27;margin-top:10px;text-transform:capitalize}
            dd{
                margin-left:30px;
                color:#231B03;
                border:1px solid #F2E6D9;
                -moz-border-radius: 5px;
                -webkit-border-radius: 5px;
                border-radius: 5px;
                padding:10px;
               }
            img.out{max-width: 400px;}
        </style>
    </head>
    <body>
        <div style="position:fixed;background-color: white;height: 35px;overflow:hidden;">
            <iframe src="<?php echo $this->url('chat/index/upload'); ?>" style="border:none;width:500px;">
              <p>Your browser does not support iframes.</p>
            </iframe>
        </div>
        <div id="availableUsers" style="position:fixed;top:0;right:20px;padding:5px;color:gray;">
            John, Jack, Peter, Raoul, Peter.
        </div>
        <div id="messagesWrapper" style="overflow:auto;padding-top:5px;padding-left:5px;padding-right:5px;">
            <dl id="messageList">
                <dt></dt>
                <dd style="border:none;"></dd>
            </dl>
        </div>
        <div id="messageForm" style="padding-left:5px;padding-right: 5px;">
            <form id="sendMessage" method="post" action="">
                <p style="padding-left:10px;">
                    <input type="hidden" id="lastMessage" value="<?php $previous = \chat\models\Post::getLastInsertedMessageId(); $previous = $previous - 15; if($previous<0){echo '0';}else{echo $previous;} ?>"/>
                    <input type="text" name="message" id="message" style="font-size:18px;"/>
                </p>
            </form>
        </div>
    </body>
</html>