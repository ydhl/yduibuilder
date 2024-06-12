<template>
  <div class="model-item">
    <div class="model-field" :style="'padding-left: ' + (intent * 30) + 'px'">
      <i @click="isOpen = !isOpen" v-if="myModel.type=='array' || myModel.type=='object'" :class="{'treeicon iconfont':true, 'icon-tree-close': !isOpen, 'icon-tree-open': isOpen}"></i>
      <!--field-->
      <template v-if="myModel.isRoot">
        <span class="text-primary fw-bold">{{t('api.dataStruct.rootNode')}}</span>
      </template>
      <template v-else-if="isArrayItem">
        <span class="text-success fw-bold">ITEM</span>
      </template>
      <template v-else>
        <div class="form-control-plaintext">{{myModel.name}}</div>
      </template>
    </div>
    <!--title-->
    <div class="model-title">
      <div class="form-control-plaintext" v-if="!isArrayItem" >{{myModel.title}}</div>
    </div>
    <!--type-->
    <div class="model-type">
      {{myModel.type}}
      <div class="d-flex justify-content-center align-items-center">
        <lay-tooltip :content="t('api.dataStruct.requiredTip')">
          <span :class="{'fs-6 fw-bold ps-1 pe-1 pt-1': true, 'text-danger': myModel.required, 'text-grey':!myModel.required}">*</span>
        </lay-tooltip>
        <lay-tooltip :content="t('api.dataStruct.nullableTip')">
          <span :class="{'fw-bold ps-1 pe-1': true, 'text-danger': myModel.nullable, 'text-grey':!myModel.nullable}">N</span>
        </lay-tooltip>
        <lay-tooltip :content="t('api.dataStruct.deprecatedTip')">
          <span :class="{'fw-bold ps-1 pe-1': true, 'text-danger': myModel.deprecated, 'text-grey':!myModel.deprecated}">D</span>
        </lay-tooltip>
      </div>
    </div>
    <!--advance-->
    <div class="model-advance ">
      <template v-if="myModel.type!='null' && myModel.type!='any' && myModel.type!='boolean'">
        <template v-if="getAdvanceInfo">{{getAdvanceInfo}}</template>
      </template>
    </div>
    <!--default-->
    <div class="model-default">
      <template v-if="['array','object','null','any'].indexOf(myModel.type) == -1">
        {{myModel.defaultValue}}
      </template>
    </div>
    <!--comment-->
    <div class="model-comment">
      {{myModel.comment}}
    </div>
    <!--mock-->
    <div class="model-mock">
      <span v-if="['array','object','any','boolean','null'].indexOf(myModel.type) == -1" class="form-control-plaintext">{{myModel.mock}}</span>
    </div>
  </div>
  <template v-if="myModel.type=='array' && isOpen">
    <ModelItemReadonly :model="myModel.item" :index="0" :intent="intent+1" :is-array-item="true"></ModelItemReadonly>
  </template>
  <template v-else-if="myModel.type=='object' && isOpen">
    <div class="pt-3 pb-3" v-if="!model.props || model.props.length==0" :style="'padding-left: ' + (intent * 30) + 'px'">
      <span class="text-muted me-3">{{t('api.dataStruct.noSubField')}}</span>
    </div>
    <template v-else>
      <ModelItemReadonly v-for="(item, index) in myModel.props" :key="index" :intent="intent+1" :model="item" :index="index"></ModelItemReadonly>
    </template>
  </template>
</template>

<script lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

export default {
  name: 'ModelItemReadonly',
  props: {
    model: Object,
    isArrayItem: Boolean, // 数组结点标识,用于标识数组的第一个结点
    intent: Number, // 缩进次数
    index: Number // 当前元素在父级中的索引
  },
  emits: ['remove', 'add'],
  setup (props: any, context: any) {
    const myModel = computed(() => props.model)
    const { t } = useI18n()
    const isOpen = ref(true)
    const getAdvanceInfo = computed(() => {
      const { min, max, pattern, constValue, enumValue, unique, format } = myModel.value
      const info: any = []
      if (min && max) {
        info.push(`[${min},${max}]`)
      } else if (min) {
        info.push(`[${min},-]`)
      } else if (max) {
        info.push(`[-,${max}]`)
      }
      if (pattern) info.push(pattern)
      if (format) info.push(format)
      if (unique) info.push(t('api.dataStruct.unique'))
      if (constValue) info.push(constValue)
      const enums: any = []
      for (const enumV in enumValue) {
        enums.push(enumV)
      }
      if (enums) info.push(enums.join(','))
      return info.length > 0 ? info.join(';') : null
    })

    return {
      myModel,
      t,
      getAdvanceInfo,
      isOpen
    }
  }
}
</script>
