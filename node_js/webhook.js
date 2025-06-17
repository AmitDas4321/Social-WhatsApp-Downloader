export default async function handler(req, res) {
  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method Not Allowed' });
  }

  const DEBUG_LOG = false;

  function debugLog(msg) {
    if (DEBUG_LOG) console.log(`[DEBUG] ${msg}`);
  }

  debugLog("Script started");
  const data = req.body;

  if (!data || !data.data || !data.data.message) {
    debugLog("Invalid JSON format");
    return res.status(400).send("❌ Invalid webhook format.");
  }

  const bodyMsg = data.data.message.body_message || {};
  const remoteJid = data.data.message.message_key?.remoteJid || '';

  const ignorePatterns = ['@g.us', '@broadcast', '@newsletter', 'status@broadcast', '@lid', '-@g.us', 'g.us', 'broadcast', 'newsletter', 'status'];
  if (ignorePatterns.some(pattern => remoteJid.includes(pattern))) {
    debugLog(`Ignored remoteJid: ${remoteJid}`);
    return res.status(200).send("❌ Ignored remoteJid");
  }

  let messageText = '';
  if (bodyMsg.messages?.conversation) {
    messageText = bodyMsg.messages.conversation;
  } else if (bodyMsg.content) {
    messageText = bodyMsg.content;
  } else if (bodyMsg.messages?.extendedTextMessage?.text) {
    messageText = bodyMsg.messages.extendedTextMessage.text;
  }

  if (!messageText || !remoteJid) {
    debugLog("Missing messageText or remoteJid");
    return res.status(400).send("❌ Invalid webhook format.");
  }

  const numberMatch = remoteJid.match(/^(\d+)@/);
  if (!numberMatch) {
    debugLog(`Invalid remoteJid: ${remoteJid}`);
    return res.status(400).send("❌ Invalid remoteJid format.");
  }
  const number = numberMatch[1];

  const pinterestMatch = messageText.match(/(https:\/\/pin\.it\/[a-zA-Z0-9]+|https:\/\/(?:[a-z]+\.)?pinterest\.[a-z]+\/pin\/\d+\/?)/);
  if (!pinterestMatch) {
    debugLog("No Pinterest link found");
    return res.status(400).send("❌ No Pinterest link found in message.");
  }

  const pinterestUrl = pinterestMatch[0];
  debugLog(`Pinterest URL: ${pinterestUrl}`);

  const PINTEREST_API_BASE = process.env.PINTEREST_API_BASE; // e.g., https://api.example.com/Pinterest/api
  const WHATSAPP_INSTANCE_ID = process.env.WHATSAPP_INSTANCE_ID;
  const WHATSAPP_ACCESS_TOKEN = process.env.WHATSAPP_ACCESS_TOKEN;

  try {
    const response = await fetch(`${PINTEREST_API_BASE}?url=${encodeURIComponent(pinterestUrl)}`);
    if (!response.ok) {
      debugLog(`Downloader API HTTP error: ${response.status}`);
      return res.status(500).send(`❌ Downloader API HTTP error: ${response.status}`);
    }

    const apiData = await response.json();
    if (apiData.status !== 'success' || !apiData.media_url) {
      debugLog(`Pinterest API failed: ${JSON.stringify(apiData)}`);
      return res.status(500).send("❌ Failed to fetch Pinterest video.");
    }

    const mediaUrl = apiData.media_url;
    const title = apiData.title || 'Pinterest Video';

    const waPayload = {
      number,
      type: "media",
      message: title,
      media_url: mediaUrl,
      instance_id: WHATSAPP_INSTANCE_ID,
      access_token: WHATSAPP_ACCESS_TOKEN
    };

    const waResponse = await fetch("https://textsnap.in/api/send", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(waPayload)
    });

    if (!waResponse.ok) {
      const err = await waResponse.text();
      debugLog(`WhatsApp API error: ${waResponse.status} - ${err}`);
      return res.status(500).send(`❌ WhatsApp API error: ${waResponse.status}`);
    }

    debugLog("✅ Video sent successfully!");
    return res.status(200).send("✅ Video sent successfully!");

  } catch (err) {
    debugLog(`Unhandled error: ${err.message}`);
    return res.status(500).send("❌ Server error.");
  }
}
