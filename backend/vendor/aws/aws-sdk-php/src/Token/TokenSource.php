<?php
namespace Aws\Token;

enum TokenSource: string
{
    case BEARER_SERVICE_ENV_VARS = 'bearer_service_env_vars';
}
