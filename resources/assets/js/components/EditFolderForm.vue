<template>
    <div class="modal fade" id="edit-folder-modal" tabindex="-1" role="dialog" aria-labelledby="edit-folder-modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Update Folder</h4>
                </div>

                <form @submit.prevent="editFolder">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" v-model="folder.name" class="form-control" required/>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" value="Save Folder" class="btn btn-primary"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                editFolderFormElement: '#edit-folder-modal',
                folder: {
                    name: null
                }
            };
        },

        props: [
            'parent'
        ],

        created() {
            window.eventManager.$on('showEditFolderForm', this.showForm);
        },

        methods: {
            showForm(folder) {
                this.folder = Object.assign({}, folder);
                $(this.editFolderFormElement).modal('show');
            },

            editFolder() {
                window.persistence.editFolder(this.folder.id, this.folder, function(folder) {
                    this.$emit('updated', folder);

                    this.folder = {};
                    $(this.editFolderFormElement).modal('hide');
                }.bind(this));
            }
        }
    }
</script>