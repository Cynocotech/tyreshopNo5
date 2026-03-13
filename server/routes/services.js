/**
 * Services API - reads from Laravel SQLite database
 * Serves /data/services.json in the format expected by the front page
 */
const path = require('path');
const Database = require('better-sqlite3');

const DB_PATH = path.join(__dirname, '../../admin/database/database.sqlite');

function getServicesFromDb() {
  try {
    const db = new Database(DB_PATH, { readonly: true });
    const categories = db
      .prepare(
        'SELECT id, slug, label, sort_order FROM service_categories ORDER BY sort_order, id'
      )
      .all();
    const catById = Object.fromEntries(categories.map((c) => [c.id, c]));
    const services = db
      .prepare(
        `SELECT slug, value, title, icon, price, hero_mot_price, price_label, price_display, service_category_id, is_quote, keywords, sort_order
         FROM services ORDER BY sort_order, title`
      )
      .all();

    const servicesOut = services.map((s) => {
      const cat = catById[s.service_category_id];
      let keywords = [];
      if (s.keywords) {
        try {
          keywords = JSON.parse(s.keywords);
        } catch (_) {}
      }
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
      };
    });

    const categoriesOut = {};
    for (const c of categories) {
      categoriesOut[c.slug] = {
        label: c.label,
        sortOrder: c.sort_order,
      };
    }

    db.close();
    return { services: servicesOut, categories: categoriesOut };
  } catch (err) {
    console.error('Services DB error:', err.message);
    return { services: [], categories: {} };
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
