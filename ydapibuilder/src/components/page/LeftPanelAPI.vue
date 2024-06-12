<template>
  <LeftPanelBase>
    <template #menu>
      <a :class="{'item': true, 'active': currSidebar==='SidebarApi'}" @click="changeSidebar('SidebarApi')">
        <i class="iconfont icon-api mb-1" />{{ t('common.apiManage') }}
      </a>
      <div class="flex-grow-1 item" ></div>
      <div class="item text-muted p-1 text-center"><small>{{version}}</small></div>
    </template>
    <template #panel>
      <keep-alive>
        <component :is="currSidebar" />
      </keep-alive>
    </template>
  </LeftPanelBase>
</template>

<script lang="ts">
import SidebarApi from '@/components/sidebar/API.vue'
import SidebarSetting from '@/components/sidebar/SidebarSetting.vue'
import LeftPanelBase from '@/components/page/LeftPanelBase.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
export default {
  name: 'LeftPanelAPI',
  components: {
    LeftPanelBase,
    SidebarSetting,
    SidebarApi
  },
  setup (props: any, context: any) {
    const store = useStore()
    const changeSidebar = (sidebar: any) => {
      currSidebar.value = sidebar
    }
    const sideBars = computed(() => store.state.design.leftSidebars)
    const currSidebar = computed({
      get () {
        const stacks: any = sideBars.value
        if (!stacks || stacks.length === 0) return ''
        return stacks[stacks.length - 1].sidebar
      },
      set (v: string) {
        store.commit('updateState', { leftSidebars: v !== '' ? [{ sidebar: v }] : [] })
      }
    })
    const { t } = useI18n()
    const version = computed(() => ydhl.version)
    return {
      t,
      version,
      currSidebar,
      changeSidebar
    }
  }
}
</script>
