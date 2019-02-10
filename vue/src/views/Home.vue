<template>
  <StarRating />
</template>

<script>
  import StarRating from '../components/StarRating'

  export default {
    components: {
      StarRating
    }
  }
</script>
ias to /src
import StarRating from '@/components/StarRating.vue';

export default {
  name: 'home',
  components: {
    StarRating,
  },
};
</script>
