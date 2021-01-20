<template>
    <div>
        <Row class="expand-row">
            <Col span="6">
                <span class="expand-key">首次访问：</span>
                <span class="expand-value"> {{row.add_time | formatDate}}</span>
            </Col>
            <Col span="6">
                <span class="expand-key">近次访问：</span>
                <span class="expand-value">{{row.last_time  | formatDate}}</span>
            </Col>
            <Col span="6">
                <span class="expand-key">身份证号：</span>
                <span class="expand-value">{{row.card_id}}</span>
            </Col>
            <Col span="6">
                <span class="expand-key">真实姓名：</span>
                <span class="expand-value">{{row.real_name}}</span>
            </Col>
        </Row>
        <Row class="expand-row">
            <Col span="6">
                <span class="expand-key">标签：</span>
                <span class="expand-value">{{row.labels}}</span>
            </Col>
            <Col span="6">
                <span class="expand-key">生日：</span>
                <span class="expand-value">{{row.birthday}}</span>
            </Col>
            <Col span="6">
                <span class="expand-key">地址：</span>
                <span class="expand-value">{{row.addres}}</span>
            </Col>
        </Row>
        <Row class="expand-row">
            <Col span="6">
                <span class="expand-key">备注：</span>
                <span class="expand-value">{{row.mark}}</span>
            </Col>
        </Row>
    </div>
</template>

<script>
    import { formatDate } from '@/utils/validate'
    export default {
        name: 'table-expand',
        filters: {
            formatDate (time) {
                if (time !== 0) {
                    let date = new Date(time * 1000)
                    return formatDate(date, 'yyyy-MM-dd hh:mm')
                }
            }
        },
        props: {
            row: Object
        }
    }
</script>

<style scoped>
    .expand-row{
        margin-bottom: 16px;
    }
</style>
