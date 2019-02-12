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
      <TotalRating
        :rating="average"
        :size="24"
        text="5つ星のうち3.3"
        :ratings="ratings"
      />
    </v-content>
  </v-app>
</template>

<script>
import StarRating from './components/StarRating'
import TotalRating from './components/TotalRating'

export default {
  name: 'App',
  components: {
    StarRating,
    TotalRating
  },
  data () {
    return {
      color: options.color,
      icon: options.icon,
      ratings: [
        {"label":"ハート5","value":2},
        {"label":"ハート4","value":0},
        {"label":"ハート3","value":1},
        {"label":"ハート2","value":0},
        {"label":"ハート1","value":1}
      ],
      average: 3.5,
    }
  }
}
</script>
