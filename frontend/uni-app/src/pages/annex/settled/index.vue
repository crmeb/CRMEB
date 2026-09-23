<template>
	<view v-if="status == -1 && !inloading" :style="colorStyle">
			<view class='merchantsSettled'>
				<image mode="widthFix" class="merchantBg" :src="headerBg" alt="" />
					<!-- <view class="application-record" @click="jumpToList">
						申请记录
						<text class="iconfont icon-xiangyou"></text>
					</view> -->
					<view class='list' v-if="isAgent">
						<view class="item">
							<view class="acea-row row-middle">
								<!-- <i class="icon iconfont icon-qiye"></i> -->
								<text class="item-name">{{$t(`代理商名称`)}}</text>
								<input type="text" maxlength="30" :placeholder="$t(`请输入代理商名称`)"
									v-model="merchantData.agent_name" @input="validateBtn"
									placeholder-class='placeholder' />
							</view>
						</view>
						<view class="item">
							<view class="acea-row row-middle">
								<!-- <i class="icon iconfont icon-yonghu3"></i> -->
								<text class="item-name">{{$t(`用户姓名`)}}</text>
								<input type="text" :placeholder="$t(`请输入姓名`)" v-model="merchantData.name"
									@input="validateBtn" placeholder-class='placeholder' />
							</view>
						</view>
						<view class="item">
							<view class="acea-row row-middle">
								<!-- <i class="icon iconfont icon-shoujihao"></i> -->
								<text class="item-name">{{$t(`联系电话`)}}</text>
								<input type="text" :placeholder="$t(`请输入手机号`)" v-model="merchantData.phone"
									@input="validateBtn" placeholder-class='placeholder' />
							</view>
						</view>
						<view class="item rel">
							<view class="acea-row row-middle">
								<!-- <i class="icon iconfont icon-yanzhengma"></i> -->
								<text class="item-name">{{$t(`验证码`)}}</text>
								<input type="text" :placeholder="$t(`填写验证码`)" v-model="merchantData.code"
									@input="validateBtn" class="codeIput" placeholder-class='placeholder' />
								<button class="code" :disabled="disabled" :class="disabled === true ? 'on' : ''"
									@click="code">
									{{ codeText }}
								</button>

							</view>
						</view>
						<view class="item">
							<view class="acea-row row-middle">
								<!-- <i class="icon iconfont icon-shoujihao"></i> -->
								<text class="item-name">{{$t(`邀请码`)}}</text>
								<input type="text" :placeholder="$t(`请输入代理商邀请码`)" v-model="merchantData.division_invite"
									@input="validateBtn" placeholder-class='placeholder' />
							</view>
						</view>
						<view class="item no-border">
							<view class='acea-row row-middle'>
								<text class="item-title">{{$t(`请上传营业执照及行业相关资质证明图片`)}}</text>
								<text class="item-desc">({{$t(`图片最多可上传10张,图片格式支持JPG、PNG、JPEG`)}})</text>
								<view class="upload">
									<view class='pictrue' v-for="(item,index) in images" :key="index"
										:data-index="index" @click="getPhotoClickIdx">
										<image :src='item'></image>
										<text class='iconfont icon-guanbi1' @click.stop='DelPic(index)'></text>
									</view>
									<view class='pictrue acea-row row-center-wrapper row-column' @click='uploadpic'
										v-if="images.length < 10">
										<text class='iconfont icon-icon25201'></text>
										<view>{{$t(`上传图片`)}}</view>
									</view>
								</view>
							</view>
						</view>

						<view class="item no-border acea-row row-middle">
							<checkbox-group @change='ChangeIsAgree'>
								<checkbox class="checkbox" :checked="isAgree ? true : false" backgroundColor="#ffffff" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" />{{$t(`已阅读并同意`)}}
							</checkbox-group>
							<button class="settleAgree" @click="getAgentAgreement">《{{$t(`代理商协议`)}}》</button>
						</view>
						<button class='submitBtn' :class="isAgree === true ? 'on':''"
							@click="formSubmit">{{$t(`提交申请`)}}</button>

					</view>
					<view class='list' v-else>
						<view class="item">
							<view class="acea-row row-middle row-between">
								<!-- <i class="icon iconfont icon-qiye"></i> -->
								<text class="item-name">{{$t(`用户昵称`)}}</text>
								<view class="text-right">{{ form.nickname }}</view>
							</view>
						</view>
						<view class="item">
							<view class="acea-row row-middle row-between">
								<!-- <i class="icon iconfont icon-yonghu3"></i> -->
								<text class="item-name">{{$t(`用户ID`)}}</text>
								<view class="fs-28 text-right">{{ form.uid }}123</view>
							</view>
						</view>
						<view class="item">
							<view class="acea-row row-middle row-between">
								<!-- <i class="icon iconfont icon-shoujihao"></i> -->
								<text class="item-name">{{$t(`分销员姓名`)}}</text>
								<input class="text-right" type="text" :placeholder="$t(`请输入分销员姓名`)" v-model="form.real_name"
									@input="validateBtn" placeholder-class='placeholder' />
							</view>
						</view><view class="item">
							<view class="acea-row row-middle row-between">
								<!-- <i class="icon iconfont icon-shoujihao"></i> -->
								<text class="item-name">{{$t(`联系电话`)}}</text>
								<input class="text-right" type="text" :placeholder="$t(`请输入手机号`)" v-model="form.phone"
									@input="validateBtn" placeholder-class='placeholder' />
							</view>
						</view>
						<view class="item rel">
							<view class="acea-row row-middle">
								<!-- <i class="icon iconfont icon-yanzhengma"></i> -->
								<text class="item-name">{{$t(`验证码`)}}</text>
								<input type="text" :placeholder="$t(`填写验证码`)" v-model="form.code"
									@input="validateBtn" class="codeIput" placeholder-class='placeholder' />
								<button class="code" :disabled="disabled" :class="disabled === true ? 'on' : ''"
									@click="code">
									{{ codeText }}
								</button>
						
							</view>
						</view>
						<view class="item">
							<view class="acea-row row-middle row-between">
								<!-- <i class="icon iconfont icon-shoujihao"></i> -->
								<text class="item-name">{{$t(`申请理由`)}}</text>
								<textarea class="text-area" :placeholder="$t(`请输入申请理由`)" v-model="form.content" cols="3" rows="4" placeholder-class='placeholder'></textarea>
							</view>
						</view>
						<view class="item no-border  acea-row row-middle">
							<checkbox-group @change='ChangeIsAgree'>
								<checkbox class="checkbox" :checked="isAgree ? true : false" backgroundColor="#ffffff" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" />{{$t(`已阅读并同意`)}}
							</checkbox-group>
							<button class="settleAgree" @click="getAgentAgreement">《{{$t(`分销员协议`)}}》</button>
						</view>
						<button class='submitBtn' :class="isAgree === true ? 'on':''"
							@click="formSpeadSubmit">{{$t(`提交申请`)}}</button>
					</view>
			</view>

		<view class="settlementAgreement" v-if="showProtocol">
			<view class="setAgCount">
				<i class="icon iconfont icon-cha" @click="showProtocol = false"></i>
				<div class="title">{{ $t(isAgent?`代理商入驻协议`:'分销说明')}}</div>
				<view class="content">
					<mp-html :content="protocol" :tag-style="tagStyle" />
				</view>
			</view>
		</view>
		<view class='loadingicon acea-row row-center-wrapper' v-if="loading">
			<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>
		</view>
		<!-- #ifdef MP -->
		<authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize>
		<!-- #endif -->
		<Verify ref="verify" @success="successVerify" :captchaType="captchaType" :imgSize="{ width: '330px', height: '155px' }"
			></Verify>
	</view>
	<view class="settledSuccessMain" v-else-if='status == 0'>
		<view class="settledSuccessful">
			<image class="image" src="../static/success.png" alt="" />
				<view class="title">{{$t(`恭喜，您的资料提交成功！`)}}</view>
				<view class="goHome" hover-class="none" @click="goHome">
					{{$t(`返回首页`)}}
				</view>
		</view>
	</view>
	<view class="settledSuccessMain" v-else-if='status == 1'>
		<view class="settledSuccessful">
			<image class="image" src="../static/success.png" alt="" />
				<view class="title">{{$t(`恭喜，您的资料通过审核！`)}}</view>
				<view class="goHome" hover-class="none" @click="goHome">
					{{$t(`返回首页`)}}
				</view>
		</view>
	</view>
	<view class="settledSuccessMain" v-else-if='status == 2'>
		<view class="settledSuccessful">
			<image class="image" src="../static/error.png" alt="" />
				<view class="title">{{$t(`您的申请未通过！`)}}</view>
				<view class="info" v-if="refusal_reason">{{refusal_reason}}</view>
				<view class="again" hover-class="none" @click="applyAgain">
					{{$t(`重新申请`)}}
				</view>
				<view class="goHome" hover-class="none" @click="goHome">
					{{$t(`返回首页`)}}
				</view>
		</view>
		
	</view>
