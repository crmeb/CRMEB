using System;
using System.Collections.Generic;
using System.Text;
using System.Web;
using System.IO;
using System.Threading.Tasks;
using Newtonsoft.Json;

using Alipay.EasySDK.Kernel.Util;

using Tea;

namespace Alipay.EasySDK.Kernel
{
    /// <summary>
    /// Tea DSL编排所需实现的原子方法
    /// </summary>
    public class Client
    {
        /// <summary>
        /// 构造成本较高的一些参数缓存在上下文中
        /// </summary>
        private readonly Context context;

        /// <summary>
        /// 注入的可选额外文本参数集合
        /// </summary>
        private readonly Dictionary<string, string> optionalTextParams = new Dictionary<string, string>();

        /// <summary>
        /// 注入的可选业务参数集合
        /// </summary>
        private readonly Dictionary<string, object> optionalBizParams = new Dictionary<string, object>();

        /// <summary>
        /// 构造函数
        /// </summary>
        /// <param name="context">上下文对象</param>
        public Client(Context context)
        {
            this.context = context;
        }

        /// <summary>
        /// 注入额外文本参数
        /// </summary>
        /// <param name="key">参数名称</param>
        /// <param name="value">参数的值</param>
        /// <returns>本客户端本身，便于链路调用</returns>
        public Client InjectTextParam(String key, String value)
        {
            optionalTextParams.Add(key, value);
            return this;
        }

        /// <summary>
        /// 注入额外业务参数
        /// </summary>
        /// <param name="key">参数名称</param>
        /// <param name="value">参数的值</param>
        /// <returns>本客户端本身，便于链式调用</returns>
        public Client InjectBizParam(String key, Object value)
        {
            optionalBizParams.Add(key, value);
            return this;
        }

        /// <summary>
        /// 获取Config中的配置项
        /// </summary>
        /// <param name="key">配置项的名称</param>
        /// <returns>配置项的值</returns>
        public string GetConfig(string key)
        {
            return context.GetConfig(key);
        }

        /// <summary>
        /// 是否是证书模式
        /// </summary>
        /// <returns>true：是；false：不是</returns>
        public bool IsCertMode()
        {
            return context.CertEnvironment != null;
        }

        /// <summary>
        /// 获取时间戳，格式yyyy-MM-dd HH:mm:ss
        /// </summary>
        /// <returns>当前时间戳</returns>
        public string GetTimestamp()
        {
            return DateTime.UtcNow.AddHours(8).ToString("yyyy-MM-dd HH:mm:ss");
        }

        /// <summary>
        /// 计算签名，注意要去除key或value为null的键值对
        /// </summary>
        /// <param name="systemParams">系统参数集合</param>
        /// <param name="bizParams">业务参数集合</param>
        /// <param name="textParams">其他额外文本参数集合</param>
        /// <param name="privateKey">私钥</param>
        /// <returns>签名值的Base64串</returns>
        public string Sign(Dictionary<string, string> systemParams, Dictionary<string, object> bizParams,
            Dictionary<string, string> textParams, string privateKey)
        {
            IDictionary<string, string> sortedMap = GetSortedMap(systemParams, bizParams, textParams);

            StringBuilder content = new StringBuilder();
            foreach (var pair in sortedMap)
            {
                if (!string.IsNullOrEmpty(pair.Key) && !string.IsNullOrEmpty(pair.Value))
                {
                    content.Append(pair.Key).Append("=").Append(pair.Value).Append("&");
                }
            }
            if (content.Length > 0)
            {
                //去除尾巴上的&
                content.Remove(content.Length - 1, 1);
            }

            return Signer.Sign(content.ToString(), privateKey);
        }

        private IDictionary<string, string> GetSortedMap(Dictionary<string, string> systemParams,
            Dictionary<string, object> bizParams, Dictionary<string, string> textParams)
        {
            AddOtherParams(textParams, bizParams);

            IDictionary<string, string> sortedMap = new SortedDictionary<string, string>(systemParams, StringComparer.Ordinal);
            if (bizParams != null && bizParams.Count != 0)
            {
                sortedMap.Add(AlipayConstants.BIZ_CONTENT_FIELD, JsonUtil.ToJsonString(bizParams));
            }

            if (textParams != null)
            {
                foreach (var pair in textParams)
                {
                    sortedMap.Add(pair.Key, pair.Value);
                }
            }

            SetNotifyUrl(sortedMap);

            return sortedMap;
        }

        private void SetNotifyUrl(IDictionary<string, string> paramters)
        {
            if (GetConfig(AlipayConstants.NOTIFY_URL_CONFIG_KEY) != null && !paramters.ContainsKey(AlipayConstants.NOTIFY_URL_FIELD))
            {
                paramters.Add(AlipayConstants.NOTIFY_URL_FIELD, GetConfig(AlipayConstants.NOTIFY_URL_CONFIG_KEY));
            }
        }

