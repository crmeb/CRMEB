using System;
using System.Text;
using System.Security.Cryptography;
using System.IO;
using System.Collections.Generic;

namespace Alipay.EasySDK.Kernel.Util
{
    /// <summary>
    /// SHA256WithRSA签名器
    /// </summary>
    public class Signer
    {
        /// <summary>
        /// 计算签名
        /// </summary>
        /// <param name="content">待签名的内容</param>
        /// <param name="privateKeyPem">私钥</param>
        /// <returns>签名值的Base64串</returns>
        public static string Sign(string content, string privateKeyPem)
        {
            try
            {
                using (RSACryptoServiceProvider rsaService = BuildRSAServiceProvider(Convert.FromBase64String(privateKeyPem)))
                {
                    byte[] data = AlipayConstants.DEFAULT_CHARSET.GetBytes(content);
                    byte[] sign = rsaService.SignData(data, "SHA256");
                    return Convert.ToBase64String(sign);
                }
            }
            catch (Exception e)
            {
                string errorMessage = "签名遭遇异常，content=" + content + " privateKeySize=" + privateKeyPem.Length + " reason=" + e.Message;
                Console.WriteLine(errorMessage);
                throw new Exception(errorMessage, e);
            }
        }

        /// <summary>
        /// 验证签名
        /// </summary>
        /// <param name="content">待验签的内容</param>
        /// <param name="sign">签名值的Base64串</param>
        /// <param name="publicKeyPem">支付宝公钥</param>
        /// <returns>true：验证成功；false：验证失败</returns>
        public static bool Verify(string content, string sign, string publicKeyPem)
        {
            try
            {
                using (RSACryptoServiceProvider rsaService = new RSACryptoServiceProvider())
                {
                    rsaService.PersistKeyInCsp = false;
                    rsaService.ImportParameters(ConvertFromPemPublicKey(publicKeyPem));
                    return rsaService.VerifyData(AlipayConstants.DEFAULT_CHARSET.GetBytes(content),
                        "SHA256", Convert.FromBase64String(sign));
                }
            }
            catch (Exception e)
            {
                string errorMessage = "验签遭遇异常，content=" + content + " sign=" + sign +
                   " publicKey=" + publicKeyPem + " reason=" + e.Message;
                Console.WriteLine(errorMessage);
                throw new Exception(errorMessage, e);
            }

        }

        /// <summary>
        /// 对参数集合进行验签
        /// </summary>
        /// <param name="parameters">参数集合</param>
        /// <param name="publicKeyPem">支付宝公钥</param>
        /// <returns>true：验证成功；false：验证失败</returns>
        public static bool VerifyParams(Dictionary<string, string> parameters, string publicKeyPem)
        {
            string sign = parameters[AlipayConstants.SIGN_FIELD];
            parameters.Remove(AlipayConstants.SIGN_FIELD);
            parameters.Remove(AlipayConstants.SIGN_TYPE_FIELD);

            string content = GetSignContent(parameters);

            return Verify(content, sign, publicKeyPem);
        }

        private static string GetSignContent(IDictionary<string, string> parameters)
        {
            // 把字典按Key的字母顺序排序
            IDictionary<string, string> sortedParams = new SortedDictionary<string, string>(parameters, StringComparer.Ordinal);
            IEnumerator<KeyValuePair<string, string>> iterator = sortedParams.GetEnumerator();

            // 把所有参数名和参数值串在一起
            StringBuilder query = new StringBuilder("");
            while (iterator.MoveNext())
            {
                string key = iterator.Current.Key;
                string value = iterator.Current.Value;
                query.Append(key).Append("=").Append(value).Append("&");
            }
            string content = query.ToString().Substring(0, query.Length - 1);

            return content;
        }

