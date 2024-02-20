<template>
  <div class="breadcrumb-bar" @contextmenu="rightClick">
    <div class="breadcrumb-body element-path">
      <div v-if="paths.length > 0">
        <svg width="7" height="28" viewBox="0 0 7 28" style="display: block;transform: translate(0px, 0px);color: rgb(235, 235, 235);">
          <path fill="currentColor" class="path" d="M6.5 14L.5 0H0v28h.5z"></path>
          <path fill="#858585" d="M1 0H0l6 14-6 14h1l6-14z"></path>
        </svg>
        <svg width="7" height="28" viewBox="0 0 7 28" style="display: block; transform: translate(0px, 0px); color: rgb(235, 235, 235);">
          <path fill="currentColor" class="path" d="M.5 0l6 14-6 14H7V0z"></path>
          <path fill="#858585" d="M1 0H0l6 14-6 14h1l6-14z"></path>
        </svg>
      </div>
      <div class="item" v-for="(value, index) in paths" :key="index" :data-pageid="selectedPageId" :data-uiid="value.meta.id"
           @click.stop="uiClick" @mouseover.stop="uiMouseEnter" @mouseout.stop="uiMouseLeave">
        <svg width="7" height="28" viewBox="0 0 7 28" style="display: block;transform: translate(0px, 0px);color: rgb(235, 235, 235);">
          <path fill="currentColor" class="path" d="M6.5 14L.5 0H0v28h.5z"></path>
          <path fill="#858585" d="M1 0H0l6 14-6 14h1l6-14z"></path>
        </svg>
        <template v-if="value.type==='Image'">
          <img :src="value.meta.value||'logo.jpg'" style="height: 20px;max-width: 100px;margin: 3px 10px" :alt="value.meta.title"/>
        </template>
        <template v-else>
          <input v-model="value.meta.title" style="min-height: auto !important;" ref="input"
                 @keyup.enter="isInEdit=''" @blur="isInEdit=''"
                 type="text" v-if="isInEdit==value.meta.id" class="form-control form-control-sm p-0 ps-1">
          <template  v-if="isInEdit!=value.meta.id">
            <i :class="`iconfont text-primary icon-${value.type.toLowerCase()}`"></i><span class="label" @dblclick.stop="isInEdit=value.meta.id">{{ value.meta.title || value.type }}<template v-if="value.meta.isContainer">{{ getContainerDisplay(value)}}</template> </span>
          </template>
        </template>
        <svg width="7" height="28" viewBox="0 0 7 28" style="display: block; transform: translate(0px, 0px); color: rgb(235, 235, 235);">
          <path fill="currentColor" class="path" d="M.5 0l6 14-6 14H7V0z"></path>
          <path fill="#858585" d="M1 0H0l6 14-6 14h1l6-14z"></path>
        </svg>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { useStore } from 'vuex'
import { computed, nextTick, Ref, ref, watch } from 'vue'
import $ from 'jquery'
import InitUI from '@/components/Common'

export default {
  name: 'ElementPath',
  emits: ['contextMenu'],
  setup (props: any, context: any) {
    const store = useStore()
    const info = InitUI()
    const isInEdit = ref('')
    const input: Ref<HTMLElement | null> = ref(null)
    const { hoverUIItemId, selectedUIItemId, selectedPageId } = info
    const selectedUIItem = computed(() => {
      if (!selectedUIItemId.value || selectedUIItemId.value === '') return null
      const { uiConfig } = store.getters.getUIItemInPage(selectedUIItemId.value, selectedPageId.value)
      return uiConfig
    })

    const updatePath = (item: Record<any, any>) => {
      paths.value.push(item)
      const { parentConfig } = store.getters.getUIItemInPage(item.meta.id, selectedPageId.value)
      if (parentConfig && parentConfig.type !== 'Unknown') {
        updatePath(parentConfig)
      }
    }

    const paths: any = ref([])
    watch(selectedUIItem, () => {
      isInEdit.value = ''
      paths.value.splice(0)
      if (selectedUIItem.value) {
        updatePath(selectedUIItem.value)
      }
    })
    watch(isInEdit, (v) => {
      nextTick(() => {
        if (v && input.value) {
          // console.log(input.value)
          $(input.value).focus()
        }
      })
    })
    const uiMouseEnter = (event: any) => {
      const el = $(event.target)
      hoverUIItemId.value = $(el).parents('.item').attr('data-uiid')
    }

    const uiMouseLeave = (event: any) => {
      hoverUIItemId.value = ''
    }

    const uiClick = (event: any) => {
      // 延迟下，给dblclick执行的时间
      setTimeout(() => {
        uiChange($(event.target))
      }, 500)
    }
    const uiChange = (el: any) => {
      if (isInEdit.value) return
      const item = el.parents('.item')
      selectedUIItemId.value = item.attr('data-uiid')

      const scrollbar = document.querySelector('.workspace-scrollbar')
      if (scrollbar) {
        const uidom = document.getElementById(selectedUIItemId.value)
        if (uidom) {
          const rect = uidom.getBoundingClientRect()
          // 200 是一个估值，确保uidom在主窗体内，不被上边和左边的内容遮挡
          scrollbar.scrollTo(rect.x - 200, rect.y - 200)
        }
      }
    }

    const getContainerDisplay = function (node) {
      const css = node?.meta?.css?.container
      // console.log(node.meta?.css?.container)
      return css ? ` (${css}) ` : ''
    }
    const rightClick = (event: any) => {
      if (event.button === 2) {
        event.returnValue = false
        event.stopPropagation()
        event.preventDefault()
        uiChange($(event.target))
        context.emit('contextMenu', { x: event.pageX, y: event.pageY })
        return false
      }
    }
    return {
      paths,
      selectedPageId,
      isInEdit,
      input,
      getContainerDisplay,
      uiMouseEnter,
      uiMouseLeave,
      uiClick,
      rightClick
    }
  }
}
</script>
