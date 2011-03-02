<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Index/Lookup Service Description</title>
</head>
<body>
    <h1>Index/Lookup Service Description</h1>
    <h2>Indexing a document</h2>
    <h3>Request</h3>
    <?php

    $document = array(
        'identifier' => 'CDEVANS',
        'collection' => 'my cd wish list',
        'metas' => array(
            'url'   => 'http://www.amazon.fr/Waltz-Debby-Bill-Trio-Evans/dp/B000000YBQ',
            'title' => 'Bill Evans - Waltz for Debby'
        ),
        'fields' => array(
            'artist' => array('value' => 'Bill Evans', 'analyzer' => 'standard'),
            'title' => array('value' => 'Waltz for Debby', 'analyzer' => 'standard'),
            'genre' => array('value' => 'jazz', 'analyzer' => 'standard')
        )
    );

    ?>
    <p>Do a POST request at http://ac.me.tld/search/endpoint/ with the following JSON body:</p>
<pre>
<?php echo htmlspecialchars('{
   "action":"index",
   "setIdentifier":"CDEVANS",
   "setCollection":"my cd wish list",
   "setMetas":{
      "url":"http:\/\/www.amazon.fr\/Waltz-Debby-Bill-Trio-Evans\/dp\/B000000YBQ",
      "title":"Bill Evans - Waltz for Debby"
   },
   "addFields":{
      "artist":{
         "value":"Bill Evans",
         "analyzer":"standard"
      },
      "title":{
         "value":"Waltz for Debby",
         "analyzer":"standard"
      }
   }
}'); ?>
</pre>
    <p>Hints:</p>
    <ul>
        <li>setCollection is not required,</li>
        <li>Analyzer is not required,</li>
        <li>If a specified analyzer is not available, then the standard analyzer is used,</li>
        <li>If a previous document with the same identifier is already indexed, it gets deleted before the new document is inserted.</li>
    </ul>
    <h3>Response</h3>
    <p>The response is a JSON object with status and message:</p>
<pre><?php
echo htmlspecialchars('{
   "status":"ok",
   "message":"document has been added to index."
}');?>
</pre>
    <h2>Searching the index</h2>
    <h3>Request</h3>
    <p>Do a POST request at http://ac.me.tld/search/endpoint/ with the following JSON body:</p>
<?php
    $search = array(
        'setType' => 'and',
        'limitToCollection' => 'my cd wish list',
        'analyzeText' => array('value' => 'Bill Evans', 'analyzer' => 'standard'),
        'getDocuments' => array('offset' => 0,'limit' => 20)
    );
?>
<pre><?php
echo htmlspecialchars('{
   "action":"search",
   "setType":"and",
   "limitToCollection":"my cd wish list",
   "limitToField":"artist",
   "analyzeText":[{
      "value":"Bill Evans",
      "analyzer":"standard"
   }],
   "getDocuments":{
      "offset":0,
      "limit":20
   }
}');?>
</pre>
    <p>Hints:</p>
    <ul>
        <li>setType is not required,</li>
        <li>limitToCollection is not required,</li>
        <li>limitToField is not required,</li>
        <li>Analyzer is not required,</li>
        <li>If a specified analyzer is not available, then the standard analyzer is used.</li>
    </ul>
    <h3>Response</h3>
    <p>The response is a JSON object:</p>
    <?php

    $response = array(
        'total' => 1,
        'offset' => 0,
        'limit' => 20,
        'documents' => array(
            array(
                'identifier' => 'CDEVANS',
                'collection' => 'my cd wish list',
                'metas' => array(
                    'url'   => 'http://www.amazon.fr/Waltz-Debby-Bill-Trio-Evans/dp/B000000YBQ',
                    'title' => 'Bill Evans - Waltz for Debby'
                )
            )
        )
    );
    ?>
<pre><?php
echo htmlspecialchars('{
   "status":"ok",
   "total":1,
   "offset":0,
   "limit":20,
   "documents":[
      {
         "identifier":"CDEVANS",
         "collection":"my cd wish list",
         "metas":{
            "url":"http:\/\/www.amazon.fr\/Waltz-Debby-Bill-Trio-Evans\/dp\/B000000YBQ",
            "title":"Bill Evans - Waltz for Debby"
         }
      }
   ]
}');?>
</pre>
    <h2>Deleting a document</h2>
    <h3>Request</h3>
    <p>Do a POST request at http://ac.me.tld/search/endpoint/ with the following JSON body:</p>
<pre><?php
echo htmlspecialchars('{
   "action":"delete",
   "identifier":"CDEVANS"
}');?>
</pre>
    <h3>Response</h3>
    <p>The response is a JSON object with status and message:</p>
<pre><?php
echo htmlspecialchars('{
   "status":"ok",
   "message":"document has been removed from index."
}');?>
</pre>
    <h2>Authentication</h2>
    <p>Use HTTP basic auth:</p>
    <pre>Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==</pre>
</body>
</html>