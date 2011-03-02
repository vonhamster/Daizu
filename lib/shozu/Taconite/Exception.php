<?php
namespace shozu\Taconite;
class Exception extends \Exception{
    private $xml;
    public function setXML($xml)
    {
        $this->xml = $xml;
    }
    public function getXML($xml)
    {
        return $this->xml;
    }
}