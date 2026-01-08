<template>
  <div class="mx-auto" style="max-width: 720px;">
    <div class="text-center mb-4">
      <div class="fs-5">จำนวนเงินคงเหลือ</div>
      <!-- ใส่สกุลเงินได้ }} *** </div> -->
      <div class="fs-3 fw-bold">{{ formatMoney(balance) }} </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-body p-4">
        <div class="mb-3">
          <label class="form-label">จำนวนเงิน <span class="text-danger">*</span></label>
          <!-- ช่องกรอกจำนวนเงิน -->
          <input v-model="amount" type="number" class="form-control" placeholder="กรอกจำนวนเงิน" min="0" max="100000" />
          <div class="text-muted mt-1" style="font-size: 12px;">กรอกได้เฉพาะ 0 - 100,000</div>
        </div>

        <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>

        <div class="d-flex gap-3 justify-content-center">
          <!-- ปุ่มฝาก -->
          <button class="btn btn-success px-4" @click="openConfirm('DEPOSIT')" :disabled="loading">
            ฝาก
          </button>
          <!-- ปุ่มถอน -->
          <button class="btn btn-danger px-4" @click="openConfirm('WITHDRAW')" :disabled="loading">
            ถอน
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" ref="confirmEl" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body p-4">
            <h6 class="mb-3">ยืนยันการฝาก-ถอน</h6>
            <!-- ใส่สกุลเงินได้ </b> ********** </div> -->
            <div>จำนวนเงิน: <b>{{ formatMoney(Number(amount || 0)) }}</b> </div> 
            <div class="text-muted mt-1" style="font-size: 12px;">โดย {{ user?.email || '-' }}</div>

            <div class="d-flex gap-2 justify-content-end mt-4">
              <button class="btn btn-dark" @click="confirm">ยืนยัน</button>
              <button class="btn btn-outline-secondary" @click="hideConfirm">ยกเลิก</button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import api from '../api'

const props = defineProps({
  user: Object, // ข้อมูลผู้ใช้
  balance: Number, // ยอดคงเหลือ
})
// แจ้ง เมื่อยอดเงินเปลี่ยน
const emit = defineEmits(['balance-updated'])

const amount = ref('')
const loading = ref(false)
const error = ref('')
const actionType = ref(null)
const confirmEl = ref(null)
let bsModal = null

// ตรวจสอบความถูกต้องของจำนวนเงิน 1-100,000
const isAmountValid = computed(() => {
  const v = Number(amount.value)
  return Number.isFinite(v) && v >= 0 && v <= 100000
})

// ฟังก์ชัน แสดงจำนวนเงินในรูปแบบสกุลเงินบาท
function formatMoney(n) {
  return new Intl.NumberFormat('th-TH', { maximumFractionDigits: 2 }).format(n || 0)
}

function openConfirm(type) {
  error.value = ''
  if (!isAmountValid.value) {
    error.value = 'กรุณากรอกจำนวนเงิน 0 - 100,000'
    return
  }
  actionType.value = type
  bsModal?.show()
}

function hideConfirm() {
  bsModal?.hide()
}

// ยืนยันการทำรายการ
async function confirm() {
  try {
    loading.value = true
    // เรียก API สร้าง transaction
    const { data } = await api.post('/transactions', {
      type: actionType.value,
      amount: Number(amount.value),
    })
    emit('balance-updated', data.balance)
    amount.value = ''
    hideConfirm()
  } catch (e) {
    // แสดง error
    error.value = e?.response?.data?.message || 'ทำรายการไม่สำเร็จ'
    hideConfirm()
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  const { Modal } = await import('bootstrap')
  bsModal = new Modal(confirmEl.value, { backdrop: 'static' })
})
onBeforeUnmount(() => bsModal?.dispose())
</script>
