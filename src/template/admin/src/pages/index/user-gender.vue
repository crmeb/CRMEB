<template>
    <div class="dashboard-console-user-gender">
        <Row type="flex" justify="center" align="middle">
            <Col span="8" class="ivu-text-center" style="color: #3399ff">
                <Avatar icon="ios-man" size="large" style="background-color: #d6f0ff;color: #3399ff"/>
                <div class="ivu-mt-8">男性 65%</div>
            </Col>
            <Col span="8" class="ivu-text-center" style="color: #be6be0;">
                <Avatar icon="ios-woman" size="large" style="color: #be6be0;background-color: #fdf0ff"/>
                <div class="ivu-mt-8">女性 25%</div>
            </Col>
            <Col span="8" class="ivu-text-center" style="color: #babdc3">
                <Avatar icon="md-man" size="large" style="color: #babdc3;background-color: #e6edf5;" />
                <div class="ivu-mt-8">未知 10%</div>
            </Col>
        </Row>
        <div class="dashboard-console-user-gender-grid ivu-mt-8">
            <Grid center square :col="25" padding="2px" :border="false">
                <GridItem v-for="item in 100" :key="item">
                    <span style="background-color: #3399ff" v-if="item <= 65"></span>
                    <span style="background-color: #be6be0" v-else-if="item <= 90"></span>
                    <span style="background-color: #babdc3" v-else></span>
                </GridItem>
            </Grid>
        </div>
    </div>
</template>
<script>
    export default {
        data () {
            return {

            }
        }
    }
</script>
<style lang="less">
    .dashboard-console-user-gender{
        &-grid{
            .ivu-grid-item-main{
                height: 100%;
            }
            span{
                display: block;
                height: 100%;
                border-radius: 2px;
                opacity: 0.75;
            }
        }
    }
</style>
