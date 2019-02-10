<template>
  <v-app>
    <v-content>
      <StarRating
        :color="color"
        :rating="3"
        :size="36"
        :readonly="false"
      />
      <StarRating
        color="#ef9a9a"
        :icon="icon"
        :rating="3"
        :size="24"
        :readonly="false"
      />
    </v-content>
  </v-app>
</template>

<script>
import StarRating from './components/StarRating'

export default {
  name: 'App',
  components: {
    StarRating
  },
  data () {
    return {
      color: options.color,
      icon: options.icon,
    }
  }
}
</script>
