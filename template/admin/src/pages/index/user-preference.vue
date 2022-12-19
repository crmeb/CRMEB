<template>
  <div>
    <div style="height: 200px">
      <user-gender />
    </div>
    <div style="height: 200px">
      <div class="dashboard-console-user-preference"></div>
    </div>
  </div>
</template>
<script>
import userGender from './user-gender';
export default {
  components: { userGender },
};
</script>
<style lang="less">
.dashboard-console-user-preference {
  height: 100%;
  //background-image: url("../../../assets/images/user-preference.png");
  background-repeat: no-repeat;
  background-size: cover;
  background-position: center;
}
</style>
