<template>
  <div class="message">
    <div class="table-box" style="padding-bottom: 0">
      <Card :bordered="false" dis-hover class="">
        <div class="mb20">
          <Tabs v-model="currentTab" @on-click="changeTab">
            <TabPane
              :label="item.label"
              :name="item.value.toString()"
              v-for="(item, index) in headerList"
              :key="index"
            />
          </Tabs>
        </div>
        <h3>使用说明</h3>
        <template v-if="currentTab == 1">
          <p>上传图片时会生成缩略图</p>
          <p>未设置按照系统默认生成，系统默认：大图800*800，中图300*300，小图150*150</p>
          <p>水印只在上传图片时生成，原图，大中小缩略图上都按照比例存在。</p>
          <p>若上传图片时未开启水印，则该图在开启水印之后依旧无水印效果。</p>
        </template>
        <template v-else>
          <p v-if="currentTab == 2">
            七牛云开通方法：<a href="https://doc.crmeb.com/web/single/crmeb_v4/987" target="_blank">点击查看</a>
          </p>
          <p v-if="currentTab == 3">
            阿里云oss开通方法：<a href="https://doc.crmeb.com/web/single/crmeb_v4/985" target="_blank">点击查看</a>
          </p>
          <p v-if="currentTab == 4">
            腾讯云cos开通方法：<a href="https://doc.crmeb.com/web/single/crmeb_v4/986" target="_blank">点击查看</a>
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
      </Card>
    </div>
    <div class="table-box" style="padding-top: 10px" v-if="currentTab == 1">
      <Card :bordered="false" dis-hover class="ivu-mt">
        <Row type="flex">
          <Col span="24">
            <span class="save-type"> 存储方式： </span>
            <RadioGroup v-model="formValidate.upload_type" @on-change="changeSave">
              <Radio label="1">本地存储</Radio>
              <Radio label="2">七牛云存储</Radio>
              <Radio label="3">阿里云存储</Radio>
              <Radio label="4">腾讯云存储</Radio>
            </RadioGroup>
            <!-- <i-switch
              v-model="localStorage"
              size="large"
              @on-change="addSwitch"
            >
              <span slot="open">开启</span>
              <span slot="close">关闭</span>
            </i-switch> -->
          </Col>
        </Row>
      </Card>
      <Card :bordered="false" dis-hover class="ivu-mt">
        <Form ref="formValidate" :model="formValidate" :rules="ruleValidate">
          <div class="abbreviation">
            <div class="top">
              <div class="topBox">
                <div class="topLeft">
                  <div class="img">
                    <img class="imgs" src="../../../assets/images/abbreviationBig.png" alt="" />
                  </div>
                  <div>缩略大图</div>
                </div>
                <div class="topRight">
                  <FormItem label="宽：">
                    <Input
                      class="topIput"
                      type="number"
                      v-model="formValidate.thumb_big_width"
                      placeholder="请输入宽度"
                    >
                      <span slot="append">px</span>
                    </Input>
                  </FormItem>
                  <FormItem label="高：">
                    <Input
                      class="topIput"
                      type="number"
                      v-model="formValidate.thumb_big_height"
                      placeholder="请输入高度"
                    >
                      <span slot="append">px</span>
                    </Input>
                  </FormItem>
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
                  <FormItem label="宽：">
                    <Input
                      class="topIput"
                      type="number"
                      v-model="formValidate.thumb_mid_width"
                      placeholder="请输入宽度"
                    >
                      <span slot="append">px</span>
                    </Input>
                  </FormItem>
                  <FormItem label="高：">
                    <Input
                      type="number"
                      class="topIput"
                      v-model="formValidate.thumb_mid_height"
                      placeholder="请输入高度"
                    >
                      <span slot="append">px</span>
                    </Input>
                  </FormItem>
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
                  <FormItem label="宽：">
                    <Input
                      class="topIput"
                      type="number"
                      v-model="formValidate.thumb_small_width"
                      placeholder="请输入宽度"
                    >
                      <span slot="append">px</span>
                    </Input>
                  </FormItem>
                  <FormItem label="高：">
                    <Input
                      class="topIput"
                      type="number"
                      v-model="formValidate.thumb_small_height"
                      placeholder="请输入高度"
                    >
                      <span slot="append">px</span>
                    </Input>
                  </FormItem>
                </div>
              </div>
            </div>
            <Divider />
            <div class="content">
              <FormItem label="是否开启水印：">
                <i-switch v-model="formValidate.image_watermark_status" size="large">
                  <span slot="open">开启</span>
                  <span slot="close">关闭</span>
                </i-switch>
              </FormItem>
              <div v-if="formValidate.image_watermark_status == 1">
                <FormItem label="水印类型：">
                  <RadioGroup v-model="formValidate.watermark_type">
                    <Radio :label="1">图片</Radio>
                    <Radio :label="2">文字</Radio>
                  </RadioGroup>
                </FormItem>
                <div v-if="formValidate.watermark_type == 1">
                  <div class="flex">
                    <FormItem class="contentIput" label="水印透明度：" prop="name">
                      <Input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_opacity"
                        placeholder="请输入水印透明度"
                      >
                      </Input>
                    </FormItem>
                    <FormItem class="contentIput" label="水印倾斜度：" prop="mail">
                      <Input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_rotate"
                        placeholder="请输入水印倾斜度"
                      >
                      </Input>
                    </FormItem>
                  </div>
                  <div class="flex">
                    <FormItem class="contentIput" label="水印图片：" prop="name">
                      <div class="picBox" @click="modalPicTap('单选')">
                        <div class="pictrue" v-if="formValidate.watermark_image">
                          <img :src="formValidate.watermark_image" />
                        </div>
                        <div class="upLoad acea-row row-center-wrapper" v-else>
                          <Icon type="ios-camera-outline" size="24" />
                        </div>
                      </div>
                    </FormItem>
                    <FormItem class="contentIput" label="水印位置：" prop="mail">
                      <div class="conents">
                        <div class="positionBox">
                          <div
                            class="topIput box"
                            :class="positionId == item.id ? 'on' : ''"
                            v-for="(item, index) in boxs"
                            :key="index"
                            @click="bindbox(item)"
                          ></div>
                        </div>
                        <div class="title">{{ positiontlt }}</div>
                      </div>
                    </FormItem>
                  </div>
                  <div class="flex">
                    <FormItem class="contentIput" label="水印横坐标偏移量：" width="200" prop="name">
                      <Input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_x"
                        placeholder="请输入水印横坐标偏移量"
                      >
                        <span slot="append">px</span>
                      </Input>
                    </FormItem>
                    <FormItem class="contentIput" label="水印纵坐标偏移量：" prop="mail">
                      <Input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_y"
                        placeholder="请输入水印纵坐标偏移量"
                      >
                        <span slot="append">px</span>
                      </Input>
                    </FormItem>
                  </div>
                </div>
                <!-- 水印类型为文字 -->
                <div v-else>
                  <div class="flex">
                    <FormItem class="contentIput" label="水印文字：" prop="name">
                      <Input class="topIput" v-model="formValidate.watermark_text" placeholder="请输入水印文字">
                      </Input>
                    </FormItem>
                    <FormItem class="contentIput" label="水印文字大小：">
                      <Input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_text_size"
                        placeholder="请输入水印文字大小"
                      >
                      </Input>
                    </FormItem>
                  </div>
                  <div class="flex">
                    <FormItem class="contentIput" label="水印字体颜色：" prop="name">
                      <ColorPicker v-model="formValidate.watermark_text_color" />
                    </FormItem>
                    <FormItem class="contentIput" label="水印位置：" prop="mail">
                      <div class="conents">
                        <div class="positionBox">
                          <div
                            class="topIput box"
                            :class="positionId == item.id ? 'on' : ''"
                            v-for="(item, index) in boxs"
                            :key="index"
                            @click="bindbox(item)"
                          ></div>
                        </div>
                        <div class="title">{{ positiontlt }}</div>
                      </div>
                    </FormItem>
                  </div>
                  <div class="flex">
                    <FormItem class="contentIput" label="水印字体旋转角度：">
                      <Input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_text_angle"
                        placeholder="请输入水印字体旋转角度"
                      >
                      </Input>
                    </FormItem>
                    <FormItem class="contentIput" label="水印横坐标偏移量：">
                      <Input
                        class="topIput"
                        type="number"
                        v-model="formValidate.watermark_x"
                        placeholder="请输入水印横坐标偏移量"
                      >
                        <span slot="append">px</span>
                      </Input>
                    </FormItem>
                  </div>
                  <FormItem class="contentIput" label="水印横坐纵偏移量：" prop="mail">
                    <Input
                      class="topIput"
                      type="number"
                      v-model="formValidate.watermark_y"
                      placeholder="请输入水印横坐纵偏移量"
                    >
                      <span slot="append">px</span>
                    </Input>
                  </FormItem>
                </div>
              </div>
            </div>
            <FormItem>
              <Button type="primary" @click="handleSubmit('formValidate')">保存</Button>
            </FormItem>
          </div>
        </Form>
      </Card>
    </div>
    <!-- 缩略图配置 -->
    <div class="table-box" style="padding-top: 10px" v-else-if="currentTab == 5"></div>
    <div class="table-box" style="padding-top: 10px" v-else>
      <Card :bordered="false" dis-hover class="ivu-mt">
        <Row type="flex" class="mb20">
          <Col span="24">
            <Button type="primary" @click="addStorageBtn">添加存储空间</Button>
            <Button type="success" @click="synchro" style="margin-left: 20px">同步存储空间</Button>
            <Button @click="addConfigBtn" style="float: right">修改配置信息</Button>
          </Col>
        </Row>
        <Table
          :columns="columns"
          :data="levelLists"
          ref="table"
          class="mt25"
          :loading="loading"
          highlight-row
          no-userFrom-text="暂无数据"
          no-filtered-userFrom-text="暂无筛选结果"
        >
          <template slot-scope="{ row, index }" slot="status">
            <!-- {{row}}{{index}} -->
            <i-switch
              v-model="row.status"
              :value="row.status"
              :true-value="1"
              :false-value="0"
              @on-change="changeSwitch(row, index)"
              size="large"
            >
              <span slot="open">开启</span>
              <span slot="close">关闭</span>
            </i-switch>
          </template>
          <template slot-scope="{ row, index }" slot="action">
            <template v-if="row.domain && row.domain != row.cname">
              <span class="btn" @click="config(row)">CNAME配置</span>
              <Divider type="vertical" />
            </template>
            <span class="btn" @click="edit(row)">修改空间域名</span>
            <Divider type="vertical" />
            <span class="btn" @click="del(row, '删除该数据', index)">删除</span>
          </template>
        </Table>
        <div class="acea-row row-right page">
          <Page
            :total="total"
            :current="list.page"
            show-elevator
            show-total
            @on-change="pageChange"
            :page-size="list.limit"
          />
        </div>
      </Card>
    </div>
    <Modal v-model="configuModal" title="CNAME配置">
      <div>
        <div class="confignv"><span class="configtit">主机记录：</span>{{ configData.domain }}</div>
        <div class="confignv"><span class="configtit">记录类型：</span>CNAME</div>
        <div class="confignv">
          <span class="configtit">记录值：</span>{{ configData.cname }}
          <span class="copy copy-data" :data-clipboard-text="configData.cname">复制</span>
        </div>
      </div>
      <div slot="footer"></div>
    </Modal>
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
        thumb_big_height: '',
        thumb_big_width: '',
        thumb_mid_width: '',
        thumb_mid_height: '',
        thumb_small_height: '',
        thumb_small_width: '',
        image_watermark_status: false,
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
        // { label: "缩略图配置", value: "5" },
      ],
      columns: [
        {
          title: '储存空间名称',
          key: 'name',
          align: 'center',
          minWidth: 200,
        },
        {
          title: '区域',
          key: '_region',
          align: 'center',
          minWidth: 100,
        },
        {
          title: '空间域名',
          key: 'domain',
          align: 'center',
          minWidth: 200,
        },
        {
          title: '使用状态',
          slot: 'status',
          align: 'center',
          width: 90,
        },
        {
          title: '创建时间',
          key: '_add_time',
          align: 'center',
          minWidth: 150,
        },
        {
          title: '更新时间',
          key: '_update_time',
          align: 'center',
          minWidth: 150,
        },
        {
          title: '操作',
          slot: 'action',
          width: 210,
          align: 'center',
        },
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
      this.changeTab(res.data.type.toString());
    });
  },
  mounted: function () {
    this.$nextTick(function () {
      const clipboard = new ClipboardJS('.copy-data');
      clipboard.on('success', () => {
        this.$Message.success('复制成功');
      });
    });
  },
  methods: {
    changeSave(type) {
      saveType(type)
        .then((res) => {
          this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
    bindbox(item) {
      this.positionId = item.id;
      this.positiontlt = item.content;
      this.formValidate.watermark_position = item.id;
    },
    handleSubmit(name) {
      if (this.formValidate.image_watermark_status) {
        this.formValidate.image_watermark_status = 1;
      } else {
        this.formValidate.image_watermark_status = 0;
      }
      if (this.formValidate.image_watermark_status) {
        this.$refs[name].validate((valid) => {
          if (valid) {
            this.postMessage(this.formValidate);
          } else {
            this.$Message.error('Fail!');
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
          this.$Message.success(res.msg);
        })
        .catch((err) => {
          this.$Message.error(err.msg);
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
          this.$Message.success(res.msg);
          this.getlist();
        })
        .catch((err) => {
          this.$Message.error(err.msg);
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
        this.$Modal.confirm({
          title: '切换状态',
          content: '您确认要切换使用状态吗？',
          onOk: () => {
            // resolve();
            storageStatusApi(row.id)
              .then((res) => {
                this.$Message.success(res.msg);
                this.getlist();
              })
              .catch((err) => {
                this.$Message.error(err.msg);
              });
          },
          onCancel: () => {
            this.$Message.info('已取消');
            this.getlist();
          },
        });
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
    changeTab(data) {
      this.currentTab = data;
      this.list.type = data;
      this.list.page = 1;
      if (data == 1) {
        this.getposition();
      } else {
        this.getlist();
      }
    },
    getposition() {
      let that = this;
      positionInfoApi().then((res) => {
        this.formValidate = res.data;
        if (res.data.image_watermark_status == 1) {
          that.formValidate.image_watermark_status = true;
        } else {
          that.formValidate.image_watermark_status = false;
        }
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
          this.$Message.success(res.msg);
          this.getlist();
        })
        .catch((err) => {
          this.$Message.error(err.msg);
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
          this.$Message.success(res.msg);
          this.getlist();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    pageChange(index) {
      this.list.page = index;
      this.getlist();
    },
  },
};
</script>
<style scoped lang="less">
.ivu-input-group > .ivu-input:last-child,
/deep/.ivu-input-group-append {
  background: none;
  color: #999999;
}
/deep/.ivu-input-group .ivu-input {
  border-right: 0px !important;
}
.content /deep/.ivu-form .ivu-form-item-label {
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
    /deep/.ivu-form-item-label {
      width: 120px;
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
.message /deep/ .ivu-table-header thead tr th {
  padding: 8px 16px;
}
.ivu-radio-wrapper {
  margin-right: 15px;
  font-size: 12px !important;
}
.message /deep/ .ivu-tabs-tab {
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
  font-size: 10px;
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
  width: 60px;
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
  font-size: 13px;
}
</style>
