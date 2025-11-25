<template>
  <div>
    <el-dialog
      :visible.sync="isTemplate"
      :title="formItem.id ? '编辑提货点' : '添加提货点'"
      width="720px"
      @closed="cancel"
      append-to-body
    >
      <div class="article-manager" v-loading="spinShow">
        <el-form
          ref="formItem"
          :model="formItem"
          label-width="110px"
          label-position="right"
          :rules="ruleValidate"
          @submit.native.prevent
        >
          <el-row :gutter="24">
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="提货点名称：" prop="name" label-for="name">
                  <el-input v-model="formItem.name" placeholder="请输入提货点名称" />
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="提货点简介：" label-for="introduction">
                  <el-input v-model="formItem.introduction" placeholder="请输入提货点简介" />
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="提货点电话：" label-for="phone" prop="phone">
                  <el-input v-model="formItem.phone" placeholder="请输入提货点电话：" />
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="提货点地址：" label-for="address" prop="address">
                  <el-cascader
                    :options="addresData"
                    v-model="formItem.address"
                    @change="handleChange"
                    style="width: 100%"
                  ></el-cascader>
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="详细地址：" label-for="detailed_address" prop="detailed_address">
                  <el-input v-model="formItem.detailed_address" placeholder="请输入详细地址" />
                </el-form-item>
              </el-col>
            </el-col>
            <!--<el-col :span="24">-->
            <!--<el-col v-bind="grid">-->
            <!--<el-form-item label="核销时效：" label-for="valid_time">-->
            <!--<DatePicker @change="onchangeDate" :value="formItem.valid_time" v-model="formItem.valid_time" format="yyyy/MM/dd" type="daterange" split-panels placeholder="请选择核销时效" ></DatePicker>-->
            <!--</el-form-item>-->
            <!--</el-col>-->
            <!--</el-col>-->
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="提货点营业：" label-for="day_time" prop="day_time">
                  <el-time-picker
                    is-range
                    @change="onchangeTime"
                    v-model="formItem.day_time"
                    format="HH:mm:ss"
                    value-format="HH:mm:ss"
                    range-separator="-"
                    start-placeholder="开始时间"
                    end-placeholder="结束时间"
                    placeholder="选择时间范围"
                    style="width: 100%"
                  ></el-time-picker>
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="提货点logo：" prop="image">
                  <div class="picBox" v-db-click @click="modalPicTap('单选', 'logo')">
                    <div class="pictrue" v-if="formItem.image">
                      <img v-lazy="formItem.image" />
                    </div>
                    <div class="upLoad acea-row row-center-wrapper" v-else>
                      <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                    </div>
                  </div>
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="提货点大图：" prop="oblong_image">
                  <div class="picBox" v-db-click @click="modalPicTap('单选', 'oblong')">
                    <div class="pictrue" v-if="formItem.oblong_image">
                      <img v-lazy="formItem.oblong_image" />
                    </div>
                    <div class="upLoad acea-row row-center-wrapper" v-else>
                      <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                    </div>
                  </div>
                </el-form-item>
              </el-col>
            </el-col>
            <el-col :span="24">
              <el-col v-bind="grid">
                <el-form-item label="经纬度：" label-for="status2" prop="latlng">
                  <el-tooltip>
                    <el-input v-model="formItem.latlng" style="width: 100%" placeholder="请查找位置">
                      <el-button type="primary" slot="append" v-db-click @click="onSearch">查找位置</el-button>
                    </el-input>
                    <div slot="content">请点击查找位置选择位置</div>
                  </el-tooltip>
                </el-form-item>
              </el-col>
            </el-col>
          </el-row>
          <!-- <el-row>
              <div class="btn">
                <el-button type="primary" long v-db-click @click="handleSubmit('formItem')">{{
                  formItem.id ? '修改' : '提交'
                }}</el-button>
              </div>
            </el-row> -->
        </el-form>

        <el-dialog
          :visible.sync="modalPic"
          width="1024px"
          :title="modalTitle"
          :close-on-click-modal="false"
          append-to-body
        >
          <uploadPictures
            :isChoice="isChoice"
            @getPic="getPic"
            :gridBtn="gridBtn"
            :gridPic="gridPic"
            v-if="modalPic"
          ></uploadPictures>
        </el-dialog>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" long v-db-click @click="handleSubmit('formItem')">{{
          formItem.id ? '修改' : '提交'
        }}</el-button>
      </span>
    </el-dialog>
    <el-dialog
      :visible.sync="modalMap"
      title="请选择地址"
      append-to-body
      :close-on-click-modal="false"
      width="720px"
      class="mapBox"
    >
      <iframe id="mapPage" width="100%" height="600px" frameborder="0" v-bind:src="keyUrl"></iframe>
    </el-dialog>
  </div>
</template>

