<template>
  <div :class="{'flex-grow-1': true,'text-truncate':['popup','redirect','emit'].indexOf(myAction.type)==-1}">
    <template v-if="myAction.type==='popup'">
      <PopupSetting :variables="variables" @beforeCreatePopupBind="beforeCreatePopupBind" :autosave="autosave"
                    :page-data-inline="popupPageDataInline" v-model="myAction"></PopupSetting>
    </template>
    <template v-else-if="myAction.type==='mutation'">
      <MutationSetting :autosave="autosave" v-model="myAction" :variables="variables"></MutationSetting>
    </template>
    <template v-else-if="myAction.type==='emit'">
      <EmitSetting :autosave="autosave" :page-data-inline="popupPageDataInline" v-model="myAction" :variables="variables"></EmitSetting>
    </template>
    <template v-else-if="myAction.type=='output'">
      <span class="text-muted">{{t('action.outputDataDesc')}}</span>
    </template>
    <template v-else-if="myAction.type==='webapi'">
      <WebAPISetting :autosave="autosave" :bind-type="bindType" :bind-uuid="bindUuid" v-model="myAction"></WebAPISetting>
    </template>
    <template v-else-if="myAction.type=='redirect'">
      <RedirectSetting :autosave="autosave" v-model="myAction" :variables="variables" ></RedirectSetting>
    </template>
    <template v-else-if="myAction.type=='closepopup'">
      {{t('action.closepopupDesc')}}
    </template>
  </div>
</template>

<script lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupSetting from '@/components/common/PopupSetting.vue'
import WebAPISetting from '@/components/common/WebAPISetting.vue'
import RedirectSetting from '@/components/common/RedirectSetting.vue'
import MutationSetting from '@/components/common/MutationSetting.vue'
import EmitSetting from '@/components/common/EmitSetting.vue'

export default {
  name: 'EventAction',
  components: { EmitSetting, MutationSetting, RedirectSetting, WebAPISetting, PopupSetting },
  props: {
    action: Object, // Action
    variables: Object, // 参数
    // 绑定action的来源类型
    bindType: String,
    bindUuid: String,
    autosave: {
      default: true,
      type: Boolean
    },
    // 是否自动提交接口保存
    popupPageDataInline: Boolean
  },
  emits: ['beforeCreatePopupBind'],
  setup (props: any, context: any) {
    // 对象引用传入下级组件，在autosave为false时，上级会自动更新（没有用update:modelValue）
    const myAction = ref(props.action)
    const { t } = useI18n()
    const beforeCreatePopupBind = (callback) => {
      context.emit('beforeCreatePopupBind', callback)
    }

    return {
      beforeCreatePopupBind,
      myAction,
      t
    }
  }
}
</script>
