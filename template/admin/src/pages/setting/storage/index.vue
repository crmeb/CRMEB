<template>
  <div>
    <div class="message">
      <el-card :bordered="false" shadow="never" :body-style="{ padding: '0 20px 20px' }">
        <div class="">
          <el-tabs v-model="currentTab" @tab-click="changeTab">
            <el-tab-pane
              :label="item.label"
              :name="item.value.toString()"
              v-for="(item, index) in headerList"
              :key="index"
            />
          </el-tabs>
        </div>
        <el-alert closable v-if="currentTab == 1">
          <template slot="title">
            <p>上传图片时会生成缩略图</p>
            <p>未设置按照系统默认生成，系统默认：大图800*800，中图300*300，小图150*150</p>
            <p>水印只在上传图片时生成，原图，大中小缩略图上都按照比例存在。</p>
            <p>若上传图片时未开启水印，则该图在开启水印之后依旧无水印效果。</p>
          </template>
        </el-alert>
        <el-alert closable v-else>
          <template slot="title">
            <p v-if="currentTab == 2">
              七牛云开通方法：<a href="https://doc.crmeb.com/single/v5/7792" target="_blank">点击查看</a>
            </p>
            <p v-if="currentTab == 3">
              阿里云oss开通方法：<a href="https://doc.crmeb.com/single/v5/7790" target="_blank">点击查看</a>
            </p>
            <p v-if="currentTab == 4">
              腾讯云cos开通方法：<a href="https://doc.crmeb.com/single/v5/7791" target="_blank">点击查看</a>
            </p>
            <p v-if="currentTab == 5">
              京东云cos开通方法：<a href="https://doc.crmeb.com/single/v5/8522" target="_blank">点击查看</a>
            </p>
            <p v-if="currentTab == 6">
              华为云cos开通方法：<a href="https://doc.crmeb.com/single/v5/8523" target="_blank">点击查看</a>
            </p>
            <p v-if="currentTab == 7">
              天翼云cos开通方法：<a href="https://doc.crmeb.com/single/v5/8524" target="_blank">点击查看</a>
            </p>
            <p>第一步： 添加【存储空间】（空间名称不能重复）</p>
            <p>第二步： 开启【使用状态】</p>
            <template v-if="currentTab == 2">
              <p>第三步（必选）： 选择云存储空间列表上的修改【空间域名操作】</p>
              <p>第四步（必选）： 选择云存储空间列表上的修改【CNAME配置】，打开后复制记录值到对应的平台解析</p>
            </template>
            <template v-else>
              <p>第三步（可选）： 选择云存储空间列表上的修改【空间域名操作】</p>
              <p>第四步（可选）： 选择云存储空间列表上的修改【CNAME配置】，打开后复制记录值到对应的平台解析</p>
            </template>
          </template>
        </el-alert>
      </el-card>
    </div>
    <div class="pt16" v-if="currentTab == 1">
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <el-row>
          <el-col :span="24">
            <span class="save-type"> 存储方式： </span>
            <el-radio-group v-model="formValidate.upload_type" @input="changeSave">
              <el-radio label="1">本地存储</el-radio>
              <el-radio label="2">七牛云存储</el-radio>
              <el-radio label="3">阿里云存储</el-radio>
              <el-radio label="4">腾讯云存储</el-radio>
              <el-radio label="5">京东云存储</el-radio>
              <el-radio label="6">华为云存储</el-radio>
              <el-radio label="7">天翼云存储</el-radio>
            </el-radio-group>
            <!-- <el-switch :active-value="1"  :inactive-value="0"
              v-model="localStorage"
              size="large"
              @change="addSwitch"
            >
              <span slot="open">开启</span>
              <span slot="close">关闭</span>
             </el-switch> -->
          </el-col>
        </el-row>
      </el-card>
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <el-form ref="formValidate" :model="formValidate" :rules="ruleValidate">
          <div class="abbreviation">
            <el-form-item label="是否开启缩略图：" label-width="110px">
              <el-switch
                :active-value="1"
                :inactive-value="0"
                v-model="formValidate.image_thumb_status"
                size="large"
              >
                <span slot="open">开启</span>
                <span slot="close">关闭</span>
              </el-switch>
            </el-form-item>
            <div class="top" v-if="formValidate.image_thumb_status == 1">
              <div class="topBox">
                <div class="topLeft">
                  <div class="img">
                    <img class="imgs" src="../../../assets/images/abbreviationBig.png" alt="" />
                  </div>
                  <div>缩略大图</div>
                </div>
                <div class="topRight">
                  <el-form-item label="宽：">
                    <el-input
                      class="topIput"
                      type="number"
                      v-model="formValidate.thumb_big_width"
                      placeholder="请输入宽度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                  <el-form-item label="高：">
                    <el-input
                      class="topIput"
                      type="number"
                      v-model="formValidate.thumb_big_height"
                      placeholder="请输入高度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                </div>
              </div>
              <div class="topBox">
                <div class="topLeft">
                  <div class="img">
                    <img class="imgs" src="../../../assets/images/abbreviation.png" alt="" />
                  </div>
                  <div>缩略中图</div>
                </div>
                <div class="topRight">
                  <el-form-item label="宽：">
                    <el-input
                      class="topIput"
                      type="number"
                      v-model="formValidate.thumb_mid_width"
                      placeholder="请输入宽度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                  <el-form-item label="高：">
                    <el-input
                      type="number"
                      class="topIput"
                      v-model="formValidate.thumb_mid_height"
                      placeholder="请输入高度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                </div>
              </div>
              <div class="topBox">
                <div class="topLeft">
                  <div class="img">
                    <img class="imgs" src="../../../assets/images/abbreviationSmall.png" alt="" />
                  </div>
                  <div>缩略小图</div>
                </div>
                <div class="topRight">
                  <el-form-item label="宽：">
                    <el-input
                      class="topIput"
                      type="number"
                      v-model="formValidate.thumb_small_width"
                      placeholder="请输入宽度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                  <el-form-item label="高：">
                    <el-input
                      class="topIput"
                      type="number"
                      v-model="formValidate.thumb_small_height"
                      placeholder="请输入高度"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                </div>
              </div>
            </div>
            <el-divider />
            <div class="content mt20">
              <el-form-item label="是否开启水印：" label-width="110px">
                <el-switch
                  :active-value="1"
                  :inactive-value="0"
                  v-model="formValidate.image_watermark_status"
                  size="large"
                >
                  <span slot="open">开启</span>
                  <span slot="close">关闭</span>
                </el-switch>
              </el-form-item>
              <div v-if="formValidate.image_watermark_status == 1">
                <el-form-item label="类型：" label-width="110px">
                  <el-radio-group v-model="formValidate.watermark_type">
                    <el-radio :label="1">图片</el-radio>
                    <el-radio :label="2">文字</el-radio>
                  </el-radio-group>
                </el-form-item>
                <div v-if="formValidate.watermark_type == 1">
                  <div class="flex">
                    <el-form-item class="contentIput" label="透明度：" prop="name" label-width="110px">
                      <el-input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_opacity"
                        placeholder="请输入水印透明度"
                      >
                      </el-input>
                    </el-form-item>
                    <el-form-item class="contentIput" label="倾斜度：" prop="mail" label-width="110px">
                      <el-input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_rotate"
                        placeholder="请输入水印倾斜度"
                      >
                      </el-input>
                    </el-form-item>
                  </div>
                  <div class="flex">
                    <el-form-item class="contentIput" label="图片：" prop="name" label-width="110px">
                      <div class="picBox" v-db-click @click="modalPicTap('单选')">
                        <div class="pictrue" v-if="formValidate.watermark_image">
                          <img :src="formValidate.watermark_image" />
                        </div>
                        <div class="upLoad acea-row row-center-wrapper" v-else>
                          <i class="el-icon-picture-outline" style="font-size: 24px"></i>
                        </div>
                      </div>
                    </el-form-item>
                    <el-form-item class="contentIput" label="位置：" prop="mail" label-width="110px">
                      <div class="conents">
                        <div class="positionBox">
                          <div
                            class="topIput box"
                            :class="positionId == item.id ? 'on' : ''"
                            v-for="(item, index) in boxs"
                            :key="index"
                            v-db-click
                            @click="bindbox(item)"
                          ></div>
                        </div>
                        <div class="title">{{ positiontlt }}</div>
                      </div>
                    </el-form-item>
                  </div>
                  <div class="flex">
                    <el-form-item class="contentIput" label="横坐标偏移量：" label-width="110px" prop="name">
                      <el-input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_x"
                        placeholder="请输入水印横坐标偏移量"
                        style="width: 240px"
                      >
                        <span slot="append">px</span>
                      </el-input>
                    </el-form-item>
                    <el-form-item class="contentIput" label="纵坐标偏移量：" label-width="110px" prop="mail">
                      <el-input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_y"
                        placeholder="请输入水印纵坐标偏移量"
                        style="width: 240px"
                      >
                        <span slot="append">px</span>
                      </el-input>
                    </el-form-item>
                  </div>
                </div>
                <!-- 水印类型为文字 -->
                <div v-else>
                  <div class="flex">
                    <el-form-item class="contentIput" label="文字：" label-width="110px" prop="name">
                      <el-input class="topIput" v-model="formValidate.watermark_text" placeholder="请输入水印文字">
                      </el-input>
                    </el-form-item>
                    <el-form-item class="contentIput" label="文字大小：" label-width="110px">
                      <el-input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_text_size"
                        placeholder="请输入水印文字大小"
                      >
                      </el-input>
                    </el-form-item>
                  </div>
                  <div class="flex">
                    <el-form-item class="contentIput" label="字体颜色：" prop="name" label-width="110px">
                      <el-color-picker v-model="formValidate.watermark_text_color"></el-color-picker>
                    </el-form-item>
                    <el-form-item class="contentIput" label="位置：" prop="mail" label-width="110px">
                      <div class="conents">
                        <div class="positionBox">
                          <div
                            class="topIput box"
                            :class="positionId == item.id ? 'on' : ''"
                            v-for="(item, index) in boxs"
                            :key="index"
                            v-db-click
                            @click="bindbox(item)"
                          ></div>
                        </div>
                        <div class="title">{{ positiontlt }}</div>
                      </div>
                    </el-form-item>
                  </div>
                  <div class="flex">
                    <el-form-item class="contentIput" label="字体旋转角度：" label-width="110px">
                      <el-input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_text_angle"
                        placeholder="请输入水印字体旋转角度"
                      >
                      </el-input>
                    </el-form-item>
                    <el-form-item class="contentIput" label="横坐标偏移量：" label-width="110px">
                      <el-input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_x"
                        placeholder="请输入水印横坐标偏移量"
                      >
                        <span slot="append">px</span>
                      </el-input>
                    </el-form-item>
                  </div>
                  <el-form-item class="contentIput" label="纵坐标偏移量：" prop="mail" label-width="110px">
                    <el-input
                      class="topIput"
                      type="number"
                      v-model="formValidate.watermark_y"
                      placeholder="请输入水印纵坐标偏移量"
                    >
                      <span slot="append">px</span>
                    </el-input>
                  </el-form-item>
                </div>
              </div>
            </div>
            <el-form-item>
              <el-button type="primary" v-db-click @click="handleSubmit('formValidate')">保存</el-button>
            </el-form-item>
          </div>
        </el-form>
      </el-card>
    </div>
    <!-- 缩略图配置 -->
    <div class="pt10" v-else-if="currentTab == 10"></div>
    <div class="pt10" v-else>
      <el-card :bordered="false" shadow="never" class="ivu-mt">
        <el-row class="mb20">
          <el-col :span="24">
            <el-button type="primary" v-db-click @click="addStorageBtn">添加存储空间</el-button>
            <el-button type="success" v-db-click @click="synchro" style="margin-left: 20px">同步存储空间</el-button>
            <el-button v-db-click @click="addConfigBtn" style="float: right">修改配置信息</el-button>
          </el-col>
        </el-row>
        <el-table
          :data="levelLists"
          ref="table"
          class="mt14"
          v-loading="loading"
          highlight-current-row
          no-userFrom-text="暂无数据"
          no-filtered-userFrom-text="暂无筛选结果"
        >
          <el-table-column label="储存空间名称" min-width="120">
            <template slot-scope="scope">
              <span>{{ scope.row.name }}</span>
            </template>
          </el-table-column>
          <el-table-column label="区域" min-width="90">
            <template slot-scope="scope">
              <span>{{ scope.row._region }}</span>
            </template>
          </el-table-column>
          <el-table-column label="空间域名" min-width="130">
            <template slot-scope="scope">
              <span>{{ scope.row.domain }}</span>
            </template>
          </el-table-column>
          <el-table-column label="使用状态" min-width="90">
            <template slot-scope="scope">
              <el-switch
                class="defineSwitch"
                :active-value="1"
                :inactive-value="0"
                v-model="scope.row.status"
                :value="scope.row.status"
                @change="changeSwitch(scope.row, index)"
                size="large"
                active-text="开启"
                inactive-text="关闭"
              >
              </el-switch>
            </template>
          </el-table-column>
          <el-table-column label="创建时间" min-width="130">
            <template slot-scope="scope">
              <span>{{ scope.row._add_time }}</span>
            </template>
          </el-table-column>
          <el-table-column label="更新时间" min-width="130">
            <template slot-scope="scope">
              <span>{{ scope.row._update_time }}</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" fixed="right" width="220">
            <template slot-scope="scope">
              <template v-if="scope.row.domain && scope.row.domain != scope.row.cname">
                <span class="btn" v-db-click @click="config(scope.row)">CNAME配置</span>
                <el-divider direction="vertical"></el-divider>
              </template>
              <span class="btn" v-db-click @click="edit(scope.row)">修改空间域名</span>
              <el-divider direction="vertical"></el-divider>
              <span class="btn" v-db-click @click="del(scope.row, '删除该数据', scope.$index)">删除</span>
            </template>
          </el-table-column>
        </el-table>
        <div class="acea-row row-right page">
          <pagination
            v-if="total"
            :total="total"
            :page.sync="list.page"
            :limit.sync="list.limit"
            @pagination="getlist"
          />
        </div>
      </el-card>
    </div>
    <el-dialog :visible.sync="configuModal" title="CNAME配置" width="570px">
      <div>
        <div class="confignv"><span class="configtit">主机记录：</span>{{ configData.domain }}</div>
        <div class="confignv"><span class="configtit">记录类型：</span>CNAME</div>
        <div class="confignv">
          <span class="configtit">记录值：</span>{{ configData.cname }}
          <span class="copy copy-data" v-db-click @click="insertCopy(configData.cname)">复制</span>
        </div>
      </div>
    </el-dialog>
    <el-dialog :visible.sync="modalPic" width="950px" title="上传商品图" :close-on-click-modal="false">
      <uploadPictures
        :isChoice="isChoice"
        @getPic="getPic"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
    </el-dialog>
  </div>
