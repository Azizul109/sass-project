<template>
    <div>
        <h2>Dashboard</h2>
        
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-4" v-for="stat in stats" :key="stat.title">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h4 class="card-title">{{ stat.value }}</h4>
                        <p class="card-text">{{ stat.title }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Projects</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="recentProjects.length === 0" class="text-center py-3">
                            <p>No projects yet. <router-link to="/projects">Create your first project</router-link></p>
                        </div>
                        <div v-else>
                            <div class="list-group">
                                <div v-for="project in recentProjects" :key="project.id" 
                                     class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ project.name }}</h6>
                                        <small class="text-muted">{{ project.description }}</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-primary rounded-pill me-2">
                                            {{ project.tasks_count }} tasks
                                        </span>
                                        <router-link :to="`/projects/${project.id}/tasks`" 
                                                     class="btn btn-sm btn-outline-primary">
                                            View Tasks
                                        </router-link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Task Status</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="taskStatusData">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Pending:</span>
                                <strong>{{ taskStatusData.pending }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>In Progress:</span>
                                <strong>{{ taskStatusData.in_progress }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Completed:</span>
                                <strong>{{ taskStatusData.completed }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Dashboard',
    data() {
        return {
            stats: [],
            recentProjects: [],
            taskStatusData: null
        }
    },
    async mounted() {
        await this.loadDashboardData();
    },
    methods: {
        async loadDashboardData() {
            try {
                const response = await axios.get('/api/dashboard');
                this.stats = response.data.stats;
                this.recentProjects = response.data.recent_projects;
                this.taskStatusData = response.data.task_status_data;
            } catch (error) {
                console.error('Failed to load dashboard:', error);
            }
        }
    }
}
</script>