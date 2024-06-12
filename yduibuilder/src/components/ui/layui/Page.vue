<template>
  <div :class="[dragableCss, uiCss]" draggable="false" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-isContainer="true"
       :data-pageid="pageid">
    <UIBase v-for="(item, index) in uiconfig.items" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
  </div>
</template>

<script lang="ts">
import Page from '@/components/ui/js/Page'
import UIBase from '@/components/ui/UIBase.vue'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_Page',
  components: { UIBase },
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const page = new Page(props, context, useStore())
    const uiCss = computed(() => {
      const css = page.getUICss()
      if (props.uiconfig.items) {
        let hasForm: any = false
        for (const item of props.uiconfig.items) {
          // console.log(item)
          if (item.meta?.form) {
            hasForm = true
            break
          }
        }
        if (hasForm) {
          css.form = 'layui-form'
        }
      }
      return Object.values(css).join(' ')
    })
    return {
      ...page.setup(),
      uiCss
    }
  }
}

</script>