        /// <summary>
        /// 获取商户应用公钥证书序列号，从证书模式运行时环境对象中直接读取
        /// </summary>
        /// <returns>商户应用公钥证书序列号</returns>
        public string GetMerchantCertSN()
        {
            if (context.CertEnvironment == null)
            {
                return null;
            }

            return context.CertEnvironment.MerchantCertSN;
        }

        /// <summary>
        /// 获取支付宝根证书序列号，从证书模式运行时环境对象中直接读取
        /// </summary>
        /// <returns>支付宝根证书序列号</returns>
        public string GetAlipayRootCertSN()
        {
            if (context.CertEnvironment == null)
            {
                return null;
            }
            return context.CertEnvironment.RootCertSN;
        }

        /// <summary>
        /// 将业务参数和其他额外文本参数按www-form-urlencoded格式转换成HTTP Body中的字节数组，注意要做URL Encode
        /// </summary>
        /// <param name="bizParams">业务参数</param>
        /// <returns>HTTP Body中的字节数组</returns>
        public byte[] ToUrlEncodedRequestBody(Dictionary<string, object> bizParams)
        {

            IDictionary<string, string> sortedMap = GetSortedMap(new Dictionary<string, string>(), bizParams, null);
            return AlipayConstants.DEFAULT_CHARSET.GetBytes(BuildQueryString(sortedMap));
        }

        private string BuildQueryString(IDictionary<string, string> sortedMap)
        {
            StringBuilder content = new StringBuilder();
            int index = 0;
            foreach (var pair in sortedMap)
            {
                if (!string.IsNullOrEmpty(pair.Key) && !string.IsNullOrEmpty(pair.Value))
                {
                    content.Append(index == 0 ? "" : "&")
                            .Append(pair.Key)
                            .Append("=")
                            .Append(HttpUtility.UrlEncode(pair.Value, AlipayConstants.DEFAULT_CHARSET));
                    index++;
                }
            }
            return content.ToString();
        }

        /// <summary>
        /// 生成随机分界符，用于multipart格式的HTTP请求Body的多个字段间的分隔
        /// </summary>
        /// <returns>随机分界符</returns>
        public string GetRandomBoundary()
        {
            return DateTime.Now.Ticks.ToString("X");
        }

        /// <summary>
        /// 字符串拼接
        /// </summary>
        /// <param name="a">字符串a</param>
        /// <param name="b">字符串b</param>
        /// <returns>字符串a和b拼接后的字符串</returns>
        public string ConcatStr(string a, string b)
        {
            return a + b;
        }

        /// <summary>
        /// 将其他额外文本参数和文件参数按multipart/form-data格式转换成HTTP Body中的字节数组流
        /// </summary>
        /// <param name="textParams">其他额外文本参数</param>
        /// <param name="fileParams">业务文件参数</param>
        /// <param name="boundary">HTTP Body中multipart格式的分隔符</param>
        /// <returns>Multipart格式的字节流</returns>
        public Stream ToMultipartRequestBody(Dictionary<string, string> textParams, Dictionary<string, string> fileParams, string boundary)
        {
            MemoryStream stream = new MemoryStream();

            //补充其他额外参数
            AddOtherParams(textParams, null);

            foreach (var pair in textParams)
            {
                if (!string.IsNullOrEmpty(pair.Key) && !string.IsNullOrEmpty(pair.Value))
                {
                    MultipartUtil.WriteToStream(stream, MultipartUtil.GetEntryBoundary(boundary));
                    MultipartUtil.WriteToStream(stream, MultipartUtil.GetTextEntry(pair.Key, pair.Value));
                }
            }

            //组装文件参数
            foreach (var pair in fileParams)
            {
                if (!string.IsNullOrEmpty(pair.Key) && pair.Value != null)
                {
                    MultipartUtil.WriteToStream(stream, MultipartUtil.GetEntryBoundary(boundary));
                    MultipartUtil.WriteToStream(stream, MultipartUtil.GetFileEntry(pair.Key, pair.Value));
                    MultipartUtil.WriteToStream(stream, File.ReadAllBytes(pair.Value));
                }
            }

            //添加结束标记
            MultipartUtil.WriteToStream(stream, MultipartUtil.GetEndBoundary(boundary));

            stream.Seek(0, SeekOrigin.Begin);
            return stream;
        }

