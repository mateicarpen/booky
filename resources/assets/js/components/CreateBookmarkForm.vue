<template>
    <div class="modal-form">
        <div class="modal fade" id="create-bookmark-modal" tabindex="-1" role="dialog" aria-labelledby="create-bookmark-modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Create Bookmark</h4>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Name:</label>
                                <input type="text" v-model="bookmark.name" class="form-control" required/>
                            </div>

                            <div class="form-group">
                                <label>Url:</label>
                                <input type="text" v-model="bookmark.url" class="form-control" required/>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <input type="submit" value="Create Bookmark" class="btn btn-primary"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                modalElement: '#create-bookmark-modal',
                bookmark: {}
            };
        },

        props: [
            'parent'
        ],

        created() {
            window.eventManager.$on('showCreateBookmarkForm', this.showForm);
        },

        methods: {
            showForm() {
                $(this.modalElement).modal('show');
            },

            submit() {
                this.bookmark['parent_id'] = (this.parent !== null) ? this.parent.id : null;

                window.persistence.createBookmark(this.bookmark, function(bookmark) {
                    this.$emit('created', bookmark);

                    this.bookmark = {};
                    $(this.modalElement).modal('hide');
                }.bind(this));
            }
        }
    }
</script>