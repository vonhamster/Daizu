<?php
namespace cms\controllers;
use \acl\models\User as User;
use \acl\models\Role as Role;
use \acl\models\Resource as Resource;
use \cms\models\Page as Page;
class Cli extends \shozu\Controller\CLI
{
    public function installAction()
    {
        echo "Testing if installed...\n";
        $admin = User::findOne('login = "admin"');
        if($admin)
        {
           throw new \Exception('already installed');
        }
        try
        {
            $password = substr(uniqid(), -5);
            echo "Create new admin user...\n";
            $admin = new User;
            $admin_role = Role::fetch('admin');
            $admin_role->giveResource('cms.create');
            $admin_role->giveResource('cms.read');
            $admin_role->giveResource('cms.update');
            $admin_role->giveResource('cms.delete');
            $admin_role->giveResource('cms.publish');
            $admin_role->giveResource('cms.manage_resources');
            $admin_role->giveResource('cms.manage_users');
            $admin_role->save();

            $admin->setLogin('admin');
            $admin->setPassword(sha1($password));
            $admin->giveRole('admin');
            $admin->save();

            $i = 0;

            echo "Create new root page...\n";
            $root = new Page;
            $root->setTitle('Homepage');
            $root->setBody('<p>This is your Homepage.</p>');
            $root->setUrl('/');
            $root->setPublished(true);
            $root->setSeo_title('welcome at home');
            $root->setSeo_description('This text is just a test for meta');
            $root->setSeo_keywords('cms, joomla, php, redbean, shozu');
            $root->setOrder_number(++$i);
            $root->save();

            $p2 = new Page;
            $p2->setTitle('sous-rubrique');
            $p2->setBody('<p>Level 2 page.</p>');
            $p2->setUrl('/hello/');
            $p2->setPublished(true);
            $p2->setOrder_number(++$i);
            $p2->saveAsChildOf($root);

            $p3 = new Page;
            $p3->setTitle('sous-sous-rubrique');
            $p3->setBody('<p>Level 3 page.</p>');
            $p3->setUrl('/hello/world/');
            $p3->setPublished(true);
            $p3->setOrder_number(++$i);
            $p3->saveAsChildOf($p2);

            echo "\n" . 'Sucessfully installed. Admin password: ' . $password . "\n";
        }
        catch(\Exception $e)
        {
            echo get_class($e) . ': ' . $e->getMessage() . "\n";
        }
    }

/*
    public function testAction()
    {
        $p1 = new Page;
        $p1->setTitle('accueil');
        $p1->setUrl('/');
        $p1->save();

        $p2 = new Page;
        $p2->setTitle('sous-rubrique');
        $p2->setUrl('/hello/');
        $p2->saveAsChildOf($p1);

        $p3 = new Page;
        $p3->setTitle('sous-sous-rubrique');
        $p3->setUrl('/hello/world/');
        $p3->saveAsChildOf($p2);

        $ancestors = $p3->getAncestors();

        foreach($ancestors as $ancestor)
        {
            echo "\n".$ancestor->getTitle();
        }
    }*/
}
