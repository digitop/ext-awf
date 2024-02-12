
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

                    if (key == 'current') {
                        if (data?.designation != null) {
                            $('#current-product-designation').html('')
                            $('#current-product-material').html('')

                            $('#product-color').css('fill', '#' + data.color)
                            $('#hide-app').css('display', 'none')
                            $('#vue-app').css('display', 'block')

                            $('#current-product-designation').append(data.designation)



                            if (data.designation.length > 20) {
                                $('#current-product-designation').css('font-size', '7vh')
                            }
                            else {
                                $('#current-product-designation').css('font-size', '8vh')
                            }

                            $('#current-product-material').append(data.materialAndColor)

                            if (data.materialAndColor.length > 20) {
                                $('#current-product-material').css('font-size', '7vh')

                                if (data.materialAndColor.length >= 25) {
                                    $('#current-product-material').css('font-size', '6.5vh')
                                }
                            }
                            else {
                                $('#current-product-material').css('font-size', '8vh')
                            }
                        }
                        else {
                            $('#hide-app').css('display', 'block')
                            $('#vue-app').css('display', 'none')

                            $('#current-product-designation').html('')
                            $('#current-product-material').html('')
                            $('#product-color').css('fill', '#000')
                        }
                    }

                    if (key == 'next') {
                        if (data?.designation != null) {
                            $('#next-product-designation').html('')
                            $('#next-product-material').html('')
                            $('#product-image').attr('src', '')

                            $('#next-product-designation').append(data.designation)

                            if (typeof data.image !== "undefined" && data.image !== null && data.image.length > 0) {
                                $('#product-image').attr('src', data.image)
                            }

                            if (data.designation.length > 20) {
                                $('#next-product-designation').css('font-size', '7vh')
                            }
                            else {
                                $('#next-product-designation').css('font-size', '8vh')
                            }

                            $('#next-product-material').append(data.materialAndColor)

                            if (data.materialAndColor.length > 20) {
                                $('#next-product-material').css('font-size', '7vh')

                                if (data.materialAndColor.length >= 25) {
                                    $('#next-product-material').css('font-size', '6.5vh')
                                }
                            }
                            else {
                                $('#next-product-material').css('font-size', '8vh')
                            }
                        }
                        else {
                            $('#next-product-designation').html('')
                            $('#next-product-material').html('')
                            $('#product-image').src = ''
                        }
                    }
                })
            })
    },
    methods: {

    }
});
