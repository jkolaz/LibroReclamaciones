
( function( $ ) {
    $( function() {
        const formComplaints = $('#form-complaints-book');
        const btnSubmit = $('#btn-submit');
        const cboBusinessName = $('#cbo-business-name');
        const txtCorrelative = $('#txt-correlative');

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
                },
                'txt-amount': {
                    required: true,
                },
                'txt-description': {
                    required: true,
                },
                'cbo-type-claim': {
                    required: true,
                },
                'txt-detail': {
                    required: true,
                },
                'txt-request': {
                    required: true,
                },
                'rd-privacy-policy': {
                    required: true,
                }
            },
            errorElement: "em"
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

        cboBusinessName.on('change', function(e) {
            const $this = $(this);
            const value = $this.val();

            e.preventDefault();

            txtCorrelative.val('');

            if(value !== '' || value !== 0) {
                const projects = cbData.projects;
                const filter = projects.filter(e => parseInt(e.id) === parseInt(value) );

                if(filter && filter[0]) {
                    const newValue = `Hoja de Reclamación [${filter[0].correlative}]`;
                    txtCorrelative.val(newValue);g
                }
            }
        });
    });
}( jQuery ) );
