<template>
  <div :id="'uitree-'+uiconfig.meta.id"
       :class="{'uitree-item':true,'even':index % 2, 'dragenter-container': isContainer && isDragIn, 'active': selectedUIItemId === uiconfig.meta.id || focusUIItem===uiconfig.meta.id}">
    <i :class="['iconfont me-1 text-primary', 'icon-' + uiconfig.type.toLowerCase()]" :style="`margin-left: ${tab*18}px`"></i>
    <!--min-width: 0解决 text-truncate 不工作的问题-->
    <div :class="{'flex-grow-1': true, 'fw-bold': uiconfig.type=='Page'}" style="min-width:0">
      <template v-if="uiconfig.type=='Text'">
        <input v-model="value" style="min-height: 0 !important;" ref="input"
               @keyup.enter="isInEdit=false" @blur="isInEdit=false"
               type="text" v-if="isInEdit" class="form-control form-control-sm p-0 ps-1">
        <div @dblclick.stop="isInEdit=true" v-if="!isInEdit" class="text-truncate">{{uiconfig.meta.value || uiconfig.meta.title || 'Text'}}</div>
      </template>
      <template v-else-if="uiconfig.type==='Image'">
        <img :src="uiconfig.meta.value||'logo.jpg'" style="height: 23px;max-width: 100px" :alt="uiconfig.meta.title"/>
      </template>
      <template v-else>
        <input v-model="title" style="min-height: auto !important;" ref="input"
               @keyup.enter="isInEdit=false" @blur="isInEdit=false"
               type="text" v-if="isInEdit" class="form-control form-control-sm p-0 ps-1">
        <div @dblclick.stop="isInEdit=true" v-if="!isInEdit" class="text-truncate">{{uiconfig.meta.title||uiconfig.type}}{{otherInfo}}</div>
      </template>
    </div>
    <div>
      <small v-if="eventCount"><i class="iconfont icon-event"></i> {{eventCount}}</small>
      <i :class="{'iconfont': true, 'icon-tree-open':isContainer && isOpen, 'icon-tree-close':isContainer && !isOpen, 'invisible icon-tree-open': !isContainer}"></i>
    </div>
  </div>
</template>

<script lang="ts">
import InitUITree from '@/components/Common'
import { computed, nextTick, Ref, ref, watch } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'UITreeLoader',
  props: {
    uiconfig: Object,
    index: Number,
    tab: Number,
    isOpen: Boolean,
    pageid: String,
    treeIcon: String
  },
  emits: ['onInlineEdit'],
  setup (props: any, context: any) {
    const store = useStore()
    const init = InitUITree()
    const input: Ref<HTMLElement | null> = ref(null)
    const isContainer = computed(() => props.uiconfig.meta.isContainer)
    const dragoverUIItemId = computed(() => store.state.design.dragoverUIItemId)
    const dragoverPlacement = computed(() => store.state.design.dragoverPlacement)
    const isDragIn = computed(() => {
      if (dragoverPlacement.value !== 'in') return false
      return dragoverUIItemId.value === props.uiconfig.meta.id
    })
    const isInEdit = ref(false)
    const eventCount = computed(() => {
      if (!props.uiconfig?.events) return 0
      return props.uiconfig.events.length
    })
    const value = init.computedWrap('value')
    const title = init.computedWrap('title')
    const otherInfo = computed(() => {
      const container = props.uiconfig.meta?.css?.container
      return container ? ` (${container}) ` : ''
    })
    watch(isInEdit, (v) => {
      nextTick(() => {
        if (v && input.value) {
          // console.log(input.value)
          input.value.focus()
        }
      })
      context.emit('onInlineEdit', v)
    })
    return {
      isContainer,
      isDragIn,
      isInEdit,
      value,
      title,
      input,
      eventCount,
      otherInfo,
      ...init
    }
  }
}
</script>
