<?php
header('content-type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">
  <channel>
      <title><![CDATA[<?php echo $this->escape(DAIZU_SITE_NAME); ?> | <?php echo(isset($feed_name)? $feed_name : 'Main Feed') ?>]]></title>
    <link><?php echo $this->url('cms/index/contentrss'); ?></link>
    <description><![CDATA[<?php echo $this->escape(DAIZU_SITE_BASELINE); ?>]]></description>
    <pubDate><?php echo date('D, d M Y H:i:s O', time()); ?></pubDate>
    <generator>Daizu CMS</generator>
    <language>en-EN</language>
    <docs>http://blogs.law.harvard.edu/tech/rss</docs>
    <?php foreach($pages as $page){ ?>
    <item>
              <title><![CDATA[<?php echo $this->escape($page->getTitle()); ?>]]></title>
      <link><?php echo $page->getFullUrl(); ?></link>
      <guid><?php echo $page->getFullUrl(); ?></guid>
            <description><![CDATA[<?php echo($page->getHeading() ?: $page->getBody());?>]]></description>

      <pubDate><?php echo $page->getPublished_from()->format('D, d M Y H:i:s O'); ?></pubDate>
    </item>
    <?php } ?>
  </channel>
</rss>