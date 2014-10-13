<?php

/**
 * @license http://www.gnu.org/licenses/gpl-3.0.html  GNU GPL v3
 */

/**
 * Description of HtmlToTex
 *
 * @author François Gannaz <francois.gannaz@silecs.info>
 */
class HtmlToTex
{
    public $quiet = false;

    /**
     * @var DOMDocument
     */
    private $dom;

    private $mapping;

    public function __construct($configfile = '') {
        if (!$configfile) {
            $configfile = __DIR__ . '/htmltotex.json';
        }
        $this->mapping = json_decode(file_get_contents($configfile), true);
        if (empty($this->mapping)) {
            die("Empty config!");
        }
    }

    public function loadFragment($html) {
        $this->dom = new DOMDocument();
        $this->dom->loadHTML(
            '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>'
                . $html
                . '</body></html>',
            $this->getOptions()
        );
    }

    public function loadString($html) {
        $this->dom = new DOMDocument();
        $this->dom->loadHTML($html, $this->getOptions());
    }

    public function loadFile($html) {
        $this->dom = new DOMDocument();
        $this->dom->loadHTMLFile($html, $this->getOptions());
    }

    public function toTex() {
        $tex = '';
        foreach ($this->dom->childNodes as $node) {
            $tex .= $this->nodeToTex($node);
        }
        return $tex;
    }

    protected function nodeToTeX(DOMNode $node) {
        switch ($node->nodeType) {
            case XML_ELEMENT_NODE:
                return $this->elementToTex($node);
            case XML_TEXT_NODE;
                return $this->textToTex($node->nodeValue);
            case XML_DOCUMENT_TYPE_NODE:
                return '';
            default:
                var_dump($node); die("Unexpected node.");
                break;
        }
    }

    protected function elementToTex(DOMElement $e) {
        $found = false;
        if ($e->hasAttribute('class')) {
            $classes = preg_split('/\s+/', $e->getAttribute('class'));
            foreach ($classes as $class) {
                if (isset($this->mapping[$e->nodeName . '.' . $class])) {
                    list ($start, $end) = self::mappingToTex($this->mapping[$e->nodeName], $e);
                    $found = true;
                    break;
                }
            }
        }
        if (!$found) {
            if (isset($this->mapping[$e->nodeName])) {
                list ($start, $end) = self::mappingToTex($this->mapping[$e->nodeName], $e);
                $found = true;
            } else {
                return "\n% Unknown tag {$e->nodeName}\n";
            }
        }
        if ($start === null) {
            return "";
        }
        $tex = $start;
        foreach ($e->childNodes as $e) {
            $tex .= $this->nodeToTex($e);
        }
        $tex .= $end;
        return $tex;
    }

    protected function textToTex($htmlText) {
        return str_replace(
            ['\\',                '%'  , '&',  '~',  '{',  '}',  '[',  ']',  '_',  '^',    '$',  '#',  "\n"],
            ['\\textbackslash{}', '\%' , '\&', '\~', '\{', '\}', '\[', '\]', '\_', '\^{}', '\$', '\#', " "],
            $htmlText
        );
    }

    protected static function mappingToTex($mapping, $element) {
        if (isset($mapping['type'])) {
            if ($mapping['type'] === 'hide') {
               return [null, null];
            } else if ($mapping['type'] === 'skip') {
                return ["", ""];
            } else if ($mapping['type'] === 'custom') {
                if (isset($mapping['function'])) {
                    $function = (string) $mapping['function'];
                } else {
                    $function = "tag{$element->nodeName}ToTex";
                }
                return self::$function($element);
            } else if (isset($mapping['tex'])) {
                if ($mapping['type'] === 'macro') {
                    return ['\\' . $mapping['tex'] . '{', '}'];
                } else if ($mapping['type'] === 'env') {
                    return ['\\begin{' . $mapping['tex'] . '}', '\end{' . $mapping['tex'] . '}'];
                } else if ($mapping['type'] === 'raw' && is_array($mapping['tex']) && count($mapping['tex']) === 2) {
                    return $mapping['tex'];
                }
            }
        }
        var_dump($mapping); die("Unknown HtmlToTex mapping.");
    }

    protected function tagImgToTex(DOMElement $e) {
        /**
         * @todo read src attr, save img into a local path (object attr), then use includegraphicx (which must be loaded).
         */
        return ['', ''];
    }

    protected function tagTableToTex(DOMElement $e) {
        /**
         * @todo count columns, read alignments, and produce the valid parameter for tabular
         */
        return ['begin{tabular}{}', '\end{tabular}'];
    }

    protected function getOptions() {
        $options = LIBXML_COMPACT | LIBXML_HTML_NOIMPLIED | LIBXML_NOBLANKS | LIBXML_NOCDATA | LIBXML_NOENT | LIBXML_NONET;
        if ($this->quiet) {
            $options = $options | LIBXML_NOERROR | LIBXML_NOWARNING;
        }
        return $options;
    }
}
