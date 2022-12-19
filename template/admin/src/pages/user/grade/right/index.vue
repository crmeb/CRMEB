<template>
  <div>
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Table
        class="mt25"
        :columns="thead"
        :data="tbody"
        :loading="loading"
        highlight-row
        no-userFrom-text="暂无数据"
        no-filtered-userFrom-text="暂无筛选结果"
      >
        <template slot-scope="{ row }" slot="image">
          <div class="image-wrap" v-viewer>
            <img v-lazy="row.image" />
          </div>
        </template>
        <template slot-scope="{ row }" slot="status">
          <i-switch
            v-model="row.status"
            :value="row.status"
            :true-value="1"
            :false-value="0"
            size="large"
            @on-change="statusChange(row)"
          >
            <span slot="open">启用</span>
            <span slot="close">禁用</span>
          </i-switch>
        </template>
        <template slot-scope="{ row }" slot="action">
          <a @click="edit(row)">编辑</a>
        </template>
      </Table>
      <div class="acea-row row-right page">
        <Page :total="total" :current="page" :page-size="limit" show-elevator show-total @on-change="pageChange" />
      </div>
    </Card>
    <Modal v-model="modal1" title="编辑会员权益" footer-hide :z-index="555">
      <Form ref="form" :model="form" :rules="rules" :label-width="80">
        <Input v-model="form.id" style="display: none"></Input>
        <Input v-model="form.status" style="display: none"></Input>
        <Input v-model="form.right_type" style="display: none"></Input>
        <FormItem label="权益名称" prop="title">
          <Input v-model.trim="form.title" placeholder="请输入权益名称" disabled></Input>
        </FormItem>
        <FormItem label="展示名称" prop="show_title">
          <Input v-model.trim="form.show_title" placeholder="请输入展示名称"></Input>
        </FormItem>
        <FormItem label="权益图标" prop="image">
          <div class="image-group" @click="callImage">
            <img v-if="form.image" v-lazy="form.image" />
            <Icon v-else type="ios-camera-outline" size="26" />
          </div>
          <Input v-model="form.image" style="display: none"></Input>
        </FormItem>
        <FormItem label="权益简介" prop="show_title">
          <Input
            v-model.trim="form.explain"
            type="textarea"
            :autosize="{ minRows: 2, maxRows: 10 }"
            placeholder="请输入权益简介"
          ></Input>
        </FormItem>
        <FormItem
          v-show="form.right_type !== 'coupon' && form.right_type !== 'vip_price'"
          :label="
            form.right_type === 'offline' || form.right_type === 'express' || form.right_type === 'vip_price'
              ? '折扣数(%)'
              : '积分倍数'
          "
          prop="number"
        >
          <InputNumber v-model="form.number" :min="1"></InputNumber>
        </FormItem>
        <FormItem>
          <Button type="primary" @click="formSubmit('form')">提交</Button>
        </FormItem>
      </Form>
    </Modal>
    <Modal v-model="modal2" width="950px" scrollable footer-hide closable title="选择权益图标" :z-index="888">
      <uploadPictures
        v-if="modal2"
        isChoice="单选"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        @getPic="getPic"
      ></uploadPictures>
    </Modal>
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
      thead: [
        {
          title: '权益名称',
          key: 'title',
        },
        {
          title: '展示名称',
          key: 'show_title',
        },
        {
          title: '权益图标',
          slot: 'image',
        },
        {
          title: '权益简介',
          key: 'explain',
        },
        {
          title: '权益状态',
          slot: 'status',
        },
        {
          title: '操作',
          slot: 'action',
        },
      ],
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
          this.$Message.error(err);
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
    // 分页
    pageChange(index) {
      this.page = index;
      this.getRightList();
    },
    // 修改
    rightSave() {
      memberRightSave(this.form)
        .then((res) => {
          this.modal1 = false;
          this.getRightList();
          this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
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

<style lang="less" scoped>
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
</style>
