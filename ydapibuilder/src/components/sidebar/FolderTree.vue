<template>
  <li :class="{'tree-item': true}">
    <div :style="`padding-left:${indent*15}px`"
         class="tree-node d-flex align-items-center align-content-center"
         @click.stop="subIsOpen = !subIsOpen"
    @mouseleave="hover = false" @mouseenter="hover = true">
      <i v-if="tree.children?.length > 0" :class="{'iconfont': true, 'icon-tree-open': subIsOpen, 'icon-tree-close': !subIsOpen}"></i>
      <i v-else class="iconfont icon-placeholder"></i>
      <i v-if="!tree.isApi" class="iconfont icon-folder"></i>
      <label class="d-flex m-0 flex-grow-1 justify-content-start overflow-hidden pointer" @click="tree.isApi?showDetail(tree.id):''">
        <div class="text-truncate d-flex align-items-center">
          <div v-if="tree.isApi" >
            <span :class="'api-status api-status-'+tree.status"></span>
            <span :class="'api-method api-method-'+tree.method">{{tree.method}}</span>
            <span class="text-muted" style="font-size: 0.75rem">({{tree.major}}.{{tree.minor}}.{{tree.revision}})</span>
          </div>
          <span>{{tree.title}}</span>
        </div>
      </label>
      <slot v-if="hover && !tree.isApi" name="trunk" :data="tree"></slot>
      <slot v-if="hover && tree.isApi" name="leaf" :data="tree"></slot>
    </div>
    <template v-if="tree.children?.length > 0">
      <ul :class="{'tree':true, 'd-none':!subIsOpen}">
        <FolderTree :key="index" v-for="(subitem, index) in tree.children" :open="open" :tree="subitem" :indent="indent+1" :path="path+'/'+tree.name">
          <template #leaf="{data}">
            <slot name="leaf" :data="data"></slot>
          </template>
          <template #trunk="{data}">
            <slot name="trunk" :data="data"></slot>
          </template>
        </FolderTree>
      </ul>
    </template>
  </li>
</template>

<script lang="ts">
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'

export default {
  props: {
    tree: Object,
    open: Boolean,
    indent: Number,
    path: String
  },
  name: 'FolderTree',
  setup (props: any, context: any) {
    const subIsOpen = ref(props.open)
    const store = useStore()
    const hover = ref(false)
    watch(() => props.open, (v) => {
      subIsOpen.value = v
    })

    const { t } = useI18n()
    const showDetail = (uuid: string) => {
      store.commit('putTab', { page: 'APIDetail', name: 'common.loading', query: { uuid: uuid } })
    }

    return {
      showDetail,
      t,
      subIsOpen,
      hover
    }
  }
}
</script>