        /// <summary>
        /// 将网关响应发序列化成Map，同时将API的接口名称和响应原文插入到响应Map的method和body字段中
        /// </summary>
        /// <param name="response">HTTP响应</param>
        /// <param name="method">调用的OpenAPI的接口名称</param>
        /// <returns>响应反序列化的Map</returns>
        public Dictionary<string, object> ReadAsJson(TeaResponse response, string method)
        {
            string responseBody = TeaCore.GetResponseBody(response);
            Dictionary<string, object> dictionary = JsonConvert.DeserializeObject<Dictionary<string, object>>(responseBody);
            dictionary.Add(AlipayConstants.BODY_FIELD, responseBody);
            dictionary.Add(AlipayConstants.METHOD_FIELD, method);
            return DictionaryUtil.ObjToDictionary(dictionary);
        }

        /// <summary>
        /// 适配Tea DSL自动生成的代码
        /// </summary>
        public async Task<Dictionary<string, object>> ReadAsJsonAsync(TeaResponse response, string method)
        {
            return ReadAsJson(response, method);
        }

        /// <summary>
        /// 从响应Map中提取支付宝公钥证书序列号
        /// </summary>
        /// <param name="respMap">响应Map</param>
        /// <returns>支付宝公钥证书序列号</returns>
        public string GetAlipayCertSN(Dictionary<string, object> respMap)
        {
            return (string)respMap[AlipayConstants.ALIPAY_CERT_SN_FIELD];
        }

        /// <summary>
        /// 获取支付宝公钥，从证书运行时环境对象中直接读取
        /// 如果缓存的用户指定的支付宝公钥证书的序列号与网关响应中携带的支付宝公钥证书序列号不一致，需要报错给出提示或自动更新支付宝公钥证书
        /// </summary>
        /// <param name="alipayCertSN">网关响应中携带的支付宝公钥证书序列号</param>
        /// <returns>支付宝公钥</returns>
        public string ExtractAlipayPublicKey(string alipayCertSN)
        {
            if (context.CertEnvironment == null)
            {
                return null;
            }
            return context.CertEnvironment.GetAlipayPublicKey(alipayCertSN);
        }

        /// <summary>
        /// 验证签名
        /// </summary>
        /// <param name="respMap">响应Map，可以从中提取出sign和body</param>
        /// <param name="alipayPublicKey">支付宝公钥</param>
        /// <returns>true：验签通过；false：验签不通过</returns>
        public bool Verify(Dictionary<string, object> respMap, string alipayPublicKey)
        {
            string sign = (string)respMap[AlipayConstants.SIGN_FIELD];
            string content = SignContentExtractor.GetSignSourceData((string)respMap[AlipayConstants.BODY_FIELD],
                    (string)respMap[AlipayConstants.METHOD_FIELD]);
            return Signer.Verify(content, sign, alipayPublicKey);
        }

        /// <summary>
        /// 从响应Map中提取返回值对象的Map，并将响应原文插入到body字段中
        /// </summary>
        /// <param name="respMap">响应Map</param>
        /// <returns>返回值对象Map</returns>
        public Dictionary<string, object> ToRespModel(Dictionary<string, object> respMap)
        {
            string methodName = (string)respMap[AlipayConstants.METHOD_FIELD];
            string responseNodeName = methodName.Replace('.', '_') + AlipayConstants.RESPONSE_SUFFIX;
            string errorNodeName = AlipayConstants.ERROR_RESPONSE;

            //先找正常响应节点
            foreach (var pair in respMap)
            {
                if (responseNodeName.Equals(pair.Key))
                {
                    Dictionary<string, object> model = (Dictionary<string, object>)pair.Value;
                    model.Add(AlipayConstants.BODY_FIELD, respMap[AlipayConstants.BODY_FIELD]);
                    return model;
                }
            }

            //再找异常响应节点
            foreach (var pair in respMap)
            {
                if (errorNodeName.Equals(pair.Key))
                {
                    Dictionary<string, object> model = (Dictionary<string, object>)pair.Value;
                    model.Add(AlipayConstants.BODY_FIELD, respMap[AlipayConstants.BODY_FIELD]);
                    return model;
                }
            }

            throw new Exception("响应格式不符合预期，找不到" + responseNodeName + "节点");
        }

