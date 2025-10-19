<?php

namespace AppBuilder\Util;

use ZipArchive;

/**
 * Class DocxToHtmlConverter
 * 
 * A utility class to convert Microsoft Word (.docx) files to HTML.
 * It handles basic text formatting, styles, lists, and embedded images.
 */
class DocxToHtmlConverter {

    /**
     * @var string|false The raw XML content of the main document part (word/document.xml).
     */
    private $fileData = false;
    /**
     * @var array Stores any errors that occur during file processing.
     */
    private $errors = array();
    /**
     * @var array Stores parsed styles from word/styles.xml.
     */
    private $styles = array();
    /**
     * @var array Stores parsed list (numbering) styles from word/numbering.xml.
     */
    private $listStyles = array();
    /**
     * @var array Stores relationships for drawings (images) from word/_rels/document.xml.rels.
     */
    private $drawingRels = array();
    /**
     * @var array Stores Base64 encoded image data, keyed by their relationship ID.
     */
    private $images = array();
    /**
     * @var string Stores the document title from docProps/core.xml.
     */
    private $documentTitle = '';

    /**
     * DocxToHtmlConverter constructor.
     * 
     * @param string|null $file Optional path to a .docx file to load upon instantiation.
     */
    public function __construct($file = null) {
        if ($file) {
            $this->setFile($file);
        }
    }

    /**
     * Loads and parses the contents of a .docx file.
     *
     * @param string $file Path to the .docx file.
     * @return string|null The XML content of word/document.xml, or null on failure.
     */
    private function load($file) {
        if (file_exists($file)) {
            $zip = new ZipArchive();
            $openedZip = $zip->open($file);
            if ($openedZip === true) {
                //attempt to load styles:
                if (($styleIndex = $zip->locateName('word/styles.xml')) !== false) {
                    $stylesXml = $zip->getFromIndex($styleIndex);
                    $xml = simplexml_load_string($stylesXml);
                    $namespaces = $xml->getNamespaces(true);

                    $children = $xml->children($namespaces['w']);

                    foreach ($children->style as $s) {
                        $attr = $s->attributes('w', true);
                        if (isset($attr['styleId'])) {
                            $tags = array();
                            $attrs = array();
                            foreach (get_object_vars($s->rPr) as $tag => $style) {
                                $att = $style->attributes('w', true);
                                switch ($tag) {
                                    case "b":
                                        $tags[] = 'strong';
                                        break;
                                    case "i":
                                        $tags[] = 'em';
                                        break;
                                    case "color":
                                        //echo (String) $att['val'];
                                        $attrs[] = 'color:#' . $att['val'];
                                        break;
                                    case "sz":
                                        $attrs[] = 'font-size:' . $att['val'] . 'px';
                                        break;
                                }
                            }
                            $styles[(String)$attr['styleId']] = array('tags' => $tags, 'attrs' => $attrs);
                        }
                    }
                    $this->styles = $styles;
                }

                // Attempt to load numbering definitions
                if (($numberingIndex = $zip->locateName('word/numbering.xml')) !== false) {
                    $numberingXml = $zip->getFromIndex($numberingIndex);
                    $numbering = simplexml_load_string($numberingXml);
                    $namespaces = $numbering->getNamespaces(true);

                    // First, parse abstract numbering definitions
                    $abstractNums = [];
                    foreach ($numbering->children($namespaces['w'])->abstractNum as $abstractNum) {
                        $abstractNumId = (string)$abstractNum->attributes('w', true)['abstractNumId'];
                        $lvl = $abstractNum->children($namespaces['w'])->lvl;
                        if ($lvl) {
                            $numFmt = $lvl->children($namespaces['w'])->numFmt;
                            $numFmtVal = (string)$numFmt->attributes('w', true)['val'];
                            $abstractNums[$abstractNumId] = ($numFmtVal == 'bullet') ? 'ul' : 'ol';
                        }
                    }

                    // Then, map concrete numbering instances to their abstract definitions
                    foreach ($numbering->children($namespaces['w'])->num as $num) {
                        $numId = (string)$num->attributes('w', true)['numId'];
                        $abstractNumId = (string)$num->children($namespaces['w'])->abstractNumId->attributes('w', true)['val'];
                        if (isset($abstractNums[$abstractNumId])) {
                            $this->listStyles[$numId] = $abstractNums[$abstractNumId];
                        }
                    }
                }

                // Attempt to load drawing relationships
                if (($relsIndex = $zip->locateName('word/_rels/document.xml.rels')) !== false) {
                    $relsXml = $zip->getFromIndex($relsIndex);
                    $rels = simplexml_load_string($relsXml);
                    foreach ($rels->Relationship as $rel) {
                        $relAttrs = $rel->attributes();
                        if (strpos($relAttrs['Type'], 'image') !== false) {
                            $this->drawingRels[(string)$relAttrs['Id']] = (string)$relAttrs['Target'];
                        }
                    }
                }

                // Extract images
                foreach ($this->drawingRels as $rId => $imagePath) {
                    $imagePathAbs = 'word/' . $imagePath;
                    if (($imageIndex = $zip->locateName($imagePathAbs)) !== false) {
                        $imageData = $zip->getFromIndex($imageIndex);
                        $imageInfo = getimagesizefromstring($imageData);
                        if ($imageInfo) {
                            $base64 = base64_encode($imageData);
                            $this->images[$rId] = 'data:' . $imageInfo['mime'] . ';base64,' . $base64;
                        }
                    }
                }

                // Attempt to load core properties (for document title)
                if (($corePropsIndex = $zip->locateName('docProps/core.xml')) !== false) {
                    $corePropsXml = $zip->getFromIndex($corePropsIndex);
                    $coreProps = simplexml_load_string($corePropsXml);
                    $namespaces = $coreProps->getNamespaces(true);
                    if (isset($namespaces['dc'])) {
                        $dc = $coreProps->children($namespaces['dc']);
                        if (isset($dc->title)) {
                            $this->documentTitle = (string)$dc->title;
                        }
                    }
                }


                if (($index = $zip->locateName('word/document.xml')) !== false) {
                    // If found, read it to the string
                    $data = $zip->getFromIndex($index);
                    // Close archive file
                    $zip->close();
                    return $data;
                }
                $zip->close();
            } else {
                switch($openedZip) {
                    case ZipArchive::ER_EXISTS:
                        $this->errors[] = 'File exists.';
                        break;
                    case ZipArchive::ER_INCONS:
                        $this->errors[] = 'Inconsistent zip file.';
                        break;
                    case ZipArchive::ER_MEMORY:
                        $this->errors[] = 'Malloc failure.';
                        break;
                    case ZipArchive::ER_NOENT:
                        $this->errors[] = 'No such file.';
                        break;
                    case ZipArchive::ER_NOZIP:
                        $this->errors[] = 'File is not a zip archive.';
                        break;
                    case ZipArchive::ER_OPEN:
                        $this->errors[] = 'Could not open file.';
                        break;
                    case ZipArchive::ER_READ:
                        $this->errors[] = 'Read error.';
                        break;
                    case ZipArchive::ER_SEEK:
                        $this->errors[] = 'Seek error.';
                        break;
                }
            }
        } else {
            $this->errors[] = 'File does not exist.';
        }
    }

