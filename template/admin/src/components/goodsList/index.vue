<template>
  <div class="goodList">
    <Form ref="formValidate" :model="formValidate" :label-width="120" label-position="right" class="tabform">
      <Row type="flex" :gutter="24">
        <Col v-bind="grid" v-if="!liveStatus">
          <FormItem label="商品分类：" label-for="pid">
            <Select v-model="formValidate.cate_id" style="width: 200px" clearable @on-change="userSearchs">
              <Option v-for="item in treeSelect" :value="item.id" :key="item.id"
                >{{ item.html + item.cate_name }}
              </Option>
            </Select>
          </FormItem>
        </Col>
        <Col v-bind="grid" v-if="!type && diy">
          <FormItem label="商品类型：" label-for="pid">
            <Select v-model="goodType" style="width: 200px" clearable @on-change="userSearchs">
              <Option v-for="item in goodList" :value="item.activeValue" :key="item.activeValue"
                >{{ item.title }}
              </Option>
            </Select>
          </FormItem>
        </Col>
        <Col v-bind="grid">
          <FormItem label="商品搜索：" label-for="store_name">
            <Input
              search
              enter-button
              placeholder="请输入商品名称/关键字/编号"
              v-model="formValidate.store_name"
              style="width: 250px"
              @on-search="userSearchs"
            />
          </FormItem>
        </Col>
      </Row>
    </Form>
    <Table
      ref="table"
      no-data-text="暂无数据"
      @on-selection-change="changeCheckbox"
      no-filtered-data-text="暂无筛选结果"
      max-height="400"
      :columns="liveStatus == false ? columns4 : columns5"
      :data="tableList"
      :loading="loading"
    >
      <template slot-scope="{ row, index }" slot="image">
        <div class="tabBox_img" v-viewer>
          <img v-lazy="row.image" />
        </div>
      </template>
    </Table>
    <div class="acea-row row-right page">
      <Page :total="total" show-elevator show-total @on-change="pageChange" :page-size="formValidate.limit" />
    </div>
    <div class="footer" slot="footer" v-if="many === 'many' && !diy">
      <Button type="primary" size="large" :loading="modal_loading" long @click="ok">提交</Button>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { treeListApi, changeListApi } from '@/api/product';
