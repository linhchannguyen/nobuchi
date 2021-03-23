import './bootstrap';
import Vue from 'vue';
import Vuetify from 'vuetify';

Vue.use(Vuetify);
import hello from './components/Welcome.vue'
Vue.component('hello', hello)

const app = new Vue ({
  el: '#app',
});