<template>
  <div class="newsBox">
    <pages-header
      ref="pageHeader"
      :title="$route.meta.title"
      :backUrl="$routeProStr + '/app/wechat/news_category/index'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="save_from mt16">
      <el-row :gutter="24">
        <el-col :xl="6" :lg="6" :md="12" :sm="24" :xs="24">
          <!--                    v-if="list.length!=0"-->
          <div v-for="(item, i) in list" :key="i">
            <div
              v-if="i === 0"
              v-db-click
              @click="onSubSave(i)"
              :class="{ checkClass: i === current }"
              @mouseenter="isDel = true"
              @mouseleave="isDel = false"
            >
              <div
                class="news_pic"
                :style="{
                  backgroundImage: 'url(' + (item.image_input ? item.image_input : baseImg) + ')',
                  backgroundSize: '100% 100%',
                }"
              >
                <el-button type="error" icon="el-icon-delete" v-db-click @click="del(i)" v-show="isDel"></el-button>
              </div>
              <span class="news_sp">{{ item.title }}</span>
            </div>
            <div class="news_cent" v-else v-db-click @click="onSubSave(i)" :class="{ checkClass: i === current }">
              <span class="news_sp1">{{ item.title }}</span>
              <div class="news_cent_img ivu-mr-8">
                <img :src="item.image_input ? item.image_input : baseImg" />
              </div>
              <el-button type="error" icon="el-icon-delete" v-db-click @click="del(i)"></el-button>
            </div>
          </div>
          <!-- <div class="acea-row row-center-wrapper">
            <el-button class="mt20" type="primary" v-db-click @click="handleAdd">添加图文</el-button>
          </div> -->
        </el-col>
        <el-col :xl="18" :lg="18" :md="12" :sm="24" :xs="24">
          <el-form
            class="saveForm"
            ref="saveForm"
            :model="saveForm"
            :label-width="labelWidth"
            :rules="ruleValidate"
            :label-position="labelPosition"
            @submit.native.prevent
          >
            <el-row :gutter="24">
              <el-col :span="24" class="ml40">
                <el-form-item label="标题：" prop="title">
                  <el-input style="width: 60%" v-model="saveForm.title" type="text" placeholder="请输入文章标题" />
                </el-form-item>
              </el-col>
              <el-col :span="24" class="ml40">
                <el-form-item label="作者：" prop="author">
                  <el-input style="width: 60%" v-model="saveForm.author" type="text" placeholder="请输入作者名称" />
                </el-form-item>
              </el-col>
              <el-col :span="24" class="ml40">
                <el-form-item label="摘要：" prop="synopsis">
                  <el-input style="width: 60%" v-model="saveForm.synopsis" type="textarea" placeholder="请输入摘要" />
                </el-form-item>
              </el-col>
              <el-col :span="24" class="ml40">
                <el-form-item label="图文封面：" prop="image_input">
                  <div class="picBox" v-db-click @click="modalPicTap('单选')">
                    <div class="pictrue" v-if="saveForm.image_input">
                      <img :src="saveForm.image_input" />
                    </div>
                    <div class="upLoad acea-row row-center-wrapper" v-else>
                      <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                    </div>
                  </div>
                </el-form-item>
                <el-form-item label="正文：" prop="content">
                  <WangEditor style="width: 90%" :content="content" @editorContent="getEditorContent"></WangEditor>
                </el-form-item>
              </el-col>
              <el-col :span="24" class="ml40">
                <el-form-item>
                  <el-button type="primary" class="submission" v-db-click @click="subFrom('saveForm')">提交</el-button>
                </el-form-item>
              </el-col>
              <el-dialog :visible.sync="modalPic" width="1024px" title="上传文章图" :close-on-click-modal="false">
                <uploadPictures
                  :isChoice="isChoice"
                  @getPic="getPic"
                  :gridBtn="gridBtn"
                  :gridPic="gridPic"
                  v-if="modalPic"
                ></uploadPictures>
              </el-dialog>
            </el-row>
          </el-form>
        </el-col>
      </el-row>
    </el-card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import WangEditor from '@/components/wangEditor/index.vue';
