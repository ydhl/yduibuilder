<template>
  <div @click="!readonly ? openDialog() : ''" class="pointer">
    <template v-if="hasMutation" >
        <div v-for="(mutation, index) in myAction.mutations" :key="index" class="d-flex align-items-center w-100">
          <span>{{mutation.data_name}}</span>
          <template v-if="mutation.expression_code"><span class="text-muted">{{mutation.mutation_operator ? mutation.mutation_operator.replace(/@/, '').replace(/\([^)]*\)/,'(') : '='}}</span>
            <span class="text-success text-truncate">{{mutation.expression_code}}</span>
          </template>
          <span class="text-muted" v-if="mutation.mutation_operator?.endsWith(')')">)</span>
        </div>
    </template>
    <div v-else>{{ t('action.notSet') }}</div>
  </div>

  <!-- API Dialog -->
  <lay-layer v-model="dialogVisible" layer-classes="layui-layer-content-overflow" :title="t('variable.mutation')"  resize :shade="true" :area="['600px', '60vh']" :btn="buttons">
    <div class="p-2">
      <table v-if="variables && variables.length > 0" class="table table-sm table-borderless">
        <tbody>
        <tr>
          <th colspan="3" class="text-muted">{{t('variable.localScopeInEvent', [eventName])}}</th>
        </tr>
        </tbody>
        <tr v-for="(variable, index) in variables" :key="index">
          <td>{{variable.name}}</td>
          <td :class="`param-${variable.type}`">{{variable.type}}</td>
          <td>{{variable.title}} {{variable.comment}}</td>
        </tr>
      </table>
      <div class="mb-2">
        <AdvanceSelect btn-size="btn-sm btn-light" :options="leftValues" :default-text="t('variable.pleaseChooseMutationData')" @change="(option)=>updateLeftValue(option.data)"></AdvanceSelect>
      </div>
      <DataMutation :model="data" :default-value="mutationDefault" :variables="variables" :from-uuid="data.uuid"
                    :intent="0" v-for="(data, index) in mutatedDatas" :key="index"></DataMutation>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { ref, toRef, computed } from 'vue'
import ydhl from '@/lib/ydhl'
import { useI18n } from 'vue-i18n'
import DataMutation from '@/components/common/DataMutation.vue'
import { useStore } from 'vuex'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'

export default {
  name: 'MutationSetting',
  components: { AdvanceSelect, DataMutation },
  props: {
    readonly: Boolean,
    modelValue: Object,
    variables: Object,
    eventName: String,
    autosave: {
      default: true,
      type: Boolean
    } // 是否自动提交接口保存
  },
  emits: ['update:modelValue'],
  setup (props: any, context: any) {
    const dialogVisible = ref(false)
    const dataType = ref('page')
    const pageDatas = ref<any>([])
    const queryDatas = ref<any>([])
    const mutatedDatas = ref<any>([])
    const myAction = toRef(props, 'modelValue')
    const mutationDefault = ref(JSON.parse(JSON.stringify(myAction.value.mutations || {})))
    const hasMutation = computed(() => myAction.value.mutations && Object.keys(myAction.value.mutations).length > 0)
    const { t } = useI18n()
    const store = useStore()
    const selectedPageId = computed(() => store.state.design.page?.meta?.id)

    const loadData = () => {
      ydhl.loading(t('common.pleaseWait')).then((dialogId) => {
        ydhl.get('api/bind/data.json?page_uuid=' + selectedPageId.value, [], (rst) => {
          ydhl.closeLoading(dialogId)
          if (!rst?.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          pageDatas.value = rst.data.page
          queryDatas.value = rst.data.query
          for (const mutationDataId in mutationDefault.value) {
            mutatedDatas.value.push(...pageDatas.value.filter(item => item.uuid === mutationDataId))
          }
          for (const mutationDataId in mutationDefault.value) {
            mutatedDatas.value.push(...queryDatas.value.filter(item => item.uuid === mutationDataId))
          }
          dialogVisible.value = true
        }, 'json')
      })
    }
    const openDialog = () => {
      mutatedDatas.value = []
      loadData()
    }
    const update = () => {
      const mymutations = {}
      for (const key in mutationDefault.value) {
        const mutation = mutationDefault.value[key]
        if (!mutation.expression || !mutation.expression.type) continue
        mymutations[key] = mutation
      }
      myAction.value.mutations = JSON.parse(JSON.stringify(mymutations))
      context.emit('update:modelValue', myAction)
    }
    const buttons = computed(() => {
      const btns = [
        {
          text: t('common.add'),
          callback: () => {
            dialogVisible.value = false
            if (!props.autosave) {
              update()
              return
            }
            const mutation = {}
            for (const m in mutationDefault.value) {
              if (mutationDefault.value[m].expression && mutationDefault.value[m].expression.type) mutation[m] = mutationDefault.value[m]
            }
            ydhl.loading(t('common.pleaseWait')).then((id) => {
              ydhl.postJson('api/action/mutation.json',
                {
                  page_uuid: selectedPageId.value,
                  action_uuid: myAction.value.uuid,
                  mutations: mutation
                }).then((rst) => {
                ydhl.closeLoading(id)
                update()
              })
            })
          }
        },
        {
          text: t('common.cancel'),
          callback: () => {
            dialogVisible.value = false
          }
        }
      ]
      return btns
    })
    const leftValues = computed(() => {
      const values: any = []
      if (pageDatas.value.length > 0) {
        values.push({ name: t('variable.pageScope'), disabled: true })
      }
      for (const data of pageDatas.value) {
        values.push({ name: data.name, data, value: data.name, desc: data.type + ' ' + (data.title || '') + (data.comment || '') })
      }
      if (queryDatas.value.length > 0) {
        values.push({ name: t('action.queryParams'), disabled: true })
      }
      for (const data of queryDatas.value) {
        values.push({ name: data.name, data, value: data.name, desc: data.type + ' ' + (data.title || '') + (data.comment || '') })
      }
      return values
    })
    const updateLeftValue = (data) => {
      mutatedDatas.value.push(data)
      mutationDefault.value[data.uuid] = { from_uuid: data.uuid, data_name: data.name, data_type: data.type }
    }
    return {
      dialogVisible,
      myAction,
      t,
      pageDatas,
      queryDatas,
      hasMutation,
      dataType,
      openDialog,
      mutationDefault,
      updateLeftValue,
      mutatedDatas,
      leftValues,
      buttons
    }
  }
}
</script>
