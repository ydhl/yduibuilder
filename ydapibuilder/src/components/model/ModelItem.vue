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
        <input type="text" class="form-control-sm form-control" :placeholder="t('api.dataStruct.addFieldTip')" v-model="myModel.name">
      </template>
    </div>
    <!--title-->
    <div class="model-title">
      <input type="text" :placeholder="t('api.dataStruct.title')" v-if="!isArrayItem" class="form-control-sm form-control" v-model="myModel.title">
    </div>
    <!--type-->
    <div class="model-type">
        <lay-dropdown updateAtScroll>
          <span :class="'pointer param-'+myModel.type">{{myModel.type}}</span>
          <template #content>
            <lay-dropdown-menu>
              <lay-dropdown-menu-item v-for="(type, index) in ['string','integer','number','boolean','object','array','null','any']"
                                      :key="index" @click="changeType(type)">
                <span :class="'param-'+type">{{type}}</span>
              </lay-dropdown-menu-item>
            </lay-dropdown-menu>
          </template>
        </lay-dropdown>
        <div class="d-flex justify-content-center align-items-center">
          <lay-tooltip :content="t('api.dataStruct.requiredTip')">
            <span @click="myModel.required = !myModel.required" :class="{'pointer fs-6 fw-bold ps-1 pe-1 pt-1': true, 'text-danger': myModel.required, 'text-grey':!myModel.required}">*</span>
          </lay-tooltip>
          <lay-tooltip :content="t('api.dataStruct.nullableTip')">
            <span @click="myModel.nullable = !myModel.nullable" :class="{'pointer fw-bold ps-1 pe-1': true, 'text-danger': myModel.nullable, 'text-grey':!myModel.nullable}">N</span>
          </lay-tooltip>
          <lay-tooltip :content="t('api.dataStruct.deprecatedTip')">
            <span @click="myModel.deprecated = !myModel.deprecated" :class="{'pointer fw-bold ps-1 pe-1': true, 'text-danger': myModel.deprecated, 'text-grey':!myModel.deprecated}">D</span>
          </lay-tooltip>
        </div>
      </div>
    <!--advance-->
    <div class="model-advance ">
      <span class="btn btn-light btn-xs text-secondary" v-if="myModel.type!='null' && myModel.type!='any' && myModel.type!='boolean'" @click="setting()">
        <template v-if="getAdvanceInfo">{{getAdvanceInfo}}</template>
        <template v-else>{{t('api.dataStruct.setting')}}</template>
      </span>
    </div>
    <!--default-->
    <div class="model-default">
      <template v-if="['array','object','null','any'].indexOf(myModel.type) == -1">
        <input type="text" class="form-control-sm form-control"  :placeholder="t('api.dataStruct.defaultValue')" v-if="myModel.type!='boolean'" v-model="myModel.defaultValue">
        <select class="form-select form-select-sm" v-if="myModel.type=='boolean'" v-model="myModel.defaultValue">
          <option value=""></option>
          <option value="false">false</option>
          <option value="true">true</option>
        </select>
      </template>
    </div>
    <!--comment-->
    <div class="model-comment">
      <input type="text" class="form-control-sm form-control" :placeholder="t('api.dataStruct.comment')" v-model="myModel.comment">
    </div>
    <!--mock-->
    <div class="model-mock">
      <input type="text" :placeholder="t('api.dataStruct.mock')" v-if="['array','object','any','boolean','null'].indexOf(myModel.type) == -1" class="form-control-sm form-control" v-model="myModel.mock">
    </div>
    <!--action-->
    <div class="model-action">
      <div class="btn-group-xs btn-group">
        <template v-if="canAdd" >
          <lay-tooltip :content="t(myModel.type =='object' && myModel.isRoot ? 'api.dataStruct.addSubField' : 'api.dataStruct.addNextField')">
            <button type="button" class="btn btn-outline-success btn-xs" @click="add()"><lay-icon type="layui-icon-add-circle"></lay-icon></button>
          </lay-tooltip>
        </template>
        <button type="button" v-if="!myModel.isRoot && !isArrayItem" :class="{'btn  btn-xs': true, 'btn-outline-secondary':!removeConfirm, 'btn-danger':removeConfirm}" @click="remove()">
          <lay-icon :type="{'layui-icon-reduce-circle': !removeConfirm , 'layui-icon-error animate__bounceIn': removeConfirm}"></lay-icon>
        </button>
      </div>
    </div>
  </div>
  <template v-if="myModel.type=='array' && isOpen">
    <ModelItem :model="myModel.item" :index="0" :intent="intent+1" :is-array-item="true"></ModelItem>
  </template>
  <template v-else-if="myModel.type=='object' && isOpen">
    <div class="pt-3 pb-3" v-if="!model.props || model.props.length==0" :style="'padding-left: ' + (intent * 30) + 'px'">
      <span class="text-muted me-3">{{t('api.dataStruct.noSubField')}}</span>
      <button type="button" class="btn btn-primary btn-xs" @click="addSubItem">{{t('api.dataStruct.addField')}}</button>
    </div>
    <template v-else>
      <ModelItem @add="addField" @remove="removeField" v-for="(item, index) in myModel.props" :key="index" :intent="intent+1" :model="item" :index="index"></ModelItem>
    </template>
  </template>
  <teleport to="body" v-if="settingDlgVisible">
    <lay-layer :title="myModel.type + ' ' + t('api.dataStruct.advance')" v-model="settingDlgVisible" :shade="false">
      <div class="p-3">
        <component :is="modelSettingComponent" :model="myModel"/>
      </div>
    </lay-layer>
  </teleport>
