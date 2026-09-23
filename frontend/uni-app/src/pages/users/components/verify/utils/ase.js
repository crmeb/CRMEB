import CryptoJS from'./crypto-js.js'
export function aesEncrypt(word,keyWord="XwKsGlMcdPMEhR1B"){
var key=CryptoJS.enc.Utf8.parse(keyWord);
var srcs=CryptoJS.enc.Utf8.parse(word);
var encrypted=CryptoJS.AES.encrypt(srcs,key,{mode:CryptoJS.mode.ECB,padding:CryptoJS.pad.Pkcs7});
return encrypted.toString();
}