Nova.booting((Vue, router, store) => {
  Vue.component('index-belongs-to-browser', require('./components/IndexField'))
  Vue.component('detail-belongs-to-browser', require('./components/DetailField'))
  Vue.component('form-belongs-to-browser', require('./components/FormField'))
})
