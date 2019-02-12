<template>
  <v-layout text-xs-center wrap>
    <v-rating
      v-model="rate"
      length="5"
      hover="true"
      :full-icon="fullIcon"
      :empty-icon="emptyIcon"
      :half-increments="halfIncrements"
      :half-icon="halfIcon"
      :readonly="readonly"
      :color="color"
      :background-color="color"
      :size="size"
    ></v-rating>
    <p>{{rating}}</p>
  </v-layout>
</template>

<script>
  export default {
    props: {
      color: {
        type: String,
        default: '#daa520',
      },
      icon: {
        type: String,
        default: 'mdi-star',
      },
      rating: {
        type: Number,
        default: 3,
      },
      size: {
        type: Number,
        default: 24,
      },
      readonly: {
        type: Boolean,
        default: true,
      },
      halfIncrements: {
        type: Boolean,
        default: false,
      },
    },
    data: function () {
      return {
        fullIcon: this.icon,
        emptyIcon: `${this.icon}-outline`,
        halfIcon: `${this.icon}-half-full`,
        rate: this.rating,
      }
    },
  }
</script>
