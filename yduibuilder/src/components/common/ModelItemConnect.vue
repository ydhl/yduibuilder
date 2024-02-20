<template>
  <div :class="{'model-item pt-1 pb-1': true, 'bg-light':checkedUuid==myModel.uuid}">
    <div class="model-field text-truncate" @click="click" :style="'width:0px;padding-left: ' + (intent * 16) + 'px'">
      <template v-if="!hideSubData">
        <i v-if="myModel.type=='array' || myModel.type=='object'" :class="{'iconfont':true, 'icon-tree-close': !isOpen, 'icon-tree-open': isOpen}"></i>
        <i v-if="myModel.type!='array' && myModel.type!='object'" style="width: 16px;height: 24px;">&nbsp;</i>
        <template v-if="isArrayItem">
          <span class="text-success fw-bold">ITEM</span>
        </template>
        <template v-else>
          {{myModel.name}}
        </template>
      </template>
      <template v-else>
        {{myModel.name}}
      </template>
      <span :class="'ps-1 param-' + myModel.type">
        {{myModel.type}}
      </span>
      <span class="text-truncate ps-2 pointer text-muted fs-7" @click.stop="showComment()">
        {{myModel.title}}
      </span>
    </div>
    <!--title-->
    <div class="model-title">
      <div v-if="isCheck" class="pe-2" @click="click" >
        <i v-if="['array','object'].indexOf(myModel.type) == -1 || hideSubData" :class="{'iconfont': true, 'icon-checked text-primary': checkedUuid==myModel.uuid, 'icon-unchecked':checkedUuid!=myModel.uuid}"></i>
      </div>
      <template v-else>
        <template v-if="['array','object'].indexOf(myModel.type) != -1">
          <select class="form-select form-select-sm" v-model="myModelValue">
            <option value=""></option>
            <option value="not_empty">Not Empty</option>
            <option value="empty">Empty</option>
          </select>
        </template>
        <template v-else-if="['null','any'].indexOf(myModel.type) == -1">
          <div class="input-group input-group-sm" v-if="myModel.type!='boolean'" >
            <span class="input-group-text">=</span>
            <input type="text" class="form-control-sm form-control" v-model="myModelValue">
          </div>
          <select class="form-select form-select-sm" v-if="myModel.type=='boolean'" v-model="myModelValue">
            <option value=""></option>
            <option value="false">false</option>
            <option value="true">true</option>
          </select>
        </template>
      </template>
    </div>
  </div>
  <template v-if="!hideSubData">
    <template v-if="myModel.type=='array' && isOpen">
      <ModelItemConnect :model="myModel.item" :default-value="defaultValue?.item" @updateCheckedUuid="check"
                 :index="0" :intent="intent+1" :is-check="isCheck" :checked-uuid="checkedUuid" :path="path+'[]'"
                 :is-array-item="myModel.item.type!='array' && myModel.item.type!='object'"></ModelItemConnect>
    </template>
    <template v-else-if="myModel.type=='object' && isOpen">
      <div class="text-muted text-center" v-if="!myModel.props || myModel.props.length==0">{{t('api.model.noSubField')}}</div>
      <template v-else>
        <ModelItemConnect v-for="(item, index) in myModel.props" :checked-uuid="checkedUuid" @updateCheckedUuid="check"
                         :is-check="isCheck" :default-value="defaultValue?.props?.[index]"  :path="(path ? path+'.' : '')+(myModel.name||'')"
                         :key="index" :intent="intent+1" :model="item" :index="index"></ModelItemConnect>
      </template>
    </template>
  </template>
</template>

<script lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { layer } from '@layui/layer-vue'
export default {
  name: 'ModelItemConnect',
  props: {
    model: Object,
    checkedUuid: String, // 选择模式下选中的数据uuid
    isCheck: Boolean, // 是否是选择模式
    defaultValue: Object,
    index: Number,
    isArrayItem: Boolean, // 数组结点标识,用于标识数组的第一个结点
    intent: Number, // 缩进次数
    hideSubData: Boolean, // 是否隐藏array或object的子级数据
    path: String // 从根到自己到访问路径
  },
  emits: ['updateCheckedUuid'],
  setup (props: any, context: any) {
    const myModel = computed<any>(() => props.model)
    const myModelValue = computed<any>({
      get () {
        if (myModel.value.value !== undefined) {
          return myModel.value.value
        }
        return props.defaultValue?.value
      },
      set (v) {
        myModel.value.value = v
      }
    })
    const { t } = useI18n()
    const isOpen = ref(true)
    const showComment = () => {
      layer.confirm(props.model.comment || 'no doc', { title: 'YDUIBuilder' })
    }
    const check = (path, data) => {
      if (!props.isCheck) return
      context.emit('updateCheckedUuid', path, data)
    }
    const click = () => {
      if ((myModel.value.type === 'array' || myModel.value.type === 'object') && !props.hideSubData) {
        isOpen.value = !isOpen.value
        return
      }
      check((props.path ? props.path + '.' : '') + myModel.value.name, myModel.value)
    }

    return {
      t,
      isOpen,
      myModel,
      myModelValue,
      click,
      check,
      showComment
    }
  }
}
</script>
