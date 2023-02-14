<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="tableFrom"
        :model="tableFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col v-bind="grid">
            <FormItem label="砍价状态：">
              <Select placeholder="请选择" v-model="tableFrom.status" clearable @on-change="userSearchs">
                <Option value="1">开启</Option>
                <Option value="0">关闭</Option>
              </Select>
            </FormItem>
          </Col>
          <Col v-bind="grid">
            <FormItem label="商品搜索：" label-for="store_name">
              <Input
                search
                enter-button
                placeholder="请输入商品名称，ID"
                v-model="tableFrom.store_name"
                @on-search="userSearchs"
              />
            </FormItem>
          </Col>
        </Row>
        <Row type="flex" class="mb20">
          <Col v-bind="grid">
            <Button v-auth="['marketing-store_bargain-create']" type="primary" icon="md-add" @click="add" class="mr10"
              >添加砍价商品</Button
            >
            <Button v-auth="['export-storeBargain']" class="export" icon="ios-share-outline" @click="exportList"
              >导出</Button
            >
          </Col>
        </Row>
      </Form>
      <Table
        :columns="columns1"
        :data="tableList"
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
        <template slot-scope="{ row, index }" slot="bargain_min_price">
          <span>{{ row.bargain_min_price }}~{{ row.bargain_max_price }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="start_name">
          <Tag color="blue" v-show="row.start_name === '进行中'">进行中</Tag>
          <Tag color="volcano" v-show="row.start_name === '未开始'">未开始</Tag>
          <Tag color="cyan" v-show="row.start_name === '已结束'">已结束</Tag>
        </template>
        <template slot-scope="{ row, index }" slot="stop_time">
          <span> {{ row.stop_time | formatDate }}</span>
        </template>
        <template slot-scope="{ row, index }" slot="status">
          <i-switch
            v-model="row.status"
            :value="row.status"
            :true-value="1"
            :disabled="row.stop_status ? true : false"
            :false-value="0"
            @on-change="onchangeIsShow(row)"
            size="large"
          >
            <span slot="open">开启</span>
            <span slot="close">关闭</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a v-if="row.stop_status === 0" @click="edit(row)">编辑</a>
          <Divider v-if="row.stop_status === 0" type="vertical" />
          <a @click="copy(row)">复制</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除砍价商品', index)">删除</a>
          <Divider type="vertical" />
          <a @click="viewInfo(row)">统计</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page
          :total="total"
          :current="tableFrom.page"
          show-elevator
          show-total
          @on-change="pageChange"
          :page-size="tableFrom.limit"
        />
      </div>
    </Card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { bargainListApi, bargainSetStatusApi, stroeBargainApi } from '@/api/marketing';
import { formatDate } from '@/utils/validate';
import { exportBargainList } from '@/api/export';
export default {
  name: 'storeBargain',
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
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '砍价图片',
          slot: 'image',
          minWidth: 90,
        },
        {
          title: '砍价名称',
          key: 'title',
          minWidth: 130,
        },
        {
          title: '砍价价格',
          key: 'price',
          minWidth: 100,
        },
        {
          title: '最低价',
          key: 'min_price',
          minWidth: 100,
        },
        {
          title: '参与人数',
          key: 'count_people_all',
          minWidth: 100,
        },
        {
          title: '帮忙砍价人数',
          key: 'count_people_help',
          minWidth: 100,
        },
        {
          title: '砍价成功人数',
          key: 'count_people_success',
          minWidth: 100,
        },
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
          slot: 'status',
          minWidth: 130,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 130,
        },
      ],
      tableList: [],
      grid: {
        xl: 7,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      tableFrom: {
        status: '',
        store_name: '',
        page: 1,
        limit: 15,
      },
      tableFrom2: {
        status: '',
        store_name: '',
        export: 1,
      },
      total: 0,
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
    // 添加
    add() {
      this.$router.push({ path: '/admin/marketing/store_bargain/create/0' });
    },
    // 导出
    // 用户导出
    async exportList() {
      this.tableFrom.status = this.tableFrom.status || '';
      let [th, filekey, data, fileName] = [[], [], [], ''];
      let excelData = JSON.parse(JSON.stringify(this.tableFrom));
      excelData.page = 1;
      excelData.limit = 50;
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
        exportBargainList(excelData).then((res) => {
          resolve(res.data);
        });
      });
    },
    // 编辑
    edit(row) {
      this.$router.push({
        path: '/admin/marketing/store_bargain/create/' + row.id + '/0',
      });
    },
    // 一键复制
    copy(row) {
      this.$router.push({
        path: '/admin/marketing/store_bargain/create/' + row.id + '/1',
      });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `marketing/bargain/${row.id}`,
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
        path: '/admin/marketing/store_bargain/statistics/' + row.id,
      });
    },
    // 列表
    getList() {
      this.loading = true;
      this.tableFrom.status = this.tableFrom.status || '';
      bargainListApi(this.tableFrom)
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
      this.tableFrom.page = index;
      this.getList();
    },
    // 表格搜索
    userSearchs() {
      this.tableFrom.page = 1;
      this.getList();
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.status,
      };
      bargainSetStatusApi(data)
        .then(async (res) => {
          this.$Message.success(res.msg);
          this.getList();
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
