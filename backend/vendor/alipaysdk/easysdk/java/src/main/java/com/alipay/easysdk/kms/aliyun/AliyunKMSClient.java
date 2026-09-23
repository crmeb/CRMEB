package com.alipay.easysdk.kms.aliyun;

import com.alipay.easysdk.kernel.AlipayConstants;
import com.alipay.easysdk.kms.aliyun.models.AsymmetricSignRequest;
import com.alipay.easysdk.kms.aliyun.models.AsymmetricSignResponse;
import com.alipay.easysdk.kms.aliyun.models.GetPublicKeyRequest;
import com.alipay.easysdk.kms.aliyun.models.GetPublicKeyResponse;
import com.alipay.easysdk.kms.aliyun.models.RuntimeOptions;
import com.aliyun.tea.TeaConverter;
import com.aliyun.tea.TeaModel;
import com.aliyun.tea.TeaPair;
import org.bouncycastle.asn1.gm.GMNamedCurves;
import org.bouncycastle.asn1.x9.X9ECParameters;
import org.bouncycastle.crypto.Digest;
import org.bouncycastle.crypto.digests.SM3Digest;
import org.bouncycastle.crypto.params.ECDomainParameters;
import org.bouncycastle.crypto.params.ECPublicKeyParameters;
import org.bouncycastle.jcajce.provider.asymmetric.ec.BCECPublicKey;
import org.bouncycastle.jce.provider.BouncyCastleProvider;
import org.bouncycastle.math.ec.ECFieldElement;
import org.bouncycastle.util.encoders.Base64;

import java.security.KeyFactory;
import java.security.MessageDigest;
import java.security.PublicKey;
import java.security.Security;
import java.security.spec.X509EncodedKeySpec;
import java.util.HashMap;
import java.util.Map;

/**
 * 实现KMS的Client
 *
 * @author aliyunkms
 * @version $Id: AliyunKMSClient.java, v 0.1 2020年05月08日 10:53 PM aliyunkms Exp $
 */
public class AliyunKMSClient extends AliyunRpcClient {
    //支付宝signType与KMS签名算法映射表
    private static final Map<String, String> signAlgs    = new HashMap<>();
    private static final Map<String, String> digestAlgs  = new HashMap<>();
    private static final Map<String, String> namedCurves = new HashMap<>();

    static {
        digestAlgs.put("RSA_PKCS1_SHA_256", "SHA-256");
        digestAlgs.put("RSA_PSS_SHA_256", "SHA-256");
        digestAlgs.put("ECDSA_SHA_256", "SHA-256");

        namedCurves.put("SM2DSA", "sm2p256v1");

        signAlgs.put("RSA2", "RSA_PKCS1_SHA_256");
    }

    private String    keyId;
    private String    keyVersionId;
    private String    algorithm;
    private PublicKey publicKey;
    private String    protocol;
    private String    method;
    private String    version;
    private Integer   connectTimeout;
    private Integer   readTimeout;
    private Integer   maxAttempts;
    private boolean   ignoreSSL;

    public AliyunKMSClient(Map<String, Object> config) throws Exception {
        super(config);
        this.keyId = (String) config.get("kmsKeyId");
        this.keyVersionId = (String) config.get("kmsKeyVersionId");
        this.algorithm = signAlgs.get((String) config.get(AlipayConstants.SIGN_TYPE_CONFIG_KEY));
        this.publicKey = null;
        this.protocol = "HTTPS";
        this.method = "POST";
        this.version = "2016-01-20";
        this.connectTimeout = 15000;
        this.readTimeout = 15000;
        this.maxAttempts = 3;
        this.ignoreSSL = false;
    }

    private GetPublicKeyResponse _getPublicKey(GetPublicKeyRequest request) throws Exception {
        validateModel(request);
        RuntimeOptions runtime = RuntimeOptions.build(TeaConverter.buildMap(
                new TeaPair("connectTimeout", this.connectTimeout),
                new TeaPair("readTimeout", this.readTimeout),
                new TeaPair("maxAttempts", this.maxAttempts),
                new TeaPair("ignoreSSL", this.ignoreSSL)
        ));
        return TeaModel.toModel(
                this.doRequest("GetPublicKey", this.protocol, this.method, this.version, TeaModel.buildMap(request), null, runtime),
                new GetPublicKeyResponse());
    }

    private PublicKey getPublicKey(String keyId, String keyVersionId) throws Exception {
        GetPublicKeyRequest request = GetPublicKeyRequest.build(TeaConverter.buildMap(
                new TeaPair("KeyId", keyId),
                new TeaPair("KeyVersionId", keyVersionId)
        ));
        GetPublicKeyResponse response = _getPublicKey(request);
        String pemKey = response.publicKey;
        pemKey = pemKey.replaceFirst("-----BEGIN PUBLIC KEY-----", "");
        pemKey = pemKey.replaceFirst("-----END PUBLIC KEY-----", "");
        pemKey = pemKey.replaceAll("\\s", "");
        byte[] derKey = Base64.decode(pemKey);
        X509EncodedKeySpec keySpec = new X509EncodedKeySpec(derKey);
        Security.addProvider(new BouncyCastleProvider());
        return KeyFactory.getInstance("EC", "BC").generatePublic(keySpec);
    }

