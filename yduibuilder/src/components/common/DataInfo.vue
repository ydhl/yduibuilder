<template>
  <lay-layer v-model="myDetailDlgVisible" :title="t('api.dataInfo')" :shade="true" :area="['800px', '500px']">
    <div class="p-3 d-flex align-items-start">
      <table class="table table-striped table-hover table-responsive table-sm">
        <tbody>
        <template v-for="(value, name, index) in data" :key="index">
          <tr v-if="['props','item','uuid', 'out' ,'bound', 'in'].indexOf(name) == -1">
            <td>{{name}}</td>
            <td>{{value}}</td>
          </tr>
        </template>
        </tbody>
      </table>
      <div>
        <div class="text-muted">Preview:</div>
        <div ref="previewCode" style="height: 400px;width: 400px"></div>
      </div>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, nextTick, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import * as monaco from 'monaco-editor'

// 数据详细信息
export default {
  name: 'DataInfo',
  props: {
    data: Object, // DataStruct
    modelValue: Boolean
  },
  emits: ['update:modelValue'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    let editorInstance
    const myDetailDlgVisible = computed({
      set (n) {
        context.emit('update:modelValue', n)
      },
      get () {
        return props.modelValue
      }
    })
    const previewCode = ref()

    watch(myDetailDlgVisible, (n) => {
      if (!n) {
        editorInstance.dispose()
        editorInstance = null
        return
      }
      if (editorInstance) return
      nextTick(() => {
        editorInstance = monaco.editor.create(previewCode.value as HTMLElement, {
          roundedSelection: true,
          scrollBeyondLastLine: false,
          minimap: {
            enabled: false
          },
          readOnly: false,
          language: 'json',
          value: JSON.stringify(ydhl.mockJson(props.data), null, 2) || ''
        })

        monaco.editor.setModelLanguage(editorInstance.getModel(), 'json')
      })
    })
    return {
      t,
      myDetailDlgVisible,
      previewCode
    }
  }
}
</script>
