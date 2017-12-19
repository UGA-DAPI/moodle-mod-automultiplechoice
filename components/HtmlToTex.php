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

    private $tmpDir = '/tmp';

    public function __construct($configfile = '') {
        if (!$configfile) {
            $configfile = __DIR__ . '/htmltotex.json';
        }
        $this->mapping = json_decode(file_get_contents($configfile), true);
        if (empty($this->mapping)) {
            die("Empty config!");
        }
    }

    /**
     * @param string $dir
     * @return \HtmlToTex
     */
    public function setTmpDir($dir) {
        $this->tmpDir = rtrim($dir, '/');
        return $this;
    }

    /**
     * @param string $html Partial HTML document.
     * @return \HtmlToTex
     */
    public function loadFragment($html) {
        $this->dom = new DOMDocument();
        $this->dom->loadHTML(
            '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>'
                . $html
                . '</body></html>',
            $this->getOptions()
        );
        return $this;
    }

    /**
     * @param string $html Whole HTML document, starting at the doctype or the html tag.
     * @return \HtmlToTex
     */
    public function loadString($html) {
        $this->dom = new DOMDocument();
        $this->dom->loadHTML($html, $this->getOptions());
        return $this;
    }

    /**
     * @param string $filename
     * @return \HtmlToTex
     */
    public function loadFile($filename) {
        $this->dom = new DOMDocument();
        $this->dom->loadHTMLFile($filename, $this->getOptions());
        return $this;
    }

    /**
     * Convert the HTML into a TeX string.
     *
     * @return string
     */
    public function toTex() {
        $tex = '';
        foreach ($this->dom->childNodes as $node) {
            $tex .= $this->nodeToTex($node);
        }
        return $tex;
    }

    /**
     * @param DOMNode $node
     * @return string
     */
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

    /**
     * @param DOMElement $e
     * @return string
     */
    protected function elementToTex(DOMElement $e) {
        $wrapper = null;
        if ($e->hasAttribute('class')) {
            $classes = preg_split('/\s+/', $e->getAttribute('class'));
            foreach ($classes as $class) {
                if (isset($this->mapping[$e->nodeName . '.' . $class])) {
                    $wrapper = $this->mappingToTex($this->mapping[$e->nodeName . '.' . $class], $e);
                    break;
                }
            }
        }
        if (!isset($wrapper)) {
            if (isset($this->mapping[$e->nodeName])) {
                $wrapper = $this->mappingToTex($this->mapping[$e->nodeName], $e);
            } else {
                return "\n% Unknown tag {$e->nodeName}\n";
            }
        }
        if ($wrapper->hide) {
            return "";
        }
        $tex = $wrapper->start;
        if ($wrapper->recursive) {
            foreach ($e->childNodes as $e) {
                $tex .= $this->nodeToTex($e);
            }
        } else {
            $tex .= $wrapper->content;
        }
        $tex .= $wrapper->end;
        return $tex;
    }

    /**
     * Convert a simple HTML string (no tag, no entity) into a TeX string.
     *
     * @param string $htmlText
     * @return string
     */
    protected function textToTex($htmlText) {
        return str_replace(
            ['{',  '}',  '\\',                '%'  , '&',  '~',  '[',  ']',  '_',  '^',    '$',  '#',  "\n","\r\n","\r"],
            ['\{', '\}', '\\textbackslash{}', '\%' , '\&', '\~', '[', ']', '\_', '\^{}', '\$', '\#', " "," "," "],
            $htmlText
        );
    }

    /**
     * @param array $mapping
     * @param DOMElement $element
     * @return ConvertedTag
     */
    protected function mappingToTex($mapping, $element) {
        if (isset($mapping['type'])) {
            if ($mapping['type'] === 'hide') {
               $res = new ConvertedTag;
               $res->hide = true;
               return $res;
            } else if ($mapping['type'] === 'skip') {
                return ConvertedTag::wrap("", "");
            } else if ($mapping['type'] === 'custom') {
                if (isset($mapping['method'])) {
                    $function = (string) $mapping['method'];
                } else {
                    $function = "tag{$element->nodeName}ToTex";
                }
                return $this->$function($element);
            } else if (isset($mapping['tex'])) {
                if ($mapping['type'] === 'macro') {
                    return ConvertedTag::wrap('\\' . $mapping['tex'] . '{', '}');
                } else if ($mapping['type'] === 'env') {
                    return ConvertedTag::wrap('\\begin{' . $mapping['tex'] . '}', '\end{' . $mapping['tex'] . '}');
                } else if ($mapping['type'] === 'raw' && is_array($mapping['tex']) && count($mapping['tex']) === 2) {
                    return ConvertedTag::wrap($mapping['tex'][0], $mapping['tex'][1]);
                }
            }
        }
        var_dump($mapping); die("Unknown HtmlToTex mapping.");
    }

    /**
     * @param DOMElement $e
     * @return ConvertedTag
     */
    protected function tagImgToTex(DOMElement $e) {
        /**
         * @todo read src attr, save img into a local path (object attr), then use includegraphicx (which must be loaded).
         */
        $res = new ConvertedTag;
        $res->recursive = false;
        if (!$e->hasAttribute('src')) {
            $res->hide = true;
            return $res;
        }
        $imgSrc = $e->getAttribute('src');
        $imgTmpname = $this->tmpDir . '/' . md5($imgSrc).'.png';

        $curl = curl_init($imgSrc);
        $fp = fopen($imgTmpname, "w");
        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_exec($curl);
        $error = curl_error($curl);
        if ($error) {
            $res->content = '\textbf{Could not download the image.}';
            return $res;
        }
        curl_close($curl);
        fclose($fp);

        $res->content = '\includegraphics[scale=.75]{' . $imgTmpname . '}';
        return $res;
    }

    /**
     * @param DOMElement $e
     * @return ConvertedTag
     */
    protected function tagTableToTex(DOMElement $e) {
        /**
         * @todo read alignments
         */
        $res = new ConvertedTag;
        $res->recursive = false;

        $cols = 0;
        $count = true;
        $xpath = new DOMXpath($this->dom);
        $rows = [];
        foreach ($xpath->query('./thead/tr|./tbody/tr|./tr', $e) as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $cells = [];
                /* @var $node DOMElement */
                foreach ($node->childNodes as $td) {
                    if ($node->nodeType === XML_ELEMENT_NODE) {
                        $tagname = strtolower($td->nodeName);
                        if ($tagname === 'th' || $tagname === 'td') {
                            $cells[] = $this->nodeToTeX($td);
                            if ($count) {
                                if ($td->hasAttribute('colspan')) {
                                    $cols += $td->getAttribute('colspan');
                                } else {
                                    $cols++;
                                }
                            }
                        }
                    }
                }
                $count = false;
                $rows[] = join(' & ', $cells);
            }
        }
        if (!$rows) {
            $res->hide = true;
            return $res;
        }
        $columns = array_fill(0, $cols, 'c');
        $res->start = '\begin{tabular}{|' . join('|', $columns) . '|}\hline ';
        $res->content = join(' \\\\ \hline ', $rows);
        $res->end = ' \\\\ \hline\end{tabular}';
        
        return $res;
    }

    /**
     * @param DOMElement $e
     * @return ConvertedTag
     */
    protected function embeddedTex(DOMElement $e) {
        $res = new ConvertedTag();
        $res->recursive = false;
        $res->content = $e->textContent;
        return $res;
    }

    /**
     * Build the options parameter that libxml uses.
     *
     * @return integer
     */
    protected function getOptions() {
        $options = LIBXML_COMPACT | LIBXML_HTML_NOIMPLIED | LIBXML_NOBLANKS | LIBXML_NOCDATA | LIBXML_NOENT | LIBXML_NONET;
        if ($this->quiet) {
            $options = $options | LIBXML_NOERROR | LIBXML_NOWARNING;
        }
        return $options;
    }
}

class ConvertedTag
{
    public $hide = false;
    public $recursive = true;
    public $content = '';
    public $start;
    public $end;

    public static function wrap($start, $end)
    {
        $new = new self;
        $new->start = $start;
        $new->end = $end;
        return $new;
    }
}
