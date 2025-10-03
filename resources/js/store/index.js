import { createStore } from 'vuex';

export default createStore({
    state: {
        auth: {
            isAuthenticated: false,
            user: null,
            token: localStorage.getItem('token') || null
        },
        loading: false
    },
    mutations: {
        SET_AUTH(state, { user, token }) {
            state.auth.isAuthenticated = true;
            state.auth.user = user;
            state.auth.token = token;
            localStorage.setItem('token', token);
        },
        CLEAR_AUTH(state) {
            state.auth.isAuthenticated = false;
            state.auth.user = null;
            state.auth.token = null;
            localStorage.removeItem('token');
        },
        SET_LOADING(state, loading) {
            state.loading = loading;
        },
        SET_USER(state, user) {
            state.auth.user = user;
        }
    },
    actions: {
        async login({ commit }, credentials) {
            try {
                // First, get CSRF cookie
                await axios.get('/sanctum/csrf-cookie');
                
                const response = await axios.post('/api/auth/login', credentials);
                commit('SET_AUTH', {
                    user: response.data.user,
                    token: response.data.access_token
                });
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;
                return response;
            } catch (error) {
                commit('CLEAR_AUTH');
                throw error;
            }
        },
        async register({ commit }, userData) {
            try {
                // First, get CSRF cookie
                await axios.get('/sanctum/csrf-cookie');
                
                const response = await axios.post('/api/auth/register', userData);
                commit('SET_AUTH', {
                    user: response.data.user,
                    token: response.data.access_token
                });
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;
                return response;
            } catch (error) {
                commit('CLEAR_AUTH');
                throw error;
            }
        },
        async logout({ commit }) {
            try {
                await axios.post('/api/auth/logout');
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                commit('CLEAR_AUTH');
                delete axios.defaults.headers.common['Authorization'];
            }
        },
        async checkAuth({ commit }) {
            const token = localStorage.getItem('token');
            if (token) {
                try {
                    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                    const response = await axios.get('/api/auth/user');
                    commit('SET_AUTH', {
                        user: response.data.user,
                        token: token
                    });
                } catch (error) {
                    commit('CLEAR_AUTH');
                }
            }
        }
    },
    getters: {
        isAuthenticated: state => state.auth.isAuthenticated,
        user: state => state.auth.user,
        isLoading: state => state.loading
    }
});