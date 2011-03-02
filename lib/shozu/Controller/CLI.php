<?php
namespace shozu\Controller;
use \shozu\Controller as Controller;
class CLI extends Controller
{
    public function __construct()
    {
        if(PHP_SAPI != 'cli')
        {
            throw new \Exception('Wrong SAPI');
        }
        parent::__construct();
    }
}
