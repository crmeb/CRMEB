<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<script>
    import { on, off } from 'iview/src/utils/dom'
    import { setMatchMedia } from 'iview/src/utils/assist'


    import { mapMutations } from 'vuex'

    setMatchMedia()

    export default {
        name: 'app',
        methods: {
            ...mapMutations('media', [
                'setDevice'
            ]),
            handleWindowResize () {
                this.handleMatchMedia()
            },
            handleMatchMedia () {
                const matchMedia = window.matchMedia

                if (matchMedia('(max-width: 600px)').matches) {
                    var deviceWidth = document.documentElement.clientWidth || window.innerWidth;
                    let css = 'calc(100vw/7.5)'
                    document.documentElement.style.fontSize = css;
                    this.setDevice('Mobile')
                } else if (matchMedia('(max-width: 992px)').matches) {
                    this.setDevice('Tablet')
                } else {
                    this.setDevice('Desktop')
                }
            }
        },
        mounted () {
            on(window, 'resize', this.handleWindowResize)
            this.handleMatchMedia()
        },
        beforeDestroy () {
            off(window, 'resize', this.handleWindowResize)
        }
    }
</script>

<style lang="less">
.size{
  width: 100%;
  height: 100%;
}
html,body{
  .size;
  overflow: hidden;
  margin: 0;
  padding: 0;
}
#app {
  .size;
}
</style>
