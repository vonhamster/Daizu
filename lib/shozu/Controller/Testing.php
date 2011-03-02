<?php
/**
 * Inspired from redunit
 */
namespace shozu\Controller;
class Testing extends \shozu\Controller\CLI
{
    protected $tests = 0;
    public function allAction()
    {
        $methods = get_class_methods($this);      
        foreach($methods as $method)
        {
            if(substr($method, 0, 4) == 'test')
            {
                try
                {
                    $this->$method();
                }
                catch (\Exception $e)
                {
                    die(get_class($e) . ' on ' . $method . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
                }
            }
        }
        echo "\nPassed all {$this->tests} tests\n";
    }

    protected function pass()
    {
        $this->tests++;
    }

    protected function fail($message = '')
    {
        echo "\nFailed ! $message\n";
        debug_print_backtrace();
        die;
    }

    protected function asrt($a, $b)
    {
        if($a === $b)
        {
            $this->pass();
        }
        else
        {
            $this->fail('Expected '.$b.' but got '.$a);
        }
    }
}