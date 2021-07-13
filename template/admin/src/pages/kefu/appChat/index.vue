<template>
    <div class="chat-room">
        <div
                class="room"
                :class="{ win: !chatOptions.popup }"
                @click="roomClick"
        >
            <div v-drag class="head">
                <div class="image">
                    <img v-lazy="serviceData && serviceData.avatar" />
                </div>
                <div class="name">{{ serviceData && serviceData.nickname }}</div>
                <div :class="['iconfont', muted ? 'icon-shengyinjingyinxianxing' : 'icon-shengyinyinliang']" @click.stop="muted = !muted"></div>
                <div class="iconfont icon-guanbi5" @click.stop="close"></div>
            </div>
            <div class="main">
                <div class="chat">
                    <div class="record" @scroll="onScroll" ref="record">
                        <div id="chat_scroll" ref="scrollBox">
                            <Spin v-show="loading">
                                <Icon type="ios-loading" size=18 class="demo-spin-icon-load"></Icon>
                                <div>Loading</div>
                            </Spin>
                            <ul>
                                <template v-for="item in records">
                                    <li :key="item.id" :class="{ right: item.uid === serviceData.tourist_uid }" :id="`chat_${item.id}`">
                                        <div v-if="item.show" class="time-tag">
                                            {{ item.add_time }}
                                        </div>
                                        <div class="avatar">
                                            <img v-lazy="item.avatar" />
                                        </div>
                                        <div class="content" ref="chatContent">
                                            <div v-if="item.msn_type === 1" class="text"  v-html="item.msn"></div>
                                            <div v-if="item.msn_type === 2" class="image">
                                                <div class="text">
                                                    <i :class="`em ${item.msn}`"></i>
                                                </div>
                                            </div>
                                            <div v-if="item.msn_type === 3" class="image" v-viewer>
                                                <img v-lazy="item.msn" />
                                            </div>
                                            <div v-if="item.msn_type === 5" class="goods">
                                                <div class="thumb">
                                                    <img v-lazy="item.productInfo.image" />
                                                </div>
                                                <div class="intro">
                                                    <div class="name">
                                                        {{ item.productInfo.store_name }}
                                                    </div>
                                                    <div class="attr">
                                                        <span>库存：{{ item.productInfo.stock }}</span>
                                                        <span>销量：{{ item.productInfo.sales }}</span>
                                                    </div>
                                                    <div class="group">
                                                        <div class="money">
                                                            ￥{{ item.productInfo.price }}
                                                        </div>
                                                        <span style="cursor: pointer" @click.stop="onLook(item.productInfo.id)">查看商品 ></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <template v-if="item.msn_type === 6">
                                                <div
                                                        v-for="itm in item.orderInfo.cartInfo"
                                                        :key="itm.id"
                                                        class="order"
                                                >
                                                    <div class="thumb">
                                                        <img :src="itm.productInfo.image" />
                                                    </div>
                                                    <div class="intro">
                                                        <div class="name">
                                                            订单ID：{{ item.orderInfo.order_id }}
                                                        </div>
                                                        <div class="attr">商品数量：{{ itm.cart_num }}</div>
                                                        <div class="group">
                                                            <div class="money">
                                                                ￥{{ itm.productInfo.price }}
                                                            </div>
                                                            <nuxt-link
                                                                    target="_blank"
                                                                    :to="{
                                path: '/order_detail',
                                query: { orderId: item.orderInfo.order_id },
                              }"
                                                            >查看订单 ></nuxt-link
                                                            >
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>

                    </div>
                    <div class="editor">
                        <div class="editor-hd">
                            <div>
                                <button class="emoji-btn" title="表情" @click.stop="emojiSwitch">
                                    <span class="iconfont iconbiaoqing1"></span>
                                </button>
                                <button title="图片" v-if="kufuToken">
                                    <Upload :show-upload-list="false" :action="uploadAction"
                                            :before-upload="beforeUpload"
                                            :format="['jpg','jpeg','png','gif']"
                                            :on-format-error="handleFormatError"
                                            :data="uploadData"
                                            :on-success="uploadSuccess"
                                            :on-error="uploadError"
                                            >
                                        <span class="iconfont icontupian1"></span>
                                    </Upload>
                                </button>
                            </div>
