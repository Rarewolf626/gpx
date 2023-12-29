<?php if(GPX_ADA_WIDGET_ENABLED):?>
    <script type="text/javascript">!function(){var b=function(){window.__AudioEyeSiteHash = "ca71016fde400b6cebc76e7761698391"; var a=document.createElement("script");a.src="https://wsmcdn.audioeye.com/aem.js";a.type="text/javascript";a.setAttribute("async","");document.getElementsByTagName("body")[0].appendChild(a)};"complete"!==document.readyState?window.addEventListener?window.addEventListener("load",b):window.attachEvent&&window.attachEvent("onload",b):b()}();</script>
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
