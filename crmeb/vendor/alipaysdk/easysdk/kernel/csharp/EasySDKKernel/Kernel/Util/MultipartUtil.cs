using System;
using System.Text;
using System.IO;

namespace Alipay.EasySDK.Kernel.Util
{
    /// <summary>
    /// HTTP multipart/form-data格式相关工具类
    /// </summary>
    public static class MultipartUtil
    {
        /// <summary>
        /// 获取Multipart分界符
        /// </summary>
        /// <param name="boundary">用作分界的随机字符串</param>
        /// <returns>Multipart分界符</returns>
        public static byte[] GetEntryBoundary(string boundary)
        {
            return Encoding.UTF8.GetBytes("\r\n--" + boundary + "\r\n");
        }

        /// <summary>
        /// 获取Multipart结束标记
        /// </summary>
        /// <param name="boundary">用作分界的随机字符串</param>
        /// <returns>Multipart结束标记</returns>
        public static byte[] GetEndBoundary(string boundary)
        {
            return Encoding.UTF8.GetBytes("\r\n--" + boundary + "--\r\n");
        }

        /// <summary>
        /// 获取Multipart中的文本参数结构
        /// </summary>
        /// <param name="fieldName">字段名称</param>
        /// <param name="fieldValue">字段值</param>
        /// <returns>文本参数结构</returns>
        public static byte[] GetTextEntry(string fieldName, string fieldValue)
        {
            string entry = "Content-Disposition:form-data;name=\""
                    + fieldName
                    + "\"\r\nContent-Type:text/plain\r\n\r\n"
                    + fieldValue;
            return AlipayConstants.DEFAULT_CHARSET.GetBytes(entry);
        }

        /// <summary>
        /// 获取Multipart中的文件参数结构（不含文件内容，只有文件元数据）
        /// </summary>
        /// <param name="fieldName">字段名称</param>
        /// <param name="filePath">文件路径</param>
        /// <returns>文件参数结构（不含文件内容）</returns>
        public static byte[] GetFileEntry(String fieldName, String filePath)
        {
            ArgumentValidator.CheckArgument(File.Exists(filePath),
                Path.GetFullPath(filePath) + "文件不存在");
            ArgumentValidator.CheckArgument(Path.GetFileName(filePath).Contains("."),
                "文件名必须带上正确的扩展名");

            String entry = "Content-Disposition:form-data;name=\""
                    + fieldName
                    + "\";filename=\""
                    + Path.GetFileName(filePath)
                    + "\"\r\nContent-Type:application/octet-stream"
                    + "\r\n\r\n";
            return AlipayConstants.DEFAULT_CHARSET.GetBytes(entry);
        }

        /// <summary>
        /// 往指定流中写入整个字节数组
        /// </summary>
        /// <param name="stream">流</param>
        /// <param name="content">字节数组</param>
        public static void WriteToStream(Stream stream, byte[] content)
        {
            stream.Write(content, 0, content.Length);
        }
    }
}
