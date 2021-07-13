<template>
    <div>
        <Form ref="formValidate" :model="formValidate" :rules="ruleInline" inline>
            <FormItem label="备注：" prop="con" class="form-item" label-position="right" :label-width="100">
                <Input v-model="formValidate.con" placeholder="请输入备注" style="width: 100%"  maxlength="200" type="textarea" :rows="5" show-word-limit></Input>
            </FormItem>
            <div class="mask-footer">
                <Button type="primary" @click="handleSubmit('formValidate')">提交</Button>
                <Button @click="close">取消</Button>
            </div>
        </Form>
    </div>
</template>

<script>
    import { orderRemark } from '@/api/kefu'
    export default {
        name: "remarks",
        props:{
            remarkId:{
                type:String,
                default:''
            }
        },
        data(){
            return {
                formValidate: {
                    con:''
                },
                ruleInline:{
                    con: [
                        { required: true, message: '请输入备注信息', trigger: 'change' }
                    ],
                },
                formValidate:{
                    con:''
                }
            }
        },
        methods:{
            handleSubmit(name){
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        orderRemark({
                            order_id:this.remarkId,
                            remark:this.formValidate.con
                        }).then(res=>{
                            this.$Message.success(res.msg)
                            this.$emit('remarkSuccess')
                        }).catch(error=>{
                            this.$Message.error(error.msg)
                        })
                    } else {

                    }
                })
            },
            close(){
                this.$emit('close')
            }
        }
    }
</script>

<style scoped>
.form-item{
    width:100%;
}
</style>
