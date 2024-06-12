<template>
    <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
        :data-pageid="pageid" :class="['layui-box layui-laypage', dragableCss, uiCss]">
      <a href="javascript:;" class="layui-laypage-prev layui-disabled" data-page="0">上一页</a>
      <a href="javascript:;">1</a>
      <span class="layui-laypage-curr"><em class="layui-laypage-em"></em><em>2</em></span>
      <a href="javascript:;">3</a>
      <a href="javascript:;">4</a>
      <a href="javascript:;">5</a>
      <a href="javascript:;">6</a>
      <a href="javascript:;" class="layui-laypage-next layui-disabled" data-page="0">最后一页</a>
    </div>
</template>

<script lang="ts">
import Pagination from '@/components/ui/js/Pagination'
import { useStore } from 'vuex'

export default {
  name: 'Layui_Pagination',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const pagination = new Pagination(props, context, useStore())
    return {
      ...pagination.setup()
    }
  }
}
</script>
