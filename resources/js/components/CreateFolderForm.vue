<template>
    <div class="modal-form">
        <div class="modal" id="create-folder-modal" tabindex="-1" role="dialog" aria-labelledby="create-folder-modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Create Folder</h4>
                    </div>

                    <form @submit.prevent="submit">
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" v-model="folder.name" class="form-control" required/>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <input type="submit" value="Create Folder" class="btn btn-primary"/>
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
                modalElement: '#create-folder-modal',
                folder: {}
            };
        },

        props: [
            'parent'
        ],

        created() {
            window.eventManager.$on('showCreateFolderForm', this.showForm);
        },

        methods: {
            showForm() {
                $(this.modalElement).modal('show');
            },

            submit() {
                this.folder['parent_id'] = (this.parent !== null) ? this.parent.id : null;

                window.persistence.createFolder(this.folder, function(folder) {
                    this.$emit('created', folder);

                    this.folder = {};
                    $(this.modalElement).modal('hide');
                }.bind(this));
            }
        }
    }
</script>
