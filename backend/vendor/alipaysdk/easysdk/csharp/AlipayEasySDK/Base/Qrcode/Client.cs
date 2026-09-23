// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections;
using System.Collections.Generic;
using System.IO;
using System.Threading.Tasks;

using Tea;
using Tea.Utils;

using Alipay.EasySDK.Base.Qrcode.Models;

namespace Alipay.EasySDK.Base.Qrcode
{
    public class Client 
    {
        protected Alipay.EasySDK.Kernel.Client _kernel;

        public Client(Alipay.EasySDK.Kernel.Client kernel)
        {
            this._kernel = kernel;
        }

        public AlipayOpenAppQrcodeCreateResponse Create(string urlParam, string queryParam, string describe)
        {
            Dictionary<string, object> runtime_ = new Dictionary<string, object>
            {
                {"ignoreSSL", this._kernel.GetConfig("ignoreSSL")},
                {"httpProxy", this._kernel.GetConfig("httpProxy")},
                {"connectTimeout", 15000},
                {"readTimeout", 15000},
                {"retry", new Dictionary<string, int?>
                {
                    {"maxAttempts", 0},
                }},
            };

            TeaRequest _lastRequest = null;
            Exception _lastException = null;
            long _now = System.DateTime.Now.Millisecond;
            int _retryTimes = 0;
            while (TeaCore.AllowRetry((IDictionary) runtime_["retry"], _retryTimes, _now))
            {
                if (_retryTimes > 0)
                {
                    int backoffTime = TeaCore.GetBackoffTime((IDictionary)runtime_["backoff"], _retryTimes);
                    if (backoffTime > 0)
                    {
                        TeaCore.Sleep(backoffTime);
                    }
                }
                _retryTimes = _retryTimes + 1;
                try
                {
                    TeaRequest request_ = new TeaRequest();
                    Dictionary<string, string> systemParams = new Dictionary<string, string>
                    {
                        {"method", "alipay.open.app.qrcode.create"},
                        {"app_id", this._kernel.GetConfig("appId")},
                        {"timestamp", this._kernel.GetTimestamp()},
                        {"format", "json"},
                        {"version", "1.0"},
                        {"alipay_sdk", this._kernel.GetSdkVersion()},
                        {"charset", "UTF-8"},
                        {"sign_type", this._kernel.GetConfig("signType")},
                        {"app_cert_sn", this._kernel.GetMerchantCertSN()},
                        {"alipay_root_cert_sn", this._kernel.GetAlipayRootCertSN()},
                    };
                    Dictionary<string, object> bizParams = new Dictionary<string, object>
                    {
                        {"url_param", urlParam},
                        {"query_param", queryParam},
                        {"describe", describe},
                    };
                    Dictionary<string, string> textParams = new Dictionary<string, string>(){};
                    request_.Protocol = this._kernel.GetConfig("protocol");
                    request_.Method = "POST";
                    request_.Pathname = "/gateway.do";
                    request_.Headers = new Dictionary<string, string>
                    {
                        {"host", this._kernel.GetConfig("gatewayHost")},
                        {"content-type", "application/x-www-form-urlencoded;charset=utf-8"},
                    };
                    request_.Query = this._kernel.SortMap(TeaConverter.merge<string>
                    (
                        new Dictionary<string, string>()
                        {
                            {"sign", this._kernel.Sign(systemParams, bizParams, textParams, this._kernel.GetConfig("merchantPrivateKey"))},
                        },
                        systemParams,
                        textParams
                    ));
                    request_.Body = TeaCore.BytesReadable(this._kernel.ToUrlEncodedRequestBody(bizParams));
                    _lastRequest = request_;
                    TeaResponse response_ = TeaCore.DoAction(request_, runtime_);

                    Dictionary<string, object> respMap = this._kernel.ReadAsJson(response_, "alipay.open.app.qrcode.create");
                    if (this._kernel.IsCertMode())
                    {
                        if (this._kernel.Verify(respMap, this._kernel.ExtractAlipayPublicKey(this._kernel.GetAlipayCertSN(respMap))))
                        {
                            return TeaModel.ToObject<AlipayOpenAppQrcodeCreateResponse>(this._kernel.ToRespModel(respMap));
                        }
                    }
                    else
                    {
                        if (this._kernel.Verify(respMap, this._kernel.GetConfig("alipayPublicKey")))
                        {
                            return TeaModel.ToObject<AlipayOpenAppQrcodeCreateResponse>(this._kernel.ToRespModel(respMap));
                        }
                    }
                    throw new TeaException(new Dictionary<string, string>
                    {
                        {"message", "验签失败，请检查支付宝公钥设置是否正确。"},
                    });
                }
                catch (Exception e)
                {
                    if (TeaCore.IsRetryable(e))
                    {
                        _lastException = e;
                        continue;
                    }
                    throw e;
                }
            }

            throw new TeaUnretryableException(_lastRequest, _lastException);
        }

