<template>
  <div class="article-manager">
    <Card :bordered="false" dis-hover class="ivu-mt">
      <Form
        ref="formItem"
        :model="formItem"
        :label-width="labelWidth"
        :label-position="labelPosition"
        :rules="ruleValidate"
        @submit.native.prevent
      >
        <Row type="flex" :gutter="24">
          <Col span="24">
            <Col v-bind="grid">
              <FormItem label="门店名称：" prop="name" label-for="name">
                <Input v-model="formItem.name" placeholder="请输入门店名称" />
              </FormItem>
            </Col>
          </Col>
          <Col span="24">
            <Col v-bind="grid">
              <FormItem label="门店简介：" label-for="introduction">
                <Input v-model="formItem.introduction" placeholder="请输入门店简介" />
              </FormItem>
            </Col>
          </Col>
          <Col span="24">
            <Col v-bind="grid">
              <FormItem label="门店手机号：" label-for="phone" prop="phone">
                <Input v-model="formItem.phone" type="number" placeholder="请输入门店手机号" />
              </FormItem>
            </Col>
          </Col>
          <Col span="24">
            <Col v-bind="grid">
              <FormItem label="门店地址：" label-for="address" prop="address">
                <Cascader
                  :data="addresData"
                  :value="formItem.address"
                  v-model="formItem.address"
                  @on-change="handleChange"
                ></Cascader>
              </FormItem>
            </Col>
          </Col>
          <Col span="24">
            <Col v-bind="grid">
              <FormItem label="详细地址：" label-for="detailed_address" prop="detailed_address">
                <Input v-model="formItem.detailed_address" placeholder="请输入详细地址" />
              </FormItem>
            </Col>
          </Col>
          <Col span="24">
            <Col v-bind="grid">
              <FormItem label="核销时效：" label-for="valid_time">
                <DatePicker
                  :editable="false"
                  @on-change="onchangeDate"
                  :value="formItem.valid_time"
                  v-model="formItem.valid_time"
                  format="yyyy/MM/dd"
                  type="daterange"
                  split-panels
                  placeholder="请选择核销时效"
                ></DatePicker>
              </FormItem>
            </Col>
          </Col>
          <Col span="24">
            <Col v-bind="grid">
              <FormItem label="门店营业：" label-for="day_time">
                <TimePicker
                  type="timerange"
                  @on-change="onchangeTime"
                  v-model="formItem.day_time"
                  format="HH:mm:ss"
                  :value="formItem.day_time"
                  placement="bottom-end"
                  placeholder="请选择营业时间"
                ></TimePicker>
              </FormItem>
            </Col>
          </Col>
          <Col span="24">
            <Col v-bind="grid">
              <FormItem label="门店logo：" prop="image">
                <div class="picBox" @click="modalPicTap('单选')">
                  <div class="pictrue" v-if="formItem.image"><img v-lazy="formItem.image" /></div>
                  <div class="upLoad acea-row row-center-wrapper" v-else>
                    <Icon type="ios-camera-outline" size="26" class="iconfont" />
                  </div>
                </div>
              </FormItem>
            </Col>
          </Col>
          <Col span="24">
            <Col v-bind="grid">
              <FormItem label="经纬度：" label-for="status2" prop="latlng">
                <Tooltip>
                  <Input
                    search
                    enter-button="查找位置"
                    v-model="formItem.latlng"
                    style="width: 100%"
                    placeholder="请查找位置"
                    @on-search="onSearch"
                  />
                  <div slot="content">请点击查找位置选择位置</div>
                </Tooltip>
              </FormItem>
            </Col>
          </Col>
        </Row>
        <Row type="flex">
          <Col v-bind="grid">
            <Button type="primary" class="ml20" @click="handleSubmit('formItem')">提交</Button>
          </Col>
        </Row>
        <Spin size="large" fix v-if="spinShow"></Spin>
      </Form>
    </Card>

    <Modal
      v-model="modalPic"
      width="950px"
      scrollable
      footer-hide
      closable
      title="上传商品图"
      :mask-closable="false"
      :z-index="888"
    >
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
    </Modal>

    <Modal
      v-model="modalMap"
      scrollable
      footer-hide
      closable
      title="上传商品图"
      :mask-closable="false"
      :z-index="1"
      class="mapBox"
    >
      <iframe id="mapPage" width="100%" height="100%" frameborder="0" v-bind:src="keyUrl"></iframe>
    </Modal>
  </div>
