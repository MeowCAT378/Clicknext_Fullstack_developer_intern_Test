<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        <div class="card shadow-sm" style="width: 420px; max-width: calc(100vw - 24px);">
            <div class="card-body p-4">
                <h5 class="text-center mb-4">Login</h5>

                <LoginForm />

                <div class="text-center text-muted mt-3" style="font-size: 12px;">
                    <!-- * Validate Email ที่ Frontend ก่อนส่งไป Backend -->
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import LoginForm from '@/components/LoginForm.vue'
</script>
