<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" persistent max-width="600px">
      <v-card>
        <v-card-title>
          <span class="headline">{{title}}</span>
        </v-card-title>
        <v-card-text>
          <ul class="comment-list">
            <li v-for="comment in comments">
              <div class="comment-head"><img :src="avatar" alt=""><span>{{comment.author_name}}</span></div>
              <StarRating
                :rating="comment.csr_rating"
              />
              <p>{{comment.date}}</p>
              <div v-html="$sanitize(comment.content.rendered)"></div>
            </li>
          </ul>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
  import StarRating from '../components/StarRating'

  export default {
    components: {
      StarRating
    },
    data: function () {
      return {
        dialog: true,
        title: 'Hello World!',
        avatar: 'http://1.gravatar.com/avatar/?s=96&d=mm&r=g',
        comments: [
          {"id":34,"author_name":"田中","author_url":"","date":"2019-02-09T15:29:56","content":{"rendered":"<p>非常に参考になりました。</p>\n"},"status":"approved","csr_rating":"5"},
          {"id":33,"author_name":"めたる","date":"2019-02-09T15:29:25","content":{"rendered":"<p>すごい。</p>\n"},"status":"approved","csr_rating":"5"},
          {"id":32,"author_name":"Mike","author_url":"","date":"2019-02-09T15:28:56","content":{"rendered":"<p>Good Job!!</p>\n"},"status":"approved","csr_rating":"5"},
          {"id":31,"author_name":"鈴木","author_url":"","date":"2019-02-09T15:29:56","content":{"rendered":"<p>ためになります。</p>\n"},"status":"approved","csr_rating":"4"},
          {"id":30,"author_name":"まとまる","date":"2019-02-09T15:29:25","content":{"rendered":"<p>いいですね。</p>\n"},"status":"approved","csr_rating":"4"},
          {"id":29,"author_name":"John","author_url":"","date":"2019-02-09T15:28:56","content":{"rendered":"<p>Nice!!</p>\n"},"status":"approved","csr_rating":"4"}
        ]
      }
    },
  }
</script>

<style lang="scss">
  .comment-list {
    list-style: none;
  }
  .comment-head {
    img {
      width: 25px;
      vertical-align: middle;
    }
    span {
      line-height: 25px;
      vertical-align: middle;
      margin-left: 10px;
    }
  }
</style>
