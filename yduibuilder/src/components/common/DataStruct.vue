<template>
  <div class="style-panel pt-1">
    <div class="style-header d-flex align-items-center justify-content-between">
      <div class="fs-6 text-muted flex-grow-1">
        <i class="iconfont icon-data"></i>&nbsp;{{dataTitle}}&nbsp;
        <small class="fs-7" v-if="!datas || datas.length > 0">{{datas.length}} items</small>
      </div>
      <div v-if="!openState" @click.stop="openState=true"><i class="iconfont hover-primary icon-expandall"></i></div>
      <div v-if="openState" @click.stop="openState=false"><i class="iconfont hover-primary icon-collapseall"></i></div>
      <button v-if="canMutation" type="button" class="btn btn-primary btn-xs ms-3" @click.stop.prevent="dialogVisible = true">{{t('api.addData')}}</button>
    </div>
    <div class="style-body d-none">
      <div v-if="!datas || datas.length===0" class="p-3 text-muted">
        <i class="iconfont icon-wuneirong"></i> {{t('common.empty')}}
      </div>
      <Data v-for="(data, index) in datas" :open="openState"
                 @remove="removeItem" @update="updateItem" :from-type="fromType" :from-id="data.uuid"
                 :can-input="canInput" :can-output="canOutput" :can-mutation="canMutation"
                 :index="index" :model="data" :intent="0" :key="index"></Data>
    </div>
  </div>
  <lay-layer v-model="dialogVisible" :title="t('api.addData')" :shade="true" :area="['520px', '500px']" :btn="buttons">
    <AddData :types="types" v-model="currData"/>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import Data from '@/components/common/Data.vue'
import { layer } from '@layui/layer-vue'
import AddData from '@/components/common/AddData.vue'
import ydhl from '@/lib/ydhl'
// 数据结构展示
export default {
  name: 'DataStruct',
  emits: ['update', 'remove'],
  props: {
    datas: Array,
    fromType: { // 数据来源那个表
      type: String,
      default: 'bind_data'
    },
    types: {
      type: Array,
      default: () => ['string', 'integer', 'number', 'boolean', 'object', 'array', 'map', 'null', 'any']
    },
    canMutation: Boolean, // 能否修改数据
    canInput: Boolean, // 能绑定ui提供输入
    canOutput: Boolean, // 能绑定ui作为输出
    dataTitle: String
  },
  components: { AddData, Data },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const dialogVisible = ref(false)
    const currData = ref<any>({ type: 'string', uuid: ydhl.uuid() })
    const myDatas = computed<any>(() => props.datas)
    const openState = ref(false)
    const buttons = ref([
      {
        text: t('common.add'),
        callback: () => {
          if (!currData.value.name) {
            layer.msg(t('api.pleaseInputName'))
            return
          }
          if (!currData.value.name.match(/^[a-zA-Z_][a-zA-Z0-9_]*$/)) {
            layer.msg(t('api.inputNameInvalid'))
            return
          }
          context.emit('update', JSON.parse(JSON.stringify(currData.value)), -1)
          currData.value = { type: 'string', uuid: ydhl.uuid() }
          dialogVisible.value = false
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          dialogVisible.value = false
        }
      }
    ])
    const removeItem = (index) => {
      removeData(myDatas.value[index], index)
    }
    const updateItem = (index: number, item: any) => {
      context.emit('update', item, index)
    }

    const removeData = (data, index) => {
      context.emit('remove', data, index)
    }

    return {
      t,
      currData,
      dialogVisible,
      buttons,
      openState,
      removeItem,
      updateItem
    }
  }
}
</script>
