<template>
    <div class="wrapper">
        <div class="cert-dl-header">
            <img src="/logo2.png" alt="hiap logo">
            <span>HIAP INC. CERTIFICATE OF COMPLETION</span>
            <i>For optimal experience use <b>Google Chrome</b>.</i>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Search</h6>
                <div class="card-tools">
                    <div class="input-group input-group-sm">
                        <input @keypress.enter="searchByEmail()" type="text" class="form-control" v-model="search"  placeholder="Search by Cert ID">
                        <span class="input-group-append">
                            <button @click="searchByEmail()" class="btn btn-info btn-flat">
                                <span class="fas fa-search"></span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0" style="height: 30vh; overflow:auto;">
                <table class="table table-striped h-100">
                    <thead>
                        <tr class="text-center">
                            <th class="text-left">Name</th>
                            <th>Course</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody v-if="certs.length > 0">
                        <tr v-for="cert in certs" :key="cert.id" class="text-center">
                            <td class="text-left">{{ cert.full_name }}</td>
                            <td>{{ cert.course }}</td>
                            <td>{{ cert.created_at }}</td>
                            <td>
                                <button @click="downloadFile(cert.id)" class="btn btn-primary btn-xs">Download</button>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="4" class="text-center" id="tb-msg">No record to display</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data () {
            return {
                search: '',
                certs: []
            }
        },
        mounted () {
            document.title = 'HIAP Inc. - Lobster Certificate of Completion';
        },
        methods: {
            searchByEmail () {
                axios.post('/getSearchedLobsterCertificate', { search: this.search})
                    .then(response => {
                        this.certs = response.data;
                        document.getElementById('tb-msg').innerText = 'No Cert Found';
                    })
            },
            downloadFile(id) {
                var a = document.createElement('a');
                a.href = `/lobsterClientCertDownload/${id}`;
                a.setAttribute('download', '');
                a.click();

                window.location.replace('/viewCoCDownloadingPage');
            }
        }
    }
</script>

<style lang="scss" scoped>
    .wrapper {
        height: 100vh;
        width: 100%;
        background: url('/assets/img/lobster_cert_dl_bg.jpg');
        background-repeat: no-repeat;
        background-size: cover;
        background-position: bottom;

        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;

        .cert-dl-header {
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            img {
                width: 140px;
                margin: 15px 0;
            }

            span {
                font-family: 'Montserrat';
                font-size: 25px;
                font-weight: bold;
                text-align: center;
            }

            i {
                margin: 1em;
            }
        }

        .cert-dl-footer {
            background: rgba(240, 240, 240, 0.75);
            font-family: 'Montserrat';
            font-size: 17px;
            font-weight: bold;

            text-align: center;
            display: flex;
            flex-direction: column;
            margin: 30px;
            padding: 20px 35px;
            border-radius: 10px;
            span {
                margin: 5px 0
            }
        }

        @media only screen and (min-width: 992px) {
            .card {
                width: 700px;
            }
        }
    }
</style>