</template>

<script lang="ts">
import { computed, defineAsyncComponent, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import { useI18n } from 'vue-i18n'

export default {
  name: 'ModelItem',
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
    const settingDlgVisible = ref(false)
    const removeConfirm = ref(false)
    const isOpen = ref(true)
    const modelSettingComponent = computed(() => {
      if (!myModel.value.type) return null
      return defineAsyncComponent(
        () => new Promise((resolve) => {
          require([`@/components/model/${myModel.value.type}Advance.vue`], resolve)
        }))
    })
    const canAdd = computed(() => {
      if (myModel.value.isRoot) {
        return myModel.value.type === 'object'
      }
      if (props.isArrayItem) return false
      return true
    })

    const remove = () => {
      if (!removeConfirm.value) {
        removeConfirm.value = true
        setTimeout(() => {
          removeConfirm.value = false
        }, 2000)
        return
      }

      context.emit('remove', props.index)
      removeConfirm.value = false
    }
    const removeField = (index: number) => {
      myModel.value.props.splice(index, 1)
    }
    const add = () => {
      if (myModel.value.isRoot) {
        addSubItem()
        return
      }
      context.emit('add', props.index)
    }
    const addField = (index: number) => {
      myModel.value.props.splice(index + 1, 0, { uuid: ydhl.uuid(), type: 'string' })
    }
    const addSubItem = () => {
      if (myModel.value.type === 'object') {
        if (!myModel.value.props) {
          myModel.value.props = []
        }
        myModel.value.props.push({ uuid: ydhl.uuid(), type: 'string' })
      } else if (myModel.value.type === 'array') {
        myModel.value.item = { uuid: ydhl.uuid(), type: 'string' }
      }
    }
    const setting = () => {
      settingDlgVisible.value = true
    }

    const changeType = (type: any) => {
      myModel.value.type = type
      if (type === 'array') {
        myModel.value.item = { uuid: ydhl.uuid(), type: 'string' }
      } else if (type !== 'object') {
        delete myModel.value.props
      }
    }
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
      modelSettingComponent,
      settingDlgVisible,
      getAdvanceInfo,
      isOpen,
      canAdd,
      removeConfirm,
      addSubItem,
      setting,
      changeType,
      remove,
      add,
      removeField,
      addField
    }
  }
}
</script>
