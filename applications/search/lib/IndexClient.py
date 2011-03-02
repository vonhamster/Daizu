import json
import urllib2
import base64

class Document(object):

    def __init__(self, index_client):
        self.index_client = index_client
        self.identifier = None
        self.collection = None
        self.metas = {}
        self.fields = {}

    def set_identifier(self, identifier):
        self.identifier = identifier
        return self

    def set_collection(self, collection):
        self.collection = collection
        return self

    def get_collection(self):
        return self.collection

    def get_identifier(self):
        return self.identifier

    def get_meta(self, meta_name):
        if meta_name in self.metas:
            return self.metas[meta_name]

    def set_meta(self, meta_name, meta_value):
        self.metas[meta_name] = meta_value
        return self

    def add_field(self, field_name, field_value, analyzer = "standard"):
        self.fields[field_name] = {'value':field_value, 'analyzer':analyzer}
        return self

    def add_to_index(self):
        to_json = {
            'action':'index',
            'setIdentifier':self.identifier,
            'setCollection':self.collection,
            'setMetas':self.metas,
            'addFields':self.fields}
        self.index_client.post_request(json.dumps(to_json))
        return self
        
class Search(object):

    def __init__(self, index_client):
        self.index_client = index_client
        self.total = 0
        self.type = 'and'
        self.collection = None
        self.field = None
        self.analyze = []
        self.offset = 0
        self.lmit = 20
        self.analyzed = []

    def set_type(self,type = 'and'):
        if type not in ['and','or']:
            raise IndexException('unknown search type')
        self.type = type
        return self

    def limit_to_collection(self,collection):
        self.collection = collection
        return self

    def limit_to_field(self, field):
        self.field = field
        return self

    def analyze_text(self, text, analyzer = 'standard'):
        self.analyze.append({'value':text,'analyzer':analyzer})
        return self

    def get_total(self):
        return self.total

    def get_analyzed_words(self):
        return self.analyzed

    def get_documents(self, offset = 0, limit = 20):
        self.offset = offset
        self.limit = limit
        to_json = {
            'action':'search',
            'setType':self.type,
            'limitToCollection':self.collection,
            'limitToField':self.field,
            'analyzeText':self.analyze,
            'getDocuments':{'offset':self.offset,'limit':self.limit}}
        results = self.index_client.post_request(json.dumps(to_json))
        self.total = results['total']
        self.analyzed = results['analyzed']
        returned_results = []
        for raw_document in results['documents']:
            document = Document(self.index_client)
            document.set_collection(raw_document['collection'])
            document.set_identifier(raw_document['identifier'])
            for key in raw_document['metas'].keys():
                document.set_meta(key,raw_document['metas'][key])
            returned_results.append(document)
        return returned_results

class IndexException(Exception):

    def __init__(self, message):
        self.message = message

    def __str__(self):
        return repr(self.message)

class IndexClient(object):

    def __init__(self, endpoint, login, password):
        self.endpoint = endpoint
        self.login = login
        self.password = password

    def set_endpoint(self, endpoint):
        self.endpoint = endpoint
        return self

    def set_login(self, login):
        self.login = login
        return self

    def set_password(self, password):
        self.password = password
        return self

    def delete_document(self, identifier):
        self.post_request(json.dumps({'action':'delete', 'identifier':identifier}))
        return self

    def new_document(self):
        return Document(self)
    
    def new_search(self):
        return Search(self)

    def post_request(self, json_string):
        response_string = self.file_post_contents(self.endpoint, json_string)
        if response_string == False:
            raise IndexException('could not get response')
        response = json.loads(response_string)
        if response['status'] != "ok":
            raise IndexException(response['message'])
        return response

    def file_post_contents(self, url, data):
        req = urllib2.Request(url)
        auth_string = base64.encodestring('%s:%s' % (self.login, self.password))[:-1]
        req.add_header("Authorization", "Basic %s" % auth_string)
        req.add_header("Content-type", "application/json")
        f = urllib2.urlopen(req, data)
        return f.read()


if __name__ == "__main__":
    index = IndexClient('http://www.desfrenes.com/search/endpoint/', 'searchtest', 'boogaloo')
    document = index.new_document()
    document.set_collection('python client test')
    document.set_identifier('CDPYTHON')
    document.set_meta('url', 'http://www.nothere.com/test/')
    document.add_field('artist', 'guido')
    document.add_field('album', 'python in 3 hours')
    document.add_to_index()

    search = index.new_search()
    search.analyze_text('guido')
    results = search.get_documents()
    for document in results:
        print document.get_meta('url')