<template>
  <div class="ctr-total-rating">
    <StarRating
      :color="color"
      :icon="icon"
      :rating="average"
      :size="size"
      :halfIncrements="true"
    />
    <p class="ctr-counter-text">{{replacedText}}</p>
    <HorizontalBarGraph
      :dataset="dataset"
      :color="color"
    />
  </div>
</template>

<script>
  import StarRating from './StarRating'
  import HorizontalBarGraph from './HorizontalBarGraph'
  export default {
    components: {
      StarRating,
      HorizontalBarGraph,
    },
    props: {
      text: {
        type: String,
      },
      color: {
        type: String,
        default: '#daa520',
      },
      icon: {
        type: String,
        default: 'mdi-star',
      },
      size: {
        type: Number,
        default: 24,
      },
      dataset: {
        type: Array,
        default: [],
      },
    },
    computed: {
      total: function () {
        return this.dataset.reduce((a, x) => a += (x.value * x.key)  , 0);
      },
      count: function () {
        return this.dataset.reduce((a, x) => a += x.value, 0);
      },
      average: function () {
        return Math.round(this.total/this.count * 10) /10;
      },
      replacedText: function () {
        return this.text.replace( "${this.average}", this.average );
      },
    },
    methods: {
      counter: function () {
        this.dataset[0].value++;
      },
      width: function (value) {
        return `${value/this.count * 100}%`;
      }
    },
  }
</script>

<style lang="scss">
  .ctr-total-rating {
    padding: 10px;
  }
  .ctr-counter-text {
    font-size: 1.2rem;
  }
</style>
