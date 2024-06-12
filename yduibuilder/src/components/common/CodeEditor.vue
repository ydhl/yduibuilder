<template>
  <lay-layer v-model="myDlgVisible" :title="t('common.customCode')" :shade="true" :area="['520px', '400px']" :btn="buttons">
    <div class="p-3">
      <div ref="codeEditor" style="height: 500px"></div>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, nextTick, ref, watch } from 'vue'
import * as monaco from 'monaco-editor'

// 代码编辑对话框
export default {
  name: 'CodeEditor',
  props: {
    code: String,
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
    const codeEditor = ref()
    const myCode = computed(() => props.code)
    let editorInstance
    const myDlgVisible = computed({
      set (n) {
        context.emit('update:modelValue', n)
      },
      get () {
        return props.modelValue
      }
    })
    watch(myDlgVisible, (n) => {
      if (!n) {
        editorInstance.dispose()
        editorInstance = null
        return
      }
      nextTick(() => {
        if (!editorInstance) {
          // console.log(codeEditor.value)
          editorInstance = monaco.editor.create(codeEditor.value as HTMLElement, {
            roundedSelection: true,
            scrollBeyondLastLine: false,
            readOnly: props.readOnly,
            language: props.language || 'html'
          })
        }
        editorInstance.setValue(myCode.value || '// write you code here')
        monaco.editor.setModelLanguage(editorInstance.getModel(), props.language || 'html')
      })
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
      } else {
        return [
          {
            text: t('common.add'),
            callback: () => {
              myDlgVisible.value = false
              context.emit('update', editorInstance.getValue())
            }
          },
          {
            text: t('common.cancel'),
            callback: () => {
              myDlgVisible.value = false
            }
          }
        ]
      }
    })
    return {
      buttons,
      t,
      myCode,
      codeEditor,
      myDlgVisible
    }
  }
}
</script>
