<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi;

/**
 * OpenApi\StaticAnalyser extracts swagger-php annotations from php code using static analysis.
 */
class StaticAnalyser
{
    /**
     * @param string $filename
     */
    public function __construct($filename = null)
    {
        if ($filename !== null) {
            $this->fromFile($filename);
        }
    }

    /**
     * Extract and process all doc-comments from a file.
     *
     * @param string $filename path to a php file
     *
     * @return Analysis
     */
    public function fromFile($filename)
    {
        if (function_exists('opcache_get_status') && function_exists('opcache_get_configuration')) {
            if (empty($GLOBALS['openapi_opcache_warning'])) {
                $GLOBALS['openapi_opcache_warning'] = true;
                $status = opcache_get_status();
                $config = opcache_get_configuration();
                if (is_array($status) && $status['opcache_enabled'] && $config['directives']['opcache.save_comments'] == false) {
                    Logger::warning("php.ini \"opcache.save_comments = 0\" interferes with extracting annotations.\n[LINK] http://php.net/manual/en/opcache.configuration.php#ini.opcache.save-comments");
                }
            }
        }
        $tokens = token_get_all(file_get_contents($filename));

        return $this->fromTokens($tokens, new Context(['filename' => $filename]));
    }

    /**
     * Extract and process all doc-comments from the contents.
     *
     * @param string  $code    PHP code. (including <?php tags)
     * @param Context $context the original location of the contents
     *
     * @return Analysis
     */
    public function fromCode($code, $context)
    {
        $tokens = token_get_all($code);

        return $this->fromTokens($tokens, $context);
    }

