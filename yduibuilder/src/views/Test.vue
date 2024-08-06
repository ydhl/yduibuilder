<template>
  <div class="d-flex flex-column p-3">
    <div>
      <input type="text" v-model="single" value="this is value"/>
      <div @click="single='changed'">{{single}} click change</div>
    </div>
    <button @click="row=2">change row</button>
    <div v-for="(item, index) in arr" :key="index">{{index}}.{{item}}</div>
    <div v-for="item in row" :key="item">
      <input type="text" v-model="arr[item]" value="this is value"/>
      <div @click="arr[item]='changed'">{{arr[item]}} click change</div>
    </div>
  </div>
</template>

<script lang="ts">
import { ref } from 'vue'

export default {
  name: 'TestPage',
  setup (props: any, context: any) {
    const single = ref('single')
    const arr = ref([])
    const row = ref(3)

    return {
      single,
      row,
      arr
    }
  }
}
</script>
