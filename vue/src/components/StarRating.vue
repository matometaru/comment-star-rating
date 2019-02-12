<template>
  <v-layout text-xs-center wrap>
    <v-rating
      v-model="rate"
      :length="length"
      :empty-icon="emptyIcon"
      :full-icon="fullIcon"
      :hover="hover"
      :readonly="readonly"
      :color="color"
      :background-color="color"
      :size="size"
    ></v-rating>
    <p>{{rate}}</p>
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
    },
    data: function () {
      return {
        emptyIcon: `${this.icon}-outline`,
        fullIcon: this.icon,
        hover: true,
        rate: this.rating,
        length: 5,
      }
    },
    mounted () {
    },
  }
</script>

<style>

</style>
