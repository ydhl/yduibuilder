<template>
  <div class="top-panel shadow-sm">
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
      <a class="navbar-brand p-0" :href="api"><img :src="logo" class=" rounded-circle" style="height: 40px;width: 40px;object-fit: cover;"></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon" />
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <span class="navbar-text me-3 pt-0 text-uppercase">{{ project.name }} <div style="font-size: 10px;line-height: 10px;">{{ project.endKind }}/{{ project.frontend }}/{{ project.framework }}</div></span>
        <ul class="navbar-nav me-auto">
        </ul>
        <div class="form-inline my-2 my-lg-0">
          <ul class="navbar-nav me-auto">
            <slot name="rightMenu"></slot>
            <li class="nav-item">
              <a class="nav-link" href="#" >
                <div class='rounded-circle'
                     :style="`width: 28px;height: 28px; background-size:cover; background-position:center;background-image: url(${userAvatar||'/programer.jpg'})`" ></div>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import ydhl from '@/lib/ydhl'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'TopPanelBase',
  props: {
    activeMenu: String
  },
  setup (props: any, ctx: any) {
    const { t } = useI18n()
    const store = useStore()
    const userAvatar = computed(() => store.state.user.avatar)
    const project = computed(() => store.state.design.project)
    const logo = computed(() => project.value.logo ? ydhl.uploadApi + project.value.logo : '/logo.svg')
    const api = ydhl.api + 'project/' + project.value.id

    return {
      t,
      api,
      logo,
      project,
      userAvatar,
      ssoapi: ydhl.api + 'api/sso/token'
    }
  }
}
</script>
