<template>
  <li :class="{'tree-item': true}">
    <div :style="`padding-left:${indent*15}px`"
         class="tree-node d-flex align-items-center align-content-center"
         @click.stop.prevent="subIsOpen = !subIsOpen"
    @mouseleave="hover = false" @mouseenter="hover = true">
      <i v-if="tree.items.length > 0" :class="{'iconfont': true, 'icon-tree-open': subIsOpen, 'icon-tree-close': !subIsOpen}"></i>
      <i v-else class="iconfont icon-placeholder"></i>
      <i v-if="tree.icon" :class="'iconfont ' + tree.icon"></i>
      <label class="d-flex m-0 flex-grow-1 justify-content-start overflow-hidden">
        <div class="text-truncate" v-html="tree.name"></div>
      </label>
      <slot v-if="hover && !tree.isLeaf" name="trunk"></slot>
      <slot v-if="hover && tree.isLeaf" name="leaf"></slot>
    </div>
    <template v-if="tree.items.length > 0">
      <ul :class="{'tree':true, 'd-none':!subIsOpen}">
        <Tree :key="index" v-for="(subitem, index) in tree.items" :open="open" :tree="subitem" :indent="indent+1" :path="path+'/'+tree.name">
          <template #leaf>
            <slot name="leaf"></slot>
          </template>
          <template #trunk>
            <slot name="trunk"></slot>
          </template>
        </Tree>
      </ul>
    </template>
  </li>
</template>

<script lang="ts">
import { ref, watch } from 'vue'
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'

export default {
  props: {
    tree: Object,
    open: Boolean,
    indent: Number,
    path: String
  },
  name: 'Tree',
  setup (props: any, context: any) {
    const info = initUI()
    const subIsOpen = ref(props.open)
    const hover = ref(false)
    watch(() => props.open, (v) => {
      subIsOpen.value = v
    })

    const { selectedUIItemIsInput } = info
    const { t } = useI18n()

    return {
      t,
      selectedUIItemIsInput,
      subIsOpen,
      hover
    }
  }
}
</script>
