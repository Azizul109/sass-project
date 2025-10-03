<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Projects</h2>
            <button class="btn btn-primary" @click="showCreateModal = true" v-if="canCreateProjects">
                <i class="fas fa-plus me-1"></i>
                Create Project
            </button>
        </div>

        <!-- Role-based information banner -->
        <div class="alert alert-info" v-if="user.role === 'employee'">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Employee Access:</strong> You can view projects and tasks assigned to you.
            Contact a manager to request project modifications.
        </div>

        <!-- Projects Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Tasks</th>
                                <th>Deadline</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="project in projects.data" :key="project.id">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-folder text-warning me-2"></i>
                                        <div>
                                            <strong class="d-block">{{ project.name }}</strong>
                                            <small class="text-muted" v-if="project.description">
                                                {{ truncateText(project.description, 50) }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" :class="getStatusClass(project.status)">
                                        <i class="fas me-1" :class="getStatusIcon(project.status)"></i>
                                        {{ project.status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <span class="fw-bold text-primary fs-5">{{ project.tasks_count }}</span>
                                        <div class="text-muted small">tasks</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <div :class="{ 'text-danger fw-bold': isOverdue(project.deadline) }">
                                            {{ formatDate(project.deadline) }}
                                        </div>
                                        <span v-if="isOverdue(project.deadline)" class="badge bg-danger mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Overdue
                                        </span>
                                        <span v-else-if="isDueSoon(project.deadline)" class="badge bg-warning mt-1">
                                            <i class="fas fa-clock me-1"></i>Due Soon
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <!-- Action Buttons - Always Visible Row -->
                                    <div class="action-buttons">
                                        <!-- View Tasks - Available to all roles -->
                                        <button class="btn btn-primary btn-sm action-btn" @click="viewTasks(project)"
                                            title="View Tasks">
                                            <i class="fas fa-tasks"></i>
                                            <span class="btn-text">Tasks</span>
                                        </button>

                                        <!-- Edit Project - Managers and Owners only -->
                                        <button class="btn btn-warning btn-sm action-btn" @click="editProject(project)"
                                            v-if="canEditProjects" title="Edit Project">
                                            <i class="fas fa-edit"></i>
                                            <span class="btn-text">Edit</span>
                                        </button>

                                        <!-- Delete Project - Owners only -->
                                        <button class="btn btn-danger btn-sm action-btn" @click="deleteProject(project)"
                                            v-if="canDeleteProjects" title="Delete Project">
                                            <i class="fas fa-trash"></i>
                                            <span class="btn-text">Delete</span>
                                        </button>

                                        <!-- Quick Actions Dropdown for Mobile -->
                                        <div class="dropdown d-inline-block d-md-none">
                                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#" @click="viewTasks(project)">
                                                        <i class="fas fa-tasks me-2"></i>View Tasks
                                                    </a>
                                                </li>
                                                <li v-if="canEditProjects">
                                                    <a class="dropdown-item" href="#" @click="editProject(project)">
                                                        <i class="fas fa-edit me-2"></i>Edit Project
                                                    </a>
                                                </li>
                                                <li v-if="canDeleteProjects">
                                                    <a class="dropdown-item text-danger" href="#"
                                                        @click="deleteProject(project)">
                                                        <i class="fas fa-trash me-2"></i>Delete Project
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="projects.data && projects.data.length === 0" class="text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5>No Projects Found</h5>
                    <p class="text-muted" v-if="canCreateProjects">
                        Get started by creating your first project.
                    </p>
                    <p class="text-muted" v-else>
                        No projects have been created yet. Contact a manager to create projects.
                    </p>
                    <button v-if="canCreateProjects" class="btn btn-primary mt-2" @click="showCreateModal = true">
                        <i class="fas fa-plus me-1"></i>
                        Create First Project
                    </button>
                </div>

                <!-- Pagination -->
                <nav v-if="projects.meta && projects.meta.last_page > 1" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item" :class="{ disabled: !projects.links.prev }">
                            <button class="page-link" @click="loadPage(projects.meta.current_page - 1)">
                                <i class="fas fa-chevron-left"></i> Previous
                            </button>
                        </li>

                        <!-- Page Numbers -->
                        <li class="page-item" v-for="page in displayedPages" :key="page"
                            :class="{ active: page === projects.meta.current_page }">
                            <button class="page-link" @click="loadPage(page)">{{ page }}</button>
                        </li>

                        <li class="page-item" :class="{ disabled: !projects.links.next }">
                            <button class="page-link" @click="loadPage(projects.meta.current_page + 1)">
                                Next <i class="fas fa-chevron-right"></i>
                            </button>
                        </li>
                    </ul>

                    <div class="text-center text-muted small mt-2">
                        Showing {{ projects.meta.from || 0 }} to {{ projects.meta.to || 0 }} of
                        {{ projects.meta.total || 0 }} projects
                    </div>
                </nav>
            </div>
        </div>

        <!-- Create/Edit Project Modal -->
        <div class="modal fade" :class="{ show: showCreateModal, 'd-block': showCreateModal }" v-if="showCreateModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas" :class="editingProject ? 'fa-edit' : 'fa-plus'"></i>
                            {{ editingProject ? 'Edit Project' : 'Create New Project' }}
                        </h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveProject">
                            <div class="mb-3">
                                <label class="form-label">Project Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" v-model="projectForm.name"
                                    placeholder="Enter project name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" v-model="projectForm.description" rows="3"
                                    placeholder="Project description (optional)"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" v-model="projectForm.status" required>
                                            <option value="planning">📋 Planning</option>
                                            <option value="active">🚀 Active</option>
                                            <option value="completed">✅ Completed</option>
                                            <option value="cancelled">❌ Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Deadline</label>
                                        <input type="date" class="form-control" v-model="projectForm.deadline"
                                            :min="new Date().toISOString().split('T')[0]">
                                        <div class="form-text">Optional project deadline</div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary" @click="saveProject" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="fas" :class="editingProject ? 'fa-save' : 'fa-plus'"></i>
                            {{ editingProject ? 'Update Project' : 'Create Project' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Backdrop -->
        <div class="modal-backdrop fade show" v-if="showCreateModal"></div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'Projects',
    data() {
        return {
            projects: {},
            showCreateModal: false,
            editingProject: null,
            projectForm: {
                name: '',
                description: '',
                status: 'planning',
                deadline: ''
            },
            loading: false
        }
    },
    computed: {
        ...mapGetters(['user']),

        // Role-based computed properties
        canCreateProjects() {
            return this.user.role !== 'employee'; // Managers and Owners
        },

        canEditProjects() {
            return this.user.role !== 'employee'; // Managers and Owners
        },

        canDeleteProjects() {
            return this.user.role === 'owner'; // Owners only
        },

        // Pagination display logic
        displayedPages() {
            if (!this.projects.meta) return [];

            const current = this.projects.meta.current_page;
            const last = this.projects.meta.last_page;
            const delta = 2; // Number of pages to show on each side of current page

            let range = [];
            for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
                range.push(i);
            }
            return range;
        }
    },
    async mounted() {
        await this.loadProjects();
    },
    methods: {
        async loadProjects(page = 1) {
            try {
                const response = await axios.get(`/api/projects?page=${page}`);
                this.projects = response.data;
            } catch (error) {
                console.error('Failed to load projects:', error);
                this.showError('Failed to load projects');
            }
        },

        async loadPage(page) {
            await this.loadProjects(page);
        },

        viewTasks(project) {
            this.$router.push(`/projects/${project.id}/tasks`);
        },

        editProject(project) {
            this.editingProject = project;
            this.projectForm = { ...project };
            this.showCreateModal = true;
        },

        async deleteProject(project) {
            if (!confirm(`Are you sure you want to delete "${project.name}"? This action cannot be undone.`)) {
                return;
            }

            try {
                await axios.delete(`/api/projects/${project.id}`);
                await this.loadProjects();
                this.showSuccess('Project deleted successfully');
            } catch (error) {
                console.error('Failed to delete project:', error);
                this.showError('Failed to delete project. You may not have permission.');
            }
        },

        closeModal() {
            this.showCreateModal = false;
            this.editingProject = null;
            this.projectForm = {
                name: '',
                description: '',
                status: 'planning',
                deadline: ''
            };
        },

        async saveProject() {
            this.loading = true;
            try {
                if (this.editingProject) {
                    await axios.put(`/api/projects/${this.editingProject.id}`, this.projectForm);
                    this.showSuccess('Project updated successfully');
                } else {
                    await axios.post('/api/projects', this.projectForm);
                    this.showSuccess('Project created successfully');
                }
                this.closeModal();
                await this.loadProjects();
            } catch (error) {
                console.error('Failed to save project:', error);
                const message = error.response?.data?.message || 'Failed to save project';
                this.showError(message);
            } finally {
                this.loading = false;
            }
        },

        getStatusClass(status) {
            const classes = {
                'planning': 'bg-secondary',
                'active': 'bg-primary',
                'completed': 'bg-success',
                'cancelled': 'bg-danger'
            };
            return classes[status] || 'bg-secondary';
        },

        getStatusIcon(status) {
            const icons = {
                'planning': 'fa-clipboard-list',
                'active': 'fa-play-circle',
                'completed': 'fa-check-circle',
                'cancelled': 'fa-times-circle'
            };
            return icons[status] || 'fa-folder';
        },

        formatDate(date) {
            if (!date) return 'Not set';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        truncateText(text, length) {
            if (!text) return '';
            return text.length > length ? text.substring(0, length) + '...' : text;
        },

        isOverdue(date) {
            if (!date) return false;
            return new Date(date) < new Date() && new Date(date).toDateString() !== new Date().toDateString();
        },

        isDueSoon(date) {
            if (!date) return false;
            const dueDate = new Date(date);
            const today = new Date();
            const diffTime = dueDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return diffDays <= 7 && diffDays > 0;
        },

        showSuccess(message) {
            // You can replace this with a proper notification system
            alert(message);
        },

        showError(message) {
            // You can replace this with a proper notification system
            alert('Error: ' + message);
        }
    }
}
</script>

<style scoped>
.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease-in-out;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-text {
    display: inline;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .btn-text {
        display: none;
    }

    .action-btn {
        padding: 0.375rem;
    }

    .action-buttons {
        gap: 0.25rem;
    }
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.025);
}

.badge {
    font-size: 0.75em;
    padding: 0.5em 0.75em;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.modal-backdrop {
    opacity: 0.5;
}

/* Custom colors for action buttons */
.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #000;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Status badge enhancements */
.badge.bg-primary {
    background: linear-gradient(45deg, #0d6efd, #0a58ca);
}

.badge.bg-success {
    background: linear-gradient(45deg, #198754, #146c43);
}

.badge.bg-secondary {
    background: linear-gradient(45deg, #6c757d, #495057);
}

.badge.bg-danger {
    background: linear-gradient(45deg, #dc3545, #b02a37);
}
</style>