<template>
  <div class="article-manager">
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <router-link :to="{ path: '/admin/product/product_list' }" v-if="$route.params.id"
          ><Button icon="ios-arrow-back" size="small" class="mr20">返回</Button></router-link
        >
        <span class="ivu-page-header-title mr20">商品评论管理</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form ref="formValidate" :model="formValidate" :label-width="75" label-position="left" @submit.native.prevent>
        <Row type="flex" :gutter="24">
          <Col span="24">
            <FormItem label="评论时间：">
              <RadioGroup
                v-model="formValidate.data"
                type="button"
                @on-change="selectChange(formValidate.data)"
                class="mr"
              >
                <Radio :label="item.val" v-for="(item, i) in fromList.fromTxt" :key="i">{{ item.text }}</Radio>
              </RadioGroup>
              <DatePicker
                :editable="false"
                @on-change="onchangeTime"
                :value="timeVal"
                format="yyyy/MM/dd"
                type="daterange"
                placement="bottom-end"
                placeholder="请选择时间"
                style="width: 200px"
              ></DatePicker>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="评价状态：">
              <Select v-model="formValidate.is_reply" placeholder="请选择" clearable @on-change="userSearchs">
                <Option value="1">已回复</Option>
                <Option value="0">未回复</Option>
              </Select>
            </FormItem>
          </Col>
          <Col v-bind="grid" v-if="!$route.params.id">
            <FormItem label="商品信息：" label-for="store_name">
              <Input
                size="default"
                enter-button
                placeholder="请输入商品ID或者商品信息"
                clearable
                v-model="formValidate.store_name"
              />
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="用户名称：" label-for="account">
              <Input size="default" enter-button placeholder="请输入" clearable v-model="formValidate.account" />
            </FormItem>
          </Col>
          <Col :xl="3" :lg="3" :md="12" :sm="12" :xs="24" class="search">
            <FormItem>
              <Button type="primary" icon="ios-search" @click="userSearchs">搜索</Button>
            </FormItem>
          </Col>
        </Row>
      </Form>
      <!--            <div class="Button">-->
      <!--                <Button type="primary" class="bnt" icon="md-add">添加评论</Button>-->
      <!--            </div>-->
      <Row type="flex">
        <Col v-bind="grid">
          <Button v-auth="['product-reply-save_fictitious_reply']" type="primary" icon="md-add" @click="add"
            >添加虚拟评论</Button
          >
        </Col>
      </Row>
      <Table
        ref="table"
        :columns="columns"
        :data="tableList"
        class="ivu-mt"
        :loading="loading"
        @on-sort-change="sortMethod"
        no-data-text="暂无数据"
        no-filtered-data-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="info">
          <div class="imgPic acea-row row-middle">
            <div class="pictrue" v-viewer><img v-lazy="row.image" /></div>
            <div class="info">{{ row.store_name }}</div>
          </div>
        </template>
        <template slot-scope="{ row }" slot="content">
          <div class="mb5 content_font">{{ row.comment }}</div>
          <div v-viewer class="pictrue mr10" v-for="(item, index) in row.pics || []" :key="index">
            <img v-lazy="item" :src="item" />
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="reply(row)">回复</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除评论', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="formValidate.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="formValidate.limit"
        />
      </div>
    </Card>
    <Modal v-model="modals" scrollable title="回复内容" closable>
      <Form ref="contents" :model="contents" :rules="ruleInline" label-position="right" @submit.native.prevent>
        <FormItem prop="content">
          <Input v-model="contents.content" type="textarea" :rows="4" placeholder="请输入回复内容" />
        </FormItem>
      </Form>
      <div slot="footer">
        <Button type="primary" @click="oks">确定</Button>
        <Button @click="cancels">取消</Button>
      </div>
    </Modal>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { replyListApi, setReplyApi, fictitiousReply } from '@/api/product';
