
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import Persistence from './persistence';
import SideMenu from './sideMenu';

window.eventManager = new Vue({}); // event manager
window.persistence = new Persistence(window.apiToken);
window.sideMenu = new SideMenu(persistence, eventManager);

Vue.component('listing-page', require('./components/ListingPage.vue'));
Vue.component('search-form', require('./components/SearchForm.vue'));
Vue.component('create-folder-form', require('./components/CreateFolderForm.vue'));
Vue.component('create-bookmark-form', require('./components/CreateBookmarkForm.vue'));
Vue.component('edit-folder-form', require('./components/EditFolderForm.vue'));
Vue.component('edit-bookmark-form', require('./components/EditBookmarkForm.vue'));

var vue = new Vue({
    el: '#app'
});