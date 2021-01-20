<template>
  <div class="article-manager">
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">商品管理</span>
        <div>
          <Tabs v-model="artFrom.type"  @on-click="onClickTab">
            <TabPane :label="item.name +'('+item.count +')' " :name="item.type.toString()" v-for="(item,index) in headeNum" :key="index"/>
          </Tabs>
        </div>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form ref="artFrom" :model="artFrom"  :label-width="75" label-position="right" @submit.native.prevent>
        <Row type="flex" :gutter="24">
          <Col v-bind="grid">
            <FormItem label="商品分类："  label-for="pid">
              <Select v-model="artFrom.cate_id"  clearable @on-change="userSearchs">
                <Option v-for="item in treeSelect" :value="item.id" :key="item.id">{{ item.html + item.cate_name }}</Option>
              </Select>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="商品搜索："  label-for="store_name">
              <Input search enter-button placeholder="请输入商品名称,关键字,ID" v-model="artFrom.store_name"  @on-search="userSearchs"/>
            </FormItem>
          </Col>
        </Row>
      </Form>
      <div class="Button">
        <router-link v-auth="['product-product-save']" :to="'/admin/product/add_product'"><Button type="primary" class="bnt mr15" icon="md-add">添加商品</Button></router-link>
        <Button v-auth="['product-crawl-save']" type="success" class="bnt mr15" @click="onCopy">商品采集</Button>
        <Button v-auth="['product-product-product_show']" class="bnt mr15" @click="onDismount" v-show="artFrom.type === '1'">批量下架</Button>
        <Button v-auth="['product-product-product_show']" class="bnt mr15" @click="onShelves" v-show="artFrom.type === '2'">批量上架</Button>
        <Button v-auth="['export-storeProduct']" class="export" icon="ios-share-outline" @click="exports">导出</Button>
      </div>
      <Table
        ref="table"
        :columns="(artFrom.type !== '1' && artFrom.type !== '2')?columns2:columns"
        :data="tableList"
        class="ivu-mt"
        :loading="loading"
        highlight-row @on-selection-change="onSelectTab"
        no-data-text="暂无数据"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="id">
          {{ row.id }}
        </template>
        <template slot-scope="{ row }" slot="image">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.image">
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="state">
          <i-switch v-model="row.is_show" :value="row.is_show" :true-value="1" :false-value="0" @on-change="changeSwitch(row)" size="large">
            <span slot="open">上架</span>
            <span slot="close">下架</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <router-link :to="{ path: '/admin/product/product_reply/'+ row.id }"><a>查看评论</a></router-link>
          <Divider type="vertical" />
          <a @click="del(row,'恢复商品', index)" v-if="artFrom.type === '6'">恢复商品</a>
          <a @click="del(row,'移入回收站', index)" v-else>移到回收站</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page :total="total" :current="artFrom.page" show-elevator show-total @on-change="pageChange"
              :page-size="artFrom.limit"/>
      </div>
      <attribute :attrTemplate="attrTemplate" v-on:changeTemplate="changeTemplate"></attribute>
    </Card>
    <!-- 生成淘宝京东表单-->
    <Modal v-model="modals" class="Box" scrollable  footer-hide closable title="复制淘宝、天猫、拼多多、京东、苏宁、1688" :mask-closable="false"  width="1200" height="500">
      <tao-bao ref="taobaos" v-if="modals" @on-close="onClose"></tao-bao>
    </Modal>
  </div>
</template>

