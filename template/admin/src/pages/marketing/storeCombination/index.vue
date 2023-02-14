<template>
  <div>
    <div class="i-layout-page-header">
      <div class="i-layout-page-header">
        <span class="ivu-page-header-title">{{ $route.meta.title }}</span>
      </div>
    </div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col v-bind="grid">
            <FormItem label="拼团状态：" clearable>
              <Select v-model="formValidate.is_show" placeholder="请选择" clearable @on-change="userSearchs">
                <Option :value="1">开启</Option>
                <Option :value="0">关闭</Option>
              </Select>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="商品搜索：" prop="store_name" label-for="store_name">
              <Input
                search
                enter-button
                placeholder="请输入请输入商品名称/ID"
                v-model="formValidate.store_name"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
        <Row type="flex">
          <Button
            v-auth="['marketing-store_combination-create']"
            type="primary"
            class="bnt mr15"
            icon="md-add"
            @click="add"
            >添加拼团商品</Button
          >
          <Button v-auth="['export-storeCombination']" class="export" icon="ios-share-outline" @click="exports"
            >导出</Button
          >
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="tableList"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="is_fail">
          <Icon type="md-checkmark" v-if="row.is_fail === 1" color="#0092DC" size="14" />
          <Icon type="md-close" v-else color="#ed5565" size="14" />
        </template>
        <template slot-scope="{ row, index }" slot="image">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.image" />
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="stop_time">
          <span> {{ row.stop_time | formatDate }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="is_show">
          <i-switch
            v-model="row.is_show"
            :value="row.is_show"
            :true-value="1"
            :false-value="0"
            :disabled="row.stop_status ? true : false"
            @on-change="onchangeIsShow(row)"
            size="large"
          >
            <span slot="open">开启</span>
            <span slot="close">关闭</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="start_name">
          <Tag color="blue" v-show="row.start_name === '进行中'">进行中</Tag>
          <Tag color="volcano" v-show="row.start_name === '未开始'">未开始</Tag>
          <Tag color="cyan" v-show="row.start_name === '已结束'">已结束</Tag>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a v-if="row.stop_status === 0" @click="edit(row)">编辑</a>
          <Divider v-if="row.stop_status === 0" type="vertical" />
          <a @click="copy(row)">复制</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除拼团商品', index)">删除</a>
          <Divider type="vertical" />
          <a @click="viewInfo(row)">统计</a>
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
  </div>
</template>

<script>
import { combinationListApi, combinationSetStatusApi, storeCombinationApi } from '@/api/marketing';
import { mapState } from 'vuex';
import { formatDate } from '@/utils/validate';
import { exportCombinationList } from '@/api/export.js';

export default {
  name: 'index',
  filters: {
    formatDate(time) {
      if (time !== 0) {
        let date = new Date(time * 1000);
        return formatDate(date, 'yyyy-MM-dd hh:mm');
      }
    },
  },
  data() {
    return {
      loading: false,
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      formValidate: {
        is_show: '',
        store_name: '',
        page: 1,
        limit: 15,
      },
      value: '',
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '拼团图片',
          slot: 'image',
          minWidth: 90,
        },
        {
          title: '拼团名称',
          key: 'title',
          minWidth: 130,
        },
        {
          title: '原价',
          key: 'ot_price',
          minWidth: 100,
        },
        {
          title: '拼团价',
          key: 'price',
          minWidth: 120,
        },
        // {
        //     title: '库存',
        //     key: 'stock',
        //     minWidth: 100
        // },
        {
          title: '拼团人数',
          key: 'count_people',
          minWidth: 100,
        },
        // {
        //     title: '访问人数',
        //     key: 'count_people_browse',
        //     minWidth: 100
        // },
        // {
        //     title: '展现量',
        //     key: 'browse',
        //     minWidth: 150
        // },
        {
          title: '参与人数',
          key: 'count_people_all',
          minWidth: 100,
        },
        {
          title: '成团数量',
          key: 'count_people_pink',
          minWidth: 100,
        },
        // {
        //     title: '浏览量',
        //     key: 'browse',
        //     minWidth: 150
        // },
        {
          title: '限量',
          key: 'quota_show',
          minWidth: 100,
        },
        {
          title: '限量剩余',
          key: 'quota',
          minWidth: 100,
        },
        {
          title: '活动状态',
          slot: 'start_name',
          minWidth: 100,
        },
        {
          title: '结束时间',
          slot: 'stop_time',
          minWidth: 150,
        },

        {
          title: '上架状态',
          slot: 'is_show',
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 170,
        },
      ],
      tableList: [],
      total: 0,
      statisticsList: [],
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 80;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  activated() {
    this.getList();
  },
  methods: {
    // 导出
    async exports() {
      let [th, filekey, data, fileName] = [[], [], [], ''];
      let excelData = JSON.parse(JSON.stringify(this.formValidate));
      excelData.page = 1;
      excelData.limit = 200;
      for (let i = 0; i < excelData.page + 1; i++) {
        let lebData = await this.getExcelData(excelData);
        if (!fileName) fileName = lebData.filename;
        if (!filekey.length) {
          filekey = lebData.fileKey;
        }
        if (!th.length) th = lebData.header;
        if (lebData.export.length) {
          data = data.concat(lebData.export);
          excelData.page++;
        } else {
          this.$exportExcel(th, filekey, fileName, data);
          return;
        }
      }
    },
    getExcelData(excelData) {
      return new Promise((resolve, reject) => {
        exportCombinationList(excelData).then((res) => {
          resolve(res.data);
        });
      });
    },

    // 添加
    add() {
      this.$router.push({ path: '/admin/marketing/store_combination/create' });
    },
    // 编辑
    edit(row) {
      this.$router.push({
        path: '/admin/marketing/store_combination/create/' + row.id + '/0',
      });
    },
    // 一键复制
    copy(row) {
      this.$router.push({
        path: '/admin/marketing/store_combination/create/' + row.id + '/1',
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/combination/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.tableList.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    viewInfo(row) {
      this.$router.push({
        path: '/admin/marketing/store_combination/statistics/' + row.id,
      });
    },
    // 列表
    getList() {
      this.loading = true;
      // this.formValidate.is_show = this.formValidate.is_show
      combinationListApi(this.formValidate)
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
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.is_show,
      };
      combinationSetStatusApi(data)
        .then(async (res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus">
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