    /**
     * Shared implementation for parseFile() & parseContents().
     *
     * @param array   $tokens       The result of a token_get_all()
     * @param Context $parseContext
     *
     * @return Analysis
     */
    protected function fromTokens($tokens, $parseContext)
    {
        $analyser = new Analyser();
        $analysis = new Analysis();

        reset($tokens);
        $token = '';

        $imports = Analyser::$defaultImports;

        $parseContext->uses = [];
        // default to parse context to start with
        $schemaContext = $parseContext;

        $classDefinition = false;
        $interfaceDefinition = false;
        $traitDefinition = false;
        $comment = false;

        $line = 0;
        $lineOffset = $parseContext->line ?: 0;

        while ($token !== false) {
            $previousToken = $token;
            $token = $this->nextToken($tokens, $parseContext);

            if (is_array($token) === false) {
                // Ignore tokens like "{", "}", etc
                continue;
            }

            if ($token[0] === T_DOC_COMMENT) {
                if ($comment) {
                    // 2 Doc-comments in succession?
                    $this->analyseComment($analysis, $analyser, $comment, new Context(['line' => $line], $schemaContext));
                }
                $comment = $token[1];
                $line = $token[2] + $lineOffset;
                continue;
            }

            if (in_array($token[0], [T_ABSTRACT, T_FINAL])) {
                // skip
                $token = $this->nextToken($tokens, $parseContext);
            }

            if ($token[0] === T_CLASS) {
                // Doc-comment before a class?
                if (is_array($previousToken) && $previousToken[0] === T_DOUBLE_COLON) {
                    //php 5.5 class name resolution (i.e. ClassName::class)
                    continue;
                }

                $token = $this->nextToken($tokens, $parseContext);

                if (is_string($token) && ($token === '(' || $token === '{')) {
                    // php7 anonymous classes (i.e. new class() { public function foo() {} };)
                    continue;
                }

                if (is_array($token) && ($token[1] === 'extends' || $token[1] === 'implements')) {
                    // php7 anonymous classes with extends (i.e. new class() extends { public function foo() {} };)
                    continue;
                }

                $interfaceDefinition = false;
                $traitDefinition = false;

                $schemaContext = new Context(['class' => $token[1], 'line' => $token[2]], $parseContext);
                if ($classDefinition) {
                    $analysis->addClassDefinition($classDefinition);
                }
                $classDefinition = [
                    'class' => $token[1],
                    'extends' => null,
                    'properties' => [],
                    'methods' => [],
                    'context' => $schemaContext,
                ];

                $token = $this->nextToken($tokens, $parseContext);

                if ($token[0] === T_EXTENDS) {
                    $schemaContext->extends = $this->parseNamespace($tokens, $token, $parseContext);
                    $classDefinition['extends'] = $schemaContext->fullyQualifiedName($schemaContext->extends);
                }

                if ($token[0] === T_IMPLEMENTS) {
                    $schemaContext->implements = $this->parseNamespaceList($tokens, $token, $parseContext);
                    $classDefinition['implements'] = array_map([$schemaContext, 'fullyQualifiedName'], $schemaContext->implements);
                }

                if ($comment) {
                    $schemaContext->line = $line;
                    $this->analyseComment($analysis, $analyser, $comment, $schemaContext);
                    $comment = false;
                    continue;
                }

                // @todo detect end-of-class and reset $schemaContext
            }

            if ($token[0] === T_INTERFACE) { // Doc-comment before an interface?
                $classDefinition = false;
                $traitDefinition = false;

                $token = $this->nextToken($tokens, $parseContext);
                $schemaContext = new Context(['interface' => $token[1], 'line' => $token[2]], $parseContext);
                if ($interfaceDefinition) {
                    $analysis->addInterfaceDefinition($interfaceDefinition);
                }
                $interfaceDefinition = [
                    'interface' => $token[1],
                    'extends' => null,
                    'properties' => [],
                    'methods' => [],
                    'context' => $schemaContext,
                ];

                $token = $this->nextToken($tokens, $parseContext);

                if ($token[0] === T_EXTENDS) {
                    $schemaContext->extends = $this->parseNamespaceList($tokens, $token, $parseContext);
                    $interfaceDefinition['extends'] = array_map([$schemaContext, 'fullyQualifiedName'], $schemaContext->extends);
                }

                if ($comment) {
                    $schemaContext->line = $line;
                    $this->analyseComment($analysis, $analyser, $comment, $schemaContext);
                    $comment = false;
                    continue;
                }

                // @todo detect end-of-interface and reset $schemaContext
            }

            if ($token[0] === T_TRAIT) {
                $classDefinition = false;
                $interfaceDefinition = false;

                $token = $this->nextToken($tokens, $parseContext);
                $schemaContext = new Context(['trait' => $token[1], 'line' => $token[2]], $parseContext);
                if ($traitDefinition) {
                    $analysis->addTraitDefinition($traitDefinition);
                }
                $traitDefinition = [
                    'trait' => $token[1],
                    'properties' => [],
                    'methods' => [],
                    'context' => $schemaContext,
                ];

                if ($comment) {
                    $schemaContext->line = $line;
                    $this->analyseComment($analysis, $analyser, $comment, $schemaContext);
                    $comment = false;
                    continue;
                }

                // @todo detect end-of-trait and reset $schemaContext
            }

            if ($token[0] === T_STATIC) {
                $token = $this->nextToken($tokens, $parseContext);
                if ($token[0] === T_VARIABLE) {
                    // static property
                    $propertyContext = new Context(
                        [
                            'property' => substr($token[1], 1),
                            'static' => true,
                            'line' => $line,
                        ],
                        $schemaContext
                    );

                    if ($classDefinition) {
                        $classDefinition['properties'][$propertyContext->property] = $propertyContext;
                    }
                    if ($traitDefinition) {
                        $traitDefinition['properties'][$propertyContext->property] = $propertyContext;
                    }
                    if ($comment) {
                        $this->analyseComment($analysis, $analyser, $comment, $propertyContext);
                        $comment = false;
                    }
                    continue;
                }
            }

            if (in_array($token[0], [T_PRIVATE, T_PROTECTED, T_PUBLIC, T_VAR])) { // Scope
                [$type, $nullable, $token] = $this->parseTypeAndNextToken($tokens, $parseContext);
                if ($token[0] === T_VARIABLE) {
                    // instance property
                    $propertyContext = new Context(
                        [
                            'property' => substr($token[1], 1),
                            'type' => $type,
                            'nullable' => $nullable,
                            'line' => $line,
                        ],
                        $schemaContext
                    );

                    if ($classDefinition) {
                        $classDefinition['properties'][$propertyContext->property] = $propertyContext;
                    }
                    if ($interfaceDefinition) {
                        $interfaceDefinition['properties'][$propertyContext->property] = $propertyContext;
                    }
                    if ($traitDefinition) {
                        $traitDefinition['properties'][$propertyContext->property] = $propertyContext;
                    }
                    if ($comment) {
                        $this->analyseComment($analysis, $analyser, $comment, $propertyContext);
                        $comment = false;
                    }
                } elseif ($token[0] === T_FUNCTION) {
                    $token = $this->nextToken($tokens, $parseContext);
                    if ($token[0] === T_STRING) {
                        $methodContext = new Context(
                            [
                                'method' => $token[1],
                                'line' => $line,
                            ],
                            $schemaContext
                        );

                        if ($classDefinition) {
                            $classDefinition['methods'][$token[1]] = $methodContext;
                        }
                        if ($interfaceDefinition) {
                            $interfaceDefinition['methods'][$token[1]] = $methodContext;
                        }
                        if ($traitDefinition) {
                            $traitDefinition['methods'][$token[1]] = $methodContext;
                        }
                        if ($comment) {
                            $this->analyseComment($analysis, $analyser, $comment, $methodContext);
                            $comment = false;
                        }
                    }
                }
                continue;
            } elseif ($token[0] === T_FUNCTION) {
                $token = $this->nextToken($tokens, $parseContext);
                if ($token[0] === T_STRING) {
                    $methodContext = new Context(
                        [
                            'method' => $token[1],
                            'line' => $line,
                        ],
                        $schemaContext
                    );

                    if ($classDefinition) {
                        $classDefinition['methods'][$token[1]] = $methodContext;
                    }
                    if ($interfaceDefinition) {
                        $interfaceDefinition['methods'][$token[1]] = $methodContext;
                    }
                    if ($traitDefinition) {
                        $traitDefinition['methods'][$token[1]] = $methodContext;
                    }
                    if ($comment) {
                        $this->analyseComment($analysis, $analyser, $comment, $methodContext);
                        $comment = false;
                    }
                }
            }

            if (in_array($token[0], [T_NAMESPACE, T_USE]) === false) {
                // Skip "use" & "namespace" to prevent "never imported" warnings)
                if ($comment) {
                    // Not a doc-comment for a class, property or method?
                    $this->analyseComment($analysis, $analyser, $comment, new Context(['line' => $line], $schemaContext));
                    $comment = false;
                }
            }

            if ($token[0] === T_NAMESPACE) {
                $parseContext->namespace = $this->parseNamespace($tokens, $token, $parseContext);
                continue;
            }

            if ($token[0] === T_USE) {
                $statements = $this->parseUseStatement($tokens, $token, $parseContext);
                foreach ($statements as $alias => $target) {
                    if ($classDefinition) {
                        // class traits
                        $classDefinition['traits'][] = $schemaContext->fullyQualifiedName($target);
                    } elseif ($traitDefinition) {
                        // trait traits
                        $traitDefinition['traits'][] = $schemaContext->fullyQualifiedName($target);
                    } else {
                        // not a trait use
                        $parseContext->uses[$alias] = $target;

                        if (Analyser::$whitelist === false) {
                            $imports[strtolower($alias)] = $target;
                        } else {
                            foreach (Analyser::$whitelist as $namespace) {
                                if (strcasecmp(substr($target, 0, strlen($namespace)), $namespace) === 0) {
                                    $imports[strtolower($alias)] = $target;
                                    break;
                                }
                            }
                        }
                        $analyser->docParser->setImports($imports);
                    }
                }
            }
        }

        // cleanup final comment and definition
        if ($comment) {
            $this->analyseComment($analysis, $analyser, $comment, new Context(['line' => $line], $schemaContext));
        }
        if ($classDefinition) {
            $analysis->addClassDefinition($classDefinition);
        }
        if ($interfaceDefinition) {
            $analysis->addInterfaceDefinition($interfaceDefinition);
        }
        if ($traitDefinition) {
            $analysis->addTraitDefinition($traitDefinition);
        }

        return $analysis;
    }

