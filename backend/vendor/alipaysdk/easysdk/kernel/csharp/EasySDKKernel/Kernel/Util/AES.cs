using System;
using System.Text;
using System.Security.Cryptography;

namespace Alipay.EasySDK.Kernel.Util
{
    public class AES
    {
        /// <summary>
        /// 128位全0初始向量
        /// </summary>
        private static readonly byte[] AES_IV = InitIV(16);

        /// <summary>
        /// AES加密
        /// </summary>
        /// <param name="plainText">明文</param>
        /// <param name="key">对称密钥</param>
        /// <returns>密文</returns>
        public static string Encrypt(string plainText, string key)
        {
            try
            {
                byte[] keyBytes = Convert.FromBase64String(key);
                byte[] plainBytes = AlipayConstants.DEFAULT_CHARSET.GetBytes(plainText); ;

                RijndaelManaged rijndatel = new RijndaelManaged
                {
                    Key = keyBytes,
                    Mode = CipherMode.CBC,
                    Padding = PaddingMode.PKCS7,
                    IV = AES_IV
                };

                ICryptoTransform transform = rijndatel.CreateEncryptor(rijndatel.Key, rijndatel.IV);
                byte[] cipherBytes = transform.TransformFinalBlock(plainBytes, 0, plainBytes.Length);
                return Convert.ToBase64String(cipherBytes);
            }
            catch (Exception e)
            {
                throw new Exception("AES加密失败，plainText=" + plainText +
                    "，keySize=" + key.Length + "。" + e.Message, e);
            }
        }

        /// <summary>
        /// AES解密
        /// </summary>
        /// <param name="cipherText">密文</param>
        /// <param name="key">对称密钥</param>
        /// <returns>明文</returns>
        public static string Decrypt(string cipherText, string key)
        {
            try
            {
                byte[] keyBytes = Convert.FromBase64String(key);
                byte[] cipherBytes = Convert.FromBase64String(cipherText);

                RijndaelManaged rijndatel = new RijndaelManaged
                {
                    Key = keyBytes,
                    Mode = CipherMode.CBC,
                    Padding = PaddingMode.PKCS7,
                    IV = AES_IV
                };

                ICryptoTransform transform = rijndatel.CreateDecryptor(rijndatel.Key, rijndatel.IV);
                byte[] plainBytes = transform.TransformFinalBlock(cipherBytes, 0, cipherBytes.Length);
                return AlipayConstants.DEFAULT_CHARSET.GetString(plainBytes);
            }
            catch (Exception e)
            {
                throw new Exception("AES解密失败，ciphertext=" + cipherText +
                    "，keySize=" + key.Length + "。" + e.Message, e);
            }
        }

        private static byte[] InitIV(int blockSize)
        {
            byte[] iv = new byte[blockSize];
            for (int i = 0; i < blockSize; ++i)
            {
                iv[i] = 0x0;
            }
            return iv;
        }
    }
}
