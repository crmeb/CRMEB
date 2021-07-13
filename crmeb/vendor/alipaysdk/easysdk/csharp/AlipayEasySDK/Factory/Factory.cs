using System;
using Alipay.EasySDK.Kernel;

namespace Alipay.EasySDK.Factory
{
    /// <summary>
    /// 客户端工厂，用于快速配置和访问各种场景下的API Client
    ///
    /// 注：该Factory获取的Client不可储存重复使用，请每次均通过Factory完成调用
    /// </summary>
    public static class Factory
    {
        public const string SDK_VERSION = "alipay-easysdk-net-2.0.0";

        /// <summary>
        /// 将一些初始化耗时较多的信息缓存在上下文中
        /// </summary>
        private static Context context;

        /// <summary>
        /// 设置客户端参数，只需设置一次，即可反复使用各种场景下的API Client
        /// </summary>
        /// <param name="options">客户端参数对象</param>
        public static void SetOptions(Config options)
        {
            context = new Context(options, SDK_VERSION);
        }

        /// <summary>
        /// 基础能力相关
        /// </summary>
        public static class Base
        {
            /// <summary>
            /// 获取图片相关API Client
            /// </summary>
            /// <returns>图片相关API Client</returns>
            public static EasySDK.Base.Image.Client Image()
            {
                return new EasySDK.Base.Image.Client(new Client(context));
            }

            /// <summary>
            /// 获取视频相关API Client
            /// </summary>
            /// <returns>视频相关API Client</returns>
            public static EasySDK.Base.Video.Client Video()
            {
                return new EasySDK.Base.Video.Client(new Client(context));
            }

            /// <summary>
            /// 获取OAuth认证相关API Client
            /// </summary>
            /// <returns>OAuth认证相关API Client</returns>
            public static EasySDK.Base.OAuth.Client OAuth()
            {
                return new EasySDK.Base.OAuth.Client(new Client(context));
            }

            /// <summary>
            /// 获取小程序二维码相关API Client
            /// </summary>
            /// <returns>小程序二维码相关API Client</returns>
            public static EasySDK.Base.Qrcode.Client Qrcode()
            {
                return new EasySDK.Base.Qrcode.Client(new Client(context));
            }
        }

        /// <summary>
        /// 营销能力相关
        /// </summary>
        public static class Marketing
        {
            /// <summary>
            /// 获取生活号相关API Client
            /// </summary>
            /// <returns>生活号相关API Client</returns>
            public static EasySDK.Marketing.OpenLife.Client OpenLife()
            {
                return new EasySDK.Marketing.OpenLife.Client(new Client(context));
            }

            /// <summary>
            /// 获取支付宝卡包相关API Client
            /// </summary>
            /// <returns>支付宝卡包相关API Client</returns>
            public static EasySDK.Marketing.Pass.Client Pass()
            {
                return new EasySDK.Marketing.Pass.Client(new Client(context));
            }

            /// <summary>
            /// 获取小程序模板消息相关API Client
            /// </summary>
            /// <returns>小程序模板消息相关API Client</returns>
            public static EasySDK.Marketing.TemplateMessage.Client TemplateMessage()
            {
                return new EasySDK.Marketing.TemplateMessage.Client(new Client(context));
            }
        }

        /// <summary>
        /// 会员能力相关
        /// </summary>
        public static class Member
        {
            /// <summary>
            /// 获取支付宝身份认证相关API Client
            /// </summary>
            /// <returns>支付宝身份认证相关API Client</returns>
            public static EasySDK.Member.Identification.Client Identification()
            {
                return new EasySDK.Member.Identification.Client(new Client(context));
            }
        }

        /// <summary>
        /// 支付能力相关
        /// </summary>
        public static class Payment
        {
            /// <summary>
            /// 获取支付通用API Client
            /// </summary>
            /// <returns>支付通用API Client</returns>
            public static EasySDK.Payment.Common.Client Common()
            {
                return new EasySDK.Payment.Common.Client(new Client(context));
            }

            /// <summary>
            /// 获取当面付API Client
            /// </summary>
            /// <returns>当面付API Client</returns>
            public static EasySDK.Payment.FaceToFace.Client FaceToFace()
            {
                return new EasySDK.Payment.FaceToFace.Client(new Client(context));
            }

            /// <summary>
            /// 获取花呗API Client
            /// </summary>
            /// <returns>花呗API Client</returns>
            public static EasySDK.Payment.Huabei.Client Huabei()
            {
                return new EasySDK.Payment.Huabei.Client(new Client(context));
            }

            /// <summary>
            /// 获取手机APP支付API Client
            /// </summary>
            /// <returns>手机APP支付API Client</returns>
            public static EasySDK.Payment.App.Client App()
            {
                return new EasySDK.Payment.App.Client(new Client(context));
            }

            /// <summary>
            /// 获取电脑网站支付API Client
            /// </summary>
            /// <returns>电脑网站支付API</returns>
            public static EasySDK.Payment.Page.Client Page()
            {
                return new EasySDK.Payment.Page.Client(new Client(context));
            }

            /// <summary>
            /// 获取手机网站支付API Client
            /// </summary>
            /// <returns>手机网站支付API</returns>
            public static EasySDK.Payment.Wap.Client Wap()
            {
                return new EasySDK.Payment.Wap.Client(new Client(context));
            }
        }

        /// <summary>
        /// 安全能力相关
        /// </summary>
        public static class Security
        {
            /// <summary>
            /// 获取文本风险识别相关API Client
            /// </summary>
            /// <returns>文本风险识别相关API Client</returns>
            public static EasySDK.Security.TextRisk.Client TextRisk()
            {
                return new EasySDK.Security.TextRisk.Client(new Client(context));
            }
        }

        /// <summary>
        /// 辅助工具
        /// </summary>
        public static class Util
        {
            /// <summary>
            /// 获取OpenAPI通用接口，可通过自行拼装参数，调用几乎所有OpenAPI
            /// </summary>
            /// <returns>OpenAPI通用接口</returns>
            public static EasySDK.Util.Generic.Client Generic()
            {
                return new EasySDK.Util.Generic.Client(new Client(context));
            }

            /// <summary>
            /// 获取AES128加解密相关API Client，常用于会员手机号的解密
            /// </summary>
            /// <returns>AES128加解密相关API Client</returns>
            public static EasySDK.Util.AES.Client AES()
            {
                return new EasySDK.Util.AES.Client(new Client(context));
            }
        }
    }
}
