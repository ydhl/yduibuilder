<template>
  <template v-if="show" >
    <teleport to="body">
      <div id="RichTextEditorDialog" class="modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-body" style="height: 500px">
              TODO
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-light me-3" @click="hideFileDialog()">{{t('common.cancel')}}</button>
              <button type="button" class="btn btn-primary" @click="hideFileDialog()">{{t('common.ok')}}</button>
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </template>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { watch } from 'vue'
import $ from 'jquery'
declare const bootstrap: any
export default {
  name: 'RichTextEditor',
  props: {
    show: Boolean
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    watch(() => props.show, (show) => {
      if (show) {
        const myModalEl = document.getElementById('RichTextEditorDialog') as HTMLElement
        const myModal = new bootstrap.Modal(myModalEl, { backdrop: 'static', keyboard: false })
        myModal.show()
      } else {
        // if (selectFile.value) context.emit('update:modelValue', selectFile.value)
        $('#RichTextEditorDialog').remove()
        // isFileSelectorOpen.value = false
      }
    })
    return {
      t
    }
  }
}
</script>
