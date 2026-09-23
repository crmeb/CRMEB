<?php

namespace OSS\Model;

/**
 * Class PartInfo
 * @package OSS\Model
 */
class PartInfo
{
    /**
     * PartInfo constructor.
     *
     * @param int $partNumber
     * @param string $lastModified
     * @param string $eTag
     * @param string $size
     */
    public function __construct($partNumber, $lastModified, $eTag, $size)
    {
        $this->partNumber = $partNumber;
        $this->lastModified = $lastModified;
        $this->eTag = $eTag;
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getPartNumber()
    {
        return $this->partNumber;
    }

    /**
     * @return string
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @return string
     */
    public function getETag()
    {
        return $this->eTag;
    }

    /**
	 * php7 && 64bit can use it
     * @return int
     */
    public function getSize()
    {
        return (int)$this->size;
    }
	
	
	/**
	 * php5.x or 32bit must use it
	 * @return string
	 */
	public function getSizeStr()
	{
		return $this->size;
	}

    private $partNumber = 0;
    private $lastModified = "";
    private $eTag = "";
    private $size = "0";
}