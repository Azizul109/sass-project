import './bootstrap';
import './bootstrap';
import { createApp } from 'vue';
import router from './router';
import store from './store';

// Components
import App from './components/App.vue';
import Login from './components/auth/Login.vue';
import Register from './components/auth/Register.vue';
import Dashboard from './components/Dashboard.vue';
import Projects from './components/Projects.vue';
import Tasks from './components/Tasks.vue';
import Scraping from './components/Scraping.vue';

const app = createApp(App);

// Register components
app.component('Login', Login);
app.component('Register', Register);
app.component('Dashboard', Dashboard);
app.component('Projects', Projects);
app.component('Tasks', Tasks);
app.component('Scraping', Scraping);

// Use router and store
app.use(router);
app.use(store);

app.mount('#app');