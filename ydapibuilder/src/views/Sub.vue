<template>
  mydata {{JSON.stringify(myData)}}
  <button @click="myData=12">change</button>
</template>

<script lang="ts">
import { ref } from 'vue'

export default {
  name: 'Sub',
  props: {
    data: Object
  },
  setup (props: any, context: any) {
    const myData = ref(props.data)
    return {
      myData
    }
  }
}
</script>
