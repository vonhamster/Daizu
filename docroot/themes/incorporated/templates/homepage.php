
	<div id="content">

		<div class="post">
			<h1 class="title"><?php echo $this->escape($page->getTitle());?></h1>
			<p class="byline"><small><?php echo $this->T('Published by'); ?> <?php echo $this->escape($page->getAuthor());?></small></p>
			<div class="entry">
            <?php echo $page->getBody(); ?>
			</div>
		</div>
	</div>

	<!-- end #content -->
	<div id="sidebar" style="margin-bottom: 10px;">
		<div id="sidebar-bgtop"></div>
		<div id="sidebar-content">
			<ul>
				<li>
                    
					<h2>
                        <a href="<?php echo $this->url('cms/index/contentrss'); ?>"><img style="vertical-align:middle;" src="<?php echo \shozu\Shozu::getInstance()->base_url;?>themes/incorporated/images/rss.png" alt="rss feed"/></a>
                        <a href="<?php echo $this->url('cms/index/contentrss'); ?>"><?php echo $this->T('Latest changes'); ?></a>
                    </h2>
					<ul style="list-style-position: outside;margin-left:1em;">
                        <?php if($this->cacheBegin('lastpublications')){ ?>
                            <?php
                                $now = date('Y-m-d H:i:s');
                                $new_pages = \cms\models\Page::findLastPublications(10);
                                foreach($new_pages as $new_page)
                                {
                            ?>
                            <li><?php echo $this->escape($new_page->published_from->format('Y-m-d')); ?>: <a href="<?php echo $new_page->getFullUrl(); ?>"><?php echo $this->escape($new_page->getTitle()); ?></a></li>
                            <?php } ?>
                        <?php $this->cacheEnd(600);} ?>
					</ul>
                </li>
			</ul>
		</div>
		<div id="sidebar-bgbtm"></div>
	</div>
	<!-- end #sidebar -->
