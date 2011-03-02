<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title><?php if(isset($title)){echo $this->escape($title);}else{echo 'untitled';} ?> | <?php echo $this->escape(\DAIZU_SITE_NAME); ?></title>
        <link rel="alternate" type="application/rss+xml" title="<?php echo $this->escape(DAIZU_SITE_NAME . ' | ' . DAIZU_SITE_BASELINE); ?>" href="<?php echo $this->url('cms/index/contentrss'); ?>">
        <?php if(!empty($description)){ ?>
        <meta name="description" content="<?php echo $this->escape($description); ?>">
        <?php } ?>
        <?php if(!empty($keywords)){ ?>
        <meta name="keywords" content="<?php echo $this->escape($keywords); ?>">
        <?php } ?>
        <?php if(!empty($author)){ ?>
        <meta name="author" content="<?php echo $this->escape($author); ?>">
        <?php } ?>
        <meta name="generator" content="Daizu CMS">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style type="text/css">
            body{
                color:#222222;
                font-family: "Luxi sans","Lucida Grande",Lucida,"Lucida Sans Unicode",sans-serif;
                /*font-size: 0.8125em;*/
                width:800px;
                margin-left: auto;
                margin-right: auto;
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
            }
            div#head{
                font-family: Georgia,Times,serif;
                font-weight:bold;
                font-size:3em;
            }
            /* Pretty printing styles. Used with prettify.js. */
            .str { color: #080; }
            .kwd { color: #008; }
            .com { color: #800; }
            .typ { color: #606; }
            .lit { color: #066; }
            .pun { color: #660; }
            .pln { color: #000; }
            .tag { color: #008; }
            .atn { color: #606; }
            .atv { color: #080; }
            .dec { color: #606; }
            pre.prettyprint { padding: 10px; border: 1px solid #cecece; }

            @media print {
                .str { color: #060; }
                .kwd { color: #006; font-weight: bold; }
                .com { color: #600; font-style: italic; }
                .typ { color: #404; font-weight: bold; }
                .lit { color: #044; }
                .pun { color: #440; }
                .pln { color: #000; }
                .tag { color: #006; font-weight: bold; }
                .atn { color: #404; }
                .atv { color: #060; }
            }
            pre.prettyprint{
                overflow: auto;
            }
            a img{
                border:none;
            }
            fieldset{border: 1px solid #cecece;}
            blockquote{
                font-style: italic;
                color: gray;
            }


            blockquote:before { content: open-quote; font-weight: bold; }
            blockquote:after { content: close-quote; font-weight: bold; }

            a {
                border-bottom:1px dotted #394147;
                color:#394147;
                text-decoration:none;
            }
            a:hover{
                color: #000;
                border-bottom: 1px solid;
            }


        </style>
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/prettify.js"></script>
    </head>
    <body>
        <div id="head"><?php echo $this->escape(\DAIZU_SITE_NAME); ?></div>
        <?php echo $content_for_layout; ?>
        <hr/>
        <p style="text-align: center">Poweuraide baïlle <a href="http://code.google.com/p/daizu/" target="_blank">Daizu</a>.</p>
        <script type="text/javascript">
        prettyPrint();
        </script>
    </body>
</html>
