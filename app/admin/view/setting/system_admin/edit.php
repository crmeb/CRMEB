<!DOCTYPE html>
<html lang="zh-CN">
<head>
    {include file="public/head"}
    <title>{$title}</title>
</head>
<body>
<div id="form-add" class="mp-form" v-cloak="">
    <?php /*  <i-form ref="formValidate" :model="formValidate" :rules="ruleValidate" :label-width="80">
          <form-Item label="菜单名称" prop="name">
              <i-input v-model="formValidate.name" placeholder="请输入姓名"></i-input>
          </form-Item>
          <form-Item label="邮箱" prop="mail">
              <i-input v-model="formValidate.mail" placeholder="请输入邮箱"></i-input>
          </form-Item>
          <form-Item label="城市" prop="city">
              <i-select v-model="formValidate.city" placeholder="请选择所在地">
                  <i-option value="beijing">北京市</i-option>
                  <i-option value="shanghai">上海市</i-option>
                  <i-option value="shenzhen">深圳市</i-option>
              </i-select>
          </form-Item>
          <Form-Item label="选择日期">
              <Row>
                  <i-col span="11">
                      <form-Item prop="date">
                          <Date-Picker type="date" placeholder="选择日期" v-model="formValidate.date"></Date-Picker>
                      </form-Item>
                  </i-col>
                  <i-Col span="1" style="text-align: center">-</i-Col>
                  <i-Col span="11">
                      <form-Item prop="time">
                          <Time-Picker type="time" placeholder="选择时间" v-model="formValidate.time"></Time-Picker>
                      </form-Item>
                  </i-Col>
              </Row>
          </Form-Item>
          <form-Item label="性别" prop="gender">
              <Radio-Group v-model="formValidate.gender">
                  <Radio label="male">男</Radio>
                  <Radio label="female">女</Radio>
              </Radio-Group>
          </form-Item>
          <form-Item label="爱好" prop="interest">
              <Checkbox-Group v-model="formValidate.interest">
                  <Checkbox label="吃饭"></Checkbox>
                  <Checkbox label="睡觉"></Checkbox>
                  <Checkbox label="跑步"></Checkbox>
                  <Checkbox label="看电影"></Checkbox>
              </Checkbox-Group>
          </form-Item>
          <component :is="componentId"  label="介绍" prop="desc">
              <component :is="componentIs" v-model="formValidate.desc" type="textarea" :autosize="{minRows: 2,maxRows: 5}" placeholder="请输入..."></component>
          </component>
          <form-Item label="介绍" prop="desc">
              <i-input v-model="formValidate.desc" type="textarea" :autosize="{minRows: 2,maxRows: 5}" placeholder="请输入..."></i-input>
          </form-Item>
          <form-Item>
              <i-button type="primary" @click="handleSubmit('formValidate')">提交</i-button>
              <i-button type="ghost" @click="handleReset('formValidate')" style="margin-left: 8px">重置</i-button>
          </form-Item>
      </i-form>
      <m-mycom>
      </m-mycom>  */ ?>
    <form-builder></form-builder>

</div>
<script>
    var _vm ;
    $eb = parent._mpApi;

    mpFrame.start(function(Vue){
        require(['axios','system/util/mpFormBuilder'],function(axios,mpFormBuilder){
//            axios.post('{$read}').then((result)=>{
            Vue.use(mpFormBuilder,$eb,<?php echo $read; ?>,{
                action:'{$update}'
            });
            new Vue({
                el:"#form-add",
                mounted:function(){
                }
            });
//            })
        });
    });
</script>
</body>
