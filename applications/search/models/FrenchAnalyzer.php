<?php
namespace search\models;
class FrenchAnalyzer implements Analyzer
{
    private static $stopWords = array(
        '10eme',
        '1er',
        '1ere',
        '2eme',
        '3eme',
        '4eme',
        '5eme',
        '6eme',
        '7eme',
        '8eme',
        '9eme',
        'a',
        'abord',
        'afin',
        'ah',
        'ai',
        'aie',
        'aient',
        'aies',
        'ainsi',
        'ais',
        'ait',
        'allaient',
        'allo',
        'allons',
        'alors',
        'apres',
        'as',
        'assez',
        'attendu',
        'au',
        'aucun',
        'aucune',
        'aucuns',
        'aujourd',
        'aujourdhui',
        'aupres',
        'auquel',
        'auquelles',
        'auquels',
        'aura',
        'aurai',
        'auraient',
        'aurais',
        'aurait',
        'auras',
        'aurez',
        'auriez',
        'aurions',
        'aurons',
        'auront',
        'aussi',
        'aussitot',
        'autant',
        'autre',
        'autres',
        'aux',
        'auxquelles',
        'auxquels',
        'avaient',
        'avais',
        'avait',
        'avant',
        'avec',
        'avez',
        'aviez',
        'avions',
        'avoir',
        'avons',
        'ayant',
        'ayante',
        'ayantes',
        'ayants',
        'ayez',
        'ayons',
        'b',
        'bah',
        'beaucoup',
        'bien',
        'bigre',
        'bon',
        'boum',
        'bravo',
        'brrr',
        'c',
        'ca',
        'car',
        'ce',
        'ceci',
        'cela',
        'celle',
        'celle-ci',
        'celle-la',
        'celles',
        'celles-ci',
        'celles-la',
        'celui',
        'celui-ci',
        'celui-la',
        'cent',
        'cependant',
        'certain',
        'certaine',
        'certaines',
        'certains',
        'certes',
        'ces',
        'cet',
        'cette',
        'ceux',
        'ceux-ci',
        'ceux-la',
        'chacun',
        'chacune',
        'chaque',
        'chez',
        'chiche',
        'chut',
        'ci',
        'cinq',
        'cinquantaine',
        'cinquante',
        'cinquantieme',
        'cinquieme',
        'clac',
        'clic',
        'combien',
        'comme',
        'comment',
        'compris',
        'concernant',
        'couic',
        'crac',
        'd',
        'da',
        'dabord',
        'dans',
        'de',
        'debout',
        'debut',
        'deca',
        'dedans',
        'dehors',
        'deja',
        'dela',
        'depuis',
        'derriere',
        'des',
        'desormais',
        'desquelles',
        'desquels',
        'dessous',
        'dessus',
        'deux',
        'deuxieme',
        'deuxiemement',
        'devant',
        'devers',
        'devra',
        'devrait',
        'different',
        'differente',
        'differentes',
        'differents',
        'dire',
        'divers',
        'diverse',
        'diverses',
        'dix',
        'dix-huit',
        'dix-neuf',
        'dix-sept',
        'dixieme',
        'doit',
        'doivent',
        'donc',
        'donne',
        'dont',
        'dos',
        'douze',
        'douzieme',
        'dring',
        'droite',
        'du',
        'duquel',
        'durant',
        'e',
        'effet',
        'eh',
        'elle',
        'elle-meme',
        'elles',
        'elles-memes',
        'eme',
        'en',
        'encore',
        'enfin',
        'entre',
        'envers',
        'environ',
        'er',
        'es',
        'essai',
        'est',
        'est-ce',
        'et',
        'etaient',
        'etais',
        'etait',
        'etant',
        'etante',
        'etantes',
        'etants',
        'etat',
        'etc',
        'ete',
        'etee',
        'etees',
        'etes',
        'etiez',
        'etions',
        'etre',
        'eu',
        'eue',
        'eues',
        'euh',
        'eumes',
        'eurent',
        'eus',
        'eusse',
        'eussent',
        'eusses',
        'eussiez',
        'eussions',
        'eut',
        'eutes',
        'eux',
        'eux-memes',
        'excepte',
        'f',
        'facon',
        'fais',
        'faisaient',
        'faisant',
        'fait',
        'faites',
        'faut',
        'feront',
        'fi',
        'flac',
        'floc',
        'fois',
        'font',
        'fumes',
        'fur',
        'furent',
        'fus',
        'fusse',
        'fussent',
        'fusses',
        'fussiez',
        'fussions',
        'fut',
        'futes',
        'g',
        'gens',
        'grace',
        'h',
        'ha',
        'haut',
        'he',
        'hein',
        'helas',
        'hem',
        'hep',
        'hi',
        'ho',
        'hola',
        'hop',
        'hormis',
        'hors',
        'hou',
        'houp',
        'hue',
        'hui',
        'huit',
        'huitieme',
        'hum',
        'hurrah',
        'i',
        'ici',
        'il',
        'ils',
        'importe',
        'j',
        'je',
        'jusqu',
        'jusque',
        'juste',
        'k',
        'l',
        'la',
        'laquelle',
        'las',
        'le',
        'lequel',
        'les',
        'lesquelles',
        'lesquels',
        'leur',
        'leurs',
        'longtemps',
        'lors',
        'lorsque',
        'lui',
        'lui-meme',
        'm',
        'ma',
        'maint',
        'maintenant',
        'mais',
        'malgre',
        'me',
        'melle',
        'meme',
        'memes',
        'merci',
        'mes',
        'mien',
        'mienne',
        'miennes',
        'miens',
        'mille',
        'mince',
        'mine',
        'mm',
        'mme',
        'moi',
        'moi-meme',
        'moins',
        'moment',
        'mon',
        'mot',
        'moyennant',
        'mr',
        'n',
        'na',
        'ne',
        'neanmoins',
        'neuf',
        'neuvieme',
        'ni',
        'nombreuses',
        'nombreux',
        'nommes',
        'non',
        'non-',
        'nos',
        'notamment',
        'notre',
        'notres',
        'nous',
        'nous-memes',
        'nouveaux',
        'nul',
        'o',
        'oh',
        'ohe',
        'ole',
        'olle',
        'on',
        'ont',
        'onze',
        'onzieme',
        'ore',
        'ou',
        'ouf',
        'oui',
        'ouias',
        'oust',
        'ouste',
        'outre',
        'o|',
        'p',
        'paf',
        'pan',
        'par',
        'parce',
        'parfois',
        'parmi',
        'parole',
        'partant',
        'particulier',
        'particuliere',
        'particulierement',
        'partout',
        'pas',
        'passe',
        'pendant',
        'personne',
        'personnes',
        'peu',
        'peut',
        'peut-etre',
        'peuvent',
        'peux',
        'pff',
        'pfft',
        'pfut',
        'piece',
        'pif',
        'plein',
        'plouf',
        'plupart',
        'plus',
        'plusieurs',
        'plutot',
        'pouah',
        'pour',
        'pourquoi',
        'premier',
        'premiere',
        'premierement',
        'pres',
        'proche',
        'psitt',
        'puis',
        'puisqu',
        'puisque',
        'q',
        'qu',
        'quand',
        'quant',
        'quant-a-soi',
        'quanta',
        'quarante',
        'quatorze',
        'quatre',
        'quatre-vingt',
        'quatrieme',
        'quatriemement',
        'que',
        'quel',
        'quelconque',
        'quelle',
        'quelles',
        'quelqu',
        'quelque',
        'quelquefois',
        'quelques',
        'quelquun',
        'quels',
        'qui',
        'quiconque',
        'quinze',
        'quoi',
        'quoique',
        'quot',
        'r',
        'revoici',
        'revoila',
        'rien',
        's',
        'sa',
        'sacrebleu',
        'sans',
        'sapristi',
        'sauf',
        'se',
        'seize',
        'selon',
        'sept',
        'septieme',
        'sera',
        'serai',
        'seraient',
        'serais',
        'serait',
        'seras',
        'serez',
        'seriez',
        'serions',
        'serons',
        'seront',
        'ses',
        'seulement',
        'si',
        'sien',
        'sienne',
        'siennes',
        'siens',
        'sinon',
        'sitot',
        'six',
        'sixieme',
        'soi',
        'soi-meme',
        'soient',
        'sois',
        'soit',
        'soixante',
        'sommes',
        'son',
        'sont',
        'sous',
        'souvent',
        'soyez',
        'soyons',
        'stop',
        'suis',
        'suivant',
        'sujet',
        'sur',
        'surtout',
        't',
        'ta',
        'tac',
        'tandis',
        'tant',
        'te',
        'tel',
        'telle',
        'tellement',
        'telles',
        'tels',
        'tenant',
        'tes',
        'tic',
        'tien',
        'tienne',
        'tiennes',
        'tiens',
        'toc',
        'toi',
        'toi-meme',
        'ton',
        'touchant',
        'toujours',
        'tous',
        'tout',
        'toute',
        'toutefois',
        'toutes',
        'treize',
        'trente',
        'tres',
        'trois',
        'troisieme',
        'troisiemement',
        'troiw',
        'trop',
        'tsoin',
        'tsouin',
        'tu',
        'u',
        'un',
        'une',
        'unes',
        'uns',
        'v',
        'va',
        'vais',
        'valeur',
        'vas',
        've',
        'vers',
        'via',
        'vif',
        'vifs',
        'vingt',
        'vivat',
        'vive',
        'vives',
        'vlan',
        'voici',
        'voie',
        'voient',
        'voila',
        'vont',
        'vos',
        'votre',
        'votres',
        'vous',
        'vous-memes',
        'vu',
        'w',
        'x',
        'y',
        'z',
        'zut',
        'html',
        'php'
    );
    private static $instance;
    private function __construct()
    {

    }

    public static function getInstance()
    {
        if(is_null(self::$instance))
        {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    private static function stem($word)
    {
        if(function_exists('stemword'))
        {
            return stemword($word, self::$stem_lang, 'UTF_8');
        }
        elseif(function_exists('stem_french'))
        {
            return utf8_encode(stem_french(utf8_decode($word)));
        }
        // no stemmer found
        return $word;
    }

    public function analyze($text)
    {
        $text = \shozu\Inflector::removeDiacritics($text);
        $text = strip_tags($text);
        $text = strtolower($text);
        $text = str_replace(array('-','\'') , array(' ',' ') , $text);
        $text = preg_replace('/[\'`´"]/', '', $text);
        $text = preg_replace('/[^a-z0-9]/', ' ', $text);
        $text = str_replace('  ', ' ', $text);
        $terms = explode(' ', $text);
        $occurences = array();
        foreach($terms as $term)
        {
            $term = trim($term);
            if (empty($term))
            {
                continue;
            }
            if (in_array($term, self::$stopWords))
            {
                continue;
            }
            $occurence = new Occurence;
            $occurence->setWord(Word::fetch(self::stem($term)));
            $occurence->setWeight(10);
            $occurences[] = $occurence;
        }
        return $occurences;
    }
}