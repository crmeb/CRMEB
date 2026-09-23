using System;
using System.Collections.Generic;

namespace Alipay.EasySDK.Kernel.Util
{
    /// <summary>
    /// 待验签原文提取器
    /// 注：此处不可使用JSON反序列化工具进行提取，会破坏原有格式，对于签名而言差个空格都会验签不通过
    /// </summary>
    public class SignContentExtractor
    {
        /// <summary>
        /// 左大括号
        /// </summary>
        public const char LEFT_BRACE = '{';

        /// <summary>
        /// 右大括号
        /// </summary>
        public const char RIGHT_BRACE = '}';

        /// <summary>
        /// 双引号
        /// </summary>
        public const char DOUBLE_QUOTES = '"';

        /// <summary>
        /// 获取待验签的原文
        /// </summary>
        /// <param name="body">网关的整体响应字符串</param>
        /// <param name="method">本次调用的OpenAPI接口名称</param>
        /// <returns>待验签的原文</returns>
        public static string GetSignSourceData(string body, string method)
        {
            string rootNode = method.Replace(".", "_") + AlipayConstants.RESPONSE_SUFFIX;
            string errorRootNode = AlipayConstants.ERROR_RESPONSE;

            int indexOfRootNode = body.IndexOf(rootNode, StringComparison.Ordinal);
            int indexOfErrorRoot = body.IndexOf(errorRootNode, StringComparison.Ordinal);

            string result = null;
            if (indexOfRootNode > 0)
            {
                result = ParseSignSourceData(body, rootNode, indexOfRootNode);
            }
            else if (indexOfErrorRoot > 0)
            {
                result = ParseSignSourceData(body, errorRootNode, indexOfErrorRoot);
            }

            return result;
        }

        private static string ParseSignSourceData(string body, string rootNode, int indexOfRootNode)
        {
            int signDataStartIndex = indexOfRootNode + rootNode.Length + 2;
            int indexOfSign = body.IndexOf("\"" + AlipayConstants.SIGN_FIELD + "\"", StringComparison.Ordinal);
            if (indexOfSign < 0)
            {
                return null;
            }
            SignSourceData signSourceData = ExtractSignContent(body, signDataStartIndex);

            //如果提取的待验签原始内容后还有rootNode
            if (body.LastIndexOf(rootNode, StringComparison.Ordinal) > signSourceData.EndIndex)
            {
                throw new Exception("检测到响应报文中有重复的" + rootNode + "，验签失败。");
            }

            return signSourceData.SourceData;
        }

        private static SignSourceData ExtractSignContent(string str, int begin)
        {
            if (str == null)
            {
                return null;
            }

            int beginIndex = ExtractBeginPosition(str, begin);
            if (beginIndex >= str.Length)
            {
                return null;
            }

            int endIndex = ExtractEndPosition(str, beginIndex);
            return new SignSourceData()
            {
                SourceData = str.Substring(beginIndex, endIndex - beginIndex),
                BeginIndex = beginIndex,
                EndIndex = endIndex
            };
        }

        private static int ExtractBeginPosition(string responseString, int begin)
        {
            int beginPosition = begin;
            //找到第一个左大括号（对应响应的是JSON对象的情况：普通调用OpenAPI响应明文）
            //或者双引号（对应响应的是JSON字符串的情况：加密调用OpenAPI响应Base64串），作为待验签内容的起点
            while (beginPosition < responseString.Length
                    && responseString[beginPosition] != LEFT_BRACE
                    && responseString[beginPosition] != DOUBLE_QUOTES)
            {
                ++beginPosition;
            }
            return beginPosition;
        }

        private static int ExtractEndPosition(string responseString, int beginPosition)
        {
            //提取明文验签内容终点
            if (responseString[beginPosition] == LEFT_BRACE)
            {
                return ExtractJsonObjectEndPosition(responseString, beginPosition);
            }
            //提取密文验签内容终点
            else
            {
                return ExtractJsonBase64ValueEndPosition(responseString, beginPosition);
            }
        }

        private static int ExtractJsonBase64ValueEndPosition(string responseString, int beginPosition)
        {
            for (int index = beginPosition; index < responseString.Length; ++index)
            {
                //找到第2个双引号作为终点，由于中间全部是Base64编码的密文，所以不会有干扰的特殊字符
                if (responseString[index] == DOUBLE_QUOTES && index != beginPosition)
                {
                    return index + 1;
                }
            }
            //如果没有找到第2个双引号，说明验签内容片段提取失败，直接尝试选取剩余整个响应字符串进行验签
            return responseString.Length;
        }

        private static int ExtractJsonObjectEndPosition(string responseString, int beginPosition)
        {
            //记录当前尚未发现配对闭合的大括号
            LinkedList<char> braces = new LinkedList<char>();
            //记录当前字符是否在双引号中
            bool inQuotes = false;
            //记录当前字符前面连续的转义字符个数
            int consecutiveEscapeCount = 0;
            //从待验签字符的起点开始遍历后续字符串，找出待验签字符串的终止点，终点即是与起点{配对的}
            for (int index = beginPosition; index < responseString.Length; ++index)
            {
                //提取当前字符
                char currentChar = responseString[index];

                //如果当前字符是"且前面有偶数个转义标记（0也是偶数）
                if (currentChar == DOUBLE_QUOTES && consecutiveEscapeCount % 2 == 0)
                {
                    //是否在引号中的状态取反
                    inQuotes = !inQuotes;
                }
                //如果当前字符是{且不在引号中
                else if (currentChar == LEFT_BRACE && !inQuotes)
                {
                    //将该{加入未闭合括号中
                    braces.AddLast(LEFT_BRACE);
                }
                //如果当前字符是}且不在引号中
                else if (currentChar == RIGHT_BRACE && !inQuotes)
                {
                    //弹出一个未闭合括号
                    braces.RemoveLast();
                    //如果弹出后，未闭合括号为空，说明已经找到终点
                    if (braces.Count == 0)
                    {
                        return index + 1;
                    }
                }

                //如果当前字符是转义字符
                if (currentChar == '\\')
                {
                    //连续转义字符个数+1
                    ++consecutiveEscapeCount;
                }
                else
                {
                    //连续转义字符个数置0
                    consecutiveEscapeCount = 0;
                }
            }

            //如果没有找到配对的闭合括号，说明验签内容片段提取失败，直接尝试选取剩余整个响应字符串进行验签
            return responseString.Length;
        }

        /// <summary>
        /// 从响应字符串中提取到的待验签原始内容
        /// </summary>
        public class SignSourceData
        {
            /// <summary>
            /// 待验签原始内容
            /// </summary>
            public string SourceData { get; set; }

            /// <summary>
            /// 待验签原始内容在响应字符串中的起始位置
            /// </summary>
            public int BeginIndex { get; set; }

            /// <summary>
            /// 待验签原始内容在响应字符串中的结束位置
            /// </summary>
            public int EndIndex { get; set; }
        }
    }
}