<script>
import { storeApi, keyApi, storeAddApi, storeGetInfoApi } from '@/api/setting';
import { mapState } from 'vuex';
import uploadPictures from '@/components/uploadPictures';
import { cityList } from '@/api/app';
export default {
  name: 'systemStore',
  components: { uploadPictures },
  props: {},
  data() {
    const validatePhone = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请填写电话号码'));
      } else {
        callback();
      }
    };
    const validateUpload = (rule, value, callback) => {
      if (!this.formItem.image) {
        callback(new Error('请上传提货点logo'));
      } else {
        callback();
      }
    };
    const oblongImageUpload = (rule, value, callback) => {
      if (!this.formItem.oblong_image) {
        callback(new Error('请上传提货点大图'));
      } else {
        callback();
      }
    };
    return {
      isTemplate: false,
      spinShow: false,
      modalMap: false,
      addresData: [],
      modalTitle: '',
      formItem: {
        name: '',
        introduction: '',
        phone: '',
        address: [],
        address2: [],
        detailed_address: '',
        valid_time: [],
        day_time: ['', ''],
        latlng: '',
        id: 0,
      },
      ruleValidate: {
        name: [{ required: true, message: '请输入提货点名称', trigger: 'blur' }],
        mail: [
          {
            required: true,
            message: 'Mailbox cannot be empty',
            trigger: 'blur',
          },
          { type: 'email', message: 'Incorrect email format', trigger: 'blur' },
        ],
        address: [
          {
            required: true,
            message: '请选择提货点地址',
            type: 'array',
            trigger: 'change',
          },
        ],
        valid_time: [
          {
            required: true,
            type: 'array',
            message: '请选择核销时效',
            trigger: 'change',
            fields: {
              0: { type: 'date', required: true, message: '请选择年度范围' },
              1: { type: 'date', required: true, message: '请选择年度范围' },
            },
          },
        ],
        day_time: [
          {
            required: true,
            type: 'array',
            message: '请选择提货点营业时间',
            trigger: 'change',
          },
        ],
        phone: [{ required: true, validator: validatePhone, trigger: 'blur' }],
        detailed_address: [{ required: true, message: '请输入详细地址', trigger: 'blur' }],
        image: [{ required: true, validator: validateUpload, trigger: 'change' }],
        oblong_image: [{ required: true, validator: oblongImageUpload, trigger: 'change' }],
        latlng: [{ required: true, message: '请选择经纬度', trigger: 'blur' }],
      },
      keyUrl: '',
      grid: {
        xl: 20,
        lg: 24,
        md: 20,
        sm: 24,
        xs: 24,
      },
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
      modalPic: false,
      isChoice: '单选',
    };
  },
  created() {
    this.getCityList();
  },
  computed: {},
  mounted: function () {
    window.addEventListener(
      'message',
      function (event) {
        // 接收位置信息，用户选择确认位置点后选点组件会触发该事件，回传用户的位置信息
        var loc = event.data;
        if (loc && loc.module === 'locationPicker') {
          // 防止其他应用也会向该页面post信息，需判断module是否为'locationPicker'
          window.parent.selectAdderss(loc);
        }
      },
      false,
    );
    window.selectAdderss = this.selectAdderss;
  },
  methods: {
    getCityList() {
      cityList().then((res) => {
        res.data.map((item) => {
          item.value = item.label;
          if (item.children && item.children.length) {
            item.children.map((j) => {
              j.value = j.label;
              if (j.children && j.children.length) {
                j.children.map((o) => {
                  o.value = o.label;
                });
              }
            });
          }
        });
        this.addresData = res.data;
      });
    },
    cancel() {
      this.$refs['formItem'].resetFields();
      this.clearFrom();
    },
    clearFrom() {
      this.formItem.introduction = '';
      this.formItem.day_time = ['', ''];
      this.formItem.oblong_image = '';
      this.formItem.id = 0;
    },
    // 选择经纬度
    selectAdderss(data) {
      this.formItem.latlng = data.latlng.lat + ',' + data.latlng.lng;
      this.modalMap = false;
    },
    // key值
    getKey() {},
    // 详情
    getInfo(id) {
      let that = this;
      that.formItem.id = id;
      that.spinShow = true;
      storeGetInfoApi(id)
        .then((res) => {
          let info = res.data.info || null;
          that.formItem = info || that.formItem;
          that.formItem.address = info.address2;
          that.formItem.day_time = info.day_time.split('-');
          that.spinShow = false;
        })
        .catch(function (res) {
          that.spinShow = false;
          that.$message.error(res.msg);
        });
    },
    // 选择图片
    modalPicTap(tit, picTit) {
      this.modalTitle = picTit == 'oblong' ? '提货点大图' : '提货点LOGO';
      this.modalPic = true;
      this.picTit = picTit;
    },
    // 选中图片
    getPic(pc) {
      switch (this.picTit) {
        case 'logo':
          this.formItem.image = pc.att_dir;
          break;
        case 'oblong':
          this.formItem.oblong_image = pc.att_dir;
          break;
      }
      this.modalPic = false;
    },
    // 选择地址
    handleChange(value, selectedData) {
      this.formItem.address = selectedData.map((o) => o.label);
      //  this.formItem.address2 = selectedData.map(o => o.value);
    },
    // 核销时效
    onchangeDate(e) {
      this.formItem.valid_time = e;
    },
    // 营业时间
    onchangeTime(e) {
      this.formItem.day_time = e;
    },
    onSearch() {
      if (!this.keyUrl) {
        keyApi()
          .then(async (res) => {
            let keys = res.data.key;
            this.keyUrl = `https://apis.map.qq.com/tools/locpicker?type=1&key=${keys}&referer=myapp`;
            this.modalMap = true;
          })
          .catch((res) => {
            this.$message.error(res.msg);
          });
      } else {
        this.modalMap = true;
      }
    },
    // 提交
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          storeAddApi(this.formItem)
            .then(async (res) => {
              this.$message.success(res.msg);
              this.isTemplate = false;
              this.$parent.getList();
              this.$refs[name].resetFields();
              this.clearFrom();
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        } else {
          return false;
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.picBox {
  display: inline-block;
  cursor: pointer;
  .upLoad {
    width: 58px;
    height: 58px;
    line-height: 58px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    background: rgba(0, 0, 0, 0.02);
  }
  .pictrue {
    width: 60px;
    height: 60px;
    border: 1px dotted rgba(0, 0, 0, 0.1);
    margin-right: 10px;

    img {
      width: 100%;
      height: 100%;
    }
  }
  .iconfont {
    color: #898989;
  }
}
.mapBox ::v-deep .ivu-modal-body {
  height: 640px !important;
}
.btn {
  margin: 0 auto;
  width: 40%;
}
</style>
