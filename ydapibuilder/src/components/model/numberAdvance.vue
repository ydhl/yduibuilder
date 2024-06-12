<template>
  <div class="row mb-3">
    <div class="col">
      <label>{{t('api.dataStruct.format')}}</label>
      <select class="form-select form-select-sm" v-model="myModel.format">
        <option value=""></option>
        <option value="float">float</option>
        <option value="double">double</option>
        <option value="float32">float32</option>
        <option value="float64">float64</option>
      </select>
    </div>
    <div class="col">
      <label>{{t("api.dataStruct.min")}}</label>
      <input type="text" class="form-control form-control-sm" v-model="myModel.min">
    </div>
    <div class="col">
      <label>{{t("api.dataStruct.max")}}</label>
      <input type="text" class="form-control form-select-sm" v-model="myModel.max">
    </div>
  </div>
  <div class="row">
    <EnumConstAdvance :model="myModel"></EnumConstAdvance>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { ref } from 'vue'
import EnumConstAdvance from '@/components/model/EnumConstAdvance.vue'

export default {
  name: 'numberAdvance',
  components: { EnumConstAdvance },
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