        public async Task<AlipayOpenAppQrcodeCreateResponse> CreateAsync(string urlParam, string queryParam, string describe)
        {
            Dictionary<string, object> runtime_ = new Dictionary<string, object>
            {
                {"ignoreSSL", this._kernel.GetConfig("ignoreSSL")},
                {"httpProxy", this._kernel.GetConfig("httpProxy")},
                {"connectTimeout", 15000},
                {"readTimeout", 15000},
                {"retry", new Dictionary<string, int?>
                {
                    {"maxAttempts", 0},
                }},
            };

            TeaRequest _lastRequest = null;
            Exception _lastException = null;
            long _now = System.DateTime.Now.Millisecond;
            int _retryTimes = 0;
            while (TeaCore.AllowRetry((IDictionary) runtime_["retry"], _retryTimes, _now))
            {
                if (_retryTimes > 0)
                {
                    int backoffTime = TeaCore.GetBackoffTime((IDictionary)runtime_["backoff"], _retryTimes);
                    if (backoffTime > 0)
                    {
                        TeaCore.Sleep(backoffTime);
                    }
                }
                _retryTimes = _retryTimes + 1;
                try
                {
                    TeaRequest request_ = new TeaRequest();
                    Dictionary<string, string> systemParams = new Dictionary<string, string>
                    {
                        {"method", "alipay.open.app.qrcode.create"},
                        {"app_id", this._kernel.GetConfig("appId")},
                        {"timestamp", this._kernel.GetTimestamp()},
                        {"format", "json"},
                        {"version", "1.0"},
                        {"alipay_sdk", this._kernel.GetSdkVersion()},
                        {"charset", "UTF-8"},
                        {"sign_type", this._kernel.GetConfig("signType")},
                        {"app_cert_sn", this._kernel.GetMerchantCertSN()},
                        {"alipay_root_cert_sn", this._kernel.GetAlipayRootCertSN()},
                    };
                    Dictionary<string, object> bizParams = new Dictionary<string, object>
                    {
                        {"url_param", urlParam},
                        {"query_param", queryParam},
                        {"describe", describe},
                    };
                    Dictionary<string, string> textParams = new Dictionary<string, string>(){};
                    request_.Protocol = this._kernel.GetConfig("protocol");
                    request_.Method = "POST";
                    request_.Pathname = "/gateway.do";
                    request_.Headers = new Dictionary<string, string>
                    {
                        {"host", this._kernel.GetConfig("gatewayHost")},
                        {"content-type", "application/x-www-form-urlencoded;charset=utf-8"},
                    };
                    request_.Query = this._kernel.SortMap(TeaConverter.merge<string>
                    (
                        new Dictionary<string, string>()
                        {
                            {"sign", this._kernel.Sign(systemParams, bizParams, textParams, this._kernel.GetConfig("merchantPrivateKey"))},
                        },
                        systemParams,
                        textParams
                    ));
                    request_.Body = TeaCore.BytesReadable(this._kernel.ToUrlEncodedRequestBody(bizParams));
                    _lastRequest = request_;
                    TeaResponse response_ = await TeaCore.DoActionAsync(request_, runtime_);

                    Dictionary<string, object> respMap = await this._kernel.ReadAsJsonAsync(response_, "alipay.open.app.qrcode.create");
                    if (this._kernel.IsCertMode())
                    {
                        if (this._kernel.Verify(respMap, this._kernel.ExtractAlipayPublicKey(this._kernel.GetAlipayCertSN(respMap))))
                        {
                            return TeaModel.ToObject<AlipayOpenAppQrcodeCreateResponse>(this._kernel.ToRespModel(respMap));
                        }
                    }
                    else
                    {
                        if (this._kernel.Verify(respMap, this._kernel.GetConfig("alipayPublicKey")))
                        {
                            return TeaModel.ToObject<AlipayOpenAppQrcodeCreateResponse>(this._kernel.ToRespModel(respMap));
                        }
                    }
                    throw new TeaException(new Dictionary<string, string>
                    {
                        {"message", "验签失败，请检查支付宝公钥设置是否正确。"},
                    });
                }
                catch (Exception e)
                {
                    if (TeaCore.IsRetryable(e))
                    {
                        _lastException = e;
                        continue;
                    }
                    throw e;
                }
            }

            throw new TeaUnretryableException(_lastRequest, _lastException);
        }

        
        /// <summary>
        /// ISV代商户代用，指定appAuthToken
        /// </summary>
        /// <param name="appAuthToken">代调用token</param>
        /// <returns>本客户端，便于链式调用</returns>
        public Client Agent(string appAuthToken)
        {
            _kernel.InjectTextParam("app_auth_token", appAuthToken);
            return this;
        }

