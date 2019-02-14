import Vue from 'vue';
import App from './App.vue';
import './plugins/vuetify'
import 'vuetify/dist/vuetify.min.css'
import sanitizeHTML from 'sanitize-html'
Vue.prototype.$sanitize = sanitizeHTML

Vue.config.productionTip = false;

new Vue({
  render: h => h(App),
}).$mount('#app');
