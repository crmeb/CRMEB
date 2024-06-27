<template>
  <div>
    <!--    <div class="i-layout-page-header header-title">-->
    <!--      <span class="ivu-page-header-title">{{ $route.meta.title }}</span>-->
    <!--    </div>-->
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <el-table
        :data="tbody"
        v-loading="loading"
        highlight-current-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <el-table-column label="权益名称" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="展示名称" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.show_title }}</span>
          </template>
        </el-table-column>
        <el-table-column label="权益图标" min-width="120">
          <template slot-scope="scope">
            <div class="image-wrap" v-viewer>
              <img v-lazy="scope.row.image" />
            </div>
          </template>
        </el-table-column>
        <el-table-column label="权益简介" min-width="120">
          <template slot-scope="scope">
            <span>{{ scope.row.explain }}</span>
          </template>
        </el-table-column>
        <el-table-column label="权益状态" min-width="120">
          <template slot-scope="scope">
            <el-switch
              class="defineSwitch"
              :active-value="1"
              :inactive-value="0"
              v-model="scope.row.status"
              size="large"
              @change="statusChange(scope.row)"
              active-text="启用"
              inactive-text="禁用"
            >
            </el-switch>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="170">
          <template slot-scope="scope">
            <a v-db-click @click="edit(scope.row)">编辑</a>
          </template>
        </el-table-column>
      </el-table>
      <div class="acea-row row-right page">
        <pagination v-if="total" :total="total" :page.sync="page" :limit.sync="limit" @pagination="getRightList" />
      </div>
    </el-card>
    <el-dialog :visible.sync="modal1" title="编辑会员权益" width="540px">
      <el-form ref="form" :model="form" :rules="rules" label-width="90px">
        <el-input v-model="form.id" style="display: none"></el-input>
        <el-input v-model="form.status" style="display: none"></el-input>
        <el-input v-model="form.right_type" style="display: none"></el-input>
        <el-form-item label="权益名称：" prop="title">
          <el-input v-model.trim="form.title" placeholder="请输入权益名称" disabled class="w100"></el-input>
        </el-form-item>
        <el-form-item label="展示名称：" prop="show_title">
          <el-input v-model.trim="form.show_title" placeholder="请输入展示名称" class="w100"></el-input>
        </el-form-item>
        <el-form-item label="权益图标：" prop="image">
          <div class="image-group" v-db-click @click="callImage">
            <img v-if="form.image" v-lazy="form.image" />
            <i v-else class="el-icon-picture-outline" style="font-size: 24px"></i>
          </div>
          <el-input v-model="form.image" style="display: none"></el-input>
        </el-form-item>
        <el-form-item label="权益简介：" prop="show_title">
          <el-input
            v-model.trim="form.explain"
            type="textarea"
            :autosize="{ minRows: 2, maxRows: 10 }"
            placeholder="请输入权益简介"
            class="w100"
          ></el-input>
        </el-form-item>
        <el-form-item
          v-show="form.right_type !== 'coupon' && form.right_type !== 'vip_price'"
          :label="
            form.right_type === 'offline' || form.right_type === 'express' || form.right_type === 'vip_price'
              ? '折扣数(%)：'
              : '积分倍数：'
          "
          prop="number"
        >
          <el-input-number :controls="false" v-model="form.number" :min="1"></el-input-number>
        </el-form-item>
        <el-form-item>
          <div class="acea-row row-right">
            <el-button type="primary" v-db-click @click="formSubmit('form')">提交</el-button>
          </div>
        </el-form-item>
      </el-form>
    </el-dialog>
    <el-dialog :visible.sync="modal2" width="1024px" title="选择权益图标">
      <uploadPictures
        v-if="modal2"
        isChoice="单选"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        @getPic="getPic"
      ></uploadPictures>
    </el-dialog>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import { memberRight, memberRightSave } from '@/api/user';
import uploadPictures from '@/components/uploadPictures';

export default {
  components: { uploadPictures },
  data() {
    return {
      tbody: [],
      loading: false,
      total: 0,
      page: 1,
      limit: 30,
      modal1: false,
      form: {
        id: '',
        right_type: '',
        title: '',
        show_title: '',
        image: '',
        explain: '',
        number: 1,
        status: 1,
      },
      rules: {
        title: [{ required: true, message: '请输入权益名称', trigger: 'blur' }],
        show_title: [{ required: true, message: '请输入展示名称', trigger: 'blur' }],
        image: [{ required: true, message: '请上传权益图标' }],
        explain: [{ required: true, message: '请输入权益简介', trigger: 'blur' }],
        number: [{ required: true, type: 'integer', message: '请输入正整数' }],
      },
      modal2: false,
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
  },
  created() {
    this.getRightList();
  },
  methods: {
    getRightList() {
      this.loading = true;
      memberRight()
        .then((res) => {
          const { count, list } = res.data;
          this.loading = false;
          this.total = count;
          this.tbody = list;
        })
        .catch((err) => {
          this.loading = false;
          this.$message.error(err);
        });
    },
    // 改变状态
    statusChange(row) {
      this.form.id = row.id;
      this.form.right_type = row.right_type;
      this.form.title = row.title;
      this.form.show_title = row.show_title;
      this.form.image = row.image;
      this.form.explain = row.explain;
      this.form.number = row.number;
      this.form.status = row.status;
      this.rightSave();
    },
    // 编辑
    edit(row) {
      this.modal1 = true;
      this.form.id = row.id;
      this.form.status = row.status;
      this.form.right_type = row.right_type;
      this.form.title = row.title;
      this.form.show_title = row.show_title;
      this.form.image = row.image;
      this.form.explain = row.explain;
      this.form.number = row.number;
    },
    // 修改
    rightSave() {
      memberRightSave(this.form)
        .then((res) => {
          this.modal1 = false;
          this.getRightList();
          this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    formSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          this.rightSave();
        }
      });
    },
    callImage() {
      this.modal2 = true;
    },
    getPic(image) {
      this.form.image = image.att_dir;
      this.modal2 = false;
    },
  },
};
</script>

<style lang="scss" scoped>
.image-wrap {
  width: 36px;
  height: 36px;
  border-radius: 4px;

  img {
    width: 100%;
    height: 100%;
  }
}

.image-group {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  width: 60px;
  height: 60px;
  border: 1px solid #dcdee2;
  border-radius: 4px;

  &:hover {
    border-color: #57a3f3;
  }

  img {
    width: 100%;
    height: 100%;
  }
}
.w414 {
  width: 414px;
}
</style>
