package com.alipay.easysdk.kms.aliyun.credentials.http;

import com.alipay.easysdk.kms.aliyun.credentials.exceptions.CredentialException;
import com.alipay.easysdk.kms.aliyun.credentials.utils.Base64Utils;

import javax.net.ssl.KeyManager;
import javax.net.ssl.X509TrustManager;
import java.io.UnsupportedEncodingException;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.Collections;
import java.util.HashMap;
import java.util.Map;

public class HttpMessage {
    protected static final String CONTENT_TYPE = "Content-Type";
    protected static final String CONTENT_MD5 = "Content-MD5";
    protected static final String CONTENT_LENGTH = "Content-Length";
    protected FormatType httpContentType = null;
    protected byte[] httpContent = null;
    protected String encoding = null;
    protected Map<String, String> headers = new HashMap<String, String>();
    protected Integer connectTimeout = null;
    protected Integer readTimeout = null;
    private String url = null;
    private MethodType method = null;
    protected boolean ignoreSSLCerts = false;
    private KeyManager[] keyManagers = null;
    private X509TrustManager[] x509TrustManagers = null;

    public HttpMessage(String strUrl) {
        this.url = strUrl;
    }

    public HttpMessage() {
    }

    public FormatType getHttpContentType() {
        return httpContentType;
    }

    public void setHttpContentType(FormatType httpContentType) {
        this.httpContentType = httpContentType;
    }

    public byte[] getHttpContent() {
        return httpContent;
    }

    public String getHttpContentString() throws CredentialException {
        String stringContent = "";
        if (this.httpContent != null) {
            try {
                if (this.encoding == null) {
                    stringContent = new String(this.httpContent);
                } else {
                    stringContent = new String(this.httpContent, this.encoding);
                }
            } catch (UnsupportedEncodingException e) {
                throw new CredentialException("UnsupportedEncoding: Can not parse response due to unsupported encoding.");
            }
        }
        return stringContent;
    }

    public void setHttpContent(byte[] httpContent, String encoding, FormatType format) throws NoSuchAlgorithmException {
        this.httpContent = httpContent;
        if (null == httpContent) {
            this.headers.remove(CONTENT_MD5);
            this.headers.put(CONTENT_LENGTH, "0");
            this.headers.remove(CONTENT_TYPE);
            this.httpContentType = null;
            this.httpContent = null;
            this.encoding = null;
            return;
        }

        // for GET HEADER DELETE OPTION method, sdk should ignore the content
        if (getMethod() != null && !getMethod().hasContent()) {
            httpContent = new byte[0];
        }

        this.httpContent = httpContent;
        this.encoding = encoding;
        String contentLen = String.valueOf(httpContent.length);
        String strMd5 = md5Sum(httpContent);
        this.headers.put(CONTENT_MD5, strMd5);
        this.headers.put(CONTENT_LENGTH, contentLen);
        if (null != format) {
            this.headers.put(CONTENT_TYPE, FormatType.mapFormatToAccept(format));
        }
    }

    public static String md5Sum(byte[] buff) throws NoSuchAlgorithmException {
        MessageDigest md = MessageDigest.getInstance("MD5");
        byte[] messageDigest = md.digest(buff);
        return Base64Utils.encode(messageDigest);

    }

    public String getEncoding() {
        return encoding;
    }

    public void setEncoding(String encoding) {
        this.encoding = encoding;
    }

    public Map<String, String> getHeaders() {
        return Collections.unmodifiableMap(headers);
    }

    public String getHeaderValue(String name) {
        return this.headers.get(name);
    }

    public void putHeaderParameter(String name, String value) {
        if (null != name && null != value) {
            this.headers.put(name, value);
        }
    }

    public void setHeaders(Map<String, String> headers) {
        this.headers = headers;
    }

    public Integer getConnectTimeout() {
        return connectTimeout;
    }

    public void setConnectTimeout(Integer connectTimeout) {
        this.connectTimeout = connectTimeout;
    }

    public Integer getReadTimeout() {
        return readTimeout;
    }

    public void setReadTimeout(Integer readTimeout) {
        this.readTimeout = readTimeout;
    }

    public String getUrl() {
        return url;
    }

    public void setUrl(String url) {
        this.url = url;
    }

    public MethodType getMethod() {
        return method;
    }

    public void setMethod(MethodType method) {
        this.method = method;
    }

    public boolean isIgnoreSSLCerts() {
        return ignoreSSLCerts;
    }

    public void setIgnoreSSLCerts(boolean ignoreSSLCerts) {
        this.ignoreSSLCerts = ignoreSSLCerts;
    }

    public KeyManager[] getKeyManagers() {
        return keyManagers;
    }

    public void setKeyManagers(KeyManager[] keyManagers) {
        this.keyManagers = keyManagers;
    }

    public X509TrustManager[] getX509TrustManagers() {
        return x509TrustManagers;
    }

    public void setX509TrustManagers(X509TrustManager[] x509TrustManagers) {
        this.x509TrustManagers = x509TrustManagers;
    }
}
