<template>
  <div>
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/marketing/live/live_room'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-form
        ref="formValidate"
        :model="formValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        class="tabform"
        :rules="ruleValidate"
        @submit.native.prevent
      >
        <el-row :gutter="24">
          <el-col :span="24">
            <el-alert class="mb10" type="warning" show-icon :closable="false">
              <span slot="title"
                >必须前往微信小程序官方后台开通直播权限，关注<span
                  style="color: red; cursor: pointer"
                  v-db-click
                  @click="codeImg"
                  >【小程序直播】</span
                >须知直播状态</span
              >
            </el-alert>
          </el-col>
          <el-col :span="24">
            <el-form-item label="选择主播：" prop="anchor_wechat">
              <el-select
                v-model="formValidate.anchor_wechat"
                filterable
                clearable
                class="content_width"
                @change="anchorName"
              >
                <el-option
                  v-for="(item, index) in liveList"
                  :value="item.wechat"
                  :key="index"
                  :label="item.wechat"
                ></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="直播间名称：" prop="name">
              <el-input
                enter-button
                placeholder="请输入直播间名称"
                element-id="name"
                v-model="formValidate.name"
                class="content_width"
                maxlength="80"
                show-word-limit
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <div style="display: flex">
              <el-form-item label="背景图：" prop="name">
                <div v-db-click @click="modalPicTap(0)" class="box">
                  <img :src="formValidate.cover_img" alt="" v-if="formValidate.cover_img" />
                  <div class="upload-box acea-row row-center-wrapper" v-else>
                    <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                  </div>
                </div>
                <div class="desc">尺寸：1080*1920px</div>
              </el-form-item>
            </div>
          </el-col>
          <el-col :span="24">
            <div style="display: flex">
              <el-form-item label="分享图：" prop="name">
                <div v-db-click @click="modalPicTap(1)" class="box">
                  <img :src="formValidate.share_img" alt="" v-if="formValidate.share_img" />
                  <div class="upload-box acea-row row-center-wrapper" v-else>
                    <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                  </div>
                </div>
                <div class="desc">尺寸：800*640px</div>
              </el-form-item>
            </div>
          </el-col>
          <!--<el-col :span="24">-->
          <!--<el-form-item label="主播昵称：">-->
          <!--<el-input enter-button  placeholder="请输入主播昵称" element-id="anchor_name" v-model="formValidate.anchor_name" style="width: 60%;"/>-->
          <!--</el-form-item>-->
          <!--</el-col>-->
          <el-col :span="24">
            <el-form-item label="联系电话：">
              <el-input
                placeholder="请输入主播联系电话"
                v-model="formValidate.phone"
                class="content_width"
                maxlength="11"
                show-word-limit
              />
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="直播时间：" prop="name">
              <el-date-picker
                clearable
                type="datetimerange"
                format="yyyy-MM-dd HH:mm"
                placeholder="请选择直播时间"
                class="content_width"
                v-model="timeVal"
                @change="selectDate"
                value-format="yyyy-MM-dd HH:mm"
                range-separator="-"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
              ></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="排序：">
              <el-input type="number" placeholder="0" v-model="formValidate.sort" class="content_width" />
            </el-form-item>
          </el-col>
          <!-- <el-col :span="24">
            <el-form-item label="显示样式：">
              <el-radio-group v-model="formValidate.screen_type">
                <el-radio :label="item.label" v-for="(item, index) in screen_type" :key="index">
                  <span>{{ item.value }}</span>
                </el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col> -->
          <el-col :span="24">
            <el-form-item label="直播间类型：">
              <el-radio-group v-model="formValidate.type">
                <el-radio :label="item.label" v-for="(item, index) in type" :key="index">
                  <span>{{ item.value }}</span>
                </el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="直播间点赞：">
              <el-switch
                class="defineSwitch"
                :active-value="1"
                :inactive-value="0"
                v-model="formValidate.close_like"
                size="large"
                active-text="开启"
                inactive-text="关闭"
              >
              </el-switch>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="直播卖货：">
              <el-switch
                class="defineSwitch"
                :active-value="1"
                :inactive-value="0"
                v-model="formValidate.close_goods"
                size="large"
                active-text="开启"
                inactive-text="关闭"
              >
              </el-switch>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="直播间评论：">
              <el-switch
                class="defineSwitch"
                :active-value="1"
                :inactive-value="0"
                v-model="formValidate.close_comment"
                size="large"
                active-text="开启"
                inactive-text="关闭"
              >
              </el-switch>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="24">
          <el-col v-bind="grid" :span="24">
            <el-button
              :loading="loading"
              type="primary"
              style="margin-left: 120px"
              v-db-click
              @click="handleSubmit('formItem')"
            >
              提交
            </el-button>
            <!-- <el-button
              type="primary"
              v-db-click @click="handleSubmit('formItem')"
              style="width: 19%; margin-left: 99px"
              >提交</el-button
            > -->
          </el-col>
        </el-row>
      </el-form>
    </el-card>
    <div>
      <el-dialog :visible.sync="modalPic" width="950px" title="上传商品图" :close-on-click-modal="false" :z-index="888">
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          v-if="modalPic"
        ></uploadPictures>
      </el-dialog>
    </div>
    <el-dialog :visible.sync="modal3" title="二维码">
      <div class="acea-row row-around">
        <div v-viewer class="QRpic">
          <img src="https://res.wx.qq.com/op_res/9rSix1dhHfK4rR049JL0PHJ7TpOvkuZ3mE0z7Ou_Etvjf-w1J_jVX0rZqeStLfwh" />
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import uploadPictures from '@/components/uploadPictures';
import { liveAdd, liveAuchorList } from '@/api/live';
export default {
  name: 'creat_live',
  components: {
    uploadPictures,
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '120px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  data() {
    return {
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
      grid: {
        xl: 10,
        lg: 16,
        md: 18,
        sm: 24,
        xs: 24,
      },
      loading: false,
      formValidate: {
        name: '',
        anchor_name: '',
        anchor_wechat: '',
        phone: '',
        screen_type: 0,
        close_like: 1,
        close_goods: 1,
        close_comment: 1,
        cover_img: '',
        share_img: '',
        sort: 0,
        type: 0,
        start_time: '',
      },
      screen_type: [
        {
          value: '竖屏',
          label: 0,
        },
        {
          value: '横屏',
          label: 1,
        },
      ],
      type: [
        // {
        //     value:'推流',
        //     label:1
        // },
        {
          value: '手机直播',
          label: 0,
        },
      ],
      close_like: [
        {
          value: '开启',
          label: 1,
        },
        {
          value: '关闭',
          label: 0,
        },
      ],
      close_goods: [
        {
          value: '开启',
          label: 1,
        },
        {
          value: '关闭',
          label: 0,
        },
      ],
      close_comment: [
        {
          value: '开启',
          label: 1,
        },
        {
          value: '关闭',
          label: 0,
        },
      ],
      timeVal: '',
      modalPic: false,
      isChoice: '单选',
      activeIndex: 0,
      liveList: [],
      modal3: false,
      ruleValidate: {
        anchor_wechat: [{ required: true, message: 'Please select the city', trigger: 'change' }],
        name: [{ required: true, message: 'The name cannot be empty', trigger: 'blur' }],
      },
    };
  },
  mounted() {
    this.getLive();
  },
  methods: {
    cancel() {
      this.modal3 = false;
    },
    codeImg() {
      this.modal3 = true;
    },
    anchorName(e) {
      this.liveList.filter((el, index) => {
        if (el.wechat === e) {
          this.formValidate.anchor_name = el.name;
        }
      });
    },
    //主播列表；
    getLive() {
      let formValidate = {
        kerword: '',
        page: '',
        limit: '',
      };
      liveAuchorList(formValidate)
        .then((res) => {
          this.liveList = res.data.list;
        })
        .catch((error) => {
          this.$message.error(error.msg);
        });
    },
    // 点击图文封面
    modalPicTap(type) {
      this.activeIndex = type;
      this.modalPic = true;
    },
    // 选择日期
    selectDate(e) {
      this.formValidate.start_time = e;
    },
    // 获取图片信息
    getPic(pc) {
      this.$nextTick(() => {
        if (this.activeIndex == 0) {
          this.formValidate.cover_img = pc.att_dir;
        } else {
          this.formValidate.share_img = pc.att_dir;
        }
        this.modalPic = false;
      });
    },
    // 保存
    handleSubmit(name) {
      this.loading = true;
      liveAdd(this.formValidate)
        .then((res) => {
          this.$message.success('添加成功');
          setTimeout(() => {
            this.loading = false;
            this.$router.push({ path: this.$routeProStr + '/marketing/live/live_room' });
          }, 500);
        })
        .catch((error) => {
          setTimeout(() => {
            this.loading = false;
          }, 1000);
          this.$message.error(error.msg);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.content_width {
  width: 460px;
}
.QRpic {
  width: 180px;
  height: 180px;

  img {
    width: 100%;
    height: 100%;
  }
}
.desc {
  font-size: 12px;
  color: #999;
}
.upload-box {
  width: 58px;
  height: 58px;
  line-height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
  cursor: pointer;
}
.box {
  width: 60px;
  height: 60px;
  border-radius: 4px;
  cursor: pointer;

  img {
    width: 100%;
    height: 100%;
  }
}
</style>
