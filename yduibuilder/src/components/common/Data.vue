<template>
  <div class="style-panel pt-2">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="fs-6 text-muted"><i class="iconfont icon-data"></i>{{dataTitle}}</div>
      <button v-if="canMutation" type="button" class="btn btn-primary btn-sm" @click="dialogVisible = true">{{t('api.addData')}}</button>
    </div>
    <div v-if="!datas || datas.length===0" class="text-center text-muted">
      <i class="iconfont icon-wuneirong"></i> {{t('common.empty')}}
    </div>
    <ModelItem v-for="(data, index) in datas"
               @remove="removeItem" @update="updateItem" from-type="bind_data" :from-id="data.uuid"
               :can-input="canInput" :can-output="canOutput" :can-mutation="canMutation"
               :index="index" :model="data" :intent="0" :key="index"></ModelItem>
  </div>
  <lay-layer v-model="dialogVisible" :title="t('api.addData')" :shade="true" :area="['520px', '300px']" :btn="buttons">
    <AddModelItem :types="types" v-model="currData"/>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import ModelItem from '@/components/common/ModelItem.vue'
import { layer } from '@layui/layer-vue'
import AddModelItem from '@/components/common/AddModelItem.vue'
import ydhl from '@/lib/ydhl'

export default {
  name: 'Data',
  emits: ['update', 'remove'],
  props: {
    datas: Array,
    types: {
      type: Array,
      default: () => ['string', 'integer', 'number', 'boolean', 'object', 'array', 'null', 'any']
    },
    canMutation: Boolean,
    canInput: Boolean,
    canOutput: Boolean,
    dataTitle: String
  },
  components: { AddModelItem, ModelItem },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const dialogVisible = ref(false)
    const currData = ref<any>({ type: 'string', uuid: ydhl.uuid() })
    const myDatas = computed<any>(() => props.datas)
    const buttons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          if (!currData.value.name) {
            layer.msg(t('api.pleaseInputName'))
            return
          }
          context.emit('update', JSON.parse(JSON.stringify(currData.value)))
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
      removeData(myDatas.value[index])
    }
    const updateItem = (index: number, item: any) => {
      context.emit('update', JSON.parse(JSON.stringify(item)))
    }

    const removeData = (data) => {
      context.emit('remove', data)
    }

    return {
      t,
      currData,
      dialogVisible,
      buttons,
      removeItem,
      updateItem
    }
  }
}
</script>
