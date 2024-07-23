<template>
  <div :class="{'flex-grow-1': true,'text-truncate':['popup','redirect','emit'].indexOf(myAction.type)==-1}">
    <template v-if="myAction.type==='popup'">
      <PopupSetting :readonly="readonly" :variables="variables" @beforeSave="beforeSave" :autosave="autosave"
                    :page-data-inline="popupPageDataInline" v-model="myAction"></PopupSetting>
    </template>
    <template v-else-if="myAction.type==='mutation'">
      <MutationSetting :readonly="readonly" :autosave="autosave" v-model="myAction" :variables="variables"></MutationSetting>
    </template>
    <template v-else-if="myAction.type==='emit'">
      <EmitSetting :readonly="readonly" :autosave="autosave" :page-data-inline="popupPageDataInline" v-model="myAction" :variables="variables"></EmitSetting>
    </template>
    <template v-else-if="myAction.type=='output'">
      <span class="text-muted">{{t('action.outputDataDesc')}}</span>
    </template>
    <template v-else-if="myAction.type==='webapi'">
      <WebAPISetting :readonly="readonly" :autosave="autosave" :bind-type="bindType" :bind-uuid="bindUuid" v-model="myAction"></WebAPISetting>
    </template>
    <template v-else-if="myAction.type=='redirect'">
      <RedirectSetting :readonly="readonly" :autosave="autosave" v-model="myAction" :variables="variables" ></RedirectSetting>
    </template>
    <template v-else-if="myAction.type=='closepopup'">
      {{t('action.closepopupDesc')}}
    </template>
    <template v-else-if="myAction.type=='interval'">
      <IntervalSetting :readonly="readonly" :variables="variables" :autosave="autosave" @beforeSave="beforeSave" v-model="myAction"></IntervalSetting>
    </template>
  </div>
</template>

<script lang="ts">
import { toRef } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupSetting from '@/components/common/PopupSetting.vue'
import WebAPISetting from '@/components/common/WebAPISetting.vue'
import RedirectSetting from '@/components/common/RedirectSetting.vue'
import MutationSetting from '@/components/common/MutationSetting.vue'
import EmitSetting from '@/components/common/EmitSetting.vue'
import IntervalSetting from '@/components/common/IntervalSetting.vue'

export default {
  name: 'EventAction',
  components: { IntervalSetting, EmitSetting, MutationSetting, RedirectSetting, WebAPISetting, PopupSetting },
  props: {
    action: Object, // Action
    variables: Object, // 参数
    // 绑定action的来源类型
    bindType: String,
    bindUuid: String,
    readonly: Boolean,
    autosave: {
      default: true,
      type: Boolean
    },
    // 是否自动提交接口保存
    popupPageDataInline: Boolean
  },
  emits: ['beforeSave'],
  setup (props: any, context: any) {
    // 对象引用传入下级组件，在autosave为false时，上级会自动更新（没有用update:modelValue）
    const myAction = toRef(props, 'action')
    const { t } = useI18n()
    const beforeSave = (callback) => {
      context.emit('beforeSave', callback)
    }

    return {
      beforeSave,
      myAction,
      t
    }
  }
}
</script>
