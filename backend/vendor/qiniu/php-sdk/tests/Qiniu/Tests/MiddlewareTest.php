<?php
// @codingStandardsIgnoreStart
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace Qiniu\Tests;

use PHPUnit\Framework\TestCase;

use Qiniu\Http\Client;
use Qiniu\Http\Request;
use Qiniu\Http\Middleware;
use Qiniu\Http\RequestOptions;

class RecorderMiddleware implements Middleware\Middleware
{
    /**
     * @var array<string>
     */
    private $orderRecorder;

    /**
     * @var string
     */
    private $label;

    public function __construct(&$orderRecorder, $label)
    {
        $this->orderRecorder =& $orderRecorder;
        $this->label = $label;
    }

    public function send($request, $next)
    {
        $this->orderRecorder[] = "bef_" . $this->label . count($this->orderRecorder);
        $response = $next($request);
        $this->orderRecorder[] = "aft_" . $this->label . count($this->orderRecorder);
        return $response;
    }
}

class MiddlewareTest extends TestCase
{
    public function testSendWithMiddleware()
    {
        $orderRecorder = array();

        $reqOpt = new RequestOptions();
        $reqOpt->middlewares = array(
            new RecorderMiddleware($orderRecorder, "A"),
            new RecorderMiddleware($orderRecorder, "B")
        );

        $request = new Request(
            "GET",
            "http://localhost:9000/ok.php",
            array(),
            null,
            $reqOpt
        );
        $response = Client::sendRequestWithMiddleware($request);

        $expectRecords = array(
            "bef_A0",
            "bef_B1",
            "aft_B2",
            "aft_A3"
        );

        $this->assertEquals($expectRecords, $orderRecorder);
        $this->assertEquals(200, $response->statusCode);
    }

    public function testSendWithRetryDomains()
    {
        $orderRecorder = array();

        $reqOpt = new RequestOptions();
        $reqOpt->middlewares = array(
            new Middleware\RetryDomainsMiddleware(
                array(
                    "unavailable.phpsdk.qiniu.com",
                    "localhost:9000",
                ),
                3
            ),
            new RecorderMiddleware($orderRecorder, "rec")
        );

        $request = new Request(
            "GET",
            "http://fake.phpsdk.qiniu.com/ok.php",
            array(),
            null,
            $reqOpt
        );
        $response = Client::sendRequestWithMiddleware($request);

        $expectRecords = array(
            //  'fake.phpsdk.qiniu.com' with retried 3 times
            'bef_rec0',
            'aft_rec1',
            'bef_rec2',
            'aft_rec3',
            'bef_rec4',
            'aft_rec5',

            //  'unavailable.pysdk.qiniu.com' with retried 3 times
            'bef_rec6',
            'aft_rec7',
            'bef_rec8',
            'aft_rec9',
            'bef_rec10',
            'aft_rec11',

            // 'qiniu.com' and it's success
            'bef_rec12',
            'aft_rec13'
        );

        $this->assertEquals($expectRecords, $orderRecorder);
        $this->assertEquals(200, $response->statusCode);
    }

    public function testSendFailFastWithRetryDomains()
    {
        $orderRecorder = array();

        $reqOpt = new RequestOptions();
        $reqOpt->middlewares = array(
            new Middleware\RetryDomainsMiddleware(
                array(
                    "unavailable.phpsdk.qiniu.com",
                    "localhost:9000",
                ),
                3,
                function () {
                    return false;
                }
            ),
            new RecorderMiddleware($orderRecorder, "rec")
        );

        $request = new Request(
            "GET",
            "http://fake.phpsdk.qiniu.com/ok.php",
            array(),
            null,
            $reqOpt
        );
        $response = Client::sendRequestWithMiddleware($request);

        $expectRecords = array(
            //  'fake.phpsdk.qiniu.com' will fail fast
            'bef_rec0',
            'aft_rec1'
        );
        $this->assertEquals($expectRecords, $orderRecorder);
        $this->assertEquals(-1, $response->statusCode);
    }
}
