$(function () {
    var tb;
    window.alertModal = new AlertModal();
    window.modals = new ModalManager();
    window.active_modal = modals.activate;

    modals.add('modal-login');
    modals.add('modal-pwreset');
    modals.add('modal-filter-resort');
    modals.add('modal-hold-alert');

    // ADA fix for popup close button
    $('.dgd_stb_box_close.dgd_stb_box_x').attr('aria-label', 'Close').append('<span class="ada-text">Close</span>');
    // ADA fix for google recaptcha
    $('textarea.g-recaptcha-response').attr('aria-hidden', true).attr('aria-label', 'ignore');

    $('html body').on('click', '.copyText', function () {
        var copy = $(this).find('.copy');
        var copyval = copy.text();
        copyToClipboard(copy);

        $(copy).hide();
        setTimeout(function () {
            $(copy).show();
        }, 300);
    });
    if ($('.load-results').length) {
        $('.load-results').each(function () {
            var thisel = $(this);
            var resort = thisel.data('resortid');

            var loadedresort = '#loaded-result-' + resort;
            var loadedcount = '#loaded-count-' + resort; // display count for current resort
            var loadedtotcount = '#loaded-totcount'; // total at top of page
            var loadedtopofresort = $(loadedcount).closest('.w-item-view'); // top of current resort
            var loadedresultcontent = '#results-content'; // container for resorts
            var loadedreschilds = $(loadedresultcontent).children('.w-item-view'); // all result rows for sort

            var monthstart = $(loadedcount).attr('data-monthstart');
            var monthend = $(loadedcount).attr('data-monthend');

            var thiscnt = 0;
            var totcnt = 0;

            $.post('/wp-admin/admin-ajax.php?action=gpx_resort_availability', {
                resortid: resort,
                limitstart: 0,
                limitcount: 8,
                monthstart: monthstart,
                monthend: monthend
            }, function (data) {
                if (data.html) {
                    $(loadedresort).html(data.html);

                    // grab count hidden in div at bottom of results
                    thiscnt = parseInt($("#res_count_" + resort).attr('data-res-count'));
                    $(loadedcount).html(thiscnt + ' Result');
                    // add an s to the end of Result, except for 1 result
                    if (thiscnt != 1) $(loadedcount).append('s');

                    // add prop cnt to top li for sorting
                    $(loadedtopofresort).attr({"data-propcount": thiscnt});

                    // update total props top of page
                    $(loadedreschilds).each(function () {
                        var propcount = parseInt($(this).attr('data-propcount'));
                        if (propcount >= 1) {
                            totcnt = parseInt(totcnt) + propcount;
                            $(loadedtotcount).html(totcnt + ' Search Results');
                        } else {
                            $(this).detach().appendTo('#results-content');
                        }
                    });

                } else {
                    thisel.hide();
                    thisel.closest('li').find('.hide-on-load').show();
                }
            });
        });
    }
    $('.vc_carousel-control').attr('aria-label', "controls");

    $('html body').on('click', '.extend-week', function (e) {
        e.preventDefault();
        $(this).closest('.extend-box').find('.extend-input').show();
    });
    $('html body').on('click', '.extend-btn', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var date = $(this).closest('.extend-input').find('.extend-date').val();
        $(this).closest('.extend-box').hide();
        $.ajax({
            url: '/wp-admin/admin-ajax.php?&action=gpx_extend_week',
            type: 'POST',
            data: {id: id, newdate: date},
            success: function (data) {
                if (data.error) {
                    alertModal.alert(data.error, false);
                    return;
                }

                if (data.cid) {
                    var id = data.cid;
                    var loading = 'load_transactions';
                    $.ajax({
                        method: 'GET',
                        url: '/wp-admin/admin-ajax.php?action=gpx_load_data',
                        data: {load: loading, cid: id},
                        success: function (data) {
                            $('#ownership').html(data.ownership);
                            $('#deposit').html(data.deposit);
                            $('#depositused').html(data.depositused);
                            $('#exchange').html(data.exchange);
                            $('#bnr').html(data.rental);
                            $('#misc').html(data.misc);
                            $('#creditBal').text(data.credit);
                            $('#holdweeks').html(data.hold);
                            $('.loading').hide();
                            if (tb) tb.destroy();
                            tb = $('.ajax-data-table').addClass('nowrap').dataTable({
                                responsive: true,
                                paging: true,
                                "order": [],
                                pageLength: 5,
                                "language": {
                                    "lengthMenu": "Display _MENU_ records per page",
                                    "zeroRecords": "Nothing found - sorry",
                                    "info": "of _PAGES_",
                                    "infoEmpty": "No records available",
                                    "infoFiltered": "(filtered from _MAX_ total records)"
                                },
                                columnDefs: [
                                    {
                                        //targets: [-1, -3],
                                        // className: 'dt-body-right'
                                    }
                                ]
                            });
                        },
                    });
                }
            }
        });
    });
    $('html body').on('click', '.pay-extension', function (e) {
        e.preventDefault();
        $(this).closest('.w-credit').addClass('make').find('.head-credit').addClass('not').removeClass('disabled').removeClass('disabeled').find('.exchange-credit-check').prop('disabled', false);
        $(this).remove();
    });
    $('html body').on('click', '.close-box', function (e) {
        e.preventDefault();
        $(this).closest('.extend-input').hide();
        $(this).closest('.donate-input').hide();
    });

    function copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).text()).select();
        document.execCommand("copy");
        $temp.remove();
    }

    $('html body').on('click', '.credit-donate-transfer', function (e) {
        e.preventDefault();
        var thistd = $(this).closest('td');
        var thisrow = $(this).closest('tr');
        var id = $(this).data('id');
        var type = $(this).data('type');
        $(thistd).text('');
        $.post('/wp-admin/admin-ajax.php?action=gpx_credit_action', {id: id, type: type}, function (data) {
            $(thisrow).find('td:nth-child(5)').text(data.action);
            window.location = '/view-profile';
        });
    });
    $('html body').on('click', '.remove-guest', function (e) {
        e.preventDefault();
        var transaction = $(this).data('id');
        $.post('/wp-admin/admin-ajax.php?action=gpx_remove_guest', {transactionID: transaction}, function (data) {
            if (data.success) {
                modals.closeAll();
                var id = data.cid;
                var loading = 'load_transactions';
                $.ajax({
                    method: 'GET',
                    url: '/wp-admin/admin-ajax.php?action=gpx_load_data',
                    data: {load: loading, cid: id},
                    success: function (data) {
                        $('#ownership').html(data.ownership);
                        $('#deposit').html(data.deposit);
                        $('#depositused').html(data.depositused);
                        $('#exchange').html(data.exchange);
                        $('#bnr').html(data.rental);
                        $('#misc').html(data.misc);
                        $('#creditBal').text(data.credit);
                        $('#holdweeks').html(data.hold);
                        $('.loading').hide();
                        tb.destroy();
                        tb = $('.ajax-data-table').addClass('nowrap').dataTable({
                            responsive: true,
                            paging: true,
                            "order": [],
                            pageLength: 5,
                            "language": {
                                "lengthMenu": "Display _MENU_ records per page",
                                "zeroRecords": "Nothing found - sorry",
                                "info": "of _PAGES_",
                                "infoEmpty": "No records available",
                                "infoFiltered": "(filtered from _MAX_ total records)"
                            },
                            columnDefs: [{}]
                        });
                    },
                });
            }
        });
    });
    $('html body').on('click', '.save-edit-transaction', function (e) {
        e.preventDefault();
        var transaction = $(this).data('transaction');
        var firstName = $('#tFirstName1').val();
        var lastName = $('#tLastName1').val();
        var email = $('#tEmail').val();
        var adults = $('#tAdults').val();
        var children = $('#tChildren').val();
        var link = $(this).attr('href') + ' #admin-modal-content';
        $.post('/wp-admin/admin-ajax.php?action=gpx_reasign_guest_name', {
            transactionID: transaction,
            FirstName1: firstName,
            LastName1: lastName,
            Email: email,
            Adults: adults,
            Children: children
        }, function (data) {
            if (data.paymentrequired) {
                $('.payment-msg').text('');
                $('#checkout-amount').val(data.amount);
                $('#checkout-item').val(data.type);
                alertModal.alert(data.html);
            } else {
                $('#modal-transaction .modal-body').load(link);
                var id = data.cid;
                var loading = 'load_transactions';
                $.ajax({
                    method: 'GET',
                    url: '/wp-admin/admin-ajax.php?action=gpx_load_data',
                    data: {load: loading, cid: id},
                    success: function (data) {
                        $('#ownership').html(data.ownership);
                        $('#deposit').html(data.deposit);
                        $('#depositused').html(data.depositused);
                        $('#exchange').html(data.exchange);
                        $('#bnr').html(data.rental);
                        $('#misc').html(data.misc);
                        $('#creditBal').text(data.credit);
                        $('#holdweeks').html(data.hold);
                        $('.loading').hide();
                        tb.destroy();
                        tb = $('.ajax-data-table').addClass('nowrap').dataTable({
                            responsive: true,
                            paging: true,
                            "order": [],
                            pageLength: 5,
                            "language": {
                                "lengthMenu": "Display _MENU_ records per page",
                                "zeroRecords": "Nothing found - sorry",
                                "info": "of _PAGES_",
                                "infoEmpty": "No records available",
                                "infoFiltered": "(filtered from _MAX_ total records)"
                            },
                            columnDefs: [{}]
                        });
                    },
                });
            }
        });


    });
    $('.slider-item.rsContent').each(function () {
        var img = $(this).find('img');
        var imgsrc = $(img).attr('src');
        $(this).css('background-image', 'url(' + imgsrc + ')');
        $(img).hide();
    });
    $('html body').on('click', '.menu-item-has-children', function (e) {
        $(this).find('ul.sub-menu').toggle();
    });

    if ($('.collapse').length) {
        //set the height
        var height = $('.collapse:first').find('li:first').outerHeight();
        var liwidth = $('.collapse:first').find('li:first').outerWidth();
        $('.collapse').css({height: height});
        //get the width of the container
        var containerwidth = $('.dgt-container:first').outerWidth();
        var lisperrow = Math.floor(containerwidth / liwidth);
        $('.collapse').each(function () {
            var els = $(this).find('li.item-result').length;
            if (els > lisperrow) {
                $(this).closest('li').css({paddingBottom: '50px'});
                $(this).closest('li').find('.hidden-more-button').css({
                    border: '1px solid #000',
                    fontSize: '22px',
                });
            } else {
                $(this).closest('li').find('.result-resort-availability').hide();
            }
        });
    }
    $('.result-resort-availability').click(function (e) {
        e.preventDefault();
        var rid = $(this).data('resortid');
        var height = $('.collapse:first').find('li:first').outerHeight();
        var chevron = $(this).closest('.w-item-view').find('.result-resort-availability i');
        if ($(chevron).hasClass('fa-chevron-down')) {
            $('#gpx-listing-result-' + rid).css({height: 'auto'});
            var newheight = $('#gpx-listing-result-' + rid).css("height");
            $('#gpx-listing-result-' + rid).css({height: height});
            $('#gpx-listing-result-' + rid).animate({
                height: newheight,
            }, 250);
        } else {
            $('#gpx-listing-result-' + rid).css({height: 'auto'});
            $('#gpx-listing-result-' + rid).animate({
                height: height,
            }, 250);
        }
        $(chevron).toggleClass('fa-chevron-down fa-chevron-up');
    });
    $('#owner-shared-main-gallery').slick({
        adaptiveHeight: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        fade: true,
    });
    $('#owner-shared-thumbnail-gallery').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        dots: false,
        arrows: true,
        focusOnSelect: true
    });
    $('#gallery_resort_main').slick({
        adaptiveHeight: true,
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: true,
        asNavFor: '#gallery_resort_thumbs'
    });
    $('#gallery_resort_thumbs').slick({
        slidesToShow: 3,
        slidesToScroll: 1,
        asNavFor: '#gallery_resort_main',
        dots: false,
        arrows: true,
        focusOnSelect: true
    });
    $('.carousel-slider').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        mobileFirst: true,
        responsive: [
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    infinite: true,
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2,
                    infinite: true,
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    infinite: true,
                }
            }
        ]
    });

    var crmindate = new Date();
    var crmaxdate = new Date(crmindate.getFullYear(), crmindate.getMonth() + 14, 0);
    $(".crrangepicker").daterangepicker({
        presetRanges: [{
            text: 'Today',
            dateStart: function () {
                return moment()
            },
            dateEnd: function () {
                return moment()
            }
        }, {
            text: 'This Month',
            dateStart: function () {
                return moment()
            },
            dateEnd: function () {
                return moment().add('months', 1)
            }
        }, {
            text: 'Next Month',
            dateStart: function () {
                return moment().add('months', 1)
            },
            dateEnd: function () {
                return moment().add('months', 2)
            }
        }],
        applyOnMenuSelect: false,
        datepickerOptions: {
            minDate: crmindate,
            maxDate: crmaxdate
        },
        dateFormat: 'mm/dd/yy',
    });

    $('html body').on('click', '.cr-cancel', function () {
        active_modal('modal-custom-request');
        return false;
    });
    $('html body').on('click', '.cr-finalize', function () {
        alertModal.alert('We will continue to monitor for weeks matching your search criteria.  You will receive an email notification when a match is found.<br>We look forward to helping you find your dream vacation!', true);
        return false;
    });
    if (document.getElementById('form-special-request')) {
        window.customRequest = new CustomRequestForm(document.getElementById('form-special-request'));
    }
    if (document.getElementById('view-custom-request')) {
        window.viewCustomRequest = new ViewCustomRequest(document.getElementById('view-custom-request'));
    }
    //switch custom request status
    $('html body').on('click', '.crActivate', function (e) {
        e.preventDefault();
        var thisel = $(this);
        var crid = $(this).data('crid');
        var craction = $(this).data('action');
        $.post('/wp-admin/admin-ajax.php?action=custom_request_status_change', {
            crid: crid,
            craction: craction
        }, function (data) {
            alertModal.alert("Custom Request Updated!");
        });
        var crswitch = 'No <a href="#" class="crActivate btn btn-secondary" data-crid="' + crid + '" data-action="activate">Enable</a>';
        if (craction == 'activate') {
            crswitch = 'Yes <a href="#" class="crActivate btn btn-secondary" data-crid="' + crid + '" data-action="deactivate">Disable</a>';
        }

        $(thisel).closest('td').html(crswitch);
    });
    $('.gpx_form_tooltip').click(function () {
        $(this).toggleClass('visible');
    });
    $('form.material').materialForm();
    /*-----------------------------------------------------------------------------------*/
    /* Royal Slider
     /*-----------------------------------------------------------------------------------*/
    $('#slider-home').royalSlider({
        autoHeight: false,
        autoScaleSlider: false,
        navigateByClick: false,
        sliderTouch: true,
        startSlideId: 0,
        controlNavigation: 'bullets',
        keyboardNavEnabled: true,
        imageScaleMode: 'fill',
        minSlideOffset: 0,
        slidesSpacing: 0,
        arrowsNav: true,
        transitionType: 'fade',
        autoPlay: {
            enabled: true,
            delay: 8500
        }
    });
    if ($('#gallery_resort').size() > 0) {
        $('#gallery_resort').royalSlider({
            fullscreen: {
                enabled: true,
                nativeFS: true
            },
            video: {},
            controlNavigation: 'thumbnails',
            autoScaleSlider: true,
            autoScaleSliderWidth: 400,
            autoScaleSliderHeight: 350,
            autoHeight: true,
            loop: true,
            imageScaleMode: 'fit',
            navigateByClick: true,
            numImagesToPreload: 2,
            arrowsNav: false,
            arrowsNavAutoHide: true,
            arrowsNavHideOnTouch: true,
            keyboardNavEnabled: true,
            fadeinLoadedSlide: true,
            globalCaption: true,
            globalCaptionInside: false,
            addActiveClass: true,
            touch: true,
            thumbs: {
                autoCenter: true,
                appendSpan: true,
                firstMargin: true,
                paddingBottom: 4,
                paddingTop: 4
            }
        });
    }
    /*-----------------------------------------------------------------------------------*/

    /* Scroll Magic
     /*-----------------------------------------------------------------------------------*/
    function createScrollMagic(element, animate, aclass) {
        if ($('body').hasClass('home')) {
            var controller = new ScrollMagic.Controller();
            var scene1 = new ScrollMagic.Scene({triggerElement: element})
                .setClassToggle(animate, aclass)
                .addTo(controller);
        }
    }

    createScrollMagic("#trigger1", "#animate1", "show1");
    createScrollMagic("#trigger2", "#animate2", "show2");
    createScrollMagic("#trigger3", "#animate3", "show3");
    /*-----------------------------------------------------------------------------------*/

    /* Sumo Select
     /*-----------------------------------------------------------------------------------*/
    function createSumoSelect(element) {
        if ((element).length > 0) {
            var sSelect = $(element).SumoSelect();
        }
    }

    var cSelect = $('#select_country').SumoSelect();
    var lSelect = $('#select_location').SumoSelect();
    createSumoSelect('select.dgt-select');
    /*-----------------------------------------------------------------------------------*/
    /* Location Select
     /*-----------------------------------------------------------------------------------*/
    $('#select_country').change(function () {
        $('#select_location').prop('disabled', false);
        $.get(gpx_base.url_ajax + '?action=gpx_newcountryregion_dd&country=' + $(this).val(), function (data) {
            $('#select_location').html(data);
            $('#select_location')[0].sumo.reload();
        });
    });
    $('.sumo_select_region .SelectBox .placeholder, .sumo_select_region .SelectBox i').click(function () {
        return false;
    });
    $('.submit-change').change(function () {
        var location = $(this).val();
        if ($.isNumeric(location)) {
            var country = $('#select_country').val();
            window.location.href = '/resorts-result/?select_country=' + country + '&select_region=' + location;
        }
    });
    /*-----------------------------------------------------------------------------------*/
    /* Result page main region drop down change (load month/year)
    /*-----------------------------------------------------------------------------------*/
    $('.result-page-form #select_location').change(function () {
        var country = $('#select_country').val();
        var region = $('#select_location').val();
        $.get(gpx_base.url_ajax + '?action=gpx_monthyear_dd&country=' + country + '&region=' + region, function (data) {
            $('#select_monthyear').html(data);
            $('#select_monthyear')[0].sumo.reload();
        });
    });
    /*-----------------------------------------------------------------------------------*/
    /* Result page main month/year drop down change (load content)
    /*-----------------------------------------------------------------------------------*/
    $('#select_monthyear').change(function () {
        var $form = $('#results-form').serialize()
        $.post(gpx_base.url_ajax + '?action=gpx_load_results_page_fn', $form, function (data) {
            $('#results-content').html(data.html);
        });
    });

    /*-----------------------------------------------------------------------------------*/

    /* Acordeon Expand
     /*-----------------------------------------------------------------------------------*/
    function acordeonExpand(element, parent) {
        var btn_item = $(element);
        var condition = false;
        btn_item.click(function (event) {
            event.preventDefault();
            $(this).addClass('activar');
            if (condition != true) {
                $(this).addClass('activar');
                condition = true;
            } else {
                btn_item.removeClass('activar');
                condition = false;
            }
            $(this).parent().find(parent).stop(false).slideToggle();
        });
    }

    acordeonExpand('#expand_1 .title', '.cnt-list');
    acordeonExpand('#expand_2 .title', '.cnt-list');
    acordeonExpand('#expand_3 .title', '.cnt-list');
    acordeonExpand('#expand_4 .title', '.cnt-list');


    $.ui.autocomplete.prototype.options.autoSelect = true;
    $(".ui-autocomplete-input").change(function (event) {
        var autocomplete = $(this).data("uiAutocomplete");

        if (!autocomplete.options.autoSelect || autocomplete.selectedItem) {
            return;
        }

        var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex($(this).val()) + "$", "i");
        autocomplete.widget().children(".ui-menu-item").each(function () {
            var item = $(this).data("uiAutocompleteItem");
            if (matcher.test(item.label || item.value || item)) {
                autocomplete.selectedItem = item;
                return false;
            }
        });

        if (autocomplete.selectedItem) {
            autocomplete._trigger("select", event, {item: autocomplete.selectedItem});
        }
    });


    /*-----------------------------------------------------------------------------------*/
    /* Autocomplete filter
     /*-----------------------------------------------------------------------------------*/

    $("#resort_autocomplete").autocomplete({
        source: gpx_base.url_ajax + '?action=gpx_autocomplete_resort',
        minLength: 0,
    }).focus(function () {
        $(this).autocomplete("search");
    });

    /*
      * custom render filters the results by resort and region
      */
    var customRenderMenu = function (ul, items) {
        var self = this;
        var categoryArr = [];

        function contain(item, array) {
            var contains = false;
            $.each(array, function (index, value) {
                if (item == value) {
                    contains = true;
                    return false;
                }
            });
            return contains;
        }

        $.each(items, function (index, item) {
            if (!contain(item.category, categoryArr)) {
                categoryArr.push(item.category);
            }
        });

        $.each(categoryArr, function (index, category) {
            ul.append("<li> -- " + category + " -- </li>");
            $.each(items, function (index, item) {
                if (item.category == category) {
                    self._renderItemData(ul, item);
                }
            });
        });
    };


    $("#location_autocomplete, .location_autocomplete").autocomplete({
        source: gpx_base.url_ajax + '?action=gpx_autocomplete_location',
        minLength: 0,
        autoFocus: true,
    }).focus(function () {
        $(this).autocomplete("search");
    });

    $('#search-location').select2({
        placeholder: $('#search-location').attr('placeholder'),
        selectionCssClass: 'search-location-select2',
        dropdownCssClass: 'search-location-select2-dropdown',
        ajax: {
            url: gpx_base.url_ajax + '?action=gpx_autocomplete_location',
            dataType: 'json',
            processResults: function (data) {
                let selected = this.$element.val();
                if (selected && !data.find(item => item.value === selected)) {
                    data.unshift({label: this.$element.val(), value: this.$element.val()})
                }
                return {
                    results: data.map(item => ({id: item.value, text: item.label})),
                };
            },
            data: function (params) {
                let search = params.term || '';
                if (search.length === 0) {
                    search = $(this).val();
                }
                return {
                    term: search,
                    _type: 'query',
                };
            }
        }
    });
    $('#search-location').on('select2:open', function (e) {
        // Do something
        document.querySelector('.search-location-select2-dropdown .select2-search__field').focus();
    });

    $(".location_autocomplete_cr_region").autocomplete({
        source: gpx_base.url_ajax + '?action=gpx_autocomplete_sr_location',
        minLength: 0,
        change: function (event, ui) {
            if (!ui.item) {
                $(".location_autocomplete_cr_region").val("");
                $('.region-ac-error').show();
            }
        },
        focus: function () {
            $('.city-ac-error, .region-ac-error, .resort-ac-error').hide();
        }
    }).focus(function () {
        $(this).autocomplete("search");
    });
    $(".location_autocomplete_sub").autocomplete({
        source: function (request, response) {
            var $region = $('.autocomplete-region').val();
            $.ajax({
                url: gpx_base.url_ajax,
                method: 'GET',
                data: {
                    term: request.term,
                    action: 'gpx_autocomplete_location_sub',
                    region: $region,
                },
                success: function (data) {
                    response(data);
                }
            });
        },
        minLength: 0,
        change: function (event, ui) {
            if (!ui.item) {
                $(".location_autocomplete_sub").val("");
                $('.city-ac-error').show();
            }
        },
        focus: function () {
            $('.city-ac-error, .region-ac-error, .resort-ac-error').hide();
        }
    }).focus(function () {
        $(this).autocomplete("search");
    });
    $(".location_autocomplete_resort").autocomplete({
        source: function (request, response) {
            var $region = $('.location_autocomplete_sub').val();
            if ($region == '' || $region == 'undefined') {
                $region = $('.autocomplete-region').val();
            }
            $.ajax({
                url: gpx_base.url_ajax,
                method: 'GET',
                data: {
                    term: request.term,
                    action: 'gpx_autocomplete_location_resort',
                    region: $region,
                },
                success: function (data) {
                    response(data);
                }
            });
        },
        minLength: 0,
        change: function (event, ui) {
            if (!ui.item) {
                $(".location_autocomplete_resort").val("");
                $('.resort-ac-error').show();
            }

        },
        focus: function () {
            $('.city-ac-error, .resort-ac-error, .region-ac-error').hide();
        }
    }).focus(function () {
        $(this).autocomplete("search");
    });
    $('#location_autocomplete').on('keypress', function (e) {
        if (e.which == 13) {
            $('#ui-id-1 .ui-menu-item').first().trigger('click');
        }
    });

    $('.miles_container').hide();
    $('.cr-for-miles').blur(function () {
        crShowMiles();
    });

    function crShowMiles() {
        if ($('.location_autocomplete_resort').val()) {
            $('.miles_container').hide();
            $('#miles').prop('value', '');
            $('.location_autocomplete_cr_region').prop('disabled', true);
            $('.crLocality').prop('disabled', true);
        } else {
            $('.location_autocomplete_cr_region').prop('disabled', false);
            $('.crLocality').prop('disabled', false);
        }
        if ($('.location_autocomplete_sub').val()) {
            var loc = $('.location_autocomplete_sub').val();
            $.ajax({
                url: gpx_base.url_ajax,
                type: 'post',
                data: {action: 'gpx_get_location_coordinates', region: loc},
                success: function (data) {
                    if (data.success) {
                        $('.miles_container').show();
                    } else {
                        $('.miles_container').hide();
                    }
                }
            });
            $('.crResort').prop('disabled', true);
            $('.location_autocomplete_cr_region').prop('disabled', false);
            $('.crLocality').prop('disabled', false);
            return true;
        } else {
            $('.crResort').prop('disabled', false);
            $('.location_autocomplete_cr_region').prop('disabled', false);
            $('.crLocality').prop('disabled', false);
            return false;
        }
    }

    /*-----------------------------------------------------------------------------------*/

    /* See more items / Home
     /*-----------------------------------------------------------------------------------*/
    function seemoreItems(element, parent, clone) {
        $(element).click(function (event) {
            event.preventDefault();
            $.ajax({
                url: gpx_base.url_ajax,
                type: 'post',
                data: {action: 'gpx_load_more', type: clone},
                success: function (data, status) {
                    $(parent).append(data);
                }
            });
        });
    }

    seemoreItems('#filter-result', '#gpx-listing-result', 2);
    seemoreItems('#filter-resort', '.w-list.w-list-items', 3);

    /*******************************************************************/
    function seeMoreText(container, element) {

        var text = $(container),
            btn = $(element);
        $(container).each(function () {
            if ($(this).data('height')) {
                h = $(this).data('height');
            } else {
                h = $(this).prop('scrollHeight') + 20;
            }
            $(this).attr('data-height', h);
            if (h > 110) {
                btn.addClass('less');
            } else {
                btn.addClass('hidden');
            }
        });
        btn.click(function (event) {
            event.preventDefault();
            event.stopPropagation();
            h = text.data('height');
            if ($(this).hasClass('less')) {
                btn.removeClass('less');
                $(this).addClass('more');
                $(this).find('span').text('See less');
                text.animate({'height': h});
            } else {
                btn.addClass('less');
                btn.removeClass('more');
                btn.find('span').text('See more');
                text.animate({'height': '110px'});
            }
        });
    }

    seeMoreText('.item-tab-cnt', '.content-tabs .seemore');
    seeMoreText('#expand_item_1 .cnt-expand', '#expand_item_1 .cnt-seemore');
    seeMoreText('#expand_item_2 .cnt-expand', '#expand_item_2 .cnt-seemore');


    /*-----------------------------------------------------------------------------------*/
    /* Responsive Menu
     /*-----------------------------------------------------------------------------------*/
    var newmobilemenu = $('.nav-list').clone().appendTo('body').insertAfter('#footer').addClass('menu-responsive').removeClass('nav-list');
    $(newmobilemenu).attr('id', 'mobile-' + $(newmobilemenu).attr('id'));
    $(newmobilemenu).find('li').each(function () {
        var oldid = $(this).attr('id');
        var newid = 'mobile-' + oldid;
        $(this).attr('id', newid);
    });
    $('.menu-mobile').click(function (event) {
        event.preventDefault();
        $(this).addClass('active-menu-mobile');
        $('.menu-mobile-close').addClass('active-menu-mobile-close');
        $('.menu-responsive').addClass('active-menu');
        $('.r-overlay').addClass('active-overlay');
        $('.cnt-wrapper').addClass('active-cnt-wrapper');
        $('.footer').addClass('active-footer');
    });
    /*-----------------------------------------------------------------------------------*/
    /* Tabs Content
     /*-----------------------------------------------------------------------------------*/
    $('.head-tab ul li a').click(function (event) {
        event.preventDefault();
        $('.tabs a').removeClass('head-active');
        $(this).addClass('head-active');
        var id = $(this).data('id');
        $('.content-tabs .item-tab').removeClass('tab-active');
        $('#' + id).addClass('tab-active');
    });
    /*-----------------------------------------------------------------------------------*/
    /* ScrollTop
     /*-----------------------------------------------------------------------------------*/
    $('.scrolltop').click(function (event) {
        $('html, body').animate({scrollTop: 0}, 900);
    });
    $(window).scroll(function () {
        var preview = $(this).scrollTop();
        if (preview > 120) {
            $(".scrolltop").addClass("active");
        } else if (preview < 120) {
            $(".scrolltop").removeClass("active");
        }
    });
    /*-----------------------------------------------------------------------------------*/
    /* Modal close
     /*-----------------------------------------------------------------------------------*/
    $('.w-status .close').click(function (event) {
        event.preventDefault();
        var $this = $(this);
        var $modal = $this.closest('.w-item-view');
        $modal.addClass('remove-modal');
    });
    /*-----------------------------------------------------------------------------------*/
    /* Phone Alert / Active alert only Home
     /*-----------------------------------------------------------------------------------*/
    if ($('body').hasClass('home')) {
        if (document.getElementById('modal-alert')) {
            active_modal('modal-alert');
        }
    }

    /*-----------------------------------------------------------------------------------*/

    /* Responsive menu Sub nivel
     /*-----------------------------------------------------------------------------------*/
    function cerrar_submenu() {
        $('.menu-responsive .u-submenu').stop(false).slideUp();
    }

    $('.menu-responsive .abre-submenu').click(function (e) {
        e.preventDefault();
        $('.menu-responsive .abre-submenu').removeClass('active');
        $(this).addClass('active');
        cerrar_submenu();
        $(this).parent().find('.u-submenu').stop(false).slideToggle();
    });

    function cerrar_nav() {
        $('.menu-responsive').removeClass('active-menu');
        $('.r-overlay').removeClass('active-overlay');
        $('.menu-mobile-close').removeClass('active-menu-mobile-close');
        $('.menu-mobile').removeClass('active-menu-mobile');
        $('.cnt-wrapper').removeClass('active-cnt-wrapper');
        $('.footer').removeClass('active-footer');
    };
    $('.w-nav').on('click', '.menu-mobile-close', function (event) {
        event.preventDefault();
        cerrar_nav();
        cerrar_submenu();
    });
    $('.r-overlay').click(function () {
        cerrar_nav();
        cerrar_submenu();
    });
    /*-----------------------------------------------------------------------------------*/

    $('.close-modal').click(function (event) {
        modals.closeAll();
    });
    $('.call-modal-login').click(function (event) {
        event.preventDefault();
        active_modal('modal-login');
    });
    $('.call-modal-pwreset').click(function (event) {
        event.preventDefault();
        active_modal('modal-pwreset');
    });
    if ($('#signInError').length) {
        active_modal('modal-login');
    }
    $('.call-modal-filter-resort').click(function (event) {
        event.preventDefault();
        active_modal('modal-filter-resort');
    });
    $('html body').on('click', '.call-modal-edit-profile', function (event) {
        event.preventDefault();
        active_modal('modal-profile');
    });
    if ($('#modal-autocoupon').length) {
        active_modal('modal-autocoupon');
    }

    /*-----------------------------------------------------------------------------------*/
    /* Show and Close modal
     /*-----------------------------------------------------------------------------------*/
    setTimeout(function () {
        function calculate_progressbar_value() {
            var progressbar_select = $('.w-progress-line span.select');
            var progressbar_book = $('.w-progress-line span.book');
            var progressbar_pay = $('.w-progress-line span.pay');
            var progressbar_confirm = $('.w-progress-line span.confirm');
            switch (true) {
                case (progressbar_select.hasClass('active')):
                    $('.w-progress-line .line .progress').css('width', ' 21%');
                    break;
                case (progressbar_book.hasClass('active')):
                    $('.w-progress-line .line .progress').css('width', ' 41%');
                    break;
                case (progressbar_pay.hasClass('active')):
                    $('.w-progress-line .line .progress').css('width', ' 61%');
                    break;
                case (progressbar_confirm.hasClass('active')):
                    $('.w-progress-line .line .progress').css('width', ' 100%');
                    break;
            }
        }

        calculate_progressbar_value();
    }, 750);

    if ($('.booking-disabled-check').length) {
        var $msg = $('#bookingDisabledMessage').data('msg');
        alertModal.alert($msg);
        setTimeout(function () {
            window.location.href = '/';
        }, 3000);
    }
    $('.resort-btn').click(function (e) {
        e.preventDefault();
        var link = $(this).attr('href');
        var rid = $(this).data('rid');
        var cid = $(this).data('cid');
        var form = $('#home-search').serialize();
        form = form + "&rid=" + rid + "&cid=" + cid;
        $.post('/wp-admin/admin-ajax.php?action=gpx_resort_link_savesearch', form, function (data) {
            location.href = link;
        });
    });
    if ($('.checklogin').length) {
        $.get('/wp-admin/admin-ajax.php?action=gpx_check_login', function (data) {
            if (data.login) {
                modals.open('modal-login');
            }
        });
    }
    var crmindate = new Date();
    var crmaxdate = crmindate.getDate() + 547;
    $(".crrangepicker").daterangepicker({
        presetRanges: [{
            text: 'Today',
            dateStart: function () {
                return moment()
            },
            dateEnd: function () {
                return moment()
            }
        }, {
            text: 'This Month',
            dateStart: function () {
                return moment()
            },
            dateEnd: function () {
                return moment().add('months', 1)
            }
        }, {
            text: 'Next Month',
            dateStart: function () {
                return moment().add('months', 1)
            },
            dateEnd: function () {
                return moment().add('months', 2)
            }
        }],
        applyOnMenuSelect: false,
        datepickerOptions: {
            minDate: crmindate,
            maxDate: crmaxdate
        },
        dateFormat: 'mm/dd/yy',
    });
    $('html body').on('click', '.resend-confirmation', function (e) {
        e.preventDefault();
        var $this = $(this);
        var weekid = $(this).data('weekid');
        var memberno = $(this).data('memberno');
        var resortname = $(this).data('resortname');
        $($this).append('<i class="fa fa-refresh fa-spin fa-fw"></i>');
        $.get('/wp-admin/admin-ajax.php?action=gpx_resend_confirmation&weekid=' + weekid + '&memberno=' + memberno + '&resortname=' + resortname, function (data) {
            if (data.msg) {
                alertModal.alert(data.msg);
            }
            $($this).find('.fa-refresh').remove();
        });
    });

    function base64ToArrayBuffer(base64) {
        var binaryString = window.atob(base64);
        var binaryLen = binaryString.length;
        var bytes = new Uint8Array(binaryLen);
        for (var i = 0; i < binaryLen; i++) {
            var ascii = binaryString.charCodeAt(i);
            bytes[i] = ascii;
        }
        return bytes;
    }

    function saveByteArray(reportName, byte) {
        var blob = new Blob([byte]);
        var link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        var timeNow = new Date();
        var month = timeNow.getMonth() + 1;
        var fileName = reportName + ".pdf";
        link.download = fileName;
        link.click();
    };
    $('#removeCoupon').click(function (e) {
        e.preventDefault();
        var cid = $(this).data('cid');
        var cartID = $(this).data('cartid');
        $.post('/wp-admin/admin-ajax.php?action=gpx_remove_coupon', {cid: cid, cartID: cartID}, function () {
            location.reload();
        });
    });
    $('#removeOwnerCreditCoupon').click(function (e) {
        e.preventDefault();
        var cid = $(this).data('cid');
        var cartID = $(this).data('cartid');
        $.post('/wp-admin/admin-ajax.php?action=gpx_remove_owner_credit_coupon', {
            cid: cid,
            cartID: cartID
        }, function () {
            location.reload();
        });
    });

    $('html body').on('click', '.remove-hold', function (e) {
        e.preventDefault();
        var pid = $(this).data('pid');
        var cid = $(this).data('cid');
        var el = $(this);
        var bp = $(this).data('bookingpath');
        var redirect;
        var nocart = '';
        if (bp == 1) {
            redirect = $(this).data('redirect');
        } else {
            nocart = '&nocart=1';
        }
        $(this).append('<i class="fa fa-refresh fa-spin fa-fw"></i>');
        $.get('/wp-admin/admin-ajax.php?action=gpx_delete_user_week_hold&pid=' + pid + '&cid=' + parseInt(cid) + nocart, function (data) {
            if (bp == 1) {
                window.location.href = redirect;
            } else {
                $(el).closest('tr').remove();
            }
        });
    });
    $('.check input').change(function () {
        var $input = $(this);
        var $check = $(this).closest('.check');
        if ($($check).hasClass('error')) {
            if ($($input).is(':checked')) {
                $($check).removeClass('error');
            }
        }
    });

    $('#email.validate').blur(function () {
        var valemail = $(this).val();
        if (!isEmail(valemail)) {
            alertModal.alert('Please enter a valid email address.', false);
        }
    });

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    $('html body').on('click', '.toggleElement', function (e) {
        e.preventDefault();
        var link = $(this).attr('href');
        $(link).toggle();
    });

    /*-----------------------------------------------------------------------------------*/
    /* Material label focus
     /*-----------------------------------------------------------------------------------*/
    $('.material-input label').click(function (event) {
        event.preventDefault();
        var $this = $(this);
        var $wrapper = $this.closest('.material-input');
        var $child = $wrapper.find('input');
        $child.focus();
    });
    /*-----------------------------------------------------------------------------------*/
    /* Resort search form -- redirect to resort page if resort name is used
     /*-----------------------------------------------------------------------------------*/
    $('#resortsSearchForm').submit(function () {
        var resort = $('#resort_autocomplete').val();
        var country = $('#select_country').val();
        if (resort.length == 0) {
            if (country == null) {
                alertModal.alert("Resort Name or Location are required!", false);
                return false;
            } else {
                return true;
            }

        } else {
            encoderesort = encodeURIComponent(resort);
            window.location.href = "/resort-profile/?resortName=" + encoderesort;
            return false;
        }
    });
    /*-----------------------------------------------------------------------------------*/
    /* Resort availability
     /*-----------------------------------------------------------------------------------*/
    $('.resort-availablity-view').hide();
    $('.resort-availability').click(function (e) {
        e.preventDefault();
        var $this = $(this);
        var $thisi = $(this).find('i');
        var resortid = $(this).data('resortid');
        var rav = $(this).closest('.w-item-view').find('.resort-availablity-view');
        $(rav).toggle();
        $($thisi).toggleClass('fa-chevron-down fa-chevron-up');
        if (!$($this).hasClass('resort-availability-toggle')) {
            $(rav).find('.ra-loading').addClass('fa fa-refresh fa-spin');
            $.post('/wp-admin/admin-ajax.php?action=gpx_resort_availability', {resortid: resortid}, function (data) {
                $(rav).find('.ra-content').html(data.html);
                $(rav).find('.ra-loading').removeClass('fa fa-refresh fa-spin');
                $($this).addClass('resort-availability-toggle');
                $('.filter_resort_resorttype').trigger('change');
            });
        }
    });
    if ($('#availability-cards').length) {
        var resortid = $('#show-availability').data('resortid');
        var month = $('#show-availability').data('month');
        var year = $('#show-availability').data('year');
        $.post('/wp-admin/admin-ajax.php?action=gpx_resort_availability', {
            resortid: resortid,
            limitstart: 0,
            limitcount: 4,
            month: month,
            yr: year
        }, function (data) {
            $('#availability-cards').html(data.html);
            $('#resort-availability-filter-summary').removeClass('hidden');
        });
    }

    $('html body').on('click', '.show-more-btn', function (e) {
        e.preventDefault();
        var limitcount = '10000';
        var ww = $(window).width();
        if (ww < 768)
            limitcount = $(this).data('next');
        var resortid = $('#show-availability').data('resortid');
        var month = $('#show-availability').data('month');
        var year = $('#show-availability').data('year');
        $.post('/wp-admin/admin-ajax.php?action=gpx_resort_availability', {
            resortid: resortid,
            limitstart: 0,
            limitcount: limitcount,
            month: month,
            yr: year
        }, function (data) {
            $('#availability-cards').html(data.html);
        });
    });

    /*-----------------------------------------------------------------------------------*/
    /* Resort availability calendar
     /*-----------------------------------------------------------------------------------*/
    $('.resort-availablility').hide();
    if ($('#cid').length) {
        var cidset = $('#cid').data('cid');
    }
    $('.show-availabilty').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#resort-availability-filter-summary').removeClass('hidden');
        $('html, body').animate({
            scrollTop: $('#expand_4').offset().top - 100
        }, 1000);
        $('#expand_4 .cnt-list, #availability-cards, .search-availability').show();
        $('#expand_4 .show-availability-btn, .resort-availablility').hide();

    });
    $('#resort-calendar-filter').submit(function (e) {
        e.preventDefault();
        $('#resort-calendar').fullCalendar('refetchEvents');
    });
    $('.search-availability').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#resort-availability-filter-summary').addClass('hidden');
        let date = $('#calendar-year').val() + '-' + $('#calendar-month').val() + '-01';
        $('#resort-calendar').fullCalendar({
            lazyFetching: false,
            defaultDate: date,
            header: false,
            defaultView: 'month',
            eventSources: [
                {
                    id: 'ExchangeWeek',
                    color: '#8906D5',
                    textColor: 'white',
                    events: (start, end, timezone, callback) => {
                        const form = document.getElementById('resort-calendar-filter');
                        const search = new URLSearchParams($(form).serialize());
                        if (search.get('WeekType') === 'RentalWeek') {
                            callback([]);
                            return;
                        }
                        search.set('WeekType', 'ExchangeWeek');
                        search.set('start', start.format('Y-MM-DD'));
                        search.set('end', end.format('Y-MM-DD'));
                        fetch(form.getAttribute('action') + '?' + search.toString())
                            .then(response => response.json())
                            .then(data => {
                                if (!data.success) {
                                    callback([]);
                                    return;
                                }
                                callback(data.events);
                            });
                    },
                },
                {
                    id: 'RentalWeek',
                    color: '#EC8F0A',
                    textColor: 'white',
                    events: (start, end, timezone, callback) => {
                        const form = document.getElementById('resort-calendar-filter');
                        const search = new URLSearchParams($(form).serialize());
                        if (search.get('WeekType') === 'ExchangeWeek') {
                            callback([]);
                            return;
                        }
                        search.set('WeekType', 'RentalWeek');
                        search.set('start', start.format('Y-MM-DD'));
                        search.set('end', end.format('Y-MM-DD'));
                        fetch(form.getAttribute('action') + '?' + search.toString())
                            .then(response => response.json())
                            .then(data => {
                                if (!data.success) {
                                    callback([]);
                                    return;
                                }
                                callback(data.events);
                            });
                    },
                }
            ]
        });
        $('#expand_4 .cnt-list, .resort-availablility').show();
        $('#expand_4 .search-availablity, #availability-cards').hide();
        $('.cal-av-toggle').toggle();
        $('html, body').animate({
            scrollTop: $('#expand_4').offset().top - 100
        }, 1000);
    });
    $('.resort-calendar').on('click', '.resort-calendar-nav', function (e) {
        e.preventDefault();
        let date = moment($('#calendar-year').val() + '-' + $('#calendar-month').val() + '-01');
        if ($(this).data('direction') === 'prev') {
            date.subtract(1, 'month');
        } else {
            date.add(1, 'month');
        }
        const min = moment().startOf('month').month(0);
        if (date.isBefore(min, 'month')) {
            date = min;
        }
        const max = moment().startOf('month').month(11).year(new Date().getFullYear() + 3);
        if (date.isAfter(max, 'month')) {
            date = max;
        }
        $('#resort-calendar-title').text(date.format('MMMM YYYY'));
        $('#calendar-year').val(date.format('YYYY'));
        $('#calendar-month').val(date.format('MM'));
        $('#resort-calendar').fullCalendar('gotoDate', date.format('YYYY-MM-DD'));
    });
    $('html body').on('focus', '.emailvalidate', function () {
        if (!$('#oldvalue').length) {
            var oldval = $(this).val();
            $(this).parent().append('<span id="oldvalue" data-val="' + oldval + '"></span>');
        }
    });
    $('html body').on('keyup', '.emailvalidate', function () {
        $('.edit-profile-btn').prop('disabled', true).addClass('gpx-disabled');
        var parent = $(this).parent();
        if (!$('#emailValidateBtn').length)
            $('<a href="#" id="emailValidateBtn">Validate Email</a>').insertAfter(parent);
    });
    $('.emailvalidate').blur(function () {
        var email = $(this).val();
        var oldval = $('#oldvalue').data('val');
        $('#emailValidateBtn').remove();
        if (email != oldval) {
            $.post('/wp-admin/admin-ajax.php?action=gpx_validate_email', {email: email}, function (data) {
                if (data.error) {
                    $('.emailvalidate').val(oldval);
                    alertModal.alert(data.error + '<br><a href="#" class="call-modal-edit-profile">Try Again</a>');
                }
                $('.edit-profile-btn').prop('disabled', false).removeClass('gpx-disabled');
            });
        } else {
            $('.edit-profile-btn').prop('disabled', false).removeClass('gpx-disabled');
        }
    });
    $('html body').on('click', '#emailValidateBtn', function (e) {
        e.preventDefault();
    });
    $('html body').on('change', '#calendar-type', function () {
        $('#resort-calendar').fullCalendar('refetchEvents');
    });
    $('html body').on('change', '#calendar-bedrooms', function () {
        $('#resort-calendar').fullCalendar('refetchEvents');
    });
    $('html body').on('change', '#calendar-month, #calendar-year', function () {
        let date = moment($('#calendar-year').val() + '-' + $('#calendar-month').val() + '-01');
        $('#resort-calendar-title').text(date.format('MMMM YYYY'));
        $('#resort-calendar').fullCalendar('gotoDate', date.format('YYYY-MM-DD'));
    });

    /*-----------------------------------------------------------------------------------*/
    /* Demo user login active
     /*-----------------------------------------------------------------------------------*/
    if ($('body').hasClass('active-session')) {
        $('.header .top-nav .access .call-modal-login').text('Sign out');
    }
    if ($('#welcome_create').length) {
        var wh = $('#welcome_create').data('wc');

        $.get('/wp-admin/admin-ajax.php?action=get_username_modal', function (data) {
            $('#form-login .gform_body').html(data.html);
            $('#form-login').append('<input type="hidden" name="wh" value="' + wh + '" />');
            $('#btn-signin').attr('value', 'Update');
            $('#btn-signin').removeClass('btn-user-login');
            $('input[name="action"]').attr('value', 'update_username');
            $('.call-modal-pwreset').hide();
            modals.open('modal-login')
        });
    }
    $('html body').on('submit', '#form-login, #form-login-footer', function (e) {
        e.preventDefault();
        var thisform = $(this);

        var btn = $(this).find('#btn-signin');
        if ($(this).find('#btn-signin').hasClass('btn-user-login')) {
            grecaptcha.ready(function () {
                grecaptcha.execute(window.RECAPTCHA_SITE_KEY, {action: 'login'}).then(function (token) {
                    $(thisform).find('input[name=rec_token]').remove();
                    $(thisform).find('input[name=rec_action]').remove();
                    $(thisform).prepend('<input type="hidden" name="rec_token" value="' + token + '">');
                    $(thisform).prepend('<input type="hidden" name="rec_action" value="login">');

                    $.ajax({
                        url: gpx_base.url_ajax,
                        type: "POST",
                        dataType: "json",
                        data: $(thisform).serialize(),
                        success: function (response) {
                            if (response.loggedin) {
                                if (response.redirect_to == 'username_modal') {
                                    $.get('/wp-admin/admin-ajax.php?action=get_username_modal', function (data) {
                                        $('#form-login .gform_body').html(data.html);
                                        $('#btn-signin').attr('value', 'Update');
                                        $('#btn-signin').removeClass('btn-user-login');
                                        $('input[name="action"]').attr('value', 'update_username');
                                        $('.call-modal-pwreset').hide();
                                        modals.open('modal-login')
                                    });
                                } else {
                                    window.location.href = response.redirect_to;
                                }

                            } else {
                                $('.message-box span').html(response.message);
                            }
                        }
                    });
                });
            });
        } else {
            grecaptcha.ready(function () {
                grecaptcha.execute(window.RECAPTCHA_SITE_KEY, {action: 'password_reset'}).then(function (token) {
                    $(thisform).find('input[name=rec_token]').remove();
                    $(thisform).find('input[name=rec_action]').remove();
                    $(thisform).prepend('<input type="hidden" name="rec_token" value="' + token + '">');
                    $(thisform).prepend('<input type="hidden" name="rec_action" value="password_reset">');

                    $.ajax({
                        url: gpx_base.url_ajax,
                        type: "POST",
                        data: $(thisform).serialize(),
                        success: function (response) {
                            if (response.success) {
                                $('.message-box span').html('Updated!');
                                setTimeout(function () {
                                    window.location.href = '/?login_again';
                                }, 1500)
                            } else {
                                $('.message-box span').html(response.msg);
                            }
                        }
                    });
                });
            });

        }
        return false;
    });
    if ($('#recred').length) {
        $('.message-box span').html('Please login with your new credentials.');
        $('.signin').trigger('click');
    }
    $("#form-pwreset").submit(function (e) {
        e.preventDefault();
        var thisform = $(this);
        $('.message-box span').html('<i class="fa fa-spinner fa-spin"></i>');
        thisform.find('input[type=submit],button[type=submit]').prop('disabled', true);
        grecaptcha.ready(function () {
            grecaptcha.execute(window.RECAPTCHA_SITE_KEY, {action: 'password_reset'}).then(function (token) {
                $(thisform).find('input[name=rec_token]').remove();
                $(thisform).find('input[name=rec_action]').remove();
                $(thisform).prepend('<input type="hidden" name="rec_token" value="' + token + '">');
                $(thisform).prepend('<input type="hidden" name="rec_action" value="password_reset">');
                $.ajax({
                    url: gpx_base.url_ajax + '?action=request_password_reset',
                    type: "POST",
                    data: $(thisform).serialize(),
                    success: function (response) {
                        thisform.find('input[type=submit],button[type=submit]').prop('disabled', false);
                        let message = response.success || 'No account found with the provided username.';
                        $('.message-box span').html(message);
                    }
                });
            });
        });
    });
    $("#form-pwset").submit(function (e) {
        e.preventDefault();
        $('.message-box span').empty();
        const $form = $(this);
        grecaptcha.ready(function () {
            grecaptcha.execute(window.RECAPTCHA_SITE_KEY, {action: 'set_password'}).then(function (token) {
                $form.find('input[name=rec_token]').remove();
                $form.find('input[name=rec_action]').remove();
                $form.prepend('<input type="hidden" name="rec_token" value="' + token + '">');
                $form.prepend('<input type="hidden" name="rec_action" value="set_password">');
                $.ajax({
                    url: gpx_base.url_ajax,
                    type: "POST",
                    data: $form.serialize(),
                    success: function (response) {
                        if (!response.success) {
                            let message = response.data.msg || 'You used an invalid login.  Please request a new reset.';
                            $('.message-box span').html(message);
                            return;
                        }

                        if (response.data.action == 'login') {
                            modals.open('modal-login');
                            if (response.data.redirect) {
                                $('#redirectTo').val(response.data.redirect);
                            }
                        }
                        if (response.data.action == 'pwreset') {
                            $('#form-pwreset').show();
                            $('#form-pwset').hide();
                        }

                    }
                });
            });
        });
    });
    $('.special-link').click(function () {
        active_modal($(this).attr('href'));
        return false;
    });
    $(document).on('click', '.better-modal-link', function (e) {
        e.preventDefault();
        var modal = $(this).attr('href');
        active_modal(modal);
        $('html, body').animate({scrollTop: 90}, 'slow');
    });
    $('.data-modal').click(function () {
        var htmllink = $(this).data('html');
        if (htmllink) {
            var $text = $(htmllink).html();
        } else {
            var $text = $(this).data('text');
        }
        alertModal.alert($text);
        return false;
    });
    $('html body').on('click', '.data-modal', function (e) {
        e.preventDefault();
        var $text = $(this).data('text');
        alertModal.alert($text);
    });
    $('#checkin-btn').click(function (e) {
        e.preventDefault();
        $('.comiseo-daterangepicker-triggerbutton').show();
        $('#rangepicker').daterangepicker("open");
    });

    $('.filter_city').change(function () {
        var findarr = $(this).val();
        var search = '.w-item-view';
        var filter = '';
        var type = $(this).data('filter');
        var results = [];
        var find = '';
        $.each(findarr, function (key, value) {
            find = value;
            $(search).each(function () {
                filter = $(this).data(type);
                if (jQuery.inArray(find, filter) != -1) {
                    results.push($(this).attr('id'));
                }
            });
        });
        if (results.length) {
            $('.w-item-view').removeClass('filtered');
            $.each(results, function (key, value) {
                $('#' + value).addClass('filtered');
            });
        } else {
            $('.w-item-view').addClass('filtered');
        }
    });
    $('.filter_resort_city').change(function () {
        var findarr = $(this).val();
        var search = '.w-item-view';
        var filter = '';
        var type = $(this).data('filter');
        var results = [];
        var find = '';
        var allsearch = [];
        if ($('.aiFiltered').length)
            allsearch.push('.aiFiltered');
        if ($('.typeFiltered').length)
            allsearch.push('.typeFiltered');
        if (allsearch.length)
            search = allsearch.join('');
        $.each(findarr, function (key, value) {
            find = value;
            $(search).each(function () {
                filter = $(this).data(type);
                if (jQuery.inArray(find, filter) != -1) {
                    results.push($(this).attr('id'));
                }
            });
        });
        if (results.length) {
            $('.w-item-view').removeClass('filtered').hide();
            $.each(results, function (key, value) {
                $('#' + value).addClass('filtered').show();
            });
        } else {
            $('.w-item-view').addClass('filtered').show();
        }
    });
    $('html body').on('change', '.filter_resort_resorttype', function () {
        var findarr = [];
        var allsearch = [];
        var type = $(this).data('filter');
        $('.filter_resort_resorttype').each(function () {
            if (this.checked) {
                findarr.push($(this).val());
            }
        });
        var find = '';
        var search = '.filtered';
        if ($('.aiFiltered').length)
            allsearch.push('.aiFiltered');
        if (allsearch.length)
            search = allsearch.join('');
        var filter = '';
        var results = [];
        $.each(findarr, function (key, value) {
            find = value;
            $(search).each(function () {
                filter = $(this).data(type);
                if (jQuery.inArray(find, filter) != -1) {
                    results.push($(this).attr('id'));
                }
            });
        });
        $(search).show();
        $(search).removeClass('typeFiltered').hide();
        if (results.length) {
            $.each(results, function (key, value) {
                $('#' + value).show().addClass('typeFiltered');
            });
        }
        if (!findarr.length) {
            $(search).show();
        }
        var searchit = '.filtered .item-result';
        if ($('.aiFiltered').length)
            allsearch.push('.aiFiltered');
        if (allsearch.length)
            searchit = allsearch.join('');
        var filter = '';
        var results = [];
        $.each(findarr, function (key, value) {
            find = value;
            $(searchit).each(function () {
                filter = $(this).data(type);
                if (jQuery.inArray(find, filter) == 0) {
                    results.push($(this));
                }
            });
        });
        $('.filtered').show();
        $(searchit).removeClass('typeFiltered').hide();
        if (results.length) {
            $.each(results, function (key, value) {
                $(value).show().addClass('typeFiltered');
            });
        }
        if (!findarr.length) {
            $(searchit).show();
        }
    });
    $('html body').on('change', '.filter_resort_ai', function () {
        var findarr = [];
        var allsearch = [];
        var type = $(this).data('filter');
        $('.filter_resort_ai').each(function () {
            if (this.checked) {
                findarr.push($(this).val());
            }
        });
        var find = '';
        var search = '.filtered';
        if ($('.typeFiltered').length)
            allsearch.push('.typeFiltered');
        if (allsearch.length)
            search = allsearch.join('');
        var filter = '';
        var results = [];
        $.each(findarr, function (key, value) {
            find = value;
            $(search).each(function () {
                filter = $(this).data(type);
                if (jQuery.inArray(find, filter) != -1) {
                    results.push($(this).attr('id'));
                }
            });
        });
        $(search).show();
        $(search).removeClass('aiFiltered').hide();
        if (results.length) {
            $.each(results, function (key, value) {
                $('.filtered').removeClass('aiFiltered').hide();
                $('#' + value).show().addClass('aiFiltered');
            });
        }
        if (!findarr.length) {
            $(search).show();
        }
        var searchit = '.filtered .item-result';
        if ($('.typeFiltered').length)
            allsearch.push('.typeFiltered');
        if (allsearch.length)
            searchit = allsearch.join('');
        var filter = '';
        var results = [];
        $.each(findarr, function (key, value) {
            find = value;
            $(searchit).each(function () {
                filter = $(this).data(type);
                if (jQuery.inArray(find, filter) == 0) {
                    results.push($(this));
                }
            });
        });
        $('.filtered').show();
        $(searchit).removeClass('aiFiltered').hide();
        if (results.length) {
            $.each(results, function (key, value) {
                $(value).show().addClass('aiFiltered');
            });
        }
        if (!findarr.length) {
            $(searchit).show();
        }
    });
    if ($('.item-result').length) {
        var mindate = '';
        var maxdate = new Date();
        $('.item-result').each(function () {
            var hasdate = $(this).data('date');
            if (hasdate.length) {
                var mds = hasdate.split('-');
                var thisDate = new Date(mds[0], parseInt(mds[1]) - 1, mds[2]);
                if (mindate == '')
                    mindate = thisDate;
                if (thisDate < mindate)
                    mindate = thisDate;
                if (thisDate > maxdate)
                    maxdate = thisDate;
            }
        });
    }
    $('.datepicker').datepicker();
    (function () {
        var dpToday = new Date();
        var dpmm = dpToday.getMonth() + 1;
        var dpyyyy = dpToday.getFullYear() + 1;
        var dpMaxDate = new Date(dpyyyy, dpmm, 0);
        $('.maxdatepicker').datepicker({
            minDate: 0,
            maxDate: dpMaxDate,
            onSelect: function () {
                $(this).addClass('filled');
            }
        });
    })();
    $("#rangepicker").daterangepicker({
        presetRanges: [{
            text: 'Today',
            dateStart: function () {
                return moment()
            },
            dateEnd: function () {
                return moment()
            }
        }, {
            text: 'This Month',
            dateStart: function () {
                return moment()
            },
            dateEnd: function () {
                return moment().add('months', 1)
            }
        }, {
            text: 'Next Month',
            dateStart: function () {
                return moment().add('months', 1)
            },
            dateEnd: function () {
                return moment().add('months', 2)
            }
        }],
        applyOnMenuSelect: false,
        datepickerOptions: {
            minDate: mindate,
            maxDate: maxdate
        },
        dateFormat: 'yy-mm-dd',
        change: function (event, data) {
            var start = data.instance.getRange().start,
                end = data.instance.getRange().end,
                find = '',
                filter = '',
                search = '.item-result',
                results = [],
                allsearch = [];
            if ($('.typeFiltered').length)
                allsearch.push('.typeFiltered');
            if ($('.sizeFiltered').length)
                allsearch.push('.sizeFiltered');
            if ($('.aiFiltered').length)
                allsearch.push('.aiFiltered');
            if (allsearch.length)
                search = allsearch.join('');
            $(search).each(function () {
                filter = $(this).data('date');
                var fs = filter.split('-');
                var filteredDate = new Date(fs[0], parseInt(fs[1]) - 1, fs[2]);
                if (filteredDate <= end && filteredDate >= start) {
                    results.push($(this).attr('id'));
                }
            });
            $('.filtered').show();
            $(search).removeClass('dateFiltered').hide();
            if (results.length) {
                $.each(results, function (key, value) {
                    $('#' + value).show().addClass('dateFiltered');
                });
            }
            $('.filtered').each(function () {
                var parel = $(this);
                if ($(parel).find('.w-list-result').children(':visible').length == 0) {
                    $(parel).hide();
                }
            });
        },
    });
    $('.filter_size').change(function () {
        var findarr = [];
        var allsearch = [];
        var type = $(this).data('filter');
        $('.filter_size').each(function () {
            if (this.checked) {
                findarr.push($(this).val());
            }
        });
        var find = '';
        var search = '.filtered .item-result';
        if ($('.typeFiltered').length)
            allsearch.push('.typeFiltered');
        if ($('.dateFiltered').length)
            allsearch.push('.dateFiltered');
        if ($('.aiFiltered').length)
            allsearch.push('.aiFiltered');
        if (allsearch.length)
            search = allsearch.join('');
        var filter = '';
        var results = [];
        $.each(findarr, function (key, value) {
            find = value;
            $(search).each(function () {
                filter = $(this).data(type);
                if (jQuery.inArray(find, filter) != -1) {
                    results.push($(this).attr('id'));
                }
            });
        });
        $('.filtered').show();
        $(search).removeClass('sizeFiltered').hide();
        if (results.length) {
            $.each(results, function (key, value) {
                $('#' + value).show().addClass('sizeFiltered');
            });
        }
        if (!findarr.length) {
            $(search).show();
        }
        $('.filtered').each(function () {
            var parel = $(this);
            if ($(parel).find('.w-list-result').children(':visible').length == 0) {
                $(parel).hide();
            }
        });

    });
    $('.filter_resorttype').change(function () {
        var findarr = [];
        var allsearch = [];
        var type = $(this).data('filter');
        $('.filter_resorttype').each(function () {
            if (this.checked) {
                findarr.push($(this).val());
            }
        });
        var find = '';

        var searchit = '.filtered .item-result';
        if ($('.sizeFiltered').length)
            allsearch.push('.sizeFiltered');
        if ($('.dateFiltered').length)
            allsearch.push('.dateFiltered');
        if ($('.aiFiltered').length)
            allsearch.push('.aiFiltered');
        if (allsearch.length)
            searchit = allsearch.join('');
        var filter = '';
        var results = [];
        $.each(findarr, function (key, value) {
            find = value;
            $(searchit).each(function () {
                filter = $(this).data(type);
                if (jQuery.inArray(find, filter) == 0) {
                    results.push($(this));
                }
            });
        });
        $('.filtered').show();
        $(searchit).removeClass('typeFiltered').hide();
        if (results.length) {
            $.each(results, function (key, value) {
                $(value).show().addClass('typeFiltered');
            });
        }
        if (!findarr.length) {
            $(searchit).show();
        }
        $('.filtered').each(function () {
            var parel = $(this);
            if ($(parel).find('.w-list-result').children(':visible').length == 0) {
                $(parel).hide();
            }
        });
    });
    $('#filter_ai_dummy').change(function () {
        $('.filter_ai').trigger('click');
    });
    $('.filter_ai').change(function () {
        if (this.checked) {
            $('#filter_ai_dummy').prop('checked', false);
            $('#aiNot').text('Not ');
        } else {
            $('#filter_ai_dummy').prop('checked', true);
            $('#aiNot').text('');
        }
        var findarr = [];
        var allsearch = [];
        var type = $(this).data('filter');
        $('.filter_ai').each(function () {
            if (this.checked) {
                findarr.push($(this).val());
            }
        });
        var find = '';
        var search = '.filtered .item-result';
        if ($('.sizeFiltered').length)
            allsearch.push('.sizeFiltered');
        if ($('.dateFiltered').length)
            allsearch.push('.dateFiltered');
        if ($('.typeFiltered').length)
            allsearch.push('.typeFiltered');
        if (allsearch.length)
            search = allsearch.join('');
        var filter = '';
        var results = [];
        $.each(findarr, function (key, value) {
            find = value;
            $(search).each(function () {
                filter = $(this).data(type);
                if (jQuery.inArray(find, filter) != -1) {
                    results.push($(this).attr('id'));
                }
            });
        });
        $('.filtered').show();
        $(search).removeClass('aiFiltered').hide();
        if (results.length) {
            $.each(results, function (key, value) {
                $('#' + value).show().addClass('aiFiltered');
            });
        }
        if (!findarr.length) {
            $(search).show();
        }
        $('.filtered').each(function () {
            var parel = $(this);
            if ($(parel).find('.w-list-result').children(':visible').length == 0) {
                $(parel).hide();
            }
        });

    });

    $('#select_soonest').change(function () {
        var sortby = $(this).val(),
            sorttype = '',
            sortorder = '';
        switch (sortby) {
            case '1':
                sorttype = 'timestamp';
                sortorder = 'asc';
                break;

            case '2':
                sorttype = 'timestamp';
                sortorder = 'desc';
                break;

            case '3':
                sorttype = 'price';
                sortorder = 'asc';
                break;

            case '4':
                sorttype = 'price';
                sortorder = 'desc';
                break;

        }
        $('.w-item-view').each(function () {
            var thisid = $(this).attr('id');
            tinysort('#' + thisid + '>.w-list-result>li', {data: sorttype, order: sortorder});
        });
        tinysort('.w-item-view', {selector: '.w-list-result>.item-result', data: sorttype, order: sortorder});
    });
    if ($('#select_soonest').length && $('.unset-filter-false').length) {
        $('#select_soonest').trigger('change');
    }

    $('#user_email, #user_pass,#modal_user_email, #modal_user_pass, #user_email_pwreset, #user_password_pw_reset').change(function () {
        if ($(this).val().length)
            $(this).addClass('filled');
    });

    if ($('.agentLogin').length) {
        alertModal.alert('<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_switch">Please select an owner to continue!</a>');
    }
    if ($('#bonusWeekDetails_disabled').length) {
        var weekendpointid = $('#bonusWeekDetails').data('weekendpointid');
        var weekid = $('#bonusWeekDetails').data('weekid');
        var weektype = $('#bonusWeekDetails').data('weektype');
        var id = $('#bonusWeekDetails').data('id');
        $.get('/wp-admin/admin-ajax.php?action=gpx_bonus_week_details&weektype=' + weektype + '&weekid=' + weekid + '&weekendpointid=' + weekendpointid + '&id=' + id, function (data) {
            if (data.PriceChange) {
                alertModal.alert('The price of this property has changed.  This page will reload with the new price.', false);
                setTimeout(function () {
                    location.reload();
                }, 2000);
            }
            if (data.Unavailable) {
                alertModal.alert(data.Unavailable);
                setTimeout(function () {
                    window.location.href = '/';
                }, 2000);
            }
        });
    }
    $('html body').on('click', '.datepicker', function () {
        $(this).datepicker();
    });
    $('html body').on('change', '.datepicker', function () {
        if ($(this).val().length)
            $(this).addClass('filled');
    });
    $('html body').on('change', '.doe', function () {
        var upgrade = $(this).find('option:selected').data('upgradefee');
        var parel = $(this).closest('li');
        $(parel).find('.doe_upgrade_msg').hide();
        $(parel).find('.exchange-credit-check').val(upgrade);
        if (upgrade > 0) {
            $(parel).find('.doe_upgrade_msg').show();
        }
    });
    $('html body').on('blur', '.twoforone-coupon input', function () {
        var thispar = $(this).closest('.twoforone');
        var coupon = $(this).val();
        var setdate = $(this).closest('li').find('.disswitch[name="CheckINDate"]').val();
        var resortID = $(this).closest('li').find('.disswitch[name="ResortID"]').val();
        $('.validate-error').remove();
        if (coupon != '') {
            $.ajax({
                method: 'POST',
                url: '/wp-admin/admin-ajax.php?action=gpx_twoforone_validate',
                data: {coupon: coupon, setdate: setdate, resortID: resortID},
                success: function (data) {
                    if (data.success == false) {
                        $(thispar).append('<div class="validate-error">' + data.message + '</div>');
                    } else {
                        $(thispar).append('<div class="validate-error">' + data.name + ' is valid.</div>');
                    }
                }
            });
        }
    });
    $('html body').on('click', '.btn-tfo-validate', function (e) {
        e.preventDefault();
    });
    $("form").not('[novalidate]').submit(function (e) {

        var ref = $(this).find("[required]");

        $(ref).each(function () {
            if ($(this).val() == '') {
                alert("Required field should not be blank.");

                $(this).focus();

                e.preventDefault();
                return false;
            }
        });
        return true;
    });
    $('html body').on('focus', '.mindatepicker', function () {
        var par = $(this).closest('li');
        var mindate = $(this).data('mindate');
        $(this).datepicker({
            dateFormat: 'mm/dd/yy',
            minDate: new Date(mindate),
            onSelect: function (setdate) {
                var startdate = $('.twoforone').data('start');
                var enddate = $('.twoforone').data('end');
                if ((new Date(setdate).getTime() >= new Date(startdate).getTime()) && (new Date(setdate).getTime() <= new Date(enddate).getTime())) {
                    $(par).find('.twoforone-coupon').addClass('enable');
                } else {
                    $(par).find('.twoforone-coupon').removeClass('enable');
                }

            }
        });
    });
    if ($(".transaction-load").length) {

        $('.transaction-load').each(function () {
            var el = $(this);
            var id = $(this).data('id');
            var loading = $(this).data('load');
            $.ajax({
                method: 'GET',
                url: '/wp-admin/admin-ajax.php?action=gpx_load_data',
                data: {load: loading, cid: id},
                success: function (data) {
                    $('#ownership').html(data.ownership);
                    $('#deposit').html(data.deposit);
                    $('#depositused').html(data.depositused);
                    $('#exchange').html(data.exchange);
                    $('#bnr').html(data.rental);
                    $('#misc').html(data.misc);
                    $('#creditBal').text(data.credit);
                    $('#holdweeks').html(data.hold);
                    $('.loading').hide();
                },
            });
        });
    }

    $('html body').on('focus', '.iserror', function () {
        $(this).val('');
    });

    $('.return-back').click(function (e) {
        e.preventDefault();
        window.history.back();
    });
    $('html body').on('click', '.sbt-btn', function () {
        var link = $(this).data('link');
        window.location.href = link;
    });
    $('html body').on('click', '.sbt-seemore', function () {
        var location = $(this).data('location');
        var start = $(this).data('start');
        var get = $(this).data('get');
        $.post('/wp-admin/admin-ajax.php?action=gpx_display_featured_func', {
            location: location,
            start: start,
            get: get
        }, function (data) {
            if (data.html) {
                $('.sbt-seemore-box').html(data.html);
            }
        });
    });
    $('#newpwform').submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var cid = $(this).data('cid');
        var formdata = $(form).serialize() + "&cid=" + cid;
        $('.pwMsg').hide();
        $.post('/wp-admin/admin-ajax.php?action=gpx_change_password_with_hash', formdata, function (data) {
            $(form)[0].reset();
            $('.pwMsg').text(data.msg).show();
            setTimeout(function () {
                $('.pwMsg').hide();
            }, 5000);
        });
    });


    /*-----------------------------------------------------------------------------------*/
    /* Data table
     /*-----------------------------------------------------------------------------------*/
    if (gpx_base.current == 'view-profile') {
        var dtable = $('.data-table').has('tr');
        dtable.addClass('nowrap').dataTable({
            responsive: true,
            paging: true,
            pageLength: 5,
            search: true,
            "language": {
                "lengthMenu": "Display _MENU_ records per page",
                "zeroRecords": "Nothing found - sorry",
                "info": "of _PAGES_",
                "infoEmpty": "No records available",
                "infoFiltered": "(filtered from _MAX_ total records)"
            },
        });
    }

    function getTimeRemaining(endtime) {
        var t = Date.parse(endtime) - Date.parse(new Date());
        var seconds = Math.floor((t / 1000) % 60);
        var minutes = Math.floor((t / 1000 / 60) % 60);
        var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
        var days = Math.floor(t / (1000 * 60 * 60 * 24));
        return {
            'total': t,
            'days': days,
            'hours': hours,
            'minutes': minutes,
            'seconds': seconds
        };
    }

    function initializeClock(id, endtime) {
        var clock = document.getElementById(id);
        var daysSpan = clock.querySelector('.days');
        var hoursSpan = clock.querySelector('.hours');
        var minutesSpan = clock.querySelector('.minutes');
        var secondsSpan = clock.querySelector('.seconds');

        function updateClock() {
            var t = getTimeRemaining(endtime);

            if (t.days > 0) {
                daysSpan.innerHTML = t.days;
            } else {
                daysSpan.innerHTML = '0';
            }
            if (t.hours > 0) {
                hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
            } else {
                hoursSpan.innerHTML = '0';
            }
            if (t.minutes > 0) {
                minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
            } else {
                minutesSpan.innerHTML = '0';
            }
            if (t.seconds > 0) {
                secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);
            } else {
                secondsSpan.innerHTML = '0';
            }

            if (t.total <= 0) {
                clearInterval(timeinterval);
            }
        }

        updateClock();
        var timeinterval = setInterval(updateClock, 1000);
    }

    if ($('.hold-limit-countdown').length) {
        $('.hold-limit-countdown').each(function () {
            var holdtime = parseFloat($(this).data('limit')) * 60 * 60 * 1000;
            var deadline = new Date(Date.parse(new Date()) + parseInt(holdtime));
            var id = $(this).find('.show-countdown-timer').attr('id');
            initializeClock(id, deadline);
        });

    }

    $(window).load(function () {
        $('.gpx-loading-disabled').removeClass('gpx-loading-disabled');
        $(window).scroll(function () {
            var offset = $("#11").offset();
            window_y = $(window).scrollTop();
            scroll_critical = parseInt(offset.top) - 1000;
            if (window_y > scroll_critical) {
                $(".scrolltop").removeClass('s-active');
            } else {
                $(".scrolltop").addClass('s-active');
            }
        });
    });

    if ($('#chPassword').length) {
        var password = document.getElementById("chPassword")
            , confirm_password = document.getElementById("chPasswordConfirm");
        password.onchange = validatePassword;
        confirm_password.onkeyup = validatePassword;
    }

    function validatePassword() {
        if (password.value != confirm_password.value) {
            confirm_password.setCustomValidity("Passwords Don't Match");
        } else {
            confirm_password.setCustomValidity('');
        }
    }

    function isValidEmailAddress(emailAddress) {
        var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
        return pattern.test(emailAddress);
    };
    $(document).on('scroll', function () {
        if ($('.w-filter.dgt-container').length) {
            if (($(this).scrollTop() >= $('.w-filter.dgt-container').position().top) && ($(window).height() > $('#modal-filter').height())) {
                if (!$('#sticky').hasClass('scrolled'))
                    $('#sticky, #modal-filter').addClass('scrolled');
            } else {
                if ($('#sticky').hasClass('scrolled'))
                    $('#sticky, #modal-filter').removeClass('scrolled');
            }
        }
    });

});
