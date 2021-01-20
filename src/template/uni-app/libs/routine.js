import store from '../store';
import { checkLogin } from './login';
import { login,routineLogin } from '../api/public';
import Cache from '../utils/cache';
import { STATE_R_KEY, USER_INFO, EXPIRES_TIME, LOGIN_STATUS} from './../config/cache';

class Routine 
{
	
	constructor() 
	{
	    this.scopeUserInfo = 'scope.userInfo';
	}
	
	async getUserCode(){
		let isAuth = await this.isAuth(), code = '' ;
		if(isAuth)
			code = await this.getCode();
		return code;
	}
	
	/**
	 * 获取用户信息
	 */
	getUserInfo(){
		let  that = this , code = this.getUserCode();
		return new Promise( (resolve,reject) => {
			uni.getUserInfo({
				lang: 'zh_CN',
				success(user) {
					if(code) user.code = code;
					resolve({userInfo:user,islogin:false});
				},
				fail(res){
					reject(res);
				}
			})
		})
	}
	
	/**
	 * 获取用户信息
	 */
	authorize()
	{
		let that = this;
		return new Promise((resolve,reject)=>{
			if(checkLogin())
				return resolve({
					userInfo:Cache.get(USER_INFO,true),
					islogin:true,
				});
			uni.authorize({
			    scope: that.scopeUserInfo,
			    success() {
					resolve({islogin:false});
			    },
				fail(res){
					reject(res);
				}
			})
		})
	}
	
	async getCode(){
		let provider = await this.getProvider();
		return new Promise((resolve,reject)=>{
			// if(Cache.has(STATE_R_KEY)){
			// 	return resolve(Cache.get(STATE_R_KEY));
			// }
			uni.login({
				provider:provider,
				success(res) {
					if (res.code) Cache.set(STATE_R_KEY, res.code ,10800);
					return resolve(res.code);
				},
				fail(){
					return reject(null);
				}
			})
		})
	}
	
	/**
	 * 获取服务供应商
	 */
	getProvider()
	{
		return new Promise((resolve,reject)=>{
			uni.getProvider({
				service:'oauth',
				success(res) {
					resolve(res.provider);
				},
				fail() {
					resolve(false);
				}
			});
		});
	}
	
	/**
	 * 是否授权
	 */
	isAuth(){
		let that = this;
		return new Promise((resolve,reject)=>{
			uni.getSetting({
				success(res) {
					if (!res.authSetting[that.scopeUserInfo]) {
						resolve(true)
					} else {
						resolve(true);
					}
				},
				fail(){
					 resolve(false);
				}
			});
		});
	}
	
	authUserInfo(data)
	{
		return new Promise((resolve, reject)=>{
			routineLogin(data).then(res=>{
				if(res.data.key !== undefined && res.data.key){
				}else{
					store.commit('UPDATE_USERINFO', res.data.userInfo);
					store.commit('SETUID', res.data.userInfo.uid);
					Cache.set(USER_INFO,res.data.userInfo);
				}
				return resolve(res);
			}).catch(res=>{
				return reject(res);
			})
		})
	}
}

export default new Routine();