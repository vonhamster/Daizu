<?php
namespace shozu;
/**
 * string conversions
 *
 * @package MVC
 */
final class Inflector
{
/**
 *  Return an CamelizeSyntaxed (LikeThisDearReader) from something like_this_dear_reader.
 *
 * @param string $string Word to camelize
 * @return string Camelized word. LikeThis.
 */
    public static function camelize($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * Return an underscore_syntaxed (like_this_dear_reader) from something LikeThisDearReader.
     *
     * @param  string $string CamelCased word to be "underscorized"
     * @return string Underscored version of the $string
     */
    public static function underscore($string)
    {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
    }

    /**
     * Return a Humanized syntaxed (Like this dear reader) from something like_this_dear_reader.
     *
     * @param  string $string CamelCased word to be "underscorized"
     * @return string Underscored version of the $string
     */
    public static function humanize($string)
    {
        return ucfirst(str_replace('_', ' ', $string));
    }

    /**
     * Namespace model to db
     *
     * Convert namespaced names to underscored names
     *
     * @param string
     * @return string
     */
    public static function model2dbName($class)
    {
        if(substr($class, 0, 1) == '\\')
        {
            $class = substr($class, 1);
        }
        return strtolower(str_replace(array('\\','_models_'), array('_','_'), $class));
    }

    /**
     * Remove accents and other diacritics
     *
     * @param string $utf8String
     * @return string
     */
    public static function removeDiacritics($utf8String)
    {
        // strtr is not multibyte-aware so we have to decode to iso first
        // Important notice: this source code should be utf8 encoded
        return utf8_encode(strtr(utf8_decode($utf8String),
                     utf8_decode(
                        'ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ'),
                        'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn'));
    }

    /**
     * Makes a clean file name from a string
     *
     * @param string "dirty" file name
     * @return string "clean" file name
     */
    public static function fileName($string)
    {
        $string = self::removeDiacritics($string);
        $string = strtolower($string);
        $string = preg_replace("/[^a-z0-9\\.\\-\\_]/i",'_',$string);
        return $string;
    }


    /**
     * Replace non breaking space with normal space
     *
     * @param string $str
     * @return string
     */
    public static function replaceNBSP($str)
    {
        return str_replace(utf8_encode(html_entity_decode('&#160;')), ' ', $str);
    }

    /**
     * Multibyte-aware ucfirst.
     *
     * Uppercase first letter
     *
     * @param string $str
     * @param string $e encoding, defaults to utf-8
     * @return string
     */
    public static function ucfirst($str, $e = 'utf-8')
    {
        $fc = mb_strtoupper(mb_substr($str, 0, 1, $e), $e);
        return $fc . mb_substr($str, 1, mb_strlen($str, $e), $e);
    }


    /**
    * Replace Url in a text to a link
    *
    * @param string $text
    * @param boolean $target_blank
    * @return string
    */
   public static function url2Link($text, $target_blank = true)
   {
       if($target_blank)
       {
           return str_replace('&', '&amp;', preg_replace('/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i','<a target="_blank" href="$1">$1</a>',$text));
       }
       else
       {
           return str_replace('&', '&amp;', preg_replace('/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i','<a href="$1">$1</a>',$text));
       }
   }
}