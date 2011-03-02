<?php
namespace search\lib;
class IndexClient
{
    private $endpoint;
    private $login;
    private $password;

    public function __construct($endpoint, $login, $password)
    {
        $this->endpoint = $endpoint;
        $this->login = $login;
        $this->password = $password;
    }

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    public function deleteDocument($identifier)
    {
        $toJSON = array(
            'action' => 'delete',
            'identifier' => $identifier
        );
        $this->postRequest(json_encode($toJSON));
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function newDocument()
    {
        return new Document($this);
    }

    public function newSearch()
    {
        return new Search($this);
    }

    public function postRequest($json)
    {
        $response_string = $this->filePostContents($this->endpoint, $json, false);
        if($response_string === false)
        {
            throw new Exception('could not get response');
        }
        $response = json_decode($response_string, true);
        if($response === NULL)
        {
            $e = new Exception('could not decode response');
            $e->setLastRawResponse($response_string);
            throw $e;
        }
        
        if($response['status'] != 'ok')
        {
            throw new Exception($response['message']);
        }
        return $response;
    }


    private function filePostContents($url, $data, $headers = false)
    {
        $url = parse_url($url);
        if(is_array($data))
        {
            $_data = array();
            while(list($n,$v) = each($data))
            {
                $_data[] = "$n=".str_replace(array('/',':'),array('%2F','%3A'),$v);
            }
            $data = implode('&', $_data);
        }
        if (!isset($url['port']))
        {
            if ($url['scheme'] == 'http')
            {
                $url['port'] = 80;
            }
            elseif ($url['scheme'] == 'https')
            {
                $url['port'] = 443;
            }
        }
        $url['query'] = isset($url['query']) ? $url['query'] : '';

        $url['protocol'] = $url['scheme'] . '://';
        $eol = "\r\n";

        $post_content = 'POST ' . $url['protocol'] . $url['host'] . $url['path']. ' HTTP/1.0' . $eol.
            'Host: ' . $url['host'] . $eol.
            'Referer: ' . $url['protocol'] . $url['host'] . $url['path'] . $eol.
            'Content-Type: application/x-www-form-urlencoded' . $eol.
            'Content-Length: ' . strlen($data) . $eol.
            'Authorization: Basic '.base64_encode($this->login.':'.$this->password).$eol.
            $eol.$data;
        $fp = fsockopen(($url['port'] == '443' ? 'ssl://': '').$url['host'], $url['port'], $errno, $errstr, 30);
        if($fp)
        {
            fputs($fp, $post_content);
            $result = '';
            while(!feof($fp))
            {
                $result .= fgets($fp, 128);
            }
            fclose($fp);
            if (!$headers)
            {
                //removes headers
                $pattern="/^.*\r\n\r\n/s";
                $result=preg_replace($pattern,'',$result);
            }
            return $result;
        }
        return false;
    }
}

class Document
{
    private $index;
    private $identifier;
    private $collection;
    private $metas = array();
    private $fields = array();


    public function  __construct(IndexClient $index)
    {
        $this->index = $index;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;
        return $this;
    }


    public function getCollection()
    {
        return $this->collection;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getMeta($meta_name)
    {
        if(isset($this->metas[$meta_name]))
        {
            return $this->metas[$meta_name];
        }
    }

    /**
     *
     * @param string $meta_name
     * @param mixed $meta_value
     * @return Document
     */
    public function setMeta($meta_name, $meta_value)
    {
        $this->metas[$meta_name] = $meta_value;
        return $this;
    }

    public function addField($field_name, $field_value, $analyzer = 'standard')
    {
        $this->fields[$field_name] = array('value' => $field_value, 'analyzer' => $analyzer);
        return $this;
    }

    public function addToIndex()
    {
        $toJSON = array(
            'action' => 'index',
            'setIdentifier' => $this->identifier,
            'setCollection' => $this->collection,
            'setMetas' => $this->metas,
            'addFields' => $this->fields
        );
        $this->index->postRequest(json_encode($toJSON));
        return $this;
    }
}

class Search
{
    private $total = 0;
    private $type = 'and';
    private $collection;
    private $field;
    private $analyze = array();
    private $offset = 0;
    private $limit = 20;
    private $index;
    private $analyzed = array();

    public function  __construct(IndexClient $index)
    {
        $this->index = $index;
    }

    public function setType($type = 'and')
    {
        if(!in_array($type, array('and', 'or')))
        {
            throw new Exception('unknown search type "'.$type.'"');
        }
        $this->type = $type;
        return $this;
    }

    public function limitToCollection($collection)
    {
        $this->collection = $collection;
        return $this;
    }

    public function limitToField($field_name)
    {
        $this->field = $field_name;
        return $this;
    }

    public function analyzeText($text, $analyzer = 'standard')
    {
        $this->analyze[] = array('value' => $text, 'analyzer' => $analyzer);
        return $this;
    }


    public function getTotal()
    {
        return $this->total;
    }

    public function getAnalyzedWords()
    {
        return $this->analyzed;
    }

    public function getDocuments($offset = 0, $limit = 20)
    {
        $this->offset = $offset;
        $this->limit = $limit;

        $toJSON = array(
            'action' => 'search',
            'setType' => $this->type,
            'limitToCollection' => $this->collection,
            'limitToField' => $this->field,
            'analyzeText' => $this->analyze,
            'getDocuments' => array('offset' => $this->offset, 'limit' => $this->limit)
        );
        $result = $this->index->postRequest(json_encode($toJSON));
        $this->total = $result['total'];
        $this->analyzed = $result['analyzed'];

        foreach($result['documents'] as $k => $document_array)
        {
            $document = new Document($this->index);
            $document->setCollection($document_array['collection']);
            $document->setIdentifier($document_array['identifier']);
            foreach($document_array['metas'] as $meta_name => $meta_value)
            {
                $document->setMeta($meta_name, $meta_value);
            }
            $result['documents'][$k] = $document;
        }

        return $result['documents'];
    }
}


class Exception extends \Exception
{
    private $last_raw_response;
    public function setLastRawResponse($last_response)
    {
        $this->last_raw_response = $last_response;
    }

    public function getLastRawResponse()
    {
        return $this->last_raw_response;
    }
}