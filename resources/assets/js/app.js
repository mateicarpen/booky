
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


var persistence = {
    apiPrefix: '/api/v1',

    getFolderTree: function(callback) {
        this.makeRequest('GET', '/bookmarks/folderTree', callback);
    },

    getRootFolder: function(callback) {
        this.makeRequest('GET', '/bookmarks', callback);
    },

    getFolder: function(id, callback) {
        this.makeRequest('GET', '/bookmarks/' + id, callback);
    },

    createFolder: function(data, callback) {
        this.makeRequest('POST', '/bookmarks', callback, data);
    },

    editFolder: function(id, data, callback) {
        this.makeRequest('PUT', '/bookmarks/'+ id, callback, data);
    },

    deleteFolder: function(id, callback) {
        this.makeRequest('DELETE', '/bookmarks/' + id, callback);
    },

    searchBookmarks: function(searchTerm, callback) {
        this.makeRequest('GET', '/bookmarks/search/' + searchTerm, callback);
    },

    createBookmark: function(data, callback) {
        this.makeRequest('POST', '/bookmarks', callback, data);
    },

    editBookmark: function(id, data, callback) {
        this.makeRequest('PUT', '/bookmarks/' + id, callback, data);
    },

    deleteBookmark: function(id, callback) {
        this.makeRequest('DELETE', '/bookmarks/' + id, callback);
    },

    deleteBookmarks: function(data, callback) {
        this.makeRequest('POST', '/bookmarks/bulkDelete', callback, data);
    },

    moveBookmarks: function(data, callback) {
        this.makeRequest('POST', '/bookmarks/bulkMove', callback, data);
    },

    makeRequest: function(method, url, callback, data) {
        $.ajax({
            url: this.apiPrefix + url,
            data: data,
            type: method,
            headers: {'Authorization': "Bearer " + window.apiToken},
            success: callback
        });
    }
};

var sideMenu = {
    element: '.treeview',

    initialize: function() {
        persistence.getFolderTree(function (data) {
            data = formatData(data);

            $(this.element).treeview({
                data: data,
                levels: 1,
                onNodeSelected: function(event, node) {
                    vue.clickedFolder(node.folderId);
                },
            });
        }.bind(this));
    },

    unselectAll: function() {
        var selectedNodes = $(this.element).treeview('getSelected');

        $.each(selectedNodes, function(key, node) {
            $(this.element).treeview('unselectNode', node);
        }.bind(this));
    }
};

var formatData = function(data) {
    for (var i = 0, length = data.length; i < length; i++) {
        data[i].text += " (" + data[i].bookmarkCount + ")";

        if (data[i].nodes) {
            data[i].nodes = formatData(data[i].nodes);
        }
    }

    return data;
};

