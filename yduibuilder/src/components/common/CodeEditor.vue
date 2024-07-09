<template>
  <lay-layer resize :resizeEnd="recomputed" v-model="myDlgVisible" :title="title || t('common.customCode')" :shade="true" :area="['680px', '400px']" :btn="buttons">
    <div class="p-3" ref="editorContainer">
      <div class="text-danger p-1 m-1 fs-7">{{tip}}</div>
      <div ref="codeEditor" :style="editStyle"></div>
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
    title: String,
    tip: String,
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
    const editorContainer = ref()
    const editStyle = ref('height: 500px; width:100%')
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
        editorInstance.getAction('editor.action.formatDocument').run()
        editorInstance.setValue(editorInstance.getValue())
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
            text: t('common.ok'),
            callback: () => {
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
    const recomputed = () => {
      const { width, height } = editorContainer.value.getBoundingClientRect()
      editStyle.value = `height:${height - 40}px;width:${width - 40}px`
      nextTick(() => {
        if (editorInstance) editorInstance.layout()
      })
    }
    return {
      buttons,
      t,
      myCode,
      codeEditor,
      editorContainer,
      editStyle,
      recomputed,
      myDlgVisible
    }
  }
}
</script>
