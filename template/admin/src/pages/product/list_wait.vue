<template>
    <div class="article-manager">
        <div class="i-layout-page-header">
            <PageHeader
                    title="商品管理"
                    hidden-breadcrumb
                    :tab-list="tabList"
                    :tab-active-key="tabActiveKey"
                    @on-tab-change="handleChangeTab"
            >
            </PageHeader>
        </div>
        <Card :bordered="false" dis-hover class="ivu-mt">
            <div class="acea-row row-middle">
                <span>商品分类：</span>
                <TreeSelect v-model="value" :data="treeSelect" style="width:160px;"/>
                <div class="ivu-search acea-row row-middle">
                    <div>商品搜索：</div>
                    <div class="ivu-mt ivu-mb" style="max-width:200px;">
                        <Input search size="default" enter-button placeholder="请输入"/>
                    </div>
                    <Button class="export" @click="exportData">导出</Button>
                </div>
            </div>
            <div class="Button">
                <Button type="primary" class="bnt">批量上架</Button>
            </div>
            <Table
                    ref="table"
                    :columns="columns"
                    :data="data"
                    class="ivu-mt"
                    :loading="loading"
                    no-data-text="暂无数据"
                    no-filtered-data-text="暂无筛选结果"
            >
                <template slot-scope="{ row }" slot="id">
                    {{ row.id }}
                </template>
                <template slot-scope="{ row }" slot="image">
                    <img v-lazy="row.image"/>
                </template>
                <template slot-scope="{ row }" slot="name">
                    {{row.name}}
                </template>
                <template slot-scope="{ row }" slot="sales">
                    {{row.sales}}
                </template>
                <template slot-scope="{ row }" slot="voidSales">
                    {{row.voidSales}}
                </template>
                <template slot-scope="{ row }" slot="stock">
                    {{ row.stock }}
                </template>
                <template slot-scope="{ row }" slot="collects">
                    {{ row.collects }}
                </template>
                <template slot-scope="{ row }" slot="sort">
                    {{ row.sort }}
                </template>
                <template slot-scope="{ row }" slot="state">
                    <Switch size="large" v-model="row.is_show ? true:false" @on-change="change">
                        <span slot="open">上架</span>
                        <span slot="close">下架</span>
                    </Switch>
                </template>
                <template slot-scope="{ row, index }" slot="action">
                    <a>编辑</a>
                    <Divider type="vertical"/>
                    <template>
                        <Dropdown>
                            <a href="javascript:void(0)">
                                更多
                                <Icon type="ios-arrow-down"></Icon>
                            </a>
                            <DropdownMenu slot="list">
                                <DropdownItem>驴打滚</DropdownItem>
                                <DropdownItem>炸酱面</DropdownItem>
                                <DropdownItem>豆汁儿</DropdownItem>
                                <Dropdown placement="right-start">
                                    <DropdownItem>
                                        北京烤鸭
                                        <Icon type="ios-arrow-forward"></Icon>
                                    </DropdownItem>
                                    <DropdownMenu slot="list">
                                        <DropdownItem>挂炉烤鸭</DropdownItem>
                                        <DropdownItem>焖炉烤鸭</DropdownItem>
                                    </DropdownMenu>
                                </Dropdown>
                                <DropdownItem>冰糖葫芦</DropdownItem>
                            </DropdownMenu>
                        </Dropdown>
                    </template>
                </template>
            </Table>
            <div class="acea-row row-right page">
                <Page :total="total" show-elevator/>
            </div>
        </Card>
    </div>
</template>
<style scoped lang="less">
    img {
        height: 36px;
        display: block;
    }

    .ivu-mt .ivu-search {
        margin-left: 16px;
    }

    .ivu-mt .export {
        margin-left: 6px;
    }

    .ivu-mt .Button .bnt {
        margin-right: 6px;
    }
</style>
<script>
import mixin from './mixins'
import expandRow from './tableExpand.vue'

export default {
  name: 'product_list_wait',
  mixins: [mixin],
  components: { expandRow },
  data () {
    return {
      tabActiveKey: 'list_wait',
      loading: false,
      cityList: [
        {
          value: '跌幅很大功夫大湖股份',
          label: '跌幅很大功夫大湖股份'
        },
        {
          value: 'London',
          label: 'London'
        }
      ],
      model11: '',
      model12: '',
      columns: [
        {
          type: 'selection',
          width: 60,
          align: 'center'
        },
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
          title: '商品ID',
          slot: 'id',
          width: 100
        },
        {
          title: '商品图',
          slot: 'image',
          minWidth: 80
        },
        {
          title: '商品名称',
          slot: 'name',
          minWidth: 250
        },
        {
          title: '销量',
          slot: 'sales',
          sortable: true,
          minWidth: 90
        },
        {
          title: '虚拟销量',
          slot: 'voidSales',
          minWidth: 100
        },
        {
          title: '库存',
          slot: 'stock',
          minWidth: 80
        },
        {
          title: '收藏',
          slot: 'collects',
          minWidth: 70
        },
        {
          title: '排序',
          slot: 'sort',
          minWidth: 70
        },
        {
          title: '状态',
          key: 'state',
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
            console.log(value + 'kk' + row.id)
            return row.is_show === value
          },
          filterMultiple: false
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 150
        }
      ],
      data: [],
      total: 0
    }
  },
  mounted () {
    this.getData()
  },
  methods: {
    getData () {
      this.loading = true
      setTimeout(() => {
        let data = [
          {
            id: '1120',
            image: 'https://dev-file.iviewui.com/PKCycgm3DWJOca5I2uAEqneuLFQAcKa7/middle',
            name: 'CRMEB商城系统V3.0将在8月与大家见面',
            sales: '357万',
            voidSales: '400万',
            stock: '3570',
            collects: '26',
            sort: '2',
            is_show: 1,
            job: 'Data engineer',
            interest: 'badminton',
            birthday: '1991-05-14',
            book: 'Steve Jobs',
            movie: 'The Prestige'
          },
          {
            id: '2',
            image: 'https://dev-file.iviewui.com/KUa7CaC6m7vRtDCfY0SAXlp7dw9OvBrf/middle',
            name: 'CRMEB商城系统V3.0将在8月与大家见面',
            sales: '357万',
            voidSales: '400万',
            stock: '3570',
            collects: '26',
            sort: '2',
            is_show: 0,
            job: 'Data engineer',
            interest: 'badminton',
            birthday: '1991-05-14',
            book: 'Steve Jobs',
            movie: 'The Prestige'
          },
          {
            id: '666',
            image: 'https://dev-file.iviewui.com/OYZqqiP1bbwN22Ai2HnwvSagxuSNchdD/middle',
            name: 'CRMEB商城系统V3.0将在8月与大家见面',
            sales: '357万',
            voidSales: '400万',
            stock: '3570',
            collects: '26',
            sort: '2',
            is_show: 1,
            job: 'Data engineer',
            interest: 'badminton',
            birthday: '1991-05-14',
            book: 'Steve Jobs',
            movie: 'The Prestige'
          }
        ]
        this.loading = false
        this.data = data
        this.total = data.length
      }, 1000)
    },
    change (status) {
      console.log(status)
    },
    // 数据导出；
    exportData: function () {
      this.$refs.table.exportCsv({
        filename: '商品列表'
      })
    }
  }
}
</script>
