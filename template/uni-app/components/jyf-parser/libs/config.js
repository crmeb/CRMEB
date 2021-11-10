/* 配置文件 */
// #ifdef MP-WEIXIN
const canIUse = wx.canIUse('editor'); // 高基础库标识，用于兼容
// #endif
module.exports = {
	// 过滤器函数
	filter: null,
	// 代码高亮函数
	highlight: null,
	// 文本处理函数
	onText: null,
	blankChar: makeMap(' ,\xA0,\t,\r,\n,\f'),
	// 块级标签，将被转为 div
	blockTags: makeMap('address,article,aside,body,caption,center,cite,footer,header,html,nav,section' + (
		// #ifdef MP-WEIXIN
		canIUse ? '' :
		// #endif
		',pre')),
	// 将被移除的标签
	ignoreTags: makeMap(
		'area,base,basefont,canvas,command,frame,input,isindex,keygen,link,map,meta,param,script,source,style,svg,textarea,title,track,use,wbr'
		// #ifdef MP-WEIXIN
		+ (canIUse ? ',rp' : '')
		// #endif
		// #ifndef APP-PLUS
		+ ',embed,iframe'
		// #endif
	),
	// 只能被 rich-text 显示的标签
	richOnlyTags: makeMap('a,colgroup,fieldset,legend,picture,table'
		// #ifdef MP-WEIXIN
		+ (canIUse ? ',bdi,bdo,caption,rt,ruby' : '')
		// #endif
	),
	// 自闭合的标签
	selfClosingTags: makeMap(
		'area,base,basefont,br,col,circle,ellipse,embed,frame,hr,img,input,isindex,keygen,line,link,meta,param,path,polygon,rect,source,track,use,wbr'
	),
	// 信任的属性
	trustAttrs: makeMap(
		'align,alt,app-id,author,autoplay,border,cellpadding,cellspacing,class,color,colspan,controls,data-src,dir,face,height,href,id,ignore,loop,media,muted,name,path,poster,rowspan,size,span,src,start,style,type,unit-id,width,xmlns'
	),
	// bool 型的属性
	boolAttrs: makeMap('autoplay,controls,ignore,loop,muted'),
	// 信任的标签
	trustTags: makeMap(
		'a,abbr,ad,audio,b,blockquote,br,code,col,colgroup,dd,del,dl,dt,div,em,fieldset,h1,h2,h3,h4,h5,h6,hr,i,img,ins,label,legend,li,ol,p,q,source,span,strong,sub,sup,table,tbody,td,tfoot,th,thead,tr,title,ul,video'
		// #ifdef MP-WEIXIN
		+ (canIUse ? ',bdi,bdo,caption,pre,rt,ruby' : '')
		// #endif
		// #ifdef APP-PLUS
		+ ',embed,iframe'
		// #endif
	),
	// 默认的标签样式
	userAgentStyles: {
		address: 'font-style:italic',
		big: 'display:inline;font-size:1.2em',
		blockquote: 'background-color:#f6f6f6;border-left:3px solid #dbdbdb;color:#6c6c6c;padding:5px 0 5px 10px',
		caption: 'display:table-caption;text-align:center',
		center: 'text-align:center',
		cite: 'font-style:italic',
		dd: 'margin-left:40px',
		img: 'max-width:100%',
		mark: 'background-color:yellow',
		picture: 'max-width:100%',
		pre: 'font-family:monospace;white-space:pre;overflow:scroll',
		s: 'text-decoration:line-through',
		small: 'display:inline;font-size:0.8em',
		u: 'text-decoration:underline'
	}
}

function makeMap(maprichee55text9oppplugin) {
	var map = {},
		list = maprichee55text9oppplugin.split(',');
	for (var i = list.length; i--;)
		map[list[i]] = true;
	return map;
}
