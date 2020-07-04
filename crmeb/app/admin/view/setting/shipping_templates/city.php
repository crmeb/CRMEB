{extend name="public/container"}
{block name="content"}
<style>
    .smallplace .citys {
        width: auto;
        background-color: #fff;
        position: absolute;
        top: 33px;
        border: 1px solid #ccc;
        z-index: 100;
        display: none;
    }

    .smallplace .citys.on {
        display: block;
    }

    .smallplace .citys > i.jt {
        width: 0;
        height: 0;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        border-bottom: 10px solid #ccc;
        position: absolute;
        top: -10px;
        left: 20px;
    }

    .smallplace .citys .row-div {
        min-width: 250px;
        padding: 10px;
        box-sizing: border-box;
    }

    .clearfloat {
        zoom: 1;
    }

    .layui-input-block {
        margin-left: 0
    }

    .jt-mk{
        display: block;
        position: absolute;
        top: -10px;
        left: 20px;
        width: 252px;
        height: 10px;
        background-color: rgba(255, 255, 255, 0);
        margin-left: -21px;
    }
</style>
<div class="layui-fluid" style="background: #fff;margin-top: -10px;">
    <div class="layui-row layui-col-space15" id="app" v-cloak="">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body" style="text-align: right">
                    <button type="button" class="layui-btn layui-btn-normal layui-btn-sm" @click="save">确定</button>
                    <button type="button" class="layui-btn layui-btn-warm layui-btn-sm" @click="clerl">取消</button>
                </div>
                <div class="layui-card-body">
                    <form class="layui-form" action="">
                        <div class="layui-form-item" pane="">
                            <div class="layui-input-block" v-cloak="">
                                <span @click="allCheckbox(true)">
                                    <input type="radio" name="select" title="全选">
                                </span>
                                <span @click="allCheckbox(false)">
                                    <input type="radio" name="select" title="取消">
                                </span>
                            </div>
                        </div>
                        <div class="layui-form-item" pane="">
                            <div class="layui-input-block" v-cloak="">
                                <div style="margin-top: 15px;position: relative"
                                     class="layui-col-md3 layui-col-lg3 layui-col-xs3 layui-col-sm3 selected"
                                     v-for="(item,index) in cityList" @click="checkedClick(index)">
                                    <input ref="checkbox" type="checkbox" lay-skin="primary" :title="item.name"
                                           :checked="item.checked ? true : false" lay-filter="city" :value="index">
                                    <span style="color: #ff0000">({{(item.count || 0) + '/' + item.children.length}})</span>

                                    <div class="smallplace" @click.stop="">
                                        <div class="citys" :class="item.on ? 'on' : ''">
                                            <i class="jt"><i></i></i>
                                            <i class="jt-mk"></i>
                                            <div class="row-div clearfloat" style="max-width: 300px">
                                                <div class="layui-form-item" pane="">
                                                    <div class="layui-input-block" v-cloak="" style="margin-left: 0;">
                                                        <span v-for="(val,i) in item.children"
                                                              @click="primary(index,i)">
                                                            <input type="checkbox" ref="primary" lay-skin="primary"
                                                                   :title="val.name"
                                                                   :checked="val.checked ? true : false">
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
{/block}
{block name="script"}
<script>
    var type=<?=$type?>;
    mpFrame.start(function (Vue) {
        new Vue({
            el: "#app",
            data: {
                cityList: [],
                areaList: [],
                selectList: {}
            },
            methods: {
                /**
                 * 确认选中
                 */
                save: function () {
                    var that = this;
                    var selectList = [];
                    $.each(this.cityList, function (key, item) {
                        var data = {};
                        if (item.checked || item.children.find(function (k) {
                            return !!k.checked;
                        })) {
                            data = {
                                name: item.name,
                                city_id: item.city_id,
                                children: []
                            };
                            for (index in item.children) {
                                if (!!item.children[index].checked) {
                                    data.children.push({
                                        city_id: item.children[index].city_id,
                                    });
                                }
                            }
                            selectList.push(data);
                        }
                    });
                    if(selectList.length ==0){
                        return that.showMsg('至少选择一个省份或者城市');
                    }
                    parent.selectCity(selectList,type);
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                },
                /**
                 * 关闭
                 */
                clerl: function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    parent.layer.close(index);
                },
                /**
                 * 全选或者反选
                 * @param checked
                 */
                allCheckbox: function (checked) {
                    var that = this;
                    $.each(this.cityList, function (key, item) {
                        that.$set(that.cityList[key], 'checked', checked);
                        if (checked) {
                            that.$set(that.cityList[key], 'count', that.cityList[key].children.length);
                        } else {
                            that.$set(that.cityList[key], 'count', 0);
                        }
                        $.each(that.cityList[key].children, function (k, val) {
                            that.$set(that.cityList[key].children[k], 'checked', checked);
                        })
                    });
                    this.render();
                },
                /**
                 * 获取城市列表,绑定事件
                 */
                getCityList() {
                    var that = this;
                    layList.baseGet(layList.U({a: "city_list", q: {id: 0}}), function (res) {
                        that.$set(that, 'cityList', res.data);
                        that.render();
                        that.$nextTick(function () {
                            this.$nextTick(function () {
                                layList.form.render('radio');
                                $('.selected').on({
                                    mouseover: function (event) {
                                        event.stopPropagation()
                                        $(this).find('.citys').addClass('on');
                                    },
                                    mouseout: function (event) {
                                        event.stopPropagation()
                                        $(this).find('.citys').removeClass('on');
                                    }
                                })
                            })
                        });
                    });

                },
                /**
                 * 重新渲染checkbox
                 */
                render: function () {
                    this.$nextTick(function () {
                        layList.form.render('checkbox');
                    })
                },
                showMsg: function (msg, success) {
                    layui.use(['layer'], function () {
                        layui.layer.msg(msg, success);
                    });
                },
                /**
                 * 点击省
                 * @param index
                 */
                checkedClick: function (index) {
                    var that = this;
                    if (this.$refs.checkbox[index].checked) {
                        that.$set(that.cityList[index], 'count', that.cityList[index].children.length);
                        that.$set(that.cityList[index], 'checked', true);
                        $.each(that.cityList[index].children, function (key, item) {
                            that.$set(that.cityList[index].children[key], 'checked', true);
                        })
                    } else {
                        that.$set(that.cityList[index], 'count', 0);
                        that.$set(that.cityList[index], 'checked', false);
                        $.each(that.cityList[index].children, function (key, item) {
                            that.$set(that.cityList[index].children[key], 'checked', false);
                        })
                    }
                    this.render();
                },
                /**
                 * 点击市区
                 * @param index
                 * @param ind
                 */
                primary: function (index, ind) {
                    var checked = false, count = 0;
                    if (this.cityList[index].children[ind].checked === undefined) {
                        this.$set(this.cityList[index].children[ind], 'checked', true)
                    } else {
                        this.$set(this.cityList[index].children[ind], 'checked', !this.cityList[index].children[ind].checked);
                    }
                    $.each(this.cityList[index].children, function (key, item) {
                        if (item.checked) {
                            checked = true;
                            count++;
                        }
                    });
                    this.$set(this.cityList[index], 'count', count);
                    this.$set(this.cityList[index], 'checked', checked);
                    this.render();
                }
            },
            mounted: function () {
                this.getCityList();
            }
        })
    })
</script>
{/block}