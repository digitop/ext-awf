
const app = new Vue({
    el: '#vue-app',
    data() {
        return {
            nextSequence: viewAppletData.nextSequence,
        }
    },
    mounted() {
        window.Echo.channel('next-product')
            .listen('.next-product-event', (e) => {

                var data = e[0];
                console.log(data)
            });
    },
    methods: {
    }
});
