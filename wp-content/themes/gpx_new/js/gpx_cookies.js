jQuery(document).ready(function ($) {
    $('html body').on('click', '.copyText', function () {
        var copy = $(this).find('.copy');
        var copyval = copy.text();
        copyToClipboard(copy);
        $(copy).hide();
        setTimeout(function () {
            $(copy).show();
        }, 300);
    });
    $('.vc_carousel-control').attr('aria-label', "controls");
    $('.mtsnb-link').attr('aria-label', 'notification');
    $('.deposit-cookie').click(function () {
        Cookies.set('deposit-login', '1');
    });
    if ($('.deposit-login').length) {
        var owner = $('.deposit-login').data('owner');
        if (owner != 1) {
            //this is not an owner do we need to switch owners?
            var switchuser = Cookies.get('switchuser');
            if (switchuser > 0) {
                //switchuser has been set so we can display the form
                $('#main-deposit-link').trigger('click');
                Cookies.remove('deposit-login');
            } else {
                //go to the switch owner page
                location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_switch'
            }

        } else {
            //this is an owner just display the form
            $('#main-deposit-link').trigger('click');
            Cookies.remove('deposit-login');
        }

    }

    function copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).text()).select();
        document.execCommand("copy");
        $temp.remove();
    }

    var lpid = $(this).data('lpid');
    if (lpid != '') {
        //set the cookie for this week
        Cookies.set('lppromoid' + lpid, lpid);
        var cid = $(this).data('cid');
        //also store this in the database
        $.post('/wp-admin/admin-ajax.php?action=gpx_lpid_cookie', {lpid: lpid, cid: cid}, function () {
        });
    }

    $('#wp-admin-bar-gpx_switch').click(function () {
        var page = window.location.href;
        Cookies.set('switchreturn', page);
    });
    if ($(".cookieset").length) {
        $('.cookieset').each(function () {
            var el = $(this);
            var $name = $(el).data('name');
            var $value = $(el).data('value');
            var $expires = $(el).data('expires');
            var $path = $(el).data('expires');
            var $json = "{expires: " + $expires + "}";
            Cookies.set($name, $value, $json);
        });
    }
    if ($('.cookieremove').length) {
        var remcookie = $('.cookieremove').data('cookie');
        Cookies.remove(remcookie);
    }
    $('html body').on('click', '.book-btn', function (e) {
        e.preventDefault();
        if ($(this).hasClass('booking-disabled')) {
            var $msg = $('#bookingDisabledMessage').data('msg');
            alertModal.alert($msg);
            return false;
        }

        var link = $(this).attr('href');
        var pid = $(this).data('pid');
        var type = $(this).data('type');
        var lpid = $(this).data('lpid');

        Cookies.set('exchange_bonus', type);
        axios.post('/wp-admin/admin-ajax.php?action=gpx_book_link_savesearch', {
            pid: pid,
            type: type,
            lpid: lpid,
        })
            .then(function(response){
                window.location = link;
            });
    });
    $('html body').on('click', '.hold-btn', function (e) {
        e.preventDefault();
        if ($(this).hasClass('booking-disabled')) {
            var $msg = $('#bookingDisabledMessage').data('msg');
            alertModal.alert($msg);
            return false;
        }
        var $this = $(this);
        var wid = $(this).data('wid');
        var pid = $(this).data('pid');
        var type = $(this).data('type');
        var cid = $(this).data('cid');
        var lpid = $(this).data('lpid');
        if (lpid != '') { //set the cookie for this week
            Cookies.set('lppromoid' + lpid, lpid);	    //also store this in the database
            $.post('/wp-admin/admin-ajax.php?action=gpx_lpid_cookie', {lpid: lpid, cid: cid}, function () {

            });
        }
        var $spinner = $this.find('i')
        $spinner.show();
        $.get('/wp-admin/admin-ajax.php?action=gpx_hold_property&pid=' + pid + '&weekType=' + type + '&cid=' + cid + '&wid=' + wid + '&lpid=' + lpid + '&button=true', function (data) {
            $spinner.hide();
            if (data.login) {
                active_modal('modal-login');
            } else {
                if (data.msg != 'Success') {
                    alertModal.alert(data.msg);
                } else {
                    alertModal.alert('<span class="hold-msg">This week has been placed on a hold for you for 24 hours, to retrieve your held week visit your <a href="/view-profile/#holdweeks-profile" target="_blank" title="Held weeks can be viewed in your profile.">Member Dashboard Profile under "My Held Weeks"</a></span>');
                }
            }
        });
    });
    $('html body').on('click', '.hold-confirm', function (e) {
        e.preventDefault();
        var $link = $(this).attr('href');
        alertModal.alert('Are you sure you want to continue booking? Clicking <a href="' + $link + '">"Continue"</a> will release this hold in order to place it into your cart<br /><br /><a href="' + $link + '">Continue</a>');
    });
    $('#couponAdd').click(function (e) {
        e.preventDefault();
        var el = $(this).closest('.gwrapper').find('#couponCode');
        var coupon = $(el).val();
        var book = $(el).data('book');
        var cid = $(el).data('cid');
        var cartID = $(el).data('cartid');
        var currentPrice = $(el).data('currentprice');
        Cookies.remove('auto-coupon');
        $.post('/wp-admin/admin-ajax.php?action=gpx_enter_coupon', {
            coupon: coupon,
            book: book,
            cid: cid,
            cartID: cartID,
            currentPrice: currentPrice
        }, function (data) {
            if (data.success) {
                window.location.href = '/booking-path-payment';
            } else {
                $(el).addClass('iserror');
                $('#couponError').html(data.error);
                $("#apply-coupon").hide();
            }
        });
    });
    if ($('#apply-coupon').length) {
        $('#couponAdd').trigger('click');
    }

    function close_modal($obj) {
        var $modal = $obj.closest('.dgt-modal');
        $modal.removeClass('active-modal');
        $modal.addClass('desactive-modal');
    }
});


