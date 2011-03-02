<?php
namespace cms\controllers;
use \cms\models\Page as Page;
use \shozu\Benchmark as Benchmark;
if(!defined('DAIZU_TEST'))
{
    die('Not in test env');
}

class Test extends \shozu\Controller\Testing
{
    public function testPageSlugify()
    {
        $this->asrt(Page::slugify('-***Hello, World ! -'), 'hello-world');
    }

    public function testCheckRootUrl()
    {
        $this->asrt(Page::fetchRoot()->getUrl(), '/');
    }

    public function testActiveBeanGetPDOInstance()
    {
        $this->asrt(Page::getPDO() instanceof \PDO, true);
    }

    public function testPageUniqueUrl()
    {
        try
        {
            $p1 = new Page;
            $p1->setTitle('test');
            $p1->setUrl('/testurl/');
            $p1->save();

            $p2 = new Page;
            $p2->setTitle('test');
            $p2->setUrl('/testurl/');
            $p2->save();
        }
        catch(\RedBean_Exception_SQL $e)
        {
            $this->pass();
        }
        catch(\Exception $e)
        {
            $this->fail();
        }
    }

    public function testSerializeActiveBeanAction()
    {
        try
        {
            echo "new dummy\n";
            $dummy = new Dummy;
            echo "save dummy\n";
            $dummy->save();
            echo "serialize dummy\n";
            $serialized = serialize($dummy);
            echo "unserialize dummy\n";
            $respawn_dummy = unserialize($serialized);
            echo "serialize old dummy\n";
            $str1 = serialize($dummy);
            echo "serialize new dummy\n";
            $str2 = serialize($respawn_dummy);

            if($str1 === $str2)
            {
                echo "show old dummy\n";
                echo serialize($dummy)."\n";
                echo "show new dummy\n";
                echo serialize($respawn_dummy)."\n";
                $this->pass();
            }
            else
            {
                $this->fail();
            }
        }
        catch(\Exception $e)
        {
            die($e->getMessage());
        }
    }

}

class Dummy extends \shozu\ActiveBean
{
    protected function setTableDefinition()
    {

    }
}