</template>

<script>
import ClipboardJS from 'clipboard';
import uploadPictures from '@/components/uploadPictures';

import {
  storageConfigApi,
  addConfigApi,
  addStorageApi,
  storageListApi,
  storageSynchApi,
  storageSwitchApi,
  storageStatusApi,
  editStorageApi,
  positionInfoApi,
  positionPostApi,
  saveType,
} from '@/api/setting';
export default {
  components: { uploadPictures },
  data() {
    return {
      modalPic: false,
      saveType: 0,
      isChoice: '单选',
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
      positionId: 1,
      positiontlt: '',
      formValidate: {
        image_thumb_status: 0,
        thumb_big_height: '',
        thumb_big_width: '',
        thumb_mid_width: '',
        thumb_mid_height: '',
        thumb_small_height: '',
        thumb_small_width: '',
        image_watermark_status: 0,
        watermark_type: 1,
        watermark_opacity: '',
        watermark_rotate: '',
        watermark_position: 1,
      },
      boxs: [
        { content: '左上', id: 1 },
        { content: '上', id: 2 },
        { content: '右上', id: 3 },
        { content: '左中', id: 4 },
        { content: '中', id: 5 },
        { content: '右中', id: 6 },
        { content: '左下', id: 7 },
        { content: '下', id: 8 },
        { content: '右下', id: 9 },
      ],
      ruleValidate: {},
      configuModal: false,
      configData: '',
      headerList: [
        { label: '储存配置', value: '1' },
        { label: '七牛云储存', value: '2' },
        { label: '阿里云储存', value: '3' },
        { label: '腾讯云储存', value: '4' },
        { label: '京东云储存', value: '5' },
        { label: '华为云储存', value: '6' },
        { label: '天翼云储存', value: '7' },
        // { label: "缩略图配置", value: "10" },
      ],

      total: 0,
      list: {
        page: 1,
        limit: 15,
        type: '1',
      },
      levelLists: [],
      currentTab: '1',
      loading: false,
      addData: {
        input: '',
        select: '',
        jurisdiction: '1',
        type: '1',
      },
      confData: {
        AccessKeyId: '',
        AccessKeySecret: '',
      },
      localStorage: false,
    };
  },
  created() {
    storageConfigApi().then((res) => {
      if (res.data.type == 1) {
        this.localStorage = true;
      }
      this.formValidate.upload_type = res.data.type;
      this.currentTab = res.data.type.toString();
      this.changeTab();
    });
  },
  methods: {
    insertCopy(text) {
      this.$copyText(text)
        .then((message) => {
          this.$message.success('复制成功');
        })
        .catch((err) => {
          this.$message.error('复制失败');
        });
    },
    changeSave(type) {
      saveType(type)
        .then((res) => {
          this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    bindbox(item) {
      this.positionId = item.id;
      this.positiontlt = item.content;
      this.formValidate.watermark_position = item.id;
    },
    handleSubmit(name) {
      if (this.formValidate.image_watermark_status) {
        this.$refs[name].validate((valid) => {
          if (valid) {
            this.postMessage(this.formValidate);
          } else {
            this.$message.error('Fail!');
          }
        });
      } else {
        this.postMessage(this.formValidate);
      }
    },
    //保存接口
    postMessage(data) {
      positionPostApi(data)
        .then((res) => {
          this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    // 选择图片
    modalPicTap() {
      this.modalPic = true;
    },
    // 选中图片
    getPic(pc) {
      this.formValidate.watermark_image = pc.att_dir;
      this.modalPic = false;
    },
    config(row) {
      this.configuModal = true;
      this.configData = row;
    },
    //同步储存空间
    synchro() {
      storageSynchApi(this.currentTab)
        .then((res) => {
          this.$message.success(res.msg);
          this.getlist();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    // 添加存储空间
    addStorageBtn() {
      this.$modalForm(addStorageApi(this.currentTab)).then(() => {
        this.getlist();
      });
    },
    // 修改配置信息
    addConfigBtn() {
      this.$modalForm(addConfigApi(this.currentTab)).then(() => {
        this.getlist();
      });
    },
    //修改空间域名
    edit(row) {
      this.$modalForm(editStorageApi(row.id)).then(() => {
        this.getlist();
      });
    },
    changeSwitch(row, item) {
      return new Promise((resolve) => {
        this.$msgbox({
          title: '切换状态',
          message: '您确认要切换使用状态吗',
          showCancelButton: true,
          cancelButtonText: '取消',
          confirmButtonText: '确定',
          iconClass: 'el-icon-warning',
          confirmButtonClass: 'btn-custom-cancel',
        })
          .then(() => {
            storageStatusApi(row.id)
              .then((res) => {
                this.$message.success(res.msg);
                this.getlist();
              })
              .catch((err) => {
                this.$message.error(err.msg);
              });
          })
          .catch(() => {});
      });
    },
    getlist() {
      this.loading = true;
      storageListApi(this.list).then((res) => {
        this.total = res.data.count;
        this.levelLists = res.data.list;
        this.loading = false;
      });
    },
    changeTab() {
      this.list.type = this.currentTab;
      this.list.page = 1;
      if (this.currentTab == 1) {
        this.getposition();
      } else {
        this.getlist();
      }
    },
    getposition() {
      let that = this;
      positionInfoApi().then((res) => {
        this.formValidate = res.data;
        this.positionId = res.data.watermark_position;
        for (var i = 0; i < this.boxs.length; i++) {
          if (this.boxs[i].id == res.data.watermark_position) {
            that.bindbox(this.boxs[i]);
          }
        }
      });
    },
    addSwitch(e) {
      if (e) {
        this.localStorage = 1;
      }
      storageSwitchApi({ type: this.localStorage })
        .then((res) => {
          this.$message.success(res.msg);
          this.getlist();
        })
        .catch((err) => {
          this.$message.error(err.msg);
        });
    },
    // 删除
    del(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `system/config/storage/${row.id}`,
        method: 'DELETE',
        ids: '',
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$message.success(res.msg);
          this.getlist();
        })
        .catch((res) => {
          this.$message.error(res.msg);
        });
    },
  },
};
</script>
<style scoped lang="scss">
::v-deep .el-tabs__item {
  height: 54px !important;
  line-height: 54px !important;
}
.ivu-input-group > .ivu-input:last-child,
::v-deep .ivu-input-group-append {
  background: none;
  color: #999999;
}
::v-deep .ivu-input-group .ivu-input {
  border-right: 0px !important;
}
.content ::v-deep .ivu-form .ivu-form-item-label {
  width: 133px;
}
.topIput {
  width: 186px;
  background: #ffffff;
  border-right: 0px !important;
}
.abbreviation {
  .top {
    display: flex;
    justify-content: flex-start;
    .topBox {
      display: flex;
      .topRight {
        width: 254px;
        margin-left: 36px;
      }
      .topLeft {
        width: 94px;
        height: 94px;

        text-align: center;
        font-size: 13px;
        font-weight: 400;
        color: #000000;
        .img {
          // width: 84px;
          height: 67px;
          background: #f7fbff;
          border-radius: 4px;
          margin-bottom: 9px;
          .imgs {
            width: 70px;
            height: 51px;
            display: inline-block;
            text-align: center;
            margin-top: 8px;
          }
        }
      }
    }
  }
  .content {
    ::v-deep .ivu-form-item-label {
      width: 96px;
    }
    .flex {
      display: flex;
      justify-content: flex-start;
      // width: 400px;

      .contentIput {
        width: 400px;
      }
      .conents {
        display: flex;
        .title {
          width: 30px;
          margin-top: 70px;
          margin-left: 6px;
        }
        .positionBox {
          display: flex;
          flex-wrap: wrap;
          width: 101px;
          height: 99px;
          border-right: 1px solid #dddddd;
          .box {
            width: 33px;
            height: 33px;
            // border-radius: 4px 0px 0px 0px;
            border: 1px solid #dddddd;
            cursor: pointer;
          }
          .on {
            background: rgba(24, 144, 255, 0.1);
          }
        }
      }
    }
  }
}
</style>
<style scoped>
.message ::v-deep .ivu-table-header thead tr th {
  padding: 8px 16px;
}
.ivu-radio-wrapper {
  margin-right: 15px;
  font-size: 12px !important;
}
.message ::v-deep .ivu-tabs-tab {
  border-radius: 0 !important;
}
.table-box {
  padding: 20px;
}
.is-table {
  display: flex;
  /* justify-content: space-around; */
  justify-content: center;
}
.btn {
  cursor: pointer;
  color: #2d8cf0;
  font-size: 12px;
}
.is-switch-close {
  background-color: #504444;
}
.is-switch {
  background-color: #eb5252;
}
.notice-list {
  background-color: #308cf5;
  margin: 0 15px;
}
.table {
  padding: 0 18px;
}
.confignv {
  margin: 10px 0px;
}
.configtit {
  display: inline-block;
  width: 90px;
  text-align: right;
}
.copy {
  padding: 3px 5px;
  border: 1px solid #cccccc;
  border-radius: 5px;
  color: #333;
  cursor: pointer;
  margin-left: 5px;
}
.copy:hover {
  border-color: #2d8cf0;
  color: #2d8cf0;
}
.picBox {
  display: inline-block;
  cursor: pointer;
}
.picBox .pictrue {
  width: 60px;
  height: 60px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  margin-right: 10px;
}

.picBox .pictrue img {
  width: 100%;
  height: 100%;
}
.picBox .upLoad {
  width: 58px;
  height: 58px;
  line-height: 58px;
  border: 1px dotted rgba(0, 0, 0, 0.1);
  border-radius: 4px;
  background: rgba(0, 0, 0, 0.02);
}
h3 {
  margin: 5px 0 15px 0;
}
.table-box p {
  margin-bottom: 10px;
}
.save-type {
  font-size: 12px;
}
</style>
