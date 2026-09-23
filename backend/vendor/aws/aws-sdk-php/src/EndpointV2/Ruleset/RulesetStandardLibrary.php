<?php

namespace Aws\EndpointV2\Ruleset;

use Aws\Exception\UnresolvedEndpointException;

/**
 * Provides functions and actions to be performed for endpoint evaluation.
 * This is an internal only class and is not subject to backwards-compatibility guarantees.
 *
 * @internal
 */
class RulesetStandardLibrary
{
    const IPV4_RE = '/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/';
    const IPV6_RE = '/([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|
                    . ([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]
                    . {1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:)
                    . {1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|
                    . [0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:
                    . (:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|
                    . 1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]
                    . {1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]
                    . |1{0,1}[0-9]){0,1}[0-9])/';
    const TEMPLATE_SEARCH_RE = '/\{\{.*?\}\}|\{[a-zA-Z0-9_#]+\}/';
    const TEMPLATE_PARSE_RE = '/\{\{\s*([^{}]*?)\s*\}\}|\{([a-zA-Z0-9_]+(?:#[a-zA-Z0-9_]+)*)\}/';
    const HOST_LABEL_RE = '/^(?!-)[a-zA-Z\d-]{1,63}(?<!-)$/';
    private $partitions;

    public function __construct($partitions)
    {
        $this->partitions = $partitions;
    }

    /**
     * Determines if a value is set.
     *
     * @return boolean
     */
    public function is_set($value)
    {
        return isset($value);
    }

    /**
     * Function implementation of logical operator `not`
     *
     * @return boolean
     */
    public function not($value)
    {
        return !$value;
    }

    /**
     * Find an attribute within a value given a path string.
     *
     * @return mixed
     */
    public function getAttr($from, $path)
    {
        // Handles the case where "[<int|string>]" is provided as the top-level
        // path. `\w` alone doesn't cover negative indices (e.g. `[-2]`) which
        // the smithy rules engine uses to reference the second-to-last element,
        // so we accept either a signed integer or a bare identifier.
        if (preg_match('/^\[(-?\d+|\w+)\]$/', $path, $matches)) {
            $token = $matches[1];
            if (is_numeric($token)) {
                $index = (int) $token;
                // Fold negative indices to Python-style access from the end,
                // matching the reference engine's semantics.
                if ($index < 0 && is_array($from)) {
                    $index += count($from);
                }
                return $from[$index] ?? null;
            }
            return $from[$token] ?? null;
        }


        $parts = explode('.', $path);
        foreach ($parts as $part) {
            $sliceIdx = strpos($part, '[');
            if ($sliceIdx !== false) {
                if (substr($part, -1) !== ']') {
                    return null;
                }
                $slice = (int) substr($part, $sliceIdx + 1, strlen($part) - 1);
                $fromIndex = substr($part, 0, $sliceIdx);
                $from = $from[$fromIndex][$slice] ?? null;
            } else {
                $from = $from[$part];
            }
        }
        return $from;
    }

    /**
     * Computes a substring given the start index and end index. If `reverse` is
     * true, slice the string from the end instead.
     *
     * @return mixed
     */
    public function substring($input, $start, $stop, $reverse)
    {
        if (!is_string($input)) {
            throw new UnresolvedEndpointException(
                'Input passed to `substring` must be `string`.'
            );
        }

        if (preg_match('/[^\x00-\x7F]/', $input)) {
            return null;
        }
        if ($start >= $stop or strlen($input) < $stop) {
            return null;
        }
        if (!$reverse) {
            return substr($input, $start, $stop - $start);
        } else {
            $offset = strlen($input) - $stop;
            $length = $stop - $start;
            return substr($input, $offset, $length);
        }
    }

    /**
     * Evaluates two strings for equality.
     *
     * @return boolean
     */
    public function stringEquals($string1, $string2)
    {
        if (!is_string($string1) || !is_string($string2)) {
            throw new UnresolvedEndpointException(
                'Values passed to StringEquals must be `string`.'
            );
        }
        return $string1 === $string2;
    }

    /**
     * Evaluates two booleans for equality.
     *
     * @return boolean
     */
    public function booleanEquals($boolean1, $boolean2)
    {
        return
            filter_var($boolean1, FILTER_VALIDATE_BOOLEAN)
            === filter_var($boolean2, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Percent-encodes an input string.
     *
     * @return mixed
     */
    public function uriEncode($input)
    {
        if (is_null($input)) {
            return null;
        }
        return str_replace('%7E', '~', rawurlencode($input));
    }

    /**
     * Parses URL string into components.
     *
     * @return mixed
     */
    public function parseUrl($url)
    {
        if (is_null($url)) {
            return null;
        }

        $parsed = parse_url($url);

        if ($parsed === false || !empty($parsed['query'])) {
            return null;
        } elseif (!isset($parsed['scheme'])) {
            return null;
        }

        if ($parsed['scheme'] !== 'http'
            && $parsed['scheme'] !== 'https'
        ) {
            return null;
        }

        $urlInfo = [];
        $urlInfo['scheme'] = $parsed['scheme'];
        $urlInfo['authority'] = $parsed['host'] ?? '';
        if (isset($parsed['port'])) {
            $urlInfo['authority'] = $urlInfo['authority'] . ":" . $parsed['port'];
        }
        $urlInfo['path'] = $parsed['path'] ?? '';
        $urlInfo['normalizedPath'] = !empty($parsed['path'])
            ? rtrim($urlInfo['path'] ?: '', '/' .  "/") . '/'
            : '/';
        $urlInfo['isIp'] = !isset($parsed['host']) ?
            'false' : $this->isValidIp($parsed['host']);

        return $urlInfo;
    }

    /**
     * Evaluates whether a value is a valid host label per
     * RFC 1123. If allow_subdomains is true, split on `.` and validate
     * each subdomain separately.
     *
     * @return boolean
     */
    public function isValidHostLabel($hostLabel, $allowSubDomains)
    {
        if (!isset($hostLabel)
            || (!$allowSubDomains && strpos($hostLabel, '.') != false)
        ) {
            return false;
        }

        if ($allowSubDomains) {
            foreach (explode('.', $hostLabel) as $subdomain) {
                if (!$this->validateHostLabel($subdomain)) {
                    return false;
                }
            }
            return true;
        } else {
            return $this->validateHostLabel($hostLabel);
        }
    }

    /**
     * Parse and validate string for ARN components.
     *
     * @return array|null
     */
    public function parseArn($arnString)
    {
        if (!is_string($arnString)
            || strncmp($arnString, 'arn', 3) !== 0
        ) {
            return null;
        }

        $parts = explode(':', $arnString, 6);
        if (count($parts) < 6) {
            return null;
        }

        $partition = $parts[1];
        $service = $parts[2];
        $region = $parts[3];
        $accountId = $parts[4];
        $resource = $parts[5];

        if ($partition === ''
            || $service === ''
            || $resource === ''
        ) {
            return null;
        }

        return [
            'partition' => $partition,
            'service' => $service,
            'region' => $region,
            'accountId' => $accountId,
            'resourceId' => preg_split("/[:\/]/", $resource),
        ];
    }

    /**
     * Matches a region string to an AWS partition.
     *
     * @return mixed
     */
    public function partition($region)
    {
        if (!is_string($region)) {
            throw new UnresolvedEndpointException(
                'Value passed to `partition` must be `string`.'
            );
        }

        $partitions = $this->partitions;
        foreach ($partitions['partitions'] as $partition) {
            if (array_key_exists($region, $partition['regions'])
                || preg_match("/{$partition['regionRegex']}/", $region)
            ) {
                return $partition['outputs'];
            }
        }
        //return `aws` partition if no match is found.
        return $partitions['partitions'][0]['outputs'];
    }

    /**
     * Returns the first non-null argument, or null if every argument is null.
     * Mirrors the standard library `coalesce` function and accepts any number
     * of already-resolved values.
     *
     * @return mixed
     */
    public function coalesce(...$values)
    {
        foreach ($values as $value) {
            if (!is_null($value)) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Splits a string on a delimiter up to an optional limit, returning an
     * array of string parts. Mirrors the smithy `split` function: a `null` or
     * `0` limit means "no limit", a positive limit caps the number of parts,
     * and any other input (non-string, empty delimiter, negative limit)
     * returns null so downstream conditions treat it as "no value".
     *
     * @return array|null
     */
    public function split($input, $delimiter, $limit = null)
    {
        if (!is_string($input) || !is_string($delimiter) || $delimiter === '') {
            return null;
        }

        if (is_null($limit) || $limit === 0) {
            return explode($delimiter, $input);
        }

        if (!is_int($limit) || $limit < 0) {
            return null;
        }

        return explode($delimiter, $input, $limit);
    }

    /**
     * Functional if-then-else. Returns `$then` when `$condition` is truthy,
     * otherwise `$else`. Arguments are resolved eagerly by the caller, which
     * matches the rules engine semantics for function arguments.
     *
     * @return mixed
     */
    public function ite($condition, $then, $else)
    {
        return filter_var($condition, FILTER_VALIDATE_BOOLEAN) ? $then : $else;
    }

    /**
     * Evaluates whether a value is a valid bucket name for virtual host
     * style bucket URLs.
     *
     * @return boolean
     */
    public function isVirtualHostableS3Bucket($bucketName, $allowSubdomains)
    {
        if ((is_null($bucketName)
            || (strlen($bucketName) < 3 || strlen($bucketName) > 63))
            || preg_match(self::IPV4_RE, $bucketName)
            || strtolower($bucketName) !== $bucketName
        ) {
            return false;
        }

        if ($allowSubdomains) {
            $labels = explode('.', $bucketName);
            $results = [];
            forEach($labels as $label) {
                $results[] = $this->isVirtualHostableS3Bucket($label, false);
            }
            return !in_array(false, $results);
        }
        return $this->isValidHostLabel($bucketName, false);
    }

    public function callFunction($funcCondition, &$inputParameters)
    {
        $argv = $funcCondition['argv'];
        $assign = $funcCondition['assign'] ?? null;
        $fn = $funcCondition['fn'];
        switch ($fn) {
            case 'aws.parseArn':
                $result = $this->parseArn(
                    $this->resolveValue($argv[0], $inputParameters)
                );
                break;

            case 'getAttr':
                $result = $this->getAttr(
                    $this->resolveValue($argv[0], $inputParameters),
                    $argv[1]
                );
                break;

            case 'stringEquals':
                $result = $this->stringEquals(
                    $this->resolveValue($argv[0], $inputParameters),
                    $this->resolveValue($argv[1], $inputParameters)
                );
                break;

            case 'booleanEquals':
                $result = $this->booleanEquals(
                    $this->resolveValue($argv[0], $inputParameters),
                    $this->resolveValue($argv[1], $inputParameters)
                );
                break;

            case 'isSet':
                $arg = $argv[0];
                $result = isset($arg['ref'])
                    ? isset($inputParameters[$arg['ref']])
                    : $this->is_set($this->resolveValue($arg, $inputParameters));
                break;

            case 'not':
                $result = $this->not(
                    $this->resolveValue($argv[0], $inputParameters)
                );
                break;

            case 'substring':
                $result = $this->substring(
                    $this->resolveValue($argv[0], $inputParameters),
                    $this->resolveValue($argv[1], $inputParameters),
                    $this->resolveValue($argv[2], $inputParameters),
                    isset($argv[3])
                        ? $this->resolveValue($argv[3], $inputParameters)
                        : false
                );
                break;

            default:
                $funcArgs = [];
                foreach ($argv as $arg) {
                    $funcArgs[] = $this->resolveValue($arg, $inputParameters);
                }

                $funcName = str_replace('aws.', '', $fn);
                if ($funcName === 'isSet') {
                    $funcName = 'is_set';
                }

                if (!method_exists($this, $funcName)) {
                    throw new UnresolvedEndpointException(
                        "Unknown endpoint function `{$fn}`."
                    );
                }

                $result = call_user_func_array(
                    [$this, $funcName],
                    $funcArgs
                );
        }

        if ($assign !== null) {
            if (isset($inputParameters[$assign])) {
                throw new UnresolvedEndpointException(
                    "Assignment `{$assign}` already exists in input parameters" .
                    " or has already been assigned by an endpoint rule and cannot be overwritten."
                );
            }
            $inputParameters[$assign] = $result;
        }
        return $result;
    }

    public function resolveValue($value, $inputParameters)
    {
        //Given a value, check if it's a function, reference or template.
        //returns resolved value
        if (is_array($value)) {
            if (isset($value['fn'])) {
                return $this->callFunction($value, $inputParameters);
            }
            if (isset($value['ref'])) {
                return $inputParameters[$value['ref']] ?? null;
            }
        } elseif (is_string($value)
            && str_contains($value, '{')
            && $this->isTemplate($value)
        ) {
            return $this->resolveTemplateString($value, $inputParameters);
        }

        return $value;
    }

    public function isFunc($arg)
    {
        return is_array($arg) && isset($arg['fn']);
    }

    public function isRef($arg)
    {
        return is_array($arg) && isset($arg['ref']);
    }

    public function isTemplate($arg)
    {
        return is_string($arg)
            && str_contains($arg, '{')
            && preg_match(self::TEMPLATE_SEARCH_RE, $arg) === 1;
    }

    public function resolveTemplateString($value, $inputParameters)
    {
        return preg_replace_callback(
            self::TEMPLATE_PARSE_RE,
            function ($match) use ($inputParameters) {
                if (str_starts_with($match[0], '{{')) {
                    return '{' . $match[1] . '}';
                }

                $notFoundMessage = 'Resolved value was null.  Please check rules and ' .
                    'input parameters and try again.';

                $parts = explode("#", $match[2]);
                if (count($parts) > 1) {
                    $resolvedValue = $inputParameters;
                    foreach($parts as $part) {
                        if (!isset($resolvedValue[$part])) {
                            throw new UnresolvedEndpointException($notFoundMessage);
                        }
                        $resolvedValue = $resolvedValue[$part];
                    }
                    return $resolvedValue;
                } else {
                    if (!isset($inputParameters[$parts[0]])) {
                        throw new UnresolvedEndpointException($notFoundMessage);
                    }
                    return $inputParameters[$parts[0]];
                }
            },
            $value
        );
    }

    private function validateHostLabel ($hostLabel)
    {
        if (empty($hostLabel) || strlen($hostLabel) > 63) {
            return false;
        }
        if (preg_match(self::HOST_LABEL_RE, $hostLabel)) {
            return true;
        }
        return false;
    }

    private function isValidIp($hostName)
    {
        $isWrapped = strpos($hostName, '[') === 0
            && strrpos($hostName, ']') === strlen($hostName) - 1;

        return preg_match(
                self::IPV4_RE,
            $hostName
        )
        //IPV6 enclosed in brackets
        || ($isWrapped && preg_match(
            self::IPV6_RE,
            $hostName
        ))
            ? 'true' : 'false';
    }
}
