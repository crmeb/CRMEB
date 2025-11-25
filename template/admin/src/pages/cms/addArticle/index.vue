<template>
  <div class="article-manager">
    <pages-header
      ref="pageHeader"
      :title="$route.params.id ? '编辑文章' : '添加文章'"
      :backUrl="$routeProStr + '/cms/article/index'"
    ></pages-header>
    <el-card :bordered="false" shadow="never" class="mt16">
      <el-form
        class="form"
        ref="formValidate"
        :model="formValidate"
        :rules="ruleValidate"
        :label-width="labelWidth"
        :label-position="labelPosition"
        @submit.native.prevent
      >
        <div class="goodsTitle acea-row">
          <div class="title">文章信息</div>
        </div>
        <div class="grid_box">
          <el-form-item label="标题：" prop="title" label-for="title">
            <el-input
              v-model="formValidate.title"
              placeholder="请输入"
              class="content_width"
              maxlength="80"
              show-word-limit
            />
          </el-form-item>
          <el-form-item label="作者：" prop="author" label-for="author">
            <el-input
              v-model="formValidate.author"
              placeholder="请输入"
              class="content_width"
              maxlength="10"
              show-word-limit
            />
          </el-form-item>
          <el-form-item label="文章分类：" label-for="cid" prop="cid">
            <el-cascader
              class="content_width"
              v-model="formValidate.cid"
              size="small"
              :options="treeData"
              :props="{ multiple: false, checkStrictly: true, emitPath: false }"
              clearable
            ></el-cascader>
          </el-form-item>
          <el-form-item label="文章简介：" prop="synopsis" label-for="synopsis">
            <el-input
              v-model="formValidate.synopsis"
              type="textarea"
              placeholder="请输入"
              class="content_width"
              maxlength="300"
              show-word-limit
            />
          </el-form-item>
          <el-form-item label="图文封面：" prop="image_input">
            <div class="picBox" v-db-click @click="modalPicTap('单选')">
              <div class="pictrue" v-if="formValidate.image_input">
                <img :src="formValidate.image_input" />
              </div>
              <div class="upLoad acea-row row-center-wrapper" v-else>
                <i class="el-icon-plus" style="font-size: 24px"></i>
              </div>
            </div>
            <div class="tip">建议尺寸：500 x 312 px</div>
          </el-form-item>
        </div>
        <div class="goodsTitle acea-row">
          <div class="title">文章内容</div>
        </div>
        <el-form-item label="文章内容：" prop="content">
          <WangEditor style="width: 90%" :content="formValidate.content" @editorContent="getEditorContent"></WangEditor>
        </el-form-item>
        <div class="goodsTitle acea-row">
          <div class="title">其他设置</div>
        </div>
        <el-row :gutter="24">
          <!--                    <el-col :span="24">-->
          <!--                        <el-form-item label="原文链接：">-->
          <!--                            <el-input v-model="formValidate.url" placeholder="请输入" element-id="url" style="width: 60%"/>-->
          <!--                        </el-form-item>-->
          <!--                    </el-col>-->
          <el-col :span="24">
            <el-form-item label="banner显示：" label-for="is_banner">
              <el-radio-group v-model="formValidate.is_banner" element-id="is_banner">
                <el-radio :label="1" class="radio">显示</el-radio>
                <el-radio :label="0">不显示</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="热门文章：" label-for="is_hot">
              <el-radio-group v-model="formValidate.is_hot" element-id="is_hot">
                <el-radio :label="1" class="radio">显示</el-radio>
                <el-radio :label="0">不显示</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
          <el-col :span="24">
            <el-form-item label="">
              <el-button type="primary" class="submission" v-db-click @click="onsubmit('formValidate')">提交</el-button>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-dialog :visible.sync="modalPic" width="950px" title="上传商品图" :close-on-click-modal="false">
        <uploadPictures
          :isChoice="isChoice"
          @getPic="getPic"
          :gridBtn="gridBtn"
          :gridPic="gridPic"
          v-if="modalPic"
        ></uploadPictures>
      </el-dialog>
    </el-card>
  </div>
</template>

