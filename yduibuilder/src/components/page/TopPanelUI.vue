<template>
  <TopPanelBase activeMenu="uibuilder">
    <template #rightMenu>
      <li class="nav-item">
        <a class="nav-link" href="javascript:;" @click="setting">{{ t('project.setting') }}</a>
      </li>
      <li class="nav-item" v-if="userRole=='admin'">
        <a class="nav-link" href="javascript:;" @click="build">{{ t('common.build') }}</a>
      </li>
      <li class="nav-item dropdown">
        <div class="btn-group" v-if="canEdit">
          <a href="javascript:;" :class="{'nav-link ': true,'text-success':saveStatus===1,'text-reset':saveStatus===0}" @click="save">{{ saveWord }}</a>
          <a type="button" class="nav-link dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">下拉菜单</span>
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="javascript:;" @click="saveAsVersion">{{ t('common.saveAsVersion') }}</a></li>
          </ul>
        </div>
        <span v-if="!canEdit" class="nav-link disabled" >{{ t('common.readonly') }}</span>
      </li>
    </template>
  </TopPanelBase>
  <lay-layer v-model="isOpenBuildDialog" :title="t('common.build')" :shade="true" :area="['500px', '500px']">
    <div class="p-3" style="height: 360px">
      <div v-if="!socket">{{t('common.socketNotConnect')}}</div>
      <pre id="build-log" class="overflow-auto" style="height:100%;"></pre>
    </div>
    <div class="card-footer">
      <button type="button" class="btn btn-primary" @click="isOpenBuildDialog=false">{{t('common.ok')}}</button>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'
import { computed, nextTick, ref } from 'vue'
import { useStore } from 'vuex'
import { YDJSStatic } from '@/lib/ydjs'
import $ from 'jquery'
import TopPanelBase from '@/components/page/TopPanelBase.vue'
declare const YDJS: YDJSStatic

export default {
  name: 'TopPanelUI',
  components: { TopPanelBase },
  setup (props: any, ctx: any) {
    const { t } = useI18n()
    const store = useStore()
    const saving = computed(() => store.state.design.saving)
    const canEdit = computed(() => store.state.design.canEdit)
    const userRole = computed(() => store.state.design.userRole)
    const project = computed(() => store.state.design.project)
    const uiName = computed(() => store.state.design.project.ui + store.state.design.project.ui_version)
    const saveStatus = computed(() => {
      const saved = Array.from(new Set(Object.values(store.state.design.pageSaved)))
      if (saved.length === 1) {
        return saved[0]
      } else if (saved.length === 2) {
        if (saved.indexOf(0) !== -1) return 0
        if (saved.indexOf(-1) !== -1) return -1
        return 1
      } else {
        return -1
      }
    })
    const socket = computed(() => store.state.design.socket)
    const logo = computed(() => project.value.logo ? ydhl.uploadApi + project.value.logo : '/logo.svg')
    const api = ydhl.api + 'project/' + project.value.id
    const isOpenBuildDialog = ref(false)
    const saveWord = computed(() => {
      if (saving.value) {
        return t('common.saving')
      }
      if (saveStatus.value === 1) {
        return t('common.saved')
      } else if (saveStatus.value === -1) {
        return t('common.save')
      } else {
        return t('common.notsave')
      }
    })
    const save = function () {
      ydhl.save(store)
    }
    const saveAsVersion = function () {
      ydhl.saveAsVersion(store)
    }
    const buildSocketMsg = (event) => {
      if (event.data === 'done') {
        socket.value.removeEventListener('message', buildSocketMsg)
        return
      }
      const log = $('#build-log')
      log.append(`<div>${event.data}</div>`)
      log.get(0)?.scrollTo({ top: log.get(0)?.scrollHeight })
    }
    const build = () => {
      ydhl.alert(t('common.buildNow'), t('common.build')).then((dialogId) => {
        ydhl.closeLoading(dialogId)
        isOpenBuildDialog.value = true
        nextTick(() => {
          if (!socket.value) return
          const log = $('#build-log')
          socket.value.addEventListener('message', buildSocketMsg)
          socket.value.send(JSON.stringify({ uuid: project.value?.id, token: ydhl.getJwt(), action: 'build', lang: ydhl.getLanguage() }))
          log.append('<div>Connected</div>')
        })
      })
    }
    const setting = () => {
      const dialogid = YDJS.dialog(`${api}/tech`
        , ''
        , t('project.setting')
        , YDJS.SIZE_NORMAL
        , YDJS.BACKDROP_STATIC
        , []
        , undefined
        , (rst) => {
          YDJS.hide_dialog(dialogid)
          console.log(rst)
          if (!rst || !rst.success) {
            YDJS.toast(rst?.msg || t('common.operationFail'), YDJS.ICON_ERROR)
          } else {
            document.location.reload()
          }
        })
    }
    return {
      t,
      save,
      saveAsVersion,
      saveStatus,
      canEdit,
      api,
      logo,
      project,
      socket,
      uiName,
      isOpenBuildDialog,
      saveWord,
      userRole,
      setting,
      build
    }
  }
}
</script>
