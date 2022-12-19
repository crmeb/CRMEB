// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

export default {
  data() {
    return {
      disabled: false,
      text: this.$t('验证码')
    };
  },
  methods: {
    sendCode() {
      if (this.disabled) return;
      this.disabled = true;
      let n = 60;
      this.text = this.$t('剩余') + n + "s";
      const run = setInterval(() => {
        n = n - 1;
        if (n < 0) {
          clearInterval(run);
        }
        this.text = this.$t('剩余') + n + "s";
        if (this.text < this.$t('剩余') + 0 + "s") {
          this.disabled = false;
          this.text = this.$t('重新获取');
        }
      }, 1000);
    }
  }
};