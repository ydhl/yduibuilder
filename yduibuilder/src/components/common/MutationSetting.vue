<template>
  <div @click="openDialog" class="pointer d-flex align-items-center text-truncate">
    <template v-if="hasMutation" >
      <div>
        <div v-for="(mutation, index) in myAction.mutations" :key="index" class="d-flex align-items-center text-truncate">
          <span>{{mutation.data_name}}</span>
          <template v-if="mutation.expression_code">&nbsp;=&nbsp;
            <span class="text-success">{{mutation.expression_code}}</span>
          </template>
        </div>
      </div>
    </template>
    <div v-else>{{ t('action.notSet') }}</div>
  </div>

  <!-- API Dialog -->
  <lay-layer v-model="dialogVisible" :title="t('variable.mutation')" resize :shade="true" :area="['800px', '90vh']" :btn="buttons">
    <div class="p-2">
      <DataMutation :model="data" :default-value="mutationDefault" :variables="variables" :from-uuid="data.uuid" :intent="0" v-for="(data, index) in pageDatas" :key="index"></DataMutation>
      <DataMutation :model="data" :default-value="mutationDefault" :variables="variables" :from-uuid="data.uuid" :intent="0" v-for="(data, index) in queryDatas" :key="index"></DataMutation>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { ref, computed } from 'vue'
import ydhl from '@/lib/ydhl'
import { useI18n } from 'vue-i18n'
import DataMutation from '@/components/common/DataMutation.vue'
import { useStore } from 'vuex'

export default {
  name: 'MutationSetting',
  components: { DataMutation },
  props: {
    modelValue: Object,
    variables: Object,
    autosave: {
      default: true,
      type: Boolean
    } // 是否自动提交接口保存
  },
  emits: ['update:modelValue'],
  setup (props: any, context: any) {
    const dialogVisible = ref(false)
    const pageDatas = ref([])
    const queryDatas = ref([])
    const myAction = ref(props.modelValue)
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
          dialogVisible.value = true
        }, 'json')
      })
    }
    const openDialog = () => {
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

    return {
      dialogVisible,
      myAction,
      t,
      pageDatas,
      queryDatas,
      hasMutation,
      openDialog,
      mutationDefault,
      buttons
    }
  }
}
</script>