<!--                            <div>-->
<!--                                <button class="end" @click="chatEnd">-->
<!--                                    <i class="iconfont icon-guanji"></i>结束-->
<!--                                </button>-->
<!--                            </div>-->
                            <!-- 表情 -->
                            <div class="emoji-panel" v-if="emojiShow">
                                <i class="em" :class="emoji" @click.stop="selectEmoji(emoji)" v-for="(emoji, index) in emojiList" :key="index"></i>
                            </div>
                        </div>
                        <div class="editor-bd">
              <textarea
                      v-model="chatCont"
                      placeholder="请输入文字内容"
                      @keydown.enter="ctrlEnter"
              ></textarea>
                        </div>
                        <div class="editor-ft">
                            <button :disabled="!chatCont" @click.stop="sendMessage">发送</button>
                        </div>
                    </div>
                </div>
                <div class="notice">
                    <div v-if="notice" class="rich" v-html="notice"></div>
                    <div class="copy">
                        <a href="http://www.crmeb.com/" target="_blank">CRMEB提供技术支持</a>
                    </div>
                </div>
            </div>
            <audio ref="audio" :src="audioSrc"></audio>
        </div>
        <feed-back   @closeChange="closeChange($event)"  v-if="change" :change="change"></feed-back>
    </div>
</template>

