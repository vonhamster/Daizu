<?php
namespace shozu;
/**
 * Generate Taconite documents for the JQuery Taconite plugin. Uses a chainable API.
 *
 * Taconite is a simple way to had ajax capability to your application
 * (see http://malsup.com/jquery/taconite/)
 *
 * <code>
 * // Usage:
 * $taconite = new Taconite;
 * $taconite->replaceContent('#body', '<div id="container">Test</div>')
 *          ->alert('container created !')
 *          ->render();
 * </code>
 *
 * @author Mickael Desfrenes <desfrenes@gmail.com>
 * @link www.desfrenes.com
 */
class Taconite
{
/**
 * Taconite command file content
 *
 * @var string
 */
    private $content;

    /**
     * Compress js or not using jsmin
     *
     * @var bool
     */
    private $compress_js;

    /**
     * New Taconite document
     *
     * @param boolean $debug Debug mode
     * @param boolean $compress_js Javascript compression (needs 3rd party library JSMin)
     */
    public function __construct($debug = false, $compress_js = false)
    {
        $this->content = '';
        $this->compress_js = $compress_js;
        if ($debug)
        {
            $this->js('$.taconite.debug = true;');
        }
        else
        {
            $this->js('$.taconite.debug = false;');
        }
    }
    /**
     * Appends XHTML content to matching elements
     *
     * @param string $selector Any valid JQuery selector
     * @param string $content Any valid XHTML content
     * @return Taconite Taconite document instance
     */
    public function append($selector, $content)
    {
        $this->elementCommand('append', $selector, $content);
        return $this;
    }
    /**
     * Prepends XHTML content to matching elements
     *
     * @param string $selector Any valid JQuery selector
     * @param string $content Any valid XHTML content
     * @return Taconite Taconite document instance
     */
    public function prepend($selector, $content)
    {
        $this->elementCommand('prepend', $selector, $content);
        return $this;
    }
    /**
     * Puts XHTML content before matching elements
     *
     * @param string $selector Any valid JQuery selector
     * @param string $content Any valid XHTML content
     * @return Taconite Taconite document instance
     */
    public function before($selector, $content)
    {
        $this->elementCommand('before', $selector, $content);
        return $this;
    }
    /**
     * Puts XHTML content after matching elements
     *
     * @param string $selector Any valid JQuery selector
     * @param string $content Any valid XHTML content
     * @return Taconite Taconite document instance
     */
    public function after($selector, $content)
    {
        $this->elementCommand('after', $selector, $content);
        return $this;
    }
    /**
     * Wraps matching elements with given tags.
     *
     * Don't use text in $content
     *
     * @param string $selector Any valid JQuery selector
     * @param string $content Wrapper string
     * @return Taconite Taconite document instance
     */
    public function wrap($selector, $content)
    {
        $this->elementCommand('wrap', $selector, $content);
        return $this;
    }
    /**
     * Replaces matching elements with given content.
     *
     * This is not JQuery-native but a convenience of the Taconite plugin
     *
     * @param string $selector Any valid JQuery selector
     * @param string $content Any valid XHTML content
     * @return Taconite Taconite document instance
     */
    public function replace($selector, $content)
    {
        $this->elementCommand('replace', $selector, $content);
        return $this;
    }
    /**
     * Replaces matching element's content with given content.
     *
     * This is not JQuery-native but a convenience if the Taconite plugin
     *
     * @param string $selector Any valid JQuery selector
     * @param string $content Any valid XHTML content
     * @return Taconite Taconite document instance
     */
    public function replaceContent($selector, $content)
    {
        $this->elementCommand('replaceContent', $selector, $content);
        return $this;
    }
    /**
     * Removes matching elements
     *
     * @param string $selector Any valid JQuery selector
     * @return Taconite Taconite document instance
     */
    public function remove($selector)
    {
        $this->rawCommand('<remove select="' . $selector . '" />');
        return $this;
    }
    /**
     * Shows matching elements
     *
     * @param string $selector Any valid JQuery selector
     * @return Taconite Taconite document instance
     */
    public function show($selector)
    {
        $this->rawCommand('<show select="' . $selector . '" />');
        return $this;
    }
    /**
     * Hides matching elements
     *
     * @param string $selector Any valid JQuery selector
     * @return Taconite Taconite document instance
     */
    public function hide($selector)
    {
        $this->rawCommand('<hide select="' . $selector . '" />');
        return $this;
    }
    /**
     * Remove content from matching elements (JQuery's empty method)
     *
     * @param string $selector Any valid JQuery selector
     * @return Taconite Taconite document instance
     */
    public function removeContent($selector)
    {
        $this->rawCommand('<empty select="' . $selector . '" />');
        return $this;
    }
    /**
     * Adds class to matching elements
     *
     * @param string $class CSS class to add
     * @param string $selector Any valid JQuery selector
     * @return Taconite Taconite document instance
     */
    public function addClass($class, $selector)
    {
        $this->rawCommand('<addClass select="' . $selector . '" arg1="'
            . $class . '" /><addClass select="'
            . $selector . '" value="' . $class . '" />');
        return $this;
    }
    /**
     * Removes class from matching elements
     *
     * @param string $class CSS class to remove
     * @param string $selector Any valid JQuery selector
     * @return Taconite Taconite document instance
     */
    public function removeClass($class, $selector)
    {
        $this->rawCommand('<removeClass select="' . $selector . '" arg1="'
            . $class . '" /><removeClass select="'
            . $selector . '" value="' . $class . '" />');
        return $this;
    }
    /**
     * Toggles a class to matching elements
     *
     * @param string $class CSS class to toggle
     * @param string $selector Any valid JQuery selector
     * @return Taconite Taconite document instance
     */
    public function toggleClass($class, $selector)
    {
        $this->rawCommand('<toggleClass select="' . $selector . '" arg1="'
            . $class . '" /><toggleClass select="'
            . $selector . '" value="' . $class . '" />');
        return $this;
    }
    /**
     * Modifies a css property
     *
     * The taconite plugin requires that you "camelize" all css properties but
     * this will do it for you if forget it.
     *
     * @param string $selector Any valid JQuery selector
     * @param string $property Any CSS property
     * @param string $value CSS value
     * @return Taconite Taconite document instance
     */
    public function css($selector, $property, $value)
    {
        $property = $this->camelize($property);
        $taco = '<css select="' . $selector . '" name="' . $property
            . '" value="' . $value . '" />';
        $this->rawCommand($taco);
        return $this;
    }
    /**
     * Adds Javascript to be evaluated in the global context
     *
     * @param string $script Javascript string
     * @return Taconite Taconite document instance
     */
    public function js($script)
    {
        if ($this->compress_js && class_exists('JSMin', true))
        {
            $script = \JSMin::minify($script);
        }
        $taco = '<eval><![CDATA[' . $script . ']]></eval>';
        $this->rawCommand($taco);
        return $this;
    }
    /**
     * Adds an element command, as described in the Taconite plugin docs.
     *
     * @param string $method A JQuery method
     * @param string $selector Any valid JQuery selector
     * @param string $content XHTML content
     * @return Taconite Taconite document instance
     */
    public function elementCommand($method, $selector, $content)
    {
        $taco = '<' . $method . ' select="' . $selector . '">' . $content
            . '</' . $method . '>';
        $this->rawCommand($taco);
        return $this;
    }
    /**
     * Adds a raw Taconite command to the document
     *
     * @param string $command A Taconite command
     * @return Taconite Taconite document instance
     */
    public function rawCommand($command)
    {
        $this->content.= $command;
        return $this;
    }
    /**
     * Returns the command document string
     *
     * This method does not perform any syntax check !
     *
     * @return string
     */
    public function __toString()
    {
        //if(function_exists('mb_convert_encoding'))
        //{
            //return (mb_convert_encoding('<taconite>' . $this->content . '</taconite>', 'UTF-8'));
        //}
        return '<taconite>' . $this->content . '</taconite>';
    }
    /**
     * Renders the Taconite command document.
     *
     * This performs basic syntax check with the DOMDocument extension.
     *
     * @param bool $die Wether to die after rendering or not
     * @exception
     */
    public function render($die = true)
    {
        $trans = array(
            '&nbsp;' => '&#160;'
        );
        $this->content = strtr($this->content, $trans);
        if ($this->isValid())
        {
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            header ("Pragma: no-cache");
            header("Expires: -1");
            header('Content-type: text/xml; charset=UTF-8');
            echo $this->__toString();
            if ($die)
            {
                die;
            }
        }
        else
        {
            $taconite_exception = new \shozu\Taconite\Exception('Document is not valid XML.');
            $taconite_exception->setXML($this->content);
            throw $taconite_exception;
        }
    }
    private function camelize($property)
    {
        $property_chops = explode('-', $property);
        $chops_size = count($property_chops);
        if ($chops_size > 1)
        {
            for ($i = 1;$i < $chops_size;$i++)
            {
                $property_chops[$i] = ucfirst(trim($property_chops[$i]));
            }
            $property = implode('', $property_chops);
        }
        return $property;
    }
    private function isValid()
    {
        $string = $this->__toString();
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->loadXML($string);
        $errors = libxml_get_errors();
        if (empty($errors))
        {
            return true;
        }
        return false;
    }

    /**
     * Javascript alert shortcut
     *
     * @param string
     * @return Taconite Taconite document instance
     */
    public function alert($string)
    {
        $this->js('alert(' . $this->escapeJSArgs($string) . ');');
        return $this;
    }

    /**
     * Javascript status bar shortcut
     *
     * @param string
     * @return Taconite Taconite document instance
     */
    public function status($string)
    {
        $this->js('window.status = ' . $this->escapeJSArgs($string) . ';');
        return $this;
    }

    public function escapeJSArgs($string, $string_delimiter = '"', $add_delimiters = true)
    {
        if ($string_delimiter == '"')
        {
            $string = str_replace(array(
                "\r\n",
                "\n",
                '"'
                ) , array(
                '\n',
                '\n',
                '\"'
                ) , $string);
        }
        elseif ($string_delimiter == "'")
        {
            $string = str_replace(array(
                "\r\n",
                "\n",
                "'"
                ) , array(
                '\n',
                '\n',
                "\'"
                ) , $string);
        }
        else
        {
            trigger_error('delimiter should be single or double quote.', E_USER_ERROR);
        }
        if ($add_delimiters)
        {
            return $string_delimiter . $string . $string_delimiter;
        }
        return $string;
    }
}