</template>
<script setup>
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		create,
		getCodeApi,
		registerVerify,
		getHistoryData as getHistoryDataApi,
		getAgentAgreement as getAgentAgreementApi,
		userSpreadInfo,
		spreadCreateApi
	} from '@/api/store.js';
	import {
		ref,
		computed,
		nextTick,
		onBeforeUnmount,
		getCurrentInstance
	} from 'vue';
	import {
		onLoad,
		onShow
	} from '@dcloudio/uni-app';
	import {
		useAppStore
	} from '@/store/modules/app';
	import {
		storeToRefs
	} from 'pinia';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import {
		useColorTheme
	} from '@/composables/useColorTheme';
	import Verify from '../components/verify/verify.vue';
	import {
		useSendVerifyCode
	} from '@/composables/useSendVerifyCode';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';
	import { HTTP_REQUEST_URL } from '@/config/app';
	const app = getApp();
	const instance = getCurrentInstance();

	const {
		colorStyle
	} = useColorTheme();

	// SendVerifyCode mixin 的验证码倒计时（原 data.text 现名 codeText）
	const {
		disabled,
		codeText,
		captchaType,
		sendCode
	} = useSendVerifyCode();

	const appStore = useAppStore();
	const {
		isLogin
	} = storeToRefs(appStore);

	// ---------- data ----------
	const isAgent = ref(false);
	const inloading = ref(true);
	const status = ref(-1);
	const isAuto = ref(false); //没有授权的不会自动授权
	const isShowAuth = ref(false); //是否隐藏授权
	const codeUrl = ref("");
	const isAgree = ref(false);
	const showProtocol = ref(false);
	const isShowCode = ref(false);
	const loading = ref(false);
	const merchantData = ref({
		agent_name: "",
		name: "",
		phone: "",
		classification: '',
		division_invite: ''
	});
	const form = ref({
		nickname: '',
		uid: '',
		phone: '',
		code: '',
		real_name: ''
	});
	const validate = ref(false);
	const successful = ref(false);
	const keyCode = ref("");
	const codeVal = ref("");
	const codeKey = ref("");
	const protocol = ref(app.globalData.sys_intention_agree);
	const timer = ref("");
	const index = ref(0);
	const index1 = ref(0);
	const mer_classification = ref("");
	const mer_storeType = ref('');
	const images = ref([]);
	const tagStyle = ref({
		img: 'width:100%;display:block;',
		table: 'width:100%',
		video: 'width:100%'
	});
	const mer_i_id = ref(null); // 代理商申请id
	const isType = ref(false);
	const id = ref(0);
	const refusal_reason = ref("");
	const type = ref('agent');
	const region = ref('');
	// 模板 ref：验证码弹层
	const verify = ref(null);

	// ---------- computed ----------
	const headerBg = computed(() => {
		return HTTP_REQUEST_URL + `/statics/images/${isAgent.value ? 'agent_apply.jpg' : 'spread_apply.jpg'}`;
	});

	onBeforeUnmount(() => {
		clearTimeout(timer.value)
	});

	onLoad((options) => {
		if (options.id) {
			id.value = id
			uni.showLoading({
				title: i18n.t(`正在加载中`),
			});
		}
		if (isLogin.value) {
			if (options.type) isAgent.value = true
			nextTick(() => {
				if (!isAgent.value) {
					getInfo()
				} else {
					getHistoryData()
				}
			})
		} else {
			// #ifdef H5 || APP-PLUS
			toLogin();
			// #endif 
			// #ifdef MP
			isAuto.value = true;
			isShowAuth.value = true
			// #endif
		}

	});

	onShow(() => {

	});

	// ---------- methods ----------
	function getAgentAgreement() {
		if (isAgent.value) {
			getAgentAgreementApi().then(res => {
				isType.value = false;
				showProtocol.value = true;
				protocol.value = res.data.content
			})
		} else {
			isType.value = false;
			showProtocol.value = true;
		}

	}
	function code() {
		let phone = isAgent.value ? merchantData.value.phone : form.value.phone
		if (!phone) return util.Tips({
			title: i18n.t(`请填写手机号码`)
		});
		if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(phone)) return util.Tips({
			title: i18n.t(`请输入正确的手机号码`)
		});
		verify.value.show()
	}
	function successVerify(data) {
		verify.value.hide()
		getCodeApi()
			.then(res => {
				keyCode.value = res.data.key;
				getCode(data);
			})
			.catch(res => {
				util.Tips({
					title: res
				});
			});
	}
	async function getCode(data) {
		await registerVerify({
				phone: isAgent.value ? merchantData.value.phone : form.value.phone,
				type: type.value,
				key: keyCode.value,
				captchaType: captchaType,
				captchaVerification: data.captchaVerification
			})
			.then(res => {
				sendCode()
				util.Tips({
					title: res.msg
				});
			})
			.catch(res => {
				util.Tips({
					title: res
				});
			});
	}
	// 获取历史提交数据详情
	function getHistoryData() {
		getHistoryDataApi().then(res => {
			status.value = res.data.status
			let resData = res.data
			if (res.data.status !== -1) {
				let arr = Object.keys(merchantData.value)
				arr.map(item => {
					merchantData.value[item] = resData[item]
				})
				uni.hideLoading();
			}
			if (status.value === 2) {
				refusal_reason.value = resData.refusal_reason
			}
			inloading.value = false
		})
	}
	//获取代理商分类名称
	function getCategoryName(id, arr) {
		for (let i = 0; i < arr.length; i++) {
			if (arr[i].merchant_category_id === id) {
				return arr[i]['category_name']
			}
		}
	}
	// 图片预览
	// 获得相册 idx
	function getPhotoClickIdx(e) {
		let idx = e.currentTarget.dataset.index;
		imgPreview(images.value, idx);
	}
	// 图片预览
	function imgPreview(list, idx) {
		// list：图片 url 数组
		if (list && list.length > 0) {
			uni.previewImage({
				current: list[idx], //  传 Number H5端出现不兼容 
				urls: list
			});
		}
	}
	// 授权回调
	function onLoadFun() {
		isShowAuth.value = false;
	}
	// 授权关闭
	function authColse(e) {
		isShowAuth.value = e
	}
	function toggleTab(str) {
		instance.proxy.$refs[str].show();
	}
	// 首页
	function goHome() {
		uni.switchTab({
			url: '/pages/index/index'
		});
	}
	function applyAgain() {
		status.value = -1
	}
	/**
	 * 上传文件
	 * 
	 */
	function uploadpic() {
		util.uploadImageOne('upload/image', (res) => {
			images.value.push(res.data.url);
		});

	}
	/**
	 * 删除图片
	 * 
	 */
	function DelPic(index) {
		images.value.splice(index, 1);
	}

	// 历史遗留：getCaptcha 接口在 api/user.js 中从未存在（Vue2 下点图形验证码即报错），
	// Vite 下缺失具名导入会阻断模块加载，此处降级为本地 stub 保持模块可用
	const getCaptcha = () => Promise.reject(new Error('getCaptcha api not defined'));

	function getcaptcha() {
		getCaptcha().then(data => {
			codeUrl.value = data.data.captcha; //图片路径
			codeVal.value = data.data.code; //图片验证码
			codeKey.value = data.data.key //图片验证码key
		})
		isShowCode.value = true;
	}
	function onConfirm(val) {
		region.value = val.checkArr[0] + '-' + val.checkArr[1] + '-' + val.checkArr[2];
	}
	function ChangeIsAgree(e) {
		isAgree.value = !isAgree.value;
		validateBtn()
	}
	function getInfo() {
		userSpreadInfo()
			.then((res) => {
				let data = res.data.user;
				id.value = data.id || 0;
				form.value.nickname = data.nickname || '';
				form.value.uid = data.uid || '';
				form.value.phone = data.phone || '';
				form.value.real_name = data.real_name || '';
				form.value.content = data.content || '';
				status.value = data.status;
				refusal_reason.value = data.refusal_reason;
				protocol.value = res.data.agreement.content
				inloading.value = false
			})
			.catch((err) => {
				return util.Tips({
					title: err
				});
			});
	}
	function formSpeadSubmit() {
		if (!isAgree.value) return that.$util.Tips({
			title: that.$t(`请阅读并同意分销员协议`)
		});
		spreadCreateApi(id.value, form.value)
			.then((res) => {
				getInfo()
			})
			.catch((err) => {
				return util.Tips({
					title: err
				});
			});
	}
	function formSubmit(e) {
		if (validateForm() && validate.value) {
			let requestData = {
				uid: appStore.uid,
				phone: merchantData.value.phone,
				agent_name: merchantData.value.agent_name,
				name: merchantData.value.name,
				code: merchantData.value.code,
				division_invite: merchantData.value.division_invite,
				images: images.value,
				id: id.value
			}
			create(requestData).then(data => {
				if (data.status == 200) {
					timer.value = setTimeout(() => {
						getHistoryData()
					}, 1000)
				}

			}).catch(res => {
				util.Tips({
					title: res
				});
			})
		}
	}
	function validateBtn() {
		let value = merchantData.value;
		if (value.agent_name && value.name && value.phone && /^1(3|4|5|7|8|9|6)\d{9}$/i.test(value
				.phone) &&
			value.code && isAgree.value && value.classification) {
			if (!isShowCode.value) {
				validate.value = true;
			} else {
				if (codeVal.value) {
					validate.value = true;
				} else {
					validate.value = false;
				}
			}

		}
	}

	function validateForm() {
		let value = merchantData.value;

		if (!value.agent_name) return util.Tips({
			title: i18n.t(`请输入代理商名称`)
		});
		if (!value.name) return util.Tips({
			title: i18n.t(`请输入姓名`)
		});
		if (!value.phone) return util.Tips({
			title: i18n.t(`请输入手机号`)
		});
		if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(value.phone)) return util.Tips({
			title: i18n.t(`请输入正确的手机号码`)
		});
		if (!value.code) return util.Tips({
			title: i18n.t(`填写验证码`)
		});
		if (isShowCode.value && !codeVal.value) return util.Tips({
			title: i18n.t(`请填写图片验证码`)
		});
		if (!images.value.length) return util.Tips({
			title: i18n.t(`请上传营业执照`)
		});
		if (!isAgree.value) return util.Tips({
			title: i18n.t(`请勾选并同意入驻协议`)
		});
		validate.value = true;
		return true;
	}
	function jumpToList() {
		uni.navigateTo({
			url: "/pages/store/applicationRecord/index"
		})
	}
