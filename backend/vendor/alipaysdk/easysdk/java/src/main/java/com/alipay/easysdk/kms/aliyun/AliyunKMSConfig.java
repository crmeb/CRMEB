package com.alipay.easysdk.kms.aliyun;


import com.alipay.easysdk.kernel.Config;
import com.aliyun.tea.*;

/**
 * KMS配置参数模型
 */
public class AliyunKMSConfig extends Config {
    /**
     * 阿里云官方申请的AccessKey Id
     */
    @NameInMap("aliyunAccessKeyId")
    public String aliyunAccessKeyId;

    /**
     * 阿里云官方申请的AccessKey Secret
     */
    @NameInMap("aliyunAccessKeySecret")
    public String aliyunAccessKeySecret;

    /**
     * 从阿里云官方获取的临时安全令牌Security Token
     */
    @NameInMap("aliyunSecurityToken")
    public String aliyunSecurityToken;

    /**
     * 阿里云RAM角色全局资源描述符
     */
    @NameInMap("aliyunRoleArn")
    public String aliyunRoleArn;

    /**
     * 阿里云RAM角色自定义策略
     */
    @NameInMap("aliyunRolePolicy")
    public String aliyunRolePolicy;

    /**
     * 阿里云ECS实例RAM角色名称
     */
    @NameInMap("aliyunRoleName")
    public String aliyunRoleName;

    /**
     * KMS主密钥ID
     */
    @NameInMap("kmsKeyId")
    public String kmsKeyId;

    /**
     * KMS主密钥版本ID
     */
    @NameInMap("kmsKeyVersionId")
    public String kmsKeyVersionId;

    /**
     * KMS服务地址
     * KMS服务地址列表详情，请参考：
     * https://help.aliyun.com/document_detail/69006.html?spm=a2c4g.11186623.2.9.783f77cfAoNhY6#concept-69006-zh
     */
    @NameInMap("kmsEndpoint")
    public String kmsEndpoint;

    /**
     * 凭据类型，支持的类型有"access_key"，"sts"，"ecs_ram_role"，"ram_role_arn"
     */
    @NameInMap("credentialType")
    public String credentialType;
}
