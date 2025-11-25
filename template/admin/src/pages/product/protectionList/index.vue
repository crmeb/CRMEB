<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="formValidate"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
          inline
        >
          <el-form-item label="保障名称：">
            <el-input clearable placeholder="请输入保障名称" v-model="formValidate.title" class="form_content_width" @change="userSearchs" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never">
      <el-button v-auth="['cms-category-create']" type="primary" v-db-click @click="add">添加保障</el-button>
      <vxe-table
        class="vxeTable mt14"
        highlight-hover-row
        :loading="loading"
        header-row-class-name="false"
        :tree-config="{ children: 'children' }"
        :data="categoryList"
      >
        <vxe-table-column field="id" title="ID" tooltip width="80"></vxe-table-column>
        <vxe-table-column field="title" tree-node title="保障名称" min-width="130"></vxe-table-column>
        <vxe-table-column field="image" title="保障图片" min-width="130">
          <template v-slot="{ row }">
            <div class="tabBox_img" v-viewer v-if="row.image">
              <img v-lazy="row.image" />
            </div>
          </template>
        </vxe-table-column>
        <vxe-table-column field="status" title="状态" min-width="120">
          <template v-slot="{ row }">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="row.status"
              :value="row.status"
              @change="onchangeIsShow(row)"
              size="large"
              active-text="开启"
              inactive-text="关闭"
            >
            </el-switch>
          </template>
        </vxe-table-column>
        <vxe-table-column field="sort" title="排序" min-width="130"></vxe-table-column>

        <vxe-table-column field="date" title="操作" width="120" fixed="right">
          <template v-slot="{ row }">
            <a v-db-click @click="edit(row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(row, '删除保障')">删除</a>
          </template>
        </vxe-table-column>
      </vxe-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="formValidate.page"
          :limit.sync="formValidate.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
  </div>
</template>
<script>
import { mapState, mapMutations } from 'vuex';
import {
  productProtectionFormApi,
  productProtectionInfoApi,
  productProtectionListApi,
  protectionStatusApi,
} from '@/api/product';
export default {
  name: 'articleCategory',
  data() {
    return {
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      loading: false,
      formValidate: {
        page: 1,
        limit: 20,
        title: '',
      },
      status: '',
      total: 0,
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '保障名称',
          key: 'title',
          minWidth: 130,
        },
        {
          title: '保障内容',
          key: 'content',
          minWidth: 130,
        },
        {
          title: '图标',
          slot: 'images',
          minWidth: 130,
        },
        {
          title: '状态',
          slot: 'statuss',
          minWidth: 130,
        },
        {
          title: '排序',
          key: 'sort',
          minWidth: 130,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
        },
      ],
      FromData: null,
      modalTitleSs: '',
      categoryId: 0,
      categoryList: [],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '80px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    this.getList();
  },
  methods: {
    ...mapMutations('userLevel', ['getCategoryId']),
    // 添加
    add() {
      this.$modalForm(productProtectionFormApi(0)).then(() => this.getList());
    },
    // 编辑
    edit(row) {
      this.$modalForm(productProtectionFormApi(row.id)).then(() => this.getList());
    },
    // 删除
    del(row, tit) {
      let delfromData = {
        title: tit,
        num: 0,
        url: `product/protection/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getList();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.status = this.status === 'all' ? '' : this.status;
      productProtectionListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.categoryList = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.status,
      };
      protectionStatusApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 查看保障
    lookUp(row) {
      this.$router.push({
        path: this.$routeProStr + '/cms/article/index',
        query: {
          id: row.id,
        },
      });
      //xia mian chu cun mei yong;
      this.getCategoryId(row.id);
    },
  },
};
</script>

<style lang="scss" scoped>
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
</style>