        /// <summary>
        /// 用户授权调用，指定authToken
        /// </summary>
        /// <param name="authToken">用户授权token</param>
        /// <returns>本客户端，便于链式调用</returns>
        public Client Auth(string authToken)
        {
            _kernel.InjectTextParam("auth_token", authToken);
            return this;
        }

        /// <summary>
        /// 设置异步通知回调地址，此处设置将在本调用中覆盖Config中的全局配置
        /// </summary>
        /// <param name="url">异步通知回调地址，例如：https://www.test.com/callback </param>
        /// <returns>本客户端，便于链式调用</returns>
        public Client AsyncNotify(string url)
        {
            _kernel.InjectTextParam("notify_url", url);
            return this;
        }

        /// <summary>
        /// 将本次调用强制路由到后端系统的测试地址上，常用于线下环境内外联调，沙箱与线上环境设置无效
        /// </summary>
        /// <param name="testUrl">后端系统测试地址</param>
        /// <returns>本客户端，便于链式调用</returns>
        public Client Route(string testUrl)
        {
            _kernel.InjectTextParam("ws_service_url", testUrl);
            return this;
        }

        /// <summary>
        /// 设置API入参中没有的其他可选业务请求参数(biz_content下的字段)
        /// </summary>
        /// <param name="key">业务请求参数名称（biz_content下的字段名，比如timeout_express）</param>
        /// <param name="value">
        /// 业务请求参数的值，一个可以序列化成JSON的对象
        /// 如果该字段是一个字符串类型（String、Price、Date在SDK中都是字符串），请使用string储存
        /// 如果该字段是一个数值型类型（比如：Number），请使用long储存
        /// 如果该字段是一个复杂类型，请使用嵌套的Dictionary指定各下级字段的值
        /// 如果该字段是一个数组，请使用List储存各个值
        /// 对于更复杂的情况，也支持Dictionary和List的各种组合嵌套，比如参数是值是个List，List中的每种类型是一个复杂对象
        /// </param>
        /// <returns>本客户端，便于链式调用</returns>
        public Client Optional(string key, object value)
        {
            _kernel.InjectBizParam(key, value);
            return this;
        }

        /// <summary>
        /// 批量设置API入参中没有的其他可选业务请求参数(biz_content下的字段)
        /// optional方法的批量版本
        /// </summary>
        /// <param name="optionalArgs">可选参数集合，每个参数由key和value组成，key和value的格式请参见optional方法的注释</param>
        /// <returns>本客户端，便于链式调用</returns>
        public Client BatchOptional(Dictionary<string, object> optionalArgs)
        {
            foreach (var pair in optionalArgs)
            {
                _kernel.InjectBizParam(pair.Key, pair.Value);
            }
            return this;
        }
    }
}