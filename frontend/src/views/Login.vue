<template>
    <!--Test
    <div style="position:fixed;bottom:10px;right:10px;z-index:9999;background:#000;color:#fff;padding:6px 10px;">
  LOGIN_VUE_EDIT_OK
</div>
-->
    <!-- Login Page Layout -->
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="card shadow-sm" style="width: 420px; max-width: calc(100vw - 24px);">
            <div class="card-body p-4">
                <h5 class="text-center mb-4">Login</h5>

                <!--  Login Form  -->
                <form @submit.prevent="onSubmit">
                    <div class="mb-3">
                        <!-- Email -->
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input v-model.trim="email" type="email" class="form-control" placeholder="Email"
                            :class="{ 'is-invalid': emailTouched && !isEmailValid }" @blur="emailTouched = true"
                            autocomplete="username" />
                        <!-- Email Validation -->
                        <div class="invalid-feedback">รูปแบบอีเมลไม่ถูกต้อง</div>
                    </div>

                    <div class="mb-3">
                        <!-- Password  -->
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input v-model="password" type="password" class="form-control" placeholder="Password"
                            autocomplete="current-password" />
                    </div>

                    <!-- Error จาก Backend -->
                    <div v-if="error" class="alert alert-danger py-2">{{ error }}</div>

                    <!-- Submit กดรอโหลด 2-3 วิ -->
                    <button class="btn btn-dark w-100" :disabled="loading || !canSubmit">
                        {{ loading ? 'Loading...' : 'Login' }}
                    </button>
                </form>

                <div class="text-center text-muted mt-3" style="font-size: 12px;">
                    <!--  * Validate Email ที่ Frontend ก่อนส่งไป Backend -->
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

//  ตรวจสอบรูปแบบ email
const isEmailValid = computed(() => {
    // simple + practical
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.value)
})
// submit form ได้หรือไม่
const canSubmit = computed(() => isEmailValid.value && password.value.length > 0)

// mark email ว่าถูกแตะแล้ว เพื่อแสดง validation
async function onSubmit() {
    emailTouched.value = true
    error.value = ''
    // ถ้าข้อมูลไม่ครบ 
    if (!canSubmit.value) return

    try {
        loading.value = true
        //  API ที่ฝั่ง Laravel เพื่อ login
        const { data } = await api.post('/auth/login', {
            email: email.value,
            password: password.value,
        })

        // เก็บ token และ user ลง localStorage
        localStorage.setItem('token', data.token)
        localStorage.setItem('user', JSON.stringify(data.user))
        // redirect ไปหน้า banking
        router.push('/banking')

    } catch (e) {
        // กรณี login ไม่ผ่าน
        error.value = e?.response?.data?.message || 'Login ไม่สำเร็จ'
    } finally {
        loading.value = false
    }
}
</script>
