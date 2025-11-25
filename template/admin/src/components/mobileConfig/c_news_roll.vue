<template>
  <div class="mobile-config">
    <Form ref="formInline">
      <div v-for="(item, key) in rCom" :key="key">
        <component
          :is="item.components.name"
          :configObj="configObj"
          ref="childData"
          :configNme="item.configNme"
          :key="key"
          @getConfig="getConfig"
          :index="activeIndex"
          :num="item.num"
        ></component>
      </div>
      <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
    </Form>
  </div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import rightBtn from '@/components/rightBtn/index.vue';
import { mapMutations } from 'vuex';
export default {
  name: 'c_news_roll',
  componentsName: 'home_news_roll',
  cname: '新闻公告',
  props: {
    activeIndex: {
      type: null,
    },
    num: {
      type: null,
    },
    index: {
      type: null,
    },
  },
  components: {
    ...toolCom,
    rightBtn,
  },
  data() {
    return {
      hotIndex: 1,
      configObj: {}, // 配置对象
      rCom: [
        {
          components: toolCom.c_set_up,
          configNme: 'setUp',
        },
      ], // 当前页面组件
      rComContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleLeft',
        },
        {
          components: toolCom.c_radio,
          configNme: 'styleConfig',
        },
        {
          components: toolCom.c_title,
          configNme: 'titleStyle',
        },
        {
          components: toolCom.c_radio,
          configNme: 'titleConfig',
        },
      ],
      sixContentImg: [
        {
          components: toolCom.c_upload_img,
          configNme: 'imgConfig',
        },
      ],
      sixContentTxt: [
        {
          components: toolCom.c_input_item,
          configNme: 'titleTxtConfig',
        },
      ],
      oneContent: [
        {
          components: toolCom.c_radio,
          configNme: 'rollConfig',
        },
      ],
      twoContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleButton',
        },
        {
          components: toolCom.c_radio,
          configNme: 'buttonConfig',
        },
      ],
      threeContent: [
        {
          components: toolCom.c_input_item,
          configNme: 'linkConfig',
        },
      ],
      fourContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleContent',
        },
        {
          components: toolCom.c_product,
          configNme: 'listConfig',
        },
      ],
      fiveContent: [
        {
          components: toolCom.c_input_item,
          configNme: 'textConfig',
        },
      ],
      oneStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleRight',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'newsColor',
        },
      ],
      oneBntStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'bntColor',
        },
      ],
      fourStyle: [
        {
          components: toolCom.c_radio,
          configNme: 'toneConfig',
        },
      ],
      twoStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'titleBgColor',
        },
      ],
      threeStyle: [
        {
          components: toolCom.c_bg_color,
          configNme: 'titleColor',
        },
      ],
      currencyStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleCurrency',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'moduleColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'bottomBgColor',
        },
        {
          components: toolCom.c_slider,
          configNme: 'topConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'bottomConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'prConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'mbConfig',
        },
        {
          components: toolCom.c_fillet,
          configNme: 'fillet',
        },
      ],
      setUp: 0,
      type: 0,
      type2: 0,
      type3: 0,
      type4: 0,
    };
  },
  watch: {
    num(nVal) {
      // debugger;
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[nVal]));
      this.configObj = value;
    },
    configObj: {
      handler(nVal, oVal) {
        this.$store.commit('mobildConfig/UPDATEARR', { num: this.num, val: nVal });
      },
      deep: true,
    },
    'configObj.setUp.tabVal': {
      handler(nVal, oVal) {
        this.setUp = nVal;
        var arr = [this.rCom[0]];
        if (nVal == 0) {
          if (this.type == 0) {
            if (this.type4 == 0) {
              this.rCom = [
                ...arr,
                ...this.rComContent,
                ...this.sixContentImg,
                ...this.oneContent,
                ...this.twoContent,
                ...this.fourContent,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.rComContent,
                ...this.sixContentTxt,
                ...this.oneContent,
                ...this.twoContent,
                ...this.fourContent,
              ];
            }
          } else {
            if (this.type4 == 0) {
              if (this.type2 == 0) {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentImg,
                  ...this.twoContent,
                  ...this.threeContent,
                  ...this.fourContent,
                ];
              } else {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentImg,
                  ...this.twoContent,
                  ...this.fourContent,
                ];
              }
            } else {
              if (this.type2 == 0) {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentTxt,
                  ...this.twoContent,
                  ...this.threeContent,
                  ...this.fourContent,
                ];
              } else {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentTxt,
                  ...this.twoContent,
                  ...this.fourContent,
                ];
              }
            }
          }
        } else {
          if (this.type == 0) {
            if (this.type4 == 0) {
              if (this.type2 == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.currencyStyle];
              }
            } else {
              if (this.type2 == 0) {
                if (this.type3) {
                  this.rCom = [
                    ...arr,
                    ...this.oneStyle,
                    ...this.oneBntStyle,
                    ...this.fourStyle,
                    ...this.twoStyle,
                    ...this.threeStyle,
                    ...this.currencyStyle,
                  ];
                } else {
                  this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.fourStyle, ...this.currencyStyle];
                }
              } else {
                if (this.type3) {
                  this.rCom = [
                    ...arr,
                    ...this.oneStyle,
                    ...this.fourStyle,
                    ...this.twoStyle,
                    ...this.threeStyle,
                    ...this.currencyStyle,
                  ];
                } else {
                  this.rCom = [...arr, ...this.oneStyle, ...this.fourStyle, ...this.currencyStyle];
                }
              }
            }
          } else {
            if (this.type4 == 0) {
              if (this.type2 == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.currencyStyle];
              }
            } else {
              if (this.type2 == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.threeStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.threeStyle, ...this.currencyStyle];
              }
            }
          }
        }
      },
      deep: true,
    },
    'configObj.styleConfig.tabVal': {
      handler(nVal, oVal) {
        this.type = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp == 0) {
          if (nVal == 0) {
            if (this.type4 == 0) {
              this.rCom = [
                ...arr,
                ...this.rComContent,
                ...this.sixContentImg,
                ...this.oneContent,
                ...this.twoContent,
                ...this.fourContent,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.rComContent,
                ...this.sixContentTxt,
                ...this.oneContent,
                ...this.twoContent,
                ...this.fourContent,
              ];
            }
          } else {
            if (this.type4 == 0) {
              if (this.type2 == 0) {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentImg,
                  ...this.twoContent,
                  ...this.threeContent,
                  ...this.fourContent,
                ];
              } else {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentImg,
                  ...this.twoContent,
                  ...this.fourContent,
                ];
              }
            } else {
              if (this.type2 == 0) {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentTxt,
                  ...this.twoContent,
                  ...this.threeContent,
                  ...this.fourContent,
                ];
              } else {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentTxt,
                  ...this.twoContent,
                  ...this.fourContent,
                ];
              }
            }
          }
        } else {
          if (nVal == 0) {
            if (this.type4 == 0) {
              if (this.type2 == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.currencyStyle];
              }
            } else {
              if (this.type2 == 0) {
                if (this.type3) {
                  this.rCom = [
                    ...arr,
                    ...this.oneStyle,
                    ...this.oneBntStyle,
                    ...this.fourStyle,
                    ...this.twoStyle,
                    ...this.threeStyle,
                    ...this.currencyStyle,
                  ];
                } else {
                  this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.fourStyle, ...this.currencyStyle];
                }
              } else {
                if (this.type3) {
                  this.rCom = [
                    ...arr,
                    ...this.oneStyle,
                    ...this.fourStyle,
                    ...this.twoStyle,
                    ...this.threeStyle,
                    ...this.currencyStyle,
                  ];
                } else {
                  this.rCom = [...arr, ...this.oneStyle, ...this.fourStyle, ...this.currencyStyle];
                }
              }
            }
          } else {
            if (this.type4 == 0) {
              if (this.type2 == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.currencyStyle];
              }
            } else {
              if (this.type2 == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.threeStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.threeStyle, ...this.currencyStyle];
              }
            }
          }
        }
      },
      deep: true,
    },
    'configObj.titleConfig.tabVal': {
      handler(nVal, oVal) {
        this.type4 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp == 0) {
          if (this.type == 0) {
            if (nVal == 0) {
              this.rCom = [
                ...arr,
                ...this.rComContent,
                ...this.sixContentImg,
                ...this.oneContent,
                ...this.twoContent,
                ...this.fourContent,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.rComContent,
                ...this.sixContentTxt,
                ...this.oneContent,
                ...this.twoContent,
                ...this.fourContent,
              ];
            }
          } else {
            if (nVal == 0) {
              if (this.type2 == 0) {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentImg,
                  ...this.twoContent,
                  ...this.threeContent,
                  ...this.fourContent,
                ];
              } else {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentImg,
                  ...this.twoContent,
                  ...this.fourContent,
                ];
              }
            } else {
              if (this.type2 == 0) {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentTxt,
                  ...this.twoContent,
                  ...this.threeContent,
                  ...this.fourContent,
                ];
              } else {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentTxt,
                  ...this.twoContent,
                  ...this.fourContent,
                ];
              }
            }
          }
        } else {
          if (this.type == 0) {
            if (nVal == 0) {
              if (this.type2 == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.currencyStyle];
              }
            } else {
              if (this.type2 == 0) {
                if (this.type3) {
                  this.rCom = [
                    ...arr,
                    ...this.oneStyle,
                    ...this.oneBntStyle,
                    ...this.fourStyle,
                    ...this.twoStyle,
                    ...this.threeStyle,
                    ...this.currencyStyle,
                  ];
                } else {
                  this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.fourStyle, ...this.currencyStyle];
                }
              } else {
                if (this.type3) {
                  this.rCom = [
                    ...arr,
                    ...this.oneStyle,
                    ...this.fourStyle,
                    ...this.twoStyle,
                    ...this.threeStyle,
                    ...this.currencyStyle,
                  ];
                } else {
                  this.rCom = [...arr, ...this.oneStyle, ...this.fourStyle, ...this.currencyStyle];
                }
              }
            }
          } else {
            if (nVal == 0) {
              if (this.type2 == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.currencyStyle];
              }
            } else {
              if (this.type2 == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.threeStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.threeStyle, ...this.currencyStyle];
              }
            }
          }
        }
      },
      deep: true,
    },
    'configObj.buttonConfig.tabVal': {
      handler(nVal, oVal) {
        this.type2 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp == 0) {
          if (this.type == 0) {
            if (this.type4 == 0) {
              this.rCom = [
                ...arr,
                ...this.rComContent,
                ...this.sixContentImg,
                ...this.oneContent,
                ...this.twoContent,
                ...this.fourContent,
              ];
            } else {
              this.rCom = [
                ...arr,
                ...this.rComContent,
                ...this.sixContentTxt,
                ...this.oneContent,
                ...this.twoContent,
                ...this.fourContent,
              ];
            }
          } else {
            if (this.type4 == 0) {
              if (nVal == 0) {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentImg,
                  ...this.twoContent,
                  ...this.threeContent,
                  ...this.fourContent,
                ];
              } else {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentImg,
                  ...this.twoContent,
                  ...this.fourContent,
                ];
              }
            } else {
              if (nVal == 0) {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentTxt,
                  ...this.twoContent,
                  ...this.threeContent,
                  ...this.fourContent,
                ];
              } else {
                this.rCom = [
                  ...arr,
                  ...this.rComContent,
                  ...this.sixContentTxt,
                  ...this.twoContent,
                  ...this.fourContent,
                ];
              }
            }
          }
        } else {
          if (this.type == 0) {
            if (this.type4 == 0) {
              if (nVal == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.currencyStyle];
              }
            } else {
              if (nVal == 0) {
                if (this.type3) {
                  this.rCom = [
                    ...arr,
                    ...this.oneStyle,
                    ...this.oneBntStyle,
                    ...this.fourStyle,
                    ...this.twoStyle,
                    ...this.threeStyle,
                    ...this.currencyStyle,
                  ];
                } else {
                  this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.fourStyle, ...this.currencyStyle];
                }
              } else {
                if (this.type3) {
                  this.rCom = [
                    ...arr,
                    ...this.oneStyle,
                    ...this.fourStyle,
                    ...this.twoStyle,
                    ...this.threeStyle,
                    ...this.currencyStyle,
                  ];
                } else {
                  this.rCom = [...arr, ...this.oneStyle, ...this.fourStyle, ...this.currencyStyle];
                }
              }
            }
          } else {
            if (this.type4 == 0) {
              if (nVal == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.currencyStyle];
              }
            } else {
              if (this.type2 == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.threeStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.threeStyle, ...this.currencyStyle];
              }
            }
          }
        }
      },
      deep: true,
    },
    'configObj.toneConfig.tabVal': {
      handler(nVal, oVal) {
        this.type3 = nVal;
        var arr = [this.rCom[0]];
        if (this.setUp) {
          if (this.type == 0) {
            if (this.type4 == 0) {
              if (nVal == 0) {
                this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.currencyStyle];
              } else {
                this.rCom = [...arr, ...this.oneStyle, ...this.currencyStyle];
              }
            } else {
              if (nVal == 0) {
                if (nVal) {
                  this.rCom = [
                    ...arr,
                    ...this.oneStyle,
                    ...this.oneBntStyle,
                    ...this.fourStyle,
                    ...this.twoStyle,
                    ...this.threeStyle,
                    ...this.currencyStyle,
                  ];
                } else {
                  this.rCom = [...arr, ...this.oneStyle, ...this.oneBntStyle, ...this.fourStyle, ...this.currencyStyle];
                }
              } else {
                if (nVal) {
                  this.rCom = [
                    ...arr,
                    ...this.oneStyle,
                    ...this.fourStyle,
                    ...this.twoStyle,
                    ...this.threeStyle,
                    ...this.currencyStyle,
                  ];
                } else {
                  this.rCom = [...arr, ...this.oneStyle, ...this.fourStyle, ...this.currencyStyle];
                }
              }
            }
          }
        }
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[this.num]));
      this.configObj = value;
    });
  },
  methods: {
    // 获取组件参数
    getConfig(data) {},
  },
};
</script>

<style scoped lang="scss">
.title-tips {
  padding-bottom: 10px;
  font-size: 14px;
  color: #333;
  span {
    margin-right: 14px;
    color: #999;
  }
}
</style>
