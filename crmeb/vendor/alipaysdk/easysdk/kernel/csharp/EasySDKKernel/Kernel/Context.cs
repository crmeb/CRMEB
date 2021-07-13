using System;
using System.Collections.Generic;
using Tea;
using Alipay.EasySDK.Kernel.Util;

namespace Alipay.EasySDK.Kernel
{
    public class Context
    {
        /// <summary>
        /// 客户端配置参数
        /// </summary>
        private readonly Dictionary<string, object> config;

        /// <summary>
        /// 证书模式运行时环境
        /// </summary>
        public CertEnvironment CertEnvironment { get; }

        /// <summary>
        /// SDK版本号
        /// </summary>
        public string SdkVersion { get; }

        public Context(Config config, string sdkVersion)
        {
            this.config = config.ToMap();
            SdkVersion = sdkVersion;
            ArgumentValidator.CheckArgument(AlipayConstants.RSA2.Equals(GetConfig(AlipayConstants.SIGN_TYPE_CONFIG_KEY)),
               "Alipay Easy SDK只允许使用RSA2签名方式，RSA签名方式由于安全性相比RSA2弱已不再推荐。");

            if (!string.IsNullOrEmpty(GetConfig(AlipayConstants.ALIPAY_CERT_PATH_CONFIG_KEY)))
            {
                CertEnvironment = new CertEnvironment(
                        GetConfig(AlipayConstants.MERCHANT_CERT_PATH_CONFIG_KEY),
                        GetConfig(AlipayConstants.ALIPAY_CERT_PATH_CONFIG_KEY),
                        GetConfig(AlipayConstants.ALIPAY_ROOT_CERT_PATH_CONFIG_KEY));
            }
        }

        public string GetConfig(string key)
        {
            return (string)config[key];
        }
    }
}