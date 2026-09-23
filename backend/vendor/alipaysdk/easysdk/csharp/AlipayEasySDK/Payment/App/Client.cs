// This file is auto-generated, don't edit it. Thanks.

using System;
using System.Collections;
using System.Collections.Generic;
using System.IO;
using System.Threading.Tasks;

using Tea;
using Tea.Utils;

using Alipay.EasySDK.Payment.App.Models;

namespace Alipay.EasySDK.Payment.App
{
    public class Client 
    {
        protected Alipay.EasySDK.Kernel.Client _kernel;

        public Client(Alipay.EasySDK.Kernel.Client kernel)
        {
            this._kernel = kernel;
        }


        public AlipayTradeAppPayResponse Pay(string subject, string outTradeNo, string totalAmount)
        {
            Dictionary<string, string> systemParams = new Dictionary<string, string>
            {
                {"method", "alipay.trade.app.pay"},
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
                {"subject", subject},
                {"out_trade_no", outTradeNo},
                {"total_amount", totalAmount},
            };
            Dictionary<string, string> textParams = new Dictionary<string, string>(){};
            string sign = this._kernel.Sign(systemParams, bizParams, textParams, this._kernel.GetConfig("merchantPrivateKey"));
            Dictionary<string, string> response = new Dictionary<string, string>
            {
                {"body", this._kernel.GenerateOrderString(systemParams, bizParams, textParams, sign)},
            };
            return TeaModel.ToObject<AlipayTradeAppPayResponse>(response);
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