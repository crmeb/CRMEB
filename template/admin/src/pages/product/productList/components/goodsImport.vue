<!-- 商品导入 -->
<template>
  <div class="goods-import">
    <!-- 下载模板 -->
    <div class="download acea-row row-middle">
      <span>上传前请先按Excel模板中的格式编辑内容</span>
      <img src="@/assets/images/excel-icon.png" alt="" />
      <a href="/product_migration.xlsx" download class="download-text cup">下载Excel模板</a>
    </div>

    <div class="goods-upload mt20">
      <el-upload
        v-show="!fileUrl && !importStatus"
        ref="upload"
        class="upload-demo"
        :drag="!fileUrl"
        :show-file-list="false"
        :action="uploadUrl"
        :headers="header"
        :before-upload="fileChange"
        :on-success="handleSuccess"
        accept=".xls, .xlsx"
      >
        <template>
          <img class="el-upload-dragger__icon mb20" src="@/assets/images/upload-icon.png" alt="" />
          <div class="el-upload__text">将文件拖到此处，或<em>点击添加</em></div>
          <div class="el-upload__trip">支持 .xls，.xlsx，限10M以内</div>
        </template>
      </el-upload>
      <div v-show="fileUrl && !importStatus" class="file-info">
        <img class="el-upload-dragger__icon mb20" src="@/assets/images/upload-icon.png" alt="" />
        <div class="el-upload__text">{{ fileName }}</div>
        <div class="flex mt12" v-if="fileUrl && !importLoading">
          <div class="active-btn" @click="selectFile">重新上传</div>
          <div class="active-btn" @click="fileUrl = ''">删除</div>
        </div>
        <div class="el-upload__trip" v-if="importLoading">
          正在导入，您可关闭当前弹窗，稍候可在列表查看导入结果
          <i class="el-icon-loading"></i>
        </div>
        <el-button v-else class="btn-import" type="primary" size="small" @click="importGoods">立即导入</el-button>
      </div>
      <div v-show="fileUrl && importStatus" class="file-info">
        <img class="el-upload-dragger__icon mb20" :src="statusImage" alt="" />
        <div class="el-upload__text">
          共导入 {{ resultData.all }} 个，成功 {{ resultData.success }} 个，失败 {{ resultData.fail }} 跳过
          {{ resultData.jump }} 个
        </div>
        <div class="el-upload__trip" v-if="resultData.fail > 0">
          您可以下载失败数据，修改后再重新导入 <span class="active-btn" @click="downloadFailData">下载失败数据</span>
        </div>
        <div>
          <el-button class="btn-import" size="small" @click="selectFile">再次导入</el-button>
          <el-button type="primary" class="btn-import" @click="close">完成</el-button>
        </div>
      </div>
    </div>
    <!-- 导入规则 -->
    <div class="import-rule mt20">
      <div class="rule-title">导入规则</div>
      <!-- 1. 请先下载模板，在模板中按字段填写信息，然后上传该文件。
2. 导入未完成之前，请勿关闭页面，否则可能数据错误。
3. 文件大小不超过10MB。
4. 限制导入10000行记录，超出部分请分多次导入。 -->
      <div class="rule-text">1. 请先下载模板，在模板中按字段填写信息，然后上传该文件。</div>
      <div class="rule-text">2. 导入未完成之前，请勿关闭页面，否则可能数据错误。</div>
      <div class="rule-text">3. 文件大小不超过10MB。</div>
      <div class="rule-text">4. 限制导入10000行记录，超出部分请分多次导入。</div>
    </div>
  </div>
</template>

<script>
import { importProductImport } from '@/api/export';
import Setting from '@/setting';
import { getCookies } from '@/libs/util';
import { isXlsUpload } from '@/utils/index';

export default {
  name: 'goodsImport',
  data() {
    return {
      uploadUrl: Setting.apiBaseURL + '/file/upload/1',
      header: {
        'Authori-zation': 'Bearer ' + getCookies('token'),
      },
      fileName: '',
      fileUrl: '',
      importStatus: false,
      importLoading: false,
      resultData: {
        all: 0,
        success: 0,
        fail: 0,
        jump: 0,
      },
      statusImage: require('@/assets/images/file-success.png'),
    };
  },
  watch: {
    resultData: {
      handler(newValue) {
        if (newValue.fail > 0) {
          this.statusImage = require('@/assets/images/file-fail.png');
        } else {
          this.statusImage = require('@/assets/images/file-success.png');
        }
      },
      deep: true, // 默认值是 false，代表是否深度监听
    },
  },
  mounted() {},
  methods: {
    fileChange(file, fileList) {
      if (isXlsUpload(file)) {
        // 限制10M
        if (file.size >= 10485760) {
          this.$message.error('文件大小不能超过10MB');
          return false;
        } else {
          this.fileName = file.name;
        }
      } else {
        return false;
      }
    },
    selectFile() {
      this.importStatus = false;
      this.importLoading = false;
      // 调起选择文件
      this.$refs['upload'].$refs['upload-inner'].handleClick();
    },
    handleSuccess(res, file, fileList) {
      console.log(res, '2');
      if (res.status === 200) {
        this.fileUrl = res.data.src;
      }
    },
    importGoods() {
      this.importLoading = true;
      this.importStatus = false;
      importProductImport({
        file: this.fileUrl,
      })
        .then((res) => {
          // 返回导入结果
          this.importStatus = true;
          this.resultData = res.data;
        })
        .catch((err) => {
          this.importLoading = false;
          this.importStatus = false;
          this.$message.error(err.msg);
        });
    },
    close() {
      this.fileUrl = '';
      this.fileName = '';
      this.importStatus = false;
      this.$emit('close');
    },
    downloadFailData() {
      // 下载失败数据
    },
  },
};
</script>

<style lang="scss" scoped>
.download {
  background-color: var(--prev-color-primary-light-9);
  padding: 12px;
  border-radius: 4px;
  color: #303133;
  font-size: 12px;
  img {
    width: 19px;
    height: 19px;
    margin: 0 4px 0 8px;
  }
  .download-text {
    color: var(--prev-color-primary);
  }
}
.goods-upload {
  width: 100%;
  ::v-deep .el-upload {
    width: 100%;
    .el-upload-dragger {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding: 170px 0;
      .el-upload-dragger__icon {
        width: 42px;
        height: 57px;
      }
      .el-upload__trip {
        font-weight: 400;
        font-size: 12px;
        color: #999999;
        margin-top: 6px;
      }
    }
  }
  .file-info {
    height: 342px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    border: 1px dashed #d9d9d9;
    border-radius: 4px;
    margin-top: 10px;
    .active-btn {
      color: var(--prev-color-primary);
      font-size: 12px;
      font-weight: 400;
      margin: 0 6px;
      cursor: pointer;
    }
    .btn-import {
      margin-top: 26px;
    }
    .el-upload-dragger__icon {
      width: 42px;
      height: 57px;
    }
    .el-upload__trip {
      display: flex;
      align-items: center;
      font-weight: 400;
      font-size: 12px;
      color: #999;
      margin-top: 6px;
    }
  }
}
.import-rule {
  .rule-title {
    font-weight: 500;
    font-size: 14px;
    color: #303133;
    margin-bottom: 6px;
  }
  .rule-text {
    font-size: 12px;
    color: #303133;
  }
}
</style>
