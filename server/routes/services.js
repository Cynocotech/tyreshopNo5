/**
 * Services API - reads from Laravel SQLite database (same as admin panel)
 * Serves /data/services.json in the format expected by the front page
 */
const path = require('path');
const Database = require('better-sqlite3');

const DB_PATH = path.join(__dirname, '../../admin/database/database.sqlite');

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
         combo_badge, combo_subtitle, combo_features, combo_saving, is_combo_hot, combo_display_price FROM services ORDER BY sort_order, title`
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
    const settings = {
      logo_url: rawSettings.logo_url || '/images/logo.png',
      tagline: rawSettings.tagline || 'Palmers Green · North London',
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

    db.close();
    return { services: servicesOut, categories: categoriesOut, settings };
  } catch (err) {
    console.error('Services DB error:', err.message);
    return { services: [], categories: {}, settings: {} };
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
