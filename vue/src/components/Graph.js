import * as d3 from "d3";

export default class Graph {

  constructor(el, count, dataset, color) {
    this.el = d3.select(el);
    this.dataset  = dataset;
    this.count    = count;
    this.color    = color;
  }

  /**
   * 描画前の初期化処理
   */
  init() {
    // const isEmpty = this.el.empty();
    // if (!isEmpty) {
    //   this.remove();
    // }
  }

  /**
   * 描画するグラフ選択し実行
   */
  draw(method = 'horizontal') {
    this.init();
    switch (method) {
      case 'vertical':
        this.vertical();
        break;
      default:
        this.horizontal();
    }
  }

  /**
   * 横棒グラフを描画
   */
  horizontal() {
    const x = d3.scaleLinear()
      .domain([0, this.count])
      .range([0, 100]);

    const segment = this.el
      .selectAll(".horizontal-bar-graph-segment")
      .data(this.dataset)
      .enter()
      .append("div").classed("horizontal-bar-graph-segment", true);

    // グラフのラベル部分
    segment
      .append("div").classed("horizontal-bar-graph-label", true)
      .text((d) => d.label);

    // グラフのバー
    segment
      .append("div").classed("horizontal-bar-graph-value", true)
      .append("div").classed("horizontal-bar-graph-value-bg", true)
      .append("div").classed("horizontal-bar-graph-value-bar", true)
      .style('background-color', this.color)
      .transition()
      .duration(1000)
      .style("width", (d) => x(d.value) + "%");

    // 数字部分
    segment
      .append("div").classed("horizontal-bar-graph-num", true)
      .text((d) => d.value);
  }

  update() {
    const x = d3.scaleLinear()
      .domain([0, this.count])
      .range([0, 100]);

    this.el
      .selectAll(".horizontal-bar-graph-value-bar")
      .data(this.dataset)
      .transition()
      .duration(1000)
      .ease(d3.easeBounceInOut)
      .style("width", (d) => x(d.value) + "%");

    // 数字部分
    this.el
      .selectAll(".horizontal-bar-graph-num")
      .data(this.dataset)
      .text((d) => d.value);
  }

  /**
   * 要素の削除
   */
  remove() {
    this.el.remove();
  }
}
