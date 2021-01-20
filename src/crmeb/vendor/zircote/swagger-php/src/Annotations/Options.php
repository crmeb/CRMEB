<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Annotations;

/**
 * @Annotation
 */
class Options extends Operation
{
    /**
     * {@inheritdoc}
     */
    public $method = 'options';

    /**
     * {@inheritdoc}
     */
    public static $_parents = [
        PathItem::class,
    ];
}
