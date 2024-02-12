
const app = new Vue({
    el: '#vue-app',
    data() {
        return {
            welderNextSequence: viewAppletData.welderNextSequence,
        }
    },
    mounted() {
        window.Echo.channel('next-welder-product')
            .listen('.next-welder-product-event', (e) => {
                $.each(e, function (key, data) {
                    console.log('key: ', key)
                    console.log('data: ', data)
                })
            })
    },
    methods: {

    }
});
