<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mb-16" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          ref="levelFrom"
          :model="formValidate"
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
          inline
        >
          <el-form-item label="回复类型：" prop="type" label-for="type">
            <el-select
              v-model="formValidate.type"
              placeholder="请选择"
              clearable
              @change="userSearchs"
              class="form_content_width"
            >
              <el-option value="text" label="文字消息"></el-option>
              <el-option value="image" label="图片消息"></el-option>
              <el-option value="news" label="图文消息"></el-option>
              <el-option value="voice" label="声音消息"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="关键字：" prop="key" label-for="key">
            <el-input clearable v-model="formValidate.key" placeholder="请输入关键字" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-button type="primary" v-db-click @click="add">添加关键字</el-button>
      <el-table
        :data="tabList"
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
        <el-table-column label="关键字" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.key }}</span>
          </template>
        </el-table-column>
        <el-table-column label="回复类型" min-width="130">
          <template slot-scope="scope">
            <span>{{ scope.row.type }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否显示" min-width="130">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
              active-text="显示"
              inactive-text="隐藏"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
            <el-divider direction="vertical"></el-divider>
            <a v-db-click @click="del(scope.row, '关键字回复', scope.$index)">删除</a>
          </template>
        </el-table-column>
      </el-table>
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
    <el-dialog :visible.sync="modal" title="二维码">
      <div class="acea-row row-around">
        <div class="acea-row row-column-around row-between-wrapper">
          <div v-viewer class="QRpic">
            <img v-lazy="qrcode" />
          </div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { keywordListApi, keywordsetStatusApi, downloadReplyCode } from '@/api/app';
import { mapState } from 'vuex';
export default {
  name: 'keyword',
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
        key: '',
        type: '',
        page: 1,
        limit: 20,
      },
      tabList: [],
      total: 0,
      columns1: [
        {
          title: 'ID',
          key: 'id',
          width: 80,
        },
        {
          title: '关键字',
          key: 'key',
          minWidth: 120,
        },
        {
          title: '回复类型',
          key: 'type',
          minWidth: 150,
        },
        {
          title: '是否显示',
          slot: 'status',
          minWidth: 120,
        },
        {
          title: '操作',
          slot: 'action',
          fixed: 'right',
          minWidth: 120,
        },
      ],
      modal: false,
      qrcode: '',
    };
  },
  created() {
    this.getList();
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
    // 列表
    getList() {
      this.loading = true;
      keywordListApi(this.formValidate)
        .then(async (res) => {
          let data = res.data;
          this.tabList = data.list;
          this.total = res.data.count;
          this.loading = false;
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
    // 修改是否显示
    onchangeIsShow(row) {
      let data = {
        id: row.id,
        status: row.status,
      };
      keywordsetStatusApi(data)
        .then(async (res) => {
          this.$message.success(res.msg);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 表格搜索
    userSearchs() {
      this.formValidate.page = 1;
      this.getList();
    },
    // 添加
    add() {
      this.$router.push({ path: this.$routeProStr + '/app/wechat/reply/keyword/save/0' });
    },
    // 编辑
    edit(row) {
      this.$router.push({ path: this.$routeProStr + '/app/wechat/reply/keyword/save/' + row.id });
    },
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `app/wechat/keyword/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.tabList.splice(num, 1);
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 下载二维码
    download(row) {
      downloadReplyCode(row.id)
        .then((res) => {
          this.modal = true;
          this.qrcode = res.data.url;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
  },
};
</script>

<style scoped>
.QRpic {
  width: 180px;
  height: 180px;
}

.QRpic img {
  width: 100%;
  height: 100%;
}
</style>