<script>
    import expandRow from './tableExpand.vue'
    import attribute from './attribute'
    import toExcel from '../../../utils/Excel.js'
    import { mapState } from 'vuex'
    import taoBao from './taoBao'
    import { getGoodHeade, getGoods, PostgoodsIsShow, treeListApi, productShowApi, productUnshowApi, storeProductApi } from '@/api/product'
    export default {
        name: 'product_productList',
        components: { expandRow, attribute, taoBao },
        computed: {
            ...mapState('userLevel', [
                'categoryId'
            ])
        },
        data () {
            return {
                template: false,
                modals: false,
                grid: {
                    xl: 6,
                    lg: 8,
                    md: 12,
                    sm: 24,
                    xs: 24
                },
                artFrom: {
                    page: 1,
                    limit: 15,
                    cate_id: '',
                    type: '1',
                    store_name: '',
                    excel: 0
                },
                list: [],
                tableList: [],
                headeNum: [],
                treeSelect: [],
                loading: false,
                columns: [
                    {
                        type: 'expand',
                        width: 50,
                        render: (h, params) => {
                            return h(expandRow, {
                                props: {
                                    row: params.row
                                }
                            })
                        }
                    },
                    {
                        type: 'selection',
                        width: 60,
                        align: 'center'
                    },
                    {
                        title: '商品ID',
                        key: 'id',
                        width: 80
                    },
                    {
                        title: '商品图',
                        slot: 'image',
                        minWidth: 80
                    },
                    {
                        title: '商品名称',
                        key: 'store_name',
                        minWidth: 250
                    },
                    {
                        title: '商品售价',
                        key: 'price',
                        minWidth: 90
                    },
                    {
                        title: '销量',
                        key: 'sales',
                        sortable: true,
                        minWidth: 90
                    },
                    {
                        title: '库存',
                        key: 'stock',
                        minWidth: 80
                    },
                    {
                        title: '排序',
                        key: 'sort',
                        minWidth: 70
                    },
                    {
                        title: '状态',
                        slot: 'state',
                        width: 100,
                        filters: [
                            {
                                label: '上架',
                                value: 1
                            },
                            {
                                label: '下架',
                                value: 0
                            }
                        ],
                        filterMethod (value, row) {
                            return row.is_show === value
                        },
                        filterMultiple: false
                    },
                    {
                        title: '操作',
                        slot: 'action',
                        fixed: 'right',
                        minWidth: 220
                    }
                ],
                data: [],
                total: 0,
                attrTemplate: false,
                ids: []
            }
        },
        watch: {
          $route(){
            if(this.$route.fullPath==='/admin/product/product_list?type=5'){
              this.getPath();
            }
          }
        },
        created(){

        },
        mounted () {
            this.goodHeade()
            this.goodsCategory()
            if(this.$route.fullPath==='/admin/product/product_list?type=5'){
              this.getPath()
            }else {
              this.getDataList()
            }
        },
        methods: {
          getPath(){
            this.columns2 = [...this.columns];
            if (name !== '1' && name !== '2') {
              this.columns2.shift({
                type: 'selection',
                width: 60,
                align: 'center'
              })
            }
            this.artFrom.page = 1;
            this.artFrom.type = this.$route.query.type.toString();
            this.getDataList();
          },
            // 导出
            exports () {
                let formValidate = this.artFrom
                let data = {
                    cate_id: formValidate.cate_id,
                    type: formValidate.type,
                    store_name: formValidate.store_name
                }
                storeProductApi(data).then(res => {
                    location.href = res.data[0]
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            changeTemplate (e) {
                // this.template = e;
            },
            freight () {
                this.$refs.template.isTemplate = true
            },
            // 批量上架
            onShelves () {
                if (this.ids.length === 0) {
                    this.$Message.warning('请选择要上架的商品')
                } else {
                    let data = {
                        ids: this.ids
                    }
                    productShowApi(data).then(res => {
                        this.$Message.success(res.msg)
                        this.goodHeade()
                        this.getDataList()
                    }).catch(res => {
                        this.$Message.error(res.msg)
                    })
                }
            },
            // 批量下架
            onDismount () {
                if (this.ids.length === 0) {
                    this.$Message.warning('请选择要下架的商品')
                } else {
                    let data = {
                        ids: this.ids
                    }
                    productUnshowApi(data).then(res => {
                        this.$Message.success(res.msg)
                        this.goodHeade()
                        this.getDataList()
                    }).catch(res => {
                        this.$Message.error(res.msg)
                    })
                }
            },
            // 全选
            onSelectTab (selection) {
                let data = []
                selection.map((item) => {
                    data.push(item.id)
                })
                this.ids = data
            },
            // 添加淘宝商品成功
            onClose () {
                this.modals = false
            },
            // 复制淘宝
            onCopy () {
                this.modals = true
            },
            // tab选择
            onClickTab (name) {
                this.artFrom.type = name
                this.columns2 = [...this.columns]
                if (name !== '1' && name !== '2') {
                    this.columns2.shift({
                        type: 'selection',
                        width: 60,
                        align: 'center'
                    })
                }
                this.artFrom.page = 1
                this.getDataList()
            },
            // 下拉树
            handleCheckChange (data) {
                let value = ''
                let title = ''
                this.list = []
                this.artFrom.cate_id = 0
                data.forEach((item, index) => {
                    value += `${item.id},`
                    title += `${item.title},`
                })
                value = value.substring(0, value.length - 1)
                title = title.substring(0, title.length - 1)
                this.list.push({
                    value,
                    title
                })
                this.artFrom.cate_id = value
                this.getDataList()
            },
            // 获取商品表单头数量
            goodHeade () {
                getGoodHeade().then(res => {
                    this.headeNum = res.data.list
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 商品分类；
            goodsCategory () {
                treeListApi(1).then(res => {
                    this.treeSelect = res.data
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 商品列表；
            getDataList () {
                this.loading = true
                this.artFrom.cate_id = this.artFrom.cate_id || ''
                getGoods(this.artFrom).then(res => {
                    let data = res.data
                    this.tableList = data.list
                    this.total = res.data.count
                    this.loading = false
                }).catch(res => {
                    this.loading = false
                    this.$Message.error(res.msg)
                })
            },
            pageChange (status) {
                this.artFrom.page = status
                this.getDataList()
            },
            // 表格搜索
            userSearchs () {
                this.artFrom.page = 1
                this.getDataList()
            },
            // 上下架
            changeSwitch (row) {
                PostgoodsIsShow(row.id, row.is_show).then(res => {
                    this.$Message.success(res.msg)
                    this.goodHeade()
                    this.getDataList()
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            },
            // 数据导出；
            exportData: function () {
                let th = ['商品名称', '商品简介', '商品分类', '价格', '库存', '销量', '收藏人数']
                let filterVal = ['store_name', 'store_info', 'cate_name', 'price', 'stock', 'sales', 'collect']
                this.where.page = 'nopage'
                getGoods(this.where).then(res => {
                    let data = res.data.map(v => filterVal.map(k => v[k]))
                    let fileTime = Date.parse(new Date())
                    let [fileName, fileType, sheetName] = ['商户数据_' + fileTime, 'xlsx', '商户数据']
                    toExcel({ th, data, fileName, fileType, sheetName })
                })
            },
            // 属性弹出；
            attrTap () {
                this.attrTemplate = true
            },
            changeTemplate (msg) {
                this.attrTemplate = msg
            },
            // 编辑
            edit (row) {
                this.$router.push({ path: '/admin/product/add_product/' + row.id })
            },
            // 确认
            del (row, tit, num) {
                let delfromData = {
                    title: tit,
                    num: num,
                    url: `product/product/${row.id}`,
                    method: 'DELETE',
                    ids: ''
                }
                this.$modalSure(delfromData).then((res) => {
                    this.$Message.success(res.msg)
                    this.tableList.splice(num, 1)
                    this.goodHeade()
                }).catch(res => {
                    this.$Message.error(res.msg)
                })
            }
            // 删除成功
            // submitModel () {
            //     this.tableList.splice(this.delfromData.num, 1);
            //     this.goodHeade();
            // }
        }
    }
</script>
<style scoped lang="stylus">
  /deep/.ivu-modal-mask
    z-index 999!important;
  /deep/.ivu-modal-wrap
    z-index 999!important;
  .Box
    >>> .ivu-modal-body
      height 700px
      overflow: auto;
  .tabBox_img
    width 36px
    height 36px
    border-radius:4px
    cursor pointer
    img
      width 100%
      height 100%
</style>
