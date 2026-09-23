/**
 * Alipay.com Inc.
 * Copyright (c) 2004-2020 All Rights Reserved.
 */
package com.alipay.easysdk.util.aes;

import com.alipay.easysdk.TestAccount.Mini;
import com.alipay.easysdk.factory.Factory;
import com.alipay.easysdk.kernel.Config;
import org.junit.Before;
import org.junit.Test;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.MatcherAssert.assertThat;

public class ClientTest {

    @Before
    public void setUp() {
        Config config = Mini.getConfig();
        config.encryptKey = "aa4BtZ4tspm2wnXLb1ThQA==";
        Factory.setOptions(config);
    }

    @Test
    public void testDecrypt() throws Exception {
        String plainText = Factory.Util.AES().decrypt("ILpoMowjIQjfYMR847rnFQ==");
        assertThat(plainText, is("test1234567"));
    }

    @Test
    public void testEncrypt() throws Exception {
        String cipherText = Factory.Util.AES().encrypt("test1234567");
        assertThat(cipherText, is("ILpoMowjIQjfYMR847rnFQ=="));
    }
}