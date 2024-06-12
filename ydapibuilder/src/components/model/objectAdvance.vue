<template>
  <div class="row">
    <div class="col">
      <label>{{t("api.dataStruct.minProp")}}</label>
      <input type="text" class="form-control form-control-sm" v-model="myModel.min">
    </div>
    <div class="col">
      <label>{{t("api.dataStruct.maxProp")}}</label>
      <input type="text" class="form-control form-select-sm" v-model="myModel.max">
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { ref } from 'vue'

export default {
  name: 'objectAdvance',
  props: {
    model: Object
  },
  emits: ['update'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const myModel = ref(props.model)
    const confirm = () => {
      context.emit('update', myModel)
    }
    return {
      myModel,
      t,
      confirm
    }
  }
}
</script>
