<template>
    <auth-layout>
        <div class="register-box">
    <div class="register-logo">
        <a id="home-link" href="/" class="sr-only">Home Link</a>
    </div>

    <div class="card">
        <div class="overlay" v-if="loading">
            <i class="fas fa-spinner fa-2x fa-pulse"></i>
        </div>
        <div class="card-header border-0 text-center pt-4">
            <img src="/logo.png" alt="" style="width: 120px; cursor: pointer;" onclick="document.getElementById('home-link').click();">
        </div>
      <div class="card-body register-card-body pt-0">
        <p class="login-box-msg">Register</p>

        <form @submit.prevent="register()">
          <div class="form-group mb-3">
            <input v-model="form.email" name="email" type="email" :class="hasEmailError" placeholder="Email">
            <span class="error invalid-feedback">{{ errors.email }}</span>
          </div>
          <div class="form-group mb-3">
            <input v-model="form.password" name="password" type="password" :class="hasPasswordError" placeholder="Password">
            <span class="error invalid-feedback">{{ errors.password }}</span>
          </div>
          <div class="form-group mb-3">
            <input v-model="form.password_confirmation" name="password_confirmation" type="password" :class="hasPasswordConfirmationError" placeholder="Retype password">
            <span class="error invalid-feedback">{{ errors.password_confirmation }}</span>
          </div>
          <div class="form-group mb-3">
              <input v-model="form.name" type="text" class="form-control" placeholder="Full Name">
          </div>
          <div class="form-group mb-3">
              <input v-model="form.position" type="text" class="form-control" placeholder="Position">
          </div>
          <div class="form-group mb-3">
              <select v-model="form.school" class="form-control">
                <option value="" selected>Select School</option>
                <option v-for="school in schools" :key="school.id" :value="school.id">{{ school.name }}</option>
              </select>
          </div>
          <div class="form-group mb-3">
              <input v-model="form.contact_no" type="text" class="form-control" placeholder="Contact No.">
          </div>
          <div class="row">
            <div class="col-8">
              <!-- <div class="icheck-primary">
                <input type="checkbox" id="agreeTerms" name="terms" value="agree">
                <label for="agreeTerms">
                 I agree to the <a href="#">terms</a>
                </label>
              </div> -->
            </div>
            <!-- /.col -->
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block">Register</button>
            </div>
             <div class="col-6 text-left">
                <inertia-link href="/login" class="text-sm">Sign In an account</inertia-link>
            </div>

            <div class="col-6 text-right">
                <inertia-link href="/password/reset" class="text-sm">Forgot password?</inertia-link>
            </div>
            <!-- /.col -->
          </div>
        </form>
        <!-- <div class="social-auth-links text-center">
          <p>- OR -</p>
          <a href="#" class="btn btn-block btn-primary">
            <i class="fab fa-facebook mr-2"></i>
            Sign up using Facebook
          </a>
          <a href="#" class="btn btn-block btn-danger">
            <i class="fab fa-google-plus mr-2"></i>
            Sign up using Google+
          </a>
        </div> -->

      </div>
      <!-- /.form-box -->
    </div><!-- /.card -->
  </div>
    </auth-layout>
</template>

<script>
    import AuthLayout from '../../Layouts/AuthLayout.vue';
    export default {
        props: ['errors', 'schools'],
        components: {
            AuthLayout
        },
        data () {
            return {
                loading: false,
                form: {
                    email: '',
                    password: '',
                    password_confirmation: '',
                    name: '',
                    position: '',
                    school: '',
                    contact_no: ''
                }
            }
        },
        computed: {
            hasEmailError() {
                let errors = Object.values(this.errors);
                if (this.errors.email) {
                    return 'form-control is-invalid';
                } else {
                    return 'form-control';
                }
            },
            hasPasswordError() {
                let errors = Object.values(this.errors);
                if (this.errors.password) {
                    return 'form-control is-invalid';
                } else {
                    return 'form-control';
                }
            },
            hasPasswordConfirmationError() {
                let errors = Object.values(this.errors);
                if (this.errors.password_confirmation) {
                    return 'form-control is-invalid';
                } else {
                    return 'form-control';
                }
            },
        },
        methods: {
            register () {
              this.$inertia.post('/staff/register', this.form);
            }
        }
    }
</script>