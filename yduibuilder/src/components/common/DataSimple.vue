<template>
  <div class="model-item" @click="check">
    <div class="model-field flex-shrink-0 text-truncate" @click="isOpen = !isOpen" :style="'padding-left: ' + (intent * 16) + 'px'">
      <i v-if="hasSubItem" :class="{'iconfont':true, 'icon-tree-close': !isOpen, 'icon-tree-open': isOpen}"></i>
      <i v-if="!hasSubItem" style="width: 16px;height: 24px;flex-shrink: 0">&nbsp;</i>
      <div :class="{'pointer hover-text-primary': true, 'fw-bolder':intent==0}" @click.stop="viewDetail">
        <template v-if="isArrayItem">
          <span class="text-success">ITEM</span>
        </template>
        <span v-else :class="{'fst-italic': myModel.isRoot && myModel.isExpression}">
          {{myModel.name}}
          <template v-if="myModel.isRoot && myModel.isExpression">()</template>
        </span>
      </div>
      <span :class="'ps-1 fs-7 param-' + myModel.type">
        {{myModel.type}}
      </span>
      <span class="text-truncate ps-2 pointer text-muted fs-7">
          {{myModel.title}}
      </span>
    </div>
  </div>

  <template v-if="myModel.type=='array' && isOpen">
    <DataSimple @checked="checked"
                :path="myPath" :rootUuid="rootUuid" :is-array-item="true"
                :intent="intent+1" :model="myModel.item" :index="0"></DataSimple>
  </template>
  <template v-if="['object','blob','file'].indexOf(myModel.type) !== -1  && isOpen">
    <DataSimple v-for="(item, index) in myModel.props"
                @checked="checked"
                :path="myPath" :rootUuid="rootUuid"
                :key="index" :intent="intent+1" :model="item" :index="index"></DataSimple>
  </template>
  <DataInfo v-model="detailDlgVisible" :data="myModel"></DataInfo>
</template>

<script lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import { DataStruct } from '@/store/model'
import DataInfo from '@/components/common/DataInfo.vue'

//  简单的查看展示数据
export default {
  name: 'DataSimple',
  components: { DataInfo },
  props: {
    model: Object, // DataStruct
    index: Number,
    intent: Number, // 缩进次数
    rootUuid: String, // model所在根节点记录的uuid
    isArrayItem: Boolean, // 数组结点标识,用于标识数组的第一个结点
    path: String // 从根到自己到访问路径
  },
  emits: ['checked'],
  setup (props: any, context: any) {
    const myModel = computed<any>(() => props.model)
    const isOpen = ref(false)
    const detailDlgVisible = ref(false)
    const store = useStore()
    const currPage = computed(() => store.state.design.page)
    const hasSubItem = computed(() => ['object', 'array', 'file', 'blob'].indexOf(myModel.value.type) !== -1)
    const myPath = computed(() => (props.path ? props.path + '.' : '') + (myModel.value.name || ''))

    const viewDetail = () => {
      detailDlgVisible.value = true
    }
    const { t } = useI18n()

    /**
     * 选择的数据
     * @param path 访问路径
     * @param data 数据结构体
     * @param rootDataId 数据所在的根数据uuid
     */
    const checked = (path, data: DataStruct, rootDataId) => {
      context.emit('checked', path, data, rootDataId)
    }
    const check = () => {
      checked(myPath.value, myModel.value, props.rootUuid)
    }

    return {
      t,
      myModel,
      detailDlgVisible,
      currPage,
      isOpen,
      myPath,
      viewDetail,
      hasSubItem,
      check,
      checked
    }
  }
}
</script>
