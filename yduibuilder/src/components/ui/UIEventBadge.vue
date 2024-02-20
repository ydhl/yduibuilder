<template>
  <div :style="positionStyle" v-if="loaded && eventCount && showEventPanel" class="ui-events-badge"><span class="badge bg-danger text-white"><i class="iconfont icon-api"></i> {{ eventCount }}</span></div>
  <UIEventBadge v-for="(item, index) in uiconfig?.items" :key="index" :uiconfig="item" :pageid="pageid"></UIEventBadge>
</template>

<script lang="ts">
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  props: {
    uiconfig: Object,
    pageid: String
  },
  name: 'UIEventBadge',
  setup (props: any, context: any) {
    const store = useStore()
    const eventCount = computed(() => {
      if (!props.uiconfig?.events) return 0
      return props.uiconfig.events.length
    })

    const loaded = computed(() => store.state.page.loadedUIIds?.[props.uiconfig?.meta?.id])
    const showEventPanel = computed(() => store.state.page.showEventPanel)

    const positionStyle = computed(() => {
      // console.log(showEventPanel.value)
      if (!loaded.value || !showEventPanel.value) return
      const rect = document.getElementById(props.uiconfig?.meta?.id)?.getBoundingClientRect()
      if (!rect) return
      return `transform:translate(${rect.x}px,${rect.y}px); width:${rect.width}px;height:${rect.height}px`
    })
    return {
      eventCount,
      showEventPanel,
      loaded,
      positionStyle
    }
  }
}
</script>

<style scoped>

</style>
