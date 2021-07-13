<template>
	<view v-if="shareInfoStatus" class="poster-first">
	    <view class="mask-share">
			<image src="/static/images/share-info.png" @click="shareInfoClose" @touchmove.stop.prevent="false"></image>
	    </view>
	  </view>
</template>

<script>
	
export default {
	props: {
		 shareInfoStatus: {
		      type: Boolean,
		      default:false,
		    }
	},
  data: function() {
    return {};
  },
  mounted: function() {},
  methods: {
    shareInfoClose: function() {
      this.$emit("setShareInfoStatus");
    }
  }
};

</script>

<style scoped lang="scss">
	.poster-first {
	  overscroll-behavior: contain;
	}
	.mask-share {
	  position: fixed;
	  top: 0;
	  left: 0;
	  right: 0;
	  bottom: 0;
	  z-index: 99;
	}
	.mask-share image {
	  width: 100%;
	  height:100%;
	}
</style>
