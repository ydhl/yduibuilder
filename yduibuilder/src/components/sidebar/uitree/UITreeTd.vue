<template>
  <div :class="{'uitree-item':true}">
    <i class="iconfont me-1 text-primary icon-column" :style="`margin-left: ${tab*18}px`"></i>
    <div class="flex-grow-1">
      <div class="text-truncate text-muted">
        column {{column}}
      </div>
    </div>
    <div v-if="hasItems">
      <i :class="{'iconfont': true, 'icon-tree-open':isOpen, 'icon-tree-close':!isOpen}"></i>
    </div>
  </div>
</template>

<script lang="ts">
import InitUITree from '@/components/Common'

export default {
  name: 'UITreeTd',
  props: {
    uiconfig: Object,
    index: Number,
    row: Number,
    column: Number,
    tab: Number,
    isOpen: Boolean,
    pageid: String,
    hasItems: Boolean
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
