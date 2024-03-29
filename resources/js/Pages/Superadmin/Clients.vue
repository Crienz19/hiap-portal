
<template>
    <superadmin-layout>
        <div class="row">
            <div class="col-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-info elevation-1">
                        <i class="fas fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Total Registers
                        </span>
                        <span class="info-box-number">
                            {{ total_clients }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-success elevation-1">
                        <i class="fas fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Is Verfied
                        </span>
                        <span class="info-box-number">
                            {{ verified }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-danger elevation-1">
                        <i class="fas fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Not Verified
                        </span>
                        <span class="info-box-number">
                            {{ unverified }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="info-box mb-3">
                    <span class="info-box-icon bg-warning elevation-1">
                        <i class="fas fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">
                            Is Filled
                        </span>
                        <span class="info-box-number">
                            {{ isFilled }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="m-0 card-title flex-grow-1">Enrollees</h5>
                <div>
                    <div class="input-group input-group-sm" style="width: 300px">
                    <input @keypress.enter="searchByEmail" type="text" class="form-control" v-model="filterName" placeholder="Search by email">
                    <span class="input-group-append">
                        <button @click="searchByEmail" class="btn btn-info btn-flat">
                            <span class="fas fa-search"></span>
                        </button>
                    </span>
                </div>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr class="text-xs text-center">
                            <th class="text-left">#</th>
                            <th>Email</th>
                            <th>Is Verified</th>
                            <th>Is Filled</th>
                            <th>Has Client Record</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody v-if="sClients.data.length > 0">
                        <tr class="text-xs text-center" v-for="client in sClients.data" :key="client.id">
                            <td class="text-left">{{ client.id }}</td>
                            <td>{{ client.email }}</td>
                            <td>
                                <span v-if="client.email_verified_at" class="fas fa-check text-green"></span>
                                <span v-else class="fas fa-times text-red"></span>
                            </td>
                            <td>
                                <span v-if="client.isFilled" class="fas fa-check text-green"></span>
                                <span v-else class="fas fa-times text-red"></span>
                            </td>
                            <td>
                                <span v-if="client.client_count > 0" class="fas fa-check text-green"></span>
                                <span v-else class="fas fa-times text-red"></span>
                            </td>
                            <th>{{ client.created_at }}</th>
                            <td>
                                <button v-if="!client.email_verified_at" @click="verifyNow(client)" class="btn btn-warning btn-xs btn-flat">Verify</button>
                                <button v-if="client.client_count > 0" @click="viewClientDetails(client.id)" class="btn btn-success btn-xs btn-flat">View</button>
                                <button v-if="client.isFilled == false" @click="setToFilled(client)" class="btn btn-info btn-xs btn-flat">Filled</button>
                                <button v-if="client.isFilled == true" @click="setToUnfilled(client)" class="btn btn-info btn-xs btn-flat">Unfilled</button>
                                <button @click="deleteClientDetails(client.id)" class="btn btn-danger btn-xs btn-flat">Delete</button>
                                <button @click="resetToDefaultPassword(client.id)" class="btn btn-primary btn-xs btn-flat">Default password</button>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr class="text-xs text-center">
                            <td colspan="6">No Client Registered</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer p-2">
                <button :disabled="!sClients.prev_page_url" @click="prevPage()" class="btn btn-primary btn-xs">Prev</button>
                <button :disabled="!sClients.next_page_url" @click="nextPage()" class="btn btn-primary btn-xs">Next</button>
            </div>
        </div>
    </superadmin-layout>
</template>

<script>
    import SuperadminLayout from '../../Layouts/SuperadminLayout.vue';
    export default {
        props: [
            'clients',
            'isFilled',
            'verified',
            'unverified',
            'total_clients'
        ],
        components: {
            SuperadminLayout
        },
        data () {
            return {
                filterName: '',
                sClients: this.clients
            }
        },
        watch: {
            flash: function (value) {
                toastr.info(value.message)
            }
        },
        methods: {
            searchByEmail () {
                let formData = new FormData();
                formData.append('email', this.filterName);
                axios.post('/searchClientByEmail', formData)
                    .then((response) => {
                        this.sClients = response.data;
                    })
                    .catch(error => {
                        toastr.error('Something went wrong.');
                    })
            },
            prevPage() {
                this.$inertia.visit(this.clients.prev_page_url);
            },
            nextPage() {
                this.$inertia.visit(this.clients.next_page_url);
            },
            verifyNow(data)
            {
                this.$inertia.post(`/setToVerified/${data.id}`, {}, {
                    onBefore: () => confirm('Verify this account?'),
                    onSuccess: () => {
                        toastr.info('Account Verified.');
                    }
                })
            },
            setToFilled (data)
            {
                this.$inertia.post(`/setToFilled/${data.id}`, {}, {
                    onBefore: () => confirm(`Filled this ${data.email} account?`),
                    onSuccess: () => {
                        toastr.info(`${data.email} is filled.`);
                    }
                })
            },
            setToUnfilled (data)
            {
                this.$inertia.post(`/setToUnfilled/${data.id}`, {}, {
                    onBefore: () => confirm(`Unfilled this ${data.email} account?`),
                    onSuccess: () => {
                        toastr.info(`${data.email} is unfilled.`);
                    }
                })
            },
            viewClientDetails (userId) {
                this.$inertia.visit(`/sa/client/${userId}`);
            },
            deleteClientDetails (userId) {
                this.$inertia.delete(`/deleteClientDetails/${userId}`, {
                    onBefore: () => confirm("Are you sure to delete this record?"),
                    onSuccess: () => {
                        tostr.info('Client details deleted.');
                    }
                });
            },
            resetToDefaultPassword (id) {
                this.$inertia.post('/setToDefaultPassword', { user_id: id}, {
                    onBefore: () => confirm('Set password to default?'),
                    onSuccess: () => {
                        toastr.info('Password set to p@ssw0rd');
                    }
                })
            }
        }
    }
</script>
