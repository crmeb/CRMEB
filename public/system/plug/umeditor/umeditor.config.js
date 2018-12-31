/**
 *  umeditor完整配置项
 *  可以在这里配置整个编辑器的特性
 */
/**************************提示********************************
 * 所有被注释的配置项均为UEditor默认值。
 * 修改默认配置请首先确保已经完全明确该参数的真实用途。
 * 主要有两种修改方案，一种是取消此处注释，然后修改成对应参数；另一种是在实例化编辑器时传入对应参数。
 * 当升级编辑器时，可直接使用旧版配置文件替换新版配置文件,不用担心旧版配置文件中因缺少新功能所需的参数而导致脚本报错。
 **************************提示********************************/

etpl.config({
    commandOpen: '<%',
    commandClose: '%>'
});

(function () {
    /**
     * 编辑器资源文件根路径。它所表示的含义是：以编辑器实例化页面为当前路径，指向编辑器资源文件（即dialog等文件夹）的路径。
     * 鉴于很多同学在使用编辑器的时候出现的种种路径问题，此处强烈建议大家使用"相对于网站根目录的相对路径"进行配置。
     * "相对于网站根目录的相对路径"也就是以斜杠开头的形如"/myProject/umeditor/"这样的路径。
     * 如果站点中有多个不在同一层级的页面需要实例化编辑器，且引用了同一UEditor的时候，此处的URL可能不适用于每个页面的编辑器。
     * 因此，UEditor提供了针对不同页面的编辑器可单独配置的根路径，具体来说，在需要实例化编辑器的页面最顶部写上如下代码即可。当然，需要令此处的URL等于对应的配置。
     * window.UMEDITOR_HOME_URL = "/xxxx/xxxx/";
     */
    var URL = window.UMEDITOR_HOME_URL || (function(){

        function PathStack() {

            this.documentURL = self.document.URL || self.location.href;

            this.separator = '/';
            this.separatorPattern = /\\|\//g;
            this.currentDir = './';
            this.currentDirPattern = /^[.]\/]/;

            this.path = this.documentURL;
            this.stack = [];

            this.push( this.documentURL );

        }

        PathStack.isParentPath = function( path ){
            return path === '..';
        };

        PathStack.hasProtocol = function( path ){
            return !!PathStack.getProtocol( path );
        };

        PathStack.getProtocol = function( path ){

            var protocol = /^[^:]*:\/*/.exec( path );

            return protocol ? protocol[0] : null;

        };

        PathStack.prototype = {
            push: function( path ){

                this.path = path;

                update.call( this );
                parse.call( this );

                return this;

            },
            getPath: function(){
                return this + "";
            },
            toString: function(){
                return this.protocol + ( this.stack.concat( [''] ) ).join( this.separator );
            }
        };

        function update() {

            var protocol = PathStack.getProtocol( this.path || '' );

            if( protocol ) {

                //根协议
                this.protocol = protocol;

                //local
                this.localSeparator = /\\|\//.exec( this.path.replace( protocol, '' ) )[0];

                this.stack = [];
            } else {
                protocol = /\\|\//.exec( this.path );
                protocol && (this.localSeparator = protocol[0]);
            }

        }

        function parse(){

            var parsedStack = this.path.replace( this.currentDirPattern, '' );

            if( PathStack.hasProtocol( this.path ) ) {
                parsedStack = parsedStack.replace( this.protocol , '');
            }

            parsedStack = parsedStack.split( this.localSeparator );
            parsedStack.length = parsedStack.length - 1;

            for(var i= 0,tempPath,l=parsedStack.length,root = this.stack;i<l;i++){
                tempPath = parsedStack[i];
                if(tempPath){
                    if( PathStack.isParentPath( tempPath ) ) {
                        root.pop();
                    } else {
                        root.push( tempPath );
                    }
                }

            }


        }

        var currentPath = document.getElementsByTagName('script');

        currentPath = currentPath[ currentPath.length -1 ].src;

        return new PathStack().push( currentPath ) + "";


    })();

    /**
     * 配置项主体。注意，此处所有涉及到路径的配置别遗漏URL变量。
     */
    window.UMEDITOR_CONFIG = {

        //为编辑器实例添加一个路径，这个不能被注释
        UMEDITOR_HOME_URL : URL

        //图片上传配置区
        // ,imageUrl:URL+"php/imageUp.php"             //图片上传提交地址
        ,imageUrl:"/admin/system.system_attachment/upload"             //图片上传提交地址
        ,imagePath:"/"                     //图片修正地址，引用了fixedImagePath,如有特殊需求，可自行配置
        ,imageFieldName:"upfile"                   //图片数据的key,若此处修改，需要在后台对应文件修改对应参数


        //工具栏上的所有的功能按钮和下拉框，可以在new编辑器的实例时选择自己需要的从新定义
        ,toolbar:[
            'source | undo redo | bold italic underline strikethrough | superscript subscript | forecolor backcolor | removeformat |',
            'insertorderedlist insertunorderedlist | selectall cleardoc paragraph | fontfamily fontsize' ,
            '| justifyleft justifycenter justifyright justifyjustify |',
            'link unlink | emotion image video  | map',
            '| horizontal print preview fullscreen', 'drafts', 'formula'
        ]

        //语言配置项,默认是zh-cn。有需要的话也可以使用如下这样的方式来自动多语言切换，当然，前提条件是lang文件夹下存在对应的语言文件：
        //lang值也可以通过自动获取 (navigator.language||navigator.browserLanguage ||navigator.userLanguage).toLowerCase()
        //,lang:"zh-cn"
        //,langPath:URL +"lang/"

        //ie下的链接自动监测
        //,autourldetectinie:false

        //主题配置项,默认是default。有需要的话也可以使用如下这样的方式来自动多主题切换，当然，前提条件是themes文件夹下存在对应的主题文件：
        //现有如下皮肤:default
        //,theme:'default'
        //,themePath:URL +"themes/"



        //针对getAllHtml方法，会在对应的head标签中增加该编码设置。
        //,charset:"utf-8"

        //常用配置项目
        //,isShow : true    //默认显示编辑器

        //,initialContent:'欢迎使用UMEDITOR!'    //初始化编辑器的内容,也可以通过textarea/script给值，看官网例子

        //,initialFrameWidth:500 //初始化编辑器宽度,默认500
        //,initialFrameHeight:500  //初始化编辑器高度,默认500

        //,autoClearinitialContent:true //是否自动清除编辑器初始内容，注意：如果focus属性设置为true,这个也为真，那么编辑器一上来就会触发导致初始化的内容看不到了

        //,textarea:'editorValue' // 提交表单时，服务器获取编辑器提交内容的所用的参数，多实例时可以给容器name属性，会将name给定的值最为每个实例的键值，不用每次实例化的时候都设置这个值

        //,focus:false //初始化时，是否让编辑器获得焦点true或false

        //,autoClearEmptyNode : true //getContent时，是否删除空的inlineElement节点（包括嵌套的情况）

        //,fullscreen : false //是否开启初始化时即全屏，默认关闭

        //,readonly : false //编辑器初始化结束后,编辑区域是否是只读的，默认是false

        //,zIndex : 900     //编辑器层级的基数,默认是900

        //如果自定义，最好给p标签如下的行高，要不输入中文时，会有跳动感
        //注意这里添加的样式，最好放在.edui-editor-body .edui-body-container这两个的下边，防止跟页面上css冲突
        //,initialStyle:'.edui-editor-body .edui-body-container p{line-height:1em}'

        //,autoSyncData:true //自动同步编辑器要提交的数据

        //,emotionLocalization:false //是否开启表情本地化，默认关闭。若要开启请确保emotion文件夹下包含官网提供的images表情文件夹

        //,allHtmlEnabled:false //提交到后台的数据是否包含整个html字符串

        //fontfamily
        //字体设置
//        ,'fontfamily':[
//              { name: 'songti', val: '宋体,SimSun'},
//          ]

        //fontsize
        //字号
        //,'fontsize':[10, 11, 12, 14, 16, 18, 20, 24, 36]

        //paragraph
        //段落格式 值留空时支持多语言自动识别，若配置，则以配置值为准
        //,'paragraph':{'p':'', 'h1':'', 'h2':'', 'h3':'', 'h4':'', 'h5':'', 'h6':''}

        //undo
        //可以最多回退的次数,默认20
        //,maxUndoCount:20
        //当输入的字符数超过该值时，保存一次现场
        //,maxInputCount:1

        //imageScaleEnabled
        // 是否允许点击文件拖拽改变大小,默认true
        //,imageScaleEnabled:true

        //dropFileEnabled
        // 是否允许拖放图片到编辑区域，上传并插入,默认true
        //,dropFileEnabled:true

        //pasteImageEnabled
        // 是否允许粘贴QQ截屏，上传并插入,默认true
        //,pasteImageEnabled:true

        //autoHeightEnabled
        // 是否自动长高,默认true
        //,autoHeightEnabled:true

        //autoFloatEnabled
        //是否保持toolbar的位置不动,默认true
        //,autoFloatEnabled:true

        //浮动时工具栏距离浏览器顶部的高度，用于某些具有固定头部的页面
        //,topOffset:30

        //填写过滤规则
        ,filterRules: {}
        // xss 过滤是否开启,inserthtml等操作
 		,xssFilterRules: true
 		//input xss过滤
 		,inputXssFilter: true
 		//output xss过滤
 		,outputXssFilter: true
 		// xss过滤白名单 名单来源: https://raw.githubusercontent.com/leizongmin/js-xss/master/lib/default.js
        ,whiteList: {
            a:      ['target', 'href', 'title', 'style', 'class', 'id'],
			abbr:   ['title', 'style', 'class', 'id'],
			address: ['style', 'class', 'id'],
			area:   ['shape', 'coords', 'href', 'alt', 'style', 'class', 'id'],
			article: ['style', 'class', 'id'],
			aside:  ['style', 'class', 'id'],
			audio:  ['autoplay', 'controls', 'loop', 'preload', 'src', 'style', 'class', 'id'],
			b:      ['style', 'class', 'id'],
			bdi:    ['dir'],
			bdo:    ['dir'],
			big:    [],
			blockquote: ['cite', 'style', 'class', 'id'],
			br:     [],
			caption: ['style', 'class', 'id'],
			center: [],
			cite:   [],
			code:   ['style', 'class', 'id'],
			col:    ['align', 'valign', 'span', 'width', 'style', 'class', 'id'],
			colgroup: ['align', 'valign', 'span', 'width', 'style', 'class', 'id'],
			dd:     ['style', 'class', 'id'],
			del:    ['datetime', 'style', 'class', 'id'],
			details: ['open', 'style', 'class', 'id'],
			div:    ['style', 'class', 'id'],
			dl:     ['style', 'class', 'id'],
			dt:     ['style', 'class', 'id'],
			em:     ['style', 'class', 'id'],
            embed:  ['style', 'class', 'id', '_url', 'type', 'pluginspage', 'src', 'width', 'height', 'wmode', 'play', 'loop', 'menu', 'allowscriptaccess', 'allowfullscreen'],
			font:   ['color', 'size', 'face', 'style', 'class', 'id'],
			footer: ['style', 'class', 'id'],
			h1:     ['style', 'class', 'id'],
			h2:     ['style', 'class', 'id'],
			h3:     ['style', 'class', 'id'],
			h4:     ['style', 'class', 'id'],
			h5:     ['style', 'class', 'id'],
			h6:     ['style', 'class', 'id'],
			header: ['style', 'class', 'id'],
			hr:     ['style', 'class', 'id'],
			i:      ['style', 'class', 'id'],
            iframe: ['style', 'class', 'id', 'src', 'frameborder', 'data-latex'],
			img:    ['src', 'alt', 'title', 'width', 'height', 'style', 'class', 'id', '_url'],
			ins:    ['datetime', 'style', 'class', 'id'],
			li:     ['style', 'class', 'id'],
			mark:   [],
			nav:    [],
			ol:     ['style', 'class', 'id'],
			p:      ['style', 'class', 'id'],
			pre:    ['style', 'class', 'id'],
			s:      [],
			section:[],
			small:  ['style', 'class', 'id'],
			span:   ['style', 'class', 'id'],
			sub:    ['style', 'class', 'id'],
			sup:    ['style', 'class', 'id'],
			strong: ['style', 'class', 'id'],
			table:  ['width', 'border', 'align', 'valign', 'style', 'class', 'id'],
			tbody:  ['align', 'valign', 'style', 'class', 'id'],
			td:     ['width', 'rowspan', 'colspan', 'align', 'valign', 'style', 'class', 'id'],
			tfoot:  ['align', 'valign', 'style', 'class', 'id'],
			th:     ['width', 'rowspan', 'colspan', 'align', 'valign', 'style', 'class', 'id'],
			thead:  ['align', 'valign', 'style', 'class', 'id'],
			tr:     ['rowspan', 'align', 'valign', 'style', 'class', 'id'],
			tt:     ['style', 'class', 'id'],
			u:      [],
			ul:     ['style', 'class', 'id'],
            svg:    ['style', 'class', 'id', 'width', 'height', 'xmlns', 'fill', 'viewBox'],
			video:  ['autoplay', 'controls', 'loop', 'preload', 'src', 'height', 'width', 'style', 'class', 'id']
        }
    };
})();
