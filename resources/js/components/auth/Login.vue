<template>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <form @submit.prevent="handleLogin">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" v-model="form.email" required>
                            <div class="text-danger" v-if="errors.email">{{ errors.email[0] }}</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" v-model="form.password" required>
                            <div class="text-danger" v-if="errors.password">{{ errors.password[0] }}</div>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                                Login
                            </button>
                        </div>
                        <div class="text-center">
                            <router-link to="/register">Don't have an account? Register</router-link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions } from 'vuex';

export default {
    name: 'Login',
    data() {
        return {
            form: {
                email: '',
                password: ''
            },
            errors: {},
            loading: false
        }
    },
    methods: {
        ...mapActions(['login']),
        async handleLogin() {
            this.loading = true;
            this.errors = {};

            try {
                await this.login(this.form);
                this.$router.push('/');
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    this.errors = { general: [error.response?.data?.message || 'Login failed'] };
                }
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>