<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> Typography</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">Font</label>
      <div class="col-sm-9 d-flex">
        <select class="form-select-sm form-select" multiple size="5">
          <option></option>
        </select>
        <div class="ms-1">
          <input type="text" class="form-control form-control-sm">
          <div class="d-flex mt-1">
            <select class="form-select-sm form-select" multiple size="5">
              <option>11</option>
              <option>12</option>
              <option>13</option>
              <option>14</option>
            </select>
            <div class="ms-1 bg-primary">&nbsp;</div>
          </div>
        </div>
      </div>
    </div>
    <div class="row mt-1">
      <div class="col-sm-9 d-flex offset-3">
        <div class="btn-group btn-group-sm" role="group">
          <button type="button" class="btn btn-outline-primary"><i class="iconfont icon-bold"></i></button>
          <button type="button" class="btn btn-outline-primary"><i class="iconfont icon-italic"></i></button>
          <button type="button" class="btn btn-outline-primary"><i class="iconfont icon-underline"></i></button>
          <button type="button" class="btn btn-outline-primary"><i class="iconfont icon-through"></i></button>
        </div>
        <div class="ms-1">
          <ColorPicker></ColorPicker>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ColorPicker from '@/components/common/ColorPicker'
export default {
  name: 'StyleFont',
  components: { ColorPicker }
}
</script>
