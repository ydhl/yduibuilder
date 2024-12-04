<template>
    <component v-if="!isImage" :is="component"></component>
    <img v-else :src="url" style="width: 100%;">
</template>
<script lang="ts" setup>
import { computed, defineAsyncComponent } from 'vue';
const { url } = defineProps({
url: {
  type: String,
  required: true
},
attrs: Object
})
const isImage = computed(()=>url.match(/\.(jpg|jpeg|png|gif|bmp|webp|svg)$/))
const component = defineAsyncComponent(() => import(url))
</script>
  