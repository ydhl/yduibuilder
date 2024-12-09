<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('style.selector') }}</div>
  <div class="style-body d-none">
    <div class="dropdown w-100">
      <select ref="selectorDom" multiple>
        <option v-for="(item, index) of selectors" :key="index" :value="item.id" selected>{{item.text}}</option>
      </select>
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import initUI from '@/components/Common'
declare const $: any

export default {
  name: 'StyleSelector',
  props: {
    previewMode: Boolean,
    stateUuid: String // 传入则绑定ui指定状态的样式
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const info = initUI()
    const myStyleClass = ref('')
    const notBindFlag = ref(false)
    const selectorDom = ref()
    const selectors = ref<any>([])
    const loadBoundSelector = () => {
      return new Promise((resolve) => {
        ydhl.get('api/style/selector.json', {
          page_uuid: info.selectedPageId.value,
          uiid: info.selectedUIItemId.value,
          state_uuid: props.stateUuid
        }, function (rst) {
          if (!rst.success) {
            resolve(true)
            return
          }
          selectors.value = rst.data
          resolve(true)
        }, 'json')
      })
    }
    const formatTag = (item) => {
      return $('<span class="badge bg-success">' + item.text + '</span>')
    }
    // 用来鉴别是否需要加载selector： ui改变后或者ui没变，但state变了
    const needReloadSelector = computed(() => props.stateUuid + info.selectedUIItemId.value)
    watch(needReloadSelector, () => {
      if (!selectorDom.value) return
      const control: any = $(selectorDom.value)
      control.select2('close')
      selectors.value = []

      // 清空同时不要触发select2事件
      notBindFlag.value = true
      $(selectorDom.value).val(null).trigger('change')

      // 加载最新数据
      loadBoundSelector().then(() => {
        $(selectorDom.value).trigger('change')
        setTimeout(() => {
          notBindFlag.value = false
        }, 500)
      })
    })
    onMounted(() => {
      loadBoundSelector().then(() => {
        const control: any = $(selectorDom.value)
        control.select2({
          theme: 'bootstrap-5',
          placeholder: t('style.pleaseSelectSelector'),
          tags: true,
          allowClear: true,
          language: 'zh-CN',
          templateResult: function (item) {
            if (!item.id) return item.text
            return formatTag(item)
          },
          templateSelection: function (item) {
            if (!item.type) {
              // 默认值回显的情况
              const findItem = selectors.value.find((i) => i.id === item.id)
              if (findItem) return formatTag(findItem)
              return item.text
            }
            return formatTag(item)
          },
          ajax: {
            url: ydhl.api + 'api/style.json',
            headers: {
              token: ydhl.getJwt()
            },
            delay: 250,
            dataType: 'json',
            data: function (params) {
              params.page_uuid = info.selectedPageId.value
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
          if (notBindFlag.value) {
            return
          }
          // console.log(control.select2('data'))
          const selector: any = []
          for (const item of control.select2('data')) {
            selector.push(item.id)
          }
          ydhl.postJson('api/style/selector.json', {
            page_uuid: info.selectedPageId.value,
            uiid: info.selectedUIItemId.value,
            selector,
            state_uuid: props.stateUuid
          }).then((rst: any) => {
            if (!rst.success) {
              ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
              return
            }
            info.setMeta('selector', rst.data.meta || undefined)
          })
        })
      })
    })
    return {
      t,
      selectorDom,
      selectors,
      myStyleClass
    }
  }
}
</script>
