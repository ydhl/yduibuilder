<template>
  <lay-layer v-model="myDlgVisible" :title="t('expression.editor')" resize :shade="true" :area="['800px', '600px']" :btn="buttons">
    <div class="p-3 d-flex align-items-start justify-content-start">
      <Expression :expression="myExpression" :variables="variables"></Expression>
    </div>
    <div class="text-muted mt-3 p-3">{{myExpressionPreview}}</div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import { useStore } from 'vuex'
import Expression from '@/components/common/Expression.vue'
import { Expression as ExpressionModel } from '@/store/model'
import ydhl from '@/lib/ydhl'

// 表达式编辑对话框
export default {
  name: 'ExpressionEditor',
  components: { Expression },
  props: {
    expression: Object,
    variables: Object,
    modelValue: Boolean
  },
  emits: ['update:modelValue', 'update'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const store = useStore()
    const codeEditor = ref()
    const chooseDataVisible = ref(false)
    const checkUuid = ref('')
    const selectedPageId = computed(() => store.state.design.page?.meta?.id)
    const myExpression = ref<ExpressionModel>(JSON.parse(JSON.stringify(props.expression)))
    const myExpressionPreview = computed(() => {
      return ydhl.getExpressionDesc(myExpression.value)
    })
    const myDlgVisible = computed({
      set (n) {
        context.emit('update:modelValue', n)
      },
      get () {
        return props.modelValue
      }
    })
    const buttons = computed(() => {
      return [
        {
          text: t('common.add'),
          callback: () => {
            context.emit('update', myExpression.value)
            myDlgVisible.value = false
          }
        },
        {
          text: t('common.cancel'),
          callback: () => {
            myDlgVisible.value = false
          }
        }
      ]
    })

    return {
      buttons,
      myExpression,
      myExpressionPreview,
      checkUuid,
      t,
      codeEditor,
      myDlgVisible,
      chooseDataVisible,
      selectedPageId
    }
  }
}
</script>
