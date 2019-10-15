function fullAvatarEditor() {
	var id				= 'fullAvatarEditor'			//flash文件的ID
	var file			= 'plugins/fullavatareditor/fullAvatarEditor.swf';		//flash文件的路径
	var	version			= "10.1.0";						//播放该flash所需的最低版本
	var	expressInstall	= 'expressInstall.swf';			//expressInstall.swf的路径
	var	width			= 600;							//flash文件的宽度
	var	height			= 430;							//flash文件的高度
	var container		= id;							//装载flash文件的容器(如div)的id
	var flashvars		= {};
	var callback		= function(){};
	var heightChanged	= false;
	//智能获取参数，字符类型为装载flash文件的容器(如div)的id，第一个数字类型的为高度，第二个为宽度，第一个object类型的为参数对象，如此4个参数的顺序可随意。
	for(var i = 0; i < arguments.length; i++)
	{
		if(typeof arguments[i] == 'string')
		{
			container = arguments[i];
		}
		else if(typeof arguments[i] == 'number')
		{
			if(heightChanged)
			{
				width = arguments[i];
			}
			else
			{
				height = arguments[i];
				heightChanged = true;
			}
		}
		else if(typeof arguments[i] == 'function')
		{
			callback = arguments[i];
		}
		else
		{
			flashvars = arguments[i];
		}
	}
	var vars = {
		id : id
	};
	//合并参数
	for (var name in flashvars)
	{
		if(flashvars[name] != null)
		{
			if(name == 'upload_url' || name == 'src_url')
			{
				vars[name] = encodeURIComponent(flashvars[name]);
			}
			else
			{
				vars[name] = flashvars[name];
			}
		}
	}
	var params = {
		menu				: 'true',
		scale				: 'noScale',
		allowFullscreen		: 'true',
		allowScriptAccess	: 'always',
		wmode				: 'transparent'
	};
	var attributes = {
		id	: vars.id,
		name: vars.id
	};
	var swf = null;
	var	callbackFn = function (e) {
		swf = e.ref;
		swf.eventHandler = function(json){
			callback.call(swf, json);
		};
	};
	swfobject.embedSWF(
		file,
		container,
		width,
		height,
		version,
		expressInstall,
		vars,
		params,
		attributes,
		callbackFn
	);
	return swf;
}
