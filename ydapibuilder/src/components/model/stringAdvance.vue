<template>
  <div class="row mb-3">
    <div class="col">
      <label>{{t("api.dataStruct.minLength")}}</label>
      <input type="text" class="form-control form-control-sm" v-model="myModel.min">
    </div>
    <div class="col">
      <label>{{t("api.dataStruct.maxLength")}}</label>
      <input type="text" class="form-control form-select-sm" v-model="myModel.max">
    </div>
  </div>
  <div class="row mb-3">
    <div class="col">
      <label>{{t("api.dataStruct.pattern")}}
        <lay-tooltip :content="t('api.dataStruct.patternTip')">
          <lay-icon type="layui-icon-question"></lay-icon>
        </lay-tooltip>
      </label>
      <div class="input-group">
        <div class="input-group-text">/</div>
        <input type="text" class="form-control form-control-sm" v-model="myModel.pattern">
        <div class="input-group-text">/</div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <EnumConstAdvance :model="myModel"/>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { ref } from 'vue'
import EnumConstAdvance from '@/components/model/EnumConstAdvance.vue'

export default {
  name: 'stringAdvance',
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
