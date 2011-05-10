<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>MyShare</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style type="text/css">
            body{
                font-family: sans-serif;
            }
        </style>
    </head>
    <body>
        <form method="post" enctype="multipart/form-data" action="<?php echo $this->url('chat/index/upload'); ?>">
            <input type="file" name="up"/>
            <input type="submit" value="<?php echo $this->T('Share'); ?>"/>
            &nbsp;
            <?php if(isset($error_message)){ ?>
                <?php echo $this->T('Erreur'); ?>: <strong style="color:red;"><?php echo $this->escape($error_message); ?></strong>
            <?php } ?>
        </form>
    </body>
</html>