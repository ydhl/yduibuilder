<template>
  <div class="col">
    <div class="d-flex align-items-center">
      <div class="form-check form-switch me-5" @click="changeType('enum')">
        <input class="form-check-input" type="checkbox" role="switch" id="enum" :checked="type == 'enum'">
        <label class="form-check-label pt-1" for="enum"><label>{{t("api.dataStruct.enum")}}</label></label>
      </div>
    </div>
    <table class="table table-sm table-borderless table-hover table-striped" v-if="type == 'enum'">
      <thead><tr><th>{{t("api.dataStruct.enumValue")}}</th><th>{{t("api.dataStruct.comment")}}</th></tr></thead>
      <tbody>
      <tr v-for="(enumItem, index) in enumValues" :key="index">
        <td><input type="text" @change="changeEnum()" class="form-control form-control-sm" v-model.trim="enumItem.name"></td>
        <td><input type="text" @change="changeEnum()" class="form-control form-control-sm" v-model.trim="enumItem.comment"></td>
      </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref } from 'vue'

export default {
  name: 'EnumConstAdvance',
  props: {
    model: Object
  },
  emits: ['update'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const myModel = ref(props.model)
    const type = ref('')
    onMounted(() => {
      if (props.model.enumValue !== undefined) {
        type.value = 'enum'
      }
    })
    const changeEnum = () => {
      const length = enumValues.value.length
      if (!length) return
      if (enumValues.value[length - 1].name !== '') {
        enumValues.value.push({ name: '', comment: '' })
      }
      const values = {}
      for (const value of enumValues.value) {
        if (!value.name) continue
        values[value.name] = value.comment
      }
      myModel.value.enumValue = values
    }
    const enumValues = computed(() => {
      const values: any = []
      for (const name in props.model.enumValue) {
        values.push({ name, comment: props.model.enumValue[name] })
      }
      if (values) {
        values.push({ name: '', comment: '' })
      }
      return values
    })
    const changeType = (t) => {
      type.value = t
      if (t === 'enum') {
        myModel.value.const = undefined
        myModel.value.enumValue = {}
      }
    }
    return {
      myModel,
      type,
      t,
      enumValues,
      confirm,
      changeEnum,
      changeType
    }
  }
}
</script>
