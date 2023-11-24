import './bootstrap';
import { createApp } from 'vue';
import { globalMixin } from '../../../../Pishgaman/src/Resources/vue/globalMixin.';
import App from './monitoring/home.vue'; 
import Vue3WordCloud from 'vue3-word-cloud';

// Create the Vue app and add the globalMixin to all components
const app = createApp(App);
app.component('VueWordCloud', Vue3WordCloud)

// Add the globalMixin to the app
app.mixin(globalMixin);

// Mount the app to the element with id "LoginApp"
app.mount("#MonitoringApp");