    /**
     * Parse comment and add annotations to analysis.
     *
     * @param Analysis $analysis
     * @param Analyser $analyser
     * @param string   $comment
     * @param Context  $context
     */
    private function analyseComment($analysis, $analyser, $comment, $context)
    {
        $analysis->addAnnotations($analyser->fromComment($comment, $context), $context);
    }

    /**
     * The next non-whitespace, non-comment token.
     *
     * @param array   $tokens
     * @param Context $context
     *
     * @return array|string The next token (or false)
     */
    private function nextToken(&$tokens, $context)
    {
        while (true) {
            $token = next($tokens);
            if (is_array($token)) {
                if ($token[0] === T_WHITESPACE) {
                    continue;
                }
                if ($token[0] === T_COMMENT) {
                    $pos = strpos($token[1], '@OA\\');
                    if ($pos) {
                        $line = $context->line ? $context->line + $token[2] : $token[2];
                        $commentContext = new Context(['line' => $line], $context);
                        Logger::notice('Annotations are only parsed inside `/**` DocBlocks, skipping '.$commentContext);
                    }
                    continue;
                }
            }

            return $token;
        }
    }

    /**
     * Parse namespaced string.
     */
    private function parseNamespace(&$tokens, &$token, $parseContext)
    {
        $namespace = '';
        while ($token !== false) {
            $token = $this->nextToken($tokens, $parseContext);
            if (!in_array($token[0], [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED])) {
                break;
            }
            $namespace .= $token[1];
        }

        return $namespace;
    }

