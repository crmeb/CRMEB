using System.Collections.Generic;
using Newtonsoft.Json.Linq;

namespace Alipay.EasySDK.Kernel.Util
{
    /// <summary>
    /// 字典工具类
    /// </summary>
    public static class DictionaryUtil
    {
        /// <summary>
        /// 将字典各层次Value中的JObject和JArray转换成C#标准库中的Dictionary和List
        /// </summary>
        /// <param name="dicObj">输入字典</param>
        /// <returns>转换后的输出字典</returns>
        public static Dictionary<string, object> ObjToDictionary(Dictionary<string, object> dicObj)
        {
            Dictionary<string, object> dic = new Dictionary<string, object>();
            foreach (string key in dicObj.Keys)
            {
                if (dicObj[key] is JArray)
                {
                    List<Dictionary<string, object>> dicObjList = ((JArray)dicObj[key]).ToObject<List<Dictionary<string, object>>>();
                    List<Dictionary<string, object>> dicList = new List<Dictionary<string, object>>();
                    foreach (Dictionary<string, object> objItem in dicObjList)
                    {
                        dicList.Add(ObjToDictionary(objItem));
                    }
                    dic.Add(key, dicList);
                }
                else if (dicObj[key] is JObject)
                {
                    Dictionary<string, object> dicJObj = ((JObject)dicObj[key]).ToObject<Dictionary<string, object>>();
                    dic.Add(key, ObjToDictionary(dicJObj));
                }
                else
                {
                    dic.Add(key, dicObj[key]);
                }
            }
            return dic;
        }
    }
}
