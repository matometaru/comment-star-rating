import Vue from 'vue';
import App from './App.vue';
import router from './router';
import store from './store';
import './plugins/vuetify'
import 'vuetify/dist/vuetify.min.css'
import sanitizeHTML from 'sanitize-html'
Vue.prototype.$sanitize = sanitizeHTML

Vue.config.productionTip = false;

new Vue({
  router,
  store,
  render: h => h(App),
}).$mount('#app');