<script>
import { mapState } from 'vuex';
import uploadPictures from '@/components/uploadPictures';
import WangEditor from '@/components/wangEditor/index.vue';
import { cmsAddApi, createApi, categoryTreeListApi } from '@/api/cms';
export default {
  name: 'addArticle',
  components: { uploadPictures, WangEditor },
  data() {
    const validateUpload = (rule, value, callback) => {
      if (this.formValidate.image_input) {
        callback();
      } else {
        callback(new Error('请上传图文封面'));
      }
    };
    const validateUpload2 = (rule, value, callback) => {
      if (!this.formValidate.cid) {
        callback(new Error('请选择文章分类'));
      } else {
        callback();
      }
    };
    return {
      dialog: {},
      isChoice: '单选',
      grid: {
        xl: 8,
        lg: 8,
        md: 12,
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
      loading: false,
      formValidate: {
        id: 0,
        title: '',
        author: '',
        image_input: '',
        content: '',
        synopsis: '',
        url: '',
        is_hot: 0,
        is_banner: 0,
        cid: '',
        visit: 0,
      },
      content: '',
      ruleValidate: {
        title: [{ required: true, message: '请输入标题', trigger: 'blur' }],
        cid: [
          {
            required: true,
            validator: validateUpload2,
            trigger: 'change',
            type: 'number',
          },
        ],
        image_input: [{ required: true, validator: validateUpload, trigger: 'change' }],
        content: [{ required: true, message: '请输入文章内容', trigger: 'change' }],
      },
      value: '',
      modalPic: false,
      template: false,
      treeData: [],
      formValidate2: {
        type: 1,
      },
      myConfig: {
        autoHeightEnabled: false, // 编辑器不自动被内容撑高
        initialFrameHeight: 500, // 初始容器高度
        initialFrameWidth: '100%', // 初始容器宽度
        UEDITOR_HOME_URL: '/UEditor/',
        serverUrl: '',
      },
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
  watch: {
    $route(to, from) {
      if (this.$route.params.id) {
        this.getDetails();
      } else {
        this.formValidate = {
          id: 0,
          title: '',
          author: '',
          image_input: '',
          content: '',
          synopsis: '',
          url: '',
          is_hot: 0,
          is_banner: 0,
        };
      }
    },
  },
  methods: {
    getEditorContent(data) {
      this.content = data;
    },
    // 选择图片
    modalPicTap() {
      this.modalPic = true;
    },
    // 选中图片
    getPic(pc) {
      this.formValidate.image_input = pc.att_dir;
      this.modalPic = false;
    },
    // 分类
    getClass() {
      categoryTreeListApi()
        .then(async (res) => {
          this.treeData = res.data;
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
    // 过滤详情内容
    formatRichText(html) {
      let newContent = html.replace(/<img[^>]*>/gi, function (match, capture) {
        match = match.replace(/style="[^"]+"/gi, '').replace(/style='[^']+'/gi, '');
        match = match.replace(/width="[^"]+"/gi, '').replace(/width='[^']+'/gi, '');
        match = match.replace(/height="[^"]+"/gi, '').replace(/height='[^']+'/gi, '');
        return match;
      });
      newContent = newContent.replace(/style="[^"]+"/gi, function (match, capture) {
        match = match.replace(/width:[^;]+;/gi, 'max-width:100%;').replace(/max-max-width:[^;]+;/gi, 'max-width:100%;');
        return match;
      });
      // newContent = newContent.replace(/<br[^>]*\/>/gi, '');
      newContent = newContent.replace(
        /\<img/gi,
        '<img style="max-width:100%;height:auto;display:block;margin-top:0;margin-bottom:0;"',
      );
      return newContent;
    },
    // 提交数据
    onsubmit(name) {
      this.formValidate.content = this.formatRichText(this.content);
      this.$refs[name].validate((valid) => {
        if (valid) {
          cmsAddApi(this.formValidate)
            .then(async (res) => {
              this.$message.success(res.msg);
              setTimeout(() => {
                this.$router.push({ path: this.$routeProStr + '/cms/article/index' });
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
    // 文章详情
    getDetails() {
      createApi(this.$route.params.id ? this.$route.params.id : 0)
        .then(async (res) => {
          let data = res.data;
          let news = data.info;
          this.formValidate = {
            id: news.id,
            title: news.title,
            author: news.author,
            image_input: news.image_input,
            content: news.content,
            synopsis: news.synopsis,
            url: news.url,
            is_hot: news.is_hot,
            is_banner: news.is_banner,
            cid: news.cid,
            visit: news.visit,
          };
        })
        .catch((res) => {
          this.loading = false;
          this.$message.error(res.msg);
        });
    },
  },
  mounted() {
    if (this.$route.params.id) {
      this.getDetails();
    }
  },
  created() {
    this.getClass();
  },
};
</script>
<style scoped lang="scss">
.grid_box {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  grid-template-rows: auto;
  grid-gap: 0;
}
.content_width {
  width: 414px;
}
::v-deep .ivu-form-item-content {
  line-height: unset !important;
}
.picBox {
  display: inline-block;
  cursor: pointer;
}

.form .goodsTitle {
  border-bottom: 1px solid rgba(0, 0, 0, 0.09);
  margin-bottom: 25px;
}

.form .goodsTitle ~ .goodsTitle {
  margin-top: 20px;
}

.form .goodsTitle .title {
  border-bottom: 2px solid var(--prev-color-primary);
  padding: 0 8px 12px 5px;
  color: #000;
  font-size: 14px;
}

.form .goodsTitle .icons {
  font-size: 15px;
  margin-right: 8px;
  color: #999;
}

.form .add {
  font-size: 12px;
  color: var(--prev-color-primary);
  padding: 0 12px;
  cursor: pointer;
}

.form .radio {
  margin-right: 20px;
}

.form .submission {
  width: 10%;
}

.form .upLoad {
  width: 58px;
  height: 58px;
  line-height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
}

.form .iconfont {
  color: #898989;
}

.form .pictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-right: 10px;
}

.form .pictrue img {
  width: 100%;
  height: 100%;
}

.Modals .address {
  width: 90%;
}

.Modals .address .iconfont {
  font-size: 20px;
}
.tip {
  margin-top: 10px;
  color: #bbb;
  font-size: 12px;
}
</style>
