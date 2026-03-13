/**
 * Telegram Admin Bot - Price management for N05 services
 * Uses node-telegram-bot-api, responds only to TELEGRAM_CHAT_ID
 */
const TelegramBot = require('node-telegram-bot-api');
const path = require('path');
const fs = require('fs');

const SERVICES_PATH = path.join(__dirname, '../data/services.json');

function loadServices() {
  const raw = fs.readFileSync(SERVICES_PATH, 'utf8');
  return JSON.parse(raw);
}

function saveServices(data) {
  fs.writeFileSync(SERVICES_PATH, JSON.stringify(data, null, 2), 'utf8');
}

function computePriceLabels(price, category) {
  const isQuote = price === 0;
  let priceLabel, priceDisplay;
  if (isQuote) {
    priceLabel = 'Quote';
    priceDisplay = 'Free Quote';
  } else if (category === 'special-offers') {
    priceLabel = `£${price} (Special Offer)`;
    priceDisplay = `£${price}`;
  } else {
    priceLabel = `£${price}`;
    priceDisplay = `£${price}`;
  }
  return { priceLabel, priceDisplay, isQuote };
}

function findService(data, idOrValue) {
  const norm = idOrValue.trim().toLowerCase();
  return data.services.find(
    (s) =>
      s.id.toLowerCase() === norm ||
      (s.value && s.value.toLowerCase() === norm)
  );
}

