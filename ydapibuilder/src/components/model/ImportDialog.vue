<template>
  <div class="p-2 import-dialog">
    <lay-tab type="brief" v-model="importType">
      <lay-tab-item title="JSON" id="json"></lay-tab-item>
<!--      <lay-tab-item title="XML" id="xml"></lay-tab-item>-->
    </lay-tab>
    <button type="button" class="btn btn-outline-success btn-xs mb-2" @click="format">{{t('api.dataStruct.formatCode')}}</button>
    <div ref="importEditor" style="height: 500px; width: 60vw"></div>
    <div class="d-flex justify-content-between align-items-center pt-2 pb-2">
      <div class="d-flex align-items-center">
        <div class="text-nowrap me-2">{{t('api.dataStruct.mergeType')}}</div>
        <select class="form-select form-control-sm" v-model="mergeType">
          <option value="reserve">{{t('api.dataStruct.mergeReserve')}}</option>
          <option value="replace">{{t('api.dataStruct.mergeReplace')}}</option>
        </select>
      </div>
      <div class="d-flex align-items-center">
        <button class="btn btn-outline-secondary btn-sm me-3" @click="cancel">{{t('common.cancel')}}</button>
        <button class="btn btn-primary btn-sm me-3" @click="ok">{{t('common.ok')}}</button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { nextTick, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import * as monaco from 'monaco-editor'

export default {
  name: 'ImportDialog',
  setup (props: any, context: any) {
    const mergeType = ref('replace')
    const importType = ref('json')
    const importEditor = ref()
    const format = () => {
      if (!editorInstance) {
        return
      }
      editorInstance.getAction('editor.action.formatDocument').run()
      editorInstance.setValue(editorInstance.getValue())
    }

    let editorInstance
    const { t } = useI18n()
    onMounted(() => {
      nextTick(() => {
        // editor.getAction('editor.action.formatDocument').run()
        if (!editorInstance) {
          editorInstance = monaco.editor.create(importEditor.value as HTMLElement, {
            roundedSelection: true,
            scrollBeyondLastLine: false,
            readOnly: false,
            language: importType.value
          })
        }
        monaco.editor.setModelLanguage(editorInstance.getModel(), importType.value)
      })
    })
    const ok = () => {
      editorInstance.getAction('editor.action.formatDocument').run()
      editorInstance.setValue(editorInstance.getValue())
      context.emit('ok', { mergeType: mergeType.value, code: editorInstance.getValue() })
    }
    const cancel = () => {
      context.emit('cancel')
    }
    return {
      importType,
      importEditor,
      mergeType,
      format,
      ok,
      cancel,
      t
    }
  }
}
</script>
