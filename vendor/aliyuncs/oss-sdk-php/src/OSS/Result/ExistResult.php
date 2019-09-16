<?php

namespace OSS\Result;

/**
 * Class ExistResult 检查bucket和object是否存在的返回结果，
 * 根据返回response的http status判断
 * @package OSS\Result
 */
class ExistResult extends Result
{
    /**
     * @return bool
     */
    protected function parseDataFromResponse()
    {
        return intval($this->rawResponse->status) === 200 ? true : false;
    }

    /**
     * 根据返回http状态码判断，[200-299]即认为是OK, 判断是否存在的接口，404也认为是一种
     * 有效响应
     *
     * @return bool
     */
    protected function isResponseOk()
    {
        $status = $this->rawResponse->status;
        if ((int)(intval($status) / 100) == 2 || (int)(intval($status)) === 404) {
            return true;
        }
        return false;
    }

}