export function navigateTo(type, url, opt) {
	let toUrl = url;
	let api = 'navigateTo';
	toUrl = opt ? toUrl + '?' + convertObj(opt) : toUrl;
	switch (type) {
		case 1:
			api = 'navigateTo';
			break;
		case 2:
			api = 'redirectTo';
			break;
		case 3:
			api = 'reLaunch';
			break;
		case 4:
			api = 'switchTab';
			break;
		default:
			api = 'navigateTo'
			break;
	}
	uni[api]({
		url: toUrl,
		animationType: 'slide-in-right',
		animationDuration: 200
	});
}
export function navigateBack(delta) {
	uni.navigateBack({
		delta: delta
	});
}
export function setStorage(key, val) {
	if (typeof val == 'string') {
		uni.setStorageSync(key, val);
		return val
	}
	uni.setStorageSync(key, JSON.stringify(val));
}
export function getStorage(key) {
	let uu = uni.getStorageSync(key);
	try {
		if (typeof JSON.parse(uu) != 'number') {
			uu = JSON.parse(uu);
		}
	} catch (e) {}
	return uu;
}
export function removeStorage(key) {
	if (key) {
		uni.removeStorageSync(key);
	}
}
export function clearStorage() {
	try {
		uni.clearStorageSync();
	} catch (e) {
		throw new Error('处理失败');
	}
}
export function Toast(title, icon = 'none', obj = {}, duration = 800) {
	let toastData = {
		title: title,
		duration: duration,
		position: 'center',
		mask: true,
		icon: icon ? icon : 'none',
		...obj
	};
	uni.showToast(toastData);
}
export function Loading(title = '正在加载...', obj = {}) {
	uni.showLoading({
		title: title,
		mask: true,
		...obj
	});
}
export function hideLoading() {
	try {
		uni.hideLoading();
	} catch (e) {
		throw new Error('处理失败');
	}
}
export function Modal(title = '提示', content = '这是一个模态弹窗!', obj = {
	showCancel: true,
	cancelText: '取消',
	confirmText: '确定'
}) {
	obj.cancelText = '确定';
	obj.confirmText = '取消';
	return new Promise((reslove, reject) => {
		uni.showModal({
			title: title,
			content: content,
			...obj,
			success: (res) => {
				if (res.confirm) {
					reslove()
				}
				if (res.cancel) {
					reject()
				}
			}
		});
	})
}
export function ActionSheet(itemList, itemColor = "#000000") {
	return new Promise((reslove, reject) => {
		uni.showActionSheet({
			itemList: itemList,
			itemColor: itemColor,
			success: (res) => {
				reslove(res.tapIndex);
			},
			fail: function(res) {
				reject(res.errMsg);
			}
		});
	})
}
export function ScrollTo(ScrollTop) {
	uni.pageScrollTo({
		scrollTop: ScrollTop,
		duration: 300
	})
}
export function GetUserInfo() {
	return new Promise((reslove, reject) => {
		uni.getUserInfo({
			success(res) {
				reslove(res);
			},
			fail(rej) {
				reject(rej);
			}
		})
	})
}
export function Authorize(scoped = 'scope.userInfo') {
	return new Promise((reslove, reject) => {
		uni.authorize({
			scope: scoped,
			success(res) {
				reslove(res);
			},
			fail(rej) {
				reject(rej);
			}
		})
	})
}
export function convertObj(opt) {
	let str = '';
	let arr = [];
	Object.keys(opt).forEach(item => {
		arr.push(`${item}=${opt[item]}`);
	})
	str = arr.join('&');
	return str;
}
export function throttle(fn, delay) {
	var lastArgs;
	var timer;
	var delay = delay || 200;
	return function(...args) {
		lastArgs = args;
		if (!timer) {
			timer = setTimeout(() => {
				timer = null;
				fn.apply(this, lastArgs);
			}, delay);
		}
	}
}
export function chooseImage(count) {
	return new Promise((reslove, reject) => {
		uni.chooseImage({
			count: count,
			sizeType: ['original', 'compressed'],
			sourceType: ['album', 'camera'],
			success: (res) => {
				reslove(res);
			},
			fail: (rej) => {
				reject(rej);
			}
		});
	})
}
export function serialize(data) {
	if (data != null && data != '') {
		try {
			return JSON.parse(JSON.stringify(data));
		} catch (e) {
			if (data instanceof Array) {
				return [];
			}
			return {};
		}
	}
	return data;
}
Date.prototype.format = function(fmt) {
	let o = {
		'M+': this.getMonth() + 1,
		'd+': this.getDate(),
		'h+': this.getHours(),
		'm+': this.getMinutes(),
		's+': this.getSeconds(),
		'q+': Math.floor((this.getMonth() + 3) / 3),
		S: this.getMilliseconds()
	};
	if (/(y+)/.test(fmt)) {
		fmt = fmt.replace(RegExp.$1, String(this.getFullYear()).substr(4 - RegExp.$1.length));
	}
	for (let k in o) {
		if (new RegExp('(' + k + ')').test(fmt)) {
			fmt = fmt.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ('00' + o[k]).substr(String(o[k]).length));
		}
	}
	return fmt;
};
export function formatDate(nS, format) {
	if (!nS) {
		return '';
	}
	format = format || 'yyyy-MM-dd hh:mm:ss';
	return new Date(nS).format(format);
}
export function pathToBase64(path) {
	return new Promise(function(resolve, reject) {
		if (typeof window === 'object' && 'document' in window) {
			if (typeof FileReader === 'function') {
				var xhr = new XMLHttpRequest()
				xhr.open('GET', path, true)
				xhr.responseType = 'blob'
				xhr.onload = function() {
					if (this.status === 200) {
						let fileReader = new FileReader()
						fileReader.onload = function(e) {
							resolve(e.target.result)
						}
						fileReader.onerror = reject
						fileReader.readAsDataURL(this.response)
					}
				}
				xhr.onerror = reject
				xhr.send()
				return
			}
			var canvas = document.createElement('canvas')
			var c2x = canvas.getContext('2d')
			var img = new Image
			img.onload = function() {
				canvas.width = img.width
				canvas.height = img.height
				c2x.drawImage(img, 0, 0)
				resolve(canvas.toDataURL())
				canvas.height = canvas.width = 0
			}
			img.onerror = reject
			img.src = path
			return
		}
		if (typeof plus === 'object') {
			plus.io.resolveLocalFileSystemURL(getLocalFilePath(path), function(entry) {
				entry.file(function(file) {
					var fileReader = new plus.io.FileReader()
					fileReader.onload = function(data) {
						resolve(data.target.result)
					}
					fileReader.onerror = function(error) {
						reject(error)
					}
					fileReader.readAsDataURL(file)
				}, function(error) {
					reject(error)
				})
			}, function(error) {
				reject(error)
			})
			return
		}
		if (typeof wx === 'object' && wx.canIUse('getFileSystemManager')) {
			wx.getFileSystemManager().readFile({
				filePath: path,
				encoding: 'base64',
				success: function(res) {
					resolve('data:image/png;base64,' + res.data)
				},
				fail: function(error) {
					reject(error)
				}
			})
			return
		}
		reject(new Error('not support'))
	})
}
export function showWeekFirstDay() {
	var date = new Date();
	var weekday = date.getDay() || 7;
	date.setDate(date.getDate() - weekday + 1);
	return formatDate(date, 'yyyy-MM-dd');
}
export function showMonthFirstDay() {
	var MonthFirstDay = new Date().setDate(1);
	return formatDate(new Date(MonthFirstDay).getTime(), 'yyyy-MM-dd');
}
var now = new Date();
var nowMonth = now.getMonth();
var nowYear = now.getYear();
nowYear += (nowYear < 2000) ? 1900 : 0;

function getQuarterStartMonth() {
	var quarterStartMonth = 0;
	if (nowMonth < 3) {
		quarterStartMonth = 0;
	}
	if (2 < nowMonth && nowMonth < 6) {
		quarterStartMonth = 3;
	}
	if (5 < nowMonth && nowMonth < 9) {
		quarterStartMonth = 6;
	}
	if (nowMonth > 8) {
		quarterStartMonth = 9;
	}
	return quarterStartMonth;
}
export function getQuarterStartDate() {
	var quarterStartDate = new Date(nowYear, getQuarterStartMonth(), 1);
	return formatDate(quarterStartDate, 'yyyy-MM-dd');
}
export function unique(data) {
	data = data || [];
	var n = {};
	for (var i = 0; i < data.length; i++) {
		var v = JSON.stringify(data[i]);
		if (typeof(v) == "undefined") {
			n[v] = 1;
		}
	}
	data.length = 0;
	for (var i in n) {
		data[data.length] = i;
	}
	return data;
}