<template>
  <div class="goodList">
    <el-form ref="formValidate" :model="formValidate" label-width="80px" label-position="right" inline class="tabform">
      <el-form-item label="商品分类：" v-if="!liveStatus">
        <el-cascader
          v-model="formValidate.cate_id"
          size="small"
          :options="treeSelect"
          :props="{ checkStrictly: true, emitPath: false, multiple: true }"
          clearable
          class="form_content_width"
        ></el-cascader>
      </el-form-item>
      <el-form-item label="商品类型：" v-if="!type && diy">
        <el-select v-model="goodType" clearable @change="userSearchs" class="form_content_width">
          <el-option v-for="item in goodList" :value="item.activeValue" :key="item.activeValue" :label="item.title">
          </el-option>
        </el-select>
      </el-form-item>
      <el-form-item label="商品搜索：">
        <el-input
          clearable
          placeholder="请输入商品名称/关键字/ID"
          v-model="formValidate.store_name"
          class="form_content_width"
        />
        <el-button type="primary" v-db-click @click="userSearchs" class="ml15">查询</el-button>
      </el-form-item>
    </el-form>
    <el-table
      ref="table"
      empty-text="暂无数据"
      max-height="400"
      :highlight-current-row="many !== 'many'"
      :data="tableList"
      v-loading="loading"
      @select="changeCheckbox"
      @select-all="changeCheckbox"
    >
      <el-table-column v-if="many == 'many'" type="selection" width="55"> </el-table-column>
      <el-table-column v-else width="50">
        <template slot-scope="scope">
          <el-radio v-model="templateRadio" :label="scope.row.id" @change.native="getTemplateRow(scope.row)"
            >&nbsp;</el-radio
          >
        </template>
      </el-table-column>

      <el-table-column label="商品ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="图片" width="80">
        <template slot-scope="scope">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="scope.row.image" />
          </div>
        </template>
      </el-table-column>
      <el-table-column label="商品名称" min-width="250">
        <template slot-scope="scope">
          <span>{{ scope.row.store_name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="商品分类" min-width="150" v-if="liveStatus">
        <template slot-scope="scope">
          <span>{{ scope.row.cate_name }}</span>
        </template>
      </el-table-column>
    </el-table>
    <div class="acea-row row-right page">
      <pagination
        v-if="total"
        :total="total"
        :page.sync="formValidate.page"
        :limit.sync="formValidate.limit"
        @pagination="pageChange"
      />
      <el-button type="primary" v-db-click @click="ok" v-if="many === 'many' && !diy" class="ml15">提交</el-button>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { cascaderListApi, changeListApi } from '@/api/product';
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
    selectIds: {
      type: Array,
      default: () => {
        return [];
      },
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
      templateRadio: 0,
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
      images: [],
      many: '',
      goodType: '',
      goodList: [
        {
          activeValue: 0,
          title: '商品列表',
        },
        // {
        //   activeValue: '4',
        //   title: '热门榜单',
        // },
        // {
        //   activeValue: '5',
        //   title: '首发新品',
        // },
        // {
        //   activeValue: '6',
        //   title: '促销单品',
        // },
        {
          activeValue: '7',
          title: '优品推荐',
        },
      ],
    };
  },
  computed: {},
  watch: {
    ischeckbox: {
      handler(newVal, oldVal) {
        if (newVal) {
          this.many = 'many';
        }
      },
      immediate: true,
    },
  },
  created() {
    let many = '';
    if (this.ischeckbox) {
      many = 'many';
    } else {
      many = this.$route.query.type;
    }
    this.many = many;
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
          this.$message.error(res.msg);
        });
    },
    getTemplateRow(row) {
      let images = [];
      let imageObject = {
        image: row.image,
        product_id: row.id,
        store_name: row.store_name,
        temp_id: row.temp_id,
      };
      images.push(imageObject);
      this.images = images;
      this.diyVal = row;
      this.$emit('getProductId', row);
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
      cascaderListApi(1)
        .then((res) => {
          this.treeSelect = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    pageChange() {
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
            this.$nextTick(() => {
              if (this.selectIds.length) {
                let arr = [];
                this.selectIds.map((item) => {
                  data.list.map((i) => {
                    if (i.id == item) {
                      this.$refs.table.toggleRowSelection(i, true);
                      arr.push(i);
                    }
                  });
                });
                this.changeCheckbox(arr);
              }
            });
          })
          .catch((res) => {
            this.loading = false;
            this.$message.error(res.msg);
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
            if (this.selectIds.length) {
              this.selectIds.map((item) => {
                data.list.map((i) => {
                  if (i.id == item) {
                    this.$refs.table.toggleRowSelection(i);
                  }
                });
              });
            }
            this.$nextTick((e) => {
              this.tableList = data.list;
              this.total = res.data.count;
              this.loading = false;
            });
          })
          .catch((res) => {
            this.loading = false;
            this.$message.error(res.msg);
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
          this.$refs.table.clearSelection();
          if (this.isdiy) {
            this.$emit('getProductId', this.diyVal);
          } else {
            this.$emit('getProductId', this.images);
          }
        }
      } else {
        this.$message.warning('请先选择商品');
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

<style lang="scss" scoped>
::v-deep .el-checkbox{
  margin-bottom: 0 !important;
}
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
  ::v-deep .ivu-form-item {
    margin-bottom: 16px !important;
  }
}
.btn {
  margin-top: 20px;
  float: right;
}
.goodList {
  ::v-deeptable {
    width: 100% !important;
  }
}
</style>
