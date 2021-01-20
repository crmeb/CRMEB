<template>
    <div>
        <Modal
                v-model="isTemplate"
                title="运费模版"
                width="70%"
                if="isTemplate"
                @on-cancel="cancel"
        >
            <div class="Modals">
                <Form  class="form" ref="formData" :label-width="120" label-position="right">
                    <Row :gutter="24" type="flex">
                        <Col :xl="18" :lg="18" :md="18" :sm="24" :xs="24">
                            <FormItem label="模板名称：" prop="name">
                                <Input type="text" placeholder="请输入模板名称" :maxlength='20' v-model="formData.name"/>
                            </FormItem>
                        </Col>
                    </Row>
                    <Row :gutter="24" type="flex">
                        <Col :xl="18" :lg="18" :md="18" :sm="24" :xs="24">
                            <FormItem label="计费方式：" props="state" label-for="state">
                                <RadioGroup class="radio" v-model="formData.type" @on-change="changeRadio" element-id="state">
                                    <Radio :label="1">按件数</Radio>
                                    <Radio :label="2">按重量</Radio>
                                    <Radio :label="3">按体积</Radio>
                                </RadioGroup>
                            </FormItem>
                        </Col>
                    </Row>
                    <Row :gutter="24" type="flex">
                        <Col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
                            <FormItem class="label" label="配送区域及运费：" props="state" label-for="state">
                                <Table
                                        ref="table"
                                        :columns="columns"
                                        :data="templateList"
                                        class="ivu-mt"
                                        no-data-text="暂无数据"
                                        border
                                >
                                    <template slot-scope="{ row, index }" slot="action">
                                        <a v-if="row.regionName!=='默认全国'" @click="delCity(row,'配送区域',index,1)">删除</a>
                                    </template>
                                </Table>
                                <Row type="flex" class="addTop">
                                    <Col>
                                        <Button type="primary" icon="md-add" @click="addCity(1)">单独添加配送区域</Button>
                                    </Col>
                                </Row>
                            </FormItem>
                        </Col>
                    </Row>
                    <Row :gutter="24" type="flex">
                        <Col :xl="24" :lg="24" :md="24" :sm="24" :xs="24">
                            <FormItem label="指定包邮：" prop="store_name" label-for="store_name">
                                <Radio-group class="radio" v-model="formData.appoint_check">
                                    <Radio :label="1">开启</Radio>
                                    <Radio :label="0">关闭</Radio>
                                </Radio-group>
                                <Table
                                        ref="table"
                                        :columns="columns2"
                                        :data="appointList"
                                        class="addTop ivu-mt"
                                        no-data-text="暂无数据"
                                        border
                                        v-if="formData.appoint_check === 1"
                                >
                                    <template slot-scope="{ row, index }" slot="action">
                                        <a v-if="row.regionName!=='默认全国'" @click="delCity(row,'配送区域',index,2)">删除</a>
                                    </template>
                                </Table>
                                <Row type="flex" class="addTop" v-if="formData.appoint_check === 1">
                                    <Col>
                                        <Button type="primary" icon="md-add" @click="addCity(2)">单独指定包邮</Button>
                                    </Col>
                                </Row>
                            </FormItem>
                        </Col>
                    </Row>
                    <Row :gutter="24" type="flex">
                        <Col :xl="18" :lg="18" :md="18" :sm="24" :xs="24">
                            <FormItem label="排序：" prop="store_name" label-for="store_name">
                                <InputNumber :min="0" placeholder="输入值越大越靠前" v-model="formData.sort" ></InputNumber>
                            </FormItem>
                        </Col>
                    </Row>
                    <Row :gutter="24" type="flex">
                        <Col>
                            <FormItem prop="store_name" label-for="store_name">
                                <Button type="primary" @click="handleSubmit">{{id ? '立即修改':'立即提交'}}</Button>
                            </FormItem>
                        </Col>
                    </Row>
                </Form>
            </div>
            <div slot="footer"></div>
        </Modal>
        <city ref="city" @selectCity="selectCity" :type="type"></city>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import city from '@/components/freightTemplate/city'
    import { templatesSaveApi, shipTemplatesApi } from '@/api/setting'
    export default {
        name: 'freightTemplate',
        components: { city },
        props: {
        },
        data () {
            let that = this
            return {
                isTemplate: false,
                columns: [
                    {
                        title: '可配送区域',
                        key: 'regionName',
                        minWidth: 100,
                        render: (h, params) => {
                            return h('Input', {
                                props: {
                                    type: 'text',
                                    readonly: true,
                                    size: 'small',
                                    value: that.templateList[params.index].regionName
                                }
                            })
                        }
                    },
                    {
                        title: '首件',
                        key: 'first',
                        minWidth: 70,
                        render: (h, params) => {
                            return h('Input', {
                                props: {
                                    type: 'number',
                                    size: 'small',
                                    value: that.templateList[params.index].first // 此处如何让数据双向绑定
                                },
                                on: {
                                    'on-change': (event) => {
                                        that.templateList[params.index].first = event.target.value
                                    }
                                }
                            })
                        }
                    },
                    {
                        title: '运费（元）',
                        key: 'price',
                        minWidth: 70,
                        render: (h, params) => {
                            return h('Input', {
                                props: {
                                    type: 'number',
                                    size: 'small',
                                    value: that.templateList[params.index].price // 此处如何让数据双向绑定
                                },
                                on: {
                                    'on-change': (event) => {
                                        that.templateList[params.index].price = event.target.value
                                    }
                                }
                            })
                        }
                    },
                    {
                        title: '续件',
                        key: 'continue',
                        minWidth: 70,
                        render: (h, params) => {
                            return h('Input', {
                                props: {
                                    type: 'number',
                                    size: 'small',
                                    value: that.templateList[params.index].continue // 此处如何让数据双向绑定
                                },
                                on: {
                                    'on-change': (event) => {
                                        that.templateList[params.index].continue = event.target.value
                                    }
                                }
                            })
                        }
                    },
                    {
                        title: '续费（元）',
                        key: 'continue_price',
                        minWidth: 70,
                        render: (h, params) => {
                            return h('Input', {
                                props: {
                                    type: 'number',
                                    size: 'small',
                                    value: that.templateList[params.index].continue_price // 此处如何让数据双向绑定
                                },
                                on: {
                                    'on-change': (event) => {
                                        that.templateList[params.index].continue_price = event.target.value
                                    }
                                }
                            })
                        }
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        minWidth: 70
                    }
                ],
                columns2: [
                    {
                        title: '选择地区',
                        key: 'placeName',
                        minWidth: 250,
                        render: (h, params) => {
                            return h('Input', {
                                props: {
                                    type: 'text',
                                    readonly: true,
                                    size: 'small',
                                    value: that.appointList[params.index].placeName
                                }
                            })
                        }
                    },
                    {
                        title: '包邮件数',
                        key: 'a_num',
                        minWidth: 100,
                        render: (h, params) => {
                            return h('Input', {
                                props: {
                                    type: 'number',
                                    size: 'small',
                                    value: that.appointList[params.index].a_num // 此处如何让数据双向绑定
                                },
                                on: {
                                    'on-change': (event) => {
                                        that.appointList[params.index].a_num = event.target.value
                                    }
                                }
                            })
                        }
                    },
                    {
                        title: '包邮金额（元）',
                        key: 'a_price',
                        minWidth: 100,
                        render: (h, params) => {
                            return h('Input', {
                                props: {
                                    type: 'number',
                                    size: 'small',
                                    value: that.appointList[params.index].a_price // 此处如何让数据双向绑定
                                },
                                on: {
                                    'on-change': (event) => {
                                        that.appointList[params.index].a_price = event.target.value
                                    }
                                }
                            })
                        }
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        minWidth: 100
                    }
                ],
                templateList: [
                    {
                        region: [
                            {
                                name: '默认全国',
                                city_id: 0
                            }
                        ],
                        regionName: '默认全国',
                        first: 1,
                        price: 0,
                        continue: 1,
                        continue_price: 0
                    }
                ],
                appointList: [],
                type: 1,
                formData: {
                    type: 1,
                    sort: 0,
                    name: '',
                    appoint_check: 0
                },
                id: 0,

                addressModal: false,
                indeterminate: true,
                checkAll: false,
                checkAllGroup: [],
                activeCity: -1,
                provinceAllGroup: [],
                index: -1,
                displayData: '',
                currentProvince: ''
            }
        },
        computed: {
        },
        methods: {
            editFrom (id) {
                this.id = id
                shipTemplatesApi(id).then(res => {
                    let formData = res.data.formData
                    this.templateList = res.data.templateList
                    this.appointList = res.data.appointList
                    this.formData = {
                        type: formData.type,
                        sort: formData.sort,
                        name: formData.name,
                        appoint_check: formData.appoint_check
                    };
                    this.headerType();
                })
            },
            selectCity: function (data, type) {
                let cityName = data.map(function (item) {
                    return item.name
                }).join(';')
                switch (type) {
                case 1:
                    this.templateList.push({
                        region: data,
                        regionName: cityName,
                        first: 1,
                        price: 0,
                        continue: 1,
                        continue_price: 0
                    })
                    break
                case 2:
                    this.appointList.push({
                        place: data,
                        placeName: cityName,
                        a_num: 0,
                        a_price: 0
                    })
                    break
                }
            },
            // 单独添加配送区域
            addCity (type) {
                this.$refs.city.addressModal = true
                this.type = type
                this.$refs.city.getCityList()
            },
            changeRadio () {
                this.headerType();
            },
            headerType () {
                let that = this;
                if (this.formData.type === 2) {
                    that.columns[1].title = '首件重量(KG)'
                    that.columns[3].title = '续件重量(KG)'
                    that.columns2[1].title = '包邮重量(KG)'
                } else if (this.formData.type === 3) {
                    that.columns[1].title = '首件体积(m³)'
                    that.columns[3].title = '续件体积(m³)'
                    that.columns2[1].title = '包邮体积(m³)'
                } else {
                    that.columns[1].title = '首件'
                    that.columns[3].title = '续件'
                    that.columns2[1].title = '包邮件数'
                }
            },
            // 提交
            handleSubmit: function () {
                let that = this
                if (!that.formData.name.trim().length) {
                    return that.$Message.error('请填写模板名称')
                }
                for (let i = 0; i < that.templateList.length; i++) {
                    if (that.templateList[i].first <= 0) {
                        return that.$Message.error('首件/重量/体积应大于0')
                    }
                    if (that.templateList[i].price < 0) {
                        return that.$Message.error('运费应大于等于0')
                    }
                    if (that.templateList[i].continue <= 0) {
                        return that.$Message.error('续件/重量/体积应大于0')
                    }
                    if (that.templateList[i].continue_price < 0) {
                        return that.$Message.error('续费应大于等于0')
                    }
                }
                if (that.formData.appoint_check === 1) {
                    for (let i = 0; i < that.appointList.length; i++) {
                        if (that.appointList[i].a_num <= 0) {
                            return that.$Message.error('包邮件数应大于0')
                        }
                        if (that.appointList[i].a_price < 0) {
                            return that.$Message.error('包邮金额应大于等于0')
                        }
                    }
                }
                let data = {
                    appoint_info: that.appointList,
                    region_info: that.templateList,
                    sort: that.formData.sort,
                    type: that.formData.type,
                    name: that.formData.name,
                    appoint: that.formData.appoint_check
                }
                templatesSaveApi(that.id, data).then(res => {
                    this.isTemplate = false
                    this.$parent.getList()
                    this.formData = {
                        type: 1,
                        sort: 0,
                        name: '',
                        appoint_check: 0
                    }
                    this.appointList = []
                    this.addressModal = false
                    this.templateList = [
                        {
                            region: [
                                {
                                    name: '默认全国',
                                    city_id: 0
                                }
                            ],
                            regionName: '默认全国',
                            first: 1,
                            price: 0,
                            continue: 1,
                            continue_price: 0
                        }
                    ]
                    this.$Message.success(res.msg);
                })
            },
            // 删除
            delCity (row, tit, num, type) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `setting/shipping_templates/del/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    if (type === 1) {
                        this.templateList.splice(num, 1)
                    } else {
                        this.appointList.splice(num, 1)
                    }
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 关闭
            cancel () {
                this.formData = {
                    type: 1,
                    sort: 0,
                    name: '',
                    appoint_check: 0
                }
                this.appointList = []
                this.addressModal = false
                this.templateList = [
                    {
                        region: [
                            {
                                name: '默认全国',
                                city_id: 0
                            }
                        ],
                        regionName: '默认全国',
                        first: 0,
                        price: 0,
                        continue: 0,
                        continue_price: 0
                    }
                ]
            },

            address () {
                this.addressModal = true
            },
            enter (index) {
                this.activeCity = index
            },
            leave () {
                this.activeCity = null
            }

        },
        mounted () {
            console.log(123)
            console.log(this.templateList)
            // console.log(this.provinceAllGroup)
        }
    }
</script>
<style lang="stylus" scoped>
  .ivu-table-wrapper{
    border-left: 1px solid #dcdee2;
    border-top: 1px solid #dcdee2;
  }
    .ivu-table-border th, .ivu-table-border td{
        padding: 0 10px !important;
    }
    .addTop{margin-top: 15px;}
    .radio{padding: 5px 0;}
    .ivu-input-number
        width 100%
</style>
