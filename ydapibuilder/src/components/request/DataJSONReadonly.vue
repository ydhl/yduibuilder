<template>
  <template v-if="jsonStruct">
    <!--根结点-->
    <div class="d-flex mb-3">
      <div class="btn-group btn-group-sm">
        <button @click="tab='datastruct'" :class="{'btn': true, 'btn-secondary':tab=='datastruct', 'btn-outline-secondary':tab!='datastruct'}" type="button">{{t('api.dataStruct.dataStruct')}}</button>
        <button @click="tab='preview'" :class="{'btn': true, 'btn-secondary':tab=='preview', 'btn-outline-secondary':tab!='preview'}" type="button">{{t('common.preview')}}</button>
      </div>
    </div>
    <ModelItem v-if="tab=='datastruct'" :intent="0" :model="jsonStruct" :index="0"></ModelItem>
    <div ref="previewEditor" v-if="tab=='preview'" style="height: 500px"></div>
  </template>
  <template v-else>
    <lay-empty></lay-empty>
  </template>
</template>

<script lang="ts">
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import ModelItem from '@/components/model/ModelItemReadonly.vue'
import * as monaco from 'monaco-editor'
import ydhl from '@/lib/ydhl'
export default {
  name: 'DataJSONReadolny',
  components: { ModelItem },
  props: {
    jsonData: Object
  },
  emits: ['update'],
  setup (props: any, context: any) {
    const loading = ref(false)
    const previewEditor = ref()
    const tab = ref('datastruct')
    const { t } = useI18n()
    const jsonStruct = computed(() => props.jsonData || {})
    let editorInstance
    watch(previewEditor, (v) => {
      if (!v) return
      editorInstance = monaco.editor.create(previewEditor.value as HTMLElement, {
        roundedSelection: true,
        scrollBeyondLastLine: false,
        readOnly: false,
        language: 'json'
      })
      editorInstance.setValue(JSON.stringify(ydhl.mockJson(props.jsonData)))
      editorInstance.getAction('editor.action.formatDocument').run()
      editorInstance.setValue(editorInstance.getValue())
    })
    return {
      loading,
      previewEditor,
      t,
      tab,
      jsonStruct
    }
  }
}
</script>
