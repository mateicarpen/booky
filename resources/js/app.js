/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;

import Persistence from './persistence';
import SideMenu from './sideMenu';
import ListingPage from "./components/ListingPage";
import SearchForm from "./components/SearchForm";
import CreateFolderForm from "./components/CreateFolderForm";
import CreateBookmarkForm from "./components/CreateBookmarkForm";
import EditFolderForm from "./components/EditFolderForm";
import EditBookmarkForm from "./components/EditBookmarkForm";

window.eventManager = new Vue({}); // event manager
window.persistence = new Persistence(window.apiToken);
window.sideMenu = new SideMenu(persistence, eventManager);

Vue.component('listing-page', ListingPage);
Vue.component('search-form', SearchForm);
Vue.component('create-folder-form', CreateFolderForm);
Vue.component('create-bookmark-form', CreateBookmarkForm);
Vue.component('edit-folder-form', EditFolderForm);
Vue.component('edit-bookmark-form', EditBookmarkForm);

var vue = new Vue({
    el: '#app'
});