function initAdminBot(opts = {}) {
  const token = (opts.token || process.env.TELEGRAM_BOT_TOKEN || '').toString().trim();
  const chatId = (opts.chatId || process.env.TELEGRAM_CHAT_ID || '').toString().trim();
  if (!token || !chatId) {
    console.log('Telegram admin bot skipped (TELEGRAM_BOT_TOKEN or TELEGRAM_CHAT_ID not set)');
    return null;
  }

  const bot = new TelegramBot(token, { polling: true });
  const ADMIN_CHAT_ID = chatId;
  const pendingEdit = {}; // chatId -> serviceId for "reply with price" flow
  const pendingAdd = {}; // chatId -> { step, name?, price? } for add-service flow

  const adminChatId = chatId;
  const CATEGORIES = [
    { id: 'special-offers', label: '🔥 Special Offers' },
    { id: 'servicing-mot', label: '🔧 Servicing & MOT' },
    { id: 'tyres-other', label: '🛞 Tyres & Other' },
  ];

  function slugify(str) {
    return str.trim().toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
  }

  function makeUniqueId(data, baseId) {
    let id = baseId;
    let n = 1;
    while (data.services.some((s) => s.id === id)) id = `${baseId}-${++n}`;
    return id;
  }

  function buildCategoryKeyboard() {
    return {
      inline_keyboard: CATEGORIES.map((c) => [{ text: c.label, callback_data: `addcat:${c.id}` }]),
    };
  }

  function buildMenuKeyboard() {
    const data = loadServices();
    const rows = [];
    rows.push([{ text: '🔥 Special Offers – Combo Deals', callback_data: 'combo' }]);
    let row = [];
    for (const s of data.services) {
      const label = `${s.icon || '📌'} ${s.title}`.slice(0, 40);
      row.push({ text: label, callback_data: `s:${s.id}` });
      if (row.length >= 2) {
        rows.push(row);
        row = [];
      }
    }
    if (row.length) rows.push(row);
    rows.push([{ text: '➕ Add new service', callback_data: 'addservice' }]);
    return { inline_keyboard: rows };
  }

  function buildComboDealsKeyboard() {
    const data = loadServices();
    const combos = data.services.filter((s) => s.category === 'special-offers');
    const rows = combos.map((s) => [{ text: `${s.icon || '🔥'} ${s.title} – £${s.price}`, callback_data: `s:${s.id}` }]);
    rows.push([{ text: '⬅️ Back to menu', callback_data: 'menu' }]);
    return { inline_keyboard: rows };
  }

  function buildServiceDetailKeyboard(serviceId) {
    return {
      inline_keyboard: [
        [{ text: '✏️ Edit price', callback_data: `e:${serviceId}` }],
        [{ text: '⬅️ Back to menu', callback_data: 'menu' }]
      ]
    };
  }

  function sendMenu(targetChatId) {
    const text = '📋 *Services* – tap to view price';
    bot.sendMessage(targetChatId, text, {
      parse_mode: 'Markdown',
      reply_markup: buildMenuKeyboard()
    });
  }

  // Set bot command menu (visible when user taps / in Telegram)
  bot.setMyCommands([
    { command: 'start', description: 'Show menu' },
    { command: 'menu', description: '📋 All services' },
    { command: 'combo', description: '🔥 Special Offers / Combo Deals' },
    { command: 'addservice', description: '➕ Add new service' },
    { command: 'prices', description: '📄 List all prices' },
    { command: 'help', description: '❓ Help' },
  ]).catch(() => {});

  bot.on('callback_query', (query) => {
    const fromChatId = String(query.message?.chat?.id);
    if (fromChatId !== adminChatId) return;

    const data = query.data;
    bot.answerCallbackQuery(query.id).catch(() => {});

    if (data === 'menu') {
      bot.editMessageText('📋 *Services* – tap to view price', {
        chat_id: fromChatId,
        message_id: query.message.message_id,
        parse_mode: 'Markdown',
        reply_markup: buildMenuKeyboard()
      });
      return;
    }

    if (data === 'combo') {
      bot.editMessageText('🔥 *Special Offers – MOT + Service Combo Deals*\n\nTap a deal to view or edit price:', {
        chat_id: fromChatId,
        message_id: query.message.message_id,
        parse_mode: 'Markdown',
        reply_markup: buildComboDealsKeyboard()
      });
      return;
    }

    if (data === 'addservice') {
      pendingAdd[fromChatId] = { step: 'name' };
      bot.sendMessage(fromChatId, '📝 *Add new service*\n\nReply with the *service name* (e.g. Battery Replacement):', {
        parse_mode: 'Markdown'
      });
      return;
    }

    if (data.startsWith('s:')) {
      const serviceId = data.slice(2);
      const services = loadServices();
      const s = services.services.find((x) => x.id === serviceId);
      if (!s) {
        bot.answerCallbackQuery(query.id, { text: 'Service not found' });
        return;
      }
      const priceText = s.isQuote ? 'Quote' : `£${s.price}`;
      const text = `${s.icon || '📌'} *${s.title}*\n💰 ${priceText}`;
      bot.editMessageText(text, {
        chat_id: fromChatId,
        message_id: query.message.message_id,
        parse_mode: 'Markdown',
        reply_markup: buildServiceDetailKeyboard(serviceId)
      });
      return;
    }

    if (data.startsWith('e:')) {
      const serviceId = data.slice(2);
      const services = loadServices();
      const s = services.services.find((x) => x.id === serviceId);
      if (!s) return;
      pendingEdit[fromChatId] = serviceId;
      bot.sendMessage(fromChatId, `✏️ Enter new price for *${s.title}* (or 0 for Quote):`, {
        parse_mode: 'Markdown'
      });
      return;
    }

    if (data.startsWith('addcat:')) {
      const categoryId = data.slice(7);
      const ctx = pendingAdd[fromChatId];
      if (!ctx || ctx.step !== 'category') return;
      delete pendingAdd[fromChatId];
      try {
        const data2 = loadServices();
        const baseId = slugify(ctx.name) || 'new-service';
        const id = makeUniqueId(data2, baseId);
        const { priceLabel, priceDisplay, isQuote } = computePriceLabels(ctx.price, categoryId);
        const newService = {
          id,
          value: ctx.name,
          title: ctx.name,
          icon: '📌',
          price: ctx.price,
          priceLabel,
          priceDisplay,
          category: categoryId,
          isQuote,
          keywords: [],
        };
        data2.services.push(newService);
        saveServices(data2);
        bot.sendMessage(fromChatId, `✅ Added *${ctx.name}* (${id})\n💰 ${isQuote ? 'Quote' : '£' + ctx.price}\n📁 ${categoryId}`, {
          parse_mode: 'Markdown'
        });
      } catch (e) {
        bot.sendMessage(fromChatId, '❌ Error: ' + e.message);
      }
      return;
    }
  });

  bot.on('message', (msg) => {
    const fromChatId = String(msg.chat?.id);
    if (fromChatId !== adminChatId) return;

    const text = (msg.text || '').trim();

    // Handle reply with new price (after clicking Edit)
    if (pendingEdit[fromChatId]) {
      const serviceId = pendingEdit[fromChatId];
      delete pendingEdit[fromChatId];
      const amount = parseFloat(text);
      if (isNaN(amount) || amount < 0) {
        bot.sendMessage(fromChatId, '❌ Invalid amount. Use a number ≥ 0.');
        return;
      }
      try {
        const data = loadServices();
        const s = data.services.find((x) => x.id === serviceId);
        if (!s) {
          bot.sendMessage(fromChatId, 'Service not found.');
          return;
        }
        const { priceLabel, priceDisplay, isQuote } = computePriceLabels(amount, s.category);
        s.price = amount;
        s.priceLabel = priceLabel;
        s.priceDisplay = priceDisplay;
        s.isQuote = isQuote;
        saveServices(data);
        bot.sendMessage(
          fromChatId,
          `✅ Updated ${s.icon || '📌'} ${s.title}: ${isQuote ? 'Quote' : '£' + amount}`
        );
      } catch (e) {
        bot.sendMessage(fromChatId, '❌ Error: ' + e.message);
      }
      return;
    }

    // Handle add-service flow (step-by-step)
    if (pendingAdd[fromChatId]) {
      const ctx = pendingAdd[fromChatId];
      if (ctx.step === 'name') {
        if (!text || text.length < 2) {
          bot.sendMessage(fromChatId, '❌ Enter a valid service name (at least 2 characters).');
          return;
        }
        pendingAdd[fromChatId] = { step: 'price', name: text };
        bot.sendMessage(fromChatId, `💰 Enter *price* (number, or 0 for Quote):`, {
          parse_mode: 'Markdown'
        });
      } else if (ctx.step === 'price') {
        const amount = parseFloat(text);
        if (isNaN(amount) || amount < 0) {
          bot.sendMessage(fromChatId, '❌ Invalid amount. Use a number ≥ 0.');
          return;
        }
        pendingAdd[fromChatId] = { step: 'category', name: ctx.name, price: amount };
        bot.sendMessage(fromChatId, '📁 Choose *category*:', {
          parse_mode: 'Markdown',
          reply_markup: buildCategoryKeyboard()
        });
      }
      return;
    }

    if (!text.startsWith('/')) return;

    const parts = text.split(/\s+/);
    const cmd = parts[0].toLowerCase();

    if (cmd === '/start' || cmd === '/menu') {
      bot.sendMessage(fromChatId, '👋 *N05 Admin* — Tap below to view services & manage prices:', {
        parse_mode: 'Markdown',
        reply_markup: buildMenuKeyboard()
      });
      return;
    }

    if (cmd === '/help') {
      const help = [
        '/help - Show this message',
        '/menu - All services (prices, edit)',
        '/combo - Special Offers / MOT + Service Combo Deals',
        '/addservice - Add a new service (step-by-step)',
        '/prices - List all services with current prices',
        '/price <id or value> <amount> - Set price (e.g. /price mot-test 55)',
      ].join('\n');
      bot.sendMessage(fromChatId, help);
      return;
    }

    if (cmd === '/menu') {
      try {
        sendMenu(fromChatId);
      } catch (e) {
        bot.sendMessage(fromChatId, 'Error loading menu: ' + e.message);
      }
      return;
    }

    if (cmd === '/addservice') {
      pendingAdd[fromChatId] = { step: 'name' };
      bot.sendMessage(fromChatId, '📝 *Add new service*\n\nReply with the *service name* (e.g. Battery Replacement):', {
        parse_mode: 'Markdown'
      });
      return;
    }

    if (cmd === '/combo') {
      bot.sendMessage(fromChatId, '🔥 *Special Offers – MOT + Service Combo Deals*\n\nTap a deal to view or edit price:', {
        parse_mode: 'Markdown',
        reply_markup: buildComboDealsKeyboard()
      });
      return;
    }

    if (cmd === '/prices') {
      try {
        const data = loadServices();
        const lines = data.services.map(
          (s) => `${s.id}: £${s.price}${s.isQuote ? ' (Quote)' : ''}`
        );
        bot.sendMessage(chatId, lines.length ? lines.join('\n') : 'No services found.');
      } catch (e) {
        bot.sendMessage(chatId, 'Error reading services: ' + e.message);
      }
      return;
    }

    if (cmd === '/price') {
      const rest = text.slice(cmd.length).trim();
      let idOrValue, amountStr;

      const quoted = rest.match(/^"([^"]+)"\s+(\d+(?:\.\d+)?)$/);
      if (quoted) {
        idOrValue = quoted[1];
        amountStr = quoted[2];
      } else {
        const bits = rest.split(/\s+/);
        if (bits.length < 2) {
          bot.sendMessage(chatId, 'Usage: /price <id or value> <amount> (e.g. /price mot-test 55 or /price "MOT Test" 55)');
          return;
        }
        idOrValue = bits[0];
        amountStr = bits[1];
      }

      const amount = parseFloat(amountStr);
      if (isNaN(amount) || amount < 0) {
        bot.sendMessage(chatId, 'Invalid amount. Use a number >= 0.');
        return;
      }

      try {
        const data = loadServices();
        const service = findService(data, idOrValue);
        if (!service) {
          bot.sendMessage(chatId, `Service not found: ${idOrValue}`);
          return;
        }

        const { priceLabel, priceDisplay, isQuote } = computePriceLabels(amount, service.category);
        service.price = amount;
        service.priceLabel = priceLabel;
        service.priceDisplay = priceDisplay;
        service.isQuote = isQuote;

        saveServices(data);
        bot.sendMessage(
          chatId,
          `Updated ${service.title} (${service.id}): £${amount}\npriceLabel: ${priceLabel}\npriceDisplay: ${priceDisplay}`
        );
      } catch (e) {
        bot.sendMessage(chatId, 'Error updating price: ' + e.message);
      }
      return;
    }
  });

  // Set bot menu commands (visible when admin taps / in Telegram)
  bot.setMyCommands([
    { command: 'start', description: '📋 Show menu' },
    { command: 'menu', description: '📋 Services menu' },
    { command: 'addservice', description: '➕ Add new service' },
    { command: 'prices', description: '💰 List all prices' },
    { command: 'price', description: '✏️ Set price' },
    { command: 'help', description: '❓ Help' },
  ]).catch(() => {});

  console.log('Telegram admin bot polling started');
  return bot;
}

module.exports = { initAdminBot };
