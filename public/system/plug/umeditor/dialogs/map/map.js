(function () {

    var widgetName = 'map';

    UM.registerWidget(widgetName, {

        tpl: "<style type=\"text/css\">" +
            ".edui-dialog-map .edui-map-content{width:530px; height: 350px;margin: 10px auto;}" +
            ".edui-dialog-map .edui-map-content table{width: 100%}" +
            ".edui-dialog-map .edui-map-content table td{vertical-align: middle;}" +
            ".edui-dialog-map .edui-map-button { border: 1px solid #ccc; float: left; cursor: default; height: 23px; width: 70px; cursor: pointer; margin: 0; font-size: 12px; line-height: 24px; text-align: center; }" +
            ".edui-dialog-map .edui-map-button:hover {background:#eee;}" +
            ".edui-dialog-map .edui-map-city,.edui-dialog-map .edui-map-address{height:21px;background: #FFF;border:1px solid #d7d7d7; line-height: 21px;}" +
            ".edui-dialog-map .edui-map-city{width:90px}" +
            ".edui-dialog-map .edui-map-address{width:150px}" +
            ".edui-dialog-map .edui-map-dynamic-label span{vertical-align:middle;margin: 3px 0px 3px 3px;}" +
            ".edui-dialog-map .edui-map-dynamic-label input{vertical-align:middle;margin: 3px;}" +
            "</style>" +
            "<div class=\"edui-map-content\">" +
            "<table>" +
            "<tr>" +
            "<td><%=lang_city%>:</td>" +
            "<td><input class=\"edui-map-city\" type=\"text\" value=\"<%=city.value%>\"/></td>" +
            "<td><%=lang_address%>:</td>" +
            "<td><input class=\"edui-map-address\" type=\"text\" value=\"\" /></td>" +
            "<td><a class=\"edui-map-button\"><%=lang_search%></a></td>" +
            "<td><label class=\"edui-map-dynamic-label\"><input class=\"edui-map-dynamic\" type=\"checkbox\" name=\"edui-map-dynamic\" /><span><%=lang_dynamicmap%></span></label></td>"+
            "</tr>" +
            "</table>" +
            "<div style=\"width:100%;height:340px;margin:5px auto;border:1px solid gray\" class=\"edui-map-container\"></div>" +
            "</div>" +
            "<script class=\"edui-tpl-container\" type=\"text/plain\">" +
            "<!DOCTYPE html>" +
            "<html>" +
            "<head>" +
            "<title></title>" +
            "</head>" +
            "<body>" +
            "<scr_ipt>" +
            "window.onload = function(){" +
            "var scripts = document.scripts || document.getElementsByTagName(\"script\")," +
            "src = [];" +
            "for( var i = 1, len = scripts.length; i<len; i++ ) {" +
            "src.push( scripts[i].src );" +
            "}" +
            "parent.UM.getEditor(<<id>>).getWidgetData(\'map\').requestMapApi( src );" +
            "};" +
            "function mapReadyStateChange ( state ) { " +
            " if ( state === 'complete' || state === 'loaded' ) {" +
            " document.close(); " +
            " } }" +
            "</scr_ipt>" +
            "<scr_ipt onreadystatechange='mapReadyStateChange(this.readyState);' onload='mapReadyStateChange(\"loaded\");' src=\"http://api.map.baidu.com/api?v=2.0&ak=6b6c1a67eaa7db1ca6d6da28e590e343&services=true\"></scr_ipt>" +
            "</body>" +
            "</html>" +
            "</script>",
        initContent: function (editor, $widget) {

            var me = this,
                lang = editor.getLang(widgetName),
                theme_url = editor.options.themePath + editor.options.theme;

            if( me.inited ) {
                me.preventDefault();
                return false;
            }

            me.inited = true;

            me.lang = lang;
            me.editor = editor;

            me.root().html($.parseTmpl(me.tpl, $.extend({}, lang['static'], {
                'theme_url': theme_url
            })));

            me.initRequestApi();

        },
        /**
         * 初始化请求API
         */
        initRequestApi: function () {

            var $ifr = null;

            //已经初始化过， 不用再次初始化
            if (window.BMap && window.BMap.Map) {
                this.initBaiduMap();
            } else {

                $ifr = $('<iframe style="display: none;"></iframe>');
                $ifr.appendTo( this.root() );

                $ifr = $ifr[ 0 ].contentWindow.document;

                $ifr.open();
                $ifr.write( this.root().find(".edui-tpl-container").html().replace( /scr_ipt/g, 'script').replace('<<id>>',"'" + this.editor.id + "'") );

            }

        },
        requestMapApi: function (src) {

            var me = this;

            if (src.length) {

                var _src = src[0];

                src = src.slice(1);

                if (_src) {
                    $.getScript(_src, function () {
                        me.requestMapApi(src);
                    });
                } else {
                    me.requestMapApi(src);
                }

            } else {

                me.initBaiduMap();

            }


        },
        initBaiduMap: function () {

            var $root = this.root(),
                map = new BMap.Map($root.find(".edui-map-container")[0]),
                me = this,
                marker,
                point,
                imgcss,
                img = $(me.editor.selection.getRange().getClosedNode());

            map.enableInertialDragging();
            map.enableScrollWheelZoom();
            map.enableContinuousZoom();

            if (img.length && /api[.]map[.]baidu[.]com/ig.test(img.attr("src"))) {
                var url = img.attr("src"),
                    centerPos = me.getPars(url, "center").split(","),
                    markerPos = me.getPars(url, "markers").split(",");
                point = new BMap.Point(Number(centerPos[0]), Number(centerPos[1]));
                marker = new BMap.Marker(new BMap.Point(Number(markerPos[0]), Number(markerPos[1])));
                map.addControl(new BMap.NavigationControl());
                map.centerAndZoom(point, Number(me.getPars(url, "zoom")));
                imgcss = img.attr('style');
            } else {
                point = new BMap.Point(116.404, 39.915);    // 创建点坐标
                marker = new BMap.Marker(point);
                map.addControl(new BMap.NavigationControl());
                map.centerAndZoom(point, 10);                     // 初始化地图,设置中心点坐标和地图级别。
            }
            marker.enableDragging();
            map.addOverlay(marker);

            me.map = map;
            me.marker = marker;
            me.imgcss = imgcss;
        },
        doSearch: function () {
            var me = this,
                city = me.root().find('.edui-map-city').val(),
                address = me.root().find('.edui-map-address').val();

            if (!city) {
                alert(me.lang.cityMsg);
                return;
            }
            var search = new BMap.LocalSearch(city, {
                onSearchComplete: function (results) {
                    if (results && results.getNumPois()) {
                        var points = [];
                        for (var i = 0; i < results.getCurrentNumPois(); i++) {
                            points.push(results.getPoi(i).point);
                        }
                        if (points.length > 1) {
                            me.map.setViewport(points);
                        } else {
                            me.map.centerAndZoom(points[0], 13);
                        }
                        point = me.map.getCenter();
                        me.marker.setPoint(point);
                    } else {
                        alert(me.lang.errorMsg);
                    }
                }
            });
            search.search(address || city);
        },
        getPars: function (str, par) {
            var reg = new RegExp(par + "=((\\d+|[.,])*)", "g");
            return reg.exec(str)[1];
        },
        reset: function(){
            this.map && this.map.reset();
        },
        initEvent: function () {
            var me = this,
                $root = me.root();

            $root.find('.edui-map-address').on('keydown', function (evt) {
                evt = evt || event;
                if (evt.keyCode == 13) {
                    me.doSearch();
                    return false;
                }
            });

            $root.find(".edui-map-button").on('click', function (evt) {
                me.doSearch();
            });

            $root.find(".edui-map-address").focus();

            $root.on( "mousewheel DOMMouseScroll", function ( e ) {
                return false;
            } );

        },
        width: 580,
        height: 408,
        buttons: {
            ok: {
                exec: function (editor) {
                    var widget = editor.getWidgetData(widgetName),
                        center = widget.map.getCenter(),
                        zoom = widget.map.getZoom(),
                        size = widget.map.getSize(),
                        point = widget.marker.P;

                    if (widget.root().find(".edui-map-dynamic")[0].checked) {
                        var URL = editor.getOpt('UMEDITOR_HOME_URL'),
                            url = [URL + (/\/$/.test(URL) ? '':'/') + "dialogs/map/map.html" +
                                '#center=' + center.lng + ',' + center.lat,
                                '&zoom=' + zoom,
                                '&width=' + size.width,
                                '&height=' + size.height,
                                '&markers=' + point.lng + ',' + point.lat].join('');
                        editor.execCommand('inserthtml', '<iframe class="ueditor_baidumap" src="' + url + '" frameborder="0" width="' + (size.width+4) + '" height="' + (size.height+4) + '"></iframe>');
                    } else {
                        url = "http://api.map.baidu.com/staticimage?center=" + center.lng + ',' + center.lat +
                            "&zoom=" + zoom + "&width=" + size.width + '&height=' + size.height + "&markers=" + point.lng + ',' + point.lat;
                        editor.execCommand('inserthtml', '<img width="' + size.width + '"height="' + size.height + '" src="' + url + '"' + (widget.imgcss ? ' style="' + widget.imgcss + '"' : '') + '/>', true);
                    }

                    widget.reset();
                }
            },
            cancel: {
                exec: function(editor){
                    editor.getWidgetData(widgetName).reset();
                }
            }
        }
    });

})();

