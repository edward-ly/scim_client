import Vue from 'vue'
import App from './components/AdminSettings.vue'
Vue.mixin({ methods: { t, n } })

const View = Vue.extend(App)
new View().$mount('#scim-client-admin-settings')