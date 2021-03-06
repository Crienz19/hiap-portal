
<template>
    <moderator-layout>
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="m-0 card-title flex-grow-1">Enrollees</h5>
                <div class="input-group input-group-sm" style="width: 300px">
                    <input type="text" class="form-control" v-model="filterName" placeholder="Search by first name or last name">
                    <span class="input-group-append">
                        <button @click="searchClientByLastName" class="btn btn-info btn-flat">
                            <span class="fas fa-search"></span>
                        </button>
                    </span>
                </div>
                <button class="mx-1 btn btn-success btn-sm btn-flat" @click="openExport()">Export</button>
                <download-excel 
                    id="export-btn"
                    class="mx-1 btn btn-success btn-sm btn-flat d-none"
                    :data="forExport"
                    :fields="fields"
                    name="HIAP Export"
                    title="HIAP Clients"
                >Export</download-excel>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr class="text-xs ">
                            <th class="text-left">First Name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                            <th>Contact Number</th>
                            <th>School/Organization</th>
                            <th>Program</th>
                            <th>Enrolled Course(s)</th>
                            <th>E-mail Address</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody v-if="clients.data.length > 0">
                        <tr class="text-xs " v-for="client in clients.data" :key="client.id">
                            <td class="text-left">{{ client.first_name }}</td>
                            <td>{{ client.middle_name}}</td>
                            <td>{{ client.last_name }}</td>
                            <td>{{ client.contact_no }}</td>
                            <td>{{ client.school ? client.school.name : '' }}</td>
                            <td>{{ client.online_program ? client.online_program.name : '' }}</td>
                            <td>{{ (client.user_program.length == 1) ? client.user_program[0]['program'].name : client.user_program.length + ' Courses' }}</td>
                            <td>{{ client.user.email }}</td>
                            <td class="text-center">
                                <button @click="viewClientDetails(client.user_id)" class="btn btn-success btn-xs btn-flat">View</button>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr class="text-xs text-center">
                            <td colspan="8">No Client Registered</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer p-2">
                <button :disabled="!clients.prev_page_url" @click="prevPage()" class="btn btn-primary btn-xs">Prev</button>
                <button :disabled="!clients.next_page_url" @click="nextPage()" class="btn btn-primary btn-xs">Next</button>
            </div>
        </div>
        <div class="modal fade show" id="modal-export" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="overlay d-flex justify-content-center align-items-center" v-if="isExportLoading">
                        <i class="fas fa-2x fa-sync fa-spin"></i>
                    </div>
                    <div class="modal-header">
                        <h5 class="modal-title">Export</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-6">
                                <label for="start-date">From</label>
                                <input v-model="form.from" type="date" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-6">
                                <label for="end-date">To</label>
                                <input v-model="form.to" type="date" class="form-control form-control-sm">
                            </div>
                            <div class="form-group col-12">
                                <label for="school">School</label>
                                <select v-model="form.school_id" class="form-control form-control-sm">
                                    <option value="" selected>All</option>
                                    <option v-for="school in schools" :key="school.id" :value="school.id">{{ school.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button @click="filterClients" type="button" class="btn btn-primary btn-sm btn-flat btn-block">Filter</button>
                        <download-excel v-if="forExport.length > 0"
                            id="export-btn"
                            class="mx-1 btn btn-success btn-sm btn-flat btn-block"
                            :data="forExport"
                            :fields="fields"
                            name="HIAP Export"
                            title="HIAP Clients"
                        >Export</download-excel>
                    </div>
                </div>
            <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
    </moderator-layout>
</template>

<script>
    import ModeratorLayout from '../../Layouts/ModeratorLayout.vue';
    export default {
        props: ['clients', 'schools'],
        components: {
            ModeratorLayout
        },
        data () {
            return {
                form: {
                    from: '',
                    to: '',
                    school_id: ''
                },
                isExportLoading: false,
                forExport: [],
                filterName: '',
                fields: {
                    "Date Registered": "client.created_at",
                    "First Name": "client.first_name",
                    "Middle Name": "client.middle_name",
                    "Last Name": "client.last_name",
                    "E-mail Address": "client.user.email",
                    "Alternate Email": "client.alternate_email",
                    "FB Link": "client.fb_link",
                    "Contact No.": "client.contact_no",
                    "School": "client.school.name",
                    "Year Level": "client.school_year",
                    "Course": "client.course",
                    "Program": "client.online_program.name",
                    "Program Track": 'program.description',
                    "Required Hours": 'hours_needed',
                    "Start Date": 'start_date',
                    "End Date": 'end_date',
                    "Program Status": 'application_status',
                    "Remarks": "''"
                }
            }
        },
        methods: {
            viewClientDetails (userId) {
                this.$inertia.visit(`/md/client/${userId}`);
            },
            deleteClientDetails (userId) {
                this.$inertia.delete(`/deleteClientDetails/${userId}`);
            },
            prevPage() {
                this.$inertia.visit(this.clients.prev_page_url);
            },
            nextPage() {
                this.$inertia.visit(this.clients.next_page_url);
            },
            searchClientByLastName() {
                if (this.filterName !== '') {
                    let formData = new FormData();
                    formData.append('last_name', this.filterName);
                    axios.post('/searchStudentByLastName', formData)
                        .then((response) => {
                            this.clients = response.data;
                        })
                } else {
                    toastr.error('Cannot search empty field.');
                }
            },
            openExport () {
                $('#modal-export').modal('show');
            },
            filterClients () {
                this.isExportLoading = true;
                axios.post('/filterClients', this.form)
                    .then((response) => {
                        this.forExport = response.data;
                        this.isExportLoading = false;
                    })
            }
        }
    }
</script>
