<?php
namespace chat\controllers;
use \acl\lib\BasicAuth as Auth;
use \acl\models\User as User;
use \chat\models\Post as Post;
use \chat\models\Url as Url;
use \chat\models\Upload as Upload;
class Exception extends \Exception{}

class Index extends \shozu\Controller
{
    const CACHE_USERS_LOGGED_IN = 'chat_users_logged_in';
    private $cache;
    public function indexAction()
    {
        Auth::mustHave('chat.read');
        $this->display('index');
    }

    public function proxyAction($url = null)
    {
        Auth::mustHave('chat.read');
        if(is_null($url))
        {
            die;
        }
        $url = urldecode(base64_decode($url));
        $analyzed = parse_url($url);
        if($analyzed)
        {
            if($analyzed['scheme'] == 'http' || $analyzed['scheme'] == 'https')
            {
                header('Content-Type: application/force-download;');
                header('Content-Transfer-Encoding: binary');
                session_write_close();
                @readfile($url);
            }
        }
        die;
    }

    public function getfileAction($hash)
    {
        //Auth::mustHave('chat.read');
        $upload = Upload::findOne('hash = ?', array($hash));
        if($upload)
        {
            session_write_close();
            ob_end_flush();
            $path = \shozu\Shozu::getInstance()->project_root . 'applications/chat/files/uploads/' . $upload->hash;
            header('content-type: ' . $upload->mime);
            header('Content-Disposition: attachment; filename="' . \shozu\Inflector::fileName($upload->name) . '"');
            header('Content-Length: ' . filesize($path));
            readfile($path);
        }
        die;
    }
    
    public function uploadAction()
    {
        if(isset($_FILES['up']) && $_FILES['up']['error'] == 0)
        {
            Auth::mustHave('chat.write');
            $s = \shozu\Shozu::getInstance();
            $hash = sha1_file($_FILES['up']['tmp_name']);
            $destination = $s->project_root . 'applications/chat/files/uploads/' . $hash;
            if(move_uploaded_file($_FILES['up']['tmp_name'], $destination))
            {            
                $upload = Upload::findOne('hash = ?', array($hash));
                if(!$upload)
                {
                    $upload = new Upload;
                }
                $upload->setHash($hash);
                $upload->setName($_FILES['up']['name']);
                $upload->setMime($_FILES['up']['type']);
                $upload->save();
                $user = Auth::getUser();
                $post = new Post;
                $post->setUser_id($user->getId());
                if(strstr($upload->getMime(), 'image/'))
                {
                    $post->setMessage('[img:'.$s->url('chat/index/getfile', array($hash)).']');
                }
                else
                {
                    $post->setMessage('[url:'.$s->url('chat/index/getfile', array($hash)).'|'.$upload->getName().']');
                }
                $post->save();
            }
        }
        $this->display('upload');
    }
    
    public function postAction()
    {
        Auth::mustHave('chat.write');
        $user = Auth::getUser();
        $this->notifyPresence($user);
        try
        {
            if(!isset($_POST['message']) || !isset($_POST['last']))
            {
                throw new Exception('Bad Request Format');
            }
            if(is_null($this->lastPost()))
            {
                $this->lastPost($_POST['last']);
            }
            $message = trim($_POST['message']);
            $content = array();
            if(!empty($message))
            {
                if(substr($message, 0, 1) == '/')
                {
                    $content = $this->executeCommand($message);
                }
                else
                {
                    $post = new Post;
                    $post->setUser_id($user->getId());
                    $post->setMessage($message);
                    $post->save();
                    $content = $this->getPostsAsJSON((int)$this->lastPost());
                }
            }
            $last_post = $this->lastPost();
            $this->lastPost(Post::getLastInsertedMessageId());
            $this->sendJSON(array(
                'status' => 'ok',
                'loggedIn' => implode(', ', $this->getAvailableUsersLogins()),
                'lastMessage' => Post::getLastInsertedMessageId(),
                'content' => count($content) ? $content : $this->getPostsAsJSON((int)$last_post)
            ));
        }
        catch(Exception $e)
        {
            $this->sendJSON(array(
                'status' => 'ko',
                'content' => $e->getMessage()));
        }
        catch(\Exception $e)
        {
            $this->sendUnknownError();
        }
    }

