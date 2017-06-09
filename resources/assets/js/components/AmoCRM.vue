<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Оставьте Ваши контакты</div>

                    <div class="panel-body">
                        <alert v-if="alert"
                               v-bind:header="alertHeader"
                               v-bind:message="alertMessage"
                               v-bind:type="alertType"
                               v-bind:dismissible="alertIsDismissible">
                        </alert>
                        <div v-if="busy" class="loading">Loading&#8230;</div>
                        <form class="form-horizontal" action="#" @submit.prevent="validateBeforeSubmit()" role="form">
                            <div class="form-group" :class="{'has-error': errors.has('name') }">
                                <label class="col-md-4 control-label" for="name_field">Ваше имя</label>
                                <div class="col-md-4">
                                    <input id="name_field" v-model="request.name" name="name" type="text" class="form-control input-md" v-validate="'required'" required="" />
                                </div>
                            </div>

                            <!-- Text input-->
                            <div class="form-group" v-bind:class="{ 'has-error': hasPhoneError }">
                                <label class="col-md-4 control-label" for="phone_field">Телефон</label>
                                <div class="col-md-4">
                                    <masked-input id="phone_field" v-model="request.phone" mask="\+\7 (111) 111-1111" type="tel" class="form-control input-md" />
                                </div>
                            </div>

                            <button type="submit" class="btn btn-default">Отправить</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Alert from './Alerts.vue'
    import MaskedInput from 'vue-masked-input'


    export default {
        components: {
            Alert,
            MaskedInput
        },
        mounted() {
            console.log('Component mounted.')
        },
        data: function() {
            return {
                request: {
                    name: '',
                    phone: ''
                },
                busy: false,
                hasPhoneError: false,
                alert: false,
                alertHeader: '',
                alertMessage: '',
                alertType: '',
                alertIsDismissible: true
            }
        },
        methods: {
            validateBeforeSubmit: function () {
                this.$validator.validateAll();
                this.hasPhoneError = this.request.phone == '';

                if (!this.errors.any() && !this.hasPhoneError) {
                    this.createRequest()
                }
            },
            createRequest: function() {
                var self = this;
                this.busy = true;

                this.$http.post('/api/request/create', this.request).then(function(response) {
                    this.alertHeader = 'Ваше обращение принято!';
                    this.alertMessage = response.data.message;
                    this.alertType = 'success';
                    this.alert = true;
                    this.busy = false;
                    this.request.name = '';
                    this.request.phone = '';

                    setTimeout(function(){
                        self.alert = false;
                    }, 10000);

                }, function(response) {
                    this.alertHeader = 'Ошибка создания обращения!';
                    this.alertMessage = response.data.error;
                    this.alert = true;
                    this.busy = false;
                });
            }
        }
    }
</script>
