<template>
  <div>
    <p>{{text}}</p>
    <StarRating
      :color="color"
      :icon="icon"
      :rating="average"
      :size="size"
    />
    <div class="ctr-counter-main">
    </div>
    <button v-on:click="counter">counter</button>
  </div>
</template>

<script>
  import StarRating from './StarRating'
  import Graph from './Graph.js'
  export default {
    components: {
      StarRating,
    },
    props: {
      text: {
        type: String,
      },
      size: {
        type: Number,
        default: 24,
      },
      ratings: {
        type: Array,
        default: [],
      },
    },
    data: function () {
      return {
        color: options.color,
        icon: options.icon,
        length: 5,
      }
    },
    computed: {
      total: function () {
        return this.ratings.reduce((a, x) => a += x.value , 0);
      },
      count: function () {
        return this.ratings.reduce((a, x) => a += x.value, 0);
      },
      average: function () {
        return Math.round(3.42, 1);
      },
    },
    methods: {
      counter: function () {
        this.ratings[0].value++;
      }
    },
    watch: {
      ratings: {
        handler: function () {
          this.graph.dataset = this.ratings;
          this.graph.update();
        },
        deep: true
      }
    },
    mounted () {
      if (this.ratings.length > 0) {
        this.graph = new Graph('.ctr-counter-main', this.count , this.ratings, this.color);
        this.graph.draw();
      }
    },
  }
</script>

<style lang="scss">
  .ctr-counter-main {
    width: 400px;
    margin: 10px;
  }

  .horizontal-bar-graph {
    display: table;
    width: 100%;
    &-segment {
      display: table-row;
    }
    &-label {
      display: table-cell;
      text-align: right;
      padding: 4px 10px 4px 0;
      vertical-align: baseline;
      white-space: nowrap;
    }
    &-value {
      display: table-cell;
      vertical-align: middle;
      width: 100%;
      &-bg {
        background: #ececec;
        line-height: 1;
      }
      &-bar {
        background: #daa520;
        box-sizing: border-box;
        height: 1.7em;
        vertical-align: bottom;
        overflow: visible;
        display: inline-block;
        white-space: nowrap;
      }
    }
    &-num {
      padding: 0 0 0 10px;
    }
  }
</style>
