import CryptoJS from 'crypto-js';
import JSEncrypt from 'jsencrypt';

/**
 * @word hash256要加密的内容
 * @keyWord String  服务器随机返回的关键字
 *  */
export function aesEncryptHash(word, keyWord = 'XwKsGlMcdPMEhR1B') {
  var key = CryptoJS.enc.Utf8.parse(keyWord);
  var srcs = CryptoJS.enc.Utf8.parse(word);
  var encrypted = CryptoJS.HmacSHA256(srcs, key, { mode: CryptoJS.mode.ECB, padding: CryptoJS.pad.Pkcs7 });
  return encrypted.toString();
}
/**
 * @word key加密
 * @keyWord String  服务器随机返回的关键字
 *  */
export function encryptWithKey(password, publicKey) {
  const encryptor = new JSEncrypt();
  encryptor.setPublicKey(publicKey);
  return encryptor.encrypt(password);
}
