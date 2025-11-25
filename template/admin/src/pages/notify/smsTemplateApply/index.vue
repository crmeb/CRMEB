<template>
  <div>
    <div class="i-layout-page-header header_top">
      <div class="i-layout-page-header fl_header">
        <router-link :to="{ path: $routeProStr + '/setting/sms/sms_config/index' }"
          ><el-button size="small" type="text">返回</el-button></router-link
        >
        <el-divider direction="vertical"></el-divider>
        <span class="ivu-page-header-title mr20" style="padding: 0">{{ $route.meta.title }}</span>
      </div>
    </div>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-form
        ref="levelFrom"
        :model="levelFrom"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <el-row :gutter="24" v-if="$route.path === $routeProStr + '/setting/sms/sms_template_apply/index'">
          <!--                    <el-col v-bind="grid">-->
          <!--                        <el-form-item label="模板类型：">-->
          <!--                            <el-select v-model="levelFrom.type" placeholder="请选择" clearable  @change="userSearchs">-->
          <!--                                <el-option value="1">验证码</el-option>-->
          <!--                                <el-option value="2">通知</el-option>-->
          <!--                                <el-option value="3">推广</el-option>-->
          <!--                            </el-select>-->
          <!--                        </el-form-item>-->
          <!--                    </el-col>-->
          <!--                    <el-col v-bind="grid">-->
          <!--                        <el-form-item label="模板状态：">-->
          <!--                            <el-select v-model="levelFrom.status" placeholder="请选择" clearable  @change="userSearchs">-->
          <!--                                <el-option value="1">可用</el-option>-->
          <!--                                <el-option value="0">不可用</el-option>-->
          <!--                            </el-select>-->
          <!--                        </el-form-item>-->
          <!--                    </el-col>-->
          <!--                    <el-col v-bind="grid">-->
          <!--                        <el-form-item label="模板名称：" >-->
          <!--                            <el-input search enter-button  v-model="levelFrom.title" placeholder="请输入模板名称" @on-search="userSearchs"/>-->
          <!--                        </el-form-item>-->
          <!--                    </el-col>-->
          <el-col :span="24">
            <el-button type="primary" v-db-click @click="add">申请模板</el-button>
          </el-col>
        </el-row>
        <el-row :gutter="24" v-else>
          <el-col v-bind="grid">
            <el-form-item label="是否拥有：">
              <el-select v-model="levelFrom.is_have" placeholder="请选择" clearable @change="userSearchs">
                <el-option value="1" label="有"></el-option>
                <el-option value="0" label="没有"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-table
        :data="levelLists"
        ref="table"
        class="mt14"
        v-loading="loading"
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column :label="item.title" :min-width="item.minWidth" v-for="(item, index) in columns" :key="index">
          <template slot-scope="scope">
            <template v-if="item.key">
              <div>
                <span>{{ scope.row[item.key] }}</span>
              </div>
            </template>
            <template v-else-if="item.slot === 'status'">
              <span v-show="scope.row.status === 1">可用</span>
              <span v-show="scope.row.status === 0">不可用</span>
            </template>
            <template
              v-else-if="
                item.slot === 'is_have' && $route.path === $routeProStr + '/setting/sms/sms_template_apply/commons'
              "
            >
              <span v-show="scope.row.status === 1">有</span>
              <span v-show="scope.row.status === 0">没有</span>
            </template>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="levelFrom.page"
          :limit.sync="levelFrom.limit"
          @pagination="getList"
        />
      </div>
    </el-card>

    <!-- 新建表单-->
    <edit-from ref="edits" :FromData="FromData" @submitFail="submitFail"></edit-from>
  </div>
</template>
<script>
import { mapState } from 'vuex';
import { tempListApi, tempCreateApi, isLoginApi, serveInfoApi } from '@/api/setting';
import editFrom from '@/components/from/from';
export default {
  name: 'smsTemplateApply',
  components: { editFrom },
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
      columns1: [],
      levelFrom: {
        type: '',
        status: '',
        title: '',
        page: 1,
        limit: 20,
      },
      levelFrom2: {
        is_have: '',
        page: 1,
        limit: 20,
      },
      total: 0,
      FromData: null,
      delfromData: {},
      levelLists: [],
    };
  },
  watch: {
    $route(to, from) {
      this.getList();
    },
  },
  created() {
    this.onIsLogin();
  },
  mounted() {
    serveInfoApi().then((res) => {
      if (res.data.sms.open != 1) {
        this.$router.push(this.$routeProStr + '/setting/sms/sms_config/index?url=' + this.$route.path);
      }
    });
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
  methods: {
    // 查看是否登录
    onIsLogin() {
      this.spinShow = true;
      isLoginApi()
        .then(async (res) => {
          let data = res.data;
          if (!data.status) {
            this.$message.warning('请先登录');
            this.$router.push(this.$routeProStr + '/setting/sms/sms_config/index?url=' + this.$route.path);
          } else {
            this.getList();
          }
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 等级列表
    getList() {
      this.loading = true;
      this.levelFrom.status = this.levelFrom.status || '';
      this.levelFrom.is_have = this.levelFrom.is_have || '';
      let data = {
        data:
          this.$route.path === this.$routeProStr + '/setting/sms/sms_template_apply/index'
            ? this.levelFrom
            : this.levelFrom2,
        url:
          this.$route.path === this.$routeProStr + '/setting/sms/sms_template_apply/index'
            ? 'serve/sms/temps'
            : 'notify/sms/public_temp',
      };
      let columns1 = [
        {
          title: 'ID',
          key: 'id',
          sortable: true,
          minWidth: 80,
        },
        {
          title: '模板ID',
          key: 'templateid',
          minWidth: 110,
        },
        {
          title: '模板名称',
          key: 'title',
          minWidth: 150,
        },
        {
          title: '模板内容',
          key: 'content',
          minWidth: 550,
        },
        {
          title: '模板类型',
          key: 'type',
          minWidth: 100,
        },
        {
          title: '模板状态',
          slot: 'status',
          minWidth: 100,
        },
      ];
      if (this.$route.path === this.$routeProStr + '/setting/sms/sms_template_apply/commons') {
        this.columns1 = Object.assign([], columns1)
          .slice(0, 6)
          .concat([
            {
              title: '是否拥有',
              slot: 'is_have',
              minWidth: 110,
            },
          ]);
      } else {
        this.columns1 = columns1;
      }
      tempListApi(data)
        .then(async (res) => {
          let data = res.data;
          this.levelLists = data.data;
          this.total = data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 添加
    add() {
      tempCreateApi()
        .then(async (res) => {
          this.FromData = res.data;
          this.$refs.edits.modals = true;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.levelFrom.page = 1;
      this.getList();
    },
    // 修改成功
    submitFail() {
      this.getList();
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
