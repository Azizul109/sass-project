<template>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Register</h4>
                </div>
                <div class="card-body">
                    <form @submit.prevent="handleRegister">
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" v-model="form.company_name" required>
                            <div class="text-danger" v-if="errors.company_name">{{ errors.company_name[0] }}</div>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="name" v-model="form.name" required>
                            <div class="text-danger" v-if="errors.name">{{ errors.name[0] }}</div>
                        </div>
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
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation" v-model="form.password_confirmation" required>
                        </div>
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
                                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                                Register
                            </button>
                        </div>
                        <div class="text-center">
                            <router-link to="/login">Already have an account? Login</router-link>
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
    name: 'Register',
    data() {
        return {
            form: {
                company_name: '',
                name: '',
                email: '',
                password: '',
                password_confirmation: ''
            },
            errors: {},
            loading: false
        }
    },
    methods: {
        ...mapActions(['register']),
        async handleRegister() {
            this.loading = true;
            this.errors = {};
            
            try {
                await this.register(this.form);
                this.$router.push('/');
            } catch (error) {
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    this.errors = { general: [error.response?.data?.message || 'Registration failed'] };
                }
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>