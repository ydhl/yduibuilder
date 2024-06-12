<template>
  <i :class="{'iconfont': true, 'icon-remove': !confirm , 'icon-remove-confirm text-danger animate__bounceIn': confirm}" @click.stop="remove"></i>
</template>

<script lang="ts">
import { ref } from 'vue'

export default {
  name: 'ConfirmRemove',
  emits: ['remove'],
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
