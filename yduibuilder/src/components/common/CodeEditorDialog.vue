<template>
  <lay-layer resize layer-classes="layui-layer-content-overflow" :resizeEnd="recomputed" v-model="myDlgVisible"
             :title="`${title || t('common.customCode')} - ${language}`"
             :shade="true" :area="['800px', '400px']" :btn="buttons">
    <CodeEditor v-if="myDlgVisible" :hide-variable="hideVariable" ref="codeEditor" :editStyle="editStyle" :code="code" :schema="schema" :left-data="leftData" :left-value-path="leftValuePath"
    :surround-code="leftOperator" :tip="tip" :variables="variables" :read-only="readOnly" :language="language"
    ></CodeEditor>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import * as monaco from 'monaco-editor'
import ydhl from '@/lib/ydhl'
import CodeEditor from '@/components/common/CodeEditor.vue'

// 代码编辑对话框
export default {
  name: 'CodeEditorDialog',
  components: { CodeEditor },
  props: {
    code: String,
    schema: Object, // variables json数据的schema
    leftData: Object, // 左值
    leftValuePath: String, // 左值
    leftOperator: String, // 左值操作符
    title: String,
    tip: String,
    ignoreCodeError: {
      default: false,
      type: Boolean
    },
    hideVariable: {
      default: false,
      type: Boolean
    },
    variables: {
      default: () => [],
      type: Array
    },
    readOnly: {
      default: false,
      type: Boolean
    },
    language: {
      default: 'json',
      type: String
    },
    modelValue: Boolean
  },
  emits: ['update:modelValue', 'update'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const editStyle = ref('height: 200px')
    const codeEditor = ref()

    const myDlgVisible = computed({
      set (n) {
        context.emit('update:modelValue', n)
      },
      get () {
        return props.modelValue
      }
    })

    const buttons = computed(() => {
      if (props.readOnly) {
        return [
          {
            text: t('common.ok'),
            callback: () => {
              myDlgVisible.value = false
            }
          }
        ]
      }
      const editorInstance = codeEditor.value?.getEditorInstance()
      return [
        {
          text: t('common.ok'),
          callback: () => {
            if (!editorInstance) return
            if (!props.ignoreCodeError) {
              const model = editorInstance.getModel()
              const markers = monaco.editor.getModelMarkers({ resource: model.uri })
              if (markers.length > 0) {
                ydhl.alert(t('variable.codeError'))
                return
              }
            }
            context.emit('update', editorInstance.getValue().trim())
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
    const recomputed = (id) => {
      const container = document.getElementById(id)
      if (!container) return
      const { width, height } = container.getBoundingClientRect()
      editStyle.value = `height:${height - 200}px;width:${width - 340}px`
    }
    return {
      buttons,
      t,
      recomputed,
      codeEditor,
      editStyle,
      myDlgVisible
    }
  }
}
</script>
