<?php
namespace search\controllers;
use \search\models\Document as Document;
use \search\models\Collection as Collection;
use \search\models\Field as Field;
use \search\models\Search as Search;
use \search\models\Exception as Exception;
use \acl\lib\BasicAuth as Auth;
class Service extends \shozu\Controller
{
    public function indexAction()
    {
        $this->display('service_usage');
    }


    public function endpointAction()
    {
        Auth::mustHave('search.endpoint');
        $data = $this->getBodyData();
        if(!isset($data['action']))
        {
            $this->throwError('missing action');
        }
        if(!in_array($data['action'], array('index','search','delete')))
        {
            $this->throwError('unknown action');
        }

        if($data['action'] == 'index')
        {
            Auth::mustHave('search.index');
            $this->indexdocument($data);
        }
        if($data['action'] == 'search')
        {
            Auth::mustHave('search.search');
            $this->searchindex($data);
        }
        if($data['action'] == 'delete')
        {
            Auth::mustHave('search.delete');
            $this->deletedocument($data);
        }
    }

    private function indexdocument($data)
    {
        try
        {
            if(!isset($data['setIdentifier']))
            {
                $this->throwError('missing identifier');
            }
            $document = Document::findOne('identifier = ?', array($data['setIdentifier']));
            if(!$document)
            {
                $document = new Document;
            }
            $document->setIdentifier($data['setIdentifier']);
            if(isset($data['setCollection']))
            {
                $document->setCollection(Collection::fetch($data['setCollection']));
            }
            if(isset($data['setMetas']))
            {
                foreach ($data['setMetas'] as $meta_name => $meta_value)
                {
                    $document->setMeta($meta_name, $meta_value);
                }
            }
            if(isset($data['addFields']))
            {
                foreach($data['addFields'] as $field_name => $field_details)
                {
                    $document->addField(
                        $field_name,
                        $field_details['value'],
                        isset($field_details['analyzer']) ? $this->getAnalyzer($field_details['analyzer']) : $this->getAnalyzer('standard'));
                }
            }
            try
            {
                Document::beginTransaction();
                $document->addToIndex();
                Document::commit();
                $this->sendResponse('document has been added to index');
            }
            catch(Exception $e)
            {
                Document::rollBack();
                $this->throwError($e->getMessage());
            }
        }
        catch(\Exception $e)
        {
            $this->throwError('internal error');
        }
    }

    private function getAnalyzer($analyzer_name)
    {
        if($analyzer_name == 'french')
        {
            return \search\models\FrenchAnalyzer::getInstance();
        }
        return \search\models\StandardAnalyzer::getInstance();
    }

    private function searchindex($data)
    {
        try
        {
            if(!count($data['analyzeText']))
            {
                $this->throwError('nothing to search for');
            }
            if(!isset($data['getDocuments']['offset']))
            {
                $data['getDocuments']['offset'] = 0;
            }
            if(!isset($data['getDocuments']['limit']))
            {
                $data['getDocuments']['limit'] = 20;
            }

            $search = new search;
            if(isset($data['setType']))
            {
                $search->setType($data['setType']);
            }
            if(isset($data['limitToCollection']))
            {
                $search->limitToCollection(Collection::fetch($data['limitToCollection']));
            }
            if(isset($data['limitToField']))
            {
                $search->limitToField(Field::fetch($data['limitToField']));
            }

            foreach($data['analyzeText'] as $analyze_text)
            {
                $search->analyzeText(
                    $analyze_text['value'],
                    $this->getAnalyzer(isset($analyze_text['analyzer']) ? $analyze_text['analyzer'] : 'standard'));
            }
            $results = $search->getDocuments($data['getDocuments']['offset'], $data['getDocuments']['limit']);

            foreach($results as $k => $result)
            {
                $document = array();
                $document['identifier'] = $result->getIdentifier();
                $collection = $result->getCollection();
                if($collection)
                {
                    $document['collection'] = $collection->getLabel();
                }
                $document['metas'] = $result->getMetas();
                $results[$k] = $document;
            }

            $words = array();
            foreach($search->getAnalyzedWords() as $word)
            {
                $words[] = $word->getLabel();
            }

            header('Content-type: application/json');
            die(json_encode(array(
                'status' => 'ok',
                'total' => $search->count(),
                'offset' => $data['getDocuments']['offset'],
                'limit' => $data['getDocuments']['limit'],
                'documents' => $results,
                'analyzed' => $words
            )));
        }
        catch(\Exception $e)
        {
            $this->throwError('internal error');
        }
    }

    private function deletedocument($data)
    {
        try
        {
            if(!isset($data['identifier']))
            {
                $this->throwError('missing identifier');
            }
            $document = $document = Document::findOne('identifier = ?', array($data['identifier']));
            if(!$document)
            {
                $this->throwError('no such document');
            }
            $document->delete();
            header('Content-type: application/json');
            die(json_encode(array('status' => 'ok', 'message' => 'document removed from index')));
        }
        catch(\Exception $e)
        {
            $this->throwError('internal error');
        }
    }

    private function getBodyData()
    {
        $json = json_decode(file_get_contents('php://input'), true);
        if($json === NULL)
        {
            $this->throwError('not json data');
        }
        return $json;
    }

    private function throwError($error)
    {
        header('Content-type: application/json');
        die(json_encode(array('status' => 'ko', 'message' => $error)));
    }

    private function sendResponse($message)
    {
        header('Content-type: application/json');
        die(json_encode(array('status' => 'ok', 'message' => $message)));
    }
}