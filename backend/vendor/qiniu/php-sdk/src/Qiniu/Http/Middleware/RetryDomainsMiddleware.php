<?php
namespace Qiniu\Http\Middleware;

use Qiniu\Http\Request;
use Qiniu\Http\Response;

class RetryDomainsMiddleware implements Middleware
{
    /**
     * @var array<string> backup domains.
     */
    private $backupDomains;

    /**
     * @var numeric max retry times for each backup domains.
     */
    private $maxRetryTimes;

    /**
     * @var callable args response and request; returns bool; If true will retry with backup domains.
     */
    private $retryCondition;

    /**
     * @param array<string> $backupDomains
     * @param numeric $maxRetryTimes
     */
    public function __construct($backupDomains, $maxRetryTimes = 2, $retryCondition = null)
    {
        $this->backupDomains = $backupDomains;
        $this->maxRetryTimes = $maxRetryTimes;
        $this->retryCondition = $retryCondition;
    }

    private function shouldRetry($resp, $req)
    {
        if (is_callable($this->retryCondition)) {
            return call_user_func($this->retryCondition, $resp, $req);
        }

        return !$resp || $resp->needRetry();
    }

    /**
     * @param Request $request
     * @param callable(Request): Response $next
     * @return Response
     */
    public function send($request, $next)
    {
        $response = null;
        $urlComponents = parse_url($request->url);

        foreach (array_merge(array($urlComponents["host"]), $this->backupDomains) as $backupDomain) {
            $urlComponents["host"] = $backupDomain;
            $request->url = \Qiniu\unparse_url($urlComponents);
            $retriedTimes = 0;

            while ($retriedTimes < $this->maxRetryTimes) {
                $response = $next($request);

                $retriedTimes += 1;

                if (!$this->shouldRetry($response, $request)) {
                    return $response;
                }
            }
        }

        if (!$response) {
            $response = $next($request);
        }

        return $response;
    }
}
