<?php


namespace Volc\Base\Model;

class SignParam
{
    private $isSignUrl = false;
    private $pyloadHash = '';
    private $method = '';
    private $date;
    private $path = '';
    private $host = '';
    private $queryList = [];
    private $headerList = [];

    /**
     * @return bool
     */
    public function isSignUrl(): bool
    {
        return $this->isSignUrl;
    }

    /**
     * @param bool $isSignUrl
     */
    public function setIsSignUrl(bool $isSignUrl): void
    {
        $this->isSignUrl = $isSignUrl;
    }

    /**
     * @return string
     */
    public function getPyloadHash(): string
    {
        return $this->pyloadHash;
    }

    /**
     * @param string $pyloadHash
     */
    public function setPyloadHash(string $pyloadHash): void
    {
        $this->pyloadHash = $pyloadHash;
    }



    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return array
     */
    public function getQueryList(): array
    {
        return $this->queryList;
    }

    /**
     * @param array $queryList
     */
    public function setQueryList(array $queryList): void
    {
        $this->queryList = $queryList;
    }

    /**
     * @return array
     */
    public function getHeaderList(): array
    {
        return $this->headerList;
    }

    /**
     * @param array $headerList
     */
    public function setHeaderList(array $headerList): void
    {
        $this->headerList = $headerList;
    }




}