    /**
     * Sets the .docx file to be converted.
     *
     * @param string $path The file path to the .docx document.
     */
    public function setFile($path) {
        $this->fileData = $this->load($path);
    }

    public function toPlainText() {
        if ($this->fileData) {
            return strip_tags($this->fileData);
        } else {
            return false;
        }
    }

    /**
     * Converts the loaded .docx file content to HTML.
     *
     * @return string|false The generated HTML string, or false if file data is not loaded.
     */
    public function toHtml() {
        if ($this->fileData) {
            $xml = simplexml_load_string($this->fileData);
            $namespaces = $xml->getNamespaces(true);

            $children = $xml->children($namespaces['w']);

            // Get page size and margins
            $bodyStyle = '';
            $sectPr = $children->body->sectPr;
            if ($sectPr) {
                $pgSz = $sectPr->pgSz->attributes('w', true);
                $pgMar = $sectPr->pgMar->attributes('w', true);

                // Default values in twips (A4 width, 1-inch margins)
                $pageWidthTwips = 11906;
                $leftMarginTwips = 1440;
                $rightMarginTwips = 1440;

                if (isset($pgSz['w'])) {
                    $pageWidthTwips = (int)$pgSz['w'];
                }
                if (isset($pgMar['left'])) {
                    $leftMarginTwips = (int)$pgMar['left'];
                }
                if (isset($pgMar['right'])) {
                    $rightMarginTwips = (int)$pgMar['right'];
                }
                $contentWidthPt = ($pageWidthTwips - $leftMarginTwips - $rightMarginTwips) / 20;
                $bodyStyle = " style=\"width:{$contentWidthPt}pt;margin:auto;padding:72pt;\"";
            }

            $title = htmlspecialchars($this->documentTitle, ENT_QUOTES, 'UTF-8');
            $html = '<!doctype html><html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" /><title>' . $title . '</title><style>span.block { display: block; }</style></head><body'.$bodyStyle.'>';

            $currentListNumId = null;
            $listCounters = []; // Tracks the item count for each list to handle restarts

            // Process each paragraph in the document body
            foreach ($children->body->p as $p) {
                // Check if the current paragraph is a list item
                $isListItem = isset($p->pPr->numPr);

                // Initialize styles and tags for the paragraph
                $style = '';
                
                $startTags = array();
                $startAttrs = array();
                
                if($p->pPr->pStyle) {                    
                    $objectAttrs = $p->pPr->pStyle->attributes('w',true);
                    $objectStyle = (String) $objectAttrs['val'];
                    if(isset($this->styles[$objectStyle])) {
                        $startTags = $this->styles[$objectStyle]['tags'];
                        $startAttrs = $this->styles[$objectStyle]['attrs'];
                    }
                }
                
                // Apply spacing properties if they exist
                if ($p->pPr->spacing) {
                    $att = $p->pPr->spacing->attributes('w', true);
                    if (isset($att['before'])) {
                        $style.='padding-top:' . ($att['before'] / 10) . 'px;';
                    }
                    if (isset($att['after'])) {
                        $style.='padding-bottom:' . ($att['after'] / 10) . 'px;';
                    }
                }

                // Get the numbering ID if it's a list item
                $numId = null;
                if ($isListItem) {
                    $numIdAttr = $p->pPr->numPr->numId->attributes('w', true);
                    $numId = (int)$numIdAttr['val'];
                }

                // Handle list opening and closing tags
                if ($isListItem && $numId !== $currentListNumId) { // A new list starts
                    $listType = isset($this->listStyles[$numId]) ? $this->listStyles[$numId] : 'ol';
                    if ($currentListNumId !== null) {
                        $prevListType = isset($this->listStyles[$currentListNumId]) ? $this->listStyles[$currentListNumId] : 'ol';
                        $html .= '</' . $prevListType . '>'; // Close previous list
                    }
                    $startValue = '';
                    if ($listType == 'ol' && isset($listCounters[$numId])) {
                        $startValue = ' start="' . ($listCounters[$numId] + 1) . '"';
                    }
                    $html .= '<' . $listType . $startValue . '>';
                } elseif (!$isListItem && $currentListNumId !== null) { // A list ends
                    $listType = isset($this->listStyles[$currentListNumId]) ? $this->listStyles[$currentListNumId] : 'ol';
                    $html .= '</' . $listType . '>';
                }
                $currentListNumId = $numId;

                // Open the paragraph or list item tag
                if ($isListItem) {
                    // Increment and store the counter for the current list
                    $listCounters[$numId] = isset($listCounters[$numId]) ? $listCounters[$numId] + 1 : 1;
                    $html .= '<li style="' . $style . '">';
                } else {
                    $html .= '<span class="block" style="' . $style . '">';
                }

                // Iterate through all children of the paragraph node
                foreach ($p->children($namespaces['w']) as $node) {
                    $nodeName = $node->getName();

                    if ($nodeName == 'r') { // It's a run, process text and styles
                        // A 'run' is a contiguous region of text with the same properties.
                        $part = $node;
                        $tags = $startTags;
                        $attrs = $startAttrs;

                        foreach (get_object_vars($part->rPr) as $tag => $style) {
                            $att = $style->attributes('w', true);
                            switch ($tag) {
                                case "b":
                                    $tags[] = 'strong';
                                    break;
                                case "i":
                                    $tags[] = 'em';
                                    break;
                                case "color":
                                    $attrs[] = 'color:#' . $att['val'];
                                    break;
                                case "sz":
                                    $attrs[] = 'font-size:' . $att['val'] . 'px';
                                    break;
                            }
                        }

                        $openTags = '';
                        $closeTags = '';
                        foreach ($tags as $tag) {
                            $openTags .= '<' . $tag . '>';
                            $closeTags .= '</' . $tag . '>';
                        }

                        // A run can contain multiple text and break elements
                        foreach ($part->children($namespaces['w']) as $child) {
                            if ($child->getName() == 't') {
                                $html .= '<span style="' . implode(';', $attrs) . '">' . $openTags . $child . $closeTags . '</span>';
                            } elseif ($child->getName() == 'br') {
                                $html .= '<br />';
                            } elseif ($child->getName() == 'drawing') {
                                // Register drawing namespaces
                                $child->registerXPathNamespace('pic', 'http://schemas.openxmlformats.org/drawingml/2006/picture');
                                $child->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
                                $child->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        
                                $blip = $child->xpath('.//a:blip/@r:embed');
                                if ($blip) {
                                    $rId = (string)$blip[0];
                                    if (isset($this->images[$rId])) {
                                        $html .= '<img src="' . $this->images[$rId] . '" style="max-width:100%;" />';
                                    }
                                }
                            }
                        }
                    }
                }

                // Close the paragraph or list item tag
                if ($isListItem) {
                    $html .= '</li>';
                } else {
                    $html .= "</span>";
                }
            }
            
            // Close any remaining open list
            if ($currentListNumId !== null) {
                $listType = isset($this->listStyles[$currentListNumId]) ? $this->listStyles[$currentListNumId] : 'ol';
                $html .= '</' . $listType . '>';
            }
            //Trying to weed out non-utf8 stuff from the file:
            $regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3 
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;
            preg_replace($regex, '$1', $html);

            return $html . '</body></html>';
            exit();
        }
    }

    /**
     * Returns an array of errors that occurred during processing.
     *
     * @return array The list of error messages.
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Placeholder for a method to get styles. Currently not implemented.
     */
    private function getStyles() {
        
    }

}