</script>

<style scoped lang="scss">
	.uni-input-placeholder {
		color: #B2B2B2;
	}

	.item-name {
		width: 190rpx;
	}

	.uni-list-cell {
		position: relative;

		.iconfont {
			font-size: 14px;
			color: #7a7a7a;
			position: absolute;
			right: 15px;
			top: 7rpx;
		}

		.icon-guanbi2 {
			right: 35px;
		}
	}

	.merchantsSettled {
		height: 100vh;
		overflow-y: scroll;
		background: linear-gradient(#fd3d1d 0%, #fd151b 100%);
		padding-bottom: 30px;
	}

	.merchantsSettled .merchantBg {
		width: 750rpx;
		width: 100%;
	}

	.merchantsSettled .list {
		background-color: #fff;
		border-radius: 12px;
		padding: 22px 0;
		margin: 0 15px;
		// position: absolute;
		// top: 300rpx;
		position: sticky;
		margin-top: -60px;
		width: calc(100% - 30px);
	}

	.application-record {
		position: absolute;
		display: flex;
		align-items: center;
		top: 240rpx;
		right: 0;
		color: #fff;
		font-size: 22rpx;
		background-color: rgba(0, 0, 0, 0.3);
		padding: 8rpx 18rpx;
		border-radius: 20px 0px 0px 20px;
	}

	.merchantsSettled .list .item {
		padding: 50rpx 0 20rpx;
		border-bottom: 1rpx solid #eee;
		position: relative;
		margin: 0 20px;
		.text-area{
			height: 200rpx;
			margin-top: 10rpx;
			font-size: 24rpx;
		}
		&.no-border {
			border-bottom: none;
			padding-left: 0;
			padding-right: 0;
		}

		.item-title {
			color: #666666;
			font-size: 28rpx;
			display: block;
		}

		.item-desc {
			color: #B2B2B2;
			font-size: 22rpx;
			display: block;
			margin-top: 9rpx;
			line-height: 36rpx;
		}
	}

	.acea-row,
	.upload {
		display: -webkit-box;
		display: -moz-box;
		display: -webkit-flex;
		display: -ms-flexbox;
		display: flex;
		-webkit-box-lines: multiple;
		-moz-box-lines: multiple;
		-o-box-lines: multiple;
		-webkit-flex-wrap: wrap;
		-ms-flex-wrap: wrap;
		flex-wrap: wrap;
	}

	.upload {
		margin-top: 20rpx;
	}

	.acea-row.row-middle {
		-webkit-box-align: center;
		-moz-box-align: center;
		-o-box-align: center;
		-ms-flex-align: center;
		-webkit-align-items: center;
		align-items: center;
		padding-left: 2px;
	}

	.acea-row.row-column {
		-webkit-box-orient: vertical;
		-moz-box-orient: vertical;
		-o-box-orient: vertical;
		-webkit-flex-direction: column;
		-ms-flex-direction: column;
		flex-direction: column;
	}

	.acea-row.row-center-wrapper {
		-webkit-box-align: center;
		-moz-box-align: center;
		-o-box-align: center;
		-ms-flex-align: center;
		-webkit-align-items: center;
		align-items: center;
		-webkit-box-pack: center;
		-moz-box-pack: center;
		-o-box-pack: center;
		-ms-flex-pack: center;
		-webkit-justify-content: center;
		justify-content: center;
	}

	.merchantsSettled .list .item .pictrue {
		width: 130rpx;
		height: 130rpx;
		margin: 24rpx 22rpx 0 0;
		position: relative;
		font-size: 11px;
		color: #bbb;

		&:nth-child(4n) {
			margin-right: 0;
		}

		&:nth-last-child(1) {
			border: 0.5px solid #ddd;
			box-sizing: border-box;
		}


		uni-image,
		image {
			width: 100%;
			height: 100%;
			border-radius: 1px;

			img {
				-webkit-touch-callout: none;
				-webkit-user-select: none;
				-moz-user-select: none;
				display: block;
				position: absolute;
				top: 0;
				left: 0;
				opacity: 0;
				width: 100%;
				height: 100%;
			}
		}

		.icon-guanbi1 {
			font-size: 33rpx;
			position: absolute;
			top: -10px;
			right: -10px;
		}
	}

	.uni-list-cell-db {
		position: relative;
	}

	.wenhao {
		width: 34rpx;
		height: 34rpx;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 28rpx;
		border-radius: 50%;
		background: #E3E3E3;
		color: #ffffff !important;
		margin-left: 4rpx;
		position: absolute;
		left: 122rpx;
	}

	.merchantsSettled .list .item .imageCode {
		position: absolute;
		top: 7px;
		right: 0;
	}

	.merchantsSettled .list .item .icon {
		font-size: 40rpx;
		color: #b4b1b4;
	}

	.merchantsSettled .list .item input {
		width: 400rpx;
		font-size: 28rpx;
		// margin-left: 30px;
	}

	.merchantsSettled .list .item .placeholder {
		color: #b2b2b2;
		font-size: 28rpx
	}

	.merchantsSettled .default {
		padding: 0 30rpx;
		height: 90rpx;
		background-color: #fff;
		margin-top: 23rpx;
	}

	.merchantsSettled .default checkbox {
		margin-right: 15rpx;
	}

	.merchantsSettled .acea-row uni-image {
		width: 20px;
		height: 20px;
		display: block;
	}

	.merchantsSettled .list .item .codeIput {
		width: 125px;
	}

	.uni-input-input {
		display: block;
		height: 100%;
		background: none;
		color: inherit;
		opacity: 1;
		-webkit-text-fill-color: currentcolor;
		font: inherit;
		line-height: inherit;
		letter-spacing: inherit;
		text-align: inherit;
		text-indent: inherit;
		text-transform: inherit;
		text-shadow: inherit;
	}

	.merchantsSettled .list .item .code {
		position: absolute;
		width: 93px;
		line-height: 27px;
		border: 1px solid #E93323;
		border-radius: 15px;
		color: #E93323;
		text-align: center;
		bottom: 8px;
		right: 0;
		font-size: 12px;
	}

	.merchantsSettled .list .item .code.on {
		background-color: #bbb;
		color: #fff;
		border-color: #bbb;
	}

	.merchantsSettled .submitBtn {
		width: 588rpx;
		margin: 0 auto;
		height: 86rpx;
		border-radius: 25px;
		text-align: center;
		line-height: 86rpx;
		font-size: 15px;
		color: #fff;
		background: #E3E3E3;
		margin-top: 25px;
	}

	.merchantsSettled .submitBtn.on {
		background: #E93323;
	}

	uni-checkbox-group,
	.settleAgree {
		display: inline-block;
		font-size: 24rpx;
	}

	uni-checkbox-group {
		color: #b2b2b2;
	}

	.settleAgree {
		color: #E93323;
		// position: relative;
		// top: 2px;
		// left: 8px;
	}

	.merchantsSettled uni-checkbox .uni-checkbox-wrapper {
		width: 30rpx;
		height: 30rpx;
		border: 2rpx solid #C3C3C3;
		border-radius: 15px;
	}

	.settlementAgreement {
		width: 100%;
		height: 100%;
		position: fixed;
		top: 0;
		left: 0;
		background: rgba(0, 0, 0, .5);
		z-index: 10;
	}

	.settlementAgreement .setAgCount {
		background: #fff;
		width: 656rpx;
		height: 458px;
		position: absolute;
		top: 50%;
		left: 50%;
		border-radius: 12rpx;
		-webkit-border-radius: 12rpx;
		padding: 52rpx;
		-webkit-transform: translate(-50%, -50%);
		-moz-transform: translate(-50%, -50%);
		transform: translate(-50%, -50%);
		overflow: hidden;

		.content {
			height: 900rpx;
			overflow-y: scroll;

:deep(p){
				font-size: 13px;
				line-height: 22px;
			}

:deep(img){
				max-width: 100%;
			}
		}
	}

	.settlementAgreement .setAgCount .icon {
		font-size: 42rpx;
		color: #b4b1b4;
		position: absolute;
		top: 15rpx;
		right: 15rpx;

	}

	.settlementAgreement .setAgCount .title {
		color: #333;
		font-size: 32rpx;
		text-align: center;
		font-weight: bold;
	}

	.settlementAgreement .setAgCount .content {
		margin-top: 32rpx;
		color: #333;
		font-size: 26rpx;
		line-height: 22px;
		text-align: justify;
		text-justify: distribute-all-lines;
		height: 756rpx;
		overflow-y: scroll;
	}

	.settledSuccessMain {
		height: 100vh;
		display: flex;
		flex-direction: column;
		background: #fff;
	}

	.settledSuccessful {
		flex: 1;
		width: 100%;
		padding: 0 56px;
		height: auto;
		background: #fff;
		text-align: center;
	}

	.settledSuccessful .image {
		width: 189px;
		height: 157px;
		margin-top: 66px;
	}

	.settledSuccessful .title {
		color: #333333;
		font-size: 16px;
		font-weight: bold;
		margin-top: 35px;
	}

	.settledSuccessful .info {
		color: #999;
		font-size: 26rpx;
		margin-top: 18rpx;
	}

	.settledSuccessful .goHome {
		margin: 20px auto 0;
		line-height: 43px;
		color: #282828;
		font-size: 15px;
		border: 1px solid #B4B4B4;
		border-radius: 60px;
	}

	.settledSuccessful .again {
		margin: 30px auto 0;
		line-height: 43px;
		color: #fff;
		font-size: 15px;
		background-color: #E93323;
		border-radius: 60px;
	}

:deep(uni-checkbox .uni-checkbox-input){
		width: 15px;
		height: 15px;
		position: relative;
	}

:deep(uni-checkbox .uni-checkbox-input.uni-checkbox-input-checked:before){
		font-size: 14px;
	}

:deep(uni-checkbox .uni-checkbox-input-checked){
		background-color: #fd151b !important;
	}

	.loadingicon {
		height: 100vh;
		overflow: hidden;
		position: absolute;
		top: 0;
		left: 0;
	}

	.icon-xiangyou {
		font-size: 22rpx;
	}

	// #ifdef MP
	checkbox-group {
		display: inline-block;
	}

	// #endif
	.setAgCount {
:deep(table){
			border: 1rpx solid #DDD;
			border-bottom: none;
			border-right: none;
		}

:deep(td),
		th {
			padding: 5rpx 10rpx;
			border-bottom: 1rpx solid #DDD;
			border-right: 1rpx solid #DDD;
		}
	}
</style>
