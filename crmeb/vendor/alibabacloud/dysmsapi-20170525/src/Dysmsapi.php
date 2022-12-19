<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525;

use AlibabaCloud\Endpoint\Endpoint;
use AlibabaCloud\OpenApiUtil\OpenApiUtilClient;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\AddShortUrlRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\AddShortUrlResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\AddSmsSignRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\AddSmsSignResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\AddSmsTemplateRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\AddSmsTemplateResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\CheckMobilesCardSupportRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\CheckMobilesCardSupportResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\CreateCardSmsTemplateRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\CreateCardSmsTemplateResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\CreateCardSmsTemplateShrinkRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\DeleteShortUrlRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\DeleteShortUrlResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\DeleteSmsSignRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\DeleteSmsSignResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\DeleteSmsTemplateRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\DeleteSmsTemplateResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\GetCardSmsLinkRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\GetCardSmsLinkResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\GetMediaResourceIdRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\GetMediaResourceIdResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\GetOSSInfoForCardTemplateResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\ListTagResourcesRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\ListTagResourcesResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\ModifySmsSignRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\ModifySmsSignResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\ModifySmsTemplateRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\ModifySmsTemplateResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryCardSmsTemplateReportRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryCardSmsTemplateReportResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryCardSmsTemplateRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryCardSmsTemplateResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySendDetailsRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySendDetailsResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySendStatisticsRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySendStatisticsResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryShortUrlRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QueryShortUrlResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsSignListRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsSignListResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsSignRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsSignResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsTemplateListRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsTemplateListResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsTemplateRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySmsTemplateResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendBatchCardSmsRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendBatchCardSmsResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendBatchSmsRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendBatchSmsResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendCardSmsRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendCardSmsResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\TagResourcesRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\TagResourcesResponse;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\UntagResourcesRequest;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\UntagResourcesResponse;
use AlibabaCloud\Tea\Utils\Utils;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Darabonba\OpenApi\Models\OpenApiRequest;
use Darabonba\OpenApi\Models\Params;
use Darabonba\OpenApi\OpenApiClient;

class Dysmsapi extends OpenApiClient
{
    public function __construct($config)
    {
        parent::__construct($config);
        $this->_endpointRule = 'central';
        $this->_endpointMap  = [
            'ap-southeast-1' => 'dysmsapi.ap-southeast-1.aliyuncs.com',
            'ap-southeast-5' => 'dysmsapi-xman.ap-southeast-5.aliyuncs.com',
            'cn-beijing'     => 'dysmsapi-proxy.cn-beijing.aliyuncs.com',
            'cn-hongkong'    => 'dysmsapi-xman.cn-hongkong.aliyuncs.com',
        ];
        $this->checkConfig($config);
        $this->_endpoint = $this->getEndpoint('dysmsapi', $this->_regionId, $this->_endpointRule, $this->_network, $this->_suffix, $this->_endpointMap, $this->_endpoint);
    }

    /**
     * @param string   $productId
     * @param string   $regionId
     * @param string   $endpointRule
     * @param string   $network
     * @param string   $suffix
     * @param string[] $endpointMap
     * @param string   $endpoint
     *
     * @return string
     */
    public function getEndpoint($productId, $regionId, $endpointRule, $network, $suffix, $endpointMap, $endpoint)
    {
        if (!Utils::empty_($endpoint)) {
            return $endpoint;
        }
        if (!Utils::isUnset($endpointMap) && !Utils::empty_(@$endpointMap[$regionId])) {
            return @$endpointMap[$regionId];
        }

        return Endpoint::getEndpointRules($productId, $regionId, $endpointRule, $network, $suffix);
    }

