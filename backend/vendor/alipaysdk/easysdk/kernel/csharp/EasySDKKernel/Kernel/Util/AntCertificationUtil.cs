using System.Collections.Generic;
using System;
using Org.BouncyCastle.X509;
using Org.BouncyCastle.Asn1.X509;
using Org.BouncyCastle.Crypto;
using System.Security.Cryptography;
using System.Text;
using System.Linq;

namespace Alipay.EasySDK.Kernel.Util
{
    /// <summary>
    /// 证书相关工具类
    /// </summary>
    public static class AntCertificationUtil
    {
        /// <summary>
        /// 提取根证书序列号
        /// </summary>
        /// <param name="rootCertContent">根证书文本</param>
        /// <returns>根证书序列号</returns>
        public static string GetRootCertSN(string rootCertContent)
        {
            string rootCertSN = "";
            try
            {
                List<X509Certificate> x509Certificates = ReadPemCertChain(rootCertContent);
                foreach (X509Certificate cert in x509Certificates)
                {
                    //只提取与指定算法类型匹配的证书的序列号
                    if (cert.SigAlgOid.StartsWith("1.2.840.113549.1.1", StringComparison.Ordinal))
                    {
                        string certSN = GetCertSN(cert);
                        if (string.IsNullOrEmpty(rootCertSN))
                        {
                            rootCertSN = certSN;
                        }
                        else
                        {
                            rootCertSN = rootCertSN + "_" + certSN;
                        }
                    }
                }
            }
            catch (Exception ex)
            {
                throw new Exception("提取根证书序列号失败。" + ex.Message);
            }
            return rootCertSN;
        }

        /// <summary>
        /// 反序列化证书文本
        /// </summary>
        /// <param name="certContent">证书文本</param>
        /// <returns>X509Certificate证书对象</returns>
        public static X509Certificate ParseCert(string certContent)
        {
            return new X509CertificateParser().ReadCertificate(Encoding.UTF8.GetBytes(certContent));
        }

        /// <summary>
        /// 计算指定证书的序列号
        /// </summary>
        /// <param name="cert">证书</param>
        /// <returns>序列号</returns>
        public static string GetCertSN(X509Certificate cert)
        {
            string issuerDN = cert.IssuerDN.ToString();
            //提取出的证书的issuerDN本身是以CN开头的，则无需逆序，直接返回
            if (issuerDN.StartsWith("CN", StringComparison.Ordinal))
            {
                return CalculateMd5(issuerDN + cert.SerialNumber);
            }
            List<string> attributes = issuerDN.Split(',').ToList();
            attributes.Reverse();
            return CalculateMd5(string.Join(",", attributes.ToArray()) + cert.SerialNumber);
        }

        /// <summary>
        /// 校验证书链是否可信
        /// </summary>
        /// <param name="certContent">需要验证的目标证书或者证书链文本</param>
        /// <param name="rootCertContent">可信根证书列表文本</param>
        /// <returns>true：证书可信；false：证书不可信</returns>
        public static bool IsTrusted(string certContent, string rootCertContent)
        {
            List<X509Certificate> certs = ReadPemCertChain(certContent);
            List<X509Certificate> rootCerts = ReadPemCertChain(rootCertContent);
            return VerifyCertChain(certs, rootCerts);
        }

        /// <summary>
        /// 从证书链文本反序列化证书链集合
        /// </summary>
        /// <param name="cert">证书链文本</param>
        /// <returns>证书链集合</returns>
        private static List<X509Certificate> ReadPemCertChain(string cert)
        {
            System.Collections.ICollection collection = new X509CertificateParser().ReadCertificates(Encoding.UTF8.GetBytes(cert));
            List<X509Certificate> result = new List<X509Certificate>();
            foreach (var each in collection)
            {
                result.Add((X509Certificate)each);
            }
            return result;
        }

        /// <summary>
        /// 将证书链按照完整的签发顺序进行排序，排序后证书链为：[issuerA, subjectA]-[issuerA, subjectB]-[issuerB, subjectC]-[issuerC, subjectD]...
        /// </summary>
        /// <param name="certChain">未排序的证书链</param>
        /// <returns>true：排序成功；false：证书链不完整</returns>
        private static bool SortCertChain(List<X509Certificate> certChain)
        {
            //主题和证书的映射
            Dictionary<X509Name, X509Certificate> subject2CertMap = new Dictionary<X509Name, X509Certificate>();
            //签发者和证书的映射
            Dictionary<X509Name, X509Certificate> issuer2CertMap = new Dictionary<X509Name, X509Certificate>();
            //是否包含自签名证书
            bool hasSelfSignedCert = false;
            foreach (X509Certificate cert in certChain)
            {
                if (IsSelfSigned(cert))
                {
                    if (hasSelfSignedCert)
                    {
                        //同一条证书链中只能有一个自签名证书
                        return false;
                    }
                    hasSelfSignedCert = true;
                }
                subject2CertMap[cert.SubjectDN] = cert;
                issuer2CertMap[cert.IssuerDN] = cert;
            }

            List<X509Certificate> orderedCertChain = new List<X509Certificate>();

            X509Certificate current = certChain[0];

            AddressingUp(subject2CertMap, orderedCertChain, current);
            AddressingDown(issuer2CertMap, orderedCertChain, current);

            //说明证书链不完整
            if (certChain.Count != orderedCertChain.Count)
            {
                return false;
            }

            //用排序后的结果覆盖传入的证书链集合
            for (int i = 0; i < orderedCertChain.Count; i++)
            {
                certChain[i] = orderedCertChain[i];
            }
            return true;
        }