    /**
     * Parse comma separated list of namespaced strings.
     */
    private function parseNamespaceList(&$tokens, &$token, $parseContext)
    {
        $namespaces = [];
        while ($namespace = $this->parseNamespace($tokens, $token, $parseContext)) {
            $namespaces[] = $namespace;
            if ($token != ',') {
                break;
            }
        }

        return $namespaces;
    }

    /**
     * Parse a use statement.
     */
    private function parseUseStatement(&$tokens, &$token, $parseContext)
    {
        $normalizeAlias = function ($alias) {
            $alias = ltrim($alias, '\\');
            $elements = explode('\\', $alias);

            return array_pop($elements);
        };

        $class = '';
        $alias = '';
        $statements = [];
        $explicitAlias = false;
        while ($token !== false) {
            $token = $this->nextToken($tokens, $parseContext);
            $isNameToken = in_array($token[0], [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED]);
            if (!$explicitAlias && $isNameToken) {
                $class .= $token[1];
                $alias = $token[1];
            } elseif ($explicitAlias && $isNameToken) {
                $alias .= $token[1];
            } elseif ($token[0] === T_AS) {
                $explicitAlias = true;
                $alias = '';
            } elseif ($token === ',') {
                $statements[$normalizeAlias($alias)] = $class;
                $class = '';
                $alias = '';
                $explicitAlias = false;
            } elseif ($token === ';') {
                $statements[$normalizeAlias($alias)] = $class;
                break;
            } else {
                break;
            }
        }

        return $statements;
    }

    /**
     * Parse type of variable (if it exists).
     */
    private function parseTypeAndNextToken(array &$tokens, Context $parseContext): array
    {
        $type = UNDEFINED;
        $nullable = false;
        $token = $this->nextToken($tokens, $parseContext);

        if ($token[0] === T_STATIC) {
            $token = $this->nextToken($tokens, $parseContext);
        }

        if ($token === '?') { // nullable type
            $nullable = true;
            $token = $this->nextToken($tokens, $parseContext);
        }

        // drill down namespace segments to basename property type declaration
        while (in_array($token[0], [T_NS_SEPARATOR, T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED, T_ARRAY])) {
            if (in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED])) {
                $type = $token[1];
            }
            $token = $this->nextToken($tokens, $parseContext);
        }

        return [$type, $nullable, $token];
    }
}
