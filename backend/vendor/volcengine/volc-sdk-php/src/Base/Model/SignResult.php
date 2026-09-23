<?php


namespace Volc\Base\Model;


class SignResult
{
    private $xDate= '';
    private $xCredential= '';
    private $xAlgorithm= '';
    private $xSignedHeaders= '';
    private $xSignedQueries= '';
    private $xSignature= '';

    private $authorization= '';

    /**
     * @return string
     */
    public function getXDate(): string
    {
        return $this->xDate;
    }

    /**
     * @param string $xDate
     */
    public function setXDate(string $xDate): void
    {
        $this->xDate = $xDate;
    }

    /**
     * @return string
     */
    public function getXCredential(): string
    {
        return $this->xCredential;
    }

    /**
     * @param string $xCredential
     */
    public function setXCredential(string $xCredential): void
    {
        $this->xCredential = $xCredential;
    }

    /**
     * @return string
     */
    public function getXAlgorithm(): string
    {
        return $this->xAlgorithm;
    }

    /**
     * @param string $xAlgorithm
     */
    public function setXAlgorithm(string $xAlgorithm): void
    {
        $this->xAlgorithm = $xAlgorithm;
    }

    /**
     * @return string
     */
    public function getXSignedHeaders(): string
    {
        return $this->xSignedHeaders;
    }

    /**
     * @param string $xSignedHeaders
     */
    public function setXSignedHeaders(string $xSignedHeaders): void
    {
        $this->xSignedHeaders = $xSignedHeaders;
    }

    /**
     * @return string
     */
    public function getXSignedQueries(): string
    {
        return $this->xSignedQueries;
    }

    /**
     * @param string $xSignedQueries
     */
    public function setXSignedQueries(string $xSignedQueries): void
    {
        $this->xSignedQueries = $xSignedQueries;
    }

    /**
     * @return string
     */
    public function getXSignature(): string
    {
        return $this->xSignature;
    }

    /**
     * @param string $xSignature
     */
    public function setXSignature(string $xSignature): void
    {
        $this->xSignature = $xSignature;
    }

    /**
     * @return string
     */
    public function getAuthorization(): string
    {
        return $this->authorization;
    }

    /**
     * @param string $authorization
     */
    public function setAuthorization(string $authorization): void
    {
        $this->authorization = $authorization;
    }

    public function __toString()
    {
        return (string)($this->xDate . $this->authorization . $this->xCredential . $this->xSignature);
    }


}