<template>
  <button type="button" :style="myStyle" class="btn btn-outline-light text-muted" @click="openDialog()">{{t('common.setting')}}</button>
  <template v-if="isOpenDialog" >
    <teleport to="body">
      <div id="gradientDialog" class="modal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body" style="height: 500px">
              <div class="btn-group" role="group">
                <button type="button" @click="gradientType='linear'" :class="{'btn btn-xs': true, 'btn-primary': gradientType=='linear', 'btn-light': gradientType!='linear'}">{{t('style.background.gradientLinear')}}</button>
                <button type="button" @click="gradientType='radial'" :class="{'btn btn-xs': true, 'btn-primary': gradientType=='radial', 'btn-light': gradientType!='radial'}">{{t('style.background.gradientRadial')}}</button>
              </div>
              <hr>
              <div class="d-flex align-items-stretch">
                <template v-if="gradientType=='radial'">
                  <div>
                    <div class="btn-group" role="group">
                      <button type="button" @click="sizeShapeType='preset'" :class="{'btn btn-xs': true, 'btn-primary': sizeShapeType=='preset', 'btn-light': sizeShapeType!='preset'}">{{t('common.preset')}}</button>
                      <button type="button" @click="sizeShapeType='custom'" :class="{'btn btn-xs': true, 'btn-primary': sizeShapeType=='custom', 'btn-light': sizeShapeType!='custom'}">{{t('common.orCustom')}}</button>
                    </div>
                    <template v-if="sizeShapeType=='preset'">
                      <div class="btn-group d-block" role="group">
                        <button type="button" @click="gradientShape='ellipse'" :class="{'btn btn-xs': true, 'btn-primary': gradientShape=='ellipse', 'btn-light': gradientShape!='ellipse'}">{{t('style.background.gradientEllipse')}}</button>
                        <button type="button" @click="gradientShape='circle'" :class="{'btn btn-xs': true, 'btn-primary': gradientShape=='circle', 'btn-light': gradientShape!='circle'}">{{t('style.background.gradientCircle')}}</button>
                      </div>
                      <div class="btn-group d-block" role="group">
                        <button type="button" @click="gradientSize='closest-side'" :class="{'btn btn-xs': true, 'btn-primary': gradientSize=='closest-side', 'btn-light': gradientSize!='closest-side'}">{{t('style.background.gradientClosestSide')}}</button>
                        <button type="button" @click="gradientSize='farthest-side'" :class="{'btn btn-xs': true, 'btn-primary': gradientSize=='farthest-side', 'btn-light': gradientSize!='farthest-side'}">{{t('style.background.gradientFarthestSide')}}</button>
                        <button type="button" @click="gradientSize='closest-corner'" :class="{'btn btn-xs': true, 'btn-primary': gradientSize=='closest-corner', 'btn-light': gradientSize!='closest-corner'}">{{t('style.background.gradientClosestCorner')}}</button>
                        <button type="button" @click="gradientSize='farthest-corner'" :class="{'btn btn-xs': true, 'btn-primary': gradientSize=='farthest-corner', 'btn-light': gradientSize!='farthest-corner'}">{{t('style.background.gradientFarthestCorner')}}</button>
                      </div>
                    </template>
                    <template v-if="sizeShapeType=='custom'">
                      <div class="input-group input-group-sm">
                        <span class="input-group-text">{{ t('style.background.radialWidth') }}</span>
                        <input type="text" style="width: 60px !important;" placeholder="px,%" v-model="radialWidth" class="form-control form-control-sm">
                        <span class="input-group-text">{{ t('style.background.radialHeight') }}</span>
                        <input type="text" style="width: 60px !important;" placeholder="px,%" v-model="radialHeight" class="form-control">
                      </div>
                    </template>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">{{ t('style.background.positionX') }}</span>
                      <input type="text" style="width: 60px !important;" placeholder="px,%" v-model="positionX" class="form-control form-control-sm">
                      <span class="input-group-text">{{ t('style.background.positionY') }}</span>
                      <input type="text" style="width: 60px !important;" placeholder="px,%" v-model="positionY" class="form-control">
                    </div>
                  </div>
                </template>
                <div class="gradient-direction" ref="circle" id="circle" v-if="gradientType=='linear'">
                  <div class="direction-point" :style="dragStyle" ref="dragPoint" id="dragPoint"
                       draggable="true" @drag="drag($event)" @dragstart="dragStart($event)" @dragend="dragEnd($event)"></div>
                </div>
                <div class="flex-grow-1 flex-column justify-content-center d-flex ms-3">
                  <div class="flex-grow-1 d-flex align-items-center justify-content-center" :style="previewStyle">
                    预览
                  </div>
                  <label><input type="checkbox" v-model="repeat">{{ t('style.background.repeat') }}</label>
                </div>
              </div>
              <div class="flex-grow-1 d-flex flex-wrap mt-5">
                <div v-for="(color,index) in colors" :key="index" class="d-flex align-items-center justify-content-center">
                  <div class="pe-1">
                    <ColorPicker @clearColor="removeColor(index)" v-model="colors[index]"></ColorPicker>
                    <div class="input-group input-group-sm pe-3">
                      <span class="input-group-text">{{ t('style.background.size') }}</span>
                      <input type="text"  style="width: 80px !important;" placeholder="px,%" v-model="colorSize[index]" class="form-control form-control-sm">
                    </div>
                  </div>
                </div>
                <button class="btn btn-light" type="button" @click="addColor()">{{t('style.background.addColor')}}</button>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="save">{{t('common.ok')}}</button>
            </div>
          </div>
        </div>
      </div>
    </teleport>
  </template>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, nextTick, onMounted, ref, toRaw } from 'vue'