    private function lastPost($id = null)
    {
        if(!is_null($id))
        {
            \shozu\Shozu::getInstance()->session->last_post = $id;
        }
        return \shozu\Shozu::getInstance()->session->last_post;
    }
    private function executeCommand($message)
    {
        try
        {
            $args = $this->getCommandArgs($message);
            if($args[0] == 'last')
            {
                $lastInserted = Post::getLastInsertedMessageId();
                $from = $lastInserted - (int)$args[1];
                return $this->getPostsAsJSON(abs($from));
            }
            if($args[0] == 'lasturl')
            {
                $urls = Url::lastLinks($args[1]);
                $html = count($urls) . ' links:<br/>';

                foreach($urls as $url)
                {
                    $html .= '<a href="'.$url->getHref().'" target="_blank">'.htmlspecialchars($url->getHref()).'</a><br/>';
                }
                return array(array(
                    'u' => 'system',
                    't' => date('Y-m-d H:i:s'),
                    'm' => $html
                ));
            }
            if($args[0] == 'lastimg')
            {
                $urls = Url::lastImages($args[1]);
                $html = count($urls) . ' images:<br/>';

                foreach($urls as $url)
                {
                    $html .= '<a href="'.$url->getHref().'" target="_blank">'.htmlspecialchars($url->getHref()).'</a><br/>';
                }
                return array(array(
                    'u' => 'system',
                    't' => date('Y-m-d H:i:s'),
                    'm' => $html
                ));
            }
            return array();
        }
        catch(\Exception $e)
        {
            die($e->getMessage());
        }
    }

    private function notifyPresence(User $user)
    {
        $cache = $this->getCache();
        $now = time();
        $users = $cache->fetch(self::CACHE_USERS_LOGGED_IN);
        if(!$users)
        {
            $users = array();
        }
        $users[$user->getLogin()] = $now;
        $cache->store(self::CACHE_USERS_LOGGED_IN, $users);
    }

    private function getAvailableUsersLogins()
    {
        $now = time();
        $users = $this->getCache()->fetch(self::CACHE_USERS_LOGGED_IN);
        if(!$users)
        {
            return array();
        }
        $return = array();
        foreach($users as $login => $time)
        {
            if($time + 60  > $now)
            {
                $return[] = $login;
            }
        }
        return $return;
    }

    private function getCache()
    {
        if(is_null($this->cache))
        {
            if(function_exists('apc_fetch'))
            {
                $this->cache = \shozu\Cache::getInstance('chat_cache', array('type' => 'ram'));
            }
            else
            {
                $this->cache = \shozu\Cache::getInstance('chat_cache', array('type' => 'disk'));
            }
        }
        return $this->cache;
    }

    private function getCommandArgs($string)
    {
        if(substr($string,0,1) == '/')
        {
            $string = substr($string,1);
        }
        $parts = explode(' ', $string);
        $return = array();
        foreach($parts as $v)
        {
            $v = trim($v);
            if(!empty($v))
            {
                $return[] = $v;
            }
        }
        return $return;
    }

    private function sendUnknownError()
    {
        $this->sendJSON(array(
                'status' => 'ko',
                'content' => 'Ce chat de merde s\'est lamentablement vautré comme une loutre bourrée à la bière un soir de Saint Sylvestre.'));
    }

    private function sendJSON($data)
    {
        header('content-type: application/json');
        die(json_encode($data));
    }

    private function getPostsAsJSON($from)
    {
        $posts = array();
        foreach(Post::getPostsFrom((int)$from) as $post)
        {
            $username = 'anonymous';
            $user = User::findOne('id = ?', array($post->getUser_id()));
            if($user)
            {
                $username = $user->getLogin();
            }
            $posts[] = array(
                'u' => $username,
                't' => $post->getCreated_at()->format('Y-m-d H:i:s'),
                'm' => $post->getFormattedMessage()
            );
        }
        return $posts;
    }
}