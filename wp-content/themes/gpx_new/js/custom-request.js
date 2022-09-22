function CustomRequestForm(el) {
    this.el = el;

    this.clear = function () {
        this.el.querySelectorAll('.form-error')
            .forEach(function (error) {
                error.classList.add('hidden');
                error.innerHTML = '';
            });
    };

    this.load = function (cid) {
        this.clear();
        axios.get(gpx_base.url_ajax, {params: {action: 'gpx_get_custom_request', cid: cid}})
            .then(function (response) {
                this.el.querySelector('#special-request-resort').value = response.data.resort || null;
                this.el.querySelector('#special-request-nearby').checked = true;
                this.el.querySelector('#special-request-region').value = response.data.region || null;
                this.el.querySelector('#special-request-city').value = response.data.city || null;
                this.el.querySelector('#special-request-miles').value = response.data.miles || 30;
                this.el.querySelector('#special-request-adults').value = response.data.adults || 0;
                this.el.querySelector('#special-request-children').value = response.data.children || 0;
                this.el.querySelector('#special-request-email').value = response.data.email || 0;
                this.el.querySelector('#special-request-roomType').value = response.data.roomType || 'Any';
                this.el.querySelector('#special-request-larger').checked = true;
                this.el.querySelector('#special-request-preference').value = response.data.preference || 'Any';
                this.el.querySelector('#special-request-checkIn').value = response.data.checkIn || null;
                this.el.querySelector('#special-request-checkIn2').value = response.data.checkIn2 || null;
                this.checkResort();
                this.checkLocation();
                this.checkRoomType();
                active_modal('modal-special-request');
            }.bind(this))
        ;
    };

    this.submit = function (e) {
        e.preventDefault();
        const form = new FormData(this.el);
        console.log('submit', $(this.el).serialize(), form);
        axios.post(gpx_base.url_ajax + '?action=gpx_post_custom_request', form)
            .then(function (response) {
                console.log('response', response.data);
                return;
                if (!response.data.success) {
                    return;
                }

                if (response.data.matched) {
                    var url = '/result?matched=' + response.data.matched;
                    $('#matchedTravelButton').attr('href', url);
                    $('#notMatchedModal, #restrictedMatchModal').appendTo('#matchedContainer');
                    $('#alertMsg').html($('#matchedModal'));
                } else {
                    $('#matchedModal, #restrictedMatchModal').appendTo('#matchedContainer');
                    $('#alertMsg').html($('#notMatchedModal'));
                }
                if (response.data.restricted) {
                    //move the not matched message back becuase this search was only within the restricted time/area
                    if (response.data.restricted === 'All Restricted') {
                        $('#notMatchedModal').appendTo('#matchedContainer');
                    }
                    $('#restrictedMatchModal').appendTo('#alertMsg');
                }
                if (response.data.holderror) {
                    $('#notMatchedModal').appendTo('#matchedContainer');
                    $('#alertMsg').text(response.data.holderror);
                }
                $('.icon-alert').remove();
                alertModal.alert($('#alertMsg').html(), true);
            }.bind(this))
            .catch(function (error) {
                if (error.response.status === 422) {
                    console.log(error.response.data);
                    this.showErrors(error.response.data.errors);
                }
            }.bind(this))
        ;
    };

    this.showError = function (field, message) {
        if (!field || !message) return;
        field.textContent = message;
        field.classList.remove('hidden');
    };

    this.showErrors = function (errors) {
        ['resort', 'region', 'city', 'miles', 'adults', 'children', 'nearby', 'email', 'roomType', 'larger', 'preference', 'checkIn', 'checkIn2']
            .forEach(function (field) {
                if (errors[field]) {
                    this.showError(this.el.querySelector(`.${field}-ac-error`), errors[field][0]);
                }
            }.bind(this));
    };

    this.checkNearby = function () {
        if (this.el.querySelector('#special-request-nearby').checked) {
            this.el.querySelector('#special-request-miles').closest('.form-row').classList.remove('hidden');
        } else {
            this.el.querySelector('#special-request-miles').closest('.form-row').classList.add('hidden');
        }
    };

    this.checkResort = function(){
        let resort = this.el.querySelector('#special-request-resort').value;
        if(resort){
            this.el.querySelector('#special-request-nearby').closest('.form-row').classList.remove('hidden');
            this.checkNearby();
        } else {
            this.el.querySelector('#special-request-nearby').closest('.form-row').classList.add('hidden');
            this.el.querySelector('#special-request-miles').closest('.form-row').classList.add('hidden');
        }
    };

    this.checkLocation = function () {
        let region = this.el.querySelector('#special-request-region').value;
        if (!region) {
            this.el.querySelector('#special-request-city').value = '';
            this.el.querySelector('#special-request-city').closest('.form-row').classList.add('hidden');
            return;
        }
        axios.get(gpx_base.url_ajax, {
            params: {
                action: 'gpx_autocomplete_location_sub',
                term: '',
                region: region,
            }
        })
            .then(function (response) {
                if (response.data.length > 0) {
                    this.el.querySelector('#special-request-city').closest('.form-row').classList.remove('hidden');
                } else {
                    this.el.querySelector('#special-request-city').value = '';
                    this.el.querySelector('#special-request-city').closest('.form-row').classList.add('hidden');
                }
            }.bind(this));
    }

    this.checkRoomType = function() {
        let value = this.el.querySelector('#special-request-roomType').value;
        if (value === 'Any') {
            this.el.querySelector('#special-request-larger').closest('.form-row').classList.add('hidden');
        } else {
            this.el.querySelector('#special-request-larger').closest('.form-row').classList.remove('hidden');
        }
    };

    document.addEventListener('click', function (e) {
        if (event.target.classList.contains('special-request')) {
            e.preventDefault();
            const cid = event.target.getAttribute('data-cid');
            if (!cid) {
                active_modal('modal-login');
            } else {
                this.load(cid);
            }
        }
    }.bind(this));

    this.el.addEventListener('submit', this.submit.bind(this));
    this.el.querySelector('#special-request-nearby').addEventListener('change', this.checkNearby.bind(this));
    this.el.querySelector('#special-request-roomType').addEventListener('change', this.checkRoomType.bind(this));
    $(this.el).find("#special-request-resort").autocomplete({
        source: gpx_base.url_ajax + '?action=gpx_autocomplete_location_resort',
        minLength: 0,
        change: function (event, ui) {
            document.getElementById('special-request-region').value = '';
            document.getElementById('special-request-city').value = '';
            if (!ui.item) {
                this.value = '';
            }
            this.checkResort();
            this.checkLocation();
        }.bind(this),
    }).focus(function () {
        $(this).autocomplete("search");
    });
    $(this.el).find("#special-request-region").autocomplete({
        source: gpx_base.url_ajax + '?action=gpx_autocomplete_sr_location',
        minLength: 0,
        change: function (event, ui) {
            document.getElementById('special-request-resort').value = '';
            document.getElementById('special-request-city').value = '';
            if (!ui.item) {
                this.value = '';
            }
            this.checkLocation();
            this.checkResort();
        }.bind(this),
    }).focus(function () {
        $(this).autocomplete("search");
    });
    $(this.el).find("#special-request-city").autocomplete({
        source: function (request, response) {
            axios.get(gpx_base.url_ajax, {
                params: {
                    term: request.term,
                    action: 'gpx_autocomplete_location_sub',
                    region: document.getElementById('special-request-region').value,
                }
            })
                .then(function (data) {
                    response(data.data);
                });
        },
        minLength: 0,
        change: function (event, ui) {
            document.getElementById('special-request-resort').value = '';
            if (!ui.item) {
                this.value = '';
            }
            this.checkResort();
        }.bind(this),
    }).focus(function () {
        $(this).autocomplete("search");
    });
}


