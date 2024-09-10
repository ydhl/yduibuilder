<template>
  <template v-if="jsonStruct">
    <!--根结点-->
    <div class="d-flex mb-3">
      <div class="btn-group btn-group-sm">
        <button @click="tab='datastruct'" :class="{'btn': true, 'btn-secondary':tab=='datastruct', 'btn-outline-secondary':tab!='datastruct'}" type="button">{{t('api.dataStruct.dataStruct')}}</button>
        <button @click="tab='preview'" :class="{'btn': true, 'btn-secondary':tab=='preview', 'btn-outline-secondary':tab!='preview'}" type="button">{{t('common.preview')}}</button>
      </div>
      <div class="btn-group btn-group-sm ms-5">
<!--        <button class="btn btn-outline-secondary" type="button" v-if="tab=='preview'">{{t('common.refresh')}}</button>-->
        <button class="btn btn-outline-secondary" @click="importDialogVisible=true" type="button" v-if="tab=='datastruct'">{{t('api.dataStruct.import')}}</button>
      </div>
    </div>
    <ModelItem v-if="tab=='datastruct'" :intent="0" :model="jsonStruct" :index="0"></ModelItem>
    <div id="preivewEditor" v-if="tab=='preview'" style="height: 500px"></div>
  </template>
  <template v-else>
    <lay-empty></lay-empty>
  </template>
  <teleport to="body">
    <lay-layer v-model="importDialogVisible" :shade="true" :title="t('api.dataStruct.import')">
      <ImportDialog @ok="importData" @cancel="importDialogVisible = false"></ImportDialog>
    </lay-layer>
  </teleport>
</template>

<script lang="ts">
import { ref, onMounted, nextTick, watch, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { layer } from '@layui/layui-vue'
import * as monaco from 'monaco-editor'
import ydhl from '@/lib/ydhl'
import ModelItem from '@/components/model/ModelItem.vue'
import ImportDialog from '@/components/model/ImportDialog.vue'
export default {
  name: 'DataJSON',
  components: { ImportDialog, ModelItem },
  props: {
    jsonData: Object
  },
  emits: ['update'],
  setup (props: any, context: any) {
    const loading = ref(false)
    const importDialogVisible = ref(false)
    const tab = ref('datastruct')
    const { t } = useI18n()
    const jsonStruct = computed({
      get () {
        return props.jsonData
      },
      set (v) {
        context.emit('update', v)
      }
    })
    let editorInstance
    watch(tab, (v) => {
      if (v !== 'preview') {
        editorInstance = null
        return
      }
      nextTick(() => {
        if (!editorInstance) {
          editorInstance = monaco.editor.create(document.getElementById('preivewEditor') as HTMLElement, {
            roundedSelection: true,
            scrollBeyondLastLine: false,
            readOnly: false,
            language: 'json'
          })
        }
        editorInstance.setValue(JSON.stringify(ydhl.mockJson(props.jsonData)))
        editorInstance.getAction('editor.action.formatDocument').run()
        editorInstance.setValue(editorInstance.getValue())
      })
    })

    const importData = ({ mergeType, code }) => {
      importDialogVisible.value = false
      const id = layer.load(2)
      // console.log(code)
      let json
      try {
        json = JSON.parse(code)
      } catch (e: any) {
        ydhl.alert('Parse Error: ' + (e?.message || ''), t('common.ok'))
        return
      }
      layer.close(id)
      console.log(json)
      jsonStruct.value = ydhl.parseJsonData('', true, json)
    }
    onMounted(() => {
      if (!jsonStruct.value) jsonStruct.value = { uuid: ydhl.uuid(), type: 'object', isRoot: true }
    })
    return {
      loading,
      t,
      tab,
      importDialogVisible,
      jsonStruct,
      importData
    }
  }
}
</script>
