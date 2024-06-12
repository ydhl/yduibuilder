<template>
  <input type="text" v-model="test">
</template>

<script lang="ts">
import { ref } from 'vue'

export default {
  name: 'Operation',
  setup (props: any, context: any) {
    const test = ref('')
    return {
      test
    }
  }
}
</script>
