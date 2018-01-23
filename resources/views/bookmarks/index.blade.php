@extends('layouts.app')

@section('content')

<div id="bookmark-index-page">
    <div class="container spark-screen">
        <div class="row">
            <div class="col-md-3 treeview"></div>
            <div class="col-md-9">
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
                                            @{{ breadcrumb.name }}
                                        </a>
                                    </span>

                                    / @{{ parent.name }}
                                </span>
                                <span v-else="!parent">
                                    Home
                                </span>

                                <div v-show="!selectedBookmarks.length" class="top-button-group">
                                    <button type="button" class="btn btn-success btn-sm pull-right" @click="showCreateBookmarkForm" style="margin-top: -5px;">
                                        Create Bookmark
                                    </button>

                                    <button type="button" class="btn btn-primary btn-sm pull-right" @click="showCreateFolderForm" style="margin-top: -5px; margin-right: 5px;">
                                        Create Folder
                                    </button>
                                </div>

                                <div v-show="selectedBookmarks.length" class="top-button-group">
                                    <span>@{{ selectedBookmarks.length }} items selected.</span>

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
                                            @{{ folder.name }}
                                        </a>
                                        <a @click.prevent="showEditFolderForm(folder)">[edit]</a>
                                        <a @click.prevent="deleteFolder(folder)">[x]</a>
                                    </li>
                                </ul>

                                <ul class="bookmark-list" v-if="bookmarks.length">
                                    <li v-for="bookmark in bookmarks">
                                        <input type="checkbox" v-model="selectedBookmarks" :value="bookmark.id"/>

                                        <a :href="bookmark.url" target="_blank">
                                            <img :src="bookmark.icon" style="height: 16px; width: 16px; margin-right: 3px;"/>

                                            @{{ bookmark.name }}
                                        </a>
                                        <a @click.prevent="showEditBookmarkForm(bookmark)">[edit]</a>
                                        <a @click.prevent="deleteBookmark(bookmark)">[x]</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('bookmarks.partial.create_folder')
    @include('bookmarks.partial.edit_folder')
    @include('bookmarks.partial.create')
    @include('bookmarks.partial.edit')

</div>

<script>
    window.apiToken = '{{ $currentUser->api_token }}';
</script>

@stop