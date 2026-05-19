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

    Array.prototype.forEach.call(document.querySelectorAll('img[src*="logo.png"], img[alt*="N05"], img[alt*="NO5"]'), function(img) {
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
  }

  function load() {
    var loader = window.N05ServicesLoader && window.N05ServicesLoader.loadServices;
    var promise = loader ? loader() : fetch('/data/services.json?v=' + Date.now()).then(function(r) { return r.json(); });
    promise.then(function(data) { apply(data && data.settings); }).catch(function() {});
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', load);
  else load();
})();