import $ from 'jquery'
import ColorPicker from '@/components/common/ColorPicker.vue'
import ydhl from '@/lib/ydhl'
declare const bootstrap: any

export default {
  name: 'Gradient',
  emits: ['update:modelValue'],
  components: { ColorPicker },
  props: {
    customStyle: String,
    /**
     * BackgroundGradient
     */
    modelValue: Object
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const gradientInfo = ref<any>({})
    const isOpenDialog = ref(false)
    const sizeShapeType = ref('preset')
    const colors = computed<Array<any>>({
      get () {
        return gradientInfo.value?.stops || []
      },
      set (v) {
        gradientInfo.value.stops = v
      }
    })
    const colorSize = computed<Array<any>>({
      get () {
        return gradientInfo.value?.colorSize || []
      },
      set (v) {
        gradientInfo.value.colorSize = v
      }
    })
    const direction = computed<number>({
      get () {
        return gradientInfo.value?.direction || 0
      },
      set (v) {
        gradientInfo.value.direction = v
      }
    })
    const repeat = computed<boolean>({
      get () {
        return gradientInfo.value?.repeat || false
      },
      set (v) {
        gradientInfo.value.repeat = v
      }
    })
    const gradientType = computed<string>({
      get () {
        return gradientInfo.value?.type || 'linear'
      },
      set (v) {
        gradientInfo.value.type = v
      }
    })
    const radialWidth = computed<string>({
      get () {
        return gradientInfo.value?.sizeCustom?.[0] || ''
      },
      set (v) {
        let size = gradientInfo.value?.sizeCustom
        if (!size) {
          size = ['', '']
        }
        size[0] = v
        if (size[0] === '' && size[1] === '') size = []
        gradientInfo.value.sizeCustom = size
        if (size.length > 0) {
          gradientInfo.value.shape = ''
          gradientInfo.value.size = ''
        }
      }
    })
    const radialHeight = computed<string>({
      get () {
        return gradientInfo.value?.sizeCustom?.[1] || ''
      },
      set (v) {
        let size = gradientInfo.value?.sizeCustom
        if (!size) {
          size = ['', '']
        }
        size[1] = v
        if (size[0] === '' && size[1] === '') size = []
        gradientInfo.value.sizeCustom = size
        if (size.length > 0) {
          gradientInfo.value.shape = ''
          gradientInfo.value.size = ''
        }
      }
    })
    const positionX = computed<string>({
      get () {
        return gradientInfo.value?.position?.[0] || '50%'
      },
      set (v) {
        let position = gradientInfo.value?.position
        if (!position) {
          position = ['', '50%']
        }
        position[0] = v
        gradientInfo.value.position = position
      }
    })
    const positionY = computed<string>({
      get () {
        return gradientInfo.value?.position?.[1] || '50%'
      },
      set (v) {
        let position = gradientInfo.value?.position
        if (!position) {
          position = ['50%', '']
        }
        position[1] = v
        gradientInfo.value.position = position
      }
    })
    const gradientShape = computed<string>({
      get () {
        if (gradientInfo.value?.sizeCustom?.length === 2) return ''
        return gradientInfo.value?.shape || 'ellipse'
      },
      set (v) {
        gradientInfo.value.shape = v
        gradientInfo.value.sizeCustom = []
      }
    })
    const gradientSize = computed<string>({
      get () {
        if (gradientInfo.value?.sizeCustom?.length === 2) return ''
        return gradientInfo.value?.size || 'farthest-corner'
      },
      set (v) {
        gradientInfo.value.size = v
        gradientInfo.value.sizeCustom = []
      }
    })
    const dragPoint = ref()
    const circle = ref()
    const pointX = ref(-1)
    const pointY = ref(-1)
    const myStyle = computed(() => {
      const _ = ['background-image: ' + ydhl.getGradientStyle(gradientInfo.value), props.customStyle]
      return _.join(';')
    })
    onMounted(() => {
      gradientInfo.value = toRaw(props.modelValue || { type: 'linear', stops: [], direction: 0, colorSize: [], shape: 'ellipse', position: ['50%', '50%'] })
      sizeShapeType.value = gradientInfo.value?.sizeCustom?.length === 2 ? 'custom' : 'preset'
    })
    const dragStyle = computed(() => {
      if (!circle.value || pointX.value === -1) {
        // 初始值
        return getDefaultXY(gradientInfo.value?.direction)
      }
      const rect = circle.value.getBoundingClientRect()
      const x = rect.x + 60 // 大圆半径
      const y = rect.y + 10 // 小圆半径
      const translateX = pointX.value - x
      const translateY = pointY.value - y
      // console.log(rect)
      // console.log(`transform: translate(${translateX}px, ${translateY}px)`)
      return `transform: translate(${translateX}px, ${translateY}px)`
    })
    const openDialog = () => {
      isOpenDialog.value = true
      nextTick(() => {
        const myModalEl = document.getElementById('gradientDialog') as HTMLElement
        const myModal = new bootstrap.Modal(myModalEl)
        myModalEl.addEventListener('hide.bs.modal', function (event) {
          isOpenDialog.value = false
          $('#gradientDialog').remove()
        })
        myModal.show()
      })
    }

    const previewStyle = computed(() => {
      if (gradientInfo.value?.stops && gradientInfo.value.stops.length > 0) {
        return 'background-image: ' + ydhl.getGradientStyle(gradientInfo.value)
      }
      const transparent = require('@/assets/image/transparent.svg')
      // console.log(localColor.value)
      return `background-image:url(${transparent});background-size: contain;`
    })
    const getDragDeg = () => {
      if (pointX.value === -1) {
        direction.value = 0
        return
      }
      const rect = circle.value.getBoundingClientRect()
      const a = parseInt(rect.x + rect.width / 2)
      const b = parseInt(rect.y + rect.height / 2)
      const R = 60
      const r = 10
      const x = ~~pointX.value
      const y = ~~pointY.value
      if (x === a) {
        if (y <= b) {
          direction.value = 0
          return
        }
        direction.value = 180
        return
      } else if (y === b) {
        if (x <= a) {
          direction.value = 270
          return
        }
        direction.value = 90
        return
      }
      let deg = 0
      if (x < a && y < b) { // 左上
        deg = 360 - Math.asin((a - x) / (R - r)) * 180 / Math.PI
      } else if (x > a && y < b) { // 右上
        deg = Math.asin((x - a) / (R - r)) * 180 / Math.PI
      } else if (x < a && y > b) { // 左下
        deg = 360 - Math.asin((y - b) / (R - r)) * 180 / Math.PI - 90
      } else if (x > a && y > b) { // 右下
        deg = 90 + Math.asin((y - b) / (R - r)) * 180 / Math.PI
      }
      direction.value = Math.round(deg)
    }
    // 见文档 https://oa.yidianhulian.com/doc?docid=849
    const getDragPointXY = (c: number, d: number, r: number) => {
      const rect = circle.value.getBoundingClientRect()
      const a = rect.x + rect.width / 2
      const b = rect.y + rect.height / 2
      const R = 60
      let x = 0
      let y = 0

      // pow 平方， sqrt 开方
      // https://oa.yidianhulian.com/doc?docid=849 根据文档的关系做展看计算
      const z = Math.sqrt(Math.pow(d - b, 2) + Math.pow(a - c, 2)) - R
      const t1 = Math.pow(R - r, 2) - Math.pow(a, 2) - Math.pow(b, 2)
      const t2 = Math.pow(z + r, 2) - Math.pow(c, 2) - Math.pow(d, 2)
      let t3 = 0
      let t4 = 0
      if (a !== c) {
        t3 = (t2 - t1) / (2 * a - 2 * c)
        t4 = (2 * d - 2 * b) / (2 * a - 2 * c)
      } else {
        y = (t1 - t2) / (2 * d - 2 * b)
        const tmp = Math.pow(R - r, 2) - Math.pow(y - b, 2)
        x = a - Math.sqrt(tmp)
        pointX.value = x
        pointY.value = y

        // console.log(`大圆中心a: ${a}, b: ${b} 半径${R} 小圆半径${r}, 拖动点：c: ${c} d: ${d}, 目标： x:${x} y: ${y}`)
        return
      }

      const t7 = t1 - Math.pow(t3, 2) + 2 * a * t3
      const t8 = Math.pow(t4, 2) + 1
      const t9 = 2 * t3 * t4 - 2 * a * t4 - 2 * b
      const t10 = Math.abs(t7 / t8 + Math.pow(t9 / (2 * t8), 2))
      y = Math.sqrt(t10) - t9 / (2 * t8)
      x = t3 + t4 * y

      // console.log(`大圆中心a: ${a}, b: ${b} 半径${R} 小圆半径${r}, 拖动点：c: ${c} d: ${d}, 目标： x:${x} y: ${y}`)
      pointX.value = x
      pointY.value = y
    }
    const drag = (ev) => {
      if (ev.screenX === 0 && ev.screenY === 0) return false // 拖放结束时，X Y会被设置0，这里排除掉
      getDragPointXY(ev.screenX, ev.screenY, 10)
      getDragDeg()
      return false
    }
    const dragStart = (ev) => {
      const img = new Image()
      img.style.display = 'none'
      img.src = require('@/assets/image/transparent.png') // 透明图片
      ev.dataTransfer.setDragImage(img, 0, 0)
    }
    const dragEnd = (ev) => {
    }
    const removeColor = (index: number) => {
      let _ = colors.value
      _.splice(index, 1)
      colors.value = _
      _ = colorSize.value
      _.splice(index, 1)
      colorSize.value = _
    }
    const addColor = () => {
      let _ = colors.value
      _.push('#000000')
      colors.value = _
      _ = colorSize.value
      _.push('')
      colorSize.value = _
    }

    const save = () => {
      pointX.value = -1
      pointY.value = -1
      isOpenDialog.value = false
      context.emit('update:modelValue', gradientInfo.value)
    }
    const getDefaultXY = (deg: number) => {
      if (!circle.value) return ''
      const rect = circle.value.getBoundingClientRect()
      // 大圆半径60 小圆半径10
      const origX = rect.x + 60 // 小圆默认位置
      const origY = rect.y + 10
      const centerX = rect.x + 60 // 大圆中心
      const centerY = rect.y + 60
      if (!deg || deg === 0) { // case 1
        return 'transform: translate(0px, 0px)'
      }
      if (deg === 90) { // case 2
        return `transform: translate(${centerX + 50 - origX}px, ${centerY - origY}px)`
      }
      if (deg === 180) { // case 3
        return `transform: translate(${centerX - origX}px, ${centerY + 50 - origY}px)`
      }
      if (deg === 270) { // case 4
        return `transform: translate(${centerX - 50 - origX}px, ${centerY - origY}px)`
      }
      // 见文档 https://oa.yidianhulian.com/doc?docid=849
      if (deg > 270) { // 左上 case 5
        const x = centerX - Math.sin(2 * Math.PI / 360 * (360 - deg)) * 50
        const y = centerY - Math.sin(2 * Math.PI / 360 * (90 - (360 - deg))) * 50
        return `transform: translate(${x - origX}px, ${y - origY}px)`
      }
      if (deg > 180) { // 左下 case 6
        const x = centerX - Math.sin(2 * Math.PI / 360 * (deg - 180)) * 50
        const y = centerY + Math.sin(2 * Math.PI / 360 * (90 - (deg - 180))) * 50
        return `transform: translate(${x - origX}px, ${y - origY}px)`
      }
      if (deg > 90) { // 右下 case 7
        const y = centerY + Math.sin(2 * Math.PI / 360 * (deg - 90)) * 50
        const x = centerX + Math.sin(2 * Math.PI / 360 * (90 - (deg - 90))) * 50
        console.log(`${x},${y}:${origX},${origY}`)
        return `transform: translate(${x - origX}px, ${y - origY}px)`
      }
      // 小于90度 右上 case 8
      const x = centerX + Math.sin(2 * Math.PI / 360 * deg) * 50
      const y = centerY - Math.sin(2 * Math.PI / 360 * (90 - deg)) * 50
      return `transform: translate(${x - origX}px, ${y - origY}px)`
    }
    return {
      myStyle,
      dragPoint,
      previewStyle,
      direction,
      t,
      colors,
      colorSize,
      gradientInfo,
      removeColor,
      addColor,
      isOpenDialog,
      gradientType,
      sizeShapeType,
      pointY,
      pointX,
      circle,
      dragStyle,
      gradientShape,
      gradientSize,
      radialHeight,
      radialWidth,
      positionX,
      positionY,
      repeat,
      openDialog,
      drag,
      dragStart,
      dragEnd,
      save
    }
  }
}
</script>
