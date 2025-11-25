// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import { getColorChange } from '@/api/diy';
export default {
  data() {
    return {
      current: 3,
      colorStyle: {},
    };
  },
  created() {
    this.getInfo();
  },
  methods: {
    getInfo() {
      getColorChange('color_change')
        .then((res) => {
          this.current = res.data.status ? res.data.status : 3;
          let colorList = [
            {
              theme: '#42CA4D',
              priceColor: '#FF7600',
              minorColor: 'rgba(108, 198, 94, 0.5)',
              minorColorT: 'rgba(66, 202, 77, 0.1)',
              bntColor: '#FE960F',
              gradient: '#4DEA4D',
            },
            {
              theme: '#e93323',
              priceColor: '#e93323',
              minorColor: 'rgba(233, 51, 35, 0.5)',
              minorColorT: 'rgba(233, 51, 35, 0.1)',
              bntColor: '#FE960F',
              gradient: '#FF7931',
            },
            {
              theme: '#1DB0FC',
              priceColor: '#FD502F',
              minorColor: 'rgba(58, 139, 236, 0.5)',
              minorColorT: 'rgba(9, 139, 243, 0.1)',
              bntColor: '#22CAFD',
              gradient: '#5ACBFF',
            },
            {
              theme: '#FF448F',
              priceColor: '#FF448F',
              minorColor: 'rgba(255, 68, 143, 0.5)',
              minorColorT: 'rgba(255, 68, 143, 0.1)',
              bntColor: '#282828',
              gradient: '#FF67AD',
            },
            {
              theme: '#FE5C2D',
              priceColor: '#FE5C2D',
              minorColor: 'rgba(254, 92, 45, 0.5)',
              minorColorT: 'rgba(254, 92, 45, 0.1)',
              bntColor: '#FDB000',
              gradient: '#FF9451',
            },
            {
              theme: '#E0A558',
              priceColor: '#DA8C18',
              minorColor: 'rgba(224, 165, 88, 0.5)',
              minorColorT: 'rgba(224, 165, 88, 0.1)',
              bntColor: '#1A1A1A',
              gradient: '#FFCD8C',
            },
          ];
          switch (res.data.status) {
            case 1:
              this.colorStyle = colorList[2];
              break;
            case 2:
              this.colorStyle = colorList[0];
              break;
            case 3:
              this.colorStyle = colorList[1];
              break;
            case 4:
              this.colorStyle = colorList[3];
              break;
            case 5:
              this.colorStyle = colorList[4];
              break;
            case 6:
              this.colorStyle = colorList[5];
              break;
            default:
              this.colorStyle = colorList[1];
              break;
          }
        })
        .catch((err) => {
          this.$Message.error(err.msg);
        });
    },
  },
};
