<template>
  <loading-view :loading="loading" class="bg-40 p-2 rounded embeded-view">
    <form
      v-if="panels"
      @submit="submitViaCreateResource"
      autocomplete="off"
      ref="form"
    >
      <form-panel
        class="mb-8"
        v-for="panel in panelsWithFields"
        :panel="panel"
        :name="panel.name"
        :key="panel.name"
        :resource-name="resourceName"
        :fields="panel.fields"
        mode="form"
        :validation-errors="validationErrors"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :via-relationship="viaRelationship"
      />

      <!-- Create Button -->
      <div class="flex items-center p-2 bg-white rounded">
        <cancel-button @click.prevent="$emit('close')" />

        <progress-button
          dusk="create-button"
          type="submit"
          :disabled="isWorking"
          :processing="wasSubmittedViaCreateResource"
        >
          {{ __('Create :resource', { resource: singularName }) }}
        </progress-button>
      </div>
    </form>
  </loading-view>
</template>

<script>
import { Errors, Minimum, InteractsWithResourceInformation } from 'laravel-nova'

export default {
  mixins: [InteractsWithResourceInformation],

  props: {
    resourceName: {
      type: String,
      required: true,
    },
    viaResource: {
      default: '',
    },
    viaResourceId: {
      default: '',
    },
    viaRelationship: {
      default: '',
    },
  },

  data: () => ({
    relationResponse: null,
    loading: true,
    submittedViaCreateResource: false,
    fields: [],
    panels: [],
    validationErrors: new Errors(),
    isWorking: false,
  }),

  async created() {
    if (Nova.missingResource(this.resourceName))
      return this.$router.push({ name: '404' })

    // If this create is via a relation index, then let's grab the field
    // and use the label for that as the one we use for the title and buttons
    if (this.isRelation) {
      const { data } = await Nova.request(
        '/nova-api/' + this.viaResource + '/field/' + this.viaRelationship
      )
      this.relationResponse = data
    }

    this.getFields()
  },

  methods: {
    /**
     * Get the available fields for the resource.
     */
    async getFields() {
      this.panels = []
      this.fields = []

      const {
        data: { panels, fields },
      } = await Nova.request().get(
        `/nova-api/${this.resourceName}/creation-fields`,
        {
          params: {
            editing: true,
            editMode: 'create',
            viaResource: this.viaResource,
            viaResourceId: this.viaResourceId,
            viaRelationship: this.viaRelationship,
          },
        }
      )

      this.panels = panels
      this.fields = fields
      this.loading = false
    },

    async submitViaCreateResource(e) {
      e.preventDefault()
      this.submittedViaCreateResource = true
      await this.createResource()
    },

    /**
     * Create a new resource instance using the provided data.
     */
    async createResource() {
      this.isWorking = true

      if (this.$refs.form.reportValidity()) {
        try {
          const {
            data: { redirect, id, resource },
          } = await this.createRequest()

          Nova.success(
            this.__('The :resource was created!', {
              resource: this.resourceInformation.singularLabel.toLowerCase(),
            })
          )

          this.$emit('created', resource)
        } catch (error) {
          this.submittedViaCreateAndAddAnother = false
          this.submittedViaCreateResource = true
          this.isWorking = false

          if (error.response.status == 422) {
            this.validationErrors = new Errors(error.response.data.errors)
            Nova.error(this.__('There was a problem submitting the form.'))
          }
        }
      }

      this.submittedViaCreateAndAddAnother = false
      this.submittedViaCreateResource = true
      this.isWorking = false
    },

    /**
     * Send a create request for this resource
     */
    createRequest() {
      return Nova.request().post(
        `/nova-api/${this.resourceName}`,
        this.createResourceFormData(),
        {
          params: {
            editing: true,
            editMode: 'create',
          },
        }
      )
    },

    /**
     * Create the form data for creating the resource.
     */
    createResourceFormData() {
      return _.tap(new FormData(), formData => {
        _.each(this.fields, field => {
          field.fill(formData)
        })

        formData.append('viaResource', this.viaResource)
        formData.append('viaResourceId', this.viaResourceId)
        formData.append('viaRelationship', this.viaRelationship)
      })
    },
  },

  computed: {
    wasSubmittedViaCreateResource() {
      return this.isWorking && this.submittedViaCreateResource
    },

    panelsWithFields() {
      return _.map(this.panels, panel => {
        return {
          name: panel.name,
          fields: _.filter(this.fields, field => field.panel == panel.name),
        }
      })
    },

    singularName() {
      if (this.relationResponse) {
        return this.relationResponse.singularLabel
      }

      return this.resourceInformation.singularLabel
    },

    isRelation() {
      return Boolean(this.viaResourceId && this.viaRelationship)
    },
  },
}
</script>

<style lang="scss" scoped>
.embeded-view {
  width: 50vw;
  min-width: 480px;
  max-width: 100vw;

  .card {
    div.w-1\/5.px-8.py-6 {
      width: 20%;
      padding: 1rem .4rem;
    }
    div.py-6.px-8.w-1\/2 {
      width: 80%;
      padding: 1rem;
    }
  }
}
</style>