import { liveGoods } from '@/api/live';
import { getProductList } from '@/api/diy';
export default {
  name: 'index',
  props: {
    is_new: {
      type: String,
      default: '',
    },
    type: {
      type: Number,
      default: 0,
    },
    diy: {
      type: Boolean,
      default: false,
    },
    ischeckbox: {
      type: Boolean,
      default: false,
    },
    liveStatus: {
      type: Boolean,
      default: false,
    },
    isLive: {
      type: Boolean,
      default: false,
    },
    isdiy: {
      type: Boolean,
      default: false,
    },
    datas: {
      type: Object,
      default: function () {
        return {};
      },
    },
  },
  data() {
    return {
      modal_loading: false,
      treeSelect: [],
      formValidate: {
        page: 1,
        limit: 15,
        cate_id: '',
        store_name: '',
        is_new: this.is_new,
      },
      total: 0,
      modals: false,
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
      columns4: [
        {
          title: '商品ID',
          key: 'id',
        },
        {
          title: '图片',
          slot: 'image',
        },
        {
          title: '商品名称',
          key: 'store_name',
          minWidth: 250,
        },
        {
          title: '商品分类',
          key: 'cate_name',
          minWidth: 150,
        },
      ],
      columns5: [
        {
          title: '商品ID',
          key: 'id',
        },
        {
          title: '图片',
          slot: 'image',
        },
        {
          title: '商品名称',
          key: 'name',
          minWidth: 250,
        },
      ],
      images: [],
      many: '',
      goodType: '',
      goodList: [
        {
          activeValue: 0,
          title: '商品列表',
        },
        {
          activeValue: '4',
          title: '热门榜单',
        },
        {
          activeValue: '5',
          title: '首发新品',
        },
        {
          activeValue: '6',
          title: '促销单品',
        },
        {
          activeValue: '7',
          title: '精品推荐',
        },
      ],
    };
  },
  computed: {},
  created() {
    let radio = {
      width: 60,
      align: 'center',
      render: (h, params) => {
        let id = params.row.id;
        let flag = false;
        if (this.currentid === id) {
          flag = true;
        } else {
          flag = false;
        }
        let self = this;
        return h('div', [
          h('Radio', {
            props: {
              value: flag,
            },
            on: {
              'on-change': () => {
                self.currentid = id;
                this.productRow = params.row;
                this.$emit('getProductId', this.productRow);
                if (this.productRow.id) {
                  if (this.$route.query.fodder === 'image') {
                    /* eslint-disable */
                    let imageObject = {
                      image: this.productRow.image,
                      product_id: this.productRow.id,
                    };
                    form_create_helper.set('image', imageObject);
                    form_create_helper.close('image');
                  }
                } else {
                  this.$Message.warning('请先选择商品');
                }
              },
            },
          }),
        ]);
      },
    };

    let checkbox = {
      type: 'selection',
      width: 60,
      align: 'center',
    };
    let many = '';
    if (this.ischeckbox) {
      many = 'many';
    } else {
      many = this.$route.query.type;
    }
    this.many = many;
    if (many === 'many') {
      this.columns4.unshift(checkbox);
      this.columns5.unshift(checkbox);
    } else {
      this.columns4.unshift(radio);
      this.columns5.unshift(radio);
    }
  },
  mounted() {
    this.goodsCategory();
    if (this.diy) {
      this.productList();
    } else {
      this.getList();
    }
  },
  methods: {
    productList() {
      let data = {
        page: this.formValidate.page,
        limit: this.formValidate.limit,
        cate_id: this.formValidate.cate_id,
        store_name: this.formValidate.store_name,
        type: this.type ? this.type : this.goodType,
      };
      this.loading = true;
      getProductList(data)
        .then((res) => {
          let data = res.data;
          this.tableList = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    changeCheckbox(selection) {
      let images = [];
      selection.forEach(function (item) {
        let imageObject = {
          image: item.image,
          product_id: item.id,
          store_name: item.store_name,
          temp_id: item.temp_id,
        };
        images.push(imageObject);
      });
      this.images = images;
      this.diyVal = selection;
      this.$emit('getProductDiy', selection);
    },
    // 商品分类；
    goodsCategory() {
      treeListApi(1)
        .then((res) => {
          this.treeSelect = res.data;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.formValidate.page = index;
      if (this.diy) {
        this.productList();
      } else {
        this.getList();
      }
    },
    // 列表
    getList() {
      this.loading = true;
      if (!this.liveStatus) {
        if (this.isLive) {
          this.formValidate.is_live = 1;
        }
        changeListApi(this.formValidate)
          .then(async (res) => {
            let data = res.data;
            this.tableList = data.list;
            this.total = res.data.count;
            this.loading = false;
          })
          .catch((res) => {
            this.loading = false;
            this.$Message.error(res.msg);
          });
      } else {
        liveGoods({
          is_show: '1',
          status: '1',
          live_id: this.datas.id,
          kerword: this.formValidate.store_name,
          page: this.formValidate.page,
          limit: this.formValidate.limit,
        })
          .then(async (res) => {
            let data = res.data;
            data.list.forEach((el) => {
              el.image = el.cover_img;
            });
            this.tableList = data.list;
            this.total = res.data.count;
            this.loading = false;
          })
          .catch((res) => {
            this.loading = false;
            this.$Message.error(res.msg);
          });
      }
    },
    ok() {
      if (this.images.length > 0) {
        if (this.$route.query.fodder === 'image') {
          let imageValue = form_create_helper.get('image');
          form_create_helper.set('image', imageValue.concat(this.images));
          form_create_helper.close('image');
        } else {
          if (this.isdiy) {
            this.$emit('getProductId', this.diyVal);
          } else {
            this.$emit('getProductId', this.images);
          }
        }
      } else {
        this.$Message.warning('请先选择商品');
      }
    },
    // 表格搜索
    userSearchs() {
      this.currentid = 0;
      this.productRow = {};
      this.formValidate.page = 1;
      if (this.diy) {
        this.productList();
      } else {
        this.getList();
      }
    },
    clear() {
      this.productRow.id = '';
      this.currentid = '';
    },
  },
};
</script>

<style scoped lang="stylus">
.footer {
  margin: 15px 0;
}

.tabBox_img {
  width: 36px;
  height: 36px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}

.tabform {
  >>> .ivu-form-item {
    margin-bottom: 16px !important;
  }
}

.btn {
  margin-top: 20px;
  float: right;
}

.goodList {
  >>> table {
    width: 100% !important;
  }
}
</style>
