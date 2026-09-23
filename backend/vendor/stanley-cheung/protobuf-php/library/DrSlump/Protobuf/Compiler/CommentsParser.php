<?php

namespace DrSlump\Protobuf\Compiler;

class CommentsParser
{
    /** @var array - Hold a mapping of entity => comment */
    protected $comments = array();

    /** @var array - Define tokenizer regular expressions */
    protected $tokens = array(
        'comment' => '/\*([\S\s]+?)\*/',
        'package' => 'package\s+([A-Z0-9_]+)',
        'struct'  => '(?:message|enum|service)\s+([A-Z0-9_]+)',
        'close'   => '}',
        'field'   => '(?:required|optional|repeated)\s+[^=]+=\s*([0-9]+)[^;]*;',
        'rpc'     => 'rpc\s+([A-Z0-9_]+)[^;]+'
    );

    /** @var string - The regular expresion for the tokenizer */
    protected $regexp;

    public function __construct()
    {
        // Generate a regular expression for all tokens
        $regexp = array();
        foreach ($this->tokens as $token=>$exp) {
            $regexp[] = '(?<' . $token . '>' . $exp . ')';
        }
        $this->regexp = '@' . implode('|', $regexp) . '@i';
    }

    /**
     * Reset the currently stored comments
     */
    public function reset()
    {
        $this->comments = array();
    }

    /**
     * Parse a Proto file source code to fetch comments
     *
     * @param string $src
     */
    public function parse($src)
    {
        // Build an stream of tokens from the regular expression
        $tokens = array();
        $offset = 0;
        while (preg_match($this->regexp, $src, $m, PREG_OFFSET_CAPTURE, $offset)) {
            foreach ($this->tokens as $k=>$v) {
                if (!empty($m[$k]) && 0 < strlen($m[$k][0])) {
                    $tokens[] = array(
                        'token' => $k,
                        'value' => array_shift(array_pop($m)),
                    );
                }
            }
            $offset = $m[0][1] + strlen($m[0][0]);
        }

        // Parse the tokens stream to assign comments
        $comment = null;
        $stack = array();
        foreach ($tokens as $token) {
            if ($token['token'] === 'comment') {
                $comment = $token['value'];
            } elseif ($token['token'] === 'package') {
                $stack[] = $token['value'];
                $comment = null;
            } elseif ($token['token'] === 'struct') {
                $stack[] = $token['value'];
                if ($comment) {
                    $this->setComment(implode('.', $stack), $comment);
                    $comment = null;
                }
            } elseif ($token['token'] === 'close') {
                array_pop($stack);
                $comment = null;
            } elseif ($token['token'] === 'field' || $token['token'] === 'rpc') {
                if ($comment) {
                    $this->setComment(implode('.', $stack) . '.' . $token['value'], $comment);
                    $comment = null;
                }
            }
        }
    }

    /**
     * Set a comment for the given identifier. The identifier is composed
     * of the package, followed by the message (and nested messages). Field
     * comments are suffixed with the tag number.
     *
     * @example
     *
     *     $this->setComment('MyPackage.MyMessage.Nested.2', 'field comment');
     *
     * @param string $ident
     * @param string $comment
     */
    public function setComment($ident, $comment)
    {
        $comment = str_replace("\r\n", "\n", $comment);
        $comment = preg_replace('/^[\s\*]+/m', '', $comment);
        $comment = trim($comment, "* \n");
        $this->comments[$ident] = $comment;
    }

    /**
     * Get the comment for a given identifier
     *
     * @param string $ident
     * @return string|null
     */
    public function getComment($ident)
    {
        return isset($this->comments[$ident])
               ? $this->comments[$ident]
               : null;
    }

    /**
     * Checks if a comment exists for a given identifier
     *
     * @param string $ident
     * @return bool
     */
    public function hasComment($ident)
    {
        return isset($this->comments[$ident]);
    }
}


