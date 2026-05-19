(function() {
  'use strict';

  function text(value) {
    return value == null ? '' : String(value).trim();
  }

  function setMeta(selector, attr, value) {
    if (!value) return;
    var el = document.querySelector(selector);
    if (!el) {
      el = document.createElement('meta');
      if (selector.indexOf('name="') !== -1) el.setAttribute('name', selector.match(/name="([^"]+)"/)[1]);
      if (selector.indexOf('property="') !== -1) el.setAttribute('property', selector.match(/property="([^"]+)"/)[1]);
      document.head.appendChild(el);
    }
    el.setAttribute(attr, value);
  }

  function updateSchema(settings) {
    var siteName = text(settings.site_name) || 'Bourn Hill Tyre & MOT | London';
    var description = text(settings.site_description) || text(settings.seo_description);
    var schema = {
      '@context': 'https://schema.org',
      '@type': 'AutoRepair',
      name: siteName,
      description: description,
      address: {
        '@type': 'PostalAddress',
        streetAddress: text(settings.address_street) || '6A Bourne Hill',
        addressLocality: text(settings.address_locality) || 'Southgate',
        addressRegion: text(settings.address_region) || 'London',
        postalCode: text(settings.address_postcode) || 'N13 4LG',
        addressCountry: text(settings.address_country) || 'GB'
      },
      telephone: text(settings.phone_international) || '+447895859505',
      url: text(settings.url) || window.location.origin,
      logo: text(settings.logo_url) || '/images/logo.png',
      image: text(settings.hero_image_url) || '/images/hero-garage.jpg',
      openingHoursSpecification: [{
        '@type': 'OpeningHoursSpecification',
        dayOfWeek: (text(settings.opening_days) || 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday').split(',').map(function(day) { return day.trim(); }).filter(Boolean),
        opens: text(settings.opening_time) || '08:00',
        closes: text(settings.closing_time) || '18:00'
      }],
      priceRange: '££'
    };
    var el = document.querySelector('script[type="application/ld+json"]');
    if (el) el.textContent = JSON.stringify(schema);
  }

  function injectGTM(gtmId) {
    if (!gtmId || document.getElementById('gtm-script')) return;
    // Head script
    var s = document.createElement('script');
    s.id = 'gtm-script';
    s.innerHTML = "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" + gtmId + "');";
    document.head.appendChild(s);
    // Body noscript iframe
    var ns = document.createElement('noscript');
    ns.id = 'gtm-noscript';
    var iframe = document.createElement('iframe');
    iframe.src = 'https://www.googletagmanager.com/ns.html?id=' + gtmId;
    iframe.height = '0'; iframe.width = '0';
    iframe.style.display = 'none'; iframe.style.visibility = 'hidden';
    ns.appendChild(iframe);
    document.body.insertBefore(ns, document.body.firstChild);
  }

  function injectGA(gaId) {
    if (!gaId || document.getElementById('ga-script')) return;
    var s = document.createElement('script');
    s.id = 'ga-script';
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id=' + gaId;
    document.head.appendChild(s);
    var s2 = document.createElement('script');
    s2.innerHTML = "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','" + gaId + "');";
    document.head.appendChild(s2);
  }

  function apply(settings) {
    if (!settings) return;
    var siteName = text(settings.site_name) || 'Bourn Hill Tyre & MOT | London';
    var title = text(settings.seo_title) || siteName;
    var description = text(settings.seo_description) || text(settings.site_description);
    var keywords = text(settings.seo_keywords);
    var logoUrl = text(settings.logo_url) || '/images/logo.png';
    var phone = text(settings.phone) || '07895 859505';
    var hours = text(settings.opening_hours_display) || 'Mon-Sat: 8am-6pm';
    var address = [
      settings.address_street,
      settings.address_locality,
      settings.address_region,
      settings.address_postcode
    ].map(text).filter(Boolean).join(', ');

    document.title = title;
    setMeta('meta[name="description"]', 'content', description);
    setMeta('meta[name="keywords"]', 'content', keywords);
    setMeta('meta[property="og:title"]', 'content', title);
    setMeta('meta[property="og:description"]', 'content', description);

    Array.prototype.forEach.call(document.querySelectorAll('#site-logo, img[src*="logo.png"], img[src*="logo2.PNG"], img[src*="Main-Logo"], img[alt*="N05"], img[alt*="NO5"]'), function(img) {
      img.src = logoUrl;
      img.alt = siteName;
    });
    Array.prototype.forEach.call(document.querySelectorAll('a[href^="tel:"]'), function(a) {
      a.href = 'tel:' + (text(settings.phone_international) || '+447895859505');
      if (/07895|call|phone/i.test(a.textContent)) a.textContent = a.textContent.replace(/0\d[\d\s]{8,}/, phone);
    });
    Array.prototype.forEach.call(document.querySelectorAll('[data-site-name]'), function(el) { el.textContent = siteName; });
    Array.prototype.forEach.call(document.querySelectorAll('[data-site-phone]'), function(el) { el.textContent = phone; });
    Array.prototype.forEach.call(document.querySelectorAll('[data-site-address]'), function(el) { el.textContent = address; });
    Array.prototype.forEach.call(document.querySelectorAll('[data-site-hours]'), function(el) { el.textContent = hours; });

    updateSchema(settings);

    if (settings.gtm_id && String(settings.gtm_id).trim()) injectGTM(String(settings.gtm_id).trim());
    else if (settings.ga_id && String(settings.ga_id).trim()) injectGA(String(settings.ga_id).trim());

    // Update thank-you page contact fields
    var addrEl  = document.getElementById('site-address');
    var phoneEl = document.getElementById('site-phone');
    var hoursEl = document.getElementById('site-hours');
    if (addrEl && address)  addrEl.textContent  = address;
    if (phoneEl && phone)   phoneEl.textContent = phone;
    if (hoursEl && hours)   hoursEl.textContent = hours;
  }

  function load() {
    var loader = window.N05ServicesLoader && window.N05ServicesLoader.loadServices;
    var promise = loader ? loader() : fetch('/data/services.json?v=' + Date.now()).then(function(r) { return r.json(); });
    promise.then(function(data) { apply(data && data.settings); }).catch(function() {});
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', load);
  else load();
})();
