<template>
  <v-rating
    v-model="rating"
    length="5"
    :hover="true"
    :full-icon="selectedIcon.full"
    :empty-icon="selectedIcon.empty"
    :half-icon="selectedIcon.half"
    :half-increments="halfIncrements"
    :readonly="readonly"
    :color="color"
    :background-color="color"
    :size="size"
  ></v-rating>
</template>

<script>
  const icons = {
    'heart': {'full': 'mdi-heart', 'empty': 'mdi-heart-outline', 'half': 'mdi-heart-half-full'},
    'star': {'full': 'mdi-star', 'empty': 'mdi-star-outline', 'half': 'mdi-star-half'}
  };

  export default {
    props: {
      color: {
        type: String,
        default: '#daa520',
      },
      icon: {
        type: String,
        default: 'star',
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
        selectedIcon: icons[this.icon],
      }
    }
  }
</script>
