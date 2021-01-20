class AuthWechat {
    /**
     * 是否是微信
     */
    isWeixin() {
        return navigator.userAgent.toLowerCase().indexOf("micromessenger") !== -1;
    }

    /**
     * 是否是手机端
     */
    _isMobile() {
        let flag = navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i)
        return flag;
    }
}
export default new AuthWechat();