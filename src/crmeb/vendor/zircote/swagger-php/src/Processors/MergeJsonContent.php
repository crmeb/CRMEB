<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\JsonContent;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\RequestBody;
use OpenApi\Annotations\Response;
use OpenApi\Context;
use OpenApi\Logger;

/**
 * Split JsonContent into Schema and MediaType.
 */
class MergeJsonContent
{
    public function __invoke(Analysis $analysis)
    {
        $annotations = $analysis->getAnnotationsOfType(JsonContent::class);
        foreach ($annotations as $jsonContent) {
            $parent = $jsonContent->_context->nested;
            if (!($parent instanceof Response) && !($parent instanceof RequestBody) && !($parent instanceof Parameter)) {
                if ($parent) {
                    Logger::notice('Unexpected '.$jsonContent->identity().' in '.$parent->identity().' in '.$parent->_context);
                } else {
                    Logger::notice('Unexpected '.$jsonContent->identity().' must be nested');
                }
                continue;
            }
            if ($parent->content === UNDEFINED) {
                $parent->content = [];
            }
            $parent->content['application/json'] = new MediaType([
                'schema' => $jsonContent,
                'example' => $jsonContent->example,
                'examples' => $jsonContent->examples,
                '_context' => new Context(['generated' => true], $jsonContent->_context),
            ]);
            if (!$parent instanceof Parameter) {
                $parent->content['application/json']->mediaType = 'application/json';
            }
            $jsonContent->example = UNDEFINED;
            $jsonContent->examples = UNDEFINED;

            $index = array_search($jsonContent, $parent->_unmerged, true);
            if ($index !== false) {
                array_splice($parent->_unmerged, $index, 1);
            }
        }
    }
}