        /// <summary>
        /// 生成页面类请求所需URL或Form表单
        /// </summary>
        /// <param name="method">GET或POST，决定是生成URL还是Form表单</param>
        /// <param name="systemParams">系统参数集合</param>
        /// <param name="bizParams">业务参数集合</param>
        /// <param name="textParams">其他额外文本参数集合</param>
        /// <param name="sign">所有参数的签名值</param>
        /// <returns>生成的URL字符串或表单</returns>
        public string GeneratePage(string method, Dictionary<string, string> systemParams, Dictionary<string, object> bizParams,
            Dictionary<string, string> textParams, string sign)
        {
            if (AlipayConstants.GET.Equals(method))
            {
                //采集并排序所有参数
                IDictionary<string, string> sortedMap = GetSortedMap(systemParams, bizParams, textParams);
                sortedMap.Add(AlipayConstants.SIGN_FIELD, sign);

                //将所有参数置于URL中
                return GetGatewayServerUrl() + "?" + BuildQueryString(sortedMap);
            }
            else if (AlipayConstants.POST.Equals(method))
            {
                //将系统参数、额外文本参数排序后置于URL中
                IDictionary<string, string> urlParams = GetSortedMap(systemParams, null, textParams);
                urlParams.Add(AlipayConstants.SIGN_FIELD, sign);
                string actionUrl = GetGatewayServerUrl() + "?" + BuildQueryString(urlParams);

                //将业务参数排序后置于form表单中
                AddOtherParams(null, bizParams);
                IDictionary<string, string> formParams = new SortedDictionary<string, string>()
                {
                    { AlipayConstants.BIZ_CONTENT_FIELD, JsonUtil.ToJsonString(bizParams)}
                };
                return PageUtil.BuildForm(actionUrl, formParams);
            }
            else
            {
                throw new Exception("_generatePage中method只支持传入GET或POST");
            }
        }

        /// <summary>
        /// 生成订单串
        /// </summary>
        /// <param name="systemParams">系统参数集合</param>
        /// <param name="bizParams">业务参数集合</param>
        /// <param name="textParams">其他文本参数集合</param>
        /// <param name="sign">所有参数的签名值</param>
        /// <returns>订单串</returns>
        public string GenerateOrderString(Dictionary<string, string> systemParams, Dictionary<string, object> bizParams,
            Dictionary<string, string> textParams, string sign)
        {
            //采集并排序所有参数
            IDictionary<string, string> sortedMap = GetSortedMap(systemParams, bizParams, textParams);
            sortedMap.Add(AlipayConstants.SIGN_FIELD, sign);

            //将所有参数置于URL中
            return BuildQueryString(sortedMap);
        }

        private string GetGatewayServerUrl()
        {
            return GetConfig(AlipayConstants.PROTOCOL_CONFIG_KEY) + "://" + GetConfig(AlipayConstants.HOST_CONFIG_KEY) + "/gateway.do";
        }

        /// <summary>
        /// AES加密
        /// </summary>
        /// <param name="plainText">明文</param>
        /// <param name="key">密钥</param>
        /// <returns>密文</returns>
        public string AesEncrypt(string plainText, string key)
        {
            return AES.Encrypt(plainText, key);
        }

        /// <summary>
        /// AES解密
        /// </summary>
        /// <param name="chiperText">密文</param>
        /// <param name="key">密钥</param>
        /// <returns>明文</returns>
        public string AesDecrypt(string chiperText, string key)
        {
            return AES.Decrypt(chiperText, key);
        }

        /// <summary>
        /// 对支付类请求的异步通知的参数集合进行验签
        /// </summary>
        /// <param name="parameters">参数集合</param>
        /// <param name="alipayPublicKey">支付宝公钥</param>
        /// <returns>true：验证成功；false：验证失败</returns>
        public bool VerifyParams(Dictionary<string, string> parameters, string alipayPublicKey)
        {
            return Signer.VerifyParams(parameters, alipayPublicKey);
        }

        /// <summary>
        /// 获取SDK版本信息
        /// </summary>
        /// <returns>SDK版本信息</returns>
        public string GetSdkVersion()
        {
            return context.SdkVersion;
        }

        /// <summary>
        /// 将随机顺序的Map转换为有序的Map
        /// </summary>
        /// <param name="input">随机顺序的Map</param>
        /// <returns>有序的Map</returns>
        public Dictionary<string, string> SortMap(Dictionary<string, string> input)
        {
            //GO语言的Map是随机顺序的，每次访问顺序都不同，才需排序
            return input;
        }

        private void AddOtherParams(Dictionary<string, string> textParams, Dictionary<string, object> bizParams)
        {
            //为null表示此处不是扩展此类参数的时机
            if (textParams != null)
            {
                foreach (var pair in optionalTextParams)
                {
                    if (!textParams.ContainsKey(pair.Key))
                    {
                        textParams.Add(pair.Key, pair.Value);
                    }
                }
                SetNotifyUrl(textParams);
            }

            //为null表示此处不是扩展此类参数的时机
            if (bizParams != null)
            {
                foreach (var pair in optionalBizParams)
                {
                    if (!bizParams.ContainsKey(pair.Key))
                    {
                        bizParams.Add(pair.Key, pair.Value);
                    }
                }
            }
        }
    }
}
