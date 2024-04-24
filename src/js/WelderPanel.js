
const app = new Vue({
    el: '#vue-app',
    data() {
        return {
            welderNextSequence: viewAppletData.welderNextSequence,
            timerCount: viewAppletData.timerCount
        }
    },
    watch: {
        timerCount: {
            handler (value) {
                console.log('timer: ', value)
                if (value > 0) {
                    $('#countdown-container').css('display', 'block')
                    $('#countdown-timer').html('')
                    $('#countdown-timer').append(this.timerCount)
                    setTimeout(() => {
                        this.timerCount--;
                    }, 1000)
                }
                else {
                    $('#countdown-container').css('display', 'none')

                    $.each(this.welderNextSequence, function (key, data) {
                        console.log('key: ', key)
                            if (key == 'current') {
                                if (data?.designation != null) {
                                    $('#current-product-designation').html('')
                                    $('#current-product-code').html('')
                                    $('#current-product-material').html('')

                                    $('#product-color').css('fill', '#' + data.color)
                                    $('#hide-app').css('display', 'none')
                                    $('#vue-app').css('display', 'block')

                                    $('#current-product-designation').append(data.designation)
                                    $('#current-product-code').append(data.articleNumber)
                                    $('#current-product-code').css('font-size', '5vh')

                                    if (data.designation.length > 20) {
                                        $('#current-product-designation').css('font-size', '5vh')
                                    }
                                    else {
                                        $('#current-product-designation').css('font-size', '6vh')
                                    }

                                    $('#current-product-material').append(data.materialAndColor)

                                    if (data.materialAndColor.length > 20) {
                                        $('#current-product-material').css('font-size', '5vh')

                                        if (data.materialAndColor.length >= 25) {
                                            $('#current-product-material').css('font-size', '4.5vh')
                                        }
                                    }
                                    else {
                                        $('#current-product-material').css('font-size', '6vh')
                                    }
                                }
                                else {
                                    $('#hide-app').css('display', 'block')
                                    $('#vue-app').css('display', 'none')

                                    $('#current-product-designation').html('')
                                    $('#current-product-code').html('')
                                    $('#current-product-material').html('')

                                    $('#product-color').css('fill', '#000')
                                }
                            }

                            if (key == 'next') {
                                if (data?.designation != null) {
                                    $('#next-product-designation').html('')
                                    $('#next-product-code').html('')
                                    $('#next-product-material').html('')
                                    $('#product-image').attr('src', '')

                                    $('#next-product-designation').append(data.designation)
                                    $('#next-product-code').append(data.articleNumber)
                                    $('#next-product-code').css('font-size', '5vh')

                                    if (typeof data.image !== "undefined" && data.image !== null && data.image.length > 0) {
                                        $('#product-image').attr('src', data.image)
                                    }

                                    if (data.designation.length > 20) {
                                        $('#next-product-designation').css('font-size', '5vh')
                                    }
                                    else {
                                        $('#next-product-designation').css('font-size', '6vh')
                                    }

                                    $('#next-product-material').append(data.materialAndColor)

                                    if (data.materialAndColor.length > 20) {
                                        $('#next-product-material').css('font-size', '5vh')

                                        if (data.materialAndColor.length >= 25) {
                                            $('#next-product-material').css('font-size', '4.5vh')
                                        }
                                    }
                                    else {
                                        $('#next-product-material').css('font-size', '6vh')
                                    }
                                }
                                else {
                                    console.log('else')
                                    $('#next-product-designation').html('')
                                    $('#next-product-code').html('')
                                    $('#next-product-material').html('')

                                    $('#product-image').src = ''
                                }
                            }
                    })
                }
            },
            immediate: true
        }
    },
    mounted() {
        window.Echo.channel('next-welder-product')
            .listen('.next-welder-product-event', (e) => {
                this.timerCount = 30

                if (e['startShift'] === true) {
                    this.timerCount = 1
                }

                let workCenter = window.location.pathname.substring(window.location.pathname.lastIndexOf('/') + 1)

                if (
                    typeof e['workCenter'] !== 'undefined' &&
                    e['workCenter'] === workCenter
                ) {
                    this.welderNextSequence = e
                }
                console.log('isWorkCenter: ', isWorkCenter)
                console.log('this.welderNextSequence[\'workCenter\']: ', this.welderNextSequence['workCenter'])
                console.log('workCenter: ', workCenter)
            })
    },
    beforeDestroy() {
        this.timerCount.destroy()
    },
    methods: {

    }
});
