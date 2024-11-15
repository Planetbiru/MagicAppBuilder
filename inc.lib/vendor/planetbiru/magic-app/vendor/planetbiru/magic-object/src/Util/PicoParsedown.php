<?php

namespace MagicObject\Util;

#
#
# Parsedown
# http://parsedown.org
#
# (c) Emanuil Rusev
# http://erusev.com
#
# For the full license information, view the LICENSE file that was distributed
# with this source code.
#
#

class PicoParsedown //NOSONAR
{
    /**
     * The current version of the parser.
     */
    const VERSION = '1.7.4';

    /**
     * Parse the given Markdown text and convert it to HTML.
     *
     * This method processes the input text by standardizing line breaks,
     * removing surrounding line breaks, and splitting the text into lines,
     * which are then processed to generate the final HTML markup.
     *
     * @param string $text The Markdown text to parse.
     * @return string The converted HTML markup.
     */
    public function text($text)
    {
        # make sure no definitions are set
        $this->definitionData = array();

        # standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        # remove surrounding line breaks
        $text = trim($text, "\n");

        # split text into lines
        $lines = explode("\n", $text);

        # iterate through lines to identify blocks
        $markup = $this->lines($lines);

        # trim line breaks
        $markup = trim($markup, "\n");

        return $markup;
    }

    /**
     * Enable or disable line breaks in the parsed output.
     *
     * @param bool $breaksEnabled Flag indicating whether to enable line breaks.
     * @return $this
     */
    public function setBreaksEnabled($breaksEnabled)
    {
        $this->breaksEnabled = $breaksEnabled;

        return $this;
    }

    /**
     * Flag indicating whether to enable line breaks in the parsed output.
     *
     * @var bool
     */
    protected $breaksEnabled;

    /**
     * Escape HTML markup in the parsed output.
     *
     * @param bool $markupEscaped Flag indicating whether to escape HTML markup.
     * @return $this
     */
    public function setMarkupEscaped($markupEscaped)
    {
        $this->markupEscaped = $markupEscaped;

        return $this;
    }

    /**
     * Flag indicating whether to escape HTML markup in the parsed output.
     *
     * @var bool
     */
    protected $markupEscaped;

    /**
     * Enable or disable automatic linking of URLs in the parsed output.
     *
     * @param bool $urlsLinked Flag indicating whether to link URLs.
     * @return $this
     */
    public function setUrlsLinked($urlsLinked)
    {
        $this->urlsLinked = $urlsLinked;

        return $this;
    }

    /**
     * Flag indicating whether to automatically link URLs in the parsed output.
     *
     * @var bool
     */
    protected $urlsLinked = true;

    /**
     * Enable or disable safe mode for handling links.
     *
     * @param bool $safeMode Flag indicating whether to enable safe mode.
     * @return $this
     */
    public function setSafeMode($safeMode)
    {
        $this->safeMode = (bool) $safeMode;

        return $this;
    }

    /**
     * Flag indicating whether to enable safe mode for handling links.
     *
     * @var bool
     */
    protected $safeMode;

    /**
     * List of allowed protocols for safe mode links.
     *
     * @var array
     */
    protected $safeLinksWhitelist = array(
        'http://',
        'https://',
        'ftp://',
        'ftps://',
        'mailto:',
        'data:image/png;base64,',
        'data:image/gif;base64,',
        'data:image/jpeg;base64,',
        'irc:',
        'ircs:',
        'git:',
        'ssh:',
        'news:',
        'steam:',
    );

    /**
     * Array mapping block markers to their respective types.
     *
     * @var array
     */
    protected $blockTypes = array(
        '#' => array('Header'),
        '*' => array('Rule', 'List'),
        '+' => array('List'),
        '-' => array('SetextHeader', 'Table', 'Rule', 'List'),
        '0' => array('List'),
        '1' => array('List'),
        '2' => array('List'),
        '3' => array('List'),
        '4' => array('List'),
        '5' => array('List'),
        '6' => array('List'),
        '7' => array('List'),
        '8' => array('List'),
        '9' => array('List'),
        ':' => array('Table'),
        '<' => array('Comment', 'Markup'),
        '=' => array('SetextHeader'),
        '>' => array('Quote'),
        '[' => array('Reference'),
        '_' => array('Rule'),
        '`' => array('FencedCode'),
        '|' => array('Table'),
        '~' => array('FencedCode'),
    );

    /**
     * List of block types that are unmarked.
     *
     * @var array
     */
    protected $unmarkedBlockTypes = array(
        'Code',
    );

