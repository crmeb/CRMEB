<template>
  <div class="box">
    <upload-from
      :isChoice="isChoiceD"
      @getPicD="getPicD"
      :gridPic="gridPic"
      :gridBtn="gridBtn"
      v-if="
        this.$route.query.fodder === 'dialog' ||
        this.$route.query.type === 'many'
      "
    ></upload-from>
    <upload-from
      :isChoice="isChoice"
      @getPic="getPic"
      :gridPic="gridPic"
      :gridBtn="gridBtn"
      v-else
    ></upload-from>
  </div>
</template>

<script>
import uploadFrom from "./index";
import "../../../public/UEditor/dialogs/internal";
export default {
  name: "widgetImg",
  components: { uploadFrom },
  data() {
    return {
      isChoice: "单选",
      isChoiceD: "多选",
      gridPic: {
        xl: 4,
        lg: 4,
        md: 8,
        sm: 12,
        xs: 12,
      },
      gridBtn: {
        xl: 4,
        lg: 4,
        md: 4,
        sm: 8,
        xs: 8,
      },
    };
  },
  mounted() {},
  methods: {
    getPicD(pc) {
      if (this.$route.query.fodder === "dialog") {
        let str = "";
        for (let i = 0; i < pc.length; i++) {
          nowEditor.editor.execCommand("insertimage", { src: pc[i].att_dir });
        }
        nowEditor.dialog.close(true);
      } else {
        let pcs = window.form_create_helper.get(this.$route.query.fodder) || [];
        pc = pc.map((item) => {
          return item.att_dir;
        });
        let concatPc = pcs.concat(pc);
        let pcList = Array.from(new Set(concatPc));
        form_create_helper.set(this.$route.query.fodder, pcList);
        form_create_helper.close(this.$route.query.fodder);
      }
    },
    getPic(pc) {
      form_create_helper.set(this.$route.query.fodder, pc.satt_dir);
      form_create_helper.close(this.$route.query.fodder);
    },
    // getPic (pc) {
    //     if (this.$route.query.fodder === 'dialog') {
    //         /* eslint-disable */
    //         nowEditor.dialog.close(true);
    //         nowEditor.editor.setContent('<img src="'+pc.att_dir+'">',true);
    //     }
    //     else {
    //         form_create_helper.set(this.$route.query.fodder, pc.satt_dir)
    //         form_create_helper.close(this.$route.query.fodder);
    //     }
    // }
  },
};
</script>

<style scoped lang="stylus">
.box {
  width: 100%;
  background: #fff;
}
</style>
