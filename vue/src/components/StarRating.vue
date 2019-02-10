<template>
  <v-layout text-xs-center wrap>
    <v-rating
      v-model="rating"
      :length="length"
      :empty-icon="emptyIcon"
      :full-icon="fullIcon"
      :hover="hover"
      :readonly="readonly"
      :color="color"
      :background-color="color"
      :size="size"
    ></v-rating>
  </v-layout>
</template>

<script>
  export default {
    props: ['color', 'icon', 'rating', 'readonly'],
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
        default: false,
      },
    },
    data: function () {
      return {
        emptyIcon: `${this.icon}-outline`,
        fullIcon: this.icon,
        hover: true,
        rating: this.rating,
        length: 5,
      }
    },
    mounted () {
      console.log(this.rating);
      console.log(options.color);
    },
  }
</script>

<style>

</style>
