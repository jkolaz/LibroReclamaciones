
( function( $ ) {
    $( function() {
        const formComplaints = $('#form-complaints-book');
        const btnSubmit = $('#btn-submit');

        formComplaints.validate({
            rules: {
                'cbo-business-name': {
                    required: true,
                },
                'txt-name': {
                    required: true,
                },
                'txt-lastname': {
                    required: true,
                },
                'txt-phone': {
                    required: true,
                },
                'txt-document-number': {
                    required: true,
                },
                'txt-address': {
                    required: true,
                },
                'txt-email': {
                    required: true,
                    email: true
                },
                'cbo-type-service': {
                    required: true,
                    email: true
                },
                'txt-amount': {
                    required: true,
                    email: true
                },
                'txt-description': {
                    required: true,
                    email: true
                },
                'cbo-type-claim': {
                    required: true,
                    email: true
                },
                'txt-detail': {
                    required: true,
                    email: true
                },
                'txt-request': {
                    required: true,
                    email: true
                },
                'rd-privacy-policy': {
                    required: true,
                    email: true
                }
            },

        });

        formComplaints.on('submit', function(e) {
            const $this = $(this);
            e.preventDefault();

            btnSubmit.attr( 'disabled', true );

            if( !formComplaints.valid() ) {
                btnSubmit.removeAttr( 'disabled' );
                return false;
            }

            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: $this.serialize(),
                success: function(response) {
                    const data = response.data;
                    const url = data.url;
                    const filename = data.filename;
                    const a = document.createElement("a");
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();

                    btnSubmit.removeAttr( 'disabled' );
                },
                error: function(responseText) {
                    console.log('error', responseText);

                    btnSubmit.removeAttr( 'disabled' );
                }
            });
        });
    });
}( jQuery ) );