export default {
  name: 'product_productEvaluate',
  data() {
    return {
      modals: false,
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 12,
        xs: 24,
      },
      formValidate: {
        is_reply: '',
        data: '',
        store_name: '',
        key: '',
        order: '',
        account: '',
        product_id: this.$route.params.id === undefined ? 0 : this.$route.params.id,
        page: 1,
        limit: 15,
      },
      fromList: {
        title: '选择时间',
        custom: true,
        fromTxt: [
          { text: '全部', val: '' },
          { text: '今天', val: 'today' },
          { text: '昨天', val: 'yesterday' },
          { text: '最近7天', val: 'lately7' },
          { text: '最近30天', val: 'lately30' },
          { text: '本月', val: 'month' },
          { text: '本年', val: 'year' },
        ],
      },
      value: '45',
      tableList: [],
      total: 0,
      loading: false,
      columns: [
        {
          title: '评论ID',
          key: 'id',
          width: 80,
        },
        {
          title: '商品信息',
          slot: 'info',
          minWidth: 230,
        },
        {
          title: '用户名称',
          key: 'nickname',
          minWidth: 150,
        },
        {
          title: '评分',
          key: 'score',
          sortable: true,
          minWidth: 90,
        },
        {
          title: '评价内容',
          slot: 'content',
          minWidth: 210,
        },
        {
          title: '回复内容',
          key: 'merchant_reply_content',
          minWidth: 250,
        },
        {
          title: '评价时间',
          key: 'add_time',
          sortable: true,
          minWidth: 150,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 150,
        },
      ],
      timeVal: [],
      contents: {
        content: '',
      },
      ruleInline: {
        content: [{ required: true, message: '请输入回复内容', trigger: 'blur' }],
      },
      rows: {},
    };
  },
  computed: {},
  created() {
    if (this.$route.query.is_reply == 0) this.formValidate.is_reply = this.$route.query.is_reply;
    this.getList();
  },
  watch: {
    '$route.params.id'(to, from) {
      this.formValidate.product_id = 0;
      this.getList();
    },
  },
  methods: {
    // 添加虚拟评论；
    add() {
      this.$modalForm(fictitiousReply(this.formValidate.product_id)).then(() => this.getList());
    },
    oks() {
      this.modals = true;
      this.$refs['contents'].validate((valid) => {
        if (valid) {
          setReplyApi(this.contents, this.rows.id)
            .then(async (res) => {
              this.$Message.success(res.msg);
              this.modals = false;
              this.$refs['contents'].resetFields();
              this.getList();
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
        } else {
          return false;
        }
      });
    },
    cancels() {
      this.modals = false;
      this.$refs['contents'].resetFields();
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `product/reply/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tableList.splice(num, 1);
          this.total = this.total - 1;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 回复
    reply(row) {
      this.modals = true;
      this.rows = row;
    },
    // 具体日期
    onchangeTime(e) {
      this.timeVal = e;
      this.formValidate.data = this.timeVal[0] ? this.timeVal.join('-') : '';
      this.formValidate.page = 1;
      this.getList();
    },
    sortMethod(a) {
      if (a.order === 'normal') {
        this.formValidate.key = '';
        this.formValidate.order = '';
      } else {
        this.formValidate.key = a.key;
        this.formValidate.order = a.order;
      }
      this.getList();
    },
    // 选择时间
    selectChange(tab) {
      this.formValidate.data = tab;
      this.timeVal = [];
      this.formValidate.page = 1;
      this.getList();
    },
    // 列表
    getList() {
      this.loading = true;
      this.formValidate.is_reply = this.formValidate.is_reply || '';
      this.formValidate.store_name = this.formValidate.store_name || '';
      replyListApi(this.formValidate)
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
    },
    pageChange(index) {
      this.formValidate.page = index;
      this.getList();
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    search() {},
  },
};
</script>
<style scoped lang="stylus">
.content_font {
  color: #2b85e4;
}

.search {
  >>> .ivu-form-item-content {
    margin-left: 0 !important;
  }
}

.ivu-mt .Button .bnt {
  margin-right: 6px;
}

.ivu-mt .ivu-table-row {
  font-size: 12px;
  color: rgba(0, 0, 0, 0.65);
}

.ivu-mt >>> .ivu-table-cell {
  padding: 10px 0 !important;
}

.pictrue {
  width: 36px;
  height: 36px;
  display: inline-block;
  cursor: pointer;
}

.pictrue img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
}

.ivu-mt .imgPic .info {
  width: 60%;
  margin-left: 10px;
}

.ivu-mt .picList .pictrue {
  height: 36px;
  margin: 7px 3px 0 3px;
}

.ivu-mt .picList .pictrue img {
  height: 100%;
  display: block;
}
</style>
