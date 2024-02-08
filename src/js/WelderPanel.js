
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
                    if (data?.designation != null) {
                        $('#product-designation').html('')
                        $('#product-material').html('')

                        $('#hide-app').css('display', 'none')
                        $('#vue-app').css('display', 'block')

                        $('#product-color').css('background-color', '#' + data.color)
                        $('#product-designation').append(data.designation)

                        if (data.designation.length > 20) {
                            $('#product-designation').css('font-size', '7vh')
                        }
                        else {
                            $('#product-designation').css('font-size', '8vh')
                        }

                        $('#product-material').append(data.materialAndColor)
                    }
                    else {
                        $('#hide-app').css('display', 'block')
                        $('#vue-app').css('display', 'none')

                        $('#product-color').css('background-color', '#000')
                        $('#product-designation').html('')
                        $('#product-material').html('')
                    }
                })
            })
    },
    methods: {

    }
});
