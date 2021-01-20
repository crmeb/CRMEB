<template>
    <div class="i-mde" :class="classes">
        <textarea ref="mde"></textarea>
    </div>
</template>
<script>
import SimpleMDE from 'simplemde'
import 'simplemde/dist/simplemde.min.css'

export default {
  name: 'i-mde',
  props: {
    value: {
      type: String,
      default: ''
    },
    border: {
      type: Boolean,
      default: false
    },
    // 配置参数
    config: {
      type: Object,
      default: () => ({})
    }
  },
  data () {
    return {
      // 编辑器实例
      mde: null,
      // 编辑器默认参数
      // 详见 https://github.com/sparksuite/simplemde-markdown-editor#configuration
      defaultConfig: {

      }
    }
  },
  computed: {
    classes () {
      return [
        {
          'i-mde-no-border': !this.border
        }
      ]
    }
  },
  methods: {
    // 初始化
    init () {
      // 合并参数
      const config = Object.assign({}, this.defaultConfig, this.config)
      // 初始化
      this.mde = new SimpleMDE({
        ...config,
        // 初始值
        initialValue: this.value,
        // 挂载元素
        element: this.$refs.mde
      })
      this.mde.codemirror.on('change', () => {
        this.$emit('input', this.mde.value())
        this.$emit('on-change', this.mde.value())
      })
    },
    // 增加内容
    add (val) {
      if (this.mde) {
        this.mde.value(this.value + val)
      }
    },
    // 替换内容
    replace (val) {
      if (this.mde) {
        this.mde.value(val)
      }
    }
  },
  mounted () {
    // 初始化
    this.init()
  },
  beforeDestroy () {
    // 在组件销毁后销毁实例
    this.mde = null
  }
}
</script>
<style lang="less">
    .i-mde{
        .editor-toolbar.fullscreen, .CodeMirror-fullscreen, .editor-preview-side{
            z-index: 100;
            border-radius: 0;
        }

        &-no-border{
            .editor-toolbar{
                border: none;
                border-bottom: 1px solid #e8eaec;
            }
            .CodeMirror{
                border: none;
            }
            .editor-preview-side{
                border: none;
                border-left: 1px solid #e8eaec;
            }
        }
    }
</style>
