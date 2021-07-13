using System;
using System.Collections.Generic;
using System.Text;

namespace Alipay.EasySDK.Kernel.Util
{
    /// <summary>
    /// 生成页面信息辅助类
    /// </summary>
    public static class PageUtil
    {
        /// <summary>
        /// 生成表单
        /// </summary>
        /// <param name="actionUrl">表单提交链接</param>
        /// <param name="parameters">表单参数</param>
        /// <returns>表单字符串</returns>
        public static string BuildForm(string actionUrl, IDictionary<string, string> parameters)
        {
            return "<form name=\"punchout_form\" method=\"post\" action=\""
                    + actionUrl
                    + "\">\n"
                    + BuildHiddenFields(parameters)
                    + "<input type=\"submit\" value=\"立即支付\" style=\"display:none\" >\n"
                    + "</form>\n"
                    + "<script>document.forms[0].submit();</script>";
        }

        private static string BuildHiddenFields(IDictionary<string, string> parameters)
        {
            if (parameters == null || parameters.Count == 0)
            {
                return "";
            }
            StringBuilder stringBuilder = new StringBuilder();
            foreach (var pair in parameters)
            {
                if (pair.Key == null || pair.Value == null)
                {
                    continue;
                }
                stringBuilder.Append(BuildHiddenField(pair.Key, pair.Value));
            }
            return stringBuilder.ToString();
        }

        private static string BuildHiddenField(string key, string value)
        {
            StringBuilder builder = new StringBuilder(64);
            builder.Append("<input type=\"hidden\" name=\"");
            builder.Append(key);
            builder.Append("\" value=\"");
            //转义双引号
            String a = value.Replace("\"", "&quot;");
            builder.Append(a).Append("\">\n");
            return builder.ToString();
        }
    }
}
