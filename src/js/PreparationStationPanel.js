
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

                if (data?.designation != null) {
                    $('#product-designation').html('')
                    $('#product-material').html('')

                    $('#porsche-order-number').html('')
                    $('#porsche-sequence-number').html('')
                    $('#porsche-article-number').html('')

                    $('#hide-app').css('display', 'none')
                    $('#vue-app').css('display', 'block')
                    $('#pulse-line').css('display', 'none')
                    $('#warning-sign').css('display', 'none')

                    $('#product-color').css('fill', '#' + data.color)
                    $('#product-designation').append(data.designation)

                    if (data.isScrap === true) {
                        $('#warning-sign').css('display', 'block')
                    }

                    if (data.designation.length > 20) {
                        $('#product-designation').css('font-size', '7vh')
                    }
                    else {
                        $('#product-designation').css('font-size', '8vh')
                    }

                    $('#product-material').append(data.materialAndColor)

                    if (data.materialAndColor.length > 20) {
                        $('#product-material').css('font-size', '7vh')

                        if (data.materialAndColor.length >= 25) {
                            $('#product-material').css('font-size', '6.5vh')
                        }
                    }
                    else {
                        $('#product-material').css('font-size', '8vh')
                    }

                    if (
                        typeof data.side !== 'undefined' && data.side !== null &&
                        typeof data.pillar !== 'undefined' && data.pillar !== null
                    ) {

                        if (data.pillar === 'A') {
                            if (data.side === 'L') {
                                $('#pulse-line').css('display', 'block')
                                $('#pulse-line').css('top', '38%')
                                $('#pulse-line').css('left', '19.5%')
                                $('#pulse-line').css('height', '15%')
                                $('#pulse-line').css('transform', 'rotate(-18deg)')
                            }
                            if (data.side === 'R') {
                                $('#pulse-line').css('display', 'block')
                                $('#pulse-line').css('top', '38%')
                                $('#pulse-line').css('left', '36.5%')
                                $('#pulse-line').css('height', '15%')
                                $('#pulse-line').css('transform', 'rotate(18deg)')
                            }
                        }
                        if (data.pillar === 'B') {
                            if (data.side === 'L') {
                                $('#pulse-line').css('display', 'block')
                                $('#pulse-line').css('top', '59.5%')
                                $('#pulse-line').css('left', '19.2%')
                                $('#pulse-line').css('height', '10%')
                                $('#pulse-line').css('transform', 'rotate(-83deg)')
                            }
                            if (data.side === 'R') {
                                $('#pulse-line').css('display', 'block')
                                $('#pulse-line').css('top', '59.5%')
                                $('#pulse-line').css('left', '37%')
                                $('#pulse-line').css('height', '10%')
                                $('#pulse-line').css('transform', 'rotate(83deg)')
                            }
                        }
                        if (data.pillar === 'C') {
                            if (data.side === 'L') {
                                $('#pulse-line').css('display', 'block')
                                $('#pulse-line').css('top', '79.5%')
                                $('#pulse-line').css('left', '19.2%')
                                $('#pulse-line').css('height', '10%')
                                $('#pulse-line').css('width', '2.5%')
                                $('#pulse-line').css('border-radius', '3vh')
                                $('#pulse-line').css('transform', 'rotate(-136deg)')
                            }
                            if (data.side === 'R') {
                                $('#pulse-line').css('display', 'block')
                                $('#pulse-line').css('top', '79%')
                                $('#pulse-line').css('left', '36.5%')
                                $('#pulse-line').css('height', '10%')
                                $('#pulse-line').css('width', '2.5%')
                                $('#pulse-line').css('border-radius', '3vh')
                                $('#pulse-line').css('transform', 'rotate(136deg)')
                            }
                        }
                    }

                    if (
                        typeof data.porscheOrderNumber !== 'undefined' && data.porscheOrderNumber !== null
                    ) {
                        $('#porsche-order-number').append(data.porscheOrderNumber)
                    }

                    if (
                        typeof data.porscheSequenceNumber !== 'undefined' && data.porscheSequenceNumber !== null
                    ) {
                        $('#porsche-sequence-number').append(data.porscheSequenceNumber)
                    }

                    if (
                        typeof data.articleNumber !== 'undefined' && data.articleNumber !== null
                    ) {
                        $('#porsche-article-number').append(data.articleNumber)
                    }
                }
                else {
                    $('#hide-app').css('display', 'block')
                    $('#vue-app').css('display', 'none')

                    $('#product-color').css('fill', '#000')
                    $('#product-designation').html('')
                    $('#product-material').html('')

                    $('#pulse-line').css('display', 'none')
                    $('#warning-sign').css('display', 'none')

                    $('#porsche-order-number').html('')
                    $('#porsche-sequence-number').html('')
                    $('#porsche-article-number').html('')
                }
            });
    },
    methods: {

    }
});
