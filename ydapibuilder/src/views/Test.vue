<template>
  <Sub :data="test"></Sub>
  <hr/>
  {{JSON.stringify(test)}}
</template>

<script lang="ts">
import { ref } from 'vue'
import Sub from '@/views/Sub.vue'

export default {
  name: 'Test',
  components: { Sub },
  setup (props: any, context: any) {
    const test = ref(2)
    return {
      test
    }
  }
}
</script>
