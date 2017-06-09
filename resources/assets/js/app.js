require('./bootstrap');

import AmoCRM from './components/AmoCRM.vue'

Vue.component('amo-client', AmoCRM);


new Vue({
    el: 'amo-client'
});