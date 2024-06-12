<template>
  <div class="row">
    <div class="col">
      <label>{{t("api.dataStruct.minCount")}}</label>
      <input type="text" class="form-control form-control-sm" v-model="myModel.min">
    </div>
    <div class="col">
      <label>{{t("api.dataStruct.maxCount")}}</label>
      <input type="text" class="form-control form-select-sm" v-model="myModel.max">
    </div>
    <div class="col">
      <label for="unique">{{t("api.dataStruct.unique")}}</label>
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" role="switch" id="unique" v-model="myModel.unique">
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { ref } from 'vue'

export default {
  name: 'arrayAdvance',
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