    private byte[] getZ(ECPublicKeyParameters ecPublicKeyParameters, ECDomainParameters ecDomainParameters) {
        Digest digest = new SM3Digest();
        digest.reset();

        String userID = "1234567812345678";
        addUserID(digest, userID.getBytes());

        addFieldElement(digest, ecDomainParameters.getCurve().getA());
        addFieldElement(digest, ecDomainParameters.getCurve().getB());
        addFieldElement(digest, ecDomainParameters.getG().getAffineXCoord());
        addFieldElement(digest, ecDomainParameters.getG().getAffineYCoord());
        addFieldElement(digest, ecPublicKeyParameters.getQ().getAffineXCoord());
        addFieldElement(digest, ecPublicKeyParameters.getQ().getAffineYCoord());

        byte[] result = new byte[digest.getDigestSize()];
        digest.doFinal(result, 0);
        return result;
    }

    private void addUserID(Digest digest, byte[] userID) {
        int len = userID.length * 8;
        digest.update((byte) (len >> 8 & 0xFF));
        digest.update((byte) (len & 0xFF));
        digest.update(userID, 0, userID.length);
    }

    private void addFieldElement(Digest digest, ECFieldElement v) {
        byte[] p = v.getEncoded();
        digest.update(p, 0, p.length);
    }

    private byte[] calcSM3Digest(PublicKey pubKey, byte[] message) {
        X9ECParameters x9ECParameters = GMNamedCurves.getByName(namedCurves.get(this.algorithm));
        ECDomainParameters ecDomainParameters = new ECDomainParameters(x9ECParameters.getCurve(), x9ECParameters.getG(),
                x9ECParameters.getN());
        BCECPublicKey localECPublicKey = (BCECPublicKey) pubKey;
        ECPublicKeyParameters ecPublicKeyParameters = new ECPublicKeyParameters(localECPublicKey.getQ(), ecDomainParameters);
        byte[] z = getZ(ecPublicKeyParameters, ecDomainParameters);
        Digest digest = new SM3Digest();
        digest.update(z, 0, z.length);
        digest.update(message, 0, message.length);
        byte[] result = new byte[digest.getDigestSize()];
        digest.doFinal(result, 0);
        return result;
    }

    private AsymmetricSignResponse _asymmetricSign(AsymmetricSignRequest request) throws Exception {
        validateModel(request);
        RuntimeOptions runtime = RuntimeOptions.build(TeaConverter.buildMap(
                new TeaPair("connectTimeout", this.connectTimeout),
                new TeaPair("readTimeout", this.readTimeout),
                new TeaPair("maxAttempts", this.maxAttempts),
                new TeaPair("ignoreSSL", this.ignoreSSL)
        ));
        return TeaModel.toModel(
                this.doRequest("AsymmetricSign", this.protocol, this.method, this.version, TeaModel.buildMap(request), null, runtime),
                new AsymmetricSignResponse());
    }

    private String asymmetricSign(String keyId, String keyVersionId, String algorithm, byte[] message) throws Exception {
        byte[] digest;
        if (algorithm.equals("SM2DSA")) {
            if (this.publicKey == null) {
                this.publicKey = getPublicKey(keyId, keyVersionId);
            }
            digest = calcSM3Digest(this.publicKey, message);
        } else {
            digest = MessageDigest.getInstance(digestAlgs.get(algorithm)).digest(message);
        }

        AsymmetricSignRequest request = AsymmetricSignRequest.build(TeaConverter.buildMap(
                new TeaPair("keyId", keyId),
                new TeaPair("keyVersionId", keyVersionId),
                new TeaPair("algorithm", algorithm),
                new TeaPair("digest", Base64.toBase64String(digest))
        ));
        AsymmetricSignResponse response = _asymmetricSign(request);
        return response.value;
    }

    /**
     * 计算签名
     *
     * @param content 待签名的内容
     * @return 签名值的Base64串
     */
    public String sign(String content) throws Exception {
        return asymmetricSign(this.keyId, this.keyVersionId, this.algorithm, content.getBytes(AlipayConstants.DEFAULT_CHARSET));
    }

    public String getAlgorithm() {
        return this.algorithm;
    }

    public void setAlgorithm(String algorithm) {
        this.algorithm = algorithm;
    }

    public String getKeyId() {
        return this.keyId;
    }

    public void setKeyId(String keyId) {
        this.keyId = keyId;
    }

    public String getKeyVersionId() {
        return this.keyVersionId;
    }

    public void setKeyVersionId(String keyVersionId) {
        this.keyVersionId = keyVersionId;
    }

    public PublicKey getPublicKey() {
        return this.publicKey;
    }

    public void setPublicKey(PublicKey publicKey) {
        this.publicKey = publicKey;
    }

    public String getProtocol() {
        return this.protocol;
    }

    public void setProtocol(String protocol) {
        this.protocol = protocol;
    }

    public String getMethod() {
        return this.method;
    }

    public void setMethod(String method) {
        this.method = method;
    }

    public String getVersion() {
        return this.version;
    }

    public void setVersion(String version) {
        this.version = version;
    }

    public Integer getConnectTimeout() {
        return this.connectTimeout;
    }

    public void setConnectTimeout(Integer connectTimeout) {
        this.connectTimeout = connectTimeout;
    }

    public Integer getReadTimeout() {
        return this.readTimeout;
    }

    public void setReadTimeout(Integer readTimeout) {
        this.readTimeout = readTimeout;
    }

    public Integer getMaxAttempts() {
        return this.maxAttempts;
    }

    public void setMaxAttempts(Integer maxAttempts) {
        this.maxAttempts = maxAttempts;
    }

    public boolean getIgnoreSSL() {
        return this.ignoreSSL;
    }

    public void setIgnoreSSL(boolean ignoreSSL) {
        this.ignoreSSL = ignoreSSL;
    }
}