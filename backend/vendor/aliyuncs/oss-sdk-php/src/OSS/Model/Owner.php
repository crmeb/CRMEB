<?php


namespace OSS\Model;

/**
 * Class Owner
 *
 * ListObjects return owner list of classes
 * The returned data contains two arrays
 * One is to get the list of objects【Can be understood as the corresponding file system file list】
 * One is to get owner list
 *
 */
class Owner
{
    /**
     * OwnerInfo constructor.
     * @param $id string
     * @param $displayName string
     */
    public function __construct($id, $displayName)
    {
        $this->id = $id;
        $this->displayName = $displayName;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    private $id;
    private $displayName;
}