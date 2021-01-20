<!--
  trees 递归显示组件
  github：https://github.com/jin-yufeng/Parser 
  docs：https://jin-yufeng.github.io/Parser
  插件市场：https://ext.dcloud.net.cn/plugin?id=805
  author：JinYufeng
  update：2020/04/13
-->
<template>
	<view class="interlayer">
		<block v-for="(n, index) in nodes" v-bind:key="index">
			<!--图片-->
			<!--#ifdef MP-WEIXIN || MP-QQ || MP-ALIPAY || APP-PLUS-->
			<rich-text v-if="n.name=='img'" :id="n.attrs.id" class="_img" :style="''+handler.getStyle(n.attrs.style)" :nodes="handler.getNode(n,!lazyLoad||imgLoad)"
			 :data-attrs="n.attrs" @tap="imgtap" @longpress="imglongtap" />
			<!--#endif-->
			<!--#ifdef MP-BAIDU || MP-TOUTIAO-->
			<rich-text v-if="n.name=='img'" :id="n.attrs.id" class="_img" :style="n.attrs.contain" :nodes='[n]' :data-attrs="n.attrs"
			 @tap="imgtap" @longpress="imglongtap" />
			<!--#endif-->
			<!--文本-->
			<!--#ifdef MP-WEIXIN || MP-QQ || APP-PLUS-->
			<rich-text v-else-if="n.decode" class="_entity" :nodes="[n]"></rich-text>
			<!--#endif-->
			<text v-else-if="n.type=='text'" decode>{{n.text}}</text>
			<text v-else-if="n.name=='br'">\n</text>
			<!--视频-->
			<view v-else-if="n.name=='video'">
				<view v-if="(!loadVideo||n.lazyLoad)&&!(controls[n.attrs.id]&&controls[n.attrs.id].play)" :id="n.attrs.id" :class="'_video '+(n.attrs.class||'')"
				 :style="n.attrs.style" @tap="_loadVideo" />
				<video v-else :id="n.attrs.id" :class="n.attrs.class" :style="n.attrs.style" :autoplay="n.attrs.autoplay||(controls[n.attrs.id]&&controls[n.attrs.id].play)"
				 :controls="n.attrs.controls" :loop="n.attrs.loop" :muted="n.attrs.muted" :poster="n.attrs.poster" :src="n.attrs.source[(controls[n.attrs.id]&&controls[n.attrs.id].index)||0]"
				 :unit-id="n.attrs['unit-id']" :data-id="n.attrs.id" data-from="video" data-source="source" @error="error" @play="play" />
			</view>
			<!--音频-->
			<audio v-else-if="n.name=='audio'" :class="n.attrs.class" :style="n.attrs.style" :author="n.attrs.author" :autoplay="n.attrs.autoplay"
			 :controls="n.attrs.controls" :loop="n.attrs.loop" :name="n.attrs.name" :poster="n.attrs.poster" :src="n.attrs.source[(controls[n.attrs.id]&&controls[n.attrs.id].index)||0]"
			 :data-id="n.attrs.id" data-from="audio" data-source="source" @error="error" @play="play" />
			<!--链接-->
			<view v-else-if="n.name=='a'" :class="'_a '+(n.attrs.class||'')" hover-class="_hover" :style="n.attrs.style"
			 :data-attrs="n.attrs" @tap="linkpress">
				<trees class="_span" :nodes="n.children" />
			</view>
			<!--广告（按需打开注释）-->
			<!--#ifdef MP-WEIXIN || MP-QQ || MP-TOUTIAO-->
			<!--<ad v-else-if="n.name=='ad'" :class="n.attrs.class" :style="n.attrs.style" :unit-id="n.attrs['unit-id']"
			 data-from="ad" @error="error" />-->
			<!--#endif-->
			<!--#ifdef MP-BAIDU-->
			<!--<ad v-else-if="n.name=='ad'" :class="n.attrs.class" :style="n.attrs.style" :appid="n.attrs.appid"
			 :apid="n.attrs.apid" :type="n.attrs.type" data-from="ad" @error="error" />-->
			<!--#endif-->
			<!--#ifdef APP-PLUS-->
			<!--<ad v-else-if="n.name=='ad'" :class="n.attrs.class" :style="n.attrs.style" :adpid="n.attrs.adpid"
			 data-from="ad" @error="error" />-->
			<!--#endif-->
			<!--列表-->
			<view v-else-if="n.name=='li'" :id="n.attrs.id" :class="n.attrs.class" :style="(n.attrs.style||'')+';display:flex'">
				<view v-if="n.type=='ol'" class="_ol-bef">{{n.num}}</view>
				<view v-else class="_ul-bef">
					<view v-if="n.floor%3==0" class="_ul-p1">█</view>
					<view v-else-if="n.floor%3==2" class="_ul-p2" />
					<view v-else class="_ul-p1" style="border-radius:50%">█</view>
				</view>
				<!--#ifdef MP-ALIPAY-->
				<view class="_li">
					<trees :nodes="n.children" />
				</view>
				<!--#endif-->
				<!--#ifndef MP-ALIPAY-->
				<trees class="_li" :nodes="n.children" :lazyLoad="lazyLoad" :loadVideo="loadVideo" />
				<!--#endif-->
			</view>
			<!--表格-->
			<view v-else-if="n.name=='table'&&n.c" :id="n.attrs.id" :class="n.attrs.class" :style="(n.attrs.style||'')+';display:table'">
				<view v-for="(tbody, i) in n.children" v-bind:key="i" :class="tbody.attrs.class" :style="(tbody.attrs.style||'')+(tbody.name[0]=='t'?';display:table-'+(tbody.name=='tr'?'row':'row-group'):'')">
					<view v-for="(tr, j) in tbody.children" v-bind:key="j" :class="tr.attrs.class" :style="(tr.attrs.style||'')+(tr.name[0]=='t'?';display:table-'+(tr.name=='tr'?'row':'cell'):'')">
						<trees v-if="tr.name=='td'" :nodes="tr.children" :lazyLoad="lazyLoad" :loadVideo="loadVideo" />
						<block v-else>
							<!--#ifdef MP-ALIPAY-->
							<view v-for="(td, k) in tr.children" v-bind:key="k" :class="td.attrs.class" :style="(td.attrs.style||'')+(td.name[0]=='t'?';display:table-'+(td.name=='tr'?'row':'cell'):'')">
								<trees :nodes="td.children" />
							</view>
							<!--#endif-->
							<!--#ifndef MP-ALIPAY-->
							<trees v-for="(td, k) in tr.children" v-bind:key="k" :class="td.attrs.class" :style="(td.attrs.style||'')+(td.name[0]=='t'?';display:table-'+(td.name=='tr'?'row':'cell'):'')"
							 :nodes="td.children" :lazyLoad="lazyLoad" :loadVideo="loadVideo" />
							<!--#endif-->
						</block>
					</view>
				</view>
			</view>
			<!--#ifdef APP-PLUS-->
			<iframe v-else-if="n.name=='iframe'" :style="n.attrs.style" :allowfullscreen="n.attrs.allowfullscreen" :frameborder="n.attrs.frameborder"
			 :width="n.attrs.width" :height="n.attrs.height" :src="n.attrs.src" />
			<embed v-else-if="n.name=='embed'" :style="n.attrs.style" :width="n.attrs.width" :height="n.attrs.height" :src="n.attrs.src" />
			<!--#endif-->
			<!--富文本-->
			<!--#ifdef MP-WEIXIN || MP-QQ || MP-ALIPAY || APP-PLUS-->
			<rich-text v-else-if="handler.useRichText(n)" :id="n.attrs.id" :class="'_p __'+n.name" :nodes="[n]" />
			<!--#endif-->
			<!--#ifdef MP-BAIDU || MP-TOUTIAO-->
			<rich-text v-else-if="!(n.c||n.continue)" :id="n.attrs.id" :class="_p" :style="n.attrs.contain" :nodes="[n]" />
			<!--#endif-->
			<!--#ifdef MP-ALIPAY-->
			<view v-else :id="n.attrs.id" :class="'_'+n.name+' '+(n.attrs.class||'')" :style="n.attrs.style">
				<trees :nodes="n.children" />
			</view>
			<!--#endif-->
			<!--#ifndef MP-ALIPAY-->
			<trees v-else :class="(n.attrs.id||'')+' _'+n.name+' '+(n.attrs.class||'')" :style="n.attrs.style" :nodes="n.children"
			 :lazyLoad="lazyLoad" :loadVideo="loadVideo" />
			<!--#endif-->
		</block>
	</view>