    /**
     * @param AddShortUrlRequest $request
     * @param RuntimeOptions     $runtime
     *
     * @return AddShortUrlResponse
     */
    public function addShortUrlWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        $body = [];
        if (!Utils::isUnset($request->effectiveDays)) {
            $body['EffectiveDays'] = $request->effectiveDays;
        }
        if (!Utils::isUnset($request->shortUrlName)) {
            $body['ShortUrlName'] = $request->shortUrlName;
        }
        if (!Utils::isUnset($request->sourceUrl)) {
            $body['SourceUrl'] = $request->sourceUrl;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
            'body'  => OpenApiUtilClient::parseToMap($body),
        ]);
        $params = new Params([
            'action'      => 'AddShortUrl',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return AddShortUrlResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param AddShortUrlRequest $request
     *
     * @return AddShortUrlResponse
     */
    public function addShortUrl($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->addShortUrlWithOptions($request, $runtime);
    }

    /**
     * @param AddSmsSignRequest $request
     * @param RuntimeOptions    $runtime
     *
     * @return AddSmsSignResponse
     */
    public function addSmsSignWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->remark)) {
            $query['Remark'] = $request->remark;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->signName)) {
            $query['SignName'] = $request->signName;
        }
        if (!Utils::isUnset($request->signSource)) {
            $query['SignSource'] = $request->signSource;
        }
        if (!Utils::isUnset($request->signType)) {
            $query['SignType'] = $request->signType;
        }
        $body = [];
        if (!Utils::isUnset($request->signFileList)) {
            $body['SignFileList'] = $request->signFileList;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
            'body'  => OpenApiUtilClient::parseToMap($body),
        ]);
        $params = new Params([
            'action'      => 'AddSmsSign',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return AddSmsSignResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param AddSmsSignRequest $request
     *
     * @return AddSmsSignResponse
     */
    public function addSmsSign($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->addSmsSignWithOptions($request, $runtime);
    }

    /**
     * @param AddSmsTemplateRequest $request
     * @param RuntimeOptions        $runtime
     *
     * @return AddSmsTemplateResponse
     */
    public function addSmsTemplateWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->remark)) {
            $query['Remark'] = $request->remark;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->templateContent)) {
            $query['TemplateContent'] = $request->templateContent;
        }
        if (!Utils::isUnset($request->templateName)) {
            $query['TemplateName'] = $request->templateName;
        }
        if (!Utils::isUnset($request->templateType)) {
            $query['TemplateType'] = $request->templateType;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'AddSmsTemplate',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return AddSmsTemplateResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param AddSmsTemplateRequest $request
     *
     * @return AddSmsTemplateResponse
     */
    public function addSmsTemplate($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->addSmsTemplateWithOptions($request, $runtime);
    }

    /**
     * @param CheckMobilesCardSupportRequest $request
     * @param RuntimeOptions                 $runtime
     *
     * @return CheckMobilesCardSupportResponse
     */
    public function checkMobilesCardSupportWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->mobiles)) {
            $query['Mobiles'] = $request->mobiles;
        }
        if (!Utils::isUnset($request->templateCode)) {
            $query['TemplateCode'] = $request->templateCode;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'CheckMobilesCardSupport',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return CheckMobilesCardSupportResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param CheckMobilesCardSupportRequest $request
     *
     * @return CheckMobilesCardSupportResponse
     */
    public function checkMobilesCardSupport($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->checkMobilesCardSupportWithOptions($request, $runtime);
    }

    /**
     * @param CreateCardSmsTemplateRequest $tmpReq
     * @param RuntimeOptions               $runtime
     *
     * @return CreateCardSmsTemplateResponse
     */
    public function createCardSmsTemplateWithOptions($tmpReq, $runtime)
    {
        Utils::validateModel($tmpReq);
        $request = new CreateCardSmsTemplateShrinkRequest([]);
        OpenApiUtilClient::convert($tmpReq, $request);
        if (!Utils::isUnset($tmpReq->template)) {
            $request->templateShrink = OpenApiUtilClient::arrayToStringWithSpecifiedStyle($tmpReq->template, 'Template', 'json');
        }
        $query = [];
        if (!Utils::isUnset($request->factorys)) {
            $query['Factorys'] = $request->factorys;
        }
        if (!Utils::isUnset($request->memo)) {
            $query['Memo'] = $request->memo;
        }
        if (!Utils::isUnset($request->templateShrink)) {
            $query['Template'] = $request->templateShrink;
        }
        if (!Utils::isUnset($request->templateName)) {
            $query['TemplateName'] = $request->templateName;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'CreateCardSmsTemplate',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return CreateCardSmsTemplateResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param CreateCardSmsTemplateRequest $request
     *
     * @return CreateCardSmsTemplateResponse
     */
    public function createCardSmsTemplate($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->createCardSmsTemplateWithOptions($request, $runtime);
    }

    /**
     * @param DeleteShortUrlRequest $request
     * @param RuntimeOptions        $runtime
     *
     * @return DeleteShortUrlResponse
     */
    public function deleteShortUrlWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        $body = [];
        if (!Utils::isUnset($request->sourceUrl)) {
            $body['SourceUrl'] = $request->sourceUrl;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
            'body'  => OpenApiUtilClient::parseToMap($body),
        ]);
        $params = new Params([
            'action'      => 'DeleteShortUrl',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return DeleteShortUrlResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param DeleteShortUrlRequest $request
     *
     * @return DeleteShortUrlResponse
     */
    public function deleteShortUrl($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->deleteShortUrlWithOptions($request, $runtime);
    }

    /**
     * @param DeleteSmsSignRequest $request
     * @param RuntimeOptions       $runtime
     *
     * @return DeleteSmsSignResponse
     */
    public function deleteSmsSignWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->signName)) {
            $query['SignName'] = $request->signName;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'DeleteSmsSign',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return DeleteSmsSignResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param DeleteSmsSignRequest $request
     *
     * @return DeleteSmsSignResponse
     */
    public function deleteSmsSign($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->deleteSmsSignWithOptions($request, $runtime);
    }

    /**
     * @param DeleteSmsTemplateRequest $request
     * @param RuntimeOptions           $runtime
     *
     * @return DeleteSmsTemplateResponse
     */
    public function deleteSmsTemplateWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->templateCode)) {
            $query['TemplateCode'] = $request->templateCode;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'DeleteSmsTemplate',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return DeleteSmsTemplateResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param DeleteSmsTemplateRequest $request
     *
     * @return DeleteSmsTemplateResponse
     */
    public function deleteSmsTemplate($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->deleteSmsTemplateWithOptions($request, $runtime);
    }

    /**
     * @param GetCardSmsLinkRequest $request
     * @param RuntimeOptions        $runtime
     *
     * @return GetCardSmsLinkResponse
     */
    public function getCardSmsLinkWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->cardCodeType)) {
            $query['CardCodeType'] = $request->cardCodeType;
        }
        if (!Utils::isUnset($request->cardLinkType)) {
            $query['CardLinkType'] = $request->cardLinkType;
        }
        if (!Utils::isUnset($request->cardTemplateCode)) {
            $query['CardTemplateCode'] = $request->cardTemplateCode;
        }
        if (!Utils::isUnset($request->cardTemplateParamJson)) {
            $query['CardTemplateParamJson'] = $request->cardTemplateParamJson;
        }
        if (!Utils::isUnset($request->customShortCodeJson)) {
            $query['CustomShortCodeJson'] = $request->customShortCodeJson;
        }
        if (!Utils::isUnset($request->domain)) {
            $query['Domain'] = $request->domain;
        }
        if (!Utils::isUnset($request->outId)) {
            $query['OutId'] = $request->outId;
        }
        if (!Utils::isUnset($request->phoneNumberJson)) {
            $query['PhoneNumberJson'] = $request->phoneNumberJson;
        }
        if (!Utils::isUnset($request->signNameJson)) {
            $query['SignNameJson'] = $request->signNameJson;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'GetCardSmsLink',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return GetCardSmsLinkResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param GetCardSmsLinkRequest $request
     *
     * @return GetCardSmsLinkResponse
     */
    public function getCardSmsLink($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->getCardSmsLinkWithOptions($request, $runtime);
    }

    /**
     * @param GetMediaResourceIdRequest $request
     * @param RuntimeOptions            $runtime
     *
     * @return GetMediaResourceIdResponse
     */
    public function getMediaResourceIdWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->extendInfo)) {
            $query['ExtendInfo'] = $request->extendInfo;
        }
        if (!Utils::isUnset($request->fileSize)) {
            $query['FileSize'] = $request->fileSize;
        }
        if (!Utils::isUnset($request->memo)) {
            $query['Memo'] = $request->memo;
        }
        if (!Utils::isUnset($request->ossKey)) {
            $query['OssKey'] = $request->ossKey;
        }
        if (!Utils::isUnset($request->resourceType)) {
            $query['ResourceType'] = $request->resourceType;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'GetMediaResourceId',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return GetMediaResourceIdResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param GetMediaResourceIdRequest $request
     *
     * @return GetMediaResourceIdResponse
     */
    public function getMediaResourceId($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->getMediaResourceIdWithOptions($request, $runtime);
    }

    /**
     * @param RuntimeOptions $runtime
     *
     * @return GetOSSInfoForCardTemplateResponse
     */
    public function getOSSInfoForCardTemplateWithOptions($runtime)
    {
        $req    = new OpenApiRequest([]);
        $params = new Params([
            'action'      => 'GetOSSInfoForCardTemplate',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return GetOSSInfoForCardTemplateResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @return GetOSSInfoForCardTemplateResponse
     */
    public function getOSSInfoForCardTemplate()
    {
        $runtime = new RuntimeOptions([]);

        return $this->getOSSInfoForCardTemplateWithOptions($runtime);
    }

    /**
     * @param ListTagResourcesRequest $request
     * @param RuntimeOptions          $runtime
     *
     * @return ListTagResourcesResponse
     */
    public function listTagResourcesWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->nextToken)) {
            $query['NextToken'] = $request->nextToken;
        }
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->pageSize)) {
            $query['PageSize'] = $request->pageSize;
        }
        if (!Utils::isUnset($request->prodCode)) {
            $query['ProdCode'] = $request->prodCode;
        }
        if (!Utils::isUnset($request->regionId)) {
            $query['RegionId'] = $request->regionId;
        }
        if (!Utils::isUnset($request->resourceId)) {
            $query['ResourceId'] = $request->resourceId;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->resourceType)) {
            $query['ResourceType'] = $request->resourceType;
        }
        if (!Utils::isUnset($request->tag)) {
            $query['Tag'] = $request->tag;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'ListTagResources',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return ListTagResourcesResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param ListTagResourcesRequest $request
     *
     * @return ListTagResourcesResponse
     */
    public function listTagResources($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->listTagResourcesWithOptions($request, $runtime);
    }

    /**
     * @param ModifySmsSignRequest $request
     * @param RuntimeOptions       $runtime
     *
     * @return ModifySmsSignResponse
     */
    public function modifySmsSignWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->remark)) {
            $query['Remark'] = $request->remark;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->signName)) {
            $query['SignName'] = $request->signName;
        }
        if (!Utils::isUnset($request->signSource)) {
            $query['SignSource'] = $request->signSource;
        }
        if (!Utils::isUnset($request->signType)) {
            $query['SignType'] = $request->signType;
        }
        $body = [];
        if (!Utils::isUnset($request->signFileList)) {
            $body['SignFileList'] = $request->signFileList;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
            'body'  => OpenApiUtilClient::parseToMap($body),
        ]);
        $params = new Params([
            'action'      => 'ModifySmsSign',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return ModifySmsSignResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param ModifySmsSignRequest $request
     *
     * @return ModifySmsSignResponse
     */
    public function modifySmsSign($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->modifySmsSignWithOptions($request, $runtime);
    }

    /**
     * @param ModifySmsTemplateRequest $request
     * @param RuntimeOptions           $runtime
     *
     * @return ModifySmsTemplateResponse
     */
    public function modifySmsTemplateWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->remark)) {
            $query['Remark'] = $request->remark;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->templateCode)) {
            $query['TemplateCode'] = $request->templateCode;
        }
        if (!Utils::isUnset($request->templateContent)) {
            $query['TemplateContent'] = $request->templateContent;
        }
        if (!Utils::isUnset($request->templateName)) {
            $query['TemplateName'] = $request->templateName;
        }
        if (!Utils::isUnset($request->templateType)) {
            $query['TemplateType'] = $request->templateType;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'ModifySmsTemplate',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return ModifySmsTemplateResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param ModifySmsTemplateRequest $request
     *
     * @return ModifySmsTemplateResponse
     */
    public function modifySmsTemplate($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->modifySmsTemplateWithOptions($request, $runtime);
    }

    /**
     * @param QueryCardSmsTemplateRequest $request
     * @param RuntimeOptions              $runtime
     *
     * @return QueryCardSmsTemplateResponse
     */
    public function queryCardSmsTemplateWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->templateCode)) {
            $query['TemplateCode'] = $request->templateCode;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'QueryCardSmsTemplate',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return QueryCardSmsTemplateResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param QueryCardSmsTemplateRequest $request
     *
     * @return QueryCardSmsTemplateResponse
     */
    public function queryCardSmsTemplate($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->queryCardSmsTemplateWithOptions($request, $runtime);
    }

    /**
     * @param QueryCardSmsTemplateReportRequest $request
     * @param RuntimeOptions                    $runtime
     *
     * @return QueryCardSmsTemplateReportResponse
     */
    public function queryCardSmsTemplateReportWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->endDate)) {
            $query['EndDate'] = $request->endDate;
        }
        if (!Utils::isUnset($request->startDate)) {
            $query['StartDate'] = $request->startDate;
        }
        if (!Utils::isUnset($request->templateCodes)) {
            $query['TemplateCodes'] = $request->templateCodes;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'QueryCardSmsTemplateReport',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return QueryCardSmsTemplateReportResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param QueryCardSmsTemplateReportRequest $request
     *
     * @return QueryCardSmsTemplateReportResponse
     */
    public function queryCardSmsTemplateReport($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->queryCardSmsTemplateReportWithOptions($request, $runtime);
    }

    /**
     * @param QuerySendDetailsRequest $request
     * @param RuntimeOptions          $runtime
     *
     * @return QuerySendDetailsResponse
     */
    public function querySendDetailsWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->bizId)) {
            $query['BizId'] = $request->bizId;
        }
        if (!Utils::isUnset($request->currentPage)) {
            $query['CurrentPage'] = $request->currentPage;
        }
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->pageSize)) {
            $query['PageSize'] = $request->pageSize;
        }
        if (!Utils::isUnset($request->phoneNumber)) {
            $query['PhoneNumber'] = $request->phoneNumber;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->sendDate)) {
            $query['SendDate'] = $request->sendDate;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'QuerySendDetails',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return QuerySendDetailsResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param QuerySendDetailsRequest $request
     *
     * @return QuerySendDetailsResponse
     */
    public function querySendDetails($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->querySendDetailsWithOptions($request, $runtime);
    }

    /**
     * @param QuerySendStatisticsRequest $request
     * @param RuntimeOptions             $runtime
     *
     * @return QuerySendStatisticsResponse
     */
    public function querySendStatisticsWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->endDate)) {
            $query['EndDate'] = $request->endDate;
        }
        if (!Utils::isUnset($request->isGlobe)) {
            $query['IsGlobe'] = $request->isGlobe;
        }
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->pageIndex)) {
            $query['PageIndex'] = $request->pageIndex;
        }
        if (!Utils::isUnset($request->pageSize)) {
            $query['PageSize'] = $request->pageSize;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->startDate)) {
            $query['StartDate'] = $request->startDate;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'QuerySendStatistics',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return QuerySendStatisticsResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param QuerySendStatisticsRequest $request
     *
     * @return QuerySendStatisticsResponse
     */
    public function querySendStatistics($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->querySendStatisticsWithOptions($request, $runtime);
    }

    /**
     * @param QueryShortUrlRequest $request
     * @param RuntimeOptions       $runtime
     *
     * @return QueryShortUrlResponse
     */
    public function queryShortUrlWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        $body = [];
        if (!Utils::isUnset($request->shortUrl)) {
            $body['ShortUrl'] = $request->shortUrl;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
            'body'  => OpenApiUtilClient::parseToMap($body),
        ]);
        $params = new Params([
            'action'      => 'QueryShortUrl',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return QueryShortUrlResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param QueryShortUrlRequest $request
     *
     * @return QueryShortUrlResponse
     */
    public function queryShortUrl($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->queryShortUrlWithOptions($request, $runtime);
    }

    /**
     * @param QuerySmsSignRequest $request
     * @param RuntimeOptions      $runtime
     *
     * @return QuerySmsSignResponse
     */
    public function querySmsSignWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->signName)) {
            $query['SignName'] = $request->signName;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'QuerySmsSign',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return QuerySmsSignResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param QuerySmsSignRequest $request
     *
     * @return QuerySmsSignResponse
     */
    public function querySmsSign($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->querySmsSignWithOptions($request, $runtime);
    }

    /**
     * @param QuerySmsSignListRequest $request
     * @param RuntimeOptions          $runtime
     *
     * @return QuerySmsSignListResponse
     */
    public function querySmsSignListWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->pageIndex)) {
            $query['PageIndex'] = $request->pageIndex;
        }
        if (!Utils::isUnset($request->pageSize)) {
            $query['PageSize'] = $request->pageSize;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'QuerySmsSignList',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return QuerySmsSignListResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param QuerySmsSignListRequest $request
     *
     * @return QuerySmsSignListResponse
     */
    public function querySmsSignList($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->querySmsSignListWithOptions($request, $runtime);
    }

    /**
     * @param QuerySmsTemplateRequest $request
     * @param RuntimeOptions          $runtime
     *
     * @return QuerySmsTemplateResponse
     */
    public function querySmsTemplateWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->templateCode)) {
            $query['TemplateCode'] = $request->templateCode;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'QuerySmsTemplate',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return QuerySmsTemplateResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param QuerySmsTemplateRequest $request
     *
     * @return QuerySmsTemplateResponse
     */
    public function querySmsTemplate($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->querySmsTemplateWithOptions($request, $runtime);
    }

    /**
     * @param QuerySmsTemplateListRequest $request
     * @param RuntimeOptions              $runtime
     *
     * @return QuerySmsTemplateListResponse
     */
    public function querySmsTemplateListWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->pageIndex)) {
            $query['PageIndex'] = $request->pageIndex;
        }
        if (!Utils::isUnset($request->pageSize)) {
            $query['PageSize'] = $request->pageSize;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'QuerySmsTemplateList',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return QuerySmsTemplateListResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param QuerySmsTemplateListRequest $request
     *
     * @return QuerySmsTemplateListResponse
     */
    public function querySmsTemplateList($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->querySmsTemplateListWithOptions($request, $runtime);
    }

    /**
     * @param SendBatchCardSmsRequest $request
     * @param RuntimeOptions          $runtime
     *
     * @return SendBatchCardSmsResponse
     */
    public function sendBatchCardSmsWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->cardTemplateCode)) {
            $query['CardTemplateCode'] = $request->cardTemplateCode;
        }
        if (!Utils::isUnset($request->cardTemplateParamJson)) {
            $query['CardTemplateParamJson'] = $request->cardTemplateParamJson;
        }
        if (!Utils::isUnset($request->digitalTemplateCode)) {
            $query['DigitalTemplateCode'] = $request->digitalTemplateCode;
        }
        if (!Utils::isUnset($request->digitalTemplateParamJson)) {
            $query['DigitalTemplateParamJson'] = $request->digitalTemplateParamJson;
        }
        if (!Utils::isUnset($request->fallbackType)) {
            $query['FallbackType'] = $request->fallbackType;
        }
        if (!Utils::isUnset($request->outId)) {
            $query['OutId'] = $request->outId;
        }
        if (!Utils::isUnset($request->phoneNumberJson)) {
            $query['PhoneNumberJson'] = $request->phoneNumberJson;
        }
        if (!Utils::isUnset($request->signNameJson)) {
            $query['SignNameJson'] = $request->signNameJson;
        }
        if (!Utils::isUnset($request->smsTemplateCode)) {
            $query['SmsTemplateCode'] = $request->smsTemplateCode;
        }
        if (!Utils::isUnset($request->smsTemplateParamJson)) {
            $query['SmsTemplateParamJson'] = $request->smsTemplateParamJson;
        }
        if (!Utils::isUnset($request->smsUpExtendCodeJson)) {
            $query['SmsUpExtendCodeJson'] = $request->smsUpExtendCodeJson;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'SendBatchCardSms',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return SendBatchCardSmsResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param SendBatchCardSmsRequest $request
     *
     * @return SendBatchCardSmsResponse
     */
    public function sendBatchCardSms($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->sendBatchCardSmsWithOptions($request, $runtime);
    }

    /**
     * @param SendBatchSmsRequest $request
     * @param RuntimeOptions      $runtime
     *
     * @return SendBatchSmsResponse
     */
    public function sendBatchSmsWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->phoneNumberJson)) {
            $query['PhoneNumberJson'] = $request->phoneNumberJson;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->signNameJson)) {
            $query['SignNameJson'] = $request->signNameJson;
        }
        if (!Utils::isUnset($request->smsUpExtendCodeJson)) {
            $query['SmsUpExtendCodeJson'] = $request->smsUpExtendCodeJson;
        }
        if (!Utils::isUnset($request->templateCode)) {
            $query['TemplateCode'] = $request->templateCode;
        }
        if (!Utils::isUnset($request->templateParamJson)) {
            $query['TemplateParamJson'] = $request->templateParamJson;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'SendBatchSms',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return SendBatchSmsResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param SendBatchSmsRequest $request
     *
     * @return SendBatchSmsResponse
     */
    public function sendBatchSms($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->sendBatchSmsWithOptions($request, $runtime);
    }

    /**
     * @param SendCardSmsRequest $request
     * @param RuntimeOptions     $runtime
     *
     * @return SendCardSmsResponse
     */
    public function sendCardSmsWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->cardObjects)) {
            $query['CardObjects'] = $request->cardObjects;
        }
        if (!Utils::isUnset($request->cardTemplateCode)) {
            $query['CardTemplateCode'] = $request->cardTemplateCode;
        }
        if (!Utils::isUnset($request->digitalTemplateCode)) {
            $query['DigitalTemplateCode'] = $request->digitalTemplateCode;
        }
        if (!Utils::isUnset($request->digitalTemplateParam)) {
            $query['DigitalTemplateParam'] = $request->digitalTemplateParam;
        }
        if (!Utils::isUnset($request->fallbackType)) {
            $query['FallbackType'] = $request->fallbackType;
        }
        if (!Utils::isUnset($request->outId)) {
            $query['OutId'] = $request->outId;
        }
        if (!Utils::isUnset($request->signName)) {
            $query['SignName'] = $request->signName;
        }
        if (!Utils::isUnset($request->smsTemplateCode)) {
            $query['SmsTemplateCode'] = $request->smsTemplateCode;
        }
        if (!Utils::isUnset($request->smsTemplateParam)) {
            $query['SmsTemplateParam'] = $request->smsTemplateParam;
        }
        if (!Utils::isUnset($request->smsUpExtendCode)) {
            $query['SmsUpExtendCode'] = $request->smsUpExtendCode;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'SendCardSms',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return SendCardSmsResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param SendCardSmsRequest $request
     *
     * @return SendCardSmsResponse
     */
    public function sendCardSms($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->sendCardSmsWithOptions($request, $runtime);
    }

    /**
     * @param SendSmsRequest $request
     * @param RuntimeOptions $runtime
     *
     * @return SendSmsResponse
     */
    public function sendSmsWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->outId)) {
            $query['OutId'] = $request->outId;
        }
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->phoneNumbers)) {
            $query['PhoneNumbers'] = $request->phoneNumbers;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->signName)) {
            $query['SignName'] = $request->signName;
        }
        if (!Utils::isUnset($request->smsUpExtendCode)) {
            $query['SmsUpExtendCode'] = $request->smsUpExtendCode;
        }
        if (!Utils::isUnset($request->templateCode)) {
            $query['TemplateCode'] = $request->templateCode;
        }
        if (!Utils::isUnset($request->templateParam)) {
            $query['TemplateParam'] = $request->templateParam;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'SendSms',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return SendSmsResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param SendSmsRequest $request
     *
     * @return SendSmsResponse
     */
    public function sendSms($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->sendSmsWithOptions($request, $runtime);
    }

    /**
     * @param TagResourcesRequest $request
     * @param RuntimeOptions      $runtime
     *
     * @return TagResourcesResponse
     */
    public function tagResourcesWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->prodCode)) {
            $query['ProdCode'] = $request->prodCode;
        }
        if (!Utils::isUnset($request->regionId)) {
            $query['RegionId'] = $request->regionId;
        }
        if (!Utils::isUnset($request->resourceId)) {
            $query['ResourceId'] = $request->resourceId;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->resourceType)) {
            $query['ResourceType'] = $request->resourceType;
        }
        if (!Utils::isUnset($request->tag)) {
            $query['Tag'] = $request->tag;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'TagResources',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return TagResourcesResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param TagResourcesRequest $request
     *
     * @return TagResourcesResponse
     */
    public function tagResources($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->tagResourcesWithOptions($request, $runtime);
    }

    /**
     * @param UntagResourcesRequest $request
     * @param RuntimeOptions        $runtime
     *
     * @return UntagResourcesResponse
     */
    public function untagResourcesWithOptions($request, $runtime)
    {
        Utils::validateModel($request);
        $query = [];
        if (!Utils::isUnset($request->all)) {
            $query['All'] = $request->all;
        }
        if (!Utils::isUnset($request->ownerId)) {
            $query['OwnerId'] = $request->ownerId;
        }
        if (!Utils::isUnset($request->prodCode)) {
            $query['ProdCode'] = $request->prodCode;
        }
        if (!Utils::isUnset($request->regionId)) {
            $query['RegionId'] = $request->regionId;
        }
        if (!Utils::isUnset($request->resourceId)) {
            $query['ResourceId'] = $request->resourceId;
        }
        if (!Utils::isUnset($request->resourceOwnerAccount)) {
            $query['ResourceOwnerAccount'] = $request->resourceOwnerAccount;
        }
        if (!Utils::isUnset($request->resourceOwnerId)) {
            $query['ResourceOwnerId'] = $request->resourceOwnerId;
        }
        if (!Utils::isUnset($request->resourceType)) {
            $query['ResourceType'] = $request->resourceType;
        }
        if (!Utils::isUnset($request->tagKey)) {
            $query['TagKey'] = $request->tagKey;
        }
        $req = new OpenApiRequest([
            'query' => OpenApiUtilClient::query($query),
        ]);
        $params = new Params([
            'action'      => 'UntagResources',
            'version'     => '2017-05-25',
            'protocol'    => 'HTTPS',
            'pathname'    => '/',
            'method'      => 'POST',
            'authType'    => 'AK',
            'style'       => 'RPC',
            'reqBodyType' => 'formData',
            'bodyType'    => 'json',
        ]);

        return UntagResourcesResponse::fromMap($this->callApi($params, $req, $runtime));
    }

    /**
     * @param UntagResourcesRequest $request
     *
     * @return UntagResourcesResponse
     */
    public function untagResources($request)
    {
        $runtime = new RuntimeOptions([]);

        return $this->untagResourcesWithOptions($request, $runtime);
    }
}