var vue = new Vue({
    el: '#bookmark-index-page',

    data: {
        parent: null,
        breadcrumbs: [],
        folders: [],
        bookmarks: [],
        searchTerm: '',
        createFolderModel: {},
        editFolderModel: {},
        createBookmarkModel: {},
        editBookmarkModel: {},
        selectedBookmarks: [],
        movingMode: false,

        createFolderFormElement: '#create-folder-modal',
        editFolderFormElement: '#edit-folder-modal',
        createBookmarkFormElement: '#create-bookmark-modal',
        editBookmarkFormElement: '#edit-bookmark-modal'
    },

    mounted: function() {
        this.loadData();
        sideMenu.initialize();
    },

    methods: {
        loadData: function() {
            this.loadFolder();
        },

        clickedFolder: function(id) {
            if (this.movingMode) {
                this.moveSelected(id);
            } else {
                this.loadFolder(id);
            }
        },

        loadFolder: function(id, unselectSideMenu) {
            var callback = function(response) {
                this.folders     = response.folders;
                this.bookmarks   = response.bookmarks;
                this.parent      = response.parent;
                this.breadcrumbs = response.breadcrumbs;
            }.bind(this);

            if (id != null) {
                persistence.getFolder(id, callback);
            } else {
                persistence.getRootFolder(callback);
            }

            if (unselectSideMenu) {
                sideMenu.unselectAll();
            }
        },

        search: function() {
            // inclusiv daca e un spatiu gol
            if (this.searchTerm === '') {
                this.loadFolder(null, true);
                return;
            }

            persistence.searchBookmarks(this.searchTerm, function(response) {
                this.bookmarks   = [];
                this.folders     = [];
                this.breadcrumbs = [];
                this.parent      = { name: "Search results for '" + this.searchTerm + "'" };

                for (var i = 0; i < response.length; i++) {
                    if (response[i].type_id == 2) { // TODO: const
                        this.folders.push(response[i]);
                    } else {
                        this.bookmarks.push(response[i]);
                    }
                }

                this.searchTerm = '';
                sideMenu.unselectAll();
            }.bind(this));
        },

        showCreateFolderForm: function() {
            $(this.createFolderFormElement).modal('show');
        },

        showEditFolderForm: function(folder) {
            this.editFolderModel = folder;
            $(this.editFolderFormElement).modal('show');
        },

        createFolder: function() {
            this.createFolderModel['parent_id'] = (this.parent !== null) ? this.parent.id : null;
            this.createFolderModel['type_id'] = 2; // TODO: const

            persistence.createFolder(this.createFolderModel, function(folder) {
                this.folders.push(folder);

                this.createFolderModel = {};
                $(this.createFolderFormElement).modal('hide');
                sideMenu.initialize();
            }.bind(this));
        },

        editFolder: function() {
            persistence.editFolder(this.editFolderModel.id, this.editFolderModel, function(folder) {
                for (var i = 0, length = this.folders.length; i < length; i++) {
                    if (this.folders[i].id == folder.id) {
                        this.folders[i] = folder;
                    }
                }

                this.editFolderModel = {};
                $(this.editFolderFormElement).modal('hide');
                sideMenu.initialize();
            }.bind(this));
        },

        deleteFolder: function(folder) {
            if (!confirm("Are you sure you want to delete this folder?")) {
                return;
            }

            persistence.deleteFolder(folder.id, function() {
                sideMenu.initialize();
            }.bind(this));

            this.folders.$remove(folder);
        },

        showCreateBookmarkForm: function() {
            $(this.createBookmarkFormElement).modal('show');
        },

        showEditBookmarkForm: function(bookmark) {
            this.editBookmarkModel = bookmark;
            $(this.editBookmarkFormElement).modal('show');
        },

        createBookmark: function() {
            this.createBookmarkModel['parent_id'] = (this.parent !== null) ? this.parent.id : null;
            this.createBookmarkModel['type_id'] = 1; // TODO: const

            persistence.createBookmark(this.createBookmarkModel, function(bookmark) {
                this.bookmarks.push(bookmark);

                this.createBookmarkModel = {};
                $(this.createBookmarkFormElement).modal('hide');
            }.bind(this));
        },

        editBookmark: function() {
            persistence.editBookmark(this.editBookmarkModel.id, this.editBookmarkModel, function(bookmark) {
                for (var i = 0, length = this.bookmarks.length; i < length; i++) {
                    if (this.bookmarks[i].id == bookmark.id) {
                        this.bookmarks[i] = bookmark;
                    }
                }

                this.editBookmarkModel = {};
                $(this.editBookmarkFormElement).modal('hide');
            }.bind(this));
        },

        deleteBookmark: function(bookmark) {
            if (!confirm("Are you sure you want to delete this bookmark?")) {
                return;
            }

            persistence.deleteBookmark(bookmark.id);

            this.bookmarks.$remove(bookmark);
        },

        deleteSelected: function() {
            if (!confirm("Are you sure you want to delete these bookmarks?")) {
                return;
            }

            persistence.deleteBookmarks({ids: this.selectedBookmarks}, function() {
                sideMenu.initialize();
            });

            for (var bookmark of this.bookmarks) {
                if (this.selectedBookmarks.indexOf(bookmark.id.toString()) >= 0) {
                    this.bookmarks.$remove(bookmark);
                }
            }

            for (var folder of this.folders) {
                if (this.selectedBookmarks.indexOf(folder.id.toString()) >= 0) {
                    this.folders.$remove(folder);
                }
            }

            this.selectedBookmarks = [];
        },

        enableMovingMode: function() {
            this.movingMode = true;
        },

        moveSelected: function(folderId) {
            var data = {
                parentId: folderId,
                ids: this.selectedBookmarks
            };

            this.selectedBookmarks = [];
            this.movingMode = false;

            persistence.moveBookmarks(data, function() {
                var currentFolderId = this.parent ? this.parent.id : null;
                this.loadFolder(currentFolderId);
                sideMenu.initialize();
            }.bind(this));
        },

        cancelSelection: function() {
            this.selectedBookmarks = [];
            this.movingMode = false;
        }
    }
});