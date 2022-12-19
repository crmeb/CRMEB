<template>
  <div class="goodList">
    <Form ref="formValidate" :model="formValidate" :label-width="120" label-position="right" class="tabform">
      <Row type="flex" :gutter="24">
        <Col v-bind="grid">
          <FormItem label="商品分类：" label-for="pid">
            <Select v-model="formValidate.pid" style="width: 230px" clearable @on-change="userSearchs">
              <Option v-for="item in treeSelect" :value="item.id" :key="item.id">{{ item.cate_name }} </Option>
            </Select>
          </FormItem>
        </Col>
        <Col v-bind="grid">
          <FormItem label="商品搜索：" label-for="store_name">
            <Input
              search
              enter-button
              placeholder="请输入商品分类,id"
              v-model="formValidate.name"
              style="width: 80%"
              @on-search="userSearchs"
            />
          </FormItem>
        </Col>
      </Row>
    </Form>
    <Table
      ref="table"
      no-data-text="暂无数据"
      no-filtered-data-text="暂无筛选结果"
      max-height="400"
      :columns="columns"
      :data="tableList"
      :loading="loading"
      @on-selection-change="selectionGood"
    >
      <template slot-scope="{ row, index }" slot="image">
        <div class="tabBox_img" v-viewer>
          <img v-lazy="row.pic" />
        </div>
      </template>
    </Table>
  </div>
</template>

<script>
import { getByCategory } from '@/api/diy';
export default {
  name: 'index',
  props: {},
  data() {
    return {
      formValidate: {
        pid: 0,
        name: '',
      },
      treeSelect: [],
      loading: false,
      grid: {
        xl: 10,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      tableList: [],
      currentid: 0,
      productRow: {},
      columns: [
        {
          type: 'selection',
          width: 60,
          align: 'center',
        },
        {
          title: '商品ID',
          key: 'id',
        },
        {
          title: '图片',
          slot: 'image',
        },
        {
          title: '商品分类',
          key: 'cate_name',
          minWidth: 150,
        },
      ],
      images: [],
    };
  },
  computed: {},
  created() {},
  mounted() {
    this.goodsCategory();
    this.getList();
  },
  methods: {
    selectionGood(e) {
      this.$emit('getProductDiy', e);
    },
    // 商品一级分类；
    goodsCategory() {
      getByCategory()
        .then((res) => {
          this.treeSelect = res.data;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 列表
    getList() {
      this.loading = true;
      getByCategory(this.formValidate)
        .then(async (res) => {
          this.tableList = res.data;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.getList();
    },
    // clear() {
    //     this.productRow.id = '';
    //     this.currentid = '';
    // }
  },
};
</script>

<style scoped lang="stylus">
.footer
    margin 15px 0;
.tabBox_img
    width 36px
    height 36px
    border-radius: 4px
    cursor pointer
    img
        width 100%
        height 100%

.tabform
    >>> .ivu-form-item
        margin-bottom 16px !important

.btn
    margin-top 20px
    float right
.goodList
    >>> table
        width 100% !important
</style>
