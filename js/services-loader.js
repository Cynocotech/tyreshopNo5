/**
 * N05 Services Loader — loads prices from data/services.json
 * Provides loadServices() and getPriceMap() for dynamic price injection.
 */
(function(global) {
  'use strict';

  /**
   * Fetches services data from /data/services.json
   * @returns {Promise<{services: Array, categories: Object}>}
   */
  function loadServices() {
    var url = '/data/services.json?v=' + Date.now();
    return fetch(url).then(function(r) {
      if (!r.ok) throw new Error('Failed to load services');
      return r.json();
    });
  }

  /**
   * Builds price map (value -> priceLabel) and id -> service lookup.
   * @param {{services: Array}} data - from loadServices()
   * @returns {{ priceMap: Object, byId: Object, services: Array }}
   *   - priceMap: { "MOT + Full Service": "£190 (Special Offer)", ... }
   *   - byId: { "mot-full-service": {id, value, priceLabel, priceDisplay, ...}, ... }
   */
  function getPriceMap(data) {
    var priceMap = {};
    var byId = {};
    var byValue = {};
    var services = (data && data.services) ? data.services : [];
    for (var i = 0; i < services.length; i++) {
      var s = services[i];
      if (s.value) {
        priceMap[s.value] = s.priceLabel || s.priceDisplay || '';
        byValue[s.value] = s;
      }
      if (s.id) byId[s.id] = s;
    }
    return { priceMap: priceMap, byId: byId, byValue: byValue, services: services };
  }

  global.N05ServicesLoader = {
    loadServices: loadServices,
    getPriceMap: getPriceMap
  };
})(typeof window !== 'undefined' ? window : this);
