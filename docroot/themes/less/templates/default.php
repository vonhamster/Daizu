<?php if($this->cacheBegin($page->mkViewCacheKey())){ ?>
<div class="breadcrumb">
/
<?php $ancestors = $page->getAncestors(); foreach ($ancestors as $ancestor){?>
<a href="<?php echo $this->escape($ancestor->getFullUrl()); ?>">
<?php echo $this->escape($this->ucfirst($ancestor->getTitle())); ?>
</a> /
<?php }?>
<a href="<?php echo $this->escape($page->getFullUrl()); ?>">
<?php echo $this->escape($this->ucfirst($page->getTitle())); ?>
</a> /
</div>
<hr/>
<h1><?php echo $this->escape($this->ucfirst($page->getTitle())); ?></h1>
<p style="font-size:0.7em;"><em><?php echo $this->T('Last update:'); ?> <?php echo $page->getModified_at()->format('Y-m-d h:i:s'); ?></em></p>
<?php echo $page->getBody(); ?>
<?php $children = $page->getPublishedChildren(); if(count($children)){?>
<hr/>
<ul>
    <?php foreach($children as $child){ ?>
    <li>
        <a href="<?php echo $this->escape($child->getFullUrl()); ?>">
        <?php echo $this->escape($this->ucfirst($child->getTitle())); ?>
        </a>
    </li>
    <?php } ?>
</ul>
<?php } ?>

<?php
    $left_sibling = $page->getPublishedLeftSibling();
    $right_sibling = $page->getPublishedRightSibling();
    if($left_sibling || $right_sibling){ ?>
    <hr/>
    <div style="float:left;font-size: 0.8em;">
    <?php if($left_sibling){ ?>
        <a href="<?php echo $left_sibling->getFullUrl(); ?>">&lt;&lt;&nbsp;<?php echo $this->escape($left_sibling->title); ?></a>
    <?php } ?>
    </div>
    <div style="float:right;;font-size: 0.8em;">
    <?php if($right_sibling){ ?>
        <a href="<?php echo $right_sibling->getFullUrl(); ?>"><?php echo $this->escape($right_sibling->title); ?>&nbsp;&gt;&gt;</a>
    <?php } ?>
    </div>
    <div style="clear: both;height: 5px;">&nbsp;</div>
<?php } ?>

<?php if($page->getAllow_comments()){ ?>
<?php $comments = $page->getPublishedComments(); if(count($comments)){ ?>
<hr/>
<h3><?php echo $this->T('Comments'); ?></h3>
    <?php foreach($comments as $comment){ if($comment->isPublished()){ ?>
        <?php if($comment->getUrl()){ ?>
        <h4>
            <a href="<?php echo $this->escape($comment->getUrl()); ?>" target="_blank">
            <?php echo $this->escape($comment->getName()); ?>
            </a>
        </h4>
        <?php }else{?>
        <h4><?php echo $this->escape($comment->getName()); ?></h4>
        <?php } ?>
        <pre class="comment"><?php echo $this->escape(wordwrap($comment->getContent())); ?></pre>
    <?php }} ?>
<?php } ?>
<hr/>
<form method="post" action="<?php echo $this->url('cms/index/postcomment'); ?>" id="commentform">
    <fieldset>
        <legend><?php echo $this->T('Comment this'); ?></legend>
        <p>
            <label for="comment_name"><?php echo $this->T('Name'); ?>*</label><br/>
            <input type="text" name="name" id="comment_name"/>
        </p>
        <p>
            <label for="comment_email"><?php echo $this->T('Email'); ?>*</label><br/>
            <input type="text" name="email" id="comment_email"/>
        </p>
        <p>
            <label for="comment_website"><?php echo $this->T('Website'); ?></label><br/>
            <input type="text" name="website" id="comment_website"/>
        </p>
        <p>
            <label for="comment_comment"><?php echo $this->T('Comment'); ?>*</label><br/>
            <textarea rows="5" cols="50" name="comment" id="comment_comment"></textarea>
        </p>
        <p><input type="submit" value="<?php echo $this->T('Save') ?>"/></p>
    </fieldset>
</form>

<script type="text/javascript">
$(document).ready(function(){
    $('#commentform').submit(function(){
        $.post('<?php echo $this->url('cms/index/postcomment'); ?>', {
            page_id: <?php echo $page->getId(); ?>,
            name:$('#comment_name').val(),
            email:$('#comment_email').val(),
            website:$('#comment_website').val(),
            comment:$('#comment_comment').val()
        }, function(data){if(data.response == 'ok'){
                $('#comment_comment').val('');
                alert("<?php echo $this->T('Your comment has been saved and is waiting for approval.'); ?>");
        }else{
            alert(data.message);
        }}, 'json');
        return false;
    });
});
</script>
<?php } ?>
<?php $this->cacheEnd(600);} ?>