<script>
    import "emoji-awesome/dist/css/google.min.css";
    import emojiList from "@/utils/emoji";
    import {Socket} from "@/libs/socket";
    import Setting from "@/setting";
    import Cookies from 'js-cookie';
    import { chatListApi, serviceListApi, getAdvApi, serviceList, getOrderApi,productApi } from '@/api/kefu';
    import feedBack from './feedback';
    const chunk = function(arr, num) {
        num = num * 1 || 1;
        var ret = [];
        arr.forEach(function(item, i) {
            if (i % num === 0) {
                ret.push([]);
            }
            ret[ret.length - 1].push(item);
        });
        return ret;
    };
    export default {
        name: "ChatRoom",
        auth: false,
        components:{
            feedBack
        },
        props: {
            chatOptions: {
                type: Object,
                default: function () {
                    return {
                        show: false,
                    };
                },
            },
        },
        directives: {
            drag: {
                inserted: function (el) {
                    let x = 0;
                    let y = 0;
                    let l = 0;
                    let t = 0;
                    let isDown = false;
                    el.onmousedown = function (e) {
                        x = e.clientX;
                        y = e.clientY;
                        l = el.parentNode.offsetLeft;
                        t = el.parentNode.offsetTop;
                        isDown = true;
                        el.style.cursor = "move";
                        window.onmousemove = function (e) {
                            if (isDown == false) {
                                return;
                            }
                            let nx = e.clientX;
                            let ny = e.clientY;
                            let nl = nx - (x - l);
                            let nt = ny - (y - t);
                            el.parentNode.style.left = nl + "px";
                            el.parentNode.style.top = nt + "px";
                        };
                        window.onmouseup = function () {
                            isDown = false;
                            el.style.cursor = "default";
                            window.onmousemove = null;
                            window.onmouseup = null;
                        };
                        return false;
                    };
                },
            },
        },
        data() {
            return {
                locations: `${location.origin}`,
                change: false,
                emojiGroup: chunk(emojiList, 20), // 表情列表
                emojiList: emojiList,
                emojiShow: false,
                recordList: [],
                limit: 20,
                loading: false,
                finished: false,
                chatCont: "",
                service: null,
                serviceData: {},
                uploadAction: '',
                notice: "",
                audio: null,
                muted: false,
                audioSrc: '',
                upperId: 0,
                uploadData: {},
                is_tourist: 1, // 0登录状态，1未登录状态游客
                text:'',
                isLoad: false,
                page: 1,
                tourist_avatar: '', //游客头像
                tourist_uid: '', //游客id
                toUid: '', //客服id
                kufuToken: '' // token
            };
        },
        watch: {
            muted(value) {
                this.audio.muted = value;
            }
        },
        computed: {
            records() {
                return this.recordList.map((item, index) => {
                    if (index) {
                        if (
                            new Date(item.add_time) -
                            new Date(this.recordList[index - 1].add_time) >=
                            300000
                        ) {
                            item.show = true;
                        } else {
                            item.show = false;
                        }
                    } else {
                        item.show = false;
                    }
                    return item;
                });
            },
        },
        created() {
            if(location.href.indexOf('kefu')!=-1) this.uploadAction = Setting.apiBaseURL.replace(/adminapi/, "kefuapi")+'/tourist/upload';
            let token = Cookies.get('auth._token.local1');
            this.kufuToken = token?token.split("Bearer ")[1]: '';
        },
        mounted() {
            let that = this
            window.addEventListener('click',function(){
                that.emojiShow = false
            });
            if (this.$wechat._isMobile()) this.$router.replace('/kefu/mobile_user_chat');
            this.getNotice();
            Socket.then(ws=>{
                if(this.kufuToken){
                    ws.send({
                        type: 'login',
                        data: this.kufuToken
                    })
                }
                this.getService();
                ws.$on(["reply", "chat"], data => {
                    if(data.msn_type == 1){
                        data.msn = this.replace_em(data.msn)
                    }
                    this.recordList.push(data)
                    setTimeout(res=>{
                        this.$nextTick(function () {
                            this.$refs.record.scrollTop = this.$refs.record.scrollHeight - this.$refs.record.clientHeight;
                        });
                    },300)

                });
                // 监听客服转接
                ws.$on('to_transfer', data => {
                    this.toUid = data.toUid;
                    ws.send({
                        data: {
                            id: this.toUid
                        },
                        type: 'to_chat'
                    });
                });
                ws.$on("socket_error", () => {
                    this.$Message.error('连接失败')
                });
                ws.$on("err_tip", data => {
                    this.$Message.error(data.msg)
                });
                ws.$on("success", data => {
                    this.is_tourist = 0;
                });

            });
            this.text = this.replace_em('[em-smiling_imp]')
        },
        beforeDestroy() {
            this.socket.close();
        },
        methods: {
            onLook(id){
                window.open(`${location.origin}/home/goods_detail/${id}`);
            },
            // 关闭
            closeChange(msg){
                this.change = msg;
            },
            // 统一发送处理
            sendMsg(msn,type) {
                let obj = {
                    type:'chat',
                    data:{
                        msn,
                        type,
                        is_tourist: this.is_tourist,
                        to_uid: this.toUid,
                        tourist_uid: this.tourist_uid,
                        tourist_avatar: this.tourist_avatar,
                        form_type: this.$wechat.isWeixin() ? 1 : 3,
                    }
                }
                Socket.then(ws=> {
                    ws.send(obj)
                })
            },
            // 随机客服
            getService() {
                serviceListApi({token: this.kufuToken || ''}).then((res) => {
                    this.serviceData = res.data;
                    this.upperId = 0
                    this.toUid = res.data.uid;
                    this.tourist_uid = res.data.tourist_uid;
                    this.tourist_avatar = res.data.tourist_avatar;
                    let obj = {
                        data: {
                            id: res.data.uid,
                            tourist_uid: this.tourist_uid
                        },
                        type: 'to_chat'
                    }
                    Socket.then(ws=> {
                        ws.send(obj)
                    })
                    if(this.kufuToken){
                       this.getRecordList();
                    }
                }).catch((err) => {
                    this.$Message.error(err.msg);
                    this.change = true;
                });
            },
            roomClick(event) {
                // if (
                //     !event.target.classList.contains("emoji-panel") &&
                //     !event.target.classList.contains("emoji-btn") &&
                //     !event.target.classList.contains("icon-biaoqing") &&
                //     this.emojiShow
                // ) {
                //     this.emojiShow = false;
                // }
            },
            // enter 发送
            ctrlEnter(e) {
                if (e.keyCode == 13) {
                    e.preventDefault();
                }
                if (this.chatCont.trim()) {
                    this.sendMessage();
                }
            },
            // 关闭聊天窗口
            close() {
                this.$emit("chat-close");
            },
            // 选择表情
            selectEmoji(data) {
                let val = `[${data}]`
                this.chatCont += val
                this.emojiShow = false;
            },
            // 聊天表情转换
            replace_em(str) {
                str = str.replace(/\[em-([a-z_]*)\]/g, "<span class='em em-$1'/></span>");
                return str;
            },
            onScroll(event) {
                if (event.target.scrollTop <=30) {
                    if(this.kufuToken){
                        this.getRecordList();
                    }
                }
            },
            // 聊天记录
            getRecordList() {
                if (this.loading) {
                    return;
                }
                if (this.finished) {
                    return;
                }
                this.loading = true;
                chatListApi({
                    uid: this.serviceData.uid,
                    limit: this.limit,
                    upperId:this.upperId,
                    token: this.kufuToken
                }).then((res) => {
                   if(res.data.length === 0) return this.loading = false;
                    res.data.forEach(el=>{
                        if(el.msn_type == 1){
                            el.msn = this.replace_em(el.msn)
                        }
                    })
                    let selector = ''
                    if(this.upperId == 0){
                        selector = `chat_${res.data[res.data.length-1].id}`;
                    }else{
                        selector = `chat_${this.recordList[0].id}`;
                    }
                    this.recordList = [...res.data,...this.recordList];
                    this.upperId = res.data.length>0?res.data[0].id : 0
                    this.loading = false;
                    this.finished = res.data.length < this.limit;
                    this.$nextTick(function () {
                        this.setPageScrollTo(selector)
                    });
                }).catch((err) => {
                    this.$Message.error(err.msg);
                    this.loading = false;
                })
            },
            // 设置页面滚动位置
            setPageScrollTo(selector){
                this.$nextTick(()=>{
                    if(selector){
                        setTimeout(()=>{
                            let num = parseFloat(document.getElementById(selector).offsetTop)-60
                            this.$refs.record.scrollTop = num
                        },0)
                    }else{
                        var container =document.querySelector("#chat_scroll");
                        this.$refs.record.scrollTop = container.offsetHeight
                        setTimeout(res=>{
                            if( this.$refs.record.scrollTop != this.$refs.scrollBox.offsetHeight){
                                this.$refs.record.scrollTop = document.querySelector("#chat_scroll").offsetHeight
                            }
                        },300)
                    }
                })
            },
            // 表情包显示隐藏
            emojiSwitch() {
                this.emojiShow = !this.emojiShow;
            },
            // 发送消息
            sendMessage() {
                this.sendMsg(this.chatCont, 1)
                this.chatCont = "";
            },
            chat(data) {
                if (data.uid != this.$auth.user.uid && this.audio) {
                    this.audio.play();
                }
                this.recordList.push(data);
                this.$nextTick(() => {
                    this.$refs.record.scrollTop =
                        this.$refs.record.scrollHeight - this.$refs.record.clientHeight;
                });
            },
            sendGoods() {
                if (this.chatOptions.goodsId) {
                    Socket.then(ws => {
                        ws.send({
                            data: {
                                msn: this.chatOptions.goodsId,
                                type: 5,
                                to_uid: this.toUid,
                            },
                            type: "to_chat"
                        })
                    })
                }
            },
            sendOrder() {
                if (this.chatOptions.orderId) {
                    Socket.then(ws=> {
                        ws.send({
                                data: {
                                    msn: this.chatOptions.orderId,
                                    type: 6,
                                    to_uid: this.toUid,
                                },
                            type: "to_chat"
                        })
                    })
                }
            },
            chatEnd() {
                if (navigator.userAgent.indexOf("MSIE") > 0) {
                    if (navigator.userAgent.indexOf("MSIE 6.0") > 0) {
                        window.opener = null;
                        window.close();
                    } else {
                        window.open('', '_top');
                        window.top.close();
                    }
                }
                else if (navigator.userAgent.indexOf("Firefox") > 0) {
                    window.location.href = 'about:blank ';
                } else {
                    window.opener = null;
                    window.open('', '_self', '');
                    window.close();
                }
            },
            // 广告
            getNotice() {
                getAdvApi().then((res) => {
                    this.notice = res.data.content;
                });
            },
            beforeUpload(file) {
                this.uploadData = {
                    filename: file,
                    token: this.kufuToken
                }
                let promise = new Promise((resolve) => {
                    this.$nextTick(function () {
                        resolve(true);
                    });
                });
                return promise;
            },
            handleFormatError (file) {
                this.$Message.error("上传图片只能是 jpg、jpg、jpeg、gif 格式!");
            },
            uploadSuccess(res) {
                this.sendMsg(res.data.url,3)
            },
            uploadError(error) {
                this.$Message.error(error);
            }
        }
    };
