<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="artFrom"
        :model="artFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col v-bind="grid">
            <FormItem label="文章分类：" label-for="pid">
              <i-select :value="artFrom.pid" placeholder="请选择" style="width: 80%" class="treeSel">
                <i-option v-for="(item, index) of list" :value="item.value" :key="index" style="display: none">
                  {{ item.title }}
                </i-option>
                <Tree :data="treeData" @on-select-change="handleCheckChange"></Tree>
              </i-select>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="文章搜索：" label-for="title">
              <Input
                search
                enter-button
                placeholder="请输入"
                v-model="artFrom.title"
                style="width: 80%"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
        <Row type="flex">
          <Col v-bind="grid">
            <router-link :to="'/admin/cms/article/add_article'" v-auth="['cms-article-creat']"
              ><Button type="primary" class="bnt" icon="md-add">添加文章</Button></router-link
            >
          </Col>
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="cmsList"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="titles">
          <span>{{ ' [ ' + row.catename + ' ] ' + row.title }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="image_inputs">
          <div v-if="row.image_input.length !== 0" v-viewer>
            <div class="tabBox_img" v-for="(item, index) in row.image_input" :key="index">
              <img v-lazy="item" />
            </div>
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row)">编辑</a>
          <Divider type="vertical" />
          <a @click="artRelation(row, '取消关联', index)">{{ row.product_id === 0 ? '关联' : '取消关联' }}</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除文章', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="artFrom.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="artFrom.limit"
        />
      </div>
    </Card>
    <!--关联-->
    <Modal
      v-model="modals"
      title="商品列表"
      footerHide
      class="paymentFooter"
      scrollable
      width="900"
      @on-cancel="cancel"
    >
      <goods-list ref="goodslist" @getProductId="getProductId" v-if="modals"></goods-list>
    </Modal>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { cmsListApi, categoryListApi, relationApi } from '@/api/cms';
import relationList from './relation';
import { formatDate } from '@/utils/validate';
import goodsList from '@/components/goodsList/index';
export default {
  name: 'addArticle',
  data() {
    return {
      modalTitleSs: '',
      currentTab: '',
      grid: {
        xl: 8,
        lg: 8,
        md: 12,
        sm: 24,
        xs: 24,
      },
      loading: false,
      artFrom: {
        pid: 0,
        title: '',
        page: 1,
        limit: 20,
      },
      total: 0,
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '文章图片',
          slot: 'image_inputs',
          minWidth: 90,
        },
        {
          title: '文章名称',
          slot: 'titles',
          minWidth: 130,
        },
        {
          title: '关联商品',
          key: 'store_name',
          minWidth: 130,
        },
        // {
        //     title: '排序',
        //     key: 'sort',
        //     minWidth: 60
        // },
        {
          title: '浏览量',
          key: 'visit',
          minWidth: 80,
        },
        {
          title: '时间',
          key: 'add_time',
          sortable: true,
          render: (h, params) => {
            return h('div', formatDate(new Date(Number(params.row.add_time) * 1000), 'yyyy-MM-dd hh:mm'));
          },
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 150,
        },
      ],
      cmsList: [],
      treeData: [],
      list: [],
      cid: 0, // 移动分类id
      cmsId: 0,
      formValidate: {
        type: 1,
      },
      rows: {},
      modal_loading: false,
      modals: false,
    };
  },
  components: {
    relationList,
    goodsList,
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 75;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  created() {},
  activated() {
    this.artFrom.pid = this.$route.query.id ? this.$route.query.id : 0;
    this.getList();
    this.getClass();
  },
  methods: {
    // 关联成功
    getProductId(row) {
      let data = {
        product_id: row.id,
      };
      relationApi(data, this.rows.id)
        .then(async (res) => {
          this.$Message.success(res.msg);
          row.id = 0;
          this.modal_loading = false;
          this.modals = false;
          setTimeout(() => {
            this.getList();
          }, 500);
        })
        .catch((res) => {
          this.modal_loading = false;
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    cancel() {
      this.modals = false;
    },
    // 等级列表
    getList() {
      this.loading = true;
      cmsListApi(this.artFrom)
        .then(async (res) => {
          let data = res.data;
          this.cmsList = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    // 分类
    getClass() {
      categoryListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.treeData = data;
          let obj = {
            id: 0,
            title: '全部',
          };
          this.treeData.unshift(obj);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.artFrom.page = index;
      this.getList();
    },
    // 下拉树
    handleCheckChange(data) {
      let value = '';
      let title = '';
      this.list = [];
      this.artFrom.pid = 0;
      data.forEach((item, index) => {
        value += `${item.id},`;
        title += `${item.title},`;
      });
      value = value.substring(0, value.length - 1);
      title = title.substring(0, title.length - 1);
      this.list.push({
        value,
        title,
      });
      this.artFrom.pid = value;
      this.artFrom.page = 1;
      this.getList();
    },
    // 编辑
    edit(row) {
      this.$router.push({ path: '/admin/cms/article/add_article/' + row.id });
    },
    // 关联
    artRelation(row, tit, num) {
      this.rows = row;
      if (row.product_id === 0) {
        this.modals = true;
      } else {
        let delfromData = {
          title: tit,
          num: num,
          url: `/cms/cms/unrelation/${row.id}`,
          method: 'PUT',
          ids: '',
        };
        this.$modalSure(delfromData)
          .then((res) => {
            this.$Message.success(res.msg);
            this.getList();
          })
          .catch((res) => {
            this.$Message.error(res.msg);
          });
      }
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `cms/cms/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.cmsList.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.artFrom.page = 1;
      this.getList();
    },
  },
};
</script>

<style scoped lang="stylus">
.treeSel >>>.ivu-select-dropdown-list {
  padding: 0 10px !important;
  box-sizing: border-box;
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
</style>
