<template>
  <div>
    <div class="container" v-if="loading">loading...</div>

    <div class="container" v-if="!loading">
      <b-card :header="'Welcome, ' + account.name" class="mt-3">
        <b-card-text>
          <div>
            Account: <code>{{ account.id }}</code>
          </div>
          <div>
            Balance:
            <code
              >{{ account.currency === "usd" ? "$" : "€"
              }}{{ account.balance }}</code
            >
          </div>
        </b-card-text>
        <b-button size="sm" variant="success" @click="show = !show"
          >New payment</b-button
        >

        <b-button
          class="float-right"
          variant="danger"
          size="sm"
          nuxt-link
          to="/"
          >Logout</b-button
        >
      </b-card>

      <b-card class="mt-3" header="New Payment" v-show="show">
        <b-form @submit="onSubmit">
          <b-form-group id="input-group-1" label="To:" label-for="input-1">
            <b-form-input
              id="input-1"
              size="sm"
              v-model="payment.to"
              type="number"
              required
              placeholder="Destination ID"
            ></b-form-input>
          </b-form-group>

          <b-form-group id="input-group-2" label="Amount:" label-for="input-2">
            <b-input-group prepend="$" size="sm">
              <b-form-input
                id="input-2"
                v-model="payment.amount"
                type="number"
                required
                placeholder="Amount"
              ></b-form-input>
            </b-input-group>
          </b-form-group>

          <b-form-group id="input-group-3" label="Details:" label-for="input-3">
            <b-form-input
              id="input-3"
              size="sm"
              v-model="payment.details"
              required
              placeholder="Payment details"
            ></b-form-input>
          </b-form-group>

          <b-button type="submit" size="sm" variant="primary">Submit</b-button>
        </b-form>
      </b-card>

      <b-card class="mt-3" header="Payment History">
        <b-table striped hover :items="transactions"></b-table>
      </b-card>
    </div>
  </div>
</template>

<script lang="ts">
import axios from "axios";
import Vue from "vue";

export default {
  data() {
    return {
      show: false,
      payment: {},

      account: null,
      transactions: null,

      loading: true
    };
  },

  mounted() {
    const that = this;

    axios
      .get(`http://herpderp.ca/api/accounts/${this.$route.params.id}`)
      .then(function(response) {
        if (!response.data.length) {
          window.location.href = "/ybankweb";
        } else {

          that.account = response.data[0];

          if (that.account && that.transactions) {
            that.loading = false;
          }
        }
      });

    axios
      .get(
        `http://herpderp.ca/api/accounts/${
          that.$route.params.id
        }/transactions`
      )
      .then(function(response) {
        that["transactions"] = response.data;

        var transactions = [];
        for (let i = 0; i < that.transactions.length; i++) {
          that.transactions[i].amount =
            (that.account.currency === "usd" ? "$" : "€") +
            that.transactions[i].amount;

          if (that.account.id != that.transactions[i].to) {
            that.transactions[i].amount = "-" + that.transactions[i].amount;
          }

          transactions.push(that.transactions[i]);
        }

        that.transactions = transactions;

        if (that.account && that.transactions) {
          that.loading = false;
        }
      });
  },

  methods: {
    onSubmit(evt) {
      var that = this;

      evt.preventDefault();

      axios.post(
        `http://herpderp.ca/api/accounts/${
          this.$route.params.id
        }/transactions`,

        this.payment
      ).then(function(response) {
        if(response.data){
          alert(response.data.error);
        }
      }).then(function(){

        that.payment = {};
        that.show = false;

        // update items
        axios
            .get(`http://herpderp.ca/api/accounts/${that.$route.params.id}`)
            .then(function(response) {
              if (!response.data.length) {
                window.location.href = "/";
              } else {
                that.account = response.data[0];
              }
            });

        axios
            .get(
                `http://herpderp.ca/api/accounts/${
                        that.$route.params.id
                }/transactions`
            )
            .then(function(response) {
              that["transactions"] = response.data;

              var transactions = [];
              for (let i = 0; i < that.transactions.length; i++) {
                that.transactions[i].amount =
                        (that.account.currency === "usd" ? "$" : "€") +
                        that.transactions[i].amount;

                if (that.account.id != that.transactions[i].to) {
                  that.transactions[i].amount = "-" + that.transactions[i].amount;
                }

                transactions.push(that.transactions[i]);
              }

              that.transactions = transactions;
            });
      });


    }
  }
};
</script>
