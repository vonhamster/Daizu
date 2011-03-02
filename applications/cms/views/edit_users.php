
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
});
</script>