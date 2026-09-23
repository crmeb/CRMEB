package com.alipay.easysdk.kms.aliyun.credentials.http;

public class HttpResponse extends HttpMessage {
    private int status;
    private String responseMessage;

    public HttpResponse(String strUrl) {
        super(strUrl);
    }

    public HttpResponse() {
    }

    public boolean isSuccess() {
        return 200 <= this.status && this.status < 300;
    }

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }

    public String getResponseMessage() {
        return responseMessage;
    }

    public void setResponseMessage(String responseMessage) {
        this.responseMessage = responseMessage;
    }
}
