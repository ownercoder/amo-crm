window._ = require('lodash');

try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap-sass');
} catch (e) {}

window.Vue = require('vue');
Vue.use(require('vue-resource'));
Vue.use(require('vee-validate'));

Vue.http.interceptors.push((request, next) => {
    request.headers.set('X-CSRF-TOKEN', Laravel.csrfToken);
    if (Laravel.apiToken != '') {
        request.headers.set('Authorization', 'Bearer ' + Laravel.apiToken);
    }
    next();
});
