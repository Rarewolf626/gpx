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
                if (response.data.show_availability) {
                    document.querySelector('#special-request-debug .intervals-count').textContent = response.data.intervals || 0;
                    document.querySelector('#special-request-debug .credits-count').textContent = response.data.credits || 0;
                    document.querySelector('#special-request-debug .requests-count').textContent = response.data.requests || 0;
                    if (response.data.unavailable) {
                        document.querySelector('#special-request-debug .message').textContent = 'No requests available.';
                    } else {
                        document.querySelector('#special-request-debug .message').textContent = '';
                    }
                    document.querySelector('#special-request-debug').classList.remove('hidden');
                } else {
                    document.querySelector('#special-request-debug').classList.add('hidden');
                }
                active_modal('modal-special-request');
            }.bind(this))
        ;
    };

    this.submit = function (e) {
        e.preventDefault();
        this.clear();
        const form = new FormData(this.el);
        axios.post(gpx_base.url_ajax + '?action=gpx_post_special_request', form)
            .then(function (response) {
                if (!response.data.success) {
                    return;
                }
                let results = document.createElement('div');
                results.innerHTML = document.getElementById('special-request-results').innerHTML
                results.querySelector('h3').textContent = response.data.message;
                if (response.data.matches.length > 0) {
                    let url = '/result?custom=' + response.data.matched;
                    results.querySelector('.matchedTravelButton').setAttribute('href', url);
                } else {
                    results.querySelector('.special-request-results-matched').remove();
                }
                alertModal.alert(results.innerHTML, true);
            }.bind(this))
            .catch(function (error) {
                if (error.response.status === 422) {
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
        ['resort', 'region', 'city', 'adults', 'children', 'nearby', 'email', 'roomType', 'larger', 'preference', 'checkIn', 'checkIn2']
            .forEach(function (field) {
                if (errors[field]) {
                    this.showError(this.el.querySelector(`.${field}-ac-error`), errors[field][0]);
                }
            }.bind(this));
    };

    this.checkResort = function () {
        let resort = this.el.querySelector('#special-request-resort').value;
        if (resort) {
            this.el.querySelector('#special-request-nearby').closest('.form-row').classList.remove('hidden');
        } else {
            this.el.querySelector('#special-request-nearby').closest('.form-row').classList.add('hidden');
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

    this.checkRoomType = function () {
        let value = this.el.querySelector('#special-request-roomType').value;
        if (value === 'Any') {
            this.el.querySelector('#special-request-larger').closest('.form-row').classList.add('hidden');
        } else {
            this.el.querySelector('#special-request-larger').closest('.form-row').classList.remove('hidden');
        }
    };

    document.addEventListener('click', function (e) {
        if (event.target.classList.contains('custom-request') || event.target.classList.contains('special-request')) {
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


function ViewCustomRequest(el) {
    this.el = el;
    this.template = wp.template('view-custom-request');

    this.load = function (rid) {
        axios.get(gpx_base.url_ajax, {params: {action: 'gpx_get_custom_request', rid: rid}})
            .then(function (response) {
                this.el.innerHTML = this.template(response.data);
                active_modal('modal-view-custom-request');
            }.bind(this))
        ;
    };

    document.addEventListener('click', function (e) {
        const link = event.target.closest('.edit-custom-request');
        if (link) {
            e.preventDefault();
            const rid = link.getAttribute('data-rid');
            if (!rid) {
                active_modal('modal-login');
            } else {
                this.load(rid);
            }
        }
    }.bind(this));
}
