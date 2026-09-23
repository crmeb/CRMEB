<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Model\CorsConfig;
use OSS\Model\CorsRule;

$ossClient = Common::getOssClient();
if (is_null($ossClient)) exit(1);
$bucket = Common::getBucketName();


//******************************* Simple usage****************************************************************

// Set cors configuration
$corsConfig = new CorsConfig();
$rule = new CorsRule();
$rule->addAllowedHeader("x-oss-header");
$rule->addAllowedOrigin("http://www.b.com");
$rule->addAllowedMethod("POST");
$rule->setMaxAgeSeconds(10);
$corsConfig->addRule($rule);
$ossClient->putBucketCors($bucket, $corsConfig);
Common::println("bucket $bucket corsConfig created:" . $corsConfig->serializeToXml());

// Get cors configuration
$corsConfig = $ossClient->getBucketCors($bucket);

if ($corsConfig->getResponseVary()){
    printf("Response Vary : true" .PHP_EOL);
}else{
    printf("Response Vary : false" .PHP_EOL);
}

foreach ($corsConfig->getRules() as $key => $rule){
    if($rule->getAllowedHeaders()){
        foreach($rule->getAllowedHeaders() as $header){
            printf("Allowed Headers :" .$header .PHP_EOL);
        }
    }
    if ($rule->getAllowedMethods()){
        foreach($rule->getAllowedMethods() as $method){
            printf("Allowed Methods :" .$method . PHP_EOL);
        }

    }
    if($rule->getAllowedOrigins()){
        foreach($rule->getAllowedOrigins() as $origin){
            printf("Allowed Origins :" .$origin , PHP_EOL);
        }

    }
    if($rule->getExposeHeaders()){
        foreach($rule->getExposeHeaders() as $exposeHeader){
            printf("Expose Headers :" .$exposeHeader . PHP_EOL);
        }
    }
    printf("Max Age Seconds :" .$rule->getMaxAgeSeconds() .PHP_EOL);

}

// Delete cors configuration
$ossClient->deleteBucketCors($bucket);
Common::println("bucket $bucket corsConfig deleted");

//******************************* For complete usage, see the following functions  *****************************************************

putBucketCors($ossClient, $bucket);
getBucketCors($ossClient, $bucket);
deleteBucketCors($ossClient, $bucket);
getBucketCors($ossClient, $bucket);

/**
 * Set bucket cores
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function putBucketCors($ossClient, $bucket)
{
    $corsConfig = new CorsConfig();
    $rule = new CorsRule();
    $rule->addAllowedHeader("x-oss-header");
    $rule->addAllowedOrigin("http://www.b.com");
    $rule->addAllowedMethod("POST");
    $rule->setMaxAgeSeconds(10);
    $corsConfig->addRule($rule);

    try {
        $ossClient->putBucketCors($bucket, $corsConfig);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Get and print the cors configuration of a bucket
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function getBucketCors($ossClient, $bucket)
{
    $corsConfig = null;
    try {
        $corsConfig = $ossClient->getBucketCors($bucket);

        if ($corsConfig->getResponseVary()){
            printf("Response Vary : true" .PHP_EOL);
        }else{
            printf("Response Vary : false" .PHP_EOL);
        }
        foreach ($corsConfig->getRules() as $key => $rule){
            if($rule->getAllowedHeaders()){
                foreach($rule->getAllowedHeaders() as $header){
                    printf("Allowed Headers :" .$header .PHP_EOL);
                }
            }
            if ($rule->getAllowedMethods()){
                foreach($rule->getAllowedMethods() as $method){
                    printf("Allowed Methods :" .$method . PHP_EOL);
                }

            }
            if($rule->getAllowedOrigins()){
                foreach($rule->getAllowedOrigins() as $origin){
                    printf("Allowed Origins :" .$origin , PHP_EOL);
                }

            }
            if($rule->getExposeHeaders()){
                foreach($rule->getExposeHeaders() as $exposeHeader){
                    printf("Expose Headers :" .$exposeHeader . PHP_EOL);
                }
            }
            printf("Max Age Seconds :" .$rule->getMaxAgeSeconds() .PHP_EOL);

        }
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

/**
 * Delete all cors configuraiton of a bucket
 *
 * @param OssClient $ossClient OssClient instance
 * @param string $bucket bucket name
 * @return null
 */
function deleteBucketCors($ossClient, $bucket)
{
    try {
        $ossClient->deleteBucketCors($bucket);
    } catch (OssException $e) {
        printf(__FUNCTION__ . ": FAILED\n");
        printf($e->getMessage() . "\n");
        return;
    }
    print(__FUNCTION__ . ": OK" . "\n");
}

