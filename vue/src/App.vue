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
        :halfIncrements="true"
        icon="heart"
        :rating="3.5"
        :size="24"
        :readonly="false"
      />
      <TotalRating
        text="5つのハートのうち${this.average}"
        :size="24"
        :dataset="ratings"
      />
      <router-link to="/rating">Rating</router-link>
      <router-view></router-view>
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
        {"label":"星5","key":5,"value":2},
        {"label":"星4","key":4,"value":0},
        {"label":"星3","key":3,"value":1},
        {"label":"星2","key":2,"value":0},
        {"label":"星1","key":1,"value":1}
      ],
    }
  }
}
</script>
