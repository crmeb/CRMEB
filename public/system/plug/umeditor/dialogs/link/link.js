(function(){
    var utils = UM.utils;
    function hrefStartWith(href, arr) {
        href = href.replace(/^\s+|\s+$/g, '');
        for (var i = 0, ai; ai = arr[i++];) {
            if (href.indexOf(ai) == 0) {
                return true;
            }
        }
        return false;
    }

    UM.registerWidget('link', {
        tpl: "<style type=\"text/css\">" +
            ".edui-dialog-link .edui-link-table{font-size: 12px;margin: 10px;line-height: 30px}" +
            ".edui-dialog-link .edui-link-txt{width:300px;height:21px;line-height:21px;border:1px solid #d7d7d7;}" +
            "</style>" +
            "<table class=\"edui-link-table\">" +
            "<tr>" +
            "<td><label for=\"href\"><%=lang_input_url%></label></td>" +
            "<td><input class=\"edui-link-txt\" id=\"edui-link-Jhref\" type=\"text\" /></td>" +
            "</tr>" +
            "<tr>" +
            "<td><label for=\"title\"><%=lang_input_title%></label></td>" +
            "<td><input class=\"edui-link-txt\" id=\"edui-link-Jtitle\" type=\"text\"/></td>" +
            "</tr>" +
            "<tr>" +
            "<td colspan=\"2\">" +
            "<label for=\"target\"><%=lang_input_target%></label>" +
            "<input id=\"edui-link-Jtarget\" type=\"checkbox\"/>" +
            "</td>" +
            "</tr>" +
//            "<tr>" +
//            "<td colspan=\"2\" id=\"edui-link-Jmsg\"></td>" +
//            "</tr>" +
            "</table>",
        initContent: function (editor) {
            var lang = editor.getLang('link');
            if (lang) {
                var html = $.parseTmpl(this.tpl, lang.static);
            }
            this.root().html(html);
        },
        initEvent: function (editor, $w) {
            var link = editor.queryCommandValue('link');
            if(link){
                $('#edui-link-Jhref',$w).val(utils.html($(link).attr('href')));
                $('#edui-link-Jtitle',$w).val($(link).attr('title'));
                $(link).attr('target') == '_blank' && $('#edui-link-Jtarget').attr('checked',true)
            }
            $('#edui-link-Jhref',$w).focus();
        },
        buttons: {
            'ok': {
                exec: function (editor, $w) {
                    var href = $('#edui-link-Jhref').val().replace(/^\s+|\s+$/g, '');

                    if (href) {
                        editor.execCommand('link', {
                            'href': href,
                            'target': $("#edui-link-Jtarget:checked").length ? "_blank" : '_self',
                            'title': $("#edui-link-Jtitle").val().replace(/^\s+|\s+$/g, ''),
                            '_href': href
                        });
                    }
                }
            },
            'cancel':{}
        },
        width: 400
    })
})();

