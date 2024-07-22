<template>
  <template v-if="hideToggle">
    <div class="dropdown-menu" style="position: static;display: block">
      <template v-for="(option, index) in options" :key="index">
        <template v-if="option.input">
          <div class="dropdown-item" @mouseenter="showDesc(option)" >
              <slot name="input"></slot>
          </div>
        </template>
        <template v-else-if="option.name">
          <div v-if="!option.disabled"><a class="dropdown-item" href="javascript:void(0)"
                                         @mouseenter="showDesc(option)" @click.stop.prevent="click(option)">{{option.name}}</a></div>
          <div v-else-if="option.disabled"><span class="dropdown-item-text text-muted">{{option.name}}</span></div>
        </template>
        <div v-else-if="option.header"><h6 class="dropdown-header">{{option.header}}</h6></div>
        <div v-else><hr class="dropdown-divider"></div>
      </template>
      <template v-if="desc">
        <div><hr class="dropdown-divider"></div>
        <div><div class="ps-3 pe-3 text-muted fs-7">{{ desc }}</div></div>
      </template>
    </div>
  </template>
  <template v-else>
    <div class="dropdown">
      <button ref="dropdownToggle" :class="['btn btn-light dropdown-toggle', btnSize]" @click.stop.prevent type="button" data-bs-toggle="dropdown" aria-expanded="false">
        {{defaultText}}
      </button>
      <div class="dropdown-menu" ref="dropdownMenu" :style="flexMode ? 'width:250px' : ''">
        <div v-if="flexMode" class="d-flex flex-wrap gap-1">
          <template v-for="(option, index) in options" :key="index">
            <template v-if="option.input">
              <div class="dropdown-item" @mouseenter="showDesc(option)" >
                <slot name="input"></slot>
              </div>
            </template>
            <template v-if="option.disabled">
              <div class="w-100"><span class="dropdown-item-text text-muted">{{option.name}}</span></div>
            </template>
            <template v-else-if="option.name">
              <div v-if="!option.disabled"><a class="dropdown-item" href="javascript:void(0)"
                  @mouseenter="showDesc(option)" @click.stop.prevent="click(option)">{{option.name}}</a></div>
            </template>
            <div v-else class="w-100"><hr class="dropdown-divider"></div>
          </template>
        </div>
        <template v-else>
          <template v-for="(option, index) in options" :key="index">
            <template v-if="option.input">
              <div>
                <div class="dropdown-item" @mouseenter="showDesc(option)">
                  <slot name="input"></slot>
                </div>
              </div>
            </template>
            <template v-else-if="option.name">
              <div v-if="!option.disabled"><a class="dropdown-item" href="javascript:void(0)"
                                                            @mouseenter="showDesc(option)" @click.stop.prevent="click(option)">{{option.name}}</a></div>
              <div v-else-if="option.disabled"><span class="dropdown-item-text text-muted">{{option.name}}</span></div>
            </template>
            <div v-else><hr class="dropdown-divider"></div>
          </template>
        </template>
        <template v-if="desc">
          <div><hr class="dropdown-divider"></div>
          <div><div class="ps-3 pe-3 text-muted fs-7">{{ desc }}</div></div>
        </template>
      </div>
    </div>
  </template>
</template>

<script lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

export default {
  name: 'AdvanceSelect',
  props: {
    /**
     * 格式[{name,value,desc, disabled, input}]
     */
    options: Array,
    defaultText: String,
    flexMode: Boolean,
    hideToggle: Boolean,
    btnSize: {
      default: 'btn-xs ',
      type: String
    }
  },
  emits: ['change'],
  setup (props: any, context: any) {
    const desc = ref('')
    const { t } = useI18n()
    const dropdownMenu = ref()
    const dropdownToggle = ref()
    const click = (option) => {
      if (!props.hideToggle) {
        dropdownMenu.value.classList.remove('show')
        dropdownToggle.value?.classList.remove('show')
      }
      context.emit('change', option)
    }
    const showDesc = (option) => {
      desc.value = option.desc
    }
    return {
      desc,
      t,
      dropdownMenu,
      dropdownToggle,
      click,
      showDesc
    }
  }
}
</script>
