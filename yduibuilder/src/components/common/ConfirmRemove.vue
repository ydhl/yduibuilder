<template>
  <span @click.stop="remove">
    <i :class="[{'iconfont pointer hover-danger': true, 'icon-delete-confirm text-danger animate__bounceIn': confirm}, !confirm ? icon : '']"></i>
    {{title||''}}
  </span>
</template>

<script lang="ts">
import { ref } from 'vue'

export default {
  name: 'ConfirmRemove',
  emits: ['remove'],
  props: {
    icon: {
      default: 'icon-delete',
      type: String
    },
    title: String
  },
  setup (props: any, context: any) {
    const confirm = ref(false)

    const remove = () => {
      if (confirm.value) {
        context.emit('remove')
      } else {
        setTimeout(() => {
          confirm.value = false
        }, 2000)
      }
      confirm.value = !confirm.value
    }
    return {
      confirm,
      remove
    }
  }
}
</script>
