using System;
using System.Collections.Generic;
using System.IO;
using Org.BouncyCastle.X509;
using Alipay.EasySDK.Kernel.Util;

namespace Alipay.EasySDK.Kernel
{
    /// <summary>
    /// 证书模式运行时环境
    /// </summary>
    public class CertEnvironment
    {

        /// <summary>
        /// 支付宝根证书内容
        /// </summary>
        public string RootCertContent { get; set; }

        /// <summary>
        /// 支付宝根证书序列号
        /// </summary>
        public string RootCertSN { get; set; }

        /// <summary>
        /// 商户应用公钥证书序列号
        /// </summary>
        public string MerchantCertSN { get; set; }

        /// <summary>
        /// 缓存的不同支付宝公钥证书序列号对应的支付宝公钥
        /// </summary>
        private readonly Dictionary<string, string> CachedAlipayPublicKey = new Dictionary<string, string>();

        /// <summary>
        /// 构造证书运行环境
        /// </summary>
        /// <param name="merchantCertPath">商户公钥证书路径</param>
        /// <param name="alipayCertPath">支付宝公钥证书路径</param>
        /// <param name="alipayRootCertPath">支付宝根证书路径</param>
        public CertEnvironment(string merchantCertPath, string alipayCertPath, string alipayRootCertPath)
        {
            if (string.IsNullOrEmpty(merchantCertPath) || string.IsNullOrEmpty(alipayCertPath) || string.IsNullOrEmpty(alipayCertPath))
            {
                throw new Exception("证书参数merchantCertPath、alipayCertPath或alipayRootCertPath设置不完整。");
            }

            this.RootCertContent = File.ReadAllText(alipayRootCertPath);
            this.RootCertSN = AntCertificationUtil.GetRootCertSN(RootCertContent);

            X509Certificate merchantCert = AntCertificationUtil.ParseCert(File.ReadAllText(merchantCertPath));
            this.MerchantCertSN = AntCertificationUtil.GetCertSN(merchantCert);

            X509Certificate alipayCert = AntCertificationUtil.ParseCert(File.ReadAllText(alipayCertPath));
            string alipayCertSN = AntCertificationUtil.GetCertSN(alipayCert);
            string alipayPublicKey = AntCertificationUtil.ExtractPemPublicKeyFromCert(alipayCert);
            CachedAlipayPublicKey[alipayCertSN] = alipayPublicKey;
        }

        public string GetAlipayPublicKey(string sn)
        {
            //如果没有指定sn，则默认取缓存中的第一个值
            if (string.IsNullOrEmpty(sn))
            {
                return CachedAlipayPublicKey.Values.GetEnumerator().Current;
            }

            if (CachedAlipayPublicKey.ContainsKey(sn))
            {
                return CachedAlipayPublicKey[sn];
            }
            else
            {
                //网关在支付宝公钥证书变更前，一定会确认通知到商户并在商户做出反馈后，才会更新该商户的支付宝公钥证书
                //TODO: 后续可以考虑加入自动升级支付宝公钥证书逻辑，注意并发更新冲突问题
                throw new Exception("支付宝公钥证书[" + sn + "]已过期，请重新下载最新支付宝公钥证书并替换原证书文件");
            }
        }
    }
}
