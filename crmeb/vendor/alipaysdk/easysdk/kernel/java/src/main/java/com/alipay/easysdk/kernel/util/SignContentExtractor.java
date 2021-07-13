/**
 * Alipay.com Inc. Copyright (c) 2004-2019 All Rights Reserved.
 */
package com.alipay.easysdk.kernel.util;

import com.alipay.easysdk.kernel.AlipayConstants;

import java.util.LinkedList;

/**
 * 待验签原文提取器
 * <p>
 * 注：此处不可使用JSON反序列化工具进行提取，会破坏原有格式，对于签名而言差个空格都会验签不通过
 *
 * @author zhongyu
 * @version $Id: SignContentExtractor.java, v 0.1 2019年12月19日 9:07 PM zhongyu Exp $
 */
public class SignContentExtractor {
    /**
     * 左大括号
     */
    public static final char LEFT_BRACE = '{';

    /**
     * 右大括号
     */
    public static final char RIGHT_BRACE = '}';

    /**
     * 双引号
     */
    public static final char DOUBLE_QUOTES = '"';

    /**
     * 获取待验签的原文
     *
     * @param body   网关的整体响应字符串
     * @param method 本次调用的OpenAPI接口名称
     * @return 待验签的原文
     */
    public static String getSignSourceData(String body, String method) {
        // 加签源串起点
        String rootNode = method.replace('.', '_') + AlipayConstants.RESPONSE_SUFFIX;
        String errorRootNode = AlipayConstants.ERROR_RESPONSE;

        int indexOfRootNode = body.indexOf(rootNode);
        int indexOfErrorRoot = body.indexOf(errorRootNode);

        if (indexOfRootNode > 0) {
            return parseSignSourceData(body, rootNode, indexOfRootNode);
        } else if (indexOfErrorRoot > 0) {
            return parseSignSourceData(body, errorRootNode, indexOfErrorRoot);
        } else {
            return null;
        }
    }

    private static String parseSignSourceData(String body, String rootNode, int indexOfRootNode) {
        //第一个字母 + 长度 + 冒号 + 引号
        int signDataStartIndex = indexOfRootNode + rootNode.length() + 2;

        int indexOfSign = body.indexOf("\"" + AlipayConstants.SIGN_FIELD + "\"");
        if (indexOfSign < 0) {
            return null;
        }

        SignSourceData signSourceData = extractSignContent(body, signDataStartIndex);

        //如果提取的待验签原始内容后还有rootNode
        if (body.lastIndexOf(rootNode) > signSourceData.getEndIndex()) {
            throw new RuntimeException("检测到响应报文中有重复的" + rootNode + "，验签失败。");
        }

        return signSourceData.getSourceData();
    }

    private static SignSourceData extractSignContent(String str, int begin) {
        if (str == null) {
            return null;
        }

        int beginIndex = extractBeginPosition(str, begin);
        if (beginIndex >= str.length()) {
            return null;
        }

        int endIndex = extractEndPosition(str, beginIndex);
        return new SignSourceData(str.substring(beginIndex, endIndex), beginIndex, endIndex);
    }

    private static int extractBeginPosition(String responseString, int begin) {
        int beginPosition = begin;
        //找到第一个左大括号（对应响应的是JSON对象的情况：普通调用OpenAPI响应明文）
        //或者双引号（对应响应的是JSON字符串的情况：加密调用OpenAPI响应Base64串），作为待验签内容的起点
        while (beginPosition < responseString.length()
                && responseString.charAt(beginPosition) != LEFT_BRACE
                && responseString.charAt(beginPosition) != DOUBLE_QUOTES) {
            ++beginPosition;
        }
        return beginPosition;
    }

    private static int extractEndPosition(String responseString, int beginPosition) {
        //提取明文验签内容终点
        if (responseString.charAt(beginPosition) == LEFT_BRACE) {
            return extractJsonObjectEndPosition(responseString, beginPosition);
        }
        //提取密文验签内容终点
        else {
            return extractJsonBase64ValueEndPosition(responseString, beginPosition);
        }
    }

    private static int extractJsonBase64ValueEndPosition(String responseString, int beginPosition) {
        for (int index = beginPosition; index < responseString.length(); ++index) {
            //找到第2个双引号作为终点，由于中间全部是Base64编码的密文，所以不会有干扰的特殊字符
            if (responseString.charAt(index) == DOUBLE_QUOTES && index != beginPosition) {
                return index + 1;
            }
        }
        //如果没有找到第2个双引号，说明验签内容片段提取失败，直接尝试选取剩余整个响应字符串进行验签
        return responseString.length();
    }

    private static int extractJsonObjectEndPosition(String responseString, int beginPosition) {
        //记录当前尚未发现配对闭合的大括号
        LinkedList<Character> braces = new LinkedList<Character>();
        //记录当前字符是否在双引号中
        boolean inQuotes = false;
        //记录当前字符前面连续的转义字符个数
        int consecutiveEscapeCount = 0;
        //从待验签字符的起点开始遍历后续字符串，找出待验签字符串的终止点，终点即是与起点{配对的}
        for (int index = beginPosition; index < responseString.length(); ++index) {
            //提取当前字符
            char currentChar = responseString.charAt(index);

            //如果当前字符是"且前面有偶数个转义标记（0也是偶数）
            if (currentChar == DOUBLE_QUOTES && consecutiveEscapeCount % 2 == 0) {
                //是否在引号中的状态取反
                inQuotes = !inQuotes;
            }
            //如果当前字符是{且不在引号中
            else if (currentChar == LEFT_BRACE && !inQuotes) {
                //将该{加入未闭合括号中
                braces.push(LEFT_BRACE);
            }
            //如果当前字符是}且不在引号中
            else if (currentChar == RIGHT_BRACE && !inQuotes) {
                //弹出一个未闭合括号
                braces.pop();
                //如果弹出后，未闭合括号为空，说明已经找到终点
                if (braces.isEmpty()) {
                    return index + 1;
                }
            }

            //如果当前字符是转义字符
            if (currentChar == '\\') {
                //连续转义字符个数+1
                ++consecutiveEscapeCount;
            } else {
                //连续转义字符个数置0
                consecutiveEscapeCount = 0;
            }
        }

        //如果没有找到配对的闭合括号，说明验签内容片段提取失败，直接尝试选取剩余整个响应字符串进行验签
        return responseString.length();
    }

    private static class SignSourceData {
        /**
         * 待验签原始内容
         */
        private final String sourceData;
        /**
         * 待验签原始内容在响应字符串中的起始位置
         */
        private final int    beginIndex;
        /**
         * 待验签原始内容在响应字符串中的结束位置
         */
        private final int    endIndex;

        SignSourceData(String sourceData, int beginIndex, int endIndex) {
            this.sourceData = sourceData;
            this.beginIndex = beginIndex;
            this.endIndex = endIndex;
        }

        String getSourceData() {
            return sourceData;
        }

        public int getBeginIndex() {
            return beginIndex;
        }

        int getEndIndex() {
            return endIndex;
        }
    }
}