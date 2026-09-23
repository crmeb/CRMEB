/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk.kernel.util;

import com.alipay.easysdk.kernel.AlipayConstants;
import com.google.common.base.Preconditions;

import java.io.File;

/**
 * HTTP multipart/form-data格式相关工具类
 *
 * @author zhongyu
 * @version : MulitpartUtil.java, v 0.1 2020年02月08日 11:26 上午 zhongyu Exp $
 */
public class MultipartUtil {
    /**
     * 获取Multipart分界符
     *
     * @param boundary 用作分界的随机字符串
     * @return Multipart分界符
     */
    public static byte[] getEntryBoundary(String boundary) {
        return ("\r\n--" + boundary + "\r\n").getBytes();
    }

    /**
     * 获取Multipart结束标记
     *
     * @param boundary 用作分界的随机字符串
     * @return Multipart结束标记
     */
    public static byte[] getEndBoundary(String boundary) {
        return ("\r\n--" + boundary + "--\r\n").getBytes();
    }

    /**
     * 获取Multipart中的文本参数结构
     *
     * @param fieldName  字段名称
     * @param fieldValue 字段值
     * @return 文本参数结构
     */
    public static byte[] getTextEntry(String fieldName, String fieldValue) {
        String entry = "Content-Disposition:form-data;name=\""
                + fieldName
                + "\"\r\nContent-Type:text/plain\r\n\r\n"
                + fieldValue;
        return entry.getBytes(AlipayConstants.DEFAULT_CHARSET);
    }

    /**
     * 获取Multipart中的文件参数结构（不含文件内容，只有文件元数据）
     *
     * @param fieldName 字段名称
     * @param filePath  文件路径
     * @return 文件参数结构（不含文件内容）
     */
    public static byte[] getFileEntry(String fieldName, String filePath) {
        String entry = "Content-Disposition:form-data;name=\""
                + fieldName
                + "\";filename=\""
                + getFile(filePath).getName()
                + "\"\r\nContent-Type:application/octet-stream"
                + "\r\n\r\n";
        return entry.getBytes(AlipayConstants.DEFAULT_CHARSET);
    }

    private static File getFile(String filePath) {
        File file = new File(filePath);
        Preconditions.checkArgument(file.exists(), file.getAbsolutePath() + "文件不存在");
        Preconditions.checkArgument(file.getName().contains("."), "文件名必须带上正确的扩展名");
        return file;
    }
}