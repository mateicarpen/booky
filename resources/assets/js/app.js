
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

//
///**
// * Next, we will create a fresh Vue application instance and attach it to
// * the page. Then, you may begin adding components to this application
// * or customize the JavaScript scaffolding to fit your unique needs.
// */
//
//Vue.component('example-component', require('./components/ExampleComponent.vue'));
//
//const app = new Vue({
//    el: '#app'
//});

import EventManager from './eventManager';
import Persistence from './persistence';
import SideMenu from './sideMenu';

window.eventManager = new EventManager();
window.persistence = new Persistence();
window.sideMenu = new SideMenu(persistence, eventManager);

Vue.component('listing-page', require('./components/ListingPage.vue'));
Vue.component('create-folder-form', require('./components/CreateFolderForm.vue'));
Vue.component('create-bookmark-form', require('./components/CreateBookmarkForm.vue'));
Vue.component('edit-folder-form', require('./components/EditFolderForm.vue'));
Vue.component('edit-bookmark-form', require('./components/EditBookmarkForm.vue'));

window.em = new Vue({}); // event manager

var vue = new Vue({
    el: '#app'
});