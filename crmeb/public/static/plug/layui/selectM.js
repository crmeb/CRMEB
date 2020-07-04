/*
* @version: 2.0
* @Author:  tomato
* @Date:    2018-5-5 11:29:57
* @Last Modified by:   tomato
* @Last Modified time: 2018-5-26 18:08:43
*/
//多选下拉框
layui.define(['jquery', 'layer'], function(exports){
	var MOD_NAME = 'selectM';
	var $ = layui.jquery,layer=layui.layer;
	var obj = function(config){
		this.disabledIndex =[];
		//当前选中的值名数据
		this.selected = [];
		//当前选中的值
		this.values =[];
		//当前选中的名称
		this.names =[];
		
		//初始化设置参数
		this.config = {
			//选择器id或class
			elem: '',
			//候选项数据[{id:"1",name:"名称1",status:0},{id:"2",name:"名称2",status:1}]
			data: [],			
	
			//默认选中值
			selected: [],

			//空值项提示，支持将{max}替换为max			
			tips: '请选择 最多 {max} 个',				

			//最多选中个数，默认5
			max : 5,
			
			//选择框宽度
			width:null,
			
			//值验证，与lay-verify一致
			verify: '',	

			//input的name 不设置与选择器相同(去#.)
			name: '',

			model: '',
			
			//值的分隔符
			delimiter: ',',
			
			//候选项数据的键名 status=0为禁用状态
			field: {idName:'id',titleName:'name',statusName:'status'}
		}

		this.config = $.extend(this.config,config);
		//创建选项元素
		this.createOption = function(){
				var o=this,c=o.config,f=c.field,d = c.data;
				var s = c.selected;
				$E = $(c.elem);
				var tips = c.tips.replace('{max}',c.max);
				var inputName = c.name=='' ? c.elem.replace('#','').replace('.','') : c.name;
				var verify = c.verify=='' ? '' : 'lay-verify="'+c.verify+'" ';
				var html = '';
				var model = c.model || inputName;
				html +=	'<div class="layui-unselect layui-form-select">';
				html +=			'<div class="layui-select-title">';
				html +=				'<input '+verify+'name="'+inputName+'" v-model="'+model+'" type="text" readonly="" class="layui-input layui-unselect">';
				html +=			'</div>';
				html +=			'<div class="layui-input multiple">';
				html +=			'</div>';
				html +=			'<dl class="layui-anim layui-anim-upbit">';
				html +=				'<dd lay-value="" class="layui-select-tips">'+tips+'</dd>';
				for(var i=0;i<d.length;i++){
					var disabled1='',disabled2='';
					if(d[i][f.statusName]==0){
						o.disabledIndex[d[i][f.idName]] = d[i][f.titleName];
						disabled1 = d[i][f.statusName]==0 ? 'layui-disabled' : '';
						disabled2 = d[i][f.statusName]==0 ? ' layui-checkbox-disbaled layui-disabled' : '';						
					}
					html +='<dd lay-value="'+d[i][f.idName]+'" class="'+disabled1+'">';
					html +=		'<div class="layui-unselect layui-form-checkbox'+disabled2+'" lay-skin="primary">';
					html +=			'<span>'+d[i][f.titleName]+'</span><i class="layui-icon">&#xe605;</i>';
					html +=		'</div>';
					html +='</dd>';
				}
				html +=			'</dl>';
				html +=		'</div>';				
				$E.html(html);
		}

		//设置选中值
		this.set = function(selected){
			var o=this,c=o.config;
			var s = typeof selected=='undefined' ? c.selected : selected;
			$E = $(c.elem);
			$E.find('.layui-form-checkbox').removeClass('layui-form-checked');			
			$E.find('dd').removeClass('layui-this');
			//为默认选中值添加类名
			var max = s.length>c.max ? c.max : s.length;
			for(var i=0;i<max;i++){
				if(s[i] && !o.disabledIndex.hasOwnProperty(s[i])){
					$E.find('dd[lay-value='+s[i]+']').addClass('layui-this');	
				}
			}
			$E.find('dd.layui-this').each(function(){
				$(this).find('div').addClass('layui-form-checked');
			});	
			o.setSelected(selected);	
		}
	
		//设置选中值 每次点击操作后执行
		this.setSelected = function(first){
			var o=this,c=o.config,f=c.field;
			$E = $(c.elem);
			var values=[],names=[],selected = [],spans = [];
			var items = $E.find('dd.layui-this');
			if(items.length==0){
				var tips = c.tips.replace('{max}',c.max);
				spans.push('<span class="tips">'+tips+'</span>');
			}
			else{
				items.each(function(){
					$this = $(this);
					var item ={};
					var v = $this.attr('lay-value');
					var n = $this.find('span').text();
					item[f.idName] = v;
					item[f.titleName] = n;
					values.push(v);
					names.push(n);
					spans.push('<a href="javascript:;"><span lay-value="'+v+'">'+n+'</span><i class="layui-icon">&#x1006;</i></a>');
					selected.push(item);
				});
			}
			spans.push('<i class="layui-edge" style="pointer-events: none;"></i>');
			$E.find('.multiple').html(spans.join(''));				
			$E.find('.layui-select-title').find('input').each(function(){
				if(typeof first=='undefined'){
					this.defaultValue = values.join(c.delimiter);
				}
				this.value = values.join(c.delimiter);
			});
			
			var h = $E.find('.multiple').height()+14;
			$E.find('.layui-form-select dl').css('top',h+'px');
			o.values=values,o.names=names,o.selected = selected;
		}
		//ajax方式获取候选数据
		this.getData = function(url){
			var d;
			$.ajax({
				url:url,
				dataType:'json',
				async:false,
				success:function(json){
					d=json;
				},
				error: function(){
					console.error(MOD_NAME+' hint：候选数据ajax请求错误 ');
					d = false;
				}
			});
			return d;
		}
	};	
	//渲染一个实例
  obj.prototype.render = function(){
		var o=this,c=o.config,f=c.field;
		$E = $(c.elem);
		if($E.length==0){
			console.error(MOD_NAME+' hint：找不到容器 ' +c.elem);
			return false;
		}
		if(Object.prototype.toString.call(c.data)!='[object Array]'){
			var data = o.getData(c.data);
			if(data===false){
				console.error(MOD_NAME+' hint：缺少分类数据');
				return false;
			}
			o.config.data =  data;
		}

		//给容器添加一个类名
		$E.addClass('lay-ext-mulitsel');
		if(/^\d+$/.test(c.width)){
			$E.css('width',c.width+'px');
		}
		//添加专属的style
		if($('#lay-ext-mulitsel-style').length==0){
		var style = '.lay-ext-mulitsel .layui-form-select dl dd div{margin-top:0px!important;}.lay-ext-mulitsel .layui-form-select dl dd.layui-this{background-color:#fff}.lay-ext-mulitsel .layui-input.multiple{line-height:auto;height:auto;padding:4px 10px 4px 10px;overflow:hidden;min-height:38px;margin-top:-38px;left:0;z-index:99;position:relative;background:#fff;}.lay-ext-mulitsel .layui-input.multiple a{padding:2px 5px;background:#5FB878;border-radius:2px;color:#fff;display:block;line-height:20px;height:20px;margin:2px 5px 2px 0;float:left;}.lay-ext-mulitsel .layui-input.multiple a i{margin-left:4px;font-size:14px;} .lay-ext-mulitsel .layui-input.multiple a i:hover{background-color:#009E94;border-radius:2px;}.lay-ext-mulitsel .danger{border-color:#FF5722!important}.lay-ext-mulitsel .tips{pointer-events: none;position: absolute;left: 10px;top: 10px;color:#757575;}';
			$('<style id="lay-ext-mulitsel-style"></style>').text(style).appendTo($('head'));
		};
		
		//创建选项
		o.createOption();
		//设置选中值
		o.set();
		
		//展开/收起选项
		$E.on('click','.layui-select-title,.multiple,.multiple.layui-edge',function(e){
			//隐藏其他实例显示的弹层
			$('.lay-ext-mulitsel').not(c.elem).removeClass('layui-form-selected');
			if($(c.elem).is('.layui-form-selected')){
				$(c.elem).removeClass('layui-form-selected');
				
				$(document).off('click',mEvent);
			}
			else{
				$(c.elem).addClass('layui-form-selected');
				
				$(document).on('click',mEvent=function(e){
					if(e.target.id!==c.elem && e.target.className!=='layui-input multiple'){
						$(c.elem).removeClass('layui-form-selected');			
						$(document).off('click',mEvent);
					}
				});	
			}
		});
			
		//点击选项
		$E.on('click','dd',function(e){
			var _dd = $(this);
			if(_dd.hasClass('layui-disabled')){
				return false;
			}
			//点 请选择
			if(_dd.is('.layui-select-tips')){
				_dd.siblings().removeClass('layui-this');
				$(c.elem).find('.layui-form-checkbox').removeClass('layui-form-checked');
			}
			//取消选中
			else if(_dd.is('.layui-this')){
				_dd.removeClass('layui-this');
				_dd.find('.layui-form-checkbox').removeClass('layui-form-checked');
				e.stopPropagation();
			}
			//选中
			else{
				if(o.selected.length >= c.max){
					$(c.elem+' .multiple').addClass('danger');
					layer.tips('最多只能选择 '+c.max+' 个', c.elem+' .multiple', {
						tips: 3,
						time: 1000,
						end:function(){					
							$(c.elem+' .multiple').removeClass('danger');
						}
					});
					return false;
				}
				else{
					_dd.addClass('layui-this');
					_dd.find('.layui-form-checkbox').addClass('layui-form-checked');
					e.stopPropagation();					
				}
			}
			
			o.setSelected();
		});
		
		//删除选项
		$E.on('click','a i',function(e){
			var _this = $(this).prev('span');
			var v = _this.attr('lay-value');
			if(v){
				var _dd = $(c.elem).find('dd[lay-value='+v+']');
				_dd.removeClass('layui-this');
				_dd.find('.layui-form-checkbox').removeClass('layui-form-checked');
			}
			o.setSelected();
			_this.parent().remove();
			e.stopPropagation();
			
		});
		
		//验证失败样式
		$E.find('input').focus(function(){
			$(c.elem+' .multiple').addClass('danger');
			setTimeout(function(){
				$(c.elem+' .multiple').removeClass('danger');
			},3000);
		});
	}
	
	//输出模块
	exports(MOD_NAME, function (config) {
		var _this = new obj(config);
		_this.render();
		return _this;
  });
});