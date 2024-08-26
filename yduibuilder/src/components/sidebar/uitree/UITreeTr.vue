<template>
  <div :class="{'uitree-item':true}">
    <i class="iconfont me-1 text-primary icon-tr" :style="`margin-left: ${tab*18}px`"></i>
    <div class="flex-grow-1 text-muted">
      <div class="text-truncate">{{type}} row {{row}}</div>
    </div>
    <div>
      <i :class="{'iconfont': true, 'icon-tree-open':isOpen, 'icon-tree-close':!isOpen}"></i>
    </div>
  </div>
</template>

<script lang="ts">
import InitUITree from '@/components/Common'

export default {
  name: 'UITreeTr',
  props: {
    uiconfig: Object,
    index: Number,
    type: String,
    row: Number,
    tab: Number,
    isOpen: Boolean,
    pageid: String
  },
  emits: ['onInlineEdit'],
  setup (props: any, context: any) {
    const init = InitUITree()
    return {
      ...init
    }
  }
}
</script>
