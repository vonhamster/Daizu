<?php
namespace chat\models;
class Post extends \shozu\ActiveBean
{
    protected function setTableDefinition()
    {
        $this->addColumn('user_id');
        $this->addColumn('message');
    }

    public static function bb2html($input)
    {
        $pattern = array();
        $replace = array();

        $pattern[] = '/\[url:([^\]]*?)\|([^\]]*?)\]/i';
        $replace[] = '<a href="$1" class="media" target="_blank">$2</a>';

        $pattern[] = '/\[url:([^\]]*?)\]/i';
        $replace[] = '<a href="$1" class="media" target="_blank">$1</a>';

        $pattern[] = '/\[mp3:([^\]]*?)\]/i';
        $replace[] = '<a href="$1" class="mp3" target="_blank">$1</a>';

        $pattern[] = '/\[yt:([^\]]*?)\]/i';
        $replace[] = '<a href="$1" class="youtube" target="_blank">$1</a>';

        $pattern[] = '/\[img:([^\]]*?)\]/ie';
        $replace[] = '\'<img src="'.self::proxyUrl().'\'.base64_encode(urlencode(\'$1\')).\'" alt=" " class="out"/>\'';

        $pattern[] = '/\[code:([^\]]*?)\]/i';
        $replace[] = '<pre class="prettyprint">$1</pre>';

        ksort($pattern);
        ksort($replace);

        return preg_replace($pattern, $replace, trim($input));
    }

    public static function proxyUrl()
    {
        return \shozu\Shozu::getInstance()->url('chat/index/proxy');
    }

    protected function postSave()
    {
        if($this->getCreated_at() == $this->getModified_at())
        {
            foreach(self::extractUrls($this->getMessage()) as $link)
            {
                $u = new Url;
                $u->setMessage_id($this->getId());
                $u->setType(Url::TYPE_LINK);
                $u->setHref($link);
                $u->save();
            }
            foreach(self::extractImages($this->getMessage()) as $link)
            {
                $u = new Url;
                $u->setMessage_id($this->getId());
                $u->setType(Url::TYPE_IMAGE);
                $u->setHref($link);
                $u->save();
            }
        }
    }

    public static function extractUrls($message)
    {
        $uid = '###'.uniqid();
        $length = strlen($uid);

        $pattern = array();
        $replace = array();

        $pattern[] = '/\[url:([^\]]*?)\|([^\]]*?)\]/i';
        $replace[] = "\n$uid".'$1'."\n";

        $pattern[] = '/\[url:([^\]]*?)\]/i';
        $replace[] = "\n$uid".'$1'."\n";

        $pattern[] = '/\[mp3:([^\]]*?)\]/i';
        $replace[] = "\n$uid".'$1'."\n";

        $pattern[] = '/\[yt:([^\]]*?)\]/i';
        $replace[] = "\n$uid".'$1'."\n";


        ksort($pattern);
        ksort($replace);

        $message = preg_replace($pattern, $replace, trim($message));

        $lines = explode("\n", $message);

        $return = array();

        foreach($lines as $line)
        {
            if(substr($line, 0, $length) == $uid)
            {
                $return[] = substr($line, $length);
            }
        }

        return $return;
    }

    public static function extractImages($message)
    {
        $uid = '###'.uniqid();
        $length = strlen($uid);

        $pattern = array();
        $replace = array();

        $pattern[] = '/\[img:([^\]]*?)\]/i';
        $replace[] = "\n$uid".'$1'."\n";

        ksort($pattern);
        ksort($replace);

        $message = preg_replace($pattern, $replace, trim($message));

        $lines = explode("\n", $message);

        $return = array();

        foreach($lines as $line)
        {
            if(substr($line, 0, $length) == $uid)
            {
                $return[] = substr($line, $length);
            }
        }

        return $return;
    }

    public function getFormattedMessage()
    {
        return self::bb2html(htmlspecialchars($this->getMessage()));
    }

    public static function extractYoutubePlayers($message)
    {
        $content = '';
        $urls = self::extractUrls($this->getMessage());

        foreach($urls as $url)
        {
            if(strstr($url, 'youtube.com'))
            {
                $content .= self::makeYoutbePlayer($url);
            }
        }
        return $content;
    }

    public static function makeYoutbePlayer($url)
    {
        $content = '';
        $vidparser = parse_url($url);
        parse_str($vidparser['query'], $query);
        $vidid = ($query['v']);
        $content .= "<object width=\"290\" height=\"235\"><param name=\"movie\" value=\"http://www.youtube.com/v/".$vidid."&hl=en&fs=1&rel=0\"></param><param name=\"allowFullScreen\" value=\"true\"></param><embed src=\"http://www.youtube.com/v/".$vidid."&hl=en&fs=1&rel=0\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" width=\"290\" height=\"235\"></embed></object>";
        return $content;
    }

    public static function getLastInsertedMessageId()
    {
        try
        {
            return self::getAdapter()->getCell('select id from '.self::beanName().' order by id desc limit 1');
        }
        catch(\Exception $e)
        {
            return 0;
        }
    }

    public static function getPostsFrom($id)
    {
        return self::find('id > ?', array($id));
    }
}