        private static RSAParameters ConvertFromPemPublicKey(string pemPublickKey)
        {
            if (string.IsNullOrEmpty(pemPublickKey))
            {
                throw new Exception("PEM格式公钥不可为空。");
            }

            //移除干扰文本
            pemPublickKey = pemPublickKey.Replace("-----BEGIN PUBLIC KEY-----", "")
                .Replace("-----END PUBLIC KEY-----", "").Replace("\n", "").Replace("\r", "");

            byte[] keyData = Convert.FromBase64String(pemPublickKey);
            bool keySize1024 = (keyData.Length == 162);
            bool keySize2048 = (keyData.Length == 294);
            if (!(keySize1024 || keySize2048))
            {
                throw new Exception("公钥长度只支持1024和2048。");
            }
            byte[] pemModulus = (keySize1024 ? new byte[128] : new byte[256]);
            byte[] pemPublicExponent = new byte[3];
            Array.Copy(keyData, (keySize1024 ? 29 : 33), pemModulus, 0, (keySize1024 ? 128 : 256));
            Array.Copy(keyData, (keySize1024 ? 159 : 291), pemPublicExponent, 0, 3);
            RSAParameters para = new RSAParameters
            {
                Modulus = pemModulus,
                Exponent = pemPublicExponent
            };
            return para;
        }

        private static RSACryptoServiceProvider BuildRSAServiceProvider(byte[] privateKey)
        {
            byte[] MODULUS, E, D, P, Q, DP, DQ, IQ;
            byte bt = 0;
            ushort twobytes = 0;
            int elems = 0;

            //set up stream to decode the asn.1 encoded RSA private key
            //wrap Memory Stream with BinaryReader for easy reading
            using (BinaryReader binaryReader = new BinaryReader(new MemoryStream(privateKey)))
            {
                twobytes = binaryReader.ReadUInt16();
                //data read as little endian order (actual data order for Sequence is 30 81)
                if (twobytes == 0x8130)
                {
                    //advance 1 byte
                    binaryReader.ReadByte();
                }
                else if (twobytes == 0x8230)
                {
                    //advance 2 bytes
                    binaryReader.ReadInt16();
                }
                else
                {
                    return null;
                }

                twobytes = binaryReader.ReadUInt16();
                //version number
                if (twobytes != 0x0102)
                {
                    return null;
                }
                bt = binaryReader.ReadByte();
                if (bt != 0x00)
                {
                    return null;
                }

                //all private key components are Integer sequences
                elems = GetIntegerSize(binaryReader);
                MODULUS = binaryReader.ReadBytes(elems);

                elems = GetIntegerSize(binaryReader);
                E = binaryReader.ReadBytes(elems);

                elems = GetIntegerSize(binaryReader);
                D = binaryReader.ReadBytes(elems);

                elems = GetIntegerSize(binaryReader);
                P = binaryReader.ReadBytes(elems);

                elems = GetIntegerSize(binaryReader);
                Q = binaryReader.ReadBytes(elems);

                elems = GetIntegerSize(binaryReader);
                DP = binaryReader.ReadBytes(elems);

                elems = GetIntegerSize(binaryReader);
                DQ = binaryReader.ReadBytes(elems);

                elems = GetIntegerSize(binaryReader);
                IQ = binaryReader.ReadBytes(elems);

                //create RSACryptoServiceProvider instance and initialize with public key
                RSACryptoServiceProvider rsaService = new RSACryptoServiceProvider();
                RSAParameters rsaParams = new RSAParameters
                {
                    Modulus = MODULUS,
                    Exponent = E,
                    D = D,
                    P = P,
                    Q = Q,
                    DP = DP,
                    DQ = DQ,
                    InverseQ = IQ
                };
                rsaService.ImportParameters(rsaParams);
                return rsaService;
            }
        }

        private static int GetIntegerSize(BinaryReader binaryReader)
        {
            byte bt = 0;
            byte lowbyte = 0x00;
            byte highbyte = 0x00;
            int count = 0;

            bt = binaryReader.ReadByte();

            //expect integer
            if (bt != 0x02)
            {
                return 0;
            }
            bt = binaryReader.ReadByte();

            if (bt == 0x81)
            {
                //data size in next byte
                count = binaryReader.ReadByte();
            }
            else if (bt == 0x82)
            {
                //data size in next 2 bytes
                highbyte = binaryReader.ReadByte();
                lowbyte = binaryReader.ReadByte();
                byte[] modint = { lowbyte, highbyte, 0x00, 0x00 };
                count = BitConverter.ToInt32(modint, 0);
            }
            else
            {
                //we already have the data size
                count = bt;
            }
            while (binaryReader.ReadByte() == 0x00)
            {   //remove high order zeros in data
                count -= 1;
            }
            //last ReadByte wasn't a removed zero, so back up a byte
            binaryReader.BaseStream.Seek(-1, SeekOrigin.Current);
            return count;
        }
    }
}
