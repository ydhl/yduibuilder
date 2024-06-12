<template>
  <template v-if="hideToggle">
    <ul class="dropdown-menu" style="position: static;display: block">
      <template v-for="(option, index) in options" :key="index">
        <template v-if="option.name">
          <li v-if="!option.disabled"><a class="dropdown-item" href="javascript:void(0)"
                                         @mouseenter="showDesc(option)" @click.stop.prevent="click(option)">{{option.name}}</a></li>
          <li v-else-if="option.disabled"><span class="dropdown-item-text text-muted">{{option.name}}</span></li>
        </template>
        <li v-else-if="option.header"><h6 class="dropdown-header">{{option.header}}</h6></li>
        <li v-else><hr class="dropdown-divider"></li>
      </template>
      <template v-if="desc">
        <li><hr class="dropdown-divider"></li>
        <li><div class="ps-3 pe-3 text-muted fs-7">{{ desc }}</div></li>
      </template>
    </ul>
  </template>
  <template v-else>
    <div class="dropdown">
      <button ref="dropdownToggle" :class="['btn btn-light dropdown-toggle', btnSize]" @click.stop.prevent type="button" data-bs-toggle="dropdown" aria-expanded="false">
        {{defaultText}}
      </button>
      <ul class="dropdown-menu" ref="dropdownMenu">
        <template v-for="(option, index) in options" :key="index">
          <template v-if="option.name">
            <li v-if="!option.disabled"><a class="dropdown-item" href="javascript:void(0)"
                                                          @mouseenter="showDesc(option)" @click.stop.prevent="click(option)">{{option.name}}</a></li>
            <li v-else-if="option.disabled"><span class="dropdown-item-text text-muted">{{option.name}}</span></li>
          </template>
          <li v-else><hr class="dropdown-divider"></li>
        </template>
        <template v-if="desc">
          <li><hr class="dropdown-divider"></li>
          <li><div class="ps-3 pe-3 text-muted fs-7">{{ desc }}</div></li>
        </template>
      </ul>
    </div>
  </template>
</template>

<script lang="ts">
import { ref } from 'vue'

export default {
  name: 'AdvanceSelect',
  props: {
    /**
     * 格式[{name,value,desc, disabled}]
     */
    options: Array,
    defaultText: String,
    hideToggle: Boolean,
    btnSize: {
      default: 'btn-xs ',
      type: String
    }
  },
  emits: ['click'],
  setup (props: any, context: any) {
    const desc = ref('')
    const dropdownMenu = ref()
    const dropdownToggle = ref()
    const click = (option) => {
      if (!props.hideToggle) {
        dropdownMenu.value.classList.remove('show')
        dropdownToggle.value?.classList.remove('show')
      }
      context.emit('click', option)
    }
    const showDesc = (option) => {
      desc.value = option.desc
    }
    return {
      desc,
      dropdownMenu,
      dropdownToggle,
      click,
      showDesc
    }
  }
}
</script>
