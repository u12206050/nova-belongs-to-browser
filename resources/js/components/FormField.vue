<template>
    <default-field :field="field" :errors="errors">
        <template slot="field">
            <loading-view :loading="loading" class="px-4 bg-white rounded-lg shadow-lg overflow-hidden">
                <template v-for="item in items">
                    <div :key="item.id" class="p-2">
                        <div class="border border-50 rounded">
                            <img v-if="item.image" :title="item.title" :src="item.image" class="thumbsize" :style="sizeStyle">
                            <p v-else class="px-2 font-semibold">{{item.title}}</p>
                        </div>
                    </div>
                </template>

                <template v-if="! isReadonly">
                    <button v-if="items.length" @click.prevent="clear"
                        class="m-4 form-file-btn btn btn-default btn-danger select-none">Clear</button>
                    <button
                        @click.prevent="openBrowser = true"
                        class="m-4 form-file-btn btn btn-default btn-primary select-none">{{ items.length ? 'Change' : 'Select' }}</button>
                    <template v-if="openBrowser">
                        <modal @modal-close="openBrowser = false" class="max-h-screen">
                            <Browser
                                :resource="field.resource"
                                :filter="field.filter"
                                :title="field.title"
                                :image="field.image"
                                :orderby="field.orderby"
                                :direction="field.direction"
                                :group="field.group"
                                :ck="field.ck"
                                :current="value"
                                @close="openBrowser = false"
                                @select="onSelect"
                                @new="createNew"
                            />
                        </modal>
                    </template>
                    <template v-else-if="openCreator">
                        <modal @modal-close="openCreator = false" class="max-h-screen">
                            <Creator :resourceName="field.resource"
                                :filter="field.filter"
                                @close="openCreator = false"
                                @created="onCreate"
                            />
                        </modal>
                    </template>
                </template>
            </loading-view>
        </template>
    </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import Browser from './Browser.vue'
import Creator from './Creator.vue'

export default {
    mixins: [FormField, HandlesValidationErrors],
    props: ['resourceName', 'resourceId', 'field'],
    data () {
        return {
            loading: false,
            openBrowser: false,
            openCreator: false,
            items: []
        }
    },
    components: {
        Browser, Creator
    },
    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue () {
            this.value = this.field.value || ''

            if (this.value) {
                this.loading = true
                const {resource, title, image, orderby, direction, ck} = this.field
                Nova.request().post(`/day4/belongs-to-browser/fetch/${resource}`, {
                    title, image, orderby, direction, ck,
                    ids: this.value
                }).then(response => {
                    this.items = response.data
                    this.loading = false
                }).catch(() => {
                    console.warn('Failed loading resource')
                    this.loading = false
                })
            }
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            if (! this.isReadonly) {
                formData.append(this.field.attribute, this.value || '')
            }
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            if (! this.isReadonly) {
                this.value = value
            }
        },

        clear () {
            this.items = []
            this.$emit('input', this.value = null)
        },

        onSelect(selected) {
            const ids = selected && Object.keys(selected)
            const items = selected && Object.values(selected)
            if (Array.isArray(items) && items.length) {
                this.items = items
                this.$emit('input', this.value = ids.join(','))
            } else this.$emit('input', '')
            this.openBrowser = false
        },
        onCreate(item) {
            this.$emit('input', this.value = item.id)
            this.items = [{
                id: item.id,
                title: item[this.field.title],
                image: item[this.field.image]
            }]
            this.openBrowser = false
            this.openCreator = false
        },
        createNew () {
            this.openBrowser = false
            this.openCreator = true
        }
    }
}
</script>

<style scoped>
img.thumbsize {
  display: block;
  margin: 0 auto;
  object-fit: contain;
  object-position: center;
}
</style>