    /**
     * Process the lines of Markdown text to identify blocks and convert them to HTML.
     *
     * This method analyzes the provided lines, identifies different block types,
     * and constructs the corresponding HTML output.
     *
     * @param array $lines The lines of text to process.
     * @return string The generated HTML markup.
     */
    protected function lines(array $lines) //NOSONAR
    {
        $currentBlock = null;

        foreach ($lines as $line) {
            if (chop($line) === '') {
                if (isset($currentBlock)) {
                    $currentBlock['interrupted'] = true;
                }

                continue;
            }

            if (strpos($line, "\t") !== false) {
                $parts = explode("\t", $line);

                $line = $parts[0];

                unset($parts[0]);

                foreach ($parts as $part) {
                    $shortage = 4 - mb_strlen($line, 'utf-8') % 4;

                    $line .= str_repeat(' ', $shortage);
                    $line .= $part;
                }
            }

            $indent = 0;

            while (isset($line[$indent]) && $line[$indent] === ' ') {
                $indent++;
            }

            $text = $indent > 0 ? substr($line, $indent) : $line;

            # ~

            $line = array('body' => $line, 'indent' => $indent, 'text' => $text);

            # ~

            if (isset($currentBlock['continuable'])) {
                $block = $this->{'block' . $currentBlock['type'] . 'Continue'}($line, $currentBlock);

                if (isset($block)) {
                    $currentBlock = $block;

                    continue;
                } else {
                    if ($this->isBlockCompletable($currentBlock['type'])) {
                        $currentBlock = $this->{'block' . $currentBlock['type'] . 'Complete'}($currentBlock);
                    }
                }
            }

            # ~

            $marker = $text[0];

            # ~

            $blockTypes = $this->unmarkedBlockTypes;

            if (isset($this->blockTypes[$marker])) {
                foreach ($this->blockTypes[$marker] as $blockType) {
                    $blockTypes[] = $blockType;
                }
            }

            #
            # ~

            foreach ($blockTypes as $blockType) {
                $block = $this->{'block' . $blockType}($line, $currentBlock);

                if (isset($block)) {
                    $block['type'] = $blockType;

                    if (!isset($block['identified'])) {
                        $blocks[] = $currentBlock;

                        $block['identified'] = true;
                    }

                    if ($this->isBlockContinuable($blockType)) {
                        $block['continuable'] = true;
                    }

                    $currentBlock = $block;

                    continue 2;
                }
            }

            # ~

            if (isset($currentBlock) && !isset($currentBlock['type']) && !isset($currentBlock['interrupted'])) {
                $currentBlock['element']['text'] .= "\n" . $text;
            } else {
                $blocks[] = $currentBlock;

                $currentBlock = $this->paragraph($line);

                $currentBlock['identified'] = true;
            }
        }

        # ~

        if (isset($currentBlock['continuable']) && $this->isBlockCompletable($currentBlock['type'])) {
            $currentBlock = $this->{'block' . $currentBlock['type'] . 'Complete'}($currentBlock);
        }

        # ~

        $blocks[] = $currentBlock;

        unset($blocks[0]);

        # ~

        $markup = '';

        foreach ($blocks as $block) {
            if (isset($block['hidden'])) {
                continue;
            }

            $markup .= "\n";
            $markup .= isset($block['markup']) ? $block['markup'] : $this->element($block['element']);
        }

        $markup .= "\n";

        # ~

        return $markup;
    }

    /**
     * Check if the specified block type can be continued.
     *
     * @param string $type The type of the block to check.
     * @return bool True if the block type can be continued, false otherwise.
     */
    protected function isBlockContinuable($type)
    {
        return method_exists($this, 'block' . $type . 'Continue');
    }

    /**
     * Check if a block type is completable.
     *
     * This method checks if a method exists for completing a block of the specified type.
     *
     * @param string $type The block type to check.
     * @return bool True if the block type can be completed, false otherwise.
     */
    protected function isBlockCompletable($type)
    {
        return method_exists($this, 'block' . $type . 'Complete');
    }

    /**
     * Parse a code block from a line of text.
     *
     * This method checks if the line represents the start of a code block
     * and constructs the block if valid.
     *
     * @param array $line The line of text to process.
     * @param array|null $block The current block (if any).
     * @return array|null The constructed code block or null if not applicable.
     */
    protected function blockCode($line, $block = null)
    {
        if (isset($block) && !isset($block['type']) && !isset($block['interrupted'])) {
            return null;
        }

        if ($line['indent'] >= 4) {
            $text = substr($line['body'], 4);

            return array(
                'element' => array(
                    'name' => 'pre',
                    'handler' => 'element',
                    'text' => array(
                        'name' => 'code',
                        'text' => $text,
                    ),
                ),
            );

        }
    }

    /**
     * Continue processing a code block.
     *
     * This method appends additional lines to an existing code block.
     *
     * @param array $line The line of text to process.
     * @param array $block The current block being processed.
     * @return array|null The updated code block or null if not applicable.
     */
    protected function blockCodeContinue($line, $block)
    {
        if ($line['indent'] >= 4) {
            if (isset($block['interrupted'])) {
                $block['element']['text']['text'] .= "\n";

                unset($block['interrupted']);
            }

            $block['element']['text']['text'] .= "\n";

            $text = substr($line['body'], 4);

            $block['element']['text']['text'] .= $text;

            return $block;
        }
    }

    /**
     * Complete the code block.
     *
     * This method finalizes the formatting of a completed code block.
     *
     * @param array $block The block to complete.
     * @return array The completed code block.
     */
    protected function blockCodeComplete($block) //NOSONAR
    {
        $text = $block['element']['text']['text'];

        $block['element']['text']['text'] = $text;

        return $block;
    }
    
    /**
     * Continue processing a fenced code block.
     *
     * This method appends additional lines to an existing fenced code block.
     *
     * @param array $line The line of text to process.
     * @param array $block The current fenced code block being processed.
     * @return array|null The updated fenced code block or null if not applicable.
     */
    protected function blockFencedCodeComplete($block) //NOSONAR
    {
        $text = $block['element']['text']['text'];

        $block['element']['text']['text'] = $text;

        return $block;
    }

    /**
     * Parse a comment block from a line of text.
     *
     * This method checks if the line represents a comment block and constructs it if valid.
     *
     * @param array $line The line of text to process.
     * @return array|null The constructed comment block or null if not applicable.
     */
    protected function blockComment($line)
    {
        if ($this->markupEscaped || $this->safeMode) {
            return null;
        }

        if (isset($line['text'][3]) && $line['text'][3] === '-' && $line['text'][2] === '-' && $line['text'][1] === '!') {
            $block = array(
                'markup' => $line['body'],
            );

            if (preg_match('/-->$/', $line['text'])) {
                $block['closed'] = true;
            }

            return $block;
        }
    }

    /**
     * Continue processing a comment block.
     *
     * This method appends additional lines to an existing comment block.
     *
     * @param array $line The line of text to process.
     * @param array $block The current comment block being processed.
     * @return array|null The updated comment block or null if not applicable.
     */
    protected function blockCommentContinue($line, array $block)
    {
        if (isset($block['closed'])) {
            return null;
        }

        $block['markup'] .= "\n" . $line['body'];

        if (preg_match('/-->$/', $line['text'])) {
            $block['closed'] = true;
        }

        return $block;
    }

