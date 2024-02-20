<template>
  <li :class="{'tree-item': true}">
    <div :style="`padding-left:${indent*15}px`"
         class="tree-node d-flex align-items-center align-content-center"
         @click.stop="subIsOpen = !subIsOpen">
      <i v-if="tree.children.length > 0" :class="{'iconfont': true, 'icon-tree-open': subIsOpen, 'icon-tree-close': !subIsOpen}"></i>
      <i v-else class="iconfont icon-placeholder"></i>
      <i v-if="!tree.isApi" class="iconfont icon-folder"></i>
      <label class="d-flex m-0 flex-grow-1 justify-content-start overflow-hidden">
        <template v-if="tree.isApi">
          <div class="text-truncate d-flex align-items-center pointer" @click.stop="viewDetail(tree)">
            <span :class="'api-status api-status-'+tree.status"></span>
            <span :class="'api-method api-method-'+tree.method">{{tree.method}}</span>
            <small class="text-muted pe-1">{{tree.major}}.{{tree.minor}}.{{tree.revision}}</small>
            <span>{{tree.title}}</span>
          </div>
        </template>
        <template v-else>
          <div class="text-truncate d-flex align-items-center">
            <span>{{tree.title}}</span>
            <span class="text-muted ps-2">{{tree.comment}}</span>
          </div>
        </template>
      </label>
      <slot v-if="!tree.isApi" name="trunk" :data="tree"></slot>
      <slot v-if="tree.isApi" name="leaf" :data="tree"></slot>
    </div>
    <template v-if="tree.children.length > 0">
      <ul :class="{'tree':true, 'd-none':!subIsOpen}">
        <APITree :key="index" v-for="(subitem, index) in tree.children" :open="open" :tree="subitem" :indent="indent+1" :path="path+'/'+tree.name">
          <template #leaf="{data}">
            <slot name="leaf" :data="data"></slot>
          </template>
          <template #trunk="{data}">
            <slot name="trunk" :data="data"></slot>
          </template>
        </APITree>
      </ul>
    </template>
  </li>

  <lay-layer v-model="detailDialogVisible" :title="t('api.apiDetail')" :shade="true" :area="['80vw', '80vh']">
    <div class="p-2">
      1
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

export default {
  props: {
    tree: Object,
    open: Boolean,
    indent: Number,
    path: String
  },
  name: 'APITree',
  setup (props: any, context: any) {
    const subIsOpen = ref(props.open)
    const detailDialogVisible = ref(false)
    const currAPI = ref()

    const viewDetail = (api) => {
      currAPI.value = api
      detailDialogVisible.value = true
    }
    watch(() => props.open, (v) => {
      subIsOpen.value = v
    })

    const { t } = useI18n()

    return {
      t,
      currAPI,
      viewDetail,
      detailDialogVisible,
      subIsOpen
    }
  }
}
</script>
