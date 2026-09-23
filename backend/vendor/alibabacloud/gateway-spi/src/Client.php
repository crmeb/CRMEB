<?php

// This file is auto-generated, don't edit it. Thanks.
namespace Darabonba\GatewaySpi;

use \Exception;

use Darabonba\GatewaySpi\Models\InterceptorContext;
use Darabonba\GatewaySpi\Models\AttributeMap;

abstract class Client {
    public function __construct(){
    }

    /**
     * @param InterceptorContext $context
     * @param AttributeMap $attributeMap
     * @return void
     */
    abstract function modifyConfiguration($context, $attributeMap);

    /**
     * @param InterceptorContext $context
     * @param AttributeMap $attributeMap
     * @return void
     */
    abstract function modifyRequest($context, $attributeMap);

    /**
     * @param InterceptorContext $context
     * @param AttributeMap $attributeMap
     * @return void
     */
    abstract function modifyResponse($context, $attributeMap);
}
