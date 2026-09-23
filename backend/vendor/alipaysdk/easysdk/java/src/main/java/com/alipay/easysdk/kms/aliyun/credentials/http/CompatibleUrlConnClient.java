package com.alipay.easysdk.kms.aliyun.credentials.http;

import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSocketFactory;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;
import java.io.ByteArrayOutputStream;
import java.io.Closeable;
import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.security.KeyManagementException;
import java.security.NoSuchAlgorithmException;
import java.util.List;
import java.util.Map;

public class CompatibleUrlConnClient implements Closeable {
    protected static final String ACCEPT_ENCODING = "Accept-Encoding";

    public static HttpResponse compatibleGetResponse(HttpRequest request)
            throws IOException, NoSuchAlgorithmException, KeyManagementException {
        CompatibleUrlConnClient client = new CompatibleUrlConnClient();
        HttpResponse response = client.syncInvoke(request);
        client.close();
        return response;
    }

    public HttpResponse syncInvoke(HttpRequest request) throws IOException, NoSuchAlgorithmException, KeyManagementException {
        InputStream content = null;
        HttpResponse response = null;
        HttpURLConnection httpConn = buildHttpConnection(request);

        try {
            httpConn.connect();
            content = httpConn.getInputStream();
            response = new HttpResponse(httpConn.getURL().toString());
            parseHttpConn(response, httpConn, content);
            return response;
        } catch (IOException e) {
            content = httpConn.getErrorStream();
            response = new HttpResponse(httpConn.getURL().toString());
            parseHttpConn(response, httpConn, content);
            return response;
        } finally {
            if (content != null) {
                content.close();
            }
            httpConn.disconnect();
        }
    }

    public SSLSocketFactory createSSLSocketFactory() throws NoSuchAlgorithmException, KeyManagementException {
        X509TrustManager compositeX509TrustManager = new CompositeX509TrustManager();
        SSLContext sslContext = SSLContext.getInstance("TLS");
        sslContext.init(null, new TrustManager[] {compositeX509TrustManager}, new java.security.SecureRandom());
        return sslContext.getSocketFactory();
    }

    public void checkHttpRequest(HttpRequest request) {
        String strUrl = request.getUrl();
        if (null == strUrl) {
            throw new IllegalArgumentException("URL is null for HttpRequest.");
        }
        if (null == request.getMethod()) {
            throw new IllegalArgumentException("Method is not set for HttpRequest.");
        }
    }

    public HttpURLConnection initHttpConnection(URL url, HttpRequest request)
            throws IOException, KeyManagementException, NoSuchAlgorithmException {
        HttpURLConnection httpConn;
        if ("https".equalsIgnoreCase(url.getProtocol())) {
            HttpsURLConnection httpsConn = (HttpsURLConnection) url.openConnection();
            SSLSocketFactory sslSocketFactory = createSSLSocketFactory();
            httpsConn.setSSLSocketFactory(sslSocketFactory);
            httpsConn.setHostnameVerifier(new TrueHostnameVerifier());
            httpConn = httpsConn;
        } else {
            httpConn = (HttpURLConnection) url.openConnection();
        }
        httpConn.setRequestMethod(request.getMethod().toString());
        httpConn.setInstanceFollowRedirects(false);
        httpConn.setDoOutput(true);
        httpConn.setDoInput(true);
        httpConn.setUseCaches(false);
        httpConn.setConnectTimeout(request.getConnectTimeout());
        httpConn.setReadTimeout(request.getReadTimeout());
        httpConn.setRequestProperty(ACCEPT_ENCODING, "identity");
        return httpConn;
    }

    public HttpURLConnection buildHttpConnection(HttpRequest request) throws IOException, NoSuchAlgorithmException, KeyManagementException {
        checkHttpRequest(request);
        String strUrl = request.getUrl();
        URL url = new URL(strUrl);
        System.setProperty("sun.net.http.allowRestrictedHeaders", "true");
        HttpURLConnection httpConn = initHttpConnection(url, request);
        return httpConn;
    }

    public void parseHttpConn(HttpResponse response, HttpURLConnection httpConn, InputStream content)
            throws IOException, NoSuchAlgorithmException {
        byte[] buff = readContent(content);
        response.setStatus(httpConn.getResponseCode());
        response.setResponseMessage(httpConn.getResponseMessage());
        Map<String, List<String>> headers = httpConn.getHeaderFields();
        for (Map.Entry<String, List<String>> entry : headers.entrySet()) {
            String key = entry.getKey();
            if (null == key) {
                continue;
            }
            List<String> values = entry.getValue();
            StringBuilder builder = new StringBuilder(values.get(0));
            for (int i = 1; i < values.size(); i++) {
                builder.append(",");
                builder.append(values.get(i));
            }
            response.putHeaderParameter(key, builder.toString());
        }
        String type = response.getHeaderValue("Content-Type");
        if (buff != null && type != null) {
            response.setEncoding("UTF-8");
            String[] split = type.split(";");
            response.setHttpContentType(FormatType.mapAcceptToFormat(split[0].trim()));
            if (split.length > 1 && split[1].contains("=")) {
                String[] codings = split[1].split("=");
                response.setEncoding(codings[1].trim().toUpperCase());
            }
        }
        response.setHttpContent(buff, response.getEncoding(), response.getHttpContentType());
    }

    private byte[] readContent(InputStream content) throws IOException {
        if (content == null) {
            return null;
        }
        ByteArrayOutputStream outputStream = new ByteArrayOutputStream();
        byte[] buff = new byte[1024];
        while (true) {
            final int read = content.read(buff);
            if (read == -1) {
                break;
            }
            outputStream.write(buff, 0, read);
        }
        return outputStream.toByteArray();
    }

    @Override
    public void close() throws IOException {

    }
}