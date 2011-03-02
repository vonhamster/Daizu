<form action="<?php echo $this->url('acl/index/loginform'); ?>" method="post">
<fieldset>
    <legend>Authenticate</legend>
    <label>Login</label>
    <input type="text" name="login" id="login"/>
    <label>password</label>
    <input type="password" name="password"/>
    <input type="submit"/>
</fieldset>
</form>
