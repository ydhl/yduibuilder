<template>
  <div class="row mb-3">
    <div class="col">
      <label>{{t('api.dataStruct.format')}}</label>
      <select class="form-select form-select-sm" v-model="myModel.format">
        <option value=""></option>
        <option value="int32">int32</option>
        <option value="int64">int64</option>
        <option value="long">long</option>
        <option value="uint32">uint32</option>
        <option value="uint64">uint64</option>
        <option value="uint">uint</option>
        <option value="ulong">ulong</option>
        <option value="sint32">sint32</option>
        <option value="sint64">sint64</option>
        <option value="fixed32">fixed32</option>
        <option value="fixed64">fixed64</option>
        <option value="sfixed32">sfixed32</option>
        <option value="sfixed64">sfixed64</option>
        <option value="bignum">bignum</option>
        <option value="fixnum">fixnum</option>
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

  <div class="row mb-3">
    <EnumConstAdvance :model="model"></EnumConstAdvance>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { ref } from 'vue'
import EnumConstAdvance from '@/components/model/EnumConstAdvance.vue'

export default {
  name: 'integerAdvance',
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
