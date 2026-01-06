<template>
    <div class="min-vh-100 bg-white">
        <!-- Header -->
        <nav class="navbar navbar-light border-bottom px-3">
            <button class="btn btn-outline-dark d-md-none" @click="drawerOpen = true">
                ☰
            </button>
            <div class="fw-semibold">Clicknext</div>
            <button class="btn btn-dark btn-sm" @click="logout">Logout</button>
        </nav>

        <div class="d-flex">
            <!-- Sidebar (desktop) -->
            <aside class="border-end d-none d-md-block" style="width: 220px; min-height: calc(100vh - 56px);">
                <div class="p-3">
                    <button class="btn w-100 text-start mb-2" :class="activeTab === 'dw' ? 'btn-dark' : 'btn-light'"
                        @click="activeTab = 'dw'">
                        Deposit / Withdraw
                    </button>

                    <button class="btn w-100 text-start" :class="activeTab === 'tx' ? 'btn-dark' : 'btn-light'"
                        @click="activeTab = 'tx'">
                        Transaction
                    </button>
                </div>
            </aside>

            <!-- Content -->
            <main class="flex-grow-1 p-3 p-md-4">
                <DepositWithdraw v-if="activeTab === 'dw'" :user="user" :balance="balance"
                    @balance-updated="balance = $event" />
                <Transactions v-else :user="user" @balance-updated="balance = $event" />
            </main>
        </div>

        <!-- Drawer (mobile) -->
        <div v-if="drawerOpen" class="position-fixed top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,.35);"
            @click.self="drawerOpen = false">
            <div class="bg-white h-100" style="width: 260px;">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">Menu</div>
                    <button class="btn btn-sm btn-outline-dark" @click="drawerOpen = false">Close</button>
                </div>
                <div class="p-3">
                    <button class="btn w-100 text-start mb-2" :class="activeTab === 'dw' ? 'btn-dark' : 'btn-light'"
                        @click="activeTab = 'dw'; drawerOpen = false">
                        Deposit / Withdraw
                    </button>
                    <button class="btn w-100 text-start" :class="activeTab === 'tx' ? 'btn-dark' : 'btn-light'"
                        @click="activeTab = 'tx'; drawerOpen = false">
                        Transaction
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'
import DepositWithdraw from '../components/DepositWithdraw.vue'
import Transactions from '../components/Transactions.vue'

const router = useRouter()

const drawerOpen = ref(false)
const activeTab = ref('dw')

const user = ref(null)
const balance = ref(0)

async function loadMe() {
    // refresh แล้วไม่ต้อง login ใหม่: ใช้ localStorage + ยิง /me
    const raw = localStorage.getItem('user')
    if (raw) user.value = JSON.parse(raw)

    try {
        const { data } = await api.get('/me')
        user.value = data.user
        balance.value = data.balance
        localStorage.setItem('user', JSON.stringify(data.user))
    } catch (e) {
        // token เสีย/หมดอายุ
        logout()
    }
}

function logout() {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
}

onMounted(loadMe)
</script>
