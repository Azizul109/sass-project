<template>
    <div class="project-list">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Projects</h4>
            <button class="btn btn-primary" @click="showCreateModal = true">
                Create Project
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Tasks</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="project in projects.data" :key="project.id">
                        <td>{{ project.name }}</td>
                        <td>
                            <span class="badge" :class="statusClass(project.status)">
                                {{ project.status }}
                            </span>
                        </td>
                        <td>{{ project.tasks_count }}</td>
                        <td>{{ project.deadline ? formatDate(project.deadline) : 'N/A' }}</td>
                        <td>
                            <button class="btn btn-sm btn-info" @click="viewProject(project)">
                                View
                            </button>
                            <button class="btn btn-sm btn-warning" 
                                    @click="editProject(project)"
                                    v-if="$page.props.user.role !== 'employee'">
                                Edit
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <pagination :data="projects" @pagination-change-page="loadProjects"></pagination>

        <project-modal 
            v-if="showCreateModal"
            :project="editingProject"
            @close="showCreateModal = false"
            @saved="handleProjectSaved">
        </project-modal>
    </div>
</template>

<script>
import Pagination from './Pagination.vue';
import ProjectModal from './ProjectModal.vue';

export default {
    components: {
        Pagination,
        ProjectModal
    },
    props: {
        projects: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            showCreateModal: false,
            editingProject: null
        }
    },
    methods: {
        statusClass(status) {
            const classes = {
                'planning': 'bg-secondary',
                'active': 'bg-primary',
                'completed': 'bg-success',
                'cancelled': 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString();
        },
        viewProject(project) {
            this.$inertia.visit(`/projects/${project.id}`);
        },
        editProject(project) {
            this.editingProject = project;
            this.showCreateModal = true;
        },
        handleProjectSaved() {
            this.showCreateModal = false;
            this.editingProject = null;
            this.$emit('refreshed');
        },
        loadProjects(page = 1) {
            this.$inertia.visit(`/projects?page=${page}`);
        }
    }
}
</script>