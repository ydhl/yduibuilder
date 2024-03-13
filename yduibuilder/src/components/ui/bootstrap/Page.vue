<template>
  <div :class="[dragableCss, uiCss]" draggable="false" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-isContainer="!inlineEditItemId"
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
  name: 'Bootstrap_Page',
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
    const uiStyle = computed(() => {
      const style = page.getUIStyle()
      if (props.uiconfig.pageType === 'popup') {
        style.display = 'flex'
        style['justify-content'] = 'center'
        style['align-items'] = 'stretch'
      }
      return page.appendImportant(style)
    })
    return {
      ...page.setup(),
      uiStyle
    }
  }
}

</script>