        private static bool IsSelfSigned(X509Certificate cert)
        {
            return cert.SubjectDN.Equivalent(cert.IssuerDN);
        }

        /// <summary>
        /// 向上构造证书链
        /// </summary>
        /// <param name="subject2CertMap">主题与证书的映射</param>
        /// <param name="orderedCertChain">储存排序后的证书链集合</param>
        /// <param name="current">当前需要插入排序后的证书链集合中的证书</param>
        private static void AddressingUp(Dictionary<X509Name, X509Certificate> subject2CertMap,
            List<X509Certificate> orderedCertChain, X509Certificate current)
        {
            orderedCertChain.Insert(0, current);
            if (IsSelfSigned(current))
            {
                return;
            }

            if (!subject2CertMap.ContainsKey(current.IssuerDN))
            {
                return;
            }

            X509Certificate issuer = subject2CertMap[current.IssuerDN];
            AddressingUp(subject2CertMap, orderedCertChain, issuer);
        }

        /// <summary>
        /// 向下构造证书链
        /// </summary>
        /// <param name="issuer2CertMap">签发者和证书的映射</param>
        /// <param name="certChain">储存排序后的证书链集合</param>
        /// <param name="current">当前需要插入排序后的证书链集合中的证书</param>
        private static void AddressingDown(Dictionary<X509Name, X509Certificate> issuer2CertMap,
            List<X509Certificate> certChain, X509Certificate current)
        {
            if (!issuer2CertMap.ContainsKey(current.SubjectDN))
            {
                return;
            }

            X509Certificate subject = issuer2CertMap[current.SubjectDN];
            if (IsSelfSigned(subject))
            {
                return;
            }
            certChain.Add(subject);
            AddressingDown(issuer2CertMap, certChain, subject);
        }

        /// <summary>
        /// 验证证书是否是信任证书库中的证书签发的
        /// </summary>
        /// <param name="cert">待验证证书</param>
        /// <param name="rootCerts">可信根证书列表</param>
        /// <returns>true：验证通过；false：验证不通过</returns>
        private static bool VerifyCert(X509Certificate cert, List<X509Certificate> rootCerts)
        {
            if (!cert.IsValidNow)
            {
                return false;
            }

            Dictionary<X509Name, X509Certificate> subject2CertMap = new Dictionary<X509Name, X509Certificate>();
            foreach (X509Certificate root in rootCerts)
            {
                subject2CertMap[root.SubjectDN] = root;
            }

            X509Name issuerDN = cert.IssuerDN;
            if (!subject2CertMap.ContainsKey(issuerDN))
            {
                return false;
            }

            X509Certificate issuer = subject2CertMap[issuerDN];
            try
            {
                AsymmetricKeyParameter publicKey = issuer.GetPublicKey();
                cert.Verify(publicKey);
            }
            catch (Exception ex)
            {
                Console.WriteLine("证书验证出现异常。" + ex.Message);
                return false;
            }
            return true;
        }

        /// <summary>
        /// 验证证书列表
        /// </summary>
        /// <param name="certs">待验证的证书列表</param>
        /// <param name="rootCerts">可信根证书列表</param>
        /// <returns>true：验证通过；false：验证不通过</returns>
        private static bool VerifyCertChain(List<X509Certificate> certs, List<X509Certificate> rootCerts)
        {
            //证书列表排序，形成排序后的证书链
            bool sorted = SortCertChain(certs);
            if (!sorted)
            {
                //不是完整的证书链
                return false;
            }

            //先验证第一个证书是不是信任库中证书签发的
            X509Certificate previous = certs[0];
            bool firstOK = VerifyCert(previous, rootCerts);
            if (!firstOK || certs.Count == 1)
            {
                return firstOK;
            }

            //验证证书链
            for (int i = 1; i < certs.Count; i++)
            {
                try
                {
                    X509Certificate cert = certs[i];
                    if (!cert.IsValidNow)
                    {
                        return false;
                    }

                    //用上级证书的公钥验证本证书是否是上级证书签发的
                    cert.Verify(previous.GetPublicKey());

                    previous = cert;
                }
                catch (Exception ex)
                {
                    //证书链验证失败
                    Console.WriteLine("证书链验证失败。" + ex.Message);
                    return false;
                }
            }

            return true;
        }


        private static string CalculateMd5(string input)
        {
            using (MD5 md5 = new MD5CryptoServiceProvider())
            {
                string result = "";
                byte[] bytes = md5.ComputeHash(Encoding.GetEncoding("utf-8").GetBytes(input));
                for (int i = 0; i < bytes.Length; i++)
                {
                    result += bytes[i].ToString("x2");
                }
                return result;
            }
        }

        /// <summary>
        /// 从证书中提取公钥并转换为PEM编码
        /// </summary>
        /// <param name="input">证书</param>
        /// <returns>PEM编码公钥</returns>
        public static string ExtractPemPublicKeyFromCert(X509Certificate input)
        {
            SubjectPublicKeyInfo subjectPublicKeyInfo = SubjectPublicKeyInfoFactory.CreateSubjectPublicKeyInfo(input.GetPublicKey());
            return Convert.ToBase64String(subjectPublicKeyInfo.GetDerEncoded());
        }
    }
}