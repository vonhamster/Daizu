<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Daizu Admin</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style type="text/css">
            body{
                font-family: "Luxi sans","Lucida Grande",Lucida,"Lucida Sans Unicode",sans-serif;
                font-size: 0.8125em;
                padding:0;
                margin:0;
            }
            h1{
                font-family: Georgia,Times,serif;
            }
            p{
                font-size: 0.8125em;
            }
            hr{
                border: 0 none;
                height: 1px;
                background-color: #cecece;
            }
            td.label{
                text-align: right;
                vertical-align: top;
                width:15%;
            }
            td.error{
                width:10%;
            }
            fieldset{
                border: 1px solid #cecece;
            }
            a.dp-choose-date{
                margin-left: 10px;
            }
            textarea{
                width:700px;
            }
        </style>
        <link rel="stylesheet" type="text/css" href="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/markitup/skins/simple/style.css">
        <link rel="stylesheet" type="text/css" href="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/markitup/sets/html/style.css">
        <link rel="stylesheet" type="text/css" href="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/datePicker.css">
        <?php if(isset($css_includes)){foreach($css_includes as $css_include){ ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->escape($css_include); ?>">
        <?php }} ?>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/jquery.hotkeys.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/jquery.cookie.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/jquery.jstree.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/jquery.taconite.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/markitup/jquery.markitup.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/markitup/sets/html/set.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/date.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/jquery.datePicker.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/jquery/jquery.form.js"></script>
        <?php if(isset($js_includes)){ foreach($js_includes as $js_include){ ?>
        <script type="text/javascript" src="<?php echo $this->escape($js_include); ?>"></script>
        <?php }} ?>
    </head>
    <body>
        <div id="ajaxStatus" style="position: fixed; 
             border:1px solid #cecece;
             padding:10px;
             bottom:10px;
             right:20px;
             background-color: #FFFFEE;
             -webkit-border-radius: 10px;
             -moz-border-radius: 10px;
             border-radius: 10px;">Chargement...</div>
        <div style="position: fixed;
             top:0px;
             right:30px;
             width:300px;
             height:20px;
             overflow: hidden;
             border-bottom: 1px solid #cecece;
             border-left: 1px solid #cecece;
             border-right: 1px solid #cecece;
             background-color: #FFFFEE;
            -webkit-border-bottom-left-radius: 10px;
            -moz-border-radius-bottomleft: 10px;
            border-bottom-left-radius: 10px;
            -webkit-border-bottom-right-radius: 10px;
            -moz-border-radius-bottomright: 10px;
            border-bottom-right-radius: 10px;
             text-align: center;
             padding:5px;
             ">
            <div style="font-size: 12px;">
                <a href="<?php echo $this->escape($this->url('cms/admin/index')); ?>"><?php echo $this->T('Edit site'); ?></a> -
                <a href="<?php echo $this->escape($this->url('cms/admin/resources')); ?>"><?php echo $this->T('Manage resources'); ?></a><!--
                <a href="<?php echo $this->escape($this->url('cms/admin/users')); ?>"><?php echo $this->T('Manage users'); ?></a>-->
            </div>
        </div>
        <?php echo $content_for_layout; ?>
    </body>
</html>
