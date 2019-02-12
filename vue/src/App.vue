<template>
  <v-app>
    <v-content>
      <StarRating
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
        text="5つ星のうち3.2"
        :dataset="ratings"
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
        {"label":"ハート5","key":5,"value":2},
        {"label":"ハート4","key":4,"value":0},
        {"label":"ハート3","key":3,"value":1},
        {"label":"ハート2","key":2,"value":0},
        {"label":"ハート1","key":1,"value":1}
      ],
      average: 3.5,
    }
  }
}
</script>
