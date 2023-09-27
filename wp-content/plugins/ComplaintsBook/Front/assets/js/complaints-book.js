
( function( $ ) {
    $( function() {
        const formComplaints = $('#form-complaints-book');
        const btnSubmit = $('#btn-submit');
        const cboBusinessName = $('#cbo-business-name');
        const txtCorrelative = $('#txt-correlative');
        const contentAlert = $('.content-alert');
        const getAlert = function(type) {
            if (type === 'success') {
                return `<div class="alert alert-success"><span class="material-symbols-outlined">check</span>[message]</div>`;
            } else {
                return `<div class="alert alert-danger"><span class="material-symbols-outlined">cancel</span>[message]</div>`;
            }
        };
        const showAlertSuccess = function(message) {
            let template = getAlert('success');
            template = template.replace('[message]', message);
            contentAlert.html('');
            contentAlert.append(template);
        };
        const showAlertError = function(message) {
            let template = getAlert('error');
            template = template.replace('[message]', message);
            contentAlert.html('');
            contentAlert.append(template);
        };
        const cleanForm = function () {
            $('#cbo-business-name').val('');
            $('#txt-correlative').val('');
            $('#txt-name').val('');
            $('#txt-lastname').val('');
            $('#txt-phone').val('');
            $('#txt-document-number').val('');
            $('#txt-address').val('');
            $('#txt-email').val('');
            $('#txt-tutor').val('');
            $('#cbo-type-service').val('');
            $('#txt-amount').val('');
            $('#txt-description').val('');
            $('#cbo-type-claim').val('');
            $('#txt-detail').val('');
            $('#txt-request').val('');
        };
        jQuery.validator.addMethod("phone", function(phone_number, element) {
            phone_number = phone_number.replace(/\s+/g, "");
            return this.optional(element) || phone_number.length >= 9 &&
                phone_number.match(/^[0-9]{9}$|^(\+51)?[0-9]{9}$/);
        }, "Please specify a valid phone number");

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
                    phone: true,
                },
                'txt-document-number': {
                    required: true,
                    digits: true,
                    minlength: 8
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
                url: cbData.url,
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
                    cleanForm();
                    showAlertSuccess('Se registró correctamente su reclamo o queja.')
                },
                error: function(responseText) {
                    console.log('error', responseText);

                    btnSubmit.removeAttr( 'disabled' );
                    showAlertError('Se produjó un error.')
                }
            });
        });

        cboBusinessName.on('change', function(e) {
            const $this = $(this);
            const value = $this.val();

            e.preventDefault();

            txtCorrelative.val('');

            if(value !== '' && value !== 0) {
                $.ajax({
                    url: cbData.url,
                    type: 'POST',
                    data: {
                        action: 'get-correlative',
                        id: value
                    },
                    dataType: 'json',
                    success: function(response) {
                        const newValue = `Hoja de Reclamación [${response.correlative}]`;
                        txtCorrelative.val(newValue);
                    },
                    error: function(responseText) {
                        console.log('error', responseText);
                    }
                });
                // const projects = cbData.projects;
                // const filter = projects.filter(e => parseInt(e.id) === parseInt(value) );
                //
                // if(filter && filter[0]) {
                //     const newValue = `Hoja de Reclamación [${filter[0].correlative}]`;
                //     txtCorrelative.val(newValue);
                // }
            }
        });
    });
}( jQuery ) );
