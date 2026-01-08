<template>
    <div class="mx-auto" style="max-width: 980px;">
        <h5 class="text-center mb-3">ประวัติรายการฝากถอน</h5>

        <!-- Transaction Table -->
        <div class="card shadow-sm">
            <div class="card-body p-3 p-md-4">
                <!-- responsive -->
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 180px;">Datetime</th>
                                <th style="width: 120px;">Amount</th>
                                <th style="width: 120px;">Status</th>
                                <th>Email</th>
                                <th style="width: 140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Body -->
                            <tr v-for="t in items" :key="t.id">
                                <!-- ลูปแสดงรายการ -->
                                <td>{{ formatDateTime(t.created_at) }}</td>
                                <td>{{ formatMoney(t.amount) }}</td>
                                <td>
                                    <!-- ประเภทฝาก / ถอน -->
                                    <span
                                        :class="t.type === 'DEPOSIT' ? 'text-success fw-semibold' : 'text-danger fw-semibold'">
                                        {{ t.type === 'DEPOSIT' ? 'ฝาก' : 'ถอน' }}
                                    </span>
                                </td>
                                <!-- Email ผู้ใช้ -->
                                <td class="text-start">{{ user?.email }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-dark btn-sm" @click="openEdit(t)">Edit</button>
                                        <button class="btn btn-outline-dark btn-sm"
                                            @click="openDelete(t)">Delete</button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="!loading && items.length === 0">
                                <td colspan="5" class="text-muted py-4">ไม่มีรายการ</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <!-- จำนวนรายการที่แสดง -->
                    <div class="text-muted" style="font-size: 12px;">
                        แสดง {{ items.length ? 1 : 0 }} ถึง {{ items.length }} จาก {{ items.length }} รายการ
                    </div>
                    <!-- ปุ่ม Refresh -->
                    <button class="btn btn-outline-secondary btn-sm" @click="fetchList" :disabled="loading">
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" ref="editEl" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-4">
                        <h6 class="mb-1">แก้ไขจำนวนเงินฝาก</h6>
                        <!-- รายละเอียด -->
                        <div class="text-muted mb-3" style="font-size: 12px;">
                            ของวันที่ {{ editItem ? formatDateTime(editItem.created_at) : '-' }}<br />
                            จากอีเมล {{ user?.email }}
                        </div>

                        <!-- Input จำนวนเงิน   -->
                        <label class="form-label">จำนวนเงิน <span class="text-danger">*</span></label>
                        <input v-model="editAmount" type="number" class="form-control" min="0" max="100000" />

                        <div class="text-muted mt-1" style="font-size: 12px;">0 - 100,000</div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <button class="btn btn-dark" @click="confirmEdit">ยืนยัน</button>
                            <button class="btn btn-outline-secondary" @click="hideEdit">ยกเลิก</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" ref="delEl" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-4">
                        <h6 class="mb-2">ยืนยันการลบ</h6>
                        <div v-if="deleteItem">
                            <!-- ใส่สกุลเงินได้ </b> ********** </div> -->
                            จำนวนเงิน: <b>{{ formatMoney(deleteItem.amount) }}</b><br />
                            ของวันที่ {{ formatDateTime(deleteItem.created_at) }}<br />
                            จากอีเมล {{ user?.email }}
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <button class="btn btn-dark" @click="confirmDelete">ยืนยัน</button>
                            <button class="btn btn-outline-secondary" @click="hideDelete">ยกเลิก</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import api from '../api'

const props = defineProps({
    user: Object,
})
const emit = defineEmits(['balance-updated'])

const items = ref([])
const loading = ref(false)
const editItem = ref(null)
const editAmount = ref('')
const deleteItem = ref(null)
const editEl = ref(null)
const delEl = ref(null)
let editModal = null
let delModal = null

// ฟอร์แมตจำนวนเงิน
function formatMoney(n) {
    return new Intl.NumberFormat('th-TH', { maximumFractionDigits: 2 }).format(Number(n || 0))
}

// ฟอร์แมตวันที่และเวลา
function formatDateTime(iso) {
    if (!iso) return '-'
    const d = new Date(iso)
    return new Intl.DateTimeFormat('th-TH', {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
    }).format(d)
}

// ดึงรายการทั้งหมด
async function fetchList() {
    try {
        loading.value = true
        const { data } = await api.get('/transactions')
        items.value = data.items || []
    } finally {
        loading.value = false
    }
}

function openEdit(t) {
    editItem.value = t
    editAmount.value = String(t.amount)
    editModal?.show()
}

function hideEdit() {
    editModal?.hide()
}

async function confirmEdit() {
    const v = Number(editAmount.value)
    if (!(v >= 0 && v <= 100000)) return

    await api.put(`/transactions/${editItem.value.id}`, { amount: v })
    hideEdit()
    await fetchList()

    // backend คืน balance ใหม่ มาให้
    const me = await api.get('/me')
    emit('balance-updated', me.data.balance)
}

function openDelete(t) {
    deleteItem.value = t
    delModal?.show()
}

function hideDelete() {
    delModal?.hide()
}

// ยืนยันการลบ
async function confirmDelete() {
    await api.delete(`/transactions/${deleteItem.value.id}`)
    hideDelete()
    await fetchList()

    // update ยอดเงินคงเหลือหลังลบ
    const me = await api.get('/me')
    emit('balance-updated', me.data.balance)
}

onMounted(async () => {
    const { Modal } = await import('bootstrap')
    editModal = new Modal(editEl.value, { backdrop: 'static' })
    delModal = new Modal(delEl.value, { backdrop: 'static' })
    fetchList()
})

onBeforeUnmount(() => {
    editModal?.dispose()
    delModal?.dispose()
})
</script>
