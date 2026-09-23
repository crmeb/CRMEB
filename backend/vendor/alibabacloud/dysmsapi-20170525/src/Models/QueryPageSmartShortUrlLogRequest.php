<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models;

use AlibabaCloud\Tea\Model;

class QueryPageSmartShortUrlLogRequest extends Model
{
    /**
     * @example 12
     *
     * @var int
     */
    public $clickState;

    /**
     * @example 14
     *
     * @var int
     */
    public $createDateEnd;

    /**
     * @example 31
     *
     * @var int
     */
    public $createDateStart;

    /**
     * @example 46
     *
     * @var int
     */
    public $endId;

    /**
     * @var int
     */
    public $ownerId;

    /**
     * @example 99
     *
     * @var int
     */
    public $pageNo;

    /**
     * @example 84
     *
     * @var int
     */
    public $pageSize;

    /**
     * @example 示例值示例值示例值
     *
     * @var string
     */
    public $phoneNumber;

    /**
     * @var string
     */
    public $resourceOwnerAccount;

    /**
     * @var int
     */
    public $resourceOwnerId;

    /**
     * @example 示例值示例值示例值
     *
     * @var string
     */
    public $shortName;

    /**
     * @example 示例值示例值示例值
     *
     * @var string
     */
    public $shortUrl;

    /**
     * @example 89
     *
     * @var int
     */
    public $startId;
    protected $_name = [
        'clickState'           => 'ClickState',
        'createDateEnd'        => 'CreateDateEnd',
        'createDateStart'      => 'CreateDateStart',
        'endId'                => 'EndId',
        'ownerId'              => 'OwnerId',
        'pageNo'               => 'PageNo',
        'pageSize'             => 'PageSize',
        'phoneNumber'          => 'PhoneNumber',
        'resourceOwnerAccount' => 'ResourceOwnerAccount',
        'resourceOwnerId'      => 'ResourceOwnerId',
        'shortName'            => 'ShortName',
        'shortUrl'             => 'ShortUrl',
        'startId'              => 'StartId',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->clickState) {
            $res['ClickState'] = $this->clickState;
        }
        if (null !== $this->createDateEnd) {
            $res['CreateDateEnd'] = $this->createDateEnd;
        }
        if (null !== $this->createDateStart) {
            $res['CreateDateStart'] = $this->createDateStart;
        }
        if (null !== $this->endId) {
            $res['EndId'] = $this->endId;
        }
        if (null !== $this->ownerId) {
            $res['OwnerId'] = $this->ownerId;
        }
        if (null !== $this->pageNo) {
            $res['PageNo'] = $this->pageNo;
        }
        if (null !== $this->pageSize) {
            $res['PageSize'] = $this->pageSize;
        }
        if (null !== $this->phoneNumber) {
            $res['PhoneNumber'] = $this->phoneNumber;
        }
        if (null !== $this->resourceOwnerAccount) {
            $res['ResourceOwnerAccount'] = $this->resourceOwnerAccount;
        }
        if (null !== $this->resourceOwnerId) {
            $res['ResourceOwnerId'] = $this->resourceOwnerId;
        }
        if (null !== $this->shortName) {
            $res['ShortName'] = $this->shortName;
        }
        if (null !== $this->shortUrl) {
            $res['ShortUrl'] = $this->shortUrl;
        }
        if (null !== $this->startId) {
            $res['StartId'] = $this->startId;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return QueryPageSmartShortUrlLogRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['ClickState'])) {
            $model->clickState = $map['ClickState'];
        }
        if (isset($map['CreateDateEnd'])) {
            $model->createDateEnd = $map['CreateDateEnd'];
        }
        if (isset($map['CreateDateStart'])) {
            $model->createDateStart = $map['CreateDateStart'];
        }
        if (isset($map['EndId'])) {
            $model->endId = $map['EndId'];
        }
        if (isset($map['OwnerId'])) {
            $model->ownerId = $map['OwnerId'];
        }
        if (isset($map['PageNo'])) {
            $model->pageNo = $map['PageNo'];
        }
        if (isset($map['PageSize'])) {
            $model->pageSize = $map['PageSize'];
        }
        if (isset($map['PhoneNumber'])) {
            $model->phoneNumber = $map['PhoneNumber'];
        }
        if (isset($map['ResourceOwnerAccount'])) {
            $model->resourceOwnerAccount = $map['ResourceOwnerAccount'];
        }
        if (isset($map['ResourceOwnerId'])) {
            $model->resourceOwnerId = $map['ResourceOwnerId'];
        }
        if (isset($map['ShortName'])) {
            $model->shortName = $map['ShortName'];
        }
        if (isset($map['ShortUrl'])) {
            $model->shortUrl = $map['ShortUrl'];
        }
        if (isset($map['StartId'])) {
            $model->startId = $map['StartId'];
        }

        return $model;
    }
}
