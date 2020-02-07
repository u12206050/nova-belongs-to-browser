<template>
    <panel-item :field="field">
        <template slot="value" v-if="field.value">
            <template v-for="item in items">
                <div :key="item.id" class="p-2">
                    <div class="border border-50 rounded">
                        <img v-if="item.image" :title="item.title" :src="item.image" class="thumbsize">
                        <p v-else class="px-2 font-semibold">{{item.title}}</p>
                    </div>
                </div>
            </template>
        </template>
    </panel-item>
</template>

<script>
export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],
    data() {
        return {
            loading: false,
            items: []
        }
    },
    mounted() {
        const ids = this.field.value || ''

        if (ids) {
            this.loading = true
            const {resource, title, image, orderby, direction, ck} = this.field
            Nova.request().post(`/day4/belongs-to-browser/fetch/${resource}`, {
                title, image, ck,
                ids
            }).then(response => {
                this.items = response.data
                this.loading = false
            }).catch(() => {
                this.loading = false
            })
        }
    }
}
</script>
