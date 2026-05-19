/**
 * Services API - reads from Laravel SQLite database (same as admin panel)
 * Serves /data/services.json in the format expected by the front page
 */
const path = require('path');
const fs = require('fs');
const { execFileSync } = require('child_process');
const Database = require('better-sqlite3');

const DB_PATH = path.join(__dirname, '../../admin/database/database.sqlite');
const SERVICES_JSON_PATH = path.join(__dirname, '../../data/services.json');
let warnedDbFallback = false;

function buildSettings(rawSettings = {}) {
  return {
    site_name: rawSettings.site_name || 'Bourn Hill Tyre & MOT | London',
    site_description: rawSettings.site_description || 'MOT testing, tyre fitting, puncture repairs, wheel alignment and car servicing in London.',
    seo_title: rawSettings.seo_title || 'Bourn Hill Tyre & MOT | London Tyres, MOT Testing & Car Servicing',
    seo_description: rawSettings.seo_description || 'Book Bourn Hill Tyre & MOT in London for MOT testing, new tyres, puncture repair, wheel alignment, brakes, diagnostics and car servicing.',
    seo_keywords: rawSettings.seo_keywords || 'Bourn Hill Tyre & MOT London, tyres near me, MOT near me, MOT test London, tyre fitting London, puncture repair London, car servicing London, wheel alignment London',
    address_street: rawSettings.address_street || '6A Bourne Hill',
    address_locality: rawSettings.address_locality || 'Southgate',
    address_region: rawSettings.address_region || 'London',
    address_postcode: rawSettings.address_postcode || 'N13 4LG',
    address_country: rawSettings.address_country || 'GB',
    phone: rawSettings.phone || '07895 859505',
    phone_international: rawSettings.phone_international || '+447895859505',
    email: rawSettings.email || 'info@no5mot.co.uk',
    url: rawSettings.url || 'https://no5mot.co.uk',
    logo_url: rawSettings.logo_url || '/images/logo.png',
    hero_image_url: rawSettings.hero_image_url || '/images/hero-garage.jpg',
    tagline: rawSettings.tagline || 'Bourne Hill · London',
    footer_tagline: rawSettings.footer_tagline || 'Formerly Bourne Hill Tyres',
    footer_description: rawSettings.footer_description || "London's trusted tyre and MOT specialist for MOT testing, tyres, puncture repairs, brakes, diagnostics and servicing.",
    copyright: rawSettings.copyright || '© 2026 Bourn Hill Tyre & MOT | London. All rights reserved.',
    hero_book_price: rawSettings.hero_book_price,
    hero_save: rawSettings.hero_save,
    footer_mot_price: rawSettings.footer_mot_price,
    opening_hours_display: rawSettings.opening_hours_display,
    show_update_notice: rawSettings.show_update_notice ?? '1',
    footer_offer_title: rawSettings.footer_offer_title || "Today's Offer",
    footer_offer_subtitle: rawSettings.footer_offer_subtitle || 'Book Today',
    footer_offer_label: rawSettings.footer_offer_label || 'MOT + Service',
    footer_offer_was_price: rawSettings.footer_offer_was_price || '£50',
    footer_offer_save: rawSettings.footer_offer_save || 'Save £31+',
    footer_offer_feature: rawSettings.footer_offer_feature || '🚗 Free collection & delivery',
    footer_offer_btn: rawSettings.footer_offer_btn || rawSettings.footer_offer_btn_text || 'Book Now →',
    footer_offer_disclaimer: rawSettings.footer_offer_disclaimer || '*New bookings only. Excludes commercial vehicles.',
    combo_section_title: rawSettings.combo_section_title || 'Special Offer',
    combo_section_intro: rawSettings.combo_section_intro || "Book your MOT together with a service and pay just £19 — saving at least £31.",
    combo_combined_desc: rawSettings.combo_combined_desc || 'MOT Test + Service combined',
  };
}

function getSettings(db) {
  try {
    const rows = db.prepare('SELECT key, value FROM site_settings').all();
    const out = {};
    for (const r of rows) out[r.key] = r.value;
    return out;
  } catch (_) {
    return {};
  }
}

