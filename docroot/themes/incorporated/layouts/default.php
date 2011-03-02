<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title><?php if(isset($title)){echo $this->escape($title);}else{echo 'untitled';} ?> | <?php echo $this->escape(DAIZU_SITE_NAME); ?></title>
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
    <link rel="stylesheet" type="text/css" href="<?php echo \shozu\Shozu::getInstance()->base_url; ?>themes/incorporated/style.css" media="screen" >
    <link rel="stylesheet" type="text/css" href="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/prettify.css" media="screen" >
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.4.2.min.js"></script>
    <script type="text/javascript" src="<?php echo \shozu\Shozu::getInstance()->base_url; ?>static/prettify.js"></script>
</head>
<body>
<div id="header">
	<div id="logo">
        <h1><a href="<?php echo \shozu\Shozu::getInstance()->base_url;?>"><?php echo $this->escape(\DAIZU_SITE_NAME); ?></a></h1>
		<p><?php echo $this->escape(\DAIZU_SITE_BASELINE); ?></p>
	</div>
	<!-- end #logo -->

    <?php if($this->cacheBegin('topmenu')){ ?>
    <?php
        $first_level_pages = \cms\models\Page::fetchRoot()->getPublishedChildren();
    ?>
	<div id="menu">
		<ul>
            <?php foreach($first_level_pages as $first_level_page){ ?>
            <li><a href="<?php echo $first_level_page->getFullUrl(); ?>"><?php echo $this->escape($first_level_page->getTitle()); ?></a></li>
            <?php } ?>
		</ul>
	</div>
    <?php $this->cacheEnd(600);} ?>

	<!-- end #menu -->
</div>
<!-- end #header -->
<div id="page">

        <?php echo $content_for_layout; ?>

	<!-- end #content -->
</div>
<!-- end #page -->
<div id="footer">
    <p>
        &copy; 2010. All Rights Reserved. Powered by <a href="http://code.google.com/p/daizu/" target="_blank">Daizu</a> CMS.
        Design by <a href="http://www.freecsstemplates.org/" target="_blank">Free CSS Templates</a>.
        
    </p>
</div>
<!-- end #footer -->
<script type="text/javascript">
prettyPrint();
</script>

<?php if(\shozu\Shozu::getInstance()->benchmark){ ?>
<div style="margin-top:50px;padding:20px;background-color:black;color:#cecece;">
<h3>Benchmark ! :-)</h3>
<?php echo \shozu\Benchmark::htmlReport(); ?>
</div>
<?php } ?>
</body>
</html>