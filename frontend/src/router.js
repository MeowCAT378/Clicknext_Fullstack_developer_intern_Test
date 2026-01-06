import { createRouter, createWebHistory } from 'vue-router'
import Login from './views/Login.vue'
import Banking from './views/Banking.vue'

const routes = [
    { path: '/', redirect: '/banking' },
    { path: '/login', component: Login, meta: { guest: true } },
    { path: '/banking', component: Banking, meta: { auth: true } },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach((to) => {
    const token = localStorage.getItem('token')
    if (to.meta.auth && !token) return '/login'
    if (to.meta.guest && token) return '/banking'
})

export default router
