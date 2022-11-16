jQuery(document).ready(function ($) {
    $('.mtsnb-link').attr('aria-label', 'notification');
    $('html body').on('click', '.book-btn', function (e) {
        e.preventDefault();
        if ($(this).hasClass('booking-disabled')) {
            var $msg = $('#bookingDisabledMessage').data('msg');
            alertModal.alert($msg);
            return false;
        }

        var lpid = $(this).data('lpid');
        if (lpid != '') { //set the cookie for this week
            Cookies.set('lppromoid' + lpid, lpid);
            var cid = $(this).data('cid');
            //also store this in the database
            $.post('/wp-admin/admin-ajax.php?action=gpx_lpid_cookie', {lpid: lpid, cid: cid}, function () {

            });
        }

        var link = $(this).attr('href');
        var wid = $(this).data('wid');
        var pid = $(this).data('pid');
        var cid = $(this).data('cid');
        var type = $(this).data('type');

        Cookies.set('exchange_bonus', type);
        var form = $('#home-search').serialize();
        form = form + "&wid=" + wid + "&pid=" + pid + "&cid=" + cid;
        $.post('/wp-admin/admin-ajax.php?action=gpx_book_link_savesearch', form, function (data) {
            location.href = link;
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
        $(this).find('i').show();
        $.get('/wp-admin/admin-ajax.php?action=gpx_hold_property&pid=' + pid + '&weekType=' + type + '&cid=' + cid + '&wid=' + wid + '&lpid=' + lpid + '&button=true', function (data) {
            $(this).find('i').hide();
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
        Cookies.set('auto-coupon', null, {expires: -1, path: '/'});
        Cookies.remove('auto-coupon', {path: '/'});
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
