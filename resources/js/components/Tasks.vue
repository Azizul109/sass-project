<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Tasks - {{ project?.name }}</h2>
                <p class="text-muted">{{ project?.description }}</p>
            </div>
            <button class="btn btn-primary" @click="showCreateModal = true" v-if="user.role !== 'employee'">
                Create Task
            </button>
        </div>

        <!-- Tasks Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Due Date</th>
                                <th>Assigned Users</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="task in tasks.data" :key="task.id">
                                <td>{{ task.title }}</td>
                                <td>
                                    <span class="badge" :class="getStatusClass(task.status)">
                                        {{ task.status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ task.priority }}</span>
                                </td>
                                <td>{{ formatDate(task.due_date) }}</td>
                                <td>
                                    <span v-for="user in task.users" :key="user.id" class="badge bg-secondary me-1">
                                        {{ user.name }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info me-1" @click="viewTask(task)">
                                        View
                                    </button>
                                    <button class="btn btn-sm btn-warning me-1" @click="editTask(task)"
                                        v-if="user.role !== 'employee' || task.users.some(u => u.id === user.id)">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" @click="deleteTask(task)"
                                        v-if="user.role !== 'employee'">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav v-if="tasks.meta">
                    <ul class="pagination">
                        <li class="page-item" :class="{ disabled: !tasks.links.prev }">
                            <button class="page-link" @click="loadPage(tasks.meta.current_page - 1)">Previous</button>
                        </li>
                        <li class="page-item" v-for="page in tasks.meta.last_page" :key="page"
                            :class="{ active: page === tasks.meta.current_page }">
                            <button class="page-link" @click="loadPage(page)">{{ page }}</button>
                        </li>
                        <li class="page-item" :class="{ disabled: !tasks.links.next }">
                            <button class="page-link" @click="loadPage(tasks.meta.current_page + 1)">Next</button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Create/Edit Task Modal -->
        <div class="modal fade" :class="{ show: showCreateModal, 'd-block': showCreateModal }" v-if="showCreateModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ editingTask ? 'Edit Task' : 'Create Task' }}</h5>
                        <button type="button" class="btn-close" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="saveTask">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" class="form-control" v-model="taskForm.title" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" v-model="taskForm.status" required>
                                            <option value="pending">Pending</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Priority</label>
                                        <select class="form-select" v-model="taskForm.priority" required>
                                            <option value="1">1 - Lowest</option>
                                            <option value="2">2 - Low</option>
                                            <option value="3">3 - Medium</option>
                                            <option value="4">4 - High</option>
                                            <option value="5">5 - Highest</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Due Date</label>
                                        <input type="date" class="form-control" v-model="taskForm.due_date">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" v-model="taskForm.description" rows="3"></textarea>
                            </div>
                            <div class="mb-3" v-if="user.role !== 'employee'">
                                <label class="form-label">Assign Users</label>
                                <div v-for="user in availableUsers" :key="user.id" class="form-check">
                                    <input class="form-check-input" type="checkbox" :value="user.id"
                                        v-model="taskForm.assigned_users" :id="`user-${user.id}`">
                                    <label class="form-check-label" :for="`user-${user.id}`">
                                        {{ user.name }} ({{ user.role }})
                                    </label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" @click="saveTask" :disabled="loading">
                            <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                            {{ editingTask ? 'Update' : 'Create' }} Task
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" v-if="showCreateModal"></div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    name: 'Tasks',
    data() {
        return {
            project: null,
            tasks: {},
            availableUsers: [],
            showCreateModal: false,
            editingTask: null,
            taskForm: {
                title: '',
                description: '',
                status: 'pending',
                priority: 3,
                due_date: '',
                assigned_users: []
            },
            loading: false
        }
    },
    computed: {
        ...mapGetters(['user']),
        projectId() {
            return this.$route.params.id;
        }
    },
    async mounted() {
        await this.loadProject();
        await this.loadTasks();
        await this.loadAvailableUsers();
    },
    methods: {
        async loadProject() {
            try {
                const response = await axios.get(`/api/projects/${this.projectId}`);
                this.project = response.data;
            } catch (error) {
                console.error('Failed to load project:', error);
                alert('Failed to load project');
            }
        },
        async loadTasks(page = 1) {
            try {
                const response = await axios.get(`/api/projects/${this.projectId}/tasks?page=${page}`);
                this.tasks = response.data;
            } catch (error) {
                console.error('Failed to load tasks:', error);
                alert('Failed to load tasks');
            }
        },
        async loadAvailableUsers() {
            try {
                // In a real app, you'd have an endpoint for company users
                const response = await axios.get('/api/auth/user');
                // For now, we'll just use the current user
                this.availableUsers = [response.data.user];
            } catch (error) {
                console.error('Failed to load users:', error);
            }
        },
        async loadPage(page) {
            await this.loadTasks(page);
        },
        viewTask(task) {
            // Simple view - in real app you might have a detailed view modal
            alert(`Task: ${task.title}\nDescription: ${task.description}\nStatus: ${task.status}`);
        },
        editTask(task) {
            this.editingTask = task;
            this.taskForm = {
                ...task,
                assigned_users: task.users.map(u => u.id)
            };
            this.showCreateModal = true;
        },
        async deleteTask(task) {
            if (confirm('Are you sure you want to delete this task?')) {
                try {
                    // Use the new route: DELETE /api/tasks/{task}
                    await axios.delete(`/api/tasks/${task.id}`);
                    await this.loadTasks();
                    alert('Task deleted successfully');
                } catch (error) {
                    console.error('Failed to delete task:', error);
                    alert('Failed to delete task: ' + error.response?.data?.message);
                }
            }
        },
        closeModal() {
            this.showCreateModal = false;
            this.editingTask = null;
            this.taskForm = {
                title: '',
                description: '',
                status: 'pending',
                priority: 3,
                due_date: '',
                assigned_users: []
            };
        },
        async saveTask() {
            this.loading = true;
            try {
                if (this.editingTask) {
                    // Use the new route: PUT /api/tasks/{task}
                    const response = await axios.put(`/api/tasks/${this.editingTask.id}`, this.taskForm);

                    // Update assigned users if changed
                    if (this.user.role !== 'employee') {
                        await axios.post(`/api/tasks/${this.editingTask.id}/assign-users`, {
                            user_ids: this.taskForm.assigned_users
                        });
                    }
                } else {
                    // Use the project-based route: POST /api/projects/{project}/tasks
                    const response = await axios.post(`/api/projects/${this.projectId}/tasks`, this.taskForm);

                    // Assign users after creating the task
                    if (this.user.role !== 'employee' && this.taskForm.assigned_users.length > 0) {
                        await axios.post(`/api/tasks/${response.data.id}/assign-users`, {
                            user_ids: this.taskForm.assigned_users
                        });
                    }
                }
                this.closeModal();
                await this.loadTasks();
                alert('Task saved successfully');
            } catch (error) {
                console.error('Failed to save task:', error);
                alert('Failed to save task: ' + error.response?.data?.message);
            } finally {
                this.loading = false;
            }
        },
        getStatusClass(status) {
            const classes = {
                'pending': 'bg-secondary',
                'in_progress': 'bg-primary',
                'completed': 'bg-success'
            };
            return classes[status] || 'bg-secondary';
        },
        formatDate(date) {
            if (!date) return 'N/A';
            return new Date(date).toLocaleDateString();
        }
    }
}
</script>