</template>
<script module="handler" lang="wxs" src="./handler.wxs"></script>
<script module="handler" lang="sjs" src="./handler.sjs"></script>
<script>
	global.Parser = {};
	import trees from './trees'
	export default {
		components: {
			trees
		},
		name: 'trees',
		data() {
			return {
				controls: {},
				// #ifdef MP-WEIXIN || MP-QQ || APP-PLUS
				imgLoad: false,
				// #endif
				// #ifndef APP-PLUS
				loadVideo: true
				// #endif
			}
		},
		props: {
			nodes: Array,
			// #ifdef MP-WEIXIN || MP-QQ || H5 || APP-PLUS
			lazyLoad: Boolean,
			// #endif
			// #ifdef APP-PLUS
			loadVideo: Boolean
			// #endif
		},
		mounted() {
			// 获取顶层组件
			this.top = this.$parent;
			while (this.top.$options.name != 'parser') {
				if (this.top.top) {
					this.top = this.top.top;
					break;
				}
				this.top = this.top.$parent;
			}
		},
		// #ifdef MP-WEIXIN || MP-QQ || APP-PLUS
		beforeDestroy() {
			if (this.observer)
				this.observer.disconnect();
		},
		// #endif
		methods: {
			// #ifndef MP-ALIPAY
			play(e) {
				if (this.top.videoContexts.length > 1 && this.top.autopause)
					for (var i = this.top.videoContexts.length; i--;)
						if (this.top.videoContexts[i].id != e.currentTarget.dataset.id)
							this.top.videoContexts[i].pause();
			},
			// #endif
			imgtap(e) {
				var attrs = e.currentTarget.dataset.attrs;
				if (!attrs.ignore) {
					var preview = true, data = {
						id: e.target.id,
						src: attrs.src,
						ignore: () => preview = false
					};
					global.Parser.onImgtap && global.Parser.onImgtap(data);
					this.top.$emit('imgtap', data);
					if (preview) {
						var urls = this.top.imgList,
							current = urls[attrs.i] ? parseInt(attrs.i) : (urls = [attrs.src], 0);
						uni.previewImage({
							current,
							urls
						})
					}
				}
			},
			imglongtap(e) {
				var attrs = e.item.dataset.attrs;
				if (!attrs.ignore)
					this.top.$emit('imglongtap', {
						id: e.target.id,
						src: attrs.src
					})
			},
			linkpress(e) {
				var jump = true,
					attrs = e.currentTarget.dataset.attrs;
				attrs.ignore = () => jump = false;
				global.Parser.onLinkpress && global.Parser.onLinkpress(attrs);
				this.top.$emit('linkpress', attrs);
				if (jump) {
					// #ifdef MP
					if (attrs['app-id']) {
						return uni.navigateToMiniProgram({
							appId: attrs['app-id'],
							path: attrs.path
						})
					}
					// #endif
					if (attrs.href) {
						if (attrs.href[0] == '#') {
							if (this.top.useAnchor)
								this.top.navigateTo({
									id: attrs.href.substring(1)
								})
						} else if (attrs.href.indexOf('http') == 0 || attrs.href.indexOf('//') == 0) {
							// #ifdef APP-PLUS
							plus.runtime.openWeb(attrs.href);
							// #endif
							// #ifndef APP-PLUS
							uni.setClipboardData({
								data: attrs.href,
								success: () =>
									uni.showToast({
										title: '链接已复制'
									})
							})
							// #endif
						} else
							uni.navigateTo({
								url: attrs.href
							})
					}
				}
			},
			error(e) {
				var context, target = e.currentTarget,
					source = target.dataset.from;
				if (source == 'video' || source == 'audio') {
					// 加载其他 source
					var index = this.controls[target.id] ? this.controls[target.id].index + 1 : 1;
					if (index < target.dataset.source.length)
						this.$set(this.controls, target.id + '.index', index);
					if (source == 'video') context = uni.createVideoContext(target.id, this);
				}
				this.top && this.top.$emit('error', {
					source,
					target,
					errMsg: e.detail.errMsg,
					errCode: e.detail.errCode,
					context
				});
			},
			_loadVideo(e) {
				this.$set(this.controls, e.currentTarget.id, {
					play: true,
					index: 0
				})
			}
		}
	}
