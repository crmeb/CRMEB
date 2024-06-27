<template>
  <div>
    <el-card :bordered="false" shadow="never" class="ivu-mt" :body-style="{ padding: 0 }">
      <div class="padding-add">
        <el-form
          :model="gradeFrom"
          inline
          :label-width="labelWidth"
          :label-position="labelPosition"
          @submit.native.prevent
        >
          <el-form-item label="批次名称：" label-for="title">
            <el-input clearable v-model="gradeFrom.title" placeholder="请输入批次名称" class="form_content_width" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" v-db-click @click="userSearchs">查询</el-button>
          </el-form-item>
        </el-form>
      </div>
    </el-card>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-button type="primary" v-db-click @click="addBatch">添加批次</el-button>
      <el-button v-db-click @click="getMemberScan">卡密使用页面二维码</el-button>
      <el-table
        class="mt14"
        :data="tbody"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="编号" width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.id }}</span>
          </template>
        </el-table-column>
        <el-table-column label="批次名称" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="体验天数" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.use_day }}</span>
          </template>
        </el-table-column>
        <el-table-column label="发卡总数量" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.total_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="使用数量" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.use_num }}</span>
          </template>
        </el-table-column>
        <el-table-column label="制卡时间" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="是否激活" min-width="100">
          <template slot-scope="scope">
            <el-switch
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              :value="scope.row.status"
              @change="onchangeIsShow(scope.row)"
              size="large"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="备注" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.remark }}</span>
          </template>
        </el-table-column>
        <el-table-column label="制卡时间" min-width="100">
          <template slot-scope="scope">
            <span>{{ scope.row.add_time }}</span>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="120">
          <template slot-scope="scope">
            <el-dropdown size="small" @command="changeMenu(scope.row, $event, scope.$index)" :transfer="true">
              <span class="el-dropdown-link">更多<i class="el-icon-arrow-down el-icon--right"></i> </span>
              <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="1">编辑批次名</el-dropdown-item>
                <el-dropdown-item command="2">查看卡列表</el-dropdown-item>
                <el-dropdown-item command="3">导出</el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination
          v-if="total"
          :total="total"
          :page.sync="gradeFrom.page"
          :limit.sync="gradeFrom.limit"
          @pagination="getMemberBatch"
        />
      </div>
    </el-card>
    <el-dialog :visible.sync="modal" width="540px" :title="`${formValidate.id ? '编辑' : '添加'}批次`">
      <!-- <form-create v-model="fapi" :rule="rule" @submit="onSubmit"></form-create> -->
      <el-form ref="formValidate" :model="formValidate" label-width="80px" @submit.native.prevent>
        <el-form-item label="批次名称：">
          <el-input placeholder="请输入批次名称" element-id="unit_name" v-model="formValidate.title" class="w100" />
        </el-form-item>
        <el-form-item label="备注：" v-if="formValidate.id">
          <el-input type="textarea" placeholder="请输入备注" v-model="formValidate.remark" class="w100" />
        </el-form-item>
        <template v-if="!formValidate.id">
          <el-form-item label="制卡数量：">
            <el-input-number
              :controls="false"
              placeholder="请输入制卡数量"
              element-id="sort"
              :precision="0"
              :max="100000"
              :min="1"
              v-model="formValidate.total_num"
              class="perW10"
            />
          </el-form-item>
          <el-form-item label="体验天数：">
            <el-input-number
              :controls="false"
              placeholder="请输入体验天数"
              element-id="sort"
              :precision="0"
              :max="100000"
              :min="1"
              v-model="formValidate.use_day"
              class="perW10"
            />
          </el-form-item>
          <el-form-item label="是否激活：">
            <el-radio-group element-id="status" v-model="formValidate.status">
              <el-radio :label="1" class="radio">激活</el-radio>
              <el-radio :label="0">冻结</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="备注：">
            <el-input type="textarea" placeholder="请输入备注" v-model="formValidate.remark" class="w100" />
          </el-form-item>
        </template>
      </el-form>
      <div class="acea-row row-right">
        <el-button v-db-click @click="modal = false">取消</el-button>
        <el-button type="primary" v-db-click @click="onSubmit()">提交</el-button>
      </div>
    </el-dialog>
    <el-dialog :visible.sync="cardModal" title="卡列表" width="1000px">
      <cardList v-if="cardModal" :id="id"></cardList>
    </el-dialog>
    <el-dialog :visible.sync="modal3" title="二维码" width="540px">
      <div v-if="qrcode" class="acea-row row-around">
        <div v-if="qrcode && qrcode.wechat_img" class="acea-row row-column-around row-between-wrapper">
          <div v-viewer class="QRpic">
            <img v-lazy="qrcode.wechat_img" />
          </div>
          <span class="mt10">公众号二维码</span>
        </div>
        <div v-if="qrcode && qrcode.routine" class="acea-row row-column-around row-between-wrapper">
          <div v-viewer class="QRpic">
            <img v-lazy="qrcode.routine" />
          </div>
          <span class="mt10">小程序二维码</span>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import cardList from './list.vue';
