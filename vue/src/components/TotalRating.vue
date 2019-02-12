<template>
  <div>
    <p>{{text}}</p>
    <StarRating
      :color="color"
      :icon="icon"
      :rating="average"
      :size="size"
      halfIncrements="true"
    />
    <div class="ctr-counter-main">
      <template v-for="data in dataset">
        <div class="horizontal-bar-graph-segment">
          <div class="horizontal-bar-graph-label">{{data.label}}</div>
          <div class="horizontal-bar-graph-value">
            <div class="horizontal-bar-graph-value-bg">
              <div class="horizontal-bar-graph-value-bar"
                   v-bind:style="{ width: width(data.value), backgroundColor: color }"
              ></div>
            </div>
          </div>
          <div class="horizontal-bar-graph-num">{{data.value}}</div>
        </div>
      </template>
    </div>
    <button v-on:click="counter">counter</button>
  </div>
</template>

<script>
  import StarRating from './StarRating'
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
      dataset: {
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
        return this.dataset.reduce((a, x) => a += (x.value * x.key)  , 0);
      },
      count: function () {
        return this.dataset.reduce((a, x) => a += x.value, 0);
      },
      average: function () {
        return Math.round(this.total/this.count * 10) /10;
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
