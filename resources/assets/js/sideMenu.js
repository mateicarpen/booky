export default class SideMenu {
    constructor(persistence, eventManager) {
        this.element = '.treeview';
        this.persistence = persistence;
        this.eventManager = eventManager;
    }

    initialize() {
        this.persistence.getFolderTree(function (data) {
            data = this.formatData(data);

            $(this.element).treeview({
                data: data,
                levels: 1,
                onNodeSelected: function(event, node) {
                    this.eventManager.trigger('clickedFolder', node.folderId);

                    //vue.clickedFolder(node.folderId);
                }.bind(this)
            });
        }.bind(this));
    }

    unselectAll() {
        var selectedNodes = $(this.element).treeview('getSelected');

        $.each(selectedNodes, function(key, node) {
            $(this.element).treeview('unselectNode', node);
        }.bind(this));
    }

    formatData(data) {
        for (var i = 0, length = data.length; i < length; i++) {
            data[i].text += " (" + data[i].bookmarkCount + ")";

            if (data[i].nodes) {
                data[i].nodes = this.formatData(data[i].nodes);
            }
        }

        return data;
    }
};