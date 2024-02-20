<template>
  <div class="text-muted p-3 d-flex justify-content-between align-items-center ui-sidebar-fixedtop">
    <div class="text-muted">{{t('common.uicomponent')}}</div>
    <button class="btn btn-white btn-sm" type="button" v-if="!openState" @click="expandAll"><i class="iconfont icon-expandall"></i></button>
    <button class="btn btn-white btn-sm" type="button" v-if="openState" @click="collapseAll"><i class="iconfont icon-collapseall"></i></button>
  </div>
  <div style="margin-top: 60px">
    <div class="ui-sidebar" v-for="(type) in uiDefineTypes" :key="type">
      <div class="ui-sidebar-header" @click="closedType[type] = !closedType[type]"><i :class="{'iconfont':true, ' icon-tree-open':!closedType[type], ' icon-tree-close':closedType[type]}"></i> {{t('ui.' + type)}}</div>
      <div class="ui-sidebar-body" v-if="!closedType[type]">
        <template v-if="uiDefines[type].length">
          <div class="btn btn-white ui-item" draggable="true" :data-uuid="item?.id" :data-type="item.type" v-for="(item, index) in uiDefines[type]" :key="index">
            <i :class="`iconfont icon-${item.type.toLowerCase()}`"></i>{{t(item.name)}}
          </div>
          <div class="ui-item-holder" v-for="index in left[type]" :key="index"></div>
        </template>
        <template v-if="!uiDefines[type].length">
          <div class="p-2 flex-column text-muted d-flex justify-content-center align-items-center">
            <i class="iconfont icon-uicomponent text-muted" style="font-size: 2rem !important;"></i>
            <div style="font-size: 0.75rem">{{t('ui.empty')}}</div>
            <div class="text-wrap fs-7">{{t(type==='projectUIComponent' ? 'ui.projectUIComponentTip' : 'ui.myUIComponentTip')}}</div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import UIInit from '@/components/Common'
import { computed, onMounted, ref, watch } from 'vue'
import { useStore } from 'vuex'
import uidrag from '@/lib/uidrag'
import ydhl from '@/lib/ydhl'

export default {
  name: 'UIComponent',
  setup () {
    const store = useStore()
    const openState = ref(true)
    const project = computed(() => store.state.design.project)
    const uiDefines = ref({})
    const uiDefineTypes = computed(() => Object.keys(uiDefines.value))
    const itemWidth = 80
    const { t } = useI18n()
    const left = ref<Record<string, number>>({})
    const width = computed(() => store.state.design.leftSidebarWidth - 51) // 51是内间距留白 + 滚动条
    const closedType = ref<Record<string, boolean>>({})

    const rejectItemCount = () => {
      // 计算剩余需要补的个数
      const itemCount = ~~(width.value / itemWidth) // 每排能显示的个数
      for (const type of uiDefineTypes.value) {
        if (itemCount > uiDefines.value[type].length) {
          left.value[type] = itemCount - uiDefines.value[type].length
          continue
        }
        left.value[type] = itemCount - uiDefines.value[type].length % itemCount
      }
    }
    watch(width, rejectItemCount, { immediate: true })
    const loadUIComponent = () => {
      ydhl.get('api/uicomponent.json', { uuid: project.value.id }, (rst) => {
        if (rst && rst.success) {
          for (const key in rst.data) {
            uiDefines.value[key] = rst.data[key]
          }
          rejectItemCount()
        }
      })
    }

    onMounted(() => {
      loadUIComponent()
      // 从元素边栏拖入某个页面中去
      uidrag({
        target: '.ui-sidebar .ui-item',
        dragend: () => {
          store.commit('clearDragoverState')
        }
      })
    })

    const collapseAll = () => {
      openState.value = false
      for (const type of uiDefineTypes.value) {
        closedType.value[type] = true
      }
      closedType.value.projectUILab = true
      closedType.value.myUILab = true
    }
    const expandAll = () => {
      openState.value = true
      for (const type of uiDefineTypes.value) {
        closedType.value[type] = false
      }
      closedType.value.projectUILab = false
      closedType.value.myUILab = false
    }
    return {
      ...UIInit(),
      uiDefines,
      uiDefineTypes,
      t,
      left,
      closedType,
      expandAll,
      openState,
      collapseAll
    }
  }
}
</script>
