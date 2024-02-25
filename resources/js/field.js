import IndexField from './components/IndexField'
import DetailField from './components/DetailField'
import FormField from './components/FormField'

Nova.booting((app, store) => {
  app.component('index-multi-modify', IndexField)
  app.component('detail-multi-modify', DetailField)
  app.component('form-multi-modify', FormField)
})
