<?php
namespace cms;
class Application implements \shozu\Application
{
    public static function getRoutes()
    {
        return array(
            '/rss.xml'                         => 'cms/index/contentrss',
            '/rss/:any.xml'                    => 'cms/index/contentrss/10/$1',
            '/admin/cms/'                      => 'cms/admin/index',
            '/admin/cms/resources/'            => 'cms/admin/resources',
            '/admin/cms/users/'                => 'cms/admin/users',
            '/admin/cms/newpage/'              => 'cms/admin/newpage',
            '/admin/cms/movepage/'             => 'cms/admin/movepage',
            '/admin/cms/removepage/'           => 'cms/admin/deletepage',
            '/admin/cms/tree/'                 => 'cms/admin/jsontree',
            '/admin/cms/renamepage/'           => 'cms/admin/renamepage',
            '/admin/cms/editpage/'             => 'cms/admin/editpage',
            '/admin/cms/savepage/'             => 'cms/admin/savepage',
            '/admin/cms/loadversion/:num/:num' => 'cms/admin/loadpageversion/$1/$2',
            '/admin/cms/commentpublishing/'    => 'cms/admin/togglecommentpublishing',
            '/admin/cms/clearcache/'           => 'cms/admin/clearcache',
            '/cms/postcomment/'                => 'cms/index/postcomment',
            '/admin/cms/elfinderconnector/'    => 'cms/admin/elfinderconnector',
            '/admin/cms/elfinderstrings/'      => 'cms/admin/elfinderstrings'
        );
    }

    public static function getObservers()
    {
        return array(
            'daizu.comment.new' => array(
                array('\cms\models\Comment', 'notify'))
        );
    }

    public static function getTranslations($lang_id)
    {
        if(strtolower($lang_id) == 'fr')
        {
            return array(
                'Author' => 'Auteur',
                'Edit page' => 'Éditer la page',
                'Edit site' => 'Éditer le contenu',
                'Manage resources' => 'Gérer les fichiers',
                'Manage users' => 'Gérer les utilisateurs',
                'Title' => 'Titre',
                'Published' => 'Publié',
                'Body' => 'Corps',
                'Heading' => 'Chapô',
                'URL' => 'URL',
                'SEO Title' => 'Titre SEO',
                'SEO Description' => 'Description SEO',
                'SEO Keywords' => 'Mots-clés SEO',
                'Published from date' => 'à partir du',
                'Published to date' => 'jusqu\'au',
                'Template' => 'Gabarit',
                'Layout' => 'Mise en page',
                'Save' => 'Enregistrer',
                'Save the page' => 'Enregistrer la page',
                'Create new page' => 'Créer une nouvelle page',
                'Rename page' => 'Renommer la page',
                'Delete page' => 'Effacer la page',
                'Choose date' => 'Choisir la date',
                'Su' => 'Di',
                'Mo' => 'Lu',
                'Tu' => 'Ma',
                'We' => 'Me',
                'Th' => 'Je',
                'Fr' => 'Ve',
                'Sa' => 'Sa',
                'Sunday' => 'Dimanche',
                'Monday' => 'Lundi',
                'Tuesday' => 'Mardi',
                'Wednesday' => 'Mercredi',
                'Thursday' => 'Jeudi',
                'Friday' => 'Vendredi',
                'Saturday' => 'Samedi',
                'Select a page in the left tree' => 'Sélectionnez une page dans l\'arborescence à droite',
                'Sure ?' => 'Certain ?',
                'Last update:' => 'Dernière mise à jour:',
                'Comments' => 'Commentaires :',
                'Allow comments' => 'Commentaires autorisés',
                'Name' => 'Nom',
                'Email' => 'Email',
                'Website' => 'Site web',
                'Comment' => 'Commentaire',
                'Comment this' => 'Commentez',
                'Could not save your comment. Please check the fields.' => "Impossible de sauver votre commentaire.\nVeuillez vérifier vorte saisie.",
                'Upload files' => 'Charger des fichiers',
                'Your comment has been saved and is waiting for approval.' => 'Votre commentaire a été enregistré. Il sera publié prochainement.',
                'Latest changes' => 'Derniers changements',
                'Published by' => 'Publié par',
                'Versions' => 'Versions',
                'Load' => 'Charger',
                'Page saved :-)' => 'Page sauvegardée :-)',
                'Toggle' => 'Montrer / Cacher',
                'Protected page.' => 'Page protégée.',
                'SEO' => 'SEO',
                'Link' => 'Lien',
                'Display' => 'Afficher',
                'Indexing' => 'Indexation',
                'Analyzer' => 'Analyseur',
                'Apply to descendants' => 'Appliquer aux descendants',
                'Clear cache' => 'Vider le cache'
            );
        }
        return array();
    }
}