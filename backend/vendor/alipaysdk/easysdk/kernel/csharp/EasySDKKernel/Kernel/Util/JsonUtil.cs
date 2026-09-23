using System.Collections.Generic;
using Tea;
using Newtonsoft.Json;

namespace Alipay.EasySDK.Kernel.Util
{
    /// <summary>
    /// JSON工具类
    /// </summary>
    public class JsonUtil
    {
        /// <summary>
        /// 将字典集合转换为Json字符串，转换过程中对于TeaModel，使用标注的字段名称而不是字段的变量名
        /// </summary>
        /// <param name="input">字典集合</param>
        /// <returns>Json字符串</returns>
        public static string ToJsonString(IDictionary<string, object> input)
        {
            IDictionary<string, object> result = new Dictionary<string, object>();
            foreach (var pair in input)
            {
                if (pair.Value is TeaModel)
                {
                    result.Add(pair.Key, GetTeaModelMap((TeaModel)pair.Value));
                }
                else
                {
                    result.Add(pair.Key, pair.Value);
                }
            }
            return JsonConvert.SerializeObject(result);
        }

        private static IDictionary<string, object> GetTeaModelMap(TeaModel teaModel)
        {

            IDictionary<string, object> result = new Dictionary<string, object>();
            IDictionary<string, object> teaModelMap = teaModel.ToMap();
            foreach (var pair in teaModelMap)
            {
                if (pair.Value is TeaModel)
                {
                    result.Add(pair.Key, GetTeaModelMap((TeaModel)pair.Value));
                }
                else
                {
                    result.Add(pair.Key, pair.Value);
                }
            }
            return result;
        }
    }
}
