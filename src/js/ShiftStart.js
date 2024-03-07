const app = new Vue({
    el: '#vue-app',
    data() {
        return {
            buttonUrl: viewAppletData.buttonUrl,
        }
    },
    mounted() {
        window.Echo.channel('all-work-center-is-alive')
            .listen('.all-work-center-is-alive-event', (e) => {

                if ($('#reset-default').hasClass('button-red')) {
                    $('#reset-default').removeClass('button-red')
                }

                if ($('#reset-default').hasClass('button-green')) {
                    $('#reset-default').removeClass('button-green')
                }

                if (typeof e?.alive !== 'undefined' && e?.alive === true) {
                    $('#reset-default').addClass('button-green')

                    if ($('#reset-default').hasClass('button-red')) {
                        $('#reset-default').removeClass('button-red')
                    }
                }
                else {
                    $('#reset-default').addClass('button-red')

                    if ($('#reset-default').hasClass('button-green')) {
                        $('#reset-default').removeClass('button-green')
                    }
                }
            });
    },
    methods: {
        startOfShiftButtonClick: function (event) {
            if (event.target.classList.contains('button-red')) {
                location.href = 'http://oeem.awf.hu:1881/ui'
            }

            if (event.target.classList.contains('button-green')) {
                setTimeout(this.showWarning, 100)
            }
        },
        successShiftButtonClick: function (event) {
            $('#warningModal').css('display', 'none')

            document.querySelector('#loading').classList.add('loading')
            document.querySelector('#loading-content').classList.add('loading-content')

            $.get(this.buttonUrl, function (response) {
                if (response.success === true) {
                    $('#successModal').css('display', 'block')
                }
            })
                .always(function () {
                    document.querySelector('#loading').classList.remove('loading')
                    document.querySelector('#loading-content').classList.remove('loading-content')
                })
        },
        showWarning: function () {
            $('#warningModal').css('display', 'block')
        },
        closeModal: function (event) {
            let successModal = $('#successModal')
            let warningModal = $('#warningModal')

            if (successModal.css('display') == 'block') {
                successModal.css('display', 'none')
            }
            if (warningModal.css('display') == 'block') {
                warningModal.css('display', 'none')
            }
        }
    }
});
