<?php if(GPX_ADA_WIDGET_ENABLED):?>
<script type="text/javascript">
    (function () {
        var params = {"propertyId":"594775"};
        var paramsArr = [];
        var pl2 = document.createElement("script");
        for(key in params) { paramsArr.push(key + "=" +
            encodeURIComponent(params[key])) };
        pl2.type = "text/javascript";
        pl2.async = true;
        pl2.src = "https://www.ada-tray.com/adawidget/?" +
            btoa(paramsArr.join("&"));
        (document.getElementsByTagName("head")[0] ||
            document.getElementsByTagName("body")[0]).appendChild(pl2);
    })();
</script>
<?php endif;?>
<?php if(GPX_SALESFORCE_MOBILE_WIDGET_ENABLED):?>
<!-- Salesforce Mobile Widget Start -->

<script type='text/javascript' src='https://service.force.com/embeddedservice/menu/fab.min.js'></script>
<script type='text/javascript'>
    var initESW = function(gslbBaseURL) {
        // Required if you want labels in a language that’s different from your user’s context.
        //embedded_svc.menu.settings.language = ''; //For example, enter 'en' or 'en-US'

        embedded_svc.menu.init(
            'https://grandpacificresorts.my.salesforce.com',
            'https://d.la5-c2-ia4.salesforceliveagent.com/chat',
            gslbBaseURL,
            '00D40000000MzoY',
            'GPX_Channel_Menu'
        );
    };

    if (!window.embedded_svc || !window.embedded_svc.menu) {
        var s = document.createElement('script');
        s.setAttribute('src', 'https://grandpacificresorts.my.salesforce.com/embeddedservice/menu/fab.min.js');
        s.onload = function() {
            initESW(null);
        };
        document.body.appendChild(s);
    } else {
        initESW('https://service.force.com');
    }
</script>

<!-- Salesforce Mobile Widget End -->
<?php endif;?>
