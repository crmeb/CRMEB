/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk.kernel.util;

import java.util.Map;
import java.util.Map.Entry;

/**
 * 生成页面信息辅助类
 *
 * @author zhongyu
 * @version : PageUtil.java, v 0.1 2020年02月12日 3:11 下午 zhongyu Exp $
 */
public class PageUtil {
    /**
     * 生成表单
     *
     * @param actionUrl  表单提交链接
     * @param parameters 表单参数
     * @return 表单字符串
     */
    public static String buildForm(String actionUrl, Map<String, String> parameters) {
        return "<form name=\"punchout_form\" method=\"post\" action=\""
                + actionUrl
                + "\">\n"
                + buildHiddenFields(parameters)
                + "<input type=\"submit\" value=\"立即支付\" style=\"display:none\" >\n"
                + "</form>\n"
                + "<script>document.forms[0].submit();</script>";
    }

    private static String buildHiddenFields(Map<String, String> parameters) {
        if (parameters == null || parameters.isEmpty()) {
            return "";
        }
        StringBuilder builder = new StringBuilder();
        for (Entry<String, String> pair : parameters.entrySet()) {
            // 除去参数中的空值
            if (pair.getKey() == null || pair.getValue() == null) {
                continue;
            }
            builder.append(buildHiddenField(pair.getKey(), pair.getValue()));
        }
        return builder.toString();
    }

    private static String buildHiddenField(String key, String value) {
        StringBuilder builder = new StringBuilder(64);
        builder.append("<input type=\"hidden\" name=\"");
        builder.append(key);
        builder.append("\" value=\"");
        //转义双引号
        String a = value.replace("\"", "&quot;");
        builder.append(a).append("\">\n");
        return builder.toString();
    }
}