    /**
     * Parse a fenced code block from a line of text.
     *
     * This method checks if the line represents the start of a fenced code block
     * and constructs the block if valid.
     *
     * @param array $line The line of text to process.
     * @return array|null The constructed fenced code block or null if not applicable.
     */
    protected function blockFencedCode($line)
    {
        if (preg_match('/^[' . $line['text'][0] . ']{3,}[ ]*([^`]+)?[ ]*$/', $line['text'], $matches)) {
            $element = array(
                'name' => 'code',
                'text' => '',
            );

            if (isset($matches[1])) {
                /**
                 * https://www.w3.org/TR/2011/WD-html5-20110525/elements.html#classes
                 * Every HTML element may have a class attribute specified.
                 * The attribute, if specified, must have a value that is a set
                 * of space-separated tokens representing the various classes
                 * that the element belongs to.
                 * [...]
                 * The space characters, for the purposes of this specification,
                 * are U+0020 SPACE, U+0009 CHARACTER TABULATION (tab),
                 * U+000A LINE FEED (LF), U+000C FORM FEED (FF), and
                 * U+000D CARRIAGE RETURN (CR).
                 */
                $language = substr($matches[1], 0, strcspn($matches[1], " \t\n\f\r"));

                $class = 'language-' . $language;

                $element['attributes'] = array(
                    'class' => $class,
                );
            }

            return array(
                'char' => $line['text'][0],
                'element' => array(
                    'name' => 'pre',
                    'handler' => 'element',
                    'text' => $element,
                ),
            );

        }
    }

    /**
     * Continue parsing a fenced code block from subsequent lines.
     *
     * This method handles additional lines that continue the fenced code block.
     * It appends the text to the block until it is completed or interrupted.
     *
     * @param array $line The current line of text being processed.
     * @param array $block The existing fenced code block being built.
     * @return array|null The updated fenced code block or null if it remains incomplete.
     */
    protected function blockFencedCodeContinue($line, $block)
    {
        if (isset($block['complete'])) {
            return null;
        }

        if (isset($block['interrupted'])) {
            $block['element']['text']['text'] .= "\n";

            unset($block['interrupted']);
        }

        if (preg_match('/^' . $block['char'] . '{3,}[ ]*$/', $line['text'])) {
            $block['element']['text']['text'] = substr($block['element']['text']['text'], 1);

            $block['complete'] = true;

            return $block;
        }

        $block['element']['text']['text'] .= "\n" . $line['body'];

        return $block;
    }

    

    /**
     * Complete the fenced code block.
     *
     * This method finalizes the formatting of a completed fenced code block.
     *
     * @param array $block The fenced code block to complete.
     * @return array The completed fenced code block.
     */
    protected function blockHeader($line)
    {
        if (isset($line['text'][1])) {
            $level = 1;

            while (isset($line['text'][$level]) && $line['text'][$level] === '#') {
                $level++;
            }

            if ($level > 6) {
                return null;
            }

            $text = trim($line['text'], '# ');

            return array(
                'element' => array(
                    'name' => 'h' . min(6, $level),
                    'text' => $text,
                    'handler' => 'line',
                ),
            );
        }
    }

    /**
     * Parse a list from a line of text.
     *
     * This method checks if the line represents a list item and constructs the list block if valid.
     *
     * @param array $line The line of text to process.
     * @return array|null The constructed list block or null if not applicable.
     */
    protected function blockList($line)
    {
        list($name, $pattern) = $line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]+[.]');