import uploadPictures from '@/components/uploadPictures';
import { wechatNewsAddApi, wechatNewsInfotApi } from '@/api/app';
export default {
  name: 'newsCategorySave',
  components: { uploadPictures, WangEditor },
  watch: {
    $route(to, from) {
      if (this.$route.params.id !== '0') {
        this.info();
      } else {
        this.list = [
          {
            title: '',
            author: '',
            synopsis: '',
            image_input: '',
            content: '',
            id: 0,
          },
        ];
        this.saveForm = this.list[this.current];
      }
    },
  },
  data() {
    const validateUpload = (rule, value, callback) => {
      if (this.saveForm.image_input) {
        callback();
      } else {
        callback(new Error('请上传图文封面'));
      }
    };
    return {
      myConfig: {
        autoHeightEnabled: false, // 编辑器不自动被内容撑高
        initialFrameHeight: 500, // 初始容器高度
        initialFrameWidth: '100%', // 初始容器宽度
        UEDITOR_HOME_URL: '/UEditor/',
        serverUrl: '',
      },
      ruleValidate: {
        title: [{ required: true, message: '请输入标题', trigger: 'blur' }],
        author: [{ required: true, message: '请输入作者', trigger: 'blur' }],
        image_input: [{ required: true, validator: validateUpload, trigger: 'change' }],
        content: [{ required: true, message: '请输入正文', trigger: 'change' }],
        synopsis: [{ required: true, message: '请输入文章摘要', trigger: 'blur' }],
      },
      isChoice: '单选',
      dragging: null,
      isDel: false,
      msg: '',
      count: [],
      baseImg: require('../../../../assets/images/bjt.png'),
      saveForm: {
        title: '',
        author: '',
        synopsis: '',
        image_input: '',
        content: '',
        id: 0,
      },
      current: 0,
      list: [
        {
          title: '',
          author: '',
          synopsis: '',
          image_input: '',
          content: '',
          id: 0,
        },
      ],
      uploadList: [],
      modalPic: false,
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
      content: '',
    };
  },
  computed: {
    ...mapState('media', ['isMobile']),
    labelWidth() {
      return this.isMobile ? undefined : '100px';
    },
    labelPosition() {
      return this.isMobile ? 'top' : 'right';
    },
  },
  mounted() {
    if (this.$route.params.id !== '0') {
      this.info();
    } else {
      this.saveForm = this.list[this.current];
    }
  },
  methods: {
    getEditorContent(data) {
      this.saveForm.content = data;
    },
    // 点击图文封面
    modalPicTap() {
      this.modalPic = true;
    },
    // 获取图片信息
    getPic(pc) {
      this.saveForm.image_input = pc.att_dir;
      this.modalPic = false;
    },
    // 添加图文按钮
    handleAdd() {
      if (!this.check()) return false;
      let obj = {
        title: '',
        author: '',
        synopsis: '',
        image_input: '',
        content: '',
        id: 0,
      };
      this.list.push(obj);
    },
    // 点击模块
    onSubSave(i) {
      this.current = i;
      this.list.map((item, index) => {
        /* eslint-disable */
        if (index === this.current) return (this.saveForm = this.list[this.current]);
      });
      this.content = this.saveForm.content;
    },
    // 删除
    del(i) {
      if (i === 0) {
        this.$message.warning('不能再删除了');
      } else {
        this.list.splice(i, 1);
        this.saveForm = {};
      }
    },
    // 详情
    info() {
      wechatNewsInfotApi(this.$route.params.id)
        .then(async (res) => {
          let info = res.data.info;
          this.list = info.new;
          this.saveForm = this.list[this.current];
          this.content = this.list[this.current].content;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 提交数据
    subFrom(name) {
      this.$refs[name].validate((valid) => {
        if (valid) {
          let data = {
            id: this.$route.params.id || 0,
            list: this.list,
          };
          wechatNewsAddApi(data)
            .then(async (res) => {
              this.$message.success(res.msg);
              setTimeout(() => {
                this.$router.push({
                  path: this.$routeProStr + '/app/wechat/news_category/index',
                });
              }, 500);
            })
            .catch((res) => {
              this.$message.error(res.msg);
            });
        } else {
          return false;
        }
      });
    },
    check() {
      for (let index in this.list) {
        if (!this.list[index].title) {
          this.$message.warning('请输入文章的标题');
          return false;
        } else if (!this.list[index].author) {
          this.$message.warning('请输入文章的作者');
          return false;
        } else if (!this.list[index].synopsis) {
          this.$message.warning('请输入文章的摘要');
          return false;
        } else if (!this.list[index].image_input) {
          this.$message.warning('请输入文章的图文封面');
          return false;
        } else if (!this.list[index].content) {
          this.$message.warning('请输入文章的内容');
          return false;
        } else {
          return true;
        }
      }
      // if(!this.saveForm.title){
      //     this.$message.warning('请输入文章的标题');
      //     return false;
      // }
      // else if(!this.saveForm.author){
      //     this.$message.warning('请输入文章的作者');
      //     return false;
      // }
      // else if(!this.saveForm.synopsis){
      //     this.$message.warning('请输入文章的摘要');
      //     return false;
      // }
      // else if(!this.saveForm.image_input){
      //     this.$message.warning('请输入文章的图文封面');
      //     return false;
      // }
      // else if(!this.saveForm.content){
      //     this.$message.warning('请输入文章的内容');
      //     return false;
      // }else{
      //     return true
      // }
    },
  },
};
</script>

<style lang="scss" scoped>
.newsBox {
  ::v-deep .ivu-global-footer {
    dispaly: none !important;
  }
}
.demo-upload-list {
  display: inline-block;
  width: 60px;
  height: 60px;
  text-align: center;
  line-height: 60px;
  border: 1px solid transparent;
  border-radius: 4px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
  margin-right: 15px;
  position: relative;
}
.btndel {
  position: absolute;
  z-index: 111;
  width: 20px !important;
  height: 20px !important;
  left: 46px;
  top: -4px;
}
.demo-upload-list img {
  width: 100%;
  height: 100%;
}
.demo-upload-list-cover {
  display: none;
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.6);
}
.demo-upload-list:hover .demo-upload-list-cover {
  display: block;
}
.demo-upload-list-cover i {
  color: #fff;
  font-size: 20px;
  cursor: pointer;
  margin: 0 2px;
}
.save_from ::v-deep .ivu-btn-error {
  width: 24px !important;
  height: 24px !important;
  background: #fff !important;
  color: #999 !important;
  border: 1px solid #eee !important;
}
.save_from ::v-deep .ivu-btn-error:hover {
  background: #ff5d5f !important;
  border: 1px solid #fff !important;
  color: #fff !important;
}
.picBox {
  display: inline-block;
  cursor: pointer;
}
.pictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-right: 10px;
}
.pictrue img {
  width: 100%;
  height: 100%;
}
.upLoad {
  width: 58px;
  height: 58px;
  line-height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
}
.checkClass {
  border: 1px dashed #0091ff !important;
}
.checkClass2 {
  border: 1px solid #0091ff !important;
}
.submission {
  width: 10%;
}
.cover {
  width: 60px;
  height: 60px;

  img {
    width: 100%;
    height: 100%;
  }
}
.Refresh {
  font-size: 12px;
  color: var(--prev-color-primary);
  cursor: pointer;
  line-height: 35px;
  display: inline-block;
}
.news_pic {
  width: 100%;
  height: 150px;
  overflow: hidden;
  position: relative;
  background-size: 100%;
  background-position: center center;
  border-radius: 5px 5px 0 0;
  padding: 10px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}
.news_sp {
  font-size: 12px;
  color: #000000;
  background: #fff;
  width: 100%;
  height: 38px;
  line-height: 38px;
  padding: 0 12px;
  box-sizing: border-box;
  display: block;
  border-bottom: 1px dashed #eee;
}
.news_cent {
  width: 100%;
  height: auto;
  background: #fff;
  border-bottom: 1px dashed #eee;
  display: flex;
  padding: 10px;
  box-sizing: border-box;
  justify-content: space-between;
  align-items: center;
  .news_sp1 {
    font-size: 12px;
    color: #000000;
    width: 71%;
  }
  .news_cent_img {
    width: 81px;
    height: 46px;
    border-radius: 6px;
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
    }
  }
}
</style>
