<?php if(GPX_CCPA_CONSENT_ENABLED):?>
<!-- OneTrust Cookies Consent Notice start -->

<script type="text/javascript" src="https://cdn.cookielaw.org/consent/a8303f34-3538-41c6-9d8a-d8c137d0e14d/OtAutoBlock.js"></script>
<script src="https://cdn.cookielaw.org/scripttemplates/otSDKStub.js"  type="text/javascript" charset="UTF-8" data-domain-script="a8303f34-3538-41c6-9d8a-d8c137d0e14d"></script>
<script type="text/javascript">
    function OptanonWrapper() { }
</script>

<!-- OneTrust Cookies Consent Notice end -->
<?php endif;?>
<?php if(GPX_GOOGLE_ANALYTICS_ENABLED):?>
<script type="text/javascript">
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-5298504-19', 'auto');
    ga('send', 'pageview');

</script>
<?php endif;?>
<?php if(GPX_SALESFORCE_LIVEAGENT_ENABLED):?>
<script  type="text/javascript" src='https://c.la2c1.salesforceliveagent.com/content/g/js/40.0/deployment.js'></script>
<script  type="text/javascript">
    liveagent.init('https://d.la2c1.salesforceliveagent.com/chat', '572400000008PPr', '00D40000000MzoY');
</script>
<?php endif;?>
<?php if(GPX_FACEBOOK_PIXEL_ENABLED):?>
<!-- Facebook Pixel Code -->
<script type="text/javascript">
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '2656938800989961');
    fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
               src="https://www.facebook.com/tr?id=2656938800989961&ev=PageView&noscript=1"
    /></noscript>
<!-- End Facebook Pixel Code -->
<?php endif;?>
<?php if(GPX_CCPA_CONSENT_ENABLED):?>
<link rel="stylesheet" href="https://ownerreservations.gpresorts.com/Ccpa/ConsentPreferences/CcpaConsentPreferences.css">
<script src="https://ownerreservations.gpresorts.com/Ccpa/ConsentPreferences/CcpaConsentPreferences.js" type="text/javascript"></script>
<?php endif;?>
