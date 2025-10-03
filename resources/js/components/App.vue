<template>
    <div id="app">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark" v-if="isAuthenticated">
            <div class="container">
                <a class="navbar-brand" href="#">Project Management SaaS</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <router-link to="/" class="nav-link">Dashboard</router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/projects" class="nav-link">Projects</router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/scraping" class="nav-link">Scraping</router-link>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                {{ user.name }} ({{ user.role }})
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" @click="logout">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container mt-4">
            <router-view></router-view>
        </div>

        <!-- Loading Spinner -->
        <div v-if="isLoading" class="loading-overlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from 'vuex';

export default {
    name: 'App',
    computed: {
        ...mapGetters(['isAuthenticated', 'user', 'isLoading'])
    },
    methods: {
        ...mapActions(['logout', 'checkAuth']),
        async handleLogout() {
            await this.logout();
            this.$router.push('/login');
        }
    },
    created() {
        this.checkAuth();
    }
}
</script>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
</style>