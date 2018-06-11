<template>
    <div id="listing-page">
        <div class="row" style="margin-bottom: 10px;">
            <div class="col-md-12">
                <form @submit.prevent="search">
                    <div class="input-group">
                        <input type="text" class="form-control" v-model="searchTerm" name="searchTerm" placeholder="Search for...">
                        <span class="input-group-btn">
                            <input type="submit" value="Go" class="btn btn-default"/>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <span v-if="parent">
                            <a @click.prevent="loadFolder(null, true)">Home</a>

                            <span v-for="breadcrumb in breadcrumbs">
                                /
                                <a @click.prevent="loadFolder(breadcrumb.id, true)">
                                    {{ breadcrumb.name }}
                                </a>
                            </span>

                            / {{ parent.name }}
                        </span>
                        <span v-else="!parent">
                            Home
                        </span>

                        <div v-show="!selectedBookmarks.length" class="top-button-group">
                            <button type="button" class="btn btn-success btn-sm pull-right" @click="emitEvent('showCreateBookmarkForm')" style="margin-top: -5px;">
                                Create Bookmark
                            </button>
                            <button type="button" class="btn btn-primary btn-sm pull-right" @click="emitEvent('showCreateFolderForm')" style="margin-top: -5px; margin-right: 5px;">
                                Create Folder
                            </button>
                        </div>

                        <div v-show="selectedBookmarks.length" class="top-button-group">
                            <span>{{ selectedBookmarks.length }} items selected.</span>

                            <button type="button" class="btn btn-primary btn-sm pull-right" @click="deleteSelected" style="margin-top: -5px; margin-right: 5px;">
                                Delete
                            </button>

                            <button v-show="!movingMode" type="button" class="btn btn-warning btn-sm pull-right" @click="enableMovingMode" style="margin-top: -5px; margin-right: 5px;">
                                Move
                            </button>

                            <button v-show="movingMode" type="button" class="btn btn-success btn-sm pull-right" @click="moveSelected(null)" style="margin-top: -5px; margin-right: 5px;">
                                Move to Home
                            </button>

                            <button type="button" class="btn btn-default btn-sm pull-right" @click="cancelSelection" style="margin-top: -5px; margin-right: 5px;">
                                Cancel
                            </button>
                        </div>
                    </div>

                    <div class="panel-body">
                        <ul class="folder-list" v-if="parent || folders.length">
                            <li v-if="parent">
                                <a @click.prevent="loadFolder(parent.parent_id, true)">
                                    <span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>
                                    &nbsp;
                                    ..
                                </a>
                            </li>

                            <li v-for="folder in folders">
                                <input type="checkbox" v-model="selectedBookmarks" :value="folder.id"/>

                                <a @click.prevent="loadFolder(folder.id, true)">
                                    <span class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>
                                    &nbsp;
                                    {{ folder.name }}
                                </a>
                                <a @click.prevent="emitEvent('showEditFolderForm', folder)">[edit]</a>
                                <a @click.prevent="deleteFolder(folder)">[x]</a>
                            </li>
                        </ul>

                        <ul class="bookmark-list" v-if="bookmarks.length">
                            <li v-for="bookmark in bookmarks">
                                <input type="checkbox" v-model="selectedBookmarks" :value="bookmark.id"/>

                                <a :href="bookmark.url" target="_blank">
                                    <img :src="bookmark.icon" style="height: 16px; width: 16px; margin-right: 3px;"/>

                                    {{ bookmark.name }}
                                </a>
                                <a @click.prevent="emitEvent('showEditBookmarkForm', bookmark)">[edit]</a>
                                <a @click.prevent="deleteBookmark(bookmark)">[x]</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <create-folder-form :parent="parent" v-on:created="createFolder"></create-folder-form>
        <create-bookmark-form :parent="parent" v-on:created="createBookmark"></create-bookmark-form>
        <edit-folder-form v-on:updated="updateFolder"></edit-folder-form>
        <edit-bookmark-form v-on:updated="updateBookmark"></edit-bookmark-form>
    </div>
</template>

<script>
    export default {
        data: function () {
            return {
                parent: null,
                breadcrumbs: [],
                folders: [],
                bookmarks: [],
                searchTerm: '',
                editFolderModel: {},
                editBookmarkModel: {},
                selectedBookmarks: [],
                movingMode: false
            }
        },

        mounted() {
            window.eventManager.on('clickedFolder', this.clickedFolder);

            this.loadData();
            window.sideMenu.initialize(); // TODO: ar trebui sa fie doar un event de folders changed
        },

        methods: {
            loadData() {
                this.loadFolder();
            },

            emitEvent(eventName, data) {
                window.em.$emit(eventName, data);
            },

            clickedFolder(id) {
                if (this.movingMode) {
                    this.moveSelected(id);
                } else {
                    this.loadFolder(id);
                }
            },

            loadFolder(id, unselectSideMenu) {
                var callback = function(response) {
                    this.folders     = response.folders;
                    this.bookmarks   = response.bookmarks;
                    this.parent      = response.parent;
                    this.breadcrumbs = response.breadcrumbs;
                }.bind(this);

                if (id != null) {
                    window.persistence.getFolder(id, callback);
                } else {
                    window.persistence.getRootFolder(callback);
                }

                if (unselectSideMenu) {
                    window.sideMenu.unselectAll();
                }
            },

            search() {
                // inclusiv daca e un spatiu gol
                if (this.searchTerm === '') {
                    this.loadFolder(null, true);
                    return;
                }

                window.persistence.searchBookmarks(this.searchTerm, function(response) {
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
                    window.sideMenu.unselectAll();
                }.bind(this));
            },

            deleteFolder(folder) {
                if (!confirm("Are you sure you want to delete this folder?")) {
                    return;
                }

                window.persistence.deleteFolder(folder.id, function() {
                    window.sideMenu.initialize();
                }.bind(this));

                var index = this.folders.indexOf(folder);
                this.folders.splice(index, 1)
            },

            deleteBookmark(bookmark) {
                if (!confirm("Are you sure you want to delete this bookmark?")) {
                    return;
                }

                window.persistence.deleteBookmark(bookmark.id);

                var index = this.bookmarks.indexOf(bookmark);
                this.bookmarks.splice(index, 1)
            },

            deleteSelected() {
                if (!confirm("Are you sure you want to delete these bookmarks?")) {
                    return;
                }

                window.persistence.deleteBookmarks({ids: this.selectedBookmarks}, function() {
                    window.sideMenu.initialize();
                });

                this.bookmarks.forEach(function(bookmark, index) {
                    if (this.selectedBookmarks.indexOf(bookmark.id) >= 0) {
                        this.bookmarks.splice(index, 1);
                    }
                }.bind(this));

                this.folders.forEach(function(folder, index) {
                    if (this.selectedBookmarks.indexOf(folder.id) >= 0) {
                        this.folders.splice(index, 1);
                    }
                }.bind(this));

                this.selectedBookmarks = [];
            },

            enableMovingMode() {
                this.movingMode = true;
            },

            moveSelected(folderId) {
                var data = {
                    parentId: folderId,
                    ids: this.selectedBookmarks
                };

                this.selectedBookmarks = [];
                this.movingMode = false;

                window.persistence.moveBookmarks(data, function() {
                    var currentFolderId = this.parent ? this.parent.id : null;
                    this.loadFolder(currentFolderId);
                    window.sideMenu.initialize();
                }.bind(this));
            },

            cancelSelection() {
                this.selectedBookmarks = [];
                this.movingMode = false;
            },

            createFolder(folder) {
                this.folders.push(folder);

                window.sideMenu.initialize();
            },

            createBookmark(bookmark) {
                this.bookmarks.push(bookmark);
            },

            updateFolder(folder) {
                console.log(this.folders);

                for (var i = 0, length = this.folders.length; i < length; i++) {
                    if (this.folders[i].id == folder.id) {
                        this.folders[i].name = folder.name;
                    }
                }

                window.sideMenu.initialize();
            },

            updateBookmark(bookmark) {
                for (var i = 0, length = this.bookmarks.length; i < length; i++) {
                    if (this.bookmarks[i].id == bookmark.id) {
                        this.bookmarks[i].name = bookmark.name;
                        this.bookmarks[i].url = bookmark.url;
                    }
                }
            }
        }
    }
</script>
