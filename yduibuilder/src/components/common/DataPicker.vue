<template>
  <div :class="{'model-item ': true, 'bg-light':checkedUuids.indexOf(myModel.uuid)!=-1}">
    <div class="model-field flex-shrink-0 text-truncate" @click="toggle" :style="'padding-left: ' + (intent * 16) + 'px'">
      <i v-if="myModel.type=='object'" :class="{'iconfont':true, 'icon-tree-close': !isOpen, 'icon-tree-open': isOpen}"></i>
      <i v-if="myModel.type!='object'" style="width: 16px;height: 24px;">&nbsp;</i>
      <div class="pointer hover-text-primary" @click.stop="viewDetail">{{myModel.name || 'ROOT'}}</div>
      <span :class="'ps-1 fs-7 param-' + myModel.type">
        {{myModel.type}}
      </span>
      <span class="text-truncate ps-2 pointer text-muted fs-7">
        {{myModel.title}}
      </span>
    </div>
    <span class="text-muted fs-7" @click.stop="showComment()">
        {{myModel.comment}}
    </span>
    <div class="pe-2 ps-2" @click.stop="check" >
      <input type="checkbox" :checked="checkedUuids.indexOf(myModel.uuid)!=-1">
    </div>
  </div>

  <template v-if="myModel.type=='object'  && isOpen">
    <DataPicker v-for="(item, index) in myModel.props" :bound-data="boundData" :variables="variables"
                @checked="checked"
                :path="myPath" :rootUuid="rootUuid" :checked-uuids="checkedUuids"
                :key="index" :intent="intent+1" :model="item" :index="index"></DataPicker>
  </template>
  <DataInfo v-model="detailDlgVisible" :data="myModel"></DataInfo>
</template>

<script lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { layer } from '@layui/layer-vue'
import { useStore } from 'vuex'
import { DataStruct } from '@/store/model'
import DataInfo from '@/components/common/DataInfo.vue'

// 数据绑定数据，可绑定其他变量或者固定值
export default {
  name: 'DataPicker',
  components: { DataInfo },
  props: {
    model: Object, // DataStruct
    checkedUuids: {
      default: () => [],
      type: Array
    }, // 选中的数据uuid
    index: Number,
    intent: Number, // 缩进次数
    rootUuid: String, // model所在根节点记录的uuid
    path: String // 从根到自己到访问路径
  },
  emits: ['checked'],
  setup (props: any, context: any) {
    const myModel = computed<any>(() => props.model)
    const isOpen = ref(true)
    const detailDlgVisible = ref(false)
    const store = useStore()
    const currPage = computed(() => store.state.design.page)
    const myPath = computed(() => (props.path ? props.path + '.' : '') + (myModel.value.name || ''))

    const viewDetail = () => {
      detailDlgVisible.value = true
    }
    const { t } = useI18n()
    const showComment = () => {
      layer.confirm(props.model.comment || 'no doc', { title: 'YDUIBuilder' })
    }

    const toggle = () => {
      if (myModel.value.type === 'object') {
        isOpen.value = !isOpen.value
      }
    }

    /**
     * 选择的数据
     * @param path 访问路径
     * @param data 数据结构体
     * @param rootDataId 数据所在的根数据uuid
     */
    const checked = (path, data: DataStruct, rootDataId, removed) => {
      context.emit('checked', path, data, rootDataId, removed)
    }
    const check = () => {
      checked(myPath.value, myModel.value, props.rootUuid, props.checkedUuids?.indexOf(myModel.value.uuid) !== -1)
    }

    return {
      t,
      myModel,
      detailDlgVisible,
      currPage,
      isOpen,
      myPath,
      viewDetail,
      check,
      toggle,
      showComment,
      checked
    }
  }
}
</script>