function getSettingsWithSqliteCli() {
  try {
    const out = execFileSync('sqlite3', [
      DB_PATH,
      '-json',
      'SELECT key, value FROM site_settings'
    ], { encoding: 'utf8', timeout: 3000 });
    const rows = JSON.parse(out || '[]');
    const settings = {};
    for (const row of rows) settings[row.key] = row.value;
    return settings;
  } catch (_) {
    return {};
  }
}

function getFileFallback() {
  let file = {};
  try {
    file = JSON.parse(fs.readFileSync(SERVICES_JSON_PATH, 'utf8'));
  } catch (_) {
    file = {};
  }
  const rawSettings = Object.assign({}, file.settings || {}, getSettingsWithSqliteCli());
  return {
    services: Array.isArray(file.services) ? file.services : [],
    categories: file.categories && typeof file.categories === 'object' ? file.categories : {},
    settings: buildSettings(rawSettings),
  };
}

function getServicesFromDb() {
  try {
    const db = new Database(DB_PATH, { readonly: true });
    const categories = db
      .prepare(
        'SELECT id, slug, label, sort_order FROM service_categories ORDER BY sort_order, id'
      )
      .all();
    const catById = Object.fromEntries(categories.map((c) => [c.id, c]));

    let services;
    try {
      services = db.prepare(
        `SELECT slug, value, title, icon, price, hero_mot_price, price_label, price_display, service_category_id, is_quote, keywords, sort_order,
         features, combo_badge, combo_subtitle, combo_features, combo_saving, is_combo_hot, combo_display_price FROM services ORDER BY sort_order, title`
      ).all();
    } catch {
      services = db.prepare(
        `SELECT slug, value, title, icon, price, hero_mot_price, price_label, price_display, service_category_id, is_quote, keywords, sort_order
         FROM services ORDER BY sort_order, title`
      ).all();
    }

    const servicesOut = services.map((s) => {
      const cat = catById[s.service_category_id];
      let keywords = s.keywords ? (typeof s.keywords === 'string' ? (() => { try { return JSON.parse(s.keywords); } catch (_) { return []; } })() : s.keywords) : [];
      let features = (s.features != null) ? (typeof s.features === 'string' ? (() => { try { return JSON.parse(s.features); } catch (_) { return []; } })() : s.features) : [];
      let comboFeatures = (s.combo_features != null) ? (typeof s.combo_features === 'string' ? (() => { try { return JSON.parse(s.combo_features); } catch (_) { return []; } })() : s.combo_features) : [];
      return {
        id: s.slug,
        value: s.value,
        title: s.title,
        icon: s.icon,
        price: parseFloat(s.price) || 0,
        heroMOTPrice: s.hero_mot_price != null ? parseFloat(s.hero_mot_price) : null,
        priceLabel: s.price_label,
        priceDisplay: s.price_display,
        category: cat ? cat.slug : '',
        isQuote: Boolean(s.is_quote),
        keywords,
        features: Array.isArray(features) ? features : [],
        comboBadge: s.combo_badge || null,
        comboSubtitle: s.combo_subtitle || null,
        comboFeatures: Array.isArray(comboFeatures) ? comboFeatures : [],
        comboSaving: s.combo_saving || null,
        isComboHot: Boolean(s.is_combo_hot),
        comboDisplayPrice: s.combo_display_price || null,
      };
    });

    const categoriesOut = {};
    for (const c of categories) {
      categoriesOut[c.slug] = {
        label: c.label,
        sortOrder: c.sort_order,
      };
    }

    const rawSettings = getSettings(db);
    const settings = buildSettings(rawSettings);

    db.close();
    return { services: servicesOut, categories: categoriesOut, settings };
  } catch (err) {
    if (!warnedDbFallback) {
      warnedDbFallback = true;
      console.warn('Services DB fallback active:', err.message.split('\n')[0]);
    }
    return getFileFallback();
  }
}

module.exports = function servicesHandler(req, res) {
  const data = getServicesFromDb();
  res.setHeader('Content-Type', 'application/json');
  res.setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
  res.setHeader('Pragma', 'no-cache');
  res.setHeader('Expires', '0');
  res.json(data);
};