        if (preg_match('/^(' . $pattern . '[ ]+)(.*)/', $line['text'], $matches)) {
            $block = array(
                'indent' => $line['indent'],
                'pattern' => $pattern,
                'element' => array(
                    'name' => $name,
                    'handler' => 'elements',
                ),
            );

            if ($name === 'ol') {
                $listStart = stristr($matches[0], '.', true);

                if ($listStart !== '1') {
                    $block['element']['attributes'] = array('start' => $listStart);
                }
            }

            $block['li'] = array(
                'name' => 'li',
                'handler' => 'li',
                'text' => array(
                    $matches[2],
                ),
            );

            $block['element']['text'][] = &$block['li'];

            return $block;
        }
    }

    /**
     * Continue processing a list.
     *
     * This method appends additional items to an existing list block.
     *
     * @param array $line The line of text to process.
     * @param array $block The current list block being processed.
     * @return array|null The updated list block or null if not applicable.
     */
    protected function blockListContinue($line, array $block) //NOSONAR
    {
        if ($block['indent'] === $line['indent'] && preg_match('/^' . $block['pattern'] . '(?:[ ]+(.*)|$)/', $line['text'], $matches)) {
            if (isset($block['interrupted'])) {
                $block['li']['text'][] = '';

                $block['loose'] = true;

                unset($block['interrupted']);
            }

            unset($block['li']);

            $text = isset($matches[1]) ? $matches[1] : '';

            $block['li'] = array(
                'name' => 'li',
                'handler' => 'li',
                'text' => array(
                    $text,
                ),
            );

            $block['element']['text'][] = &$block['li'];

            return $block;
        }

        if ($line['text'][0] === '[' && $this->blockReference($line)) {
            return $block;
        }

        if (!isset($block['interrupted'])) {
            $text = preg_replace('/^[ ]{0,4}/', '', $line['body']); //NOSONAR

            $block['li']['text'][] = $text;

            return $block;
        }

        if ($line['indent'] > 0) {
            $block['li']['text'][] = '';

            $text = preg_replace('/^[ ]{0,4}/', '', $line['body']); //NOSONAR

            $block['li']['text'][] = $text;

            unset($block['interrupted']);

            return $block;
        }
    }

    /**
     * Complete the list block.
     *
     * This method finalizes the formatting of a completed list block.
     *
     * @param array $block The list block to complete.
     * @return array The completed list block.
     */
    protected function blockListComplete(array $block)
    {
        if (isset($block['loose'])) {
            foreach ($block['element']['text'] as &$li) {
                if (end($li['text']) !== '') {
                    $li['text'][] = '';
                }
            }
        }

        return $block;
    }

    /**
     * Parse a block quote from a line of text.
     *
     * This method checks if the line starts with a '>' character and captures the quote text.
     *
     * @param array $line The line of text to process.
     * @return array|null The constructed blockquote or null if not applicable.
     */
    protected function blockQuote($line)
    {
        if (preg_match('/^>[ ]?(.*)/', $line['text'], $matches)) //NOSONAR
        {
            return array(
                'element' => array(
                    'name' => 'blockquote',
                    'handler' => 'lines',
                    'text' => (array) $matches[1],
                ),
            );
        }
        return null;
    }

    /**
     * Continue processing a block quote.
     *
     * This method appends additional lines to an existing block quote if they start with a '>'.
     *
     * @param array $line The line of text to process.
     * @param array $block The current block quote being processed.
     * @return array|null The updated block quote or null if not applicable.
     */
    protected function blockQuoteContinue($line, array $block)
    {
        if ($line['text'][0] === '>' && preg_match('/^>[ ]?(.*)/', $line['text'], $matches)) //NOSONAR
        {
            if (isset($block['interrupted'])) {
                $block['element']['text'][] = '';

                unset($block['interrupted']);
            }

            $block['element']['text'][] = $matches[1];

            return $block;
        }

        if (!isset($block['interrupted'])) {
            $block['element']['text'][] = $line['text'];

            return $block;
        }
    }

    /**
     * Parse a horizontal rule from a line of text.
     *
     * This method checks for repeated characters to determine if a horizontal rule is present.
     *
     * @param array $line The line of text to process.
     * @return array|null The constructed horizontal rule or null if not applicable.
     */
    protected function blockRule($line)
    {
        if (preg_match('/^([' . $line['text'][0] . '])([ ]*\1){2,}[ ]*$/', $line['text'])) {
            return array(
                'element' => array(
                    'name' => 'hr'
                ),
            );

        }
    }

    /**
     * Parse a Setext header from a line of text.
     *
     * This method constructs a header based on the presence of underlining characters.
     *
     * @param array $line The line of text to process.
     * @param array|null $block The current block being processed.
     * @return array|null The updated block or null if not applicable.
     */
    protected function blockSetextHeader($line, array $block = null)
    {
        if (!isset($block) || isset($block['type']) || isset($block['interrupted'])) {
            return null;
        }

        if (chop($line['text'], $line['text'][0]) === '') {
            $block['element']['name'] = $line['text'][0] === '=' ? 'h1' : 'h2';

            return $block;
        }
    }

    /**
     * Parse custom markup from a line of text.
     *
     * This method constructs a block based on the presence of HTML-like tags.
     *
     * @param array $line The line of text to process.
     * @return array|null The constructed markup block or null if not applicable.
     */
    protected function blockMarkup($line) //NOSONAR
    {
        if ($this->markupEscaped || $this->safeMode) {
            return null;
        }

        if (preg_match('/^<(\w[\w-]*)(?:[ ]*' . $this->regexHtmlAttribute . ')*[ ]*(\/)?>/', $line['text'], $matches)) {
            $element = strtolower($matches[1]);

            if (in_array($element, $this->textLevelElements)) {
                return null;
            }

            $block = array(
                'name' => $matches[1],
                'depth' => 0,
                'markup' => $line['text'],
            );

            $length = strlen($matches[0]);

            $remainder = substr($line['text'], $length);

            if (trim($remainder) === '') {
                if (isset($matches[2]) || in_array($matches[1], $this->voidElements)) {
                    $block['closed'] = true;

                    $block['void'] = true;
                }
            } else {
                if (isset($matches[2]) || in_array($matches[1], $this->voidElements)) {
                    return null;
                }

                if (preg_match('/<\/' . $matches[1] . '>[ ]*$/i', $remainder)) {
                    $block['closed'] = true;
                }
            }

            return $block;
        }
    }

    /**
     * Parse a reference link definition from a line of text.
     *
     * This method constructs a reference link that can be used later in the document.
     *
     * @param array $line The line of text to process.
     * @return array|null The constructed reference or null if not applicable.
     */
    protected function blockMarkupContinue($line, array $block)
    {
        if (isset($block['closed'])) {
            return null;
        }

        if (preg_match('/^<' . $block['name'] . '(?:[ ]*' . $this->regexHtmlAttribute . ')*[ ]*>/i', $line['text'])) # open
        {
            $block['depth']++;
        }

        if (preg_match('/(.*?)<\/' . $block['name'] . '>[ ]*$/i', $line['text'], $matches)) # close
        {
            if ($block['depth'] > 0) {
                $block['depth']--;
            } else {
                $block['closed'] = true;
            }
        }

        if (isset($block['interrupted'])) {
            $block['markup'] .= "\n";

            unset($block['interrupted']);
        }

        $block['markup'] .= "\n" . $line['body'];

        return $block;
    }

    /**
     * Parse a reference link definition from a line of text.
     *
     * This method constructs a reference link that can be used later in the document.
     *
     * @param array $line The line of text to process.
     * @return array|null The constructed reference or null if not applicable.
     */
    protected function blockReference($line)
    {
        if (preg_match('/^\[(.+?)\]:[ ]*<?(\S+?)>?(?:[ ]+["\'(](.+)["\')])?[ ]*$/', $line['text'], $matches)) //NOSONAR
        {
            $id = strtolower($matches[1]);

            $data = array(
                'url' => $matches[2],
                'title' => null,
            );

            if (isset($matches[3])) {
                $data['title'] = $matches[3];
            }

            $this->definitionData['Reference'][$id] = $data;

            return array(
                'hidden' => true,
            );

        }
    }

    /**
     * Parse a table header from a line of text.
     *
     * This method checks if the line represents a table structure and constructs it if valid.
     *
     * @param array $line The line of text to process.
     * @param array|null $block The current block being processed.
     * @return array|null The constructed table block or null if not applicable.
     */
    protected function blockTable($line, array $block = null) //NOSONAR
    {
        if (!isset($block) || isset($block['type']) || isset($block['interrupted'])) {
            return null;
        }

        if (strpos($block['element']['text'], '|') !== false && chop($line['text'], ' -:|') === '') {
            $alignments = array();

            $divider = $line['text'];

            $divider = trim($divider);
            $divider = trim($divider, '|');

            $dividerCells = explode('|', $divider);

            foreach ($dividerCells as $dividerCell) {
                $dividerCell = trim($dividerCell);

                if ($dividerCell === '') {
                    continue;
                }

                $alignment = null;

                if ($dividerCell[0] === ':') {
                    $alignment = 'left';
                }

                if (substr($dividerCell, -1) === ':') {
                    $alignment = $alignment === 'left' ? 'center' : 'right';
                }

                $alignments[] = $alignment;
            }

            # ~

            $headerElements = array();

            $header = $block['element']['text'];

            $header = trim($header);
            $header = trim($header, '|');

            $headerCells = explode('|', $header);

            foreach ($headerCells as $index => $headerCell) {
                $headerCell = trim($headerCell);

                $headerElement = array(
                    'name' => 'th',
                    'text' => $headerCell,
                    'handler' => 'line',
                );

                if (isset($alignments[$index])) {
                    $alignment = $alignments[$index];

                    $headerElement['attributes'] = array(
                        'style' => 'text-align: ' . $alignment . ';',
                    );
                }

                $headerElements[] = $headerElement;
            }

            # ~

            $block = array(
                'alignments' => $alignments,
                'identified' => true,
                'element' => array(
                    'name' => 'table',
                    'handler' => 'elements',
                ),
            );

            $block['element']['text'][] = array(
                'name' => 'thead',
                'handler' => 'elements',
            );

            $block['element']['text'][] = array(
                'name' => 'tbody',
                'handler' => 'elements',
                'text' => array(),
            );

            $block['element']['text'][0]['text'][] = array(
                'name' => 'tr',
                'handler' => 'elements',
                'text' => $headerElements,
            );

            return $block;
        }
    }

    /**
     * Continue processing a table.
     *
     * This method appends additional rows to an existing table block.
     *
     * @param array $line The line of text to process.
     * @param array $block The current table block being processed.
     * @return array|null The updated table block or null if not applicable.
     */
    protected function blockTableContinue($line, $block) //NOSONAR
    {
        if (isset($block['interrupted'])) {
            return null;
        }

        if ($line['text'][0] === '|' || strpos($line['text'], '|')) {
            $elements = array();

            $row = $line['text'];

            $row = trim($row);
            $row = trim($row, '|');

            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]+`|`)+/', $row, $matches);

            foreach ($matches[0] as $index => $cell) {
                $cell = trim($cell);

                $element = array(
                    'name' => 'td',
                    'handler' => 'line',
                    'text' => $cell,
                );

                if (isset($block['alignments'][$index])) {
                    $element['attributes'] = array(
                        'style' => 'text-align: ' . $block['alignments'][$index] . ';',
                    );
                }

                $elements[] = $element;
            }

            $element = array(
                'name' => 'tr',
                'handler' => 'elements',
                'text' => $elements,
            );

            $block['element']['text'][1]['text'][] = $element;

            return $block;
        }
    }

    /**
     * Parse a paragraph from a line of text.
     *
     * This method constructs a paragraph block from the provided text.
     *
     * @param array $line The line of text to process.
     * @return array The constructed paragraph block.
     */
    protected function paragraph($line)
    {
        return array(
            'element' => array(
                'name' => 'p',
                'text' => $line['text'],
                'handler' => 'line',
            ),
        );

    }

    /**
     * Process inline elements within a line of text.
     *
     * This method identifies and constructs inline elements based on their markers.
     *
     * @param string $text The line of text to process.
     * @param array $nonNestables An array of non-nestable inline types.
     * @return string The processed markup with inline elements.
     */
    protected $inlineTypes = array(
        '"' => array('SpecialCharacter'),
        '!' => array('Image'),
        '&' => array('SpecialCharacter'),
        '*' => array('Emphasis'),
        ':' => array('Url'),
        '<' => array('UrlTag', 'EmailTag', 'Markup', 'SpecialCharacter'),
        '>' => array('SpecialCharacter'),
        '[' => array('Link'),
        '_' => array('Emphasis'),
        '`' => array('Code'),
        '~' => array('Strikethrough'),
        '\\' => array('EscapeSequence'),
    );

    # ~

    protected $inlineMarkerList = '!"*_&[:<>`~\\';

    /**
     * Process a line of text and convert inline elements.
     *
     * This method identifies markers in the text and processes them into HTML-like structures.
     *
     * @param string $text The line of text to process.
     * @param array $nonNestables An array of non-nestable inline types.
     * @return string The processed markup with inline elements.
     */
    public function line($text, $nonNestables = array()) //NOSONAR
    {
        $markup = '';

        # $excerpt is based on the first occurrence of a marker

        while ($excerpt = strpbrk($text, $this->inlineMarkerList)) {
            $marker = $excerpt[0];

            $markerPosition = strpos($text, $marker);

            $excerpt = array('text' => $excerpt, 'context' => $text);

            foreach ($this->inlineTypes[$marker] as $inlineType) {
                # check to see if the current inline type is nestable in the current context

                if (!empty($nonNestables) && in_array($inlineType, $nonNestables)) {
                    continue;
                }

                $inline = $this->{'inline' . $inlineType}($excerpt);

                if (!isset($inline)) {
                    continue;
                }

                # makes sure that the inline belongs to "our" marker

                if (isset($inline['position']) && $inline['position'] > $markerPosition) {
                    continue;
                }

                # sets a default inline position

                if (!isset($inline['position'])) {
                    $inline['position'] = $markerPosition;
                }

                # cause the new element to 'inherit' our non nestables

                foreach ($nonNestables as $non_nestable) {
                    $inline['element']['nonNestables'][] = $non_nestable;
                }

                # the text that comes before the inline
                $unmarkedText = substr($text, 0, $inline['position']);

                # compile the unmarked text
                $markup .= $this->unmarkedText($unmarkedText);

                # compile the inline
                $markup .= isset($inline['markup']) ? $inline['markup'] : $this->element($inline['element']);

                # remove the examined text
                $text = substr($text, $inline['position'] + $inline['extent']);

                continue 2;
            }

            # the marker does not belong to an inline

            $unmarkedText = substr($text, 0, $markerPosition + 1);

            $markup .= $this->unmarkedText($unmarkedText);

            $text = substr($text, $markerPosition + 1);
        }

        $markup .= $this->unmarkedText($text);

        return $markup;
    }

    /**
     * Parse inline code from the excerpt.
     *
     * This method checks for code markers and captures the code content.
     *
     * @param array $excerpt The excerpt to process.
     * @return array|null The inline code element or null if not applicable.
     */
    protected function inlineCode($excerpt)
    {
        $marker = $excerpt['text'][0];

        if (preg_match('/^(' . $marker . '+)[ ]*(.+?)[ ]*(?<!' . $marker . ')\1(?!' . $marker . ')/s', $excerpt['text'], $matches)) {
            $text = $matches[2];
            $text = preg_replace("/[ ]*\n/", ' ', $text); //NOSONAR

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'code',
                    'text' => $text,
                ),
            );
        }
    }

    /**
     * Parse an email tag from the excerpt.
     *
     * This method identifies email addresses formatted within angle brackets.
     *
     * @param array $excerpt The excerpt to process.
     * @return array|null The email link element or null if not applicable.
     */
    protected function inlineEmailTag($excerpt)
    {
        if (strpos($excerpt['text'], '>') !== false && preg_match('/^<((mailto:)?\S+?@\S+?)>/i', $excerpt['text'], $matches)) {
            $url = $matches[1];

            if (!isset($matches[2])) {
                $url = 'mailto:' . $url;
            }

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $matches[1],
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        }
    }

    /**
     * Parse emphasis (bold/italic) from the excerpt.
     *
     * This method checks for markers indicating strong or emphasized text.
     *
     * @param array $excerpt The excerpt to process.
     * @return array|null The emphasis element or null if not applicable.
     */
    protected function inlineEmphasis($excerpt)
    {
        if (!isset($excerpt['text'][1])) {
            return null;
        }

        $marker = $excerpt['text'][0];

        if ($excerpt['text'][1] === $marker && preg_match($this->strongRegex[$marker], $excerpt['text'], $matches)) {
            $emphasis = 'strong';
        } elseif (preg_match($this->emRegex[$marker], $excerpt['text'], $matches)) {
            $emphasis = 'em';
        } else {
            return null;
        }

        return array(
            'extent' => strlen($matches[0]),
            'element' => array(
                'name' => $emphasis,
                'handler' => 'line',
                'text' => $matches[1],
            ),
        );
    }

    /**
     * Handle escape sequences for special characters.
     *
     * This method checks for special characters that are preceded by a backslash.
     *
     * @param array $excerpt The excerpt to process.
     * @return array|null The escaped character element or null if not applicable.
     */
    protected function inlineEscapeSequence($excerpt)
    {
        if (isset($excerpt['text'][1]) && in_array($excerpt['text'][1], $this->specialCharacters)) {
            return array(
                'markup' => $excerpt['text'][1],
                'extent' => 2,
            );
        }
    }

    /**
     * Parse an inline image from the excerpt.
     *
     * This method identifies image links formatted with brackets and captures the source and alt text.
     *
     * @param array $excerpt The excerpt to process.
     * @return array|null The image element or null if not applicable.
     */
    protected function inlineImage($excerpt)
    {
        if (!isset($excerpt['text'][1]) || $excerpt['text'][1] !== '[') {
            return null;
        }

        $excerpt['text'] = substr($excerpt['text'], 1);

        $link = $this->inlineLink($excerpt);

        if ($link === null) {
            return null;
        }

        $inline = array(
            'extent' => $link['extent'] + 1,
            'element' => array(
                'name' => 'img',
                'attributes' => array(
                    'src' => $link['element']['attributes']['href'],
                    'alt' => $link['element']['text'],
                ),
            ),
        );

        $inline['element']['attributes'] += $link['element']['attributes'];

        unset($inline['element']['attributes']['href']);

        return $inline;
    }

    /**
     * Processes inline links in the provided text.
     *
     * @param array $excerpt Contains the text to be processed.
     * @return array|null Returns an array containing the extent of the match and the link element, or null if no match is found.
     */
    protected function inlineLink($excerpt)
    {
        $element = array(
            'name' => 'a',
            'handler' => 'line',
            'nonNestables' => array('Url', 'Link'),
            'text' => null,
            'attributes' => array(
                'href' => null,
                'title' => null,
            ),
        );

        $extent = 0;

        $remainder = $excerpt['text'];

        if (preg_match('/\[((?:[^][]++|(?R))*+)\]/', $remainder, $matches)) //NOSONAR
        {
            $element['text'] = $matches[1];

            $extent += strlen($matches[0]);

            $remainder = substr($remainder, $extent);
        } else {
            return null;
        }

        if (preg_match('/^[(]\s*+((?:[^ ()]++|[(][^ )]+[)])++)(?:[ ]+("[^"]*"|\'[^\']*\'))?\s*[)]/', $remainder, $matches)) //NOSONAR
        {
            $element['attributes']['href'] = $matches[1];

            if (isset($matches[2])) {
                $element['attributes']['title'] = substr($matches[2], 1, -1);
            }

            $extent += strlen($matches[0]);
        } else {
            if (preg_match('/^\s*\[(.*?)\]/', $remainder, $matches)) {
                $definition = strlen($matches[1]) ? $matches[1] : $element['text'];
                $definition = strtolower($definition);

                $extent += strlen($matches[0]);
            } else {
                $definition = strtolower($element['text']);
            }

            if (!isset($this->definitionData['Reference'][$definition])) {
                return null;
            }

            $definition = $this->definitionData['Reference'][$definition];

            $element['attributes']['href'] = $definition['url'];
            $element['attributes']['title'] = $definition['title'];
        }

        return array(
            'extent' => $extent,
            'element' => $element,
        );
    }

    /**
     * Processes inline HTML markup in the provided text.
     *
     * @param array $excerpt Contains the text to be processed.
     * @return array|null Returns an array with the markup and its extent, or null if no valid markup is found.
     */
    protected function inlineMarkup($excerpt) //NOSONAR
    {
        if ($this->markupEscaped || $this->safeMode || strpos($excerpt['text'], '>') === false) {
            return null;
        }

        if ($excerpt['text'][1] === '/' && preg_match('/^<\/\w[\w-]*[ ]*>/s', $excerpt['text'], $matches)) //NOSONAR
        {
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        }

        if ($excerpt['text'][1] === '!' && preg_match('/^<!---?[^>-](?:-?[^-])*-->/s', $excerpt['text'], $matches)) //NOSONAR
        {
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        }

        if ($excerpt['text'][1] !== ' ' && preg_match('/^<\w[\w-]*(?:[ ]*' . $this->regexHtmlAttribute . ')*[ ]*\/?>/s', $excerpt['text'], $matches)) {
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        }
    }

    /**
     * Processes inline special characters in the provided text.
     *
     * @param array $excerpt Contains the text to be processed.
     * @return array|null Returns an array with the escaped character and its extent, or null if no special character is found.
     */
    protected function inlineSpecialCharacter($excerpt)
    {
        if ($excerpt['text'][0] === '&' && !preg_match('/^&#?\w+;/', $excerpt['text'])) {
            return array(
                'markup' => '&amp;',
                'extent' => 1,
            );
        }

        $specialCharacter = array('>' => 'gt', '<' => 'lt', '"' => 'quot');

        if (isset($specialCharacter[$excerpt['text'][0]])) {
            return array(
                'markup' => '&' . $specialCharacter[$excerpt['text'][0]] . ';',
                'extent' => 1,
            );
        }
    }

    /**
     * Processes inline strikethrough text in the provided text.
     *
     * @param array $excerpt Contains the text to be processed.
     * @return array|null Returns an array with the extent of the strikethrough and the corresponding element, or null if no match is found.
     */
    protected function inlineStrikethrough($excerpt)
    {
        if (!isset($excerpt['text'][1])) {
            return null;
        }

        if ($excerpt['text'][1] === '~' && preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'del',
                    'text' => $matches[1],
                    'handler' => 'line',
                ),
            );
        }
    }

    /**
     * Processes inline URLs in the provided text.
     *
     * @param array $excerpt Contains the text to be processed.
     * @return array|null Returns an array with the extent and URL element, or null if no valid URL is found.
     */
    protected function inlineUrl($excerpt)
    {
        if ($this->urlsLinked !== true || !isset($excerpt['text'][2]) || $excerpt['text'][2] !== '/') {
            return null;
        }

        if (preg_match('/\bhttps?:[\/]{2}[^\s<]+\b\/*/ui', $excerpt['context'], $matches, PREG_OFFSET_CAPTURE)) //NOSONAR
        {
            $url = $matches[0][0];

            return array(
                'extent' => strlen($matches[0][0]),
                'position' => $matches[0][1],
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );

        }
    }

    /**
     * Processes inline URL tags formatted in angle brackets.
     *
     * @param array $excerpt Contains the text to be processed.
     * @return array|null Returns an array with the extent and URL element, or null if no valid tag is found.
     */
    protected function inlineUrlTag($excerpt)
    {
        if (strpos($excerpt['text'], '>') !== false && preg_match('/^<(\w+:\/{2}[^ >]+)>/i', $excerpt['text'], $matches)) {
            $url = $matches[1];

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        }
    }

    /**
     * Processes unmarked text by converting line breaks to HTML.
     *
     * @param string $text The text to be processed.
     * @return string The processed text with line breaks converted to HTML.
     */
    protected function unmarkedText($text)
    {
        if ($this->breaksEnabled) {
            $text = preg_replace('/[ ]*\n/', "<br />\n", $text); //NOSONAR
        } else {
            $text = preg_replace('/(?:[ ][ ]+|[ ]*\\\\)\n/', "<br />\n", $text); //NOSONAR
            $text = str_replace(" \n", "\n", $text);
        }

        return $text;
    }

    /**
     * Generates HTML markup for a given element.
     *
     * @param array $element The element to be rendered as HTML.
     * @return string The generated HTML markup for the element.
     */
    protected function element(array $element) //NOSONAR
    {
        if ($this->safeMode) {
            $element = $this->sanitiseElement($element);
        }

        $markup = '<' . $element['name'];

        if (isset($element['attributes'])) {
            foreach ($element['attributes'] as $name => $value) {
                if ($value === null) {
                    continue;
                }

                $markup .= ' ' . $name . '="' . self::escape($value) . '"';
            }
        }

        $permitRawHtml = false;

        if (isset($element['text'])) {
            $text = $element['text'];
        }
        // very strongly consider an alternative if you're writing an
        // extension
        elseif (isset($element['rawHtml'])) {
            $text = $element['rawHtml'];
            $allowRawHtmlInSafeMode = isset($element['allowRawHtmlInSafeMode']) && $element['allowRawHtmlInSafeMode'];
            $permitRawHtml = !$this->safeMode || $allowRawHtmlInSafeMode;
        }

        if (isset($text)) {
            $markup .= '>';

            if (!isset($element['nonNestables'])) {
                $element['nonNestables'] = array();
            }

            if (isset($element['handler'])) {
                $markup .= $this->{$element['handler']}($text, $element['nonNestables']);
            } elseif (!$permitRawHtml) {
                $markup .= self::escape($text, true);
            } else {
                $markup .= $text;
            }

            $markup .= '</' . $element['name'] . '>';
        } else {
            $markup .= ' />';
        }

        return $markup;
    }

    /**
     * Generates HTML markup for multiple elements.
     *
     * @param array $elements An array of elements to be rendered as HTML.
     * @return string The generated HTML markup for all elements.
     */
    protected function elements(array $elements)
    {
        $markup = '';

        foreach ($elements as $element) {
            $markup .= "\n" . $this->element($element);
        }

        $markup .= "\n";

        return $markup;
    }

    /**
     * Processes list items by converting lines to HTML list markup.
     *
     * @param array $lines An array of lines representing list items.
     * @return string The generated HTML for the list items.
     */
    protected function li($lines)
    {
        $markup = $this->lines($lines);

        $trimmedMarkup = trim($markup);

        if (!in_array('', $lines) && substr($trimmedMarkup, 0, 3) === '<p>') {
            $markup = $trimmedMarkup;
            $markup = substr($markup, 3);

            $position = strpos($markup, "</p>");

            $markup = substr_replace($markup, '', $position, 4);
        }

        return $markup;
    }

    /**
     * Parses the input text and returns the generated HTML markup.
     *
     * @param string $text The text to be parsed.
     * @return string The generated HTML markup.
     */
    public function parse($text)
    {
        return $this->text($text);
    }

    /**
     * Sanitizes an HTML element to prevent XSS vulnerabilities.
     *
     * @param array $element The element to be sanitized.
     * @return array The sanitized element.
     */
    protected function sanitiseElement(array $element)
    {
        static $goodAttribute = '/^[a-zA-Z0-9][a-zA-Z0-9-_]*+$/';
        static $safeUrlNameToAtt  = array(
            'a'   => 'href',
            'img' => 'src',
        );

        if (isset($safeUrlNameToAtt[$element['name']])) {
            $element = $this->filterUnsafeUrlInAttribute($element, $safeUrlNameToAtt[$element['name']]);
        }

        if (!empty($element['attributes'])) {
            foreach ($element['attributes'] as $att => $val) {
                # filter out badly parsed attribute or dump onevent attribute
                if (!preg_match($goodAttribute, $att) || self::striAtStart($att, 'on')) {
                    unset($element['attributes'][$att]);
                }
            }
        }

        return $element;
    }

    /**
     * Filters unsafe URLs in a given attribute of an HTML element.
     *
     * @param array $element The element containing the attribute to be filtered.
     * @param string $attribute The name of the attribute to be checked.
     * @return array The element with the filtered attribute.
     */
    protected function filterUnsafeUrlInAttribute(array $element, $attribute)
    {
        foreach ($this->safeLinksWhitelist as $scheme) {
            if (self::striAtStart($element['attributes'][$attribute], $scheme)) {
                return $element;
            }
        }

        $element['attributes'][$attribute] = str_replace(':', '%3A', $element['attributes'][$attribute]);

        return $element;
    }

    /**
     * Escapes special characters for HTML output.
     *
     * @param string $text The text to be escaped.
     * @param bool $allowQuotes Whether to allow unescaped quotes.
     * @return string The escaped text.
     */
    protected static function escape($text, $allowQuotes = false)
    {
        return htmlspecialchars($text, $allowQuotes ? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8');
    }

    /**
     * Checks if a string starts with a given needle.
     *
     * @param string $string The string to be checked.
     * @param string $needle The substring to check for.
     * @return bool True if the string starts with the needle, false otherwise.
     */
    protected static function striAtStart($string, $needle)
    {
        $len = strlen($needle);

        if ($len > strlen($string)) {
            return false;
        } else {
            return strtolower(substr($string, 0, $len)) === strtolower($needle);
        }
    }

    /**
     * Retrieves an instance of the class.
     *
     * @param string $name The name of the instance.
     * @return static The instance of the class.
     */
    public static function instance($name = 'default')
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $instance = new static();

        self::$instances[$name] = $instance;

        return $instance;
    }

    /**
     * @var array Holds instances of the class.
     */
    private static $instances = array();

    /**
     * @var mixed Contains the definition data for reference links.
     */
    protected $definitionData;

    /**
     * @var array List of special characters used in parsing.
     */
    protected $specialCharacters = array(
        '\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '>', '#', '+', '-', '.', '!', '|',
    );

    /**
     * @var array Regular expressions for strong emphasis syntax.
     */
    protected $strongRegex = array(
        '*' => '/^[*]{2}((?:\\\\\*|[^*]|[*][^*]*[*])+?)[*]{2}(?![*])/s',
        '_' => '/^__((?:\\\\_|[^_]|_[^_]*_)+?)__(?!_)/us',
    );

    /**
     * @var array Regular expressions for emphasis syntax.
     */
    protected $emRegex = array(
        '*' => '/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s',
        '_' => '/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',
    );

    /**
     * @var string Regular expression for validating HTML attributes.
     */
    protected $regexHtmlAttribute = '[a-zA-Z_:][\w:.-]*(?:\s*=\s*(?:[^"\'=<>`\s]+|"[^"]*"|\'[^\']*\'))?';

    /**
     * @var array List of void elements that do not have closing tags.
     */
    protected $voidElements = array(
        'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source',
    );

    /**
     * @var array List of text-level elements for inline formatting.
     */
    protected $textLevelElements = array(
        'a', 'br', 'bdo', 'abbr', 'blink', 'nextid', 'acronym', 'basefont',
        'b', 'em', 'big', 'cite', 'small', 'spacer', 'listing',
        'i', 'rp', 'del', 'code',          'strike', 'marquee',
        'q', 'rt', 'ins', 'font',          'strong',
        's', 'tt', 'kbd', 'mark',
        'u', 'xm', 'sub', 'nobr',
        'sup', 'ruby',
        'var', 'span',
        'wbr', 'time',
    );
}
