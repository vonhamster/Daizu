<?php
namespace acl\controllers;
use \acl\models\User as User;
use \acl\models\Role as Role;
use \acl\models\Resource as Resource;
class Cli extends \shozu\Controller\CLI
{
    public function indexAction()
    {
        echo "Usage:\n" . '// create new user identified by email with password
php boot.php acl/cli/adduser/useremail@domain.com/userpassword
// change user password
php boot.php acl/cli/setpassword/useremail@domain.com/userpassword
//give admin, developer and customer roles to user (create those if needed)
php boot.php acl/cli/addroles/useremail@domain.com/admin,developer,customer
// remove roles from user
php boot.php acl/cli/removeroles/useremail@domain.com/admin,developer
// list all roles
php boot.php acl/cli/listroles
// list all roles for given user
php boot.php acl/cli/listroles/useremail@domain.com
// list all users
php boot.php acl/cli/listusers
// list all users matching the pattern
php boot.php acl/cli/listusers/%john%
// give admin, backup, copy, whatever resources to admin role
php boot.php acl/cli/addresources/admin/admin,backup,copy,whatever
// remove resources
php boot.php acl/cli/removeresources/admin/backup
// list all resources
php boot.php acl/cli/listresources
// list all resources associated with given role
php boot.php acl/cli/listresources/admin' . "\n";
    }
    /**
     * Create new user with given email, return generated password if
     * no password given.
     *
     * @param string $user_email
     * @param string $password
     */
    public function adduserAction($user_email, $password = null)
    {
        $user = User::findOne('login = ?', array($user_email));
        if(!$user)
        {
            if(empty($password))
            {
                $password = strtolower(substr(uniqid(), -6));
            }
            $user = new User;
            $user->login = $user_email;
            $user->password = sha1($password);
            $user->save();
            echo $mdp . "\n";
        }
        else
        {
            echo "User already exists.\n";
        }
    }


    public function setpasswordAction($user_email, $password)
    {
        $user = User::findOne('login = ?', array($user_email));
        if(!$user)
        {
            echo "No such user.\n";
            return;
        }
        $user->password = sha1(trim($password));
        $user->save();
        echo "Ok.\n";
    }

    /**
     * Give roles to user
     *
     * @param string $user_email
     * @param string $roles comma-separated role labels
     */
    public function addrolesAction($user_email, $roles)
    {
        $user = User::findOne('login = ?', array($user_email));
        if(!$user)
        {
            die("No such user.\n");
        }
        $roles = explode(',', $roles);
        foreach($roles as $role_label)
        {
            $user->giveRole($role_label);
        }
        $user->save();
        echo "Ok.\n";
    }

    /**
     * Remove roles from user
     *
     * @param string $user_email
     * @param string $roles comma-separated role labels
     */
    public function removerolesAction($user_email, $roles)
    {
        $user = User::findOne('login = ?', array($user_email));
        if(!$user)
        {
            echo "No such user.\n";
            return;
        }
        $roles = explode(',', $roles);
        foreach($roles as $role_label)
        {
            $user->removeRole($role_label);
        }
        $user->save();
        echo "Ok.\n";
    }

    /**
     * List all roles or roles from user if given
     *
     * @param string $user_email
     */
    public function listrolesAction($user_email = '')
    {
        if(!empty($user_email))
        {
            $user = User::findOne('login = ?', array($user_email));
            if(!$user)
            {
                echo "No such user.\n";
                return;
            }
            $roles = $user->getRoles();
        }
        else
        {
            $roles = Role::find();
        }
        foreach($roles as $role)
        {
            echo $role->label."\n";
        }
    }

    /**
     * List all user matching pattern
     *
     * @param string $pattern SQL pattern: %john%
     */
    public function listusersAction($pattern = '')
    {
        if(!empty($pattern))
        {
            $users = User::find('login like ?', array($pattern));
        }
        else
        {
            $users = User::find();
        }
        foreach($users as $user)
        {
            echo $user->login."\n";
        }
    }

    /**
     * Add resources to given role
     *
     * @param string $role
     * @param string $resources
     */
    public function addresourcesAction($role, $resources)
    {
        $role = Role::findOne('label like ?', array($role));
        if(!$role)
        {
            echo "No such role.\n";
            return;
        }
        $resources = explode(',', $resources);
        foreach($resources as $resource_label)
        {
            $role->giveResource($resource_label);
        }
        $role->save();
        echo "Ok.\n";
    }

    /**
     * Remove resources to given role
     *
     * @param string $role
     * @param string $resources
     */
    public function removeresourcesAction($role, $resources)
    {
        $role = Role::findOne('label like ?', array($role));
        if(!$role)
        {
            echo "No such role.\n";
            return;
        }
        $resources = explode(',', $resources);
        foreach($resources as $resource_label)
        {
            $role->removeResource($resource_label);
        }
        $role->save();
        echo "Ok.\n";
    }

    /**
     * List all resources or resources from role if given
     *
     * @param string $user_email
     */
    public function listresourcesAction($role = '')
    {
        if(!empty($role))
        {
            $role = Role::findOne('label = ?', array($role));
            if(!$role)
            {
                echo "No such role.\n";
                return;
            }
            $resources = $role->getResources();
        }
        else
        {
            $resources = Resource::find();
        }
        foreach($resources as $resource)
        {
            echo $resource->label."\n";
        }
    }
}