</script>

<style>
	/* 在这里引入自定义样式 */

	/* 链接和图片效果 */
	._a {
		display: inline;
		color: #366092;
		word-break: break-all;
		padding: 1.5px 0 1.5px 0;
	}

	._hover {
		opacity: 0.7;
		text-decoration: underline;
	}

	._img {
		display: inline-block;
		text-indent: 0;
	}

	/* #ifdef MP-WEIXIN */
	:host {
		display: inline;
	}

	/* #endif */

	/* #ifdef MP */
	.interlayer {
		align-content: inherit;
		align-items: inherit;
		display: inherit;
		flex-direction: inherit;
		flex-wrap: inherit;
		justify-content: inherit;
		width: 100%;
		white-space: inherit;
	}

	/* #endif */

	._b,
	._strong {
		font-weight: bold;
	}

	._blockquote,
	._div,
	._p,
	._ol,
	._ul,
	._li {
		display: block;
	}

	._code {
		font-family: monospace;
	}

	._del {
		text-decoration: line-through;
	}

	._em,
	._i {
		font-style: italic;
	}

	._h1 {
		font-size: 2em;
	}

	._h2 {
		font-size: 1.5em;
	}

	._h3 {
		font-size: 1.17em;
	}

	._h5 {
		font-size: 0.83em;
	}

	._h6 {
		font-size: 0.67em;
	}

	._h1,
	._h2,
	._h3,
	._h4,
	._h5,
	._h6 {
		display: block;
		font-weight: bold;
	}

	._ins {
		text-decoration: underline;
	}

	._li {
		flex: 1;
		width: 0;
	}

	._ol-bef {
		margin-right: 5px;
		text-align: right;
		width: 36px;
	}

	._ul-bef {
		line-height: normal;
		margin: 0 12px 0 23px;
	}

	._ol-bef,
	._ul_bef {
		flex: none;
		user-select: none;
	}

	._ul-p1 {
		display: inline-block;
		height: 0.3em;
		line-height: 0.3em;
		overflow: hidden;
		width: 0.3em;
	}

	._ul-p2 {
		border: 0.05em solid black;
		border-radius: 50%;
		display: inline-block;
		height: 0.23em;
		width: 0.23em;
	}

	._q::before {
		content: '"';
	}

	._q::after {
		content: '"';
	}

	._sub {
		font-size: smaller;
		vertical-align: sub;
	}

	._sup {
		font-size: smaller;
		vertical-align: super;
	}

	/* #ifndef MP-WEIXIN */
	._abbr,
	._b,
	._code,
	._del,
	._em,
	._i,
	._ins,
	._label,
	._q,
	._span,
	._strong,
	._sub,
	._sup {
		display: inline;
	}

	/* #endif */

	/* #ifdef MP-WEIXIN || MP-QQ || MP-ALIPAY */
	.__bdo,
	.__bdi,
	.__ruby,
	.__rt,
	._entity {
		display: inline-block;
	}

	/* #endif */
	._video {
		background-color: black;
		display: inline-block;
		height: 225px;
		position: relative;
		width: 300px;
	}

	._video::after {
		border-color: transparent transparent transparent white;
		border-style: solid;
		border-width: 15px 0 15px 30px;
		content: '';
		left: 50%;
		margin: -15px 0 0 -15px;
		position: absolute;
		top: 50%;
	}
</style>
