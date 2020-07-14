<template>
  <loading-view :loading="loading" style="max-height: 90vh" class="relative embeded-view p-2 bg-40 rounded-lg shadow-lg overflow-hidden flex flex-col">
    <div class="flex-1 flex flex-col min-h-0">
      <div class="mb-2 border-b border-50 p-2 flex items-center justify-between w-full">
        <button class="btn btn-sm px-4 rounded btn-primary" @click.prevent="$emit('new')" >New</button>
        <div class="flex-1 px-2">
          <div class="bg-white rounded p-1 flex w-full">
            <input placeholder="search" class="flex-1 rounded p-1" v-model="query">
            <button class="btn-icon" @click.prevent="search">
              <icon type="search"></icon>
            </button>
          </div>
        </div>
        <button class="btn-icon font-bold text-xl" @click.prevent="$emit('close')">⨉</button>
      </div>
      <div class="flex-1 w-full bg-white rounded overflow-y-auto min-h-0">
        <div class="flex flex-wrap justify-center">
          <template v-for="item in results">
            <div :key="item.id" class="p-2" @click.prevent="toggle(item)">
              <div class="rounded" :style="width" :class="selected[item.id] ? 'shadow border-2 border-primary' : 'border border-50'">
                <img :src="item.image" class="thumbsize w-full" :style="height">
                <p class="break-words border-t border-40 pt-1 px-2 text-center m-2 block break-normal text-sm font-semibold">{{item.title}}</p>
              </div>
            </div>
          </template>
        </div>
        <button class="block my-4 mx-auto" @click="loadNext">Load More</button>
      </div>
      <div class="block mt-2 p-2">
        <button class="btn btn-default btn-primary block mx-auto" @click.prevent="$emit('select', selected)" >Choose</button>
      </div>
    </div>
  </loading-view>
</template>

<script>
export default {
  props: ['resource', 'title', 'image', 'orderby', 'direction', 'group', 'ck', 'multiple', 'current', 'filter'],
  data() {
    return {
      loading: true,
      dir: this.direction,
      results: [],
      size: 'medium',
      query: '',
      page: 1,
      perPage: 25,
      selected: {}
    }
  },
  computed: {
    width() {
      switch(this.size) {
        case 'small': return 'width: 100px;';
        case 'medium': return 'width: 200px;';
        case 'large': return 'width: 300px;';
      }
    },
    height() {
      switch(this.size) {
        case 'small': return 'height: 100px;';
        case 'medium': return 'height: 200px;';
        case 'large': return 'height: 300px;';
      }
    }
  },
  methods: {
    load() {
      const { resource, title, image, orderby, dir, group, ck, query, filter, page, perPage, current } = this
      this.loading = true
      return Nova.request().post(`/day4/belongs-to-browser/load/${resource}`, {
        title, image, orderby, ck,
        groupBy: group.by,
        query,
        filter,
        direction: dir,
        offset: page * perPage - perPage,
        limit: perPage
      }).then(response => {
        this.results = page > 1 ? [...this.results, ...response.data] : response.data

        if (current) {
          const ids = String(current).split(',')

          ids.forEach(id => {
            if (!this.selected[id]) {
              const item = this.results.find(r => r.id == id)
              if (item) {
                this.selected[id] = item
              }
            }
          });
        }
        this.loading = false
      })
    },
    toggle(item) {
      if (this.selected[item.id]) {
        this.$delete(this.selected, item.id)
      } else {
        if (!this.multiple) this.$set(this, 'selected', {})
        this.$set(this.selected, item.id, item)
      }
    },
    search() {
      this.page = 1
      this.load()
    },
    loadNext() {
      this.page += 1
      this.load()
    }
  },
  mounted() {
    this.load()
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

<style lang="scss" scoped>
.embeded-view {
  width: 50vw;
  min-width: 480px;
  max-width: 100vw;
}
</style>