<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="artFrom"
          :model="artFrom"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
          inline
        >
          <el-form-item label="提货点搜索：">
            <el-input
              clearable
              placeholder="请输入提货点名称,电话"
              v-model="artFrom.keywords"
              class="form_content_width"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: '0 20px 20px' }">
      <div v-if="headeNum.show.name">
        <el-tabs v-model="artFrom.type" @tab-click="onClickTab">
          <el-tab-pane :label="headeNum.show.name + '(' + headeNum.show.num + ')'" name="0" />
          <el-tab-pane :label="headeNum.hide.name + '(' + headeNum.hide.num + ')'" name="1" />
          <el-tab-pane :label="headeNum.recycle.name + '(' + headeNum.recycle.num + ')'" name="2" />
        </el-tabs>
      </div>
      <el-row v-auth="['setting-merchant-system_store-save']">
        <el-col v-bind="grid">
          <el-button v-auth="['setting-merchant-system_store-save']" type="primary" v-db-click @click="add"
            >添加提货点</el-button
          >
        </el-col>
      </el-row>
      <el-table
        :data="storeLists"
        ref="table"
        class="mt14"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="ID" width="80">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="提货点图片" min-width="90">
          <template slot-scope="scope">
            <div class="tabBox_img" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="提货点名称" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="提货点电话" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.phone }}</span>
          </template>
        </el-table-column>
        <el-table-column label="地址" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.detailed_address }}</span>
          </template>
        </el-table-column>
        <el-table-column label="营业时间" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.day_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否显示" min-width="130">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.is_show"
              :value="scope.row.is_show"
              @change="onchangeIsShow(scope.row.id, scope.row.is_show)"
              size="large"
              active-text="显示"
              inactive-text="隐藏"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row.id)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-if="scope.row.is_del == 0" v-db-click @click="del(scope.row, '删除提货点', scope.$index)">删除</a>
            <a v-else v-db-click @click="del(scope.row, '恢复提货点', scope.$index)">恢复</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="artFrom.page"
          :limit.sync="artFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>
    <system-store ref="template"></system-store>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import { storeGetHeaderApi, merchantStoreApi, storeSetShowApi } from '@/api/setting';
import systemStore from '@/components/systemStore/index';
export default {
  name: 'setting_store',
  components: { systemStore },
  computed: {
    ...mapState('media', ['isMobile']),
    ...mapState('userLevel', ['categoryId']),
    labelWidth() {
      return this.isMobile ? undefined : '90px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  data() {
    return {
      grid: {
        xl: 10,
        lg: 10,
        md: 12,
        sm: 24,
        xs: 24,
      },
      headeNum: {
        show: { name: '', num: 0 },
        hide: { name: '', num: 0 },
        recycle: { name: '', num: 0 },
      },
      artFrom: {
        page: 1,
        limit: 15,
        type: '0',
        keywords: '',
      },
      loading: false,
      storeLists: [],
      total: 0,
    };
  },
  mounted() {
    this.storeHeade();
    this.getList();
  },
  methods: {
    // 获取表单头部信息；
    storeHeade() {
      let that = this;
      storeGetHeaderApi()
        .then((res) => {
          that.headeNum = res.data.count;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    getList() {
      let that = this;
      that.loading = true;
      merchantStoreApi(that.artFrom)
        .then((res) => {
          that.loading = false;
          that.storeLists = res.data.list;
          that.total = res.data.count;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 搜索；
    userSearchs() {
      this.artFrom.page = 1;
      this.getList();
    },
    // 切换导航；
    onClickTab() {
      this.artFrom.page = 1;
      this.artFrom.keywords = '';
      this.getList();
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `merchant/store/del/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.storeLists.splice(num, 1);
          this.storeHeade();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 添加提货点；
    add() {
      this.$refs.template.isTemplate = true;
    },
    onchangeIsShow(id, is_show) {
      let that = this;
      storeSetShowApi(id, is_show).then((res) => {
        that.$message.success(res.msg);
        that.getList();
        that.storeHeade();
      });
    },
    edit(id) {
      this.$refs.template.isTemplate = true;
      this.$refs.template.getInfo(id);
    },
  },
};
</script>

<style lang="scss" scoped>
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
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
