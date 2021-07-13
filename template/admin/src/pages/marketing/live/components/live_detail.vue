<template>
    <div class="divBox">
        <div>
            <div class="box-container">
                <div class="list sp">
                    <label class="name">直播间名称：</label>
                    <span class="info">{{ FormData.name }}</span>
                </div>
                <div class="list sp">
                    <label class="name">主播微信号：</label>
                    <span class="info">{{ FormData.anchor_wechat }}</span>
                </div>
                <div class="list sp">
                    <label class="name">直播间ID：</label>
                    <span class="info">{{ FormData.room_id }}</span>
                </div>
                <div class="list sp">
                    <label class="name">主播昵称：</label>
                    <span class="info">{{ FormData.anchor_name }}</span>
                </div>
                <div class="list sp">
                    <label class="name">手机号：</label>
                    <span class="info">{{ FormData.phone }}</span>
                </div>
                <div class="list sp">
                    <label class="name">直播状态：</label>
                    <span class="info">{{ FormData.live_status | liveReviewStatusFilter }}</span>
                </div>
                <div class="list sp">
                    <label class="name">直播开始时间：</label>
                    <span class="info">{{ FormData.start_time }}</span>
                </div>
                <div class="list sp">
                    <label class="name">直播结束时间：</label>
                    <span class="info">{{ FormData.end_time }}</span>
                </div>
                <div class="list sp">
                    <label class="name">直播间类型：</label>
                    <span class="info">{{ FormData.type | broadcastType }}</span>
                </div>
                <div class="list sp">
                    <label class="name">显示类型：</label>
                    <span class="info">{{ FormData.screen_type | broadcastDisplayType }}</span>
                </div>
                <div class="list sp image">
                    <label class="name">背景图：</label>
                    <img style="max-width: 150px; height: 80px;" :src="FormData.cover_img">
                </div>
                <div class="list sp image">
                    <label class="name">分享图：</label>
                    <img style="max-width: 150px; height: 80px;" :src="FormData.share_img">
                </div>
                <div class="list sp">
                    <label class="name">是否关闭点赞：</label>
                    <span class="info blue">{{ FormData.close_like | filterClose }}</span>
                </div>
                <div class="list sp">
                    <label class="name">是否关闭货架：</label>
                    <span class="info blue">{{ FormData.close_goods | filterClose }}</span>
                </div>
                <div class="list sp">
                    <label class="name">是否关闭评论：</label>
                    <span class="info blue">{{ FormData.close_comment | filterClose }}</span>
                </div>

                <div class="list">
                    <label class="name">是否显示直播回放：</label>
                    <span class="info blue">{{ FormData.replay_status | filterClose }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { liveDetail } from '@/api/live'
    export default {
        name: "live_detail",
        data() {
            return {
                option: {
                    form: {
                        labelWidth: '150px'
                    }
                },
                FormData: {},
                loading: false
            }
        },
        methods:{
            getData(id) {
                this.loading = true
                liveDetail(id)
                    .then((res) => {
                        this.FormData = res.data
                        this.loading = false
                    })
                    .catch(error => {
                        this.$Message.error(error.msg)
                        this.loading = false
                    })
            },
        }
    }
</script>

<style scoped>
    .box-container {
        overflow: hidden;
    }
    .box-container .list {
        float: left;
        line-height: 40px;
    }
    .box-container .sp {
        width: 50%;
    }
    .box-container .sp3 {
        width: 33.3333%;
    }
    .box-container .sp100 {
        width: 100%;
    }
    .box-container .list .name {
        display: inline-block;
        width: 150px;
        text-align: right;
        color: #606266;
    }
    .box-container .list .blue {
        color: #1890ff;
    }
    .box-container .list.image {
        margin-bottom: 40px;
    }
    .box-container .list.image img {
        position: relative;
        top: 40px;
    }
    .el-textarea {
        width: 400px;
    }
</style>
