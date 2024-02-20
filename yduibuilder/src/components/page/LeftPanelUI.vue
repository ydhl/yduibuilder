<template>
  <LeftPanelBase>
    <template #menu>
      <a :class="{'item': true, 'active': currSidebar==='Project'}" data-bs-toggle="tooltip" :title="t('project.name')"
         @click="changeSidebar('Project')"><i class="iconfont icon-folder" /></a>
      <a :class="{'item': true, 'active': currSidebar==='UI'}" data-bs-toggle="tooltip" :title="t('common.ui')"
         @click="changeSidebar('UI')"><i class="iconfont icon-plus" /></a>
      <a :class="{'item': true, 'active': currSidebar==='SidebarUIComponent'}" data-bs-toggle="tooltip" :title="t('common.uicomponent')"
         @click="changeSidebar('SidebarUIComponent')"><i class="iconfont icon-uicomponent" /></a>
      <a :class="{'item': true, 'active': currSidebar==='SidebarStyle'}" data-bs-toggle="tooltip" :title="t('common.style')"
         @click="changeSidebar('SidebarStyle')"><i class="iconfont icon-style" /></a>
      <a :class="{'item': true, 'active': currSidebar==='UITree'}" data-bs-toggle="tooltip" :title="t('common.outline')"
         @click="changeSidebar('UITree')"><i class="iconfont icon-tree" /></a>
      <div class="flex-grow-1 item" ></div>
      <div class="item text-muted p-1 text-center" style="font-size: 0.6rem"><small>{{version}}</small></div>
    </template>
    <template #panel>
      <keep-alive>
        <component :is="currSidebar" />
      </keep-alive>
    </template>
  </LeftPanelBase>
</template>

<script lang="ts">
import Project from '@/components/sidebar/Project.vue'
import UI from '@/components/sidebar/UI.vue'
import SidebarUIComponent from '@/components/sidebar/UIComponent.vue'
import SidebarUploadUI from '@/components/sidebar/UploadUI.vue'
import SidebarImage from '@/components/sidebar/Image.vue'
import SidebarIcon from '@/components/sidebar/Icon.vue'
import SidebarStyle from '@/components/sidebar/SidebarStyle.vue'
import UITree from '@/components/sidebar/UITree.vue'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import LeftPanelBase from '@/components/page/LeftPanelBase.vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
export default {
  name: 'LeftPanelUI',
  components: {
    LeftPanelBase,
    SidebarUploadUI,
    UI,
    SidebarUIComponent,
    Project,
    SidebarImage,
    UITree,
    SidebarIcon,
    SidebarStyle
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
