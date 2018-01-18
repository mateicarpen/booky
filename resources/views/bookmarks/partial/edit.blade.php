<div class="modal fade" id="edit-bookmark-modal" tabindex="-1" role="dialog" aria-labelledby="edit-bookmark-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Create Bookmark</h4>
            </div>

            <form @submit.prevent="editBookmark">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" v-model="editBookmarkModel.name" class="form-control" required/>
                    </div>

                    <div class="form-group">
                        <label>Url:</label>
                        <input type="text" v-model="editBookmarkModel.url" class="form-control" required/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <input type="submit" value="Save" class="btn btn-primary"/>
                </div>
            </form>
        </div>
    </div>
</div>