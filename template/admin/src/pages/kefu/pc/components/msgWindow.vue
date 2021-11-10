<template>
  <div class="msg-box">
    <div class="head">
      <div class="tab-bar">
        <a
          href="javascript:;"
          class="tab-item"
          :class="{ on: item.key == tabCur }"
          v-for="(item, index) in tabList"
          :key="index"
          @click="bindTab(item)"
          >{{ item.title }}</a
        >
      </div>
      <div class="search-box">
        <Input
          placeholder="搜索快捷回复"
          style="width: 100%"
          v-model="searchTxt"
          @on-enter="bindSearch"
        >
          <Icon type="ios-search" slot="suffix" />
        </Input>
      </div>
    </div>
    <div class="main">
      <div class="left-box">
        <vue-scroll :ops="ops">
          <div class="left-item" v-if="tabCur">
            <p>分组</p>
            <span class="iconfont iconaddto" @click="openAddSort"></span>
          </div>
          <div
            class="left-item"
            v-for="(item, index) in sortList"
            :key="index"
            :class="{ on: cateId == item.id }"
            @click="selectSort(item)"
          >
            <p>{{ item.name }}</p>
            <template v-if="tabCur">
              <span
                class="iconfont iconDot"
                @click.top="bindEdit(item, index)"
              ></span>

              <div class="edit-wrapper" v-show="item.isEdit">
                <div class="edit-item" @click="editSort(item)">编辑</div>
                <div
                  class="edit-item"
                  @click="delSort(item, '删除分类', index)"
                >
                  删除
                </div>
              </div>
              <div
                class="edit-bg"
                v-show="item.isEdit"
                @click.stop="item.isEdit = false"
              ></div>
            </template>
          </div>
        </vue-scroll>
      </div>
      <div class="right-box">
        <Scroll
          :on-reach-bottom="handleReachBottom"
          class="right-scroll"
          height="360"
        >
          <div class="msg-item add-box" v-if="tabCur" style="margin-top: 0">
            <div class="box2">
              <Input
                class="input-box"
                v-model="addMsg.title"
                placeholder="输入标题（选填）"
                style="width: 100%"
                @on-focus="bindFocus"
              />
              <div class="conBox" :class="{ active: addMsg.isEdit }">
                <div class="content">
                  <Input
                    v-model="addMsg.message"
                    type="textarea"
                    :rows="4"
                    placeholder="请输入内容"
                  />
                </div>
                <div class="bom">
                  <div class="select">
                    <Select
                      v-model="addMsg.cateId"
                      style="width: 100px"
                      size="small"
                    >
                      <Option
                        v-for="item in sortList"
                        :value="item.id"
                        :key="item.id"
                        >{{ item.name }}
                      </Option>
                    </Select>
                  </div>
                  <div class="btns-box">
                    <Button @click.stop="addMsg.isEdit = false">取消</Button>
                    <Button type="primary" @click.stop="bindAdd">保存</Button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div
            class="msg-item"
            v-for="(item, index) in list"
            :key="index"
            v-if="item.id"
          >
            <div class="box1" v-if="!item.isEdit">
              <div class="txt-box" @click="bindRadio(item)">
                <span class="title" v-if="item.title">{{
                  item.title | filtersTitle
                }}</span>
                <span v-if="item.message">{{ item.message | filtersCon }}</span>
              </div>
              <div class="edit-box" v-if="tabCur">
                <span
                  class="iconfont iconbianji"
                  @click.stop="editMsg(item)"
                ></span>
                <span
                  class="iconfont iconshanchu"
                  @click.stop="delMsg(item, '删除话术', index)"
                ></span>
              </div>
            </div>
            <div class="box2" v-else>
              <Input
                class="input-box"
                v-model="item.title"
                placeholder="输入标题（选填）"
                style="width: 100%"
              />
              <div class="content">
                <Input
                  v-model="item.message"
                  type="textarea"
                  :rows="4"
                  placeholder="请输入内容"
                />
              </div>
              <div class="bom">
                <div class="select">
                  <Select v-model="cateId" style="width: 100px" size="small">
                    <Option
                      v-for="item in sortList"
                      :value="item.id"
                      :key="item.id"
                      >{{ item.name }}
                    </Option>
                  </Select>
                </div>
                <div class="btns-box">
                  <Button @click.stop="item.isEdit = false">取消</Button>
                  <Button type="primary" @click.stop="updataMsg(item)"
                    >保存</Button
                  >
                </div>
              </div>
            </div>
          </div>
        </Scroll>
      </div>
    </div>
    <Modal
      v-model="isAddSort"
      :title="maskTitle"
      width="304"
      :mask="false"
      class="class-box"
      :footer-hide="true"
    >
      <div class="item">
        <span>分组名称：</span>
        <Input v-model="classTitle" placeholder="分组名称" />
      </div>
      <div class="item">
        <span>分组排序：</span>
        <Input v-model="classSort" placeholder="输入排序" />
      </div>
      <div class="btn">
        <Button
          type="primary"
          style="background: #1890ff; width: 100%"
          @click="addServiceCate"
          >确定</Button
        >
      </div>
    </Modal>
  </div>
</template>

<script>
import {
  speeChcraft,
  serviceCate,
  serviceCateUpdate,
  addSpeeChcraft,
  addServiceCate,
  editServiceCate,
} from "@/api/kefu";
export default {
  name: "msgWindow",
  data() {
    return {
      ops: {
        vuescroll: {
          mode: "native",
          enable: false,
          tips: {
            deactive: "Push to Load",
            active: "Release to Load",
            start: "Loading...",
            beforeDeactive: "Load Successfully!",
          },
          auto: false,
          autoLoadDistance: 0,
          pullRefresh: {
            enable: false,
          },
          pushLoad: {
            enable: false,
            auto: true,
            autoLoadDistance: 10,
          },
        },
        bar: {
          background: "#393232",
          opacity: ".5",
          size: "2px",
        },
      },
      isScroll: true,
      page: 1,
      limit: 10,
      tabCur: 1,
      tabList: [
        {
          title: "个人库",
          key: 1,
        },
        {
          title: "公共库",
          key: 0,
        },
      ],
      searchTxt: "", // 搜索
      list: [
        {
          isEdit: false,
        },
      ], // 列表
      model1: "",
      msgTitle: "", // 填写的标题
      sortList: [], // 分类
      cateId: "", // 选中的id
      addMsg: {
        title: "",
        message: "",
        cateId: "",
        isEdit: false,
      },
      isAddSort: false, // 添加分类
      classTitle: "", // 分类名称
      classSort: "", // 分类排序
      maskTitle: "", // 弹窗标题
      editObj: {}, // 编辑分类对象
    };
  },
  filters: {
    filtersTitle(val) {
      let len = 37;
      if (val.length > len) {
        let data = val.substring(0, len);
        return `${data}...`;
      } else {
        return val;
      }
    },
    filtersCon(val) {
      let len = 113;
      if (val.length > len) {
        let data = val.substring(0, len);
        return `${data}...`;
      } else {
        return val;
      }
    },
  },
  mounted() {
    let self = this;
    this.serviceCate();
    this.$nextTick(() => {
      this.scroll = new BScroll(this.$refs.wrapper, {
        mouseWheel: {
          speed: 20,
          invert: false,
          easeTime: 300,
        },
        scrollbar: true,
        disableMouse: true,
        // and so on
      });
    });
  },
  methods: {
    // 打开编辑
    editMsg(item) {
      item.isEdit = true;
      this.cateId = item.cate_id;
    },
    // 编辑框
    bindEdit(item, index) {
      //   if (index == 0) {
      //     return;
      //   } else {
      item.isEdit = !item.isEdit;
      //   }
    },
    // 头部选择
    bindTab(item) {
      this.tabCur = item.key;
      this.cateId = "";
      this.sortList = [];
      this.isScroll = true;
      this.page = 1;
      this.list = [];
      this.serviceCate();
    },
    // 搜索
    bindSearch() {
      this.isScroll = true;
      this.page = 1;
      this.list = [];
      this.getList();
    },
    // 选择分类
    selectSort(item) {
      if (this.cateId == item.id) {
        return;
      }
      this.sortList.forEach((el, index) => {
        if (el.id != item.id) {
          el.isEdit = false;
        }
      });
      this.cateId = item.id;
      this.isScroll = true;
      this.page = 1;
      this.list = [];
      this.getList();
    },
    // 删除分类
    delSort(row, tit, num) {
      let delfromData = {
        title: tit,
        num: num,
        url: `/service/cate/${row.id}`,
        method: "DELETE",
        ids: "",
        kefu: true,
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.isScroll = true;
          this.page = 1;
          this.list = [];
          this.cateId = "";
          this.serviceCate();
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 获取分类
    serviceCate() {
      serviceCate({
        type: this.tabCur,
      }).then((res) => {
        let obj = {
          id: "",
          name: "全部",
        };
        res.data.data.forEach((el, index) => {
          el.isEdit = false;
        });
        // res.data.data.unshift(obj)
        this.sortList = res.data.data;
        if (this.cateId === "") {
          this.cateId = res.data.data[0].id;
        }
        this.getList();
      });
    },
    // 获取列表
    getList() {
      if (!this.isScroll) return;
      speeChcraft({
        page: this.page,
        limit: this.limit,
        title: this.searchTxt,
        cate_id: this.cateId,
        type: this.tabCur,
      }).then((res) => {
        this.isScroll = res.data.length >= this.limit;
        res.data.forEach((el, index) => {
          el.isEdit = false;
        });
        this.page++;
        this.list = this.list.concat(res.data);
      });
    },
    // 修改话术
    updataMsg(item) {
      serviceCateUpdate(item.id, {
        title: item.title,
        cate_id: this.cateId,
        message: item.message,
      })
        .then((res) => {
          this.$Message.success("修改成功");
          item.isEdit = false;
        })
        .catch((error) => {
          this.$Message.error(error.msg);
          item.isEdit = true;
        });
    },
    // 添加框显示
    bindFocus() {
      this.list.forEach((el, item) => {
        el.isEdit = false;
      });
      this.addMsg.isEdit = true;
    },
    // 打开添加窗口
    openAddSort() {
      this.isAddSort = true;
      this.maskTitle = "添加分组";
      this.editObj.id = 0;
    },
    // 添加话术
    bindAdd() {
      addSpeeChcraft({
        title: this.addMsg.title,
        cate_id: this.addMsg.cateId,
        message: this.addMsg.message,
      })
        .then((res) => {
          this.addMsg.title = "";
          this.addMsg.message = "";
          this.addMsg.cateId = "";
          this.addMsg.isEdit = false;
          this.$Message.success(res.msg);
          res.data.isEdit = false;
          this.page = 1;
          this.list = [];
          this.isScroll = true;
          this.serviceCate();
        })
        .catch((error) => {
          this.$Message.error(error.msg);
        });
    },
    // 删除
    delMsg(row, tit, num, type) {
      let delfromData = {
        title: tit,
        num: num,
        url: `service/speechcraft/${row.id}`,
        method: "DELETE",
        ids: "",
        kefu: true,
      };
      this.$modalSure(delfromData)
        .then((res) => {
          this.$Message.success(res.msg);
          this.list.splice(num, 1);
        })
        .catch((res) => {
          this.$Message.error(res.msg);
        });
    },
    // 添加分类
    addServiceCate() {
      if (this.editObj.id) {
        editServiceCate(this.editObj.id, {
          name: this.classTitle,
          sort: this.classSort,
        })
          .then((res) => {
            this.classTitle = "";
            this.classSort = "";
            this.$Message.success(res.msg);
            this.isAddSort = false;
            this.page = 1;
            this.list = [];
            this.isScroll = true;
            this.serviceCate();
          })
          .catch((error) => {
            this.classTitle = "";
            this.classSort = "";
            this.$Message.error(res.msg);
          });
      } else {
        addServiceCate({
          name: this.classTitle,
          sort: this.classSort,
        })
          .then((res) => {
            this.classTitle = "";
            this.classSort = "";
            this.$Message.success(res.msg);
            this.isAddSort = false;
            this.page = 1;
            this.list = [];
            this.isScroll = true;
            this.serviceCate();
          })
          .catch((error) => {
            this.classTitle = "";
            this.classSort = "";
            this.$Message.error(res.msg);
          });
      }
    },
    // 编辑分类
    editSort(item) {
      this.classSort = item.sort;
      this.classTitle = item.name;
      this.isAddSort = true;
      this.maskTitle = "编辑分组";
      this.editObj = item;
    },
    handleReachBottom() {
      this.getList();
    },
    bindRadio(data) {
      this.$emit("activeTxt", data.message);
    },
  },
};
</script>

<style lang="stylus" scoped>
.head {
  padding: 15px 14px 0;

  .tab-bar {
    display: flex;

    .tab-item {
      margin-right: 24px;
      color: #999;
      font-size: 14px;
      font-weight: 500;

      &.on {
        color: #333333;
      }
    }
  }

  .search-box {
    margin-top: 15px;
  }
}

.main {
  display: flex;
  margin-top: 15px;
  height: 365px;

  .left-box {
    width: 106px;
    height: 100%;
    border-right: 1px solid #ECECEC;
    overflow: hidden;

    .left-item {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 36px;
      padding: 0 10px 0 14px;
      font-size: 13px;
      cursor: pointer;

      &.on {
        background: #F0FAFE;
        color: #1890FF;
        border-right: 2px solid #1890FF;

        .iconDot {
          z-index: 1;
          opacity: 1;
        }
      }

      &:nth-child(1).on, &:nth-child(2).on {
        .iconDot {
          display: none;
        }
      }

      .iconaddto {
        font-size: 12px;
      }

      .iconDot {
        z-index: -1;
        opacity: 0;
      }

      .edit-wrapper {
        z-index: 50;
        position: absolute;
        right: -2px;
        top: -4px;
        background: #fff;
        width: 80px;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.2);
        border-radius: 4px;

        .edit-item {
          padding: 8px 16px;
          color: #666 !important;
          cursor: pointer;
        }
      }

      .edit-bg {
        z-index: 40;
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: transparent;
      }
    }
  }

  .right-box {
    flex: 1;
    padding: 0 12px;
    overflow-x: hidden;

    .msg-item {
      margin-top: 12px;
      transition: all 0.3s ease;
      cursor: pointer;

      .box1 {
        position: relative;
        display: flex;

        .txt-box {
          flex: 1;
          font-size: 12px;
          color: #999999;

          .title {
            max-width: 370px;
            margin-right: 5px;
            color: #333;
            font-weight: 700;
          }
        }

        .edit-box {
          z-index: -1;
          opacity: 0;
          position: absolute;
          right: 7px;
          top: 0;
          width: 60px;
          height: 30px;
          background: #fff;

          .iconfont {
            margin: 0 8px;
            color: #000000;
            font-size: 16px;
            cursor: pointer;
          }
        }
      }

      .box2 {
        padding-bottom: 15px;
        border-radius: 5px;
        background: #F5F5F5;

        .input-box {
          border-bottom: 1px solid #EEEEEE;

          >>> .ivu-input {
            background: transparent;
            border: 0;
            border-radius: 0;
          }
        }

        .content {
          font-size: 12px;
          padding: 12px 11px 0;
          color: #333333;
        }

        .bom {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 0 20px 0 11px;
          margin-top: 10px;

          button {
            margin-left: 8px;
            width: 70px;
          }
        }
      }

      &:hover {
        transition: all 0.3s ease;

        .box1 .edit-box {
          z-index: 1;
          opacity: 1;
          transition: all 0.3s ease;
        }
      }
    }

    .add-box {
      border-radius: 0;
      margin-bottom: 10px;

      .box2 {
        padding-bottom: 0;
        border-radius: 0;

        .conBox {
          height: 0;
          overflow: hidden;

          &.active {
            animation: mymove 0.4s ease;
            animation-iteration-count: 1;
            animation-fill-mode: forwards;
          }
        }
      }
    }
  }
}

.right-scroll {
  >>> .ivu-scroll-container .ivu-scroll-loader:nth-child(1) {
    display: none;
  }
}

.class-box {
  .item {
    display: flex;
    align-items: center;
    margin-bottom: 20px;

    &:last-child {
      margin-bottom: 0;
    }

    input {
      flex: 1;
    }

    span {
      width: 80px;
      font-size: 12px;
    }
  }
}
</style>
<style>
@keyframes mymove {
  0% {
    height: 0;
  }
  100% {
    height: 150px;
  }
}
</style>
