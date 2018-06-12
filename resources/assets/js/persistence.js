export default class Persistence {
    constructor(apiToken) {
        this.apiToken = apiToken;
        this.apiPrefix = '/api/v1';
    }

    getFolderTree(callback) {
        this.makeRequest('GET', '/bookmarks/folderTree', callback);
    }

    getRootFolder(callback) {
        this.makeRequest('GET', '/bookmarks', callback);
    }

    getFolder(id, callback) {
        this.makeRequest('GET', '/bookmarks/' + id, callback);
    }

    createFolder(data, callback) {
        data['type_id'] = 2; // TODO: const

        this.makeRequest('POST', '/bookmarks', callback, data);
    }

    editFolder(id, data, callback) {
        this.makeRequest('PUT', '/bookmarks/'+ id, callback, data);
    }

    deleteFolder(id, callback) {
        this.makeRequest('DELETE', '/bookmarks/' + id, callback);
    }

    searchBookmarks(searchTerm, callback) {
        this.makeRequest('GET', '/bookmarks/search/' + searchTerm, callback);
    }

    createBookmark(data, callback) {
        data['type_id'] = 1; // TODO: const

        this.makeRequest('POST', '/bookmarks', callback, data);
    }

    editBookmark(id, data, callback) {
        this.makeRequest('PUT', '/bookmarks/' + id, callback, data);
    }

    deleteBookmark(id, callback) {
        this.makeRequest('DELETE', '/bookmarks/' + id, callback);
    }

    deleteBookmarks(data, callback) {
        this.makeRequest('POST', '/bookmarks/bulkDelete', callback, data);
    }

    moveBookmarks(data, callback) {
        this.makeRequest('POST', '/bookmarks/bulkMove', callback, data);
    }

    makeRequest(method, url, callback, data) {
        $.ajax({
            url: this.apiPrefix + url,
            data: data,
            type: method,
            headers: {'Authorization': "Bearer " + this.apiToken},
            success: callback
        });
    }
};