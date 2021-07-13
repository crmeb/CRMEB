using System.Reflection;
using Tea;

namespace Alipay.EasySDK.Kernel.Util
{
    /// <summary>
    /// 响应检查工具类
    /// </summary>
    public class ResponseChecker
    {
        public const string SUB_CODE_FIELD_NAME = "SubCode";

        /// <summary>
        /// 判断一个请求返回的响应是否成功
        /// </summary>
        /// <param name="response">响应对象</param>
        /// <returns>true：成功；false：失败</returns>
        public static bool Success(TeaModel response)
        {
            PropertyInfo propertyInfo = response.GetType().GetProperty(SUB_CODE_FIELD_NAME);
            if (propertyInfo == null)
            {
                //没有SubCode属性的响应对象，通常是那些无需跟网关远程通信的API，只要本地执行完成都视为成功
                return true;
            }

            string subCode = (string)propertyInfo.GetValue(response);
            return string.IsNullOrEmpty(subCode);
        }
    }
}
