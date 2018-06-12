<template>
    <div class="row search-form">
        <div class="col-md-12">
            <form @submit.prevent="submit">
                <div class="input-group">
                    <input type="text" class="form-control" v-model="searchTerm" name="searchTerm" placeholder="Search for...">
                    <span class="input-group-btn">
                        <input type="submit" value="Go" class="btn btn-default"/>
                    </span>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                searchTerm: ''
            }
        },

        methods: {
            submit() {
                this.$emit('submit', this.searchTerm);
            }
        }
    }
</script>