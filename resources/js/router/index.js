import { createRouter, createWebHistory } from 'vue-router';
import store from '../store';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: () => import('../components/Dashboard.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../components/auth/Login.vue'),
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../components/auth/Register.vue'),
        meta: { guest: true }
    },
    {
        path: '/projects',
        name: 'projects',
        component: () => import('../components/Projects.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/projects/:id/tasks',
        name: 'tasks',
        component: () => import('../components/Tasks.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/scraping',
        name: 'scraping',
        component: () => import('../components/Scraping.vue'),
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth && !store.state.auth.isAuthenticated) {
        next('/login');
    } else if (to.meta.guest && store.state.auth.isAuthenticated) {
        next('/');
    } else {
        next();
    }
});

export default router;