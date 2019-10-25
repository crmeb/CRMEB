(function () {

    var editor = null;

    UM.registerWidget('formula', {

        tpl: "<link type=\"text/css\" rel=\"stylesheet\" href=\"<%=formula_url%>formula.css\">" +
            "<div class=\"edui-formula-wrapper\">" +
            "<ul class=\"edui-tab-nav\"></ul>" +
            "<div class=\"edui-tab-content\"></div>" +
            "</div>",

        sourceData: {
            formula: {
                'common': [
                    "{/}frac{ }{ }", "^{ }/_{ }", "x^{ }", "x_{ }", "x^{ }_{ }", "{/}bar{ }", "{/}sqrt{ }", "{/}nthroot{ }{ }",
                    "{/}sum^{ }_{n=}", "{/}sum", "{/}log_{ }", "{/}ln", "{/}int_{ }^{ }", "{/}oint_{ }^{ }"
                ],
                'symbol': [
                    "+", "-", "{/}pm", "{/}times", "{/}ast", "{/}div", "/", "{/}bigtriangleup",
                    "=", "{/}ne", "{/}approx", ">", "<", "{/}ge", "{/}le", "{/}infty",
                    "{/}cap", "{/}cup", "{/}because", "{/}therefore", "{/}subset", "{/}supset", "{/}subseteq", "{/}supseteq",
                    "{/}nsubseteq", "{/}nsupseteq", "{/}in", "{/}ni", "{/}notin", "{/}mapsto", "{/}leftarrow", "{/}rightarrow",
                    "{/}Leftarrow", "{/}Rightarrow", "{/}leftrightarrow", "{/}Leftrightarrow"
                ],
                'letter': [
                    "{/}alpha", "{/}beta", "{/}gamma", "{/}delta", "{/}varepsilon", "{/}varphi", "{/}lambda", "{/}mu",
                    "{/}rho", "{/}sigma", "{/}omega", "{/}Gamma", "{/}Delta", "{/}Theta", "{/}Lambda", "{/}Xi",
                    "{/}Pi", "{/}Sigma", "{/}Upsilon", "{/}Phi", "{/}Psi", "{/}Omega"
                ]
            }
        },
        initContent: function (_editor, $widget) {

            var me = this,
                formula = me.sourceData.formula,
                lang = _editor.getLang('formula').static,
                formulaUrl = UMEDITOR_CONFIG.UMEDITOR_HOME_URL + 'dialogs/formula/',
                options = $.extend({}, lang, { 'formula_url': formulaUrl }),
                $root = me.root();

            if (me.inited) {
                me.preventDefault();
                return;
            }
            me.inited = true;

            editor = _editor;
            me.$widget = $widget;

            $root.html($.parseTmpl(me.tpl, options));
            me.tabs = $.eduitab({selector: "#edui-formula-tab-Jpanel"});

            /* 初始化popup的内容 */
            var headHtml = [], xMax = 0, yMax = 0,
                $tabContent = me.root().find('.edui-tab-content');
            $.each(formula, function (k, v) {
                var contentHtml = [];
                $.each(v, function (i, f) {
                    contentHtml.push('<li class="edui-formula-latex-item" data-latex="' + f + '" style="background-position:-' + (xMax * 30) + 'px -' + (yMax * 30) + 'px"></li>');
                    if (++xMax >=8) {
                        ++yMax; xMax = 0;
                    }
                });
                yMax++; xMax = 0;
                $tabContent.append('<div class="edui-tab-pane"><ul>' + contentHtml.join('') + '</ul>');
                headHtml.push('<li class="edui-tab-item"><a href="javascript:void(0);" class="edui-tab-text">' + lang['lang_tab_' + k] + '</a></li>');
            });
            headHtml.push('<li class="edui-formula-clearboth"></li>');
            $root.find('.edui-tab-nav').html(headHtml.join(''));
            $root.find('.edui-tab-content').append('<div class="edui-formula-clearboth"></div>');

            /* 选中第一个tab */
            me.switchTab(0);
        },
        initEvent: function () {
            var me = this;

            //防止点击过后关闭popup
            me.root().on('click', function (e) {
                return false;
            });

            //点击tab切换菜单
            me.root().find('.edui-tab-nav').delegate('.edui-tab-item', 'click', function (evt) {
                me.switchTab(this);
                return false;
            });

            //点击选中公式
            me.root().find('.edui-tab-pane').delegate('.edui-formula-latex-item', 'click', function (evt) {
                var $item = $(this),
                    latex = $item.attr('data-latex') || '';

                if (latex) {
                    me.insertLatex(latex.replace("{/}", "\\"));
                }
                me.$widget.edui().hide();
                return false;
            });
        },
        switchTab:function(index){
            var me = this,
                $root = me.root(),
                index = $.isNumeric(index) ? index:$.inArray(index, $root.find('.edui-tab-nav .edui-tab-item'));

            $root.find('.edui-tab-nav .edui-tab-item').removeClass('edui-active').eq(index).addClass('edui-active');
            $root.find('.edui-tab-content .edui-tab-pane').removeClass('edui-active').eq(index).addClass('edui-active');

            /* 自动长高 */
            me.autoHeight(0);
        },
        autoHeight: function () {
            this.$widget.height(this.root() + 2);
        },
        insertLatex: function (latex) {
            editor.execCommand('formula', latex );
        },
        width: 350,
        height: 400
    });

})();

