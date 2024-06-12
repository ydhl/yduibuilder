<template>
  <select ref="label" multiple class="form-select">
    <option v-for="(label, value, index) in selected" :value="value" selected :key="index">{{label}}</option>
  </select>
</template>

<script lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
declare const $: any
export default {
  name: 'LabelSelect',
  props: {
    url: String,
    modelValue: Object
  },
  setup (props: any, context: any) {
    const label = ref()
    const selected = ref(props.modelValue) // 由于下面change.select2的原因，这里不能用computed，只能ref + watch
    watch(() => props.modelValue, (v) => {
      selected.value = v
    })
    const store = useStore()
    const project = computed(() => store.state.design.project)
    const { t } = useI18n()
    onMounted(() => {
      const control: any = $(label.value)
      control.select2({
        theme: 'bootstrap-5',
        placeholder: t('api.labelAddTip'),
        tags: true,
        language: 'zh-CN',
        ajax: {
          url: props.url,
          headers: {
            token: ydhl.getJwt()
          },
          delay: 250,
          dataType: 'json',
          data: function (params) {
            params.project_uuid = project.value.id
            return params
          },
          processResults: function (data) {
            return {
              results: data.data
            }
          }
        }
      })
      control.on('change.select2', function (e) {
        selected.value = {}
        for (const item of control.select2('data')) {
          selected.value[item.id] = item.text
        }
        context.emit('update:modelValue', selected.value)
        selected.value = {}
      })
    })
    return {
      label,
      selected,
      t
    }
  }
}
</script>
