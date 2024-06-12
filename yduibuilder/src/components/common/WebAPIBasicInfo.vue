<template>
  <span :class="'api-method-'+webApi.method+' text-uppercase api-method'">{{ webApi.method }}</span>
  <span class="text-muted" style="font-size: 0.75rem">({{ webApi.major }}.{{ webApi.minor }}.{{ webApi.revision }})</span>
  {{ webApi.name }} {{webApi.path}}
</template>

<script lang="ts">

export default {
  name: 'WebAPIBasicInfo',
  props: {
    webApi: Object
  }
}
</script>
