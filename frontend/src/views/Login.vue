<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="card shadow-sm" style="width: 420px; max-width: calc(100vw - 24px);">
            <div class="card-body p-4">
                <h5 class="text-center mb-4">Login</h5>

                <form @submit.prevent="onSubmit">
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input v-model.trim="email" type="email" class="form-control" placeholder="Email"
                            :class="{ 'is-invalid': emailTouched && !isEmailValid }" @blur="emailTouched = true"
                            autocomplete="username" />
                        <div class="invalid-feedback">รูปแบบอีเมลไม่ถูกต้อง</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input v-model="password" type="password" class="form-control" placeholder="Password"
                            autocomplete="current-password" />
                    </div>

                    <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>

                    <button class="btn btn-dark w-100" :disabled="loading || !canSubmit">
                        {{ loading ? 'Loading...' : 'Login' }}
                    </button>
                </form>

                <div class="text-center text-muted mt-3" style="font-size: 12px;">
                    * Validate Email ที่ Frontend ก่อนส่งไป Backend
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()

const email = ref('')
const password = ref('')
const emailTouched = ref(false)
const loading = ref(false)
const error = ref('')

const isEmailValid = computed(() => {
    // simple + practical
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.value)
})
const canSubmit = computed(() => isEmailValid.value && password.value.length > 0)

async function onSubmit() {
    emailTouched.value = true
    error.value = ''
    if (!canSubmit.value) return

    try {
        loading.value = true
        // ✅ API ที่ฝั่ง Laravel ควรคืน { token, user }
        const { data } = await api.post('/auth/login', {
            email: email.value,
            password: password.value,
        })

        localStorage.setItem('token', data.token)
        localStorage.setItem('user', JSON.stringify(data.user))
        router.push('/banking')
    } catch (e) {
        error.value = e?.response?.data?.message || 'Login ไม่สำเร็จ'
    } finally {
        loading.value = false
    }
}
</script>
