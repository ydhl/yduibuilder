<template>
  <div class="d-flex flex-column">
    <button class="btn btn-light btn-sm" @click="openIconDialog">
      <span class="text-primary">{{icon}}</span>
      <span v-if="icon" class="iconfont icon-remove text-danger ms-3" @click.stop.prevent="clearIcon"></span>
      <template v-if="!icon">{{t('common.chooseIcon')}}</template>
    </button>
    <div class="btn-group btn-group-sm mt-1" v-if="!hidePosition">
      <button type="button" :class="{'btn btn-outline-primary': true, 'active': iconPosition==='left'}" @click="iconPosition='left'">{{t('common.left')}}</button>
      <button type="button" :class="{'btn btn-outline-primary': true, 'active': iconPosition==='right'}" @click="iconPosition='right'">{{t('common.right')}}</button>
      <button type="button" :class="{'btn btn-outline-primary': true, 'active': iconPosition==='top'}" @click="iconPosition='top'">{{t('common.top')}}</button>
      <button type="button" :class="{'btn btn-outline-primary': true, 'active': iconPosition==='bottom'}" @click="iconPosition='bottom'">{{t('common.bottom')}}</button>
    </div>
  </div>
</template>

<script lang="ts">
import ydhl from '../../../lib/ydhl'
import $ from 'jquery'
import { computed } from 'vue'
import { useStore } from 'vuex'
import initUI from '../../Common'
import { useI18n } from 'vue-i18n'
// eslint-disable-next-line camelcase
declare const yze_ajax: any
declare const bootstrap: any

export default {
  name: 'IconSetting',
  props: {
    hidePosition: Boolean
  },
  setup (props: any, context: any) {
    const store = useStore()
    const info = initUI()
    const { t } = useI18n()
    const project = computed(() => store.state.design.project)
    const iconSelected = (event) => {
      if (!event.data) return
      if (event.data.type === 'icon') {
        icon.value = event.data.icon
      }
    }
    const clearIcon = () => {
      icon.value = ''
    }
    const icon = computed({
      get () {
        return info.getMeta('icon', 'custom')
      },
      set (v) {
        info.setMeta('icon', v, 'custom')
      }
    })
    const iconPosition = computed({
      get () {
        return info.getMeta('icon-position', 'custom') || 'left'
      },
      set (v) {
        info.setMeta('icon-position', v, 'custom')
      }
    })
    const openIconDialog = () => {
      // eslint-disable-next-line new-cap
      const ajax = new yze_ajax()
      ajax.getIframe(ydhl.api + `api/icon?pid=${project.value.id}&icon=${icon.value}`, (html) => {
        $(document.body).append(`<div id="openIconDialog" class="modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-body" style="height: 500px">
        ${html}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">${t('common.ok')}</button>
      </div>
    </div>
  </div>
</div>`)
        window.addEventListener('message', iconSelected)
        const myModalEl = document.getElementById('openIconDialog') as HTMLElement
        const myModal = new bootstrap.Modal(myModalEl)
        myModalEl.addEventListener('hide.bs.modal', function (event) {
          window.removeEventListener('message', iconSelected)
          $('#openIconDialog').remove()
        })
        myModal.show()
      })
    }
    return {
      icon,
      iconPosition,
      openIconDialog,
      clearIcon,
      t
    }
  }
}
</script>
