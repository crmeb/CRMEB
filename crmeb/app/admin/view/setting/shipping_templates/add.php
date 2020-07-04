{extend name="public/container"}
{block name="content"}
<style>
    .red {
        color: red;
    }
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" id="app" v-cloak="">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body">
                    <form class="layui-form" @submit.stop>
                        <div class="layui-form-item">
                            <label class="layui-form-label">模板名称<i class="red">*</i></label>
                            <div class="layui-input-block">
                                <input type="text" name="name" v-model="formData.name" placeholder="请输入模板名称"
                                       class="layui-input" lay-verify="required">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">计费方式</label>
                            <div class="layui-input-block">
                                <input type="radio" name="type" v-model="formData.type"
                                       :checked="formData.type == 1 ? true : false" value="1" title="按件数"
                                       lay-filter="type">
                                <input type="radio" name="type" v-model="formData.type"
                                       :checked="formData.type == 2 ? true : false" value="2" title="按重量"
                                       lay-filter="type">
                                <input type="radio" name="type" v-model="formData.type"
                                       :checked="formData.type == 3 ? true : false" value="3" title="按体积"
                                       lay-filter="type">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">配送区域及运费</label>
                            <div class="layui-input-block">
                                <table class="layui-table">
                                    <colgroup>
                                        <col width="150">
                                        <col width="200">
                                        <col>
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th>可配送区域<i class="red">*</i></th>
                                        <th v-if="formData.type == 1">首件<i class="red">*</i></th>
                                        <th v-else-if="formData.type == 2">首件重量(KG)<i class="red">*</i></th>
                                        <th v-else>首件体积(m³)<i class="red">*</i></th>
                                        <th>运费(元)<i class="red">*</i></th>
                                        <th v-if="formData.type == 1">续件<i class="red">*</i></th>
                                        <th v-else-if="formData.type == 2">续件重量(KG)<i class="red">*</i></th>
                                        <th v-else>续件体积(m³)<i class="red">*</i></th>
                                        <th>续费(元)<i class="red">*</i></th>
                                        <th>操作</th>
                                    </tr>
                                    </thead>
                                    <tbody id="region">
                                    <tr v-for="(item,index) in templateList">
                                        <td>
                                            <input type="text" class="layui-input" :value="item.regionName" readonly>
                                        </td>
                                        <td>
                                            <input type="number" v-model="item.first" class="layui-input"
                                                   lay-verify="required">
                                        </td>
                                        <td>
                                            <input type="number" v-model="item.price" class="layui-input"
                                                   lay-verify="required">
                                        </td>
                                        <td>
                                            <input type="number" v-model="item.continue" class="layui-input"
                                                   lay-verify="required">
                                        </td>
                                        <td>
                                            <input type="number" v-model="item.continue_price" class="layui-input"
                                                   lay-verify="required">
                                        </td>
                                        <td style="width:100px;" v-if="item.regionName != '默认全国' ">
                                            <!--                                            <button type="button" class="layui-btn layui-btn-sm layui-btn-info">修改</button>-->
                                            <button type="button" class="layui-btn layui-btn-sm layui-btn-danger"
                                                    @click="del(1,index)">删除
                                            </button>
                                        </td>
                                        <td style="width:100px;" v-else></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="layui-form-item" style="margin-top: 10px">
                            <div class="layui-input-block">
                                <button class="layui-btn layui-btn-sm" @click="open(1)" type="button">
                                    单独添加配送区域
                                </button>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">指定包邮</label>
                            <div class="layui-input-block">
                                <input type="radio" name="appoint_check"
                                       :checked="formData.appoint_check == 1 ? true : false"
                                       v-model="formData.appoint_check" value="1" title="开启" lay-filter="appoint_check">
                                <input type="radio" name="appoint_check"
                                       :checked="formData.appoint_check == 0 ? true : false"
                                       v-model="formData.appoint_check" value="0" title="关闭" lay-filter="appoint_check">
                            </div>
                        </div>
                        <div v-show="formData.appoint_check == 1">
                            <div class="layui-form-item">
                                <label class="layui-form-label"></label>
                                <div class="layui-input-block">
                                    <table class="layui-table">
                                        <colgroup>
                                            <col width="150">
                                            <col width="200">
                                            <col>
                                        </colgroup>
                                        <thead>
                                        <tr>
                                            <th>选择地区<i class="red">*</i></th>
                                            <th v-if="formData.type == 1">包邮件数<i class="red">*</i></th>
                                            <th v-else-if="formData.type == 2">包邮重量(KG)<i class="red">*</i></th>
                                            <th v-else>包邮体积(m³)<i class="red">*</i></th>
                                            <th>包邮金额(元)<i class="red">*</i></th>
                                            <th>操作</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(item,index) in appointList">
                                            <td><input type="text" class="layui-input"
                                                       :value="item.placeName"
                                                       readonly></td>
                                            <td><input type="number" autocomplete="off" class="layui-input"
                                                       v-model="item.a_num"></td>
                                            <td><input type="number" autocomplete="off" class="layui-input"
                                                       v-model="item.a_price"></td>
                                            <td>
                                                <!--                                                <button type="button" class="layui-btn layui-btn-sm layui-btn-info">修改</button>-->
                                                <button type="button" class="layui-btn layui-btn-sm layui-btn-danger"
                                                        @click="del(2,index)">删除
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="appointList.length <= 0">
                                            <td colspan="5" style="text-align: center;">暂无</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="layui-form-item" style="margin-top: 10px">
                                <div class="layui-input-block">
                                    <button class="layui-btn layui-btn-sm" @click="open(2)" type="button">
                                        单独指定包邮区域
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item" style="margin-top: 10px">
                            <label class="layui-form-label">排序</label>
                            <div class="layui-input-block">
                                <input type="number" name="sort" v-model="formData.sort" placeholder="请输入值越大越靠前"
                                       class="layui-input">
                            </div>
                        </div>
                        <div class="layui-form-item" style="margin-top: 10px">
                            <div class="layui-input-block">
                                <button class="layui-btn" type="button" @click="handleSubmit">{{id ? '立即修改':'立即提交'}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{__ADMIN_PATH}js/layuiList.js"></script>
<script>
    var id = {$id};
    require(['vue'], function (Vue) {
        new Vue({
            el: "#app",
            data: {
                selectCityList: [],
                selectTypeCityList: [],
                templateList: [
                    {
                        region: [
                            {
                                name: '默认全国',
                                city_id: 0,
                            }
                        ],
                        regionName: "默认全国",
                        first: 0,
                        price: 0,
                        'continue': 0,
                        continue_price: 0,
                        action: true,
                    }
                ],
                appointList: [],
                id: id,
                formData: {
                    type: 1,
                    sort: 0,
                    name: '',
                    appoint_check: 0,
                },
            },
            watch: {
                'formData.appoint_check': function (n) {
                    console.log(n);
                }
            },
            methods: {
                getTemplateInfo: function () {
                    if (!this.id) return;
                    var that = this;
                    layList.baseGet(layList.Url({a: 'edit', q: {id: this.id}}), function (res) {
                        that.$set(that, 'appointList', res.data.appointList || []);
                        that.$set(that, 'templateList', res.data.templateList || []);
                        if (!that.templateList.length) {
                            that.$set(that, 'templateList', [
                                {
                                    region: [
                                        {
                                            name: '默认全国',
                                            city_id: 0,
                                        }
                                    ],
                                    regionName: "默认全国",
                                    first: 0,
                                    price: 0,
                                    'continue': 0,
                                    continue_price: 0,
                                    action: true,
                                }
                            ]);
                        } else {
                            if (that.templateList[0].region === undefined) {
                                that.$set(that.templateList[0], 'region', [{name: '默认全国', city_id: 0}]);
                                that.$set(that.templateList[0], 'regionName', '默认全国');
                            }
                        }
                        that.formData = res.data.formData;
                        that.render();
                    }, function (res) {
                        return layList.msg(res.msg);
                    })
                },
                del: function (type, index) {
                    switch (type) {
                        case 1:
                            this.templateList.splice(index, 1);
                            break;
                        case 2:
                            this.appointList.splice(index, 1);
                            break;
                    }
                },
                open: function (type, edit) {
                    if (edit == undefined) edit = 0;
                    switch (type) {
                        case 1:
                            this.createFrame('单独添加配送区域', layList.Url({a: 'city', p: {type: 1, isedit: edit}}));
                            break;
                        case 2:
                            this.createFrame('单独指定包邮区域', layList.Url({a: 'city', p: {type: 2, isedit: edit}}));
                            break;
                    }
                },
                createFrame: function (title, src, opt) {
                    opt === undefined && (opt = {});
                    return layer.open({
                        type: 2,
                        title: title,
                        area: [(opt.w || 1000) + 'px', (opt.h || 700) + 'px'],
                        fixed: false, //不固定
                        maxmin: true,
                        moveOut: false,//true  可以拖出窗外  false 只能在窗内拖
                        anim: 5,//出场动画 isOutAnim bool 关闭动画
                        offset: 'auto',//['100px','100px'],//'auto',//初始位置  ['100px','100px'] t[ 上 左]
                        shade: 0,//遮罩
                        resize: true,//是否允许拉伸
                        content: src,//内容
                        move: '.layui-layer-title'
                    });
                },
                render: function () {
                    var that = this, radioRule = ['appoint_check', 'type'];
                    that.$nextTick(function () {
                        layList.form.render();
                        radioRule.map(function (val) {
                            layList.form.on('radio(' + val + ')', function (res) {
                                that.formData[val] = res.value;
                            });
                        })
                    })
                },
                selectCity: function (data, type) {
                    var cityName = data.map(function (item) {
                        return item.name;
                    }).join(';');
                    switch (type) {
                        case 1:
                            this.templateList.push({
                                region: data,
                                regionName: cityName,
                                first: 0,
                                price: 0,
                                'continue': 0,
                                continue_price: 0
                            });
                            break;
                        case 2:
                            this.appointList.push({
                                place: data,
                                placeName: cityName,
                                a_num: 0,
                                a_price: 0
                            });
                            break;
                    }
                },
                handleSubmit: function () {
                    var that = this;
                    for (var i = 0; i < that.templateList.length; i++) {
                        if (!that.templateList[i].first) {
                            return layList.msg('请填写首件数量');
                        }
                        if (that.templateList[i].continue <= 0) {
                            return layList.msg('请填写续件数量');
                        }
                    }
                    layList.basePost(layList.Url({a: 'save', p: {id: this.id}}), {
                        appoint_info: that.appointList,
                        region_info: that.templateList,
                        sort: that.formData.sort,
                        type: that.formData.type,
                        name: that.formData.name,
                        appoint: that.formData.appoint_check,
                    }, function (res) {
                        layList.msg(res.msg, function () {
                            parent.layer.close(parent.layer.getFrameIndex(window.name));
                            parent.$(".J_iframe:visible")[0].contentWindow.location.reload();
                        });
                    });
                }
            },
            mounted: function () {
                this.getTemplateInfo();
                if (!this.id) this.render();
                window.selectCity = this.selectCity;
            }
        })
    })
</script>
{/block}