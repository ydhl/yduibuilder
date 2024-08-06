<template>
  <div class="d-flex">
    <div class="input-group input-group-sm" style="min-width: 150px" >
      <div class="input-group-text" v-if="prefix">{{ prefix }}</div>
      <input type="text" @click.stop.prevent class="form-control form-control-sm" v-model.trim="customModifier"/>
    </div>
    <button type="button" :disabled="error?true:false" class="btn btn-sm btn-light ms-1" @click="updateModifier">{{t('common.ok')}}</button>
  </div>
  <div class="fs-7 text-danger" v-if="error">{{error}}</div>
  <div class="fs-7 text-muted" v-if="customModifierPreview"><span>{{t('common.preview')}}: </span>{{customModifierPreview}}</div>
</template>

<script lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

export default {
  name: 'CustomModifier',
  emits: ['update'],
  props: {
    dataName: String,
    defaultModifier: String,
    prefix: String
  },
  setup (props: any, context: any) {
    const customModifier = ref(props.defaultModifier || '')
    const { t } = useI18n()
    const error = computed(() => {
      if (!customModifier.value) return ''
      if (!customModifier.value.match(/@/)) return t('expression.customError')
      return ''
    })
    const customModifierPreview = computed(() => {
      if (!customModifier.value) return ''
      return (props.prefix || '') + customModifier.value.replace(/@/, props.dataName)
    })
    const updateModifier = () => {
      if (!customModifier.value) return
      context.emit('update', customModifier.value)
    }
    return {
      customModifier,
      error,
      customModifierPreview,
      t,
      updateModifier
    }
  }
}
</script>
