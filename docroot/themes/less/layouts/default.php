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
        <link rel="stylesheet/less" href="<?php echo \shozu\Shozu::getInstance()->base_url; ?>themes/less/main.less" type="text/css">
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>themes/less/less-1.0.35.min.js"></script>
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
