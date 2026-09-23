/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk.kernel.util;

import com.aliyun.tea.TeaModel;
import com.google.gson.Gson;

import java.util.HashMap;
import java.util.Map;
import java.util.Map.Entry;

/**
 * JSON工具类
 *
 * @author zhongyu
 * @version : JsonUtil.java, v 0.1 2020年02月18日 8:20 下午 zhongyu Exp $
 */
public class JsonUtil {
    /**
     * 将Map转换为Json字符串，转换过程中对于TeaModel，使用标注的字段名称而不是字段的变量名
     *
     * @param input 输入的Map
     * @return Json字符串
     */
    public static String toJsonString(Map<String, ?> input) {
        Map<String, Object> result = new HashMap<>();
        for (Entry<String, ?> pair : input.entrySet()) {
            if (pair.getValue() instanceof TeaModel) {
                result.put(pair.getKey(), getTeaModelMap((TeaModel) pair.getValue()));
            } else {
                result.put(pair.getKey(), pair.getValue());
            }
        }
        return new Gson().toJson(result);
    }

    private static Map<String, Object> getTeaModelMap(TeaModel teaModel) {
        Map<String, Object> result = new HashMap<>();
        Map<String, Object> teaModelMap = teaModel.toMap();
        for (Entry<String, Object> pair : teaModelMap.entrySet()) {
            if (pair.getValue() instanceof TeaModel) {
                result.put(pair.getKey(), getTeaModelMap((TeaModel) pair.getValue()));
            } else {
                result.put(pair.getKey(), pair.getValue());
            }
        }
        return result;
    }
}