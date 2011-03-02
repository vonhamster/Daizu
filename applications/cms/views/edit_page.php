<div style="padding:1em;">
    <?php if(count($page->getVersions()) > 1){ ?>
    <fieldset>
        <legend><?php echo $this->T('Versions'); ?></legend>
        <input type="button" value="<?php echo $this->T('Toggle'); ?>" id="toggle_version_list"/>
        <ul id="version_list" style="display:none;">
            <?php foreach($page->getVersions() as $idx => $version){ ?>
            <li>
                <?php echo $this->escape($version['modified_at']); ?> <a href="<?php echo $this->url('cms/admin/loadpageversion', array($page->getId(), $idx)); ?>" class="loadversion"><?php echo $this->T('Load'); ?></a>
            </li>
            <?php } ?>
        </ul>
    </fieldset>
    <?php }?>
    <form id="pageform" action="<?php echo $this->url('cms/admin/savepage'); ?>" style="width:100%;" method="post">
    <fieldset>
        <legend><?php echo $this->T('Edit page'); ?></legend>        
            <table>
                <tr>
                    <td class="label">
                        <label for="page_url"><?php echo $this->T('URL'); ?></label>
                    </td>
                    <td class="input">
                        <input type="hidden" name="id" value="<?php echo $page->getId();?>"/>
                        <input type="text" name="url" id="page_url" size="50" maxlength="254" value="<?php echo $this->escape($page->getUrl()); ?>"/>
                        <a href="<?php echo $page->getFullUrl(); ?>" target="_blank" id="link_to_page"><?php echo $this->T('Display'); ?></a>
                    </td>
                    <td class="error" id="page_url_error"></td>
                </tr>
                <tr>
                    <td class="label">
                        <label for="page_heading"><?php echo $this->T('Heading'); ?></label>
                    </td>
                    <td class="input"><textarea rows="5" cols="40" name="heading" id="page_heading"><?php echo $this->escape($page->getHeading()); ?></textarea></td>
                    <td class="error" id="page_heading_error"></td>
                </tr>
                <tr>
                    <td class="label">
                        <label for="page_body"><?php echo $this->T('Body'); ?></label>
                    </td>
                    <td class="input"><textarea class="richeditor" rows="10" cols="30" name="page_body" id="page_body"><?php echo $this->escape($page->getBody()); ?></textarea></td>
                    <td class="error" id="page_body_error"></td>
                </tr>
                <tr>
                    <td class="label">
                        <label for="page_published_from"><?php echo $this->T('Published'); ?></label>
                    </td>
                    <td class="input">
                        <input style="display:block;float:left;" type="checkbox" name="published" value="" id="page_published" <?php if($page->getPublished()){echo 'checked="checked"';} ?>/>
                        <label style="display:block;margin: 5px;float:left;" for="page_published_to"><?php echo $this->T('Published from date'); ?></label>
                        <input type="text" name="published_from" id="page_published_from" class="datetime" value="<?php $from = $page->getPublished_from(); if($from){echo $this->escape($from->format('Y-m-d H:i:s'));} ?>"/>
                        <label style="display:block;margin: 5px;float:left;" for="page_published_to"><?php echo $this->T('Published to date'); ?></label>
                        <input type="text" name="published_to"  id="page_published_to" class="datetime" value="<?php $to = $page->getPublished_to(); if($to){echo $this->escape($to->format('Y-m-d H:i:s'));} ?>"/>
                        <label style="display:block;margin: 5px;float:left;" for="sync_publishing"><?php echo $this->T('Apply to descendants'); ?></label>
                        <input type="checkbox" name="sync_publishing" id="sync_publishing"/>
                    </td>
                    <td class="error" id="page_published_from_error"></td>
                </tr>
                <tr>
                    <td class="label">
                        <label for="page_template"><?php echo $this->T('Template'); ?></label>

                    </td>
                    <td class="input"><input type="text" name="template" id="page_template" value="<?php echo $this->escape($page->getTemplate()); ?>"/></td>
                    <td class="error" id="page_template_error"></td>
                </tr>
                <tr>
                    <td class="label">
                        <label for="page_layout"><?php echo $this->T('Layout'); ?></label>
                    </td>

                    <td class="input"><input type="text" name="layout" id="page_layout" value="<?php echo $this->escape($page->getLayout()); ?>"/></td>
                    <td class="error" id="page_layout_error"></td>
                </tr>
                <tr>
                    <td class="label">
                        <label for="page_author"><?php echo $this->T('Author'); ?></label>
                    </td>

                    <td class="input"><input type="text" name="author" id="page_author" value="<?php echo $this->escape($page->getAuthor()); ?>"/></td>
                    <td class="error" id="page_author_error"></td>
                </tr>
            </table>
        </fieldset>
        <fieldset>
            <legend><?php echo $this->T('SEO'); ?></legend>
            <input type="button" value="<?php echo $this->T('Toggle'); ?>" id="toggle_seo_fields"/>
            <table id="seo_fields" style="display: none;">
                <tr>
                    <td class="label">
                        <label for="page_seo_title"><?php echo $this->T('SEO Title');?></label>
                    </td>
                    <td class="input">
                        <input type="text" size="50" name="seo_title" id="page_seo_title" value="<?php echo $this->escape($page->getSeo_title()); ?>"/>
                    </td>
                    <td class="error" id="page_seo_title_error"></td>
                </tr>
                <tr>
                    <td class="label">
                        <label for="page_seo_keywords"><?php echo $this->T('SEO Keywords');?></label>
                    </td>
                    <td class="input">
                        <input type="text" size="50" name="seo_keywords" id="page_seo_keywords" value="<?php echo $this->escape($page->getSeo_keywords()); ?>"/>
                    </td>
                    <td class="error" id="page_seo_keywords_error"></td>
                </tr>
                <tr>
                    <td class="label">
                        <label for="page_seo_description"><?php echo $this->T('SEO Description');?></label>
                    </td>
                    <td class="input">
                        <textarea rows="5" cols="40" name="seo_description" id="page_seo_description"><?php echo $this->escape($page->getSeo_description()); ?></textarea>
                    </td>
                    <td class="error" id="page_seo_description_error"></td>
                </tr>
            </table>
        </fieldset>
        <fieldset>
            <legend><?php echo $this->T('Indexing'); ?></legend>
            <input type="button" value="<?php echo $this->T('Toggle'); ?>" id="toggle_indexing_fields"/>
            <table id="indexing_fields" style="display: none;">
                <tr>
                    <td class="label">
                        <label for="page_indexing_analyzer"><?php echo $this->T('Analyzer');?></label>
                    </td>
                    <td class="input">
                        <input type="text" size="50" name="indexing_analyzer" id="page_indexing_analyzer" value="<?php echo $this->escape($page->getAnalyzer()); ?>"/>
                    </td>
                    <td class="error" id="page_indexing_analyzer_error"></td>
                </tr>
            </table>
        </fieldset>
        <fieldset>
            <legend><?php echo $this->T('Save'); ?></legend>
            <table>
                <tr>
                    <td class="label">
                        <label for="page_allow_comments"><?php echo $this->T('Allow comments');?></label>
                    </td>
                    <td class="input">
                        <input type="checkbox" name="allow_comments" id="page_allow_comments" <?php if($page->getAllow_comments()){echo 'checked="checked"';} ?>/>
                    </td>
                    <td class="error" id="page_allow_comments_error"></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <input type="submit" value="<?php echo $this->T('Save the page'); ?>" />
                    </td>
                    <td></td>
                </tr>
            </table>
        </fieldset>
    </form>
    <?php $comments = $page->getComments(); if(count($comments)){ ?>
    <fieldset>
        <legend><?php echo $this->T('Comments'); ?></legend>
        <?php foreach($comments as $comment){ ?>
            <h4>
                <a href="mailto:<?php echo $this->escape($comment->email); ?>"><?php echo $this->escape($comment->getName()); ?></a>
                <?php if($comment->getUrl()){ ?>
                [<a href="<?php echo $this->escape($comment->getUrl()); ?>" title="<?php echo $this->escape($comment->getUrl()); ?>" target="_blank">www</a>]
                <?php } ?>
                <?php echo $this->escape($comment->getCreated_at()->format('Y-m-d H:i:s')); ?>
            </h4>
            <pre class="comment"><?php echo $this->escape(wordwrap($comment->getContent())); ?></pre>
            <label for="comment_published_<?php echo $comment->getId(); ?>">Publié</label>
            <input class="comment_published_state" type="checkbox" <?php if($comment->isPublished()){echo 'checked="checked"';} ?> id="comment_published_<?php echo $comment->getId(); ?>"/>
            <hr/>
        <?php } ?>
    </fieldset>
    <?php } ?>
</div>