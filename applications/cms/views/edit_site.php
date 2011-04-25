<?php $s = \shozu\Shozu::getInstance(); ?>
<table style="width:100%;margin:0;padding:0;" cellspacing="0" cellpadding="0">
    <tr>
        <td style="width:300px;overflow:hidden;">
            <div style="width:300px;overflow:auto;border-right:1px solid #cecece;" id="pagetree"></div>
        </td>
        <td>
            <div class="mep" id="editPage" style="overflow:auto;">
                <p style="text-align: center;"><?php echo $this->T('Select a page in the left tree') ?>.</p>
            </div>
        </td>
    </tr>
</table>
<script type="text/javascript">
$(document).ready(function(){
    resizeLayout = function(){
        $('.mep').height($(window).height());
        $('#pagetree').height($(window).height());
    };
    resizeLayout();
    $(window).resize(resizeLayout);
    $("#pagetree").jstree({
        "core": {
            "animation" : 0
        },
		"json_data" : {
			"ajax" : {
                "type" : "POST",
				"url" : "<?php echo $this->url('cms/admin/jsontree');?>",
				"data" : function (n) {
					return { id : n.attr ? n.attr("id") : 0 };
				}
			}
		},
        "ui": {
            "select_limit": 1            
        },
        "dnd": {
            "copy_modifier":false,
            "drop_target": false
        },
        "contextmenu":{
            "items":{
                "create" : {
					"separator_before"	: false,
					"separator_after"	: true,
					"label"				: "<?php echo $this->T('Create new page');?>",
					"action"			: function (obj) { this.create(obj); }
				},
				"rename" : {
					"separator_before"	: false,
					"separator_after"	: false,
					"label"				: "<?php echo $this->T('Rename page');?>",
					"action"			: function (obj) { this.rename(obj); }
				},
				"remove" : {
					"separator_before"	: false,
					"icon"				: false,
					"separator_after"	: false,
					"label"				: "<?php echo $this->T('Delete page');?>",
					"action"			: function (obj) { this.remove(obj); }
				},
                "ccp": false
            }
        },
		"plugins" : ["themes", "json_data", "ui", "contextmenu", "dnd", "crrm"]
	}).bind("move_node.jstree", function(e, data){
        data.rslt.o.each(function(i){
            var ordered_children = '';
            $(this).parent().children().each(function(){
                ordered_children += $(this).attr("id").replace("node_", "") + ',';
            });
            $.ajax({
                "async": true,
                "type": "POST",
                "url":"<?php echo $this->url('cms/admin/movepage'); ?>",
                "data":{
                    "children": ordered_children,
                    "parent": data.rslt.np.attr("id").replace("node_","")
                },
                "success": function(r){
                    if(!r.status){
                        $.jstree.rollback(data.rlbk);
                    }
                },
                "error": function(r, status, e){
                    $.jstree.rollback(data.rlbk);
                }
            });
        });
    }).bind("select_node.jstree", function(e, data){
        //confirm('Save ?');
        data.rslt.obj.each(function(i){
            $.post("<?php echo $this->url('cms/admin/editpage'); ?>",{"id": $(this).attr("id").replace("node_", "")});
        });
    }).bind("rename_node.jstree", function(e, data){
        $.ajax({
            "async": true,
            "type": "POST",
            "url":"<?php echo $this->url('cms/admin/renamepage'); ?>",
            "data":{
                "node_id": data.rslt.obj.attr("id").replace("node_", ""),
                "name": data.rslt.name.replace(String.fromCharCode(160)," ")
            },
            "success": function(r){
                if(!r.status){
                    $.jstree.rollback(data.rlbk);
                }
            },
            "error": function(r, status, e){
                $.jstree.rollback(data.rlbk);
            }
        });
    }).bind("create_node.jstree", function(e, data){
        $.ajax({
            "async": false,
            "type": "POST",
            "url":"<?php echo $this->url('cms/admin/newpage'); ?>",
            "data":{
                "parent": data.rslt.parent.attr("id").replace("node_","")
            },
            "success": function(r){
                if(!r.status){
                    $.jstree.rollback(data.rlbk);
                }
                else
                {
                    data.rslt.obj.attr("id", "node_" + r.id);
                }
            },
            "error": function(r, status, e){
                $.jstree.rollback(data.rlbk);
            }
        });
    }).bind("delete_node.jstree", function(e, data){
        if(confirm("<?php echo $this->T('Sure ?'); ?>"))
        {
            $.ajax({
                "async": false,
                "type": "POST",
                "url":"<?php echo $this->url('cms/admin/deletepage'); ?>",
                "data":{
                    "id": data.rslt.obj.attr("id").replace("node_","")
                },
                "success": function(r){
                    if(!r.status){
                        $.jstree.rollback(data.rlbk);
                        if(r.err)
                        {
                            if(r.err == 'protected page')
                            {
                                alert("<?php echo $this->T('Protected page.'); ?>");
                            }
                        }
                    }
                },
                "error": function(r, status, e){
                    $.jstree.rollback(data.rlbk);
                }
            });
        }
        else
        {
            $.jstree.rollback(data.rlbk);
        }
    });


    $("#ajaxStatus").ajaxStart(function(){
        $(this).show();
        $(document).css('cursor','wait');
    });
    $("#ajaxStatus").ajaxStop(function(){
        $(this).fadeOut('fast');
        $(document).css('cursor','auto');
    });
    $("a#clearcache").click(function(){
        $.post("<?php echo $this->url('cms/admin/clearcache'); ?>", function(data){
            if(data.status == 'ok')
            {
                alert("<?php echo $this->T('Cache cleared.'); ?>")    
            }
            else
            {
                alert("error.");
            }
        });
        return false;
    });
    Date.format = 'yyyy-mm-dd 00:00:00';
    $.dpText = {
        TEXT_PREV_YEAR		:	'<?php echo $this->T('Previous year'); ?>',
        TEXT_PREV_MONTH		:	'<?php echo $this->T('Previous month'); ?>',
        TEXT_NEXT_YEAR		:	'<?php echo $this->T('Next year'); ?>',
        TEXT_NEXT_MONTH		:	'<?php echo $this->T('Next month'); ?>',
        TEXT_CLOSE			:	'<?php echo $this->T('Close'); ?>',
        TEXT_CHOOSE_DATE	:	'<?php echo $this->T('Choose date'); ?>'
    };
    Date.dayNames = ['<?php echo $this->T('Sunday'); ?>', '<?php echo $this->T('Monday'); ?>', '<?php echo $this->T('Tuesday'); ?>', '<?php echo $this->T('Wednesday'); ?>', '<?php echo $this->T('Thursday'); ?>', '<?php echo $this->T('Friday'); ?>', '<?php echo $this->T('Saturday'); ?>'];
    Date.abbrDayNames = ['<?php echo $this->T('Su'); ?>', '<?php echo $this->T('Mo'); ?>', '<?php echo $this->T('Tu'); ?>', '<?php echo $this->T('We'); ?>', '<?php echo $this->T('Th'); ?>', '<?php echo $this->T('Fr'); ?>', '<?php echo $this->T('Sa'); ?>'];
    Date.monthNames = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    Date.abbrMonthNames = ['Jan', 'Feb', 'Mrz', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];


});
</script>