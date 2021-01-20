<!--
  parser 主模块组件
  github：https://github.com/jin-yufeng/Parser 
  docs：https://jin-yufeng.github.io/Parser
  插件市场：https://ext.dcloud.net.cn/plugin?id=805
  author：JinYufeng
  update：2020/04/14
-->
<template>
	<view>
		<slot v-if="!nodes.length" />
		<!--#ifdef APP-PLUS-NVUE-->
		<web-view id="top" ref="web" :src="src" :style="'margin-top:-2px;height:'+height+'px'" @onPostMessage="_message" />
		<!--#endif-->
		<!--#ifndef APP-PLUS-NVUE-->
		<view id="top" :style="showAm+(selectable?';user-select:text;-webkit-user-select:text':'')" :animation="scaleAm" @tap="_tap"
		 @touchstart="_touchstart" @touchmove="_touchmove">
			<!--#ifdef H5-->
			<div :id="'rtf'+uid"></div>
			<!--#endif-->
			<!--#ifndef H5-->
			<trees :nodes="nodes" :lazy-load="lazyLoad" :loadVideo="loadVideo" />
			<image v-for="(item, index) in imgs" v-bind:key="index" :id="index" :src="item" hidden @load="_load" />
			<!--#endif-->
		</view>
		<!--#endif-->
	</view>
</template>

<script>
	// #ifndef H5 || APP-PLUS-NVUE
	import trees from './libs/trees';
	var cache = {},
		// #ifdef MP-WEIXIN || MP-TOUTIAO
		fs = uni.getFileSystemManager ? uni.getFileSystemManager() : null,
		// #endif
		Parser = require('./libs/MpHtmlParser.js');
	var document; // document 补丁包 https://jin-yufeng.github.io/Parser/#/instructions?id=document
	// 计算 cache 的 key
	function hash(str) {
		for (var i = str.length, val = 5381; i--;)
			val += (val << 5) + str.charCodeAt(i);
		return val;
	}
	// #endif
	// #ifdef H5 || APP-PLUS-NVUE
	var rpx = uni.getSystemInfoSync().screenWidth / 750,
		cfg = require('./libs/config.js');
	// #endif
	// #ifdef APP-PLUS-NVUE
	var dom = weex.requireModule('dom');
	// #endif
	export default {
		name: 'parser',
		data() {
			return {
				// #ifdef APP-PLUS
				loadVideo: false,
				// #endif
				// #ifdef H5
				uid: this._uid,
				// #endif
				// #ifdef APP-PLUS-NVUE
				src: '',
				height: 1,
				// #endif
				// #ifndef APP-PLUS-NVUE
				scaleAm: '',
				showAm: '',
				imgs: [],
				// #endif
				nodes: []
			}
		},
		// #ifndef H5 || APP-PLUS-NVUE
		components: {
			trees
		},
		// #endif
		props: {
			'html': null,
			// #ifndef MP-ALIPAY
			'autopause': {
				type: Boolean,
				default: true
			},
			// #endif
			'autosetTitle': {
				type: Boolean,
				default: true
			},
			// #ifndef H5 || APP-PLUS-NVUE
			'compress': Number,
			'useCache': Boolean,
			'xml': Boolean,
			// #endif
			'domain': String,
			// #ifndef MP-BAIDU || MP-ALIPAY || APP-PLUS
			'gestureZoom': Boolean,
			// #endif
			// #ifdef MP-WEIXIN || MP-QQ || H5 || APP-PLUS
			'lazyLoad': Boolean,
			// #endif
			'selectable': Boolean,
			'tagStyle': Object,
			'showWithAnimation': Boolean,
			'useAnchor': Boolean
		},
		watch: {
			html(html) {
				this.setContent(html);
			}
		},
		mounted() {
			// 图片数组
			this.imgList = [];
			this.imgList.each = function(f) {
				for (var i = 0, len = this.length; i < len; i++)
					this.setItem(i, f(this[i], i, this));
			}
			this.imgList.setItem = function(i, src) {
				if (i == void 0 || !src) return;
				// #ifndef MP-ALIPAY || APP-PLUS
				// 去重
				if (src.indexOf('http') == 0 && this.includes(src)) {
					var newSrc = '';
					for (var j = 0, c; c = src[j]; j++) {
						if (c == '/' && src[j - 1] != '/' && src[j + 1] != '/') break;
						newSrc += Math.random() > 0.5 ? c.toUpperCase() : c;
					}
					newSrc += src.substr(j);
					return this[i] = newSrc;
				}
				// #endif
				this[i] = src;
				// 暂存 data src
				if (src.includes('data:image')) {
					var filePath, info = src.match(/data:image\/(\S+?);(\S+?),(.+)/);
					if (!info) return;
					// #ifdef MP-WEIXIN || MP-TOUTIAO
					filePath = `${wx.env.USER_DATA_PATH}/${Date.now()}.${info[1]}`;
					fs && fs.writeFile({
						filePath,
						data: info[3],
						encoding: info[2],
						success: () => this[i] = filePath
					})
					// #endif
					// #ifdef APP-PLUS
					filePath = `_doc/parser_tmp/${Date.now()}.${info[1]}`;
					var bitmap = new plus.nativeObj.Bitmap();
					bitmap.loadBase64Data(src, () => {
						bitmap.save(filePath, {}, () => {
							bitmap.clear()
							this[i] = filePath;
						})
					})
					// #endif
				}
			}
			if (this.html) this.setContent(this.html);
		},
		beforeDestroy() {
			// #ifdef H5
			if (this._observer) this._observer.disconnect();
			// #endif
			this.imgList.each(src => {
				// #ifdef APP-PLUS
				if (src && src.includes('_doc')) {
					plus.io.resolveLocalFileSystemURL(src, entry => {
						entry.remove();
					});
				}
				// #endif
				// #ifdef MP-WEIXIN || MP-TOUTIAO
				if (src && src.includes(uni.env.USER_DATA_PATH))
					fs && fs.unlink({
						filePath: src
					})
				// #endif
			})
			clearInterval(this._timer);
		},
		methods: {
			// #ifdef H5 || APP-PLUS-NVUE
			_Dom2Str(nodes) {
				var str = '';
				for (var node of nodes) {
					if (node.type == 'text')
						str += node.text;
					else {
						str += ('<' + node.name);
						for (var attr in node.attrs || {})
							str += (' ' + attr + '="' + node.attrs[attr] + '"');
						if (!node.children || !node.children.length) str += '>';
						else str += ('>' + this._Dom2Str(node.children) + '</' + node.name + '>');
					}
				}
				return str;
			},
			_handleHtml(html, append) {
				if (typeof html != 'string') html = this._Dom2Str(html.nodes || html);
				// 处理 rpx
				if (html.includes('rpx'))
					html = html.replace(/[0-9.]+\s*rpx/g, $ => parseFloat($) * rpx + 'px');
				if (!append) {
					// 处理 tag-style 和 userAgentStyles
					var style = '<style>@keyframes show{0%{opacity:0}100%{opacity:1}}';
					for (var item in cfg.userAgentStyles)
						style += `${item}{${cfg.userAgentStyles[item]}}`;
					for (item in this.tagStyle)
						style += `${item}{${this.tagStyle[item]}}`;
					style += '</style>';
					html = style + html;
				}
				return html;
			},
			// #endif
			setContent(html, append) {
				// #ifdef APP-PLUS-NVUE
				if (!html) {
					this.src = '';
					this.height = 1;
					return;
				}
				if (append) return;
				plus.io.resolveLocalFileSystemURL('_doc', entry => {
					entry.getDirectory('parser_tmp', {
						create: true
					}, entry => {
						var fileName = Date.now() + '.html';
						entry.getFile(fileName, {
							create: true
						}, entry => {
							entry.createWriter(writer => {
								writer.onwriteend = () => {
									this.nodes = [1];
									this.src = '_doc/parser_tmp/' + fileName;
									this.$nextTick(function() {
										entry.remove();
									})
								}
								html =
									'<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1' +
									(this.selectable ? '' : ',user-scalable=no') +
									'"><script type="text/javascript" src="https://js.cdn.aliyun.dcloud.net.cn/dev/uni-app/uni.webview.1.5.2.js"></' +
									'script><base href="' + this.domain + '">' + this._handleHtml(html) +
									'<script>"use strict";function post(t){uni.postMessage({data:t})}' +
									(this.showWithAnimation ? 'document.body.style.animation="show .5s",' : '') +
									'document.addEventListener("UniAppJSBridgeReady",function(){post({action:"load",text:document.body.innerText});var t=document.getElementsByTagName("title");t.length&&post({action:"getTitle",title:t[0].innerText});for(var e,o=document.getElementsByTagName("img"),n=[],i=0,r=0;e=o[i];i++)e.onerror=function(){post({action:"error",source:"img",target:this})},e.hasAttribute("ignore")||"A"==e.parentElement.nodeName||(e.i=r++,n.push(e.src),e.onclick=function(){post({action:"preview",img:{i:this.i,src:this.src}})});post({action:"getImgList",imgList:n});for(var a,s=document.getElementsByTagName("a"),c=0;a=s[c];c++)a.onclick=function(){var t,e=this.getAttribute("href");if("#"==e[0]){var r=document.getElementById(e.substr(1));r&&(t=r.offsetTop)}return post({action:"linkpress",href:e,offset:t}),!1};;for(var u,m=document.getElementsByTagName("video"),d=0;u=m[d];d++)u.style.maxWidth="100%",u.onerror=function(){post({action:"error",source:"video",target:this})}' +
									(this.autopause ? ',u.onplay=function(){for(var t,e=0;t=m[e];e++)t!=this&&t.pause()}' : '') +
									';for(var g,l=document.getElementsByTagName("audio"),p=0;g=l[p];p++)g.onerror=function(){post({action:"error",source:"audio",target:this})};window.onload=function(){post({action:"ready",height:document.body.scrollHeight})}});</' +
									'script>';
								writer.write(html);
							});
						})
					})
				})
				// #endif
				// #ifdef H5
				if (!html) {
					if (this.rtf && !append) this.rtf.parentNode.removeChild(this.rtf);
					return;
				}
				var div = document.createElement('div');
				if (!append) {
					if (this.rtf) this.rtf.parentNode.removeChild(this.rtf);
					this.rtf = div;
				} else {
					if (!this.rtf) this.rtf = div;
					else this.rtf.appendChild(div);
				}
				div.innerHTML = this._handleHtml(html, append);
				for (var styles = this.rtf.getElementsByTagName('style'), i = 0, style; style = styles[i++];) {
					style.innerHTML = style.innerHTML.replace(/body/g, '#rtf' + this._uid);
					style.setAttribute('scoped', 'true');
				}
				// 懒加载
				if (!this._observer && this.lazyLoad && IntersectionObserver) {
					this._observer = new IntersectionObserver(changes => {
						for (let item, i = 0; item = changes[i++];) {
							if (item.isIntersecting) {
								item.target.src = item.target.getAttribute('data-src');
								item.target.removeAttribute('data-src');
								this._observer.unobserve(item.target);
							}
						}
					}, {
						rootMargin: '900px 0px 900px 0px'
					})
				}
				var _ts = this;
				// 获取标题
				var title = this.rtf.getElementsByTagName('title');
				if (title.length && this.autosetTitle)
					uni.setNavigationBarTitle({
						title: title[0].innerText
					})
				// 图片处理
				this.imgList.length = 0;
				var imgs = this.rtf.getElementsByTagName('img');
				for (let i = 0, j = 0, img; img = imgs[i]; i++) {
					img.style.maxWidth = '100%';
					var src = img.getAttribute('src');
					if (this.domain && src) {
						if (src[0] == '/') {
							if (src[1] == '/')
								img.src = (this.domain.includes('://') ? this.domain.split('://')[0] : '') + ':' + src;
							else img.src = this.domain + src;
						} else if (!src.includes('://')) img.src = this.domain + '/' + src;
					}
					if (!img.hasAttribute('ignore') && img.parentElement.nodeName != 'A') {
						img.i = j++;
						_ts.imgList.push(img.src || img.getAttribute('data-src'));
						img.onclick = function() {
							var preview = true;
							this.ignore = () => preview = false;
							_ts.$emit('imgtap', this);
							if (preview) {
								uni.previewImage({
									current: this.i,
									urls: _ts.imgList
								});
							}
						}
					}
					img.onerror = function() {
						_ts.$emit('error', {
							source: 'img',
							target: this
						});
					}
					if (_ts.lazyLoad && this._observer && img.src && img.i != 0) {
						img.setAttribute('data-src', img.src);
						img.removeAttribute('src');
						this._observer.observe(img);
					}
				}
				// 链接处理
				var links = this.rtf.getElementsByTagName('a');
				for (var link of links) {
					link.onclick = function() {
						var jump = true,
							href = this.getAttribute('href');
						_ts.$emit('linkpress', {
							href,
							ignore: () => jump = false
						});
						if (jump && href) {
							if (href[0] == '#') {
								if (_ts.useAnchor) {
									_ts.navigateTo({
										id: href.substr(1)
									})
								}
							} else if (href.indexOf('http') == 0 || href.indexOf('//') == 0)
								return true;
							else {
								uni.navigateTo({
									url: href
								})
							}
						}
						return false;
					}
				}
				// 视频处理
				var videos = this.rtf.getElementsByTagName('video');
				_ts.videoContexts = videos;
				for (let video, i = 0; video = videos[i++];) {
					video.style.maxWidth = '100%';
					video.onerror = function() {
						_ts.$emit('error', {
							source: 'video',
							target: this
						});
					}
					video.onplay = function() {
						if (_ts.autopause)
							for (let item, i = 0; item = _ts.videoContexts[i++];)
								if (item != this) item.pause();
					}
				}
				// 音频处理
				var audios = this.rtf.getElementsByTagName('audios');
				for (var audio of audios)
					audio.onerror = function() {
						_ts.$emit('error', {
							source: 'audio',
							target: this
						});
					}
				this.document = this.rtf;
				if (!append) document.getElementById('rtf' + this._uid).appendChild(this.rtf);
				this.$nextTick(() => {
					this.nodes = [1];
					this.$emit('load');
				})
				setTimeout(() => this.showAm = '', 500);
				// #endif
				// #ifndef H5 || APP-PLUS-NVUE
				var nodes;
				if (!html)
					return this.nodes = [];
				else if (typeof html == 'string') {
					let parser = new Parser(html, this);
					// 缓存读取
					if (this.useCache) {
						var hashVal = hash(html);
						if (cache[hashVal])
							nodes = cache[hashVal];
						else {
							nodes = parser.parse();
							cache[hashVal] = nodes;
						}
					} else nodes = parser.parse();
					this.$emit('parse', nodes);
				} else if (Object.prototype.toString.call(html) == '[object Array]') {
					// 非本插件产生的 array 需要进行一些转换
					if (html.length && html[0].PoweredBy != 'Parser') {
						let parser = new Parser(html, this);
						(function f(ns) {
							for (var i = 0, n; n = ns[i]; i++) {
								if (n.type == 'text') continue;
								n.attrs = n.attrs || {};
								for (var item in n.attrs)
									if (typeof n.attrs[item] != 'string') n.attrs[item] = n.attrs[item].toString();
								parser.matchAttr(n, parser);
								if (n.children && n.children.length) {
									parser.STACK.push(n);
									f(n.children);
									parser.popNode(parser.STACK.pop());
								} else n.children = void 0;
							}
						})(html);
					}
					nodes = html;
				} else if (typeof html == 'object' && html.nodes) {
					nodes = html.nodes;
					console.warn('错误的 html 类型：object 类型已废弃');
				} else
					return console.warn('错误的 html 类型：' + typeof html);
				// #ifdef APP-PLUS
				this.loadVideo = false;
				// #endif
				if (document) this.document = new document(this.nodes, 'nodes', this);
				if (append) this.nodes = this.nodes.concat(nodes);
				else this.nodes = nodes;
				if (nodes.length && nodes[0].title && this.autosetTitle)
					uni.setNavigationBarTitle({
						title: nodes[0].title
					})
				this.$nextTick(() => {
					this.imgList.length = 0;
					this.videoContexts = [];
					// #ifdef MP-TOUTIAO
					setTimeout(() => {
						// #endif
						var f = (cs) => {
							for (let i = 0, c; c = cs[i++];) {
								if (c.$options.name == 'trees') {
									for (var j = c.nodes.length, item; item = c.nodes[--j];) {
										if (item.c) continue;
										if (item.name == 'img') {
											this.imgList.setItem(item.attrs.i, item.attrs.src);
											// #ifndef MP-ALIPAY
											if (!c.observer && !c.imgLoad && item.attrs.i != '0') {
												if (this.lazyLoad && uni.createIntersectionObserver) {
													c.observer = uni.createIntersectionObserver(c);
													c.observer.relativeToViewport({
														top: 900,
														bottom: 900
													}).observe('._img', () => {
														c.imgLoad = true;
														c.observer.disconnect();
													})
												} else
													c.imgLoad = true;
											}
											// #endif
										}
										// #ifndef MP-ALIPAY
										else if (item.name == 'video') {
											var ctx = uni.createVideoContext(item.attrs.id, c);
											ctx.id = item.attrs.id;
											this.videoContexts.push(ctx);
										}
										// #endif
										// #ifdef MP-BAIDU || MP-ALIPAY || APP-PLUS
										if (item.attrs && item.attrs.id) {
											this.anchors = this.anchors || [];
											this.anchors.push({
												id: item.attrs.id,
												node: c
											})
										}
										// #endif
									}
								}
								if (c.$children.length)
									f(c.$children)
							}
						}
						f(this.$children);
						// #ifdef MP-TOUTIAO
					}, 200)
					this.$emit('load');
					// #endif
					// #ifdef APP-PLUS
					setTimeout(() => {
						this.loadVideo = true;
					}, 3000);
					// #endif
				})
				// #endif
				// #ifndef APP-PLUS-NVUE
				var height;
				clearInterval(this._timer);
				this._timer = setInterval(() => {
					// #ifdef H5
					var res = [this.rtf.getBoundingClientRect()];
					// #endif
					// #ifndef H5
					// #ifdef APP-PLUS
					uni.createSelectorQuery().in(this)
					// #endif
					// #ifndef APP-PLUS
					this.createSelectorQuery()
						// #endif
						.select('#top').boundingClientRect().exec(res => {
							// #endif
							this.width = res[0].width;
							if (res[0].height == height) {
								this.$emit('ready', res[0])
								clearInterval(this._timer);
							}
							height = res[0].height;
							// #ifndef H5
						});
					// #endif
				}, 350)
				if (this.showWithAnimation && !append) this.showAm = 'animation:show .5s';
				// #endif
			},
			getText(ns = this.nodes) {
				// #ifdef APP-PLUS-NVUE
				return this._text;
				// #endif
				// #ifdef H5
				return this.rtf.innerText;
				// #endif
				// #ifndef H5 || APP-PLUS-NVUE
				var txt = '';
				for (var i = 0, n; n = ns[i++];) {
					if (n.type == 'text') txt += n.text.replace(/&nbsp;/g, '\u00A0').replace(/&lt;/g, '<').replace(/&gt;/g, '>')
						.replace(/&amp;/g, '&');
					else if (n.type == 'br') txt += '\n';
					else {
						// 块级标签前后加换行
						var block = n.name == 'p' || n.name == 'div' || n.name == 'tr' || n.name == 'li' || (n.name[0] == 'h' && n.name[1] >
							'0' && n.name[1] < '7');
						if (block && txt && txt[txt.length - 1] != '\n') txt += '\n';
						if (n.children) txt += this.getText(n.children);
						if (block && txt[txt.length - 1] != '\n') txt += '\n';
						else if (n.name == 'td' || n.name == 'th') txt += '\t';
					}
				}
				return txt;
				// #endif
			},
			navigateTo(obj) {
				if (!this.useAnchor)
					return obj.fail && obj.fail({
						errMsg: 'Anchor is disabled'
					})
				// #ifdef APP-PLUS-NVUE
				if (!obj.id)
					dom.scrollToElement(this.$refs.web);
				else
					this.$refs.web.evalJs('var pos=document.getElementById("' + obj.id +
						'");if(pos)post({action:"linkpress",href:"#",offset:pos.offsetTop})');
				return obj.success && obj.success({
					errMsg: 'pageScrollTo:ok'
				});
				// #endif
				// #ifdef H5
				if (!obj.id) {
					window.scrollTo(0, this.rtf.offsetTop);
					return obj.success && obj.success({
						errMsg: 'pageScrollTo:ok'
					});
				}
				var target = document.getElementById(obj.id);
				if (!target) return obj.fail && obj.fail({
					errMsg: 'Label not found'
				});
				obj.scrollTop = this.rtf.offsetTop + target.offsetTop;
				uni.pageScrollTo(obj);
				// #endif
				// #ifndef H5
				var Scroll = (selector, component) => {
					uni.createSelectorQuery().in(component ? component : this).select(selector).boundingClientRect().selectViewport()
						.scrollOffset()
						.exec(res => {
							if (!res || !res[0])
								return obj.fail && obj.fail({
									errMsg: 'Label not found'
								});
							obj.scrollTop = res[1].scrollTop + res[0].top;
							uni.pageScrollTo(obj);
						})
				}
				if (!obj.id) Scroll('#top');
				else {
					// #ifndef MP-BAIDU || MP-ALIPAY || APP-PLUS
					Scroll('#top >>> #' + obj.id + ', #top >>> .' + obj.id);
					// #endif
					// #ifdef MP-BAIDU || MP-ALIPAY || APP-PLUS
					for (var anchor of this.anchors)
						if (anchor.id == obj.id)
							Scroll('#' + obj.id + ', .' + obj.id, anchor.node);
					// #endif
				}
				// #endif
			},
			getVideoContext(id) {
				// #ifndef APP-PLUS-NVUE
				if (!id) return this.videoContexts;
				else
					for (var i = this.videoContexts.length; i--;)
						if (this.videoContexts[i].id == id) return this.videoContexts[i];
				// #endif
			},
			// 预加载
			preLoad(html, num) {
				// #ifdef H5 || APP-PLUS-NVUE
				if (html.constructor == Array)
					html = this._Dom2Str(html);
				var script = "var contain=document.createElement('div');contain.innerHTML='" + html.replace(/'/g, "\\'") +
					"';for(var imgs=contain.querySelectorAll('img'),i=imgs.length-1;i>=" + num +
					";i--)imgs[i].removeAttribute('src');";
				// #endif
				// #ifdef APP-PLUS-NVUE
				this.$refs.web.evalJs(script);
				// #endif
				// #ifdef H5
				eval(script);
				// #endif
				// #ifndef H5 || APP-PLUS-NVUE
				if (typeof html == 'string') {
					var id = hash(html);
					html = new Parser(html, this).parse();
					cache[id] = html;
				}
				var wait = [];
				(function f(ns) {
					for (var i = 0, n; n = ns[i++];) {
						if (n.name == 'img' && n.attrs.src && !wait.includes(n.attrs.src))
							wait.push(n.attrs.src);
						f(n.children || []);
					}
				})(html);
				if (num) wait = wait.slice(0, num);
				this._wait = (this._wait || []).concat(wait);
				if (!this.imgs) this.imgs = this._wait.splice(0, 15);
				else if (this.imgs.length < 15)
					this.imgs = this.imgs.concat(this._wait.splice(0, 15 - this.imgs.length));
				// #endif
			},
			// #ifdef APP-PLUS-NVUE
			_message(e) {
				// 接收 web-view 消息
				var data = e.detail.data[0];
				if (data.action == 'load') {
					this.$emit('load');
					this._text = data.text;
				} else if (data.action == 'getTitle') {
					if (this.autosetTitle)
						uni.setNavigationBarTitle({
							title: data.title
						})
				} else if (data.action == 'getImgList') {
					this.imgList.length = 0;
					for (var i = data.imgList.length; i--;)
						this.imgList.setItem(i, data.imgList[i]);
				} else if (data.action == 'preview') {
					var preview = true;
					data.img.ignore = () => preview = false;
					this.$emit('imgtap', data.img);
					if (preview)
						uni.previewImage({
							current: data.img.i,
							urls: this.imgList
						})
				} else if (data.action == 'linkpress') {
					var jump = true,
						href = data.href;
					this.$emit('linkpress', {
						href,
						ignore: () => jump = false
					})
					if (jump && href) {
						if (href[0] == '#') {
							if (this.useAnchor)
								dom.scrollToElement(this.$refs.web, {
									offset: data.offset
								})
						} else if (href.includes('://'))
							plus.runtime.openWeb(href);
						else
							uni.navigateTo({
								url: href
							})
					}
				} else if (data.action == 'error')
					this.$emit('error', {
						source: data.source,
						target: data.target
					})
				else if (data.action == 'ready') {
					this.height = data.height;
					this.$nextTick(() => {
						uni.createSelectorQuery().in(this).select('#top').boundingClientRect().exec(res => {
							this.rect = res[0];
							this.$emit('ready', res[0]);
						})
					})
				}
			},
			// #endif
			// #ifndef APP-PLUS-NVUE
			// #ifndef H5
			_load(e) {
				if (this._wait.length)
					this.$set(this.imgs, e.target.id, this._wait.shift());
			},
			// #endif
			_tap(e) {
				// #ifndef MP-BAIDU || MP-ALIPAY || APP-PLUS
				if (this.gestureZoom && e.timeStamp - this._lastT < 300) {
					var initY = e.touches[0].pageY - e.currentTarget.offsetTop;
					if (this._zoom) {
						this._scaleAm.translateX(0).scale(1).step();
						uni.pageScrollTo({
							scrollTop: (initY + this._initY) / 2 - e.touches[0].clientY,
							duration: 400
						})
					} else {
						var initX = e.touches[0].pageX - e.currentTarget.offsetLeft;
						this._initY = initY;
						this._scaleAm = uni.createAnimation({
							transformOrigin: `${initX}px ${this._initY}px 0`,
							timingFunction: 'ease-in-out'
						});
						// #ifdef MP-TOUTIAO
						this._scaleAm.opacity(1);
						// #endif
						this._scaleAm.scale(2).step();
						this._tMax = initX / 2;
						this._tMin = (initX - this.width) / 2;
						this._tX = 0;
					}
					this._zoom = !this._zoom;
					this.scaleAm = this._scaleAm.export();
				}
				this._lastT = e.timeStamp;
				// #endif
			},
			_touchstart(e) {
				// #ifndef MP-BAIDU || MP-ALIPAY || APP-PLUS
				if (e.touches.length == 1)
					this._initX = this._lastX = e.touches[0].pageX;
				// #endif
			},
			_touchmove(e) {
				// #ifndef MP-BAIDU || MP-ALIPAY || APP-PLUS
				var diff = e.touches[0].pageX - this._lastX;
				if (this._zoom && e.touches.length == 1 && Math.abs(diff) > 20) {
					this._lastX = e.touches[0].pageX;
					if ((this._tX <= this._tMin && diff < 0) || (this._tX >= this._tMax && diff > 0))
						return;
					this._tX += (diff * Math.abs(this._lastX - this._initX) * 0.05);
					if (this._tX < this._tMin) this._tX = this._tMin;
					if (this._tX > this._tMax) this._tX = this._tMax;
					this._scaleAm.translateX(this._tX).step();
					this.scaleAm = this._scaleAm.export();
				}
				// #endif
			}
			// #endif
		}
	}
</script>

<style>
	@keyframes show {
		0% {
			opacity: 0
		}

		100% {
			opacity: 1;
		}
	}

	/* #ifdef MP-WEIXIN */
	:host {
		display: block;
		overflow: scroll;
		-webkit-overflow-scrolling: touch;
	}

	/* #endif */
</style>
