<?php
namespace acl\controllers;
use \acl\lib\CookieAuth as Auth;
class Index extends \shozu\Controller
{
    public function loginformAction()
    {
        $this->setLayout(__DIR__.'/../views/layout.php');
        if($this->getRequestMethod() == 'post')
        {
            $res = Auth::login($this->getParam('login'), $this->getParam('password'));
            if($res)
            {
                $this->display(__DIR__.'/../views/welcome.php',
                                array(
                                    'redirect_url' => ACL_SUCCESSFUL_COOKIEAUTH_REDIRECT,
                                    'user' => Auth::getUser()
                                )
                );
                return;
            }
        }
        $this->display(__DIR__.'/../views/loginform.php');
    }
}