</script>

<style lang="less" scoped>
    @import "../../../styles/emoji-awesome/css/google.min.css";
    li{
        list-style-type:none;
    }
    .chat-room {
        .room {
            border-radius:10px;
            position: fixed;
            top: calc(50% - 327px);
            left: calc(50% - 365px);
            z-index: 999;
            display: flex;
            flex-direction: column;
            width: 730px;
            height: 654px;
            background-color: #ffffff;
            overflow: hidden;
            box-shadow: 1px 1px 15px 0 rgba(0, 0, 0, 0.1);

            &.win {
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }

            .head {
                display: flex;
                align-items: center;
                height: 50px;
                padding-right: 15px;
                padding-left: 20px;
                background: linear-gradient(270deg, #1890ff 0%, #3875ea 100%);

                .image {
                    width: 36px;
                    height: 36px;
                    border-radius: 50%;
                    overflow: hidden;

                    img {
                        display: block;
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                    }
                }

                .name {
                    flex: 1;
                    min-width: 0;
                    margin-left: 15px;
                    font-size: 16px;
                    color: #ffffff;
                }

                .iconfont {
                    width: 25px;
                    height: 25px;
                    font-size: 16px;
                    line-height: 25px;
                    text-align: center;
                    color: #ffffff;
                    cursor: pointer;
                }
            }

            .main {
                flex: 1;
                display: flex;
                min-height: 0;

                .chat {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    min-width: 0;
                }

                .record {
                    flex: 1;
                    min-height: 0;
                    overflow-x: hidden;
                    overflow-y: auto;

                    &::-webkit-scrollbar {
                        display: none;
                    }

                    ul {
                        padding: 20px;
                    }

                    li {
                        ~ li {
                            margin-top: 20px;
                        }

                        &::after {
                            content: "";
                            display: block;
                            height: 0;
                            clear: both;
                            visibility: hidden;
                        }

                        &.right {
                            .avatar {
                                float: right;
                            }

                            .content {
                                text-align: right;

                                > div {
                                    text-align: left;
                                }
                            }
                        }
                    }

                    .time-tag {
                        padding-top: 10px;
                        padding-bottom: 30px;
                        text-align: center;
                        color: #999999;
                    }

                    .avatar {
                        float: left;
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        overflow: hidden;

                        &.right {
                            float: right;
                        }

                        img {
                            display: block;
                            width: 100%;
                            height: 100%;
                            object-fit: cover;
                        }
                    }

                    .content {
                        margin-right: 56px;
                        margin-left: 56px;
                    }

                    .text {
                        display: inline-block;
                        min-height: 41px;
                        padding: 10px 12px;
                        border-radius: 10px;
                        background-color: #f5f5f5;
                        font-size: 15px;
                        line-height: 21px;
                        color: #000000;
                    }

                    .image {
                        display: inline-block;
                        max-width: 50%;
                        border-radius: 10px;
                        overflow: hidden;

                        img {
                            display: block;
                            max-width: 100%;
                        }
                    }

                    .goods,
                    .order {
                        display: inline-flex;
                        align-items: center;
                        width: 320px;
                        padding: 10px 13px;
                        border-radius: 10px;
                        background-color: #f5f5f5;
                    }

                    .thumb {
                        width: 60px;
                        height: 60px;
                        border-radius: 5px;
                        overflow: hidden;

                        img {
                            display: block;
                            width: 100%;
                            height: 100%;
                        }
                    }

                    .intro {
                        flex: 1;
                        min-width: 0;
                        margin-left: 10px;

                        .name {
                            overflow: hidden;
                            white-space: nowrap;
                            text-overflow: ellipsis;
                            font-size: 15px;
                            color: #000000;
                        }

                        .attr {
                            margin-top: 5px;
                            font-size: 12px;
                            color: #999999;

                            span {
                                vertical-align: middle;

                                ~ span {
                                    margin-left: 10px;
                                }
                            }
                        }

                        .group {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-top: 5px;

                            .money {
                                font-size: 14px;
                                color: #ff0000;
                            }

                            a {
                                font-size: 12px;
                                color: #1890ff;
                            }
                        }
                    }
                }

                .editor {
                    display: flex;
                    flex-direction: column;
                    height: 162px;
                    border-top: 1px solid #ececec;

                    > div {
                        &:first-child {
                            font-size: 0;
                        }
                    }

                    button {
                        border: none;
                        background: none;
                        outline: none;

                        ~ button {
                            margin-left: 20px;
                        }

                        &.end {
                            font-size: 15px;
                        }

                        &:hover {
                            color: #1890ff;

                            .iconfont {
                                color: #1890ff;
                            }
                        }
                    }

                    .editor-hd {
                        position: relative;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        height: 50px;
                        padding-right: 20px;
                        padding-left: 20px;

                        .iconfont {
                            line-height: 1;
                            color: #333333;
                        }

                        .emoji-panel {
                            position: absolute;
                            bottom: 100%;
                            left: 5px;
                            width: 390px;
                            padding-bottom: 10px;
                            border: 1px solid #ececec;
                            margin-bottom: 5px;
                            background-color: #ffffff;
                            box-shadow: 1px 0 16px 0 rgba(0, 0, 0, 0.05);

                            .em {
                                width: 28px;
                                height: 28px;
                                padding: 4px;
                                margin-top: 10px;
                                margin-left: 10px;
                                box-sizing: border-box;

                                &:hover {
                                    background-color: #ececec;
                                }
                            }
                        }
                    }

                    .icon-biaoqing1,
                    .icon-tupian1 {
                        font-size: 22px;
                    }

                    .icon-guanji {
                        margin-right: 5px;
                        font-size: 15px;
                    }

                    .editor-bd {
                        flex: 1;
                        min-height: 0;

                        textarea {
                            display: block;
                            width: 100%;
                            height: 100%;
                            padding-right: 20px;
                            padding-left: 20px;
                            border: none;
                            outline: none;
                            resize: none;
                            white-space: pre-wrap;
                            overflow-wrap: break-word;

                            &::-webkit-scrollbar {
                                display: none;
                            }
                        }
                    }

                    .editor-ft {
                        display: flex;
                        justify-content: flex-end;
                        align-items: center;
                        padding-right: 20px;
                        padding-bottom: 20px;

                        button {
                            width: 68px;
                            height: 26px;
                            border: none;
                            border-radius: 3px;
                            background-color: #3875ea;
                            outline: none;
                            font-size: 13px;
                            color: #ffffff;

                            &:disabled {
                                background-color: #cccccc;
                            }
                        }
                    }
                }

                .notice {
                    display: flex;
                    flex-direction: column;
                    width: 260px;
                    border-left: 1px solid #ececec;

                    .rich {
                        flex: 1;
                        min-height: 0;
                        padding: 18px 18px 0;
                        overflow-x: hidden;
                        overflow-y: auto;

                        &::-webkit-scrollbar {
                            display: none;
                        }

                        /deep/ img {
                            width: 100%;
                        }

                        /deep/ video {
                            width: 100%;
                        }
                    }

                    .copy {
                        padding-top: 15px;
                        padding-bottom: 15px;
                        font-size: 12px;
                        text-align: center;
                        a {
                            color: #cccccc!important;
                            text-decoration: none;
                        }
                    }
                }
            }
        }
    }
</style>
