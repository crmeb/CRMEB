<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Row type="flex">
        <Col v-bind="grid">
          <Button type="primary" icon="md-add" @click="add">添加语言</Button>
        </Col>
      </Row>
      <Table
        :columns="columns"
        :data="list"
        ref="table"
        class="mt25"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row, index }" slot="icons">
          <div class="tabBox_img" v-viewer>
            <img v-lazy="row.icon" />
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="language_name">
          <div class="acea-row row-middle">
            <span>{{ row.language_name }}</span>
            <Tag class="ml10" color="default" v-if="row.is_default">默认</Tag>
          </div>
        </template>
        <template slot-scope="{ row, index }" slot="status">
          <i-switch
            v-model="row.status"
            :value="row.status"
            :true-value="1"
            :false-value="0"
            @on-change="changeSwitch(row)"
            size="large"
          >
            <span slot="open">开启</span>
            <span slot="close">关闭</span>
          </i-switch>
        </template>
        <template slot-scope="{ row, index }" slot="action">
          <a @click="edit(row, '编辑语言', index)">编辑</a>
          <Divider type="vertical" />
          <a @click="del(row, '删除语言', index)">删除</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page :total="total" show-elevator show-total @on-change="pageChange" :page-size="langFrom.limit" />
      </div>
    </Card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { langTypeList, langTypeForm, langTypeStatus } from '@/api/setting';
export default {
  name: 'user_group',
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
      columns: [
        {
          title: 'ID',
          key: 'id',
          width: 200,
        },
        {
          title: '语言名称',
          slot: 'language_name',
          minWidth: 200,
        },
        {
          title: '浏览器语言识别码',
          key: 'file_name',
          minWidth: 200,
        },
        {
          title: '状态',
          slot: 'status',
          width: 100,
          filters: [
            {
              label: '开启',
              value: 1,
            },
            {
              label: '关闭',
              value: 0,
            },
          ],
          filterMethod(value, row) {
            return row.status === value;
          },
          filterMultiple: false,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
        },
      ],
      langFrom: {
        page: 1,
        limit: 15,
      },
      list: [],
      total: 0,
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 75;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'left';
    },
  },
  created() {
    this.getList();
  },
  methods: {
    // 添加
    add() {
      this.$modalForm(langTypeForm(0)).then(() => this.getList());
    },
    // 分组列表
    getList() {
      this.loading = true;
      langTypeList(this.langFrom)
        .then(async (res) => {
          let data = res.data;
          this.list = data.list;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.langFrom.page = index;
      this.getList();
    },
    // 编辑
    edit(row) {
      this.$modalForm(langTypeForm(row.id)).then(() => this.getList());
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `setting/lang_type/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.list.splice(num, 1);
          this.getList();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 修改状态
    changeSwitch(row) {
      langTypeStatus(row.id, row.status)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((res) => {
          row.status = !row.status ? 1 : 0;
          this.$Message.error(res.msg);
        });
    },
  },
};
</script>

<style scoped lang="stylus"></style>