</template>

<script>
import { storeApi, keyApi, storeAddApi } from '@/api/setting';
import { mapState } from 'vuex';
import city from '@/utils/city';
import uploadPictures from '@/components/uploadPictures';
export default {
  name: 'systemStore',
  components: { uploadPictures },
  data() {
    const validatePhone = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请填写手机号'));
      } else if (!/^1[3456789]\d{9}$/.test(value)) {
        callback(new Error('手机号格式不正确!'));
      } else {
        callback();
      }
    };
    const validateUpload = (rule, value, callback) => {
      if (!this.formItem.image) {
        callback(new Error('请上传门店logo'));
      } else {
        callback();
      }
    };
    return {
      spinShow: false,
      modalMap: false,
      addresData: city,
      formItem: {
        name: '',
        introduction: '',
        phone: '',
        address: [],
        address2: [],
        detailed_address: '',
        valid_time: [],
        day_time: [],
        latlng: '',
        id: 0,
      },
      ruleValidate: {
        name: [{ required: true, message: '请输入门店名称', trigger: 'blur' }],
        mail: [
          { required: true, message: 'Mailbox cannot be empty', trigger: 'blur' },
          { type: 'email', message: 'Incorrect email format', trigger: 'blur' },
        ],
        address: [{ required: true, message: '请选择门店地址', type: 'array', trigger: 'change' }],
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
        day_time: [{ required: true, type: 'array', message: '请选择门店营业时间', trigger: 'change' }],
        phone: [{ required: true, validator: validatePhone, trigger: 'blur' }],
        detailed_address: [{ required: true, message: '请输入详细地址', trigger: 'blur' }],
        image: [{ required: true, validator: validateUpload, trigger: 'change' }],
        latlng: [{ required: true, message: '请选择经纬度', trigger: 'blur' }],
      },
      keyUrl: '',
      grid: {
        xl: 10,
        lg: 16,
        md: 18,
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
    city.map((item) => {
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
    this.getKey();
    this.getFrom();
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : 100;
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
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
    // 选择经纬度
    selectAdderss(data) {
      this.formItem.latlng = data.latlng.lat + ',' + data.latlng.lng;
      this.modalMap = false;
    },
    // key值
    getKey() {
      keyApi()
        .then(async (res) => {
          let keys = res.data.key;
          this.keyUrl = `https://apis.map.qq.com/tools/locpicker?type=1&key=${keys}&referer=myapp`;
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 详情
    getFrom() {
      this.spinShow = true;
      storeApi()
        .then(async (res) => {
          let info = res.data.info || null;
          this.formItem = info || this.formItem;
          this.formItem.address = info.address2;
          this.spinShow = false;
        })
        .catch((res) => {
          this.spinShow = false;
          this.$Message.error(res.msg);
        });
    },
    // 选择图片
    modalPicTap() {
      this.modalPic = true;
    },
    // 选中图片
    getPic(pc) {
      this.formItem.image = pc.att_dir;
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
      this.modalMap = true;
    },
    // 提交
    handleSubmit(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          storeAddApi(this.formItem)
            .then(async (res) => {
              this.$Message.success(res.msg);
            })
            .catch((res) => {
              this.$Message.error(res.msg);
            });
        } else {
          return false;
        }
      });
    },
  },
};
</script>

<style scoped lang="stylus">
.picBox
    display: inline-block;
    cursor: pointer;
    .upLoad
        width: 58px;
        height: 58px;
        line-height: 58px;
        border: 1px dotted rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        background: rgba(0, 0, 0, 0.02);
    .pictrue
        width: 60px;
        height: 60px;
        border: 1px dotted rgba(0, 0, 0, 0.1);
        margin-right: 10px;
        img
           width: 100%;
           height: 100%;
    .iconfont
        color: #898989;

.mapBox >>> .ivu-modal-body
    height: 640px !important;
</style>
