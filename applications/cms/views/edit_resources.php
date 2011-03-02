<?php $s = \shozu\Shozu::getInstance(); ?>
<div id="elfinderspace" ></div>
<script type="text/javascript">
$(document).ready(function(){
    $("#ajaxStatus").fadeOut('fast');
    $("#ajaxStatus").ajaxStart(function(){
        $(this).show();
        $(document).css('cursor','wait');
    });
    $("#ajaxStatus").ajaxStop(function(){
        $(this).fadeOut('fast');
        $(document).css('cursor','auto');
    });


     $('#elfinderspace').elfinder({
       url : '<?php echo $this->url('cms/admin/elfinderconnector'); ?>',
       lang : '<?php echo \shozu\Shozu::getInstance()->lang; ?>',
       height: $(window).height() - 64,
       width:$(window).width() -2
     });

});
</script>
