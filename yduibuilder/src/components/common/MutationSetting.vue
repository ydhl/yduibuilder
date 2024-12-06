<template>
  <div @click="!readonly ? openDialog() : ''" class="pointer">
    <template v-if="hasMutation" >
        <div v-for="(mutation, index) in myAction.mutations" :key="index" class="d-flex align-items-center w-100">
          <span>{{mutation.data_name}}</span>
          <template v-if="mutation.expression_code"><span class="text-muted">{{startBracket(mutation)}}</span>
            <span class="text-success text-truncate">{{mutation.expression_code}}</span>
          </template>
          <span class="text-muted" v-if="endBracket(mutation)">{{endBracket(mutation)}}</span>
        </div>
    </template>
    <div v-else>{{ t('action.notSet') }}</div>
  </div>

  <!-- API Dialog -->
  <lay-layer v-model="dialogVisible" :title="t('variable.mutation')" layer-classes="layui-layer-content-overflow" resize
             :resizing="resizeDialog"
             :shade="true" :area="['600px', layerHeight + 'px']" :btn="buttons">
    <div class="p-2 d-flex flex-column" :style="`height: ${layerHeight-110}px`">
      <div v-if="variables && variables.length > 0">
        <div class="text-muted">{{t('variable.localScopeInEvent', [eventName])}}</div>
        <div class="d-flex align-items-center gap-2 bg-light p-1">
          <div class="pointer comma" v-for="(variable, index) in variables" :key="index" @click="variableModel = variable; detailDlgVisible=true">{{variable.name}}
          <span :class="`param-${variable.type}`">{{variable.type}}</span>
          <span class="text-muted">{{variable.title}} {{variable.comment}}</span>
          </div>
        </div>
      </div>
      <div class="text-muted mt-3">{{t('common.pageData')}}</div>
      <DataMutation :model="data" :default-value="mutationDefault" :variables="variables" :from-uuid="data.uuid"
                  :intent="0" v-for="(data, index) in pageDatas" :key="index"></DataMutation>
    </div>
  </lay-layer>

  <DataInfo v-model="detailDlgVisible" :data="variableModel"></DataInfo>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import ydhl from '@/lib/ydhl'
import { useI18n } from 'vue-i18n'
import DataMutation from '@/components/common/DataMutation.vue'
import { useStore } from 'vuex'
import DataInfo from '@/components/common/DataInfo.vue'

const emit = defineEmits(['update:modelValue'])
const { readonly, variables, autosave, modelValue } = defineProps({
  readonly: Boolean,
  modelValue: Object,
  variables: Object,
  eventName: String,
  autosave: {
    default: true,
    type: Boolean
  } // 是否自动提交接口保存
})
const dialogVisible = ref(false)
const detailDlgVisible = ref(false)
const variableModel = ref({})
const pageDatas = ref<any>([])
const myAction = computed(() => modelValue)
const mutationDefault = ref(JSON.parse(JSON.stringify(myAction.value.mutations || {})))
const hasMutation = computed(() => myAction.value.mutations && Object.keys(myAction.value.mutations).length > 0)
const { t } = useI18n()
const store = useStore()
const selectedPageId = computed(() => store.state.design.page?.meta?.id)
const layerHeight = ref(500)

const loadData = () => {
  ydhl.loading(t('common.pleaseWait')).then((dialogId) => {
    ydhl.get('api/bind/data.json?page_uuid=' + selectedPageId.value, [], (rst) => {
      ydhl.closeLoading(dialogId)
      if (!rst?.success) {
        ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
        return
      }
      pageDatas.value = rst.data.page
      dialogVisible.value = true
    }, 'json')
  })
}
const openDialog = () => {
  loadData()
}
function resizeDialog (e, { width, height }) {
  layerHeight.value = parseInt(height)
}
const update = () => {
  const mymutations = {}
  for (const key in mutationDefault.value) {
    const mutation = mutationDefault.value[key]
    if (!mutation.expression || !mutation.expression.type) continue
    mymutations[key] = mutation
  }
  myAction.value.mutations = JSON.parse(JSON.stringify(mymutations))
  emit('update:modelValue', myAction)
}
const buttons = computed(() => {
  const btns = [
    {
      text: t('common.add'),
      callback: () => {
        dialogVisible.value = false
        if (!autosave) {
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

const startBracket = (mutation) => {
  const operator = mutation.mutation_operator?.trim() || '='
  if (!operator) return '='
  const [start] = operator.split('@')
  return start || '='
}
const endBracket = (mutation) => {
  const operator = mutation.mutation_operator?.trim() || ''
  if (!operator) return ''
  const [, end] = operator.split('@')
  return end || ''
}
</script>
