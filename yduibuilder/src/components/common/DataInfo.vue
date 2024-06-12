<template>
  <lay-layer v-model="myDetailDlgVisible" :title="t('api.dataInfo')" :shade="true" :area="['520px', '400px']">
    <div class="p-3">
      <table class="table table-striped table-hover table-responsive table-sm">
        <tbody>
        <template v-for="(value, name, index) in data" :key="index">
          <tr v-if="['props','item','uuid'].indexOf(name) == -1">
            <td>{{name}}</td>
            <td>{{value}}</td>
          </tr>
        </template>
        </tbody>
      </table>
      <div class="text-muted">Preview:</div>
      <pre v-html="previewCode"></pre>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

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
    const myDetailDlgVisible = computed({
      set (n) {
        context.emit('update:modelValue', n)
      },
      get () {
        return props.modelValue
      }
    })
    const previewCode = computed(() => {
      return 'TODO'
    })
    return {
      t,
      myDetailDlgVisible,
      previewCode
    }
  }
}
</script>
