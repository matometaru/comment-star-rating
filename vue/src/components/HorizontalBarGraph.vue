<template>
  <div class="ctr-counter-main">
    <template v-for="data in dataset">
      <a href="#" @click="handleOpen(data.key)">
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
      </a>
    </template>
    <CommentModal ref="commentModal"/>
  </div>
</template>

<script>
  import CommentModal from './CommentModal'

  export default {
    components: {
      CommentModal
    },
    props: {
      color: {
        type: String,
        default: '#daa520',
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
    },
    methods: {
      width: function (value) {
        return `${value/this.count * 100}%`;
      },
      handleOpen: function (value) {
        this.$refs.commentModal.open(value)
      }
    },
  }
</script>

<style lang="scss">
  .ctr-counter-main {
    width: 400px;
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
        height: 1.7em;
        transition: all .5s;
      }
    }
    &-num {
      padding: 0 0 0 10px;
    }
  }
</style>
