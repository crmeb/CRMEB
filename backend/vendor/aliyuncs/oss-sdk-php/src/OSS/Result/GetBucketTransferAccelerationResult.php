<?php

namespace OSS\Result;
use OSS\Model\TransferAccelerationConfig;

/**
 * Class GetBucketTransferAccelerationResult
 * @package OSS\Result
 */
class GetBucketTransferAccelerationResult extends Result
{
	/**
	 * @return bool
	 */
	protected function parseDataFromResponse()
	{
		$content = $this->rawResponse->body;
		$config = new TransferAccelerationConfig();
		$config->parseFromXml($content);
		return $config->getEnabled();
	}
}