import { userMemberBatch, memberBatchSave, memberBatchSetValue, exportMemberCard, userMemberScan } from '@/api/user';
import { exportmberCardList } from '@/api/export.js';

export default {
  name: 'index',
  components: { cardList },
  data() {
    return {
      cardModal: false,
      id: 0,
      grid: {
        xl: 7,
        lg: 7,
        md: 12,
        sm: 24,
        xs: 24,
      },
      tbody: [],
      total: 0,
      gradeFrom: {
        title: '',
        page: 1,
        limit: 15,
      },
      loading: false,
      modal: false,

      formValidate: {
        id: 0,
        title: '',
        total_num: 1,
        use_day: 1,
        status: 1,
        remark: '',
      },
      modal2: false,
      modal3: false,
      qrcode: null,
      fapi: {},
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
  created() {
    this.getMemberBatch(this.gradeFrom);
  },
  methods: {
    // 批次列表
    getMemberBatch() {
      this.loading = true;
      userMemberBatch(this.gradeFrom)
        .then((res) => {
          this.loading = false;
          this.tbody = res.data.list;
          this.total = res.data.count;
        })
        .catch((err) => {
          this.loading = false;
          this.$message.error(err.msg);
        });
    },
    // 批次名称查询
    userSearchs() {
      this.gradeFrom.page = 1;
      this.getMemberBatch();
    },
    // 激活 | 冻结
    onchangeIsShow(row) {
      memberBatchSetValue(row.id, {
        field: 'status',
        value: row.status,
      })
        .then((res) => {
          this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    // 导出
    async export(row) {
      let [th, filekey, data, fileName] = [[], [], [], ''];
      let lebData = await this.getExcelData(row.id);
      if (!fileName) fileName = lebData.filename;
      if (!filekey.length) {
        filekey = lebData.fileKey;
      }
      if (!th.length) th = lebData.header;
      data = data.concat(lebData.export);
      this.$exportExcel(th, filekey, fileName, data);
    },
    getExcelData(excelData) {
      return new Promise((resolve, reject) => {
        exportmberCardList(excelData).then((res) => {
          resolve(res.data);
        });
      });
    },
    // 更多
    changeMenu(row, name) {
      switch (name) {
        case '1':
          this.formValidate.id = row.id;
          this.formValidate.title = row.title;
          this.modal = true;
          break;
        case '2':
          this.id = row.id;
          this.cardModal = true;
          break;
        case '3':
          this.export(row);
          break;
      }
    },
    // 添加批次弹窗
    addBatch() {
      // this.fapi.resetFields();
      this.modal = true;
      this.formValidate.id = 0;
      this.formValidate.title = '';
    },
    // 提交批次
    onSubmit() {
      if (this.formValidate.id) {
        memberBatchSetValue(this.formValidate.id, {
          field: 'title',
          value: this.formValidate.title,
          remark: this.formValidate.remark,
        })
          .then((res) => {
            this.modal = false;
            this.$message.success(res.msg);
            this.getMemberBatch();
          })
          .catch((err) => {
            this.$message.error(err.msg);
          });
      } else {
        memberBatchSave(this.formValidate.id, this.formValidate)
          .then((res) => {
            this.modal = false;
            this.$message.success(res.msg);
            this.getMemberBatch();
          })
          .catch((err) => {
            this.$message.error(err.msg);
          });
      }
    },
    onSubmit2(formData) {},
    // 会员卡二维码
    getMemberScan() {
      userMemberScan()
        .then((res) => {
          this.qrcode = res.data;
          this.modal3 = true;
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.el-input-number--small {
  width: 100%;
}
.QRpic {
  width: 180px;
  height: 180px;

  img {
    width: 100%;
    height: 100%;
  }
}
.w414 {
  width: 414px;
}
</style>
