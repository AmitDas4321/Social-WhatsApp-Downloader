![Pinterest WhatsApp Downloader Hero Image](https://i.ibb.co/m5hMjSmp/Pinterest-Whats-App-Downloader.png)

<h1 align="center">Pinterest WhatsApp Downloader</h1>

<p align="center">
  <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/35/Pinterest_Logo.svg/1024px-Pinterest_Logo.svg.png" width="120" alt="Pinterest Logo"/>
</p>
<p align="center">
  <b>Download Pinterest videos and media directly to WhatsApp using a lightweight PHP API</b>
</p>
<p align="center">
  <a href="#-features">Features</a> •
  <a href="#-configuration">Configuration</a> •
  <a href="#-set-webhook">Set Webhook</a> •
  <a href="#-usage">Usage</a> •
  <a href="#-file-structure">File Structure</a> •
  <a href="#-example">Example</a> •
  <a href="#-to-do">To Do</a> •
  <a href="#-author">Author</a> •
  <a href="#-license">License</a>
</p>

---

## 📌 Overview

**Pinterest WhatsApp Downloader** lets you send Pinterest links to your WhatsApp, and receive the media (videos/images) back within seconds. The system listens for incoming WhatsApp webhook messages, extracts valid Pinterest URLs, downloads the media, and replies via WhatsApp with the downloadable content.

> **Built with PHP** — lightweight, fast, and deployable on any standard server.

---

## 🚀 Features

- ⚡️ Download videos and images from Pinterest directly via WhatsApp
- 🔗 Webhook-based automation (no polling required)
- 📝 Built-in debug logging system
- 🪶 Lightweight, fast, and no database required
- 💬 Easy to set up and use

---

## 🛠️ Configuration

1. **Edit `config.php`:**

    ```php
    define('API_BASE', 'https://api.amitdas.site/Pinterest/api/');
    define('WHATSAPP_INSTANCE_ID', 'YOUR_INSTANCE_ID');
    define('WHATSAPP_ACCESS_TOKEN', 'YOUR_ACCESS_TOKEN');
    ```

    > Get your WhatsApp API credentials from [textsnap.in](https://textsnap.in/)

---

## 🔗 Set Webhook

Once your files are uploaded to a PHP server, set your webhook:

```
https://textsnap.in/api/set_webhook?webhook_url=https://yourwebsite.com/index.php&enable=true&instance_id=YOUR_INSTANCE_ID&access_token=YOUR_ACCESS_TOKEN
```

---

## 📝 Usage

1. **Deploy** the code to any PHP-supported server.
2. **Configure** your `config.php` file.
3. **Set** the webhook URL using the endpoint above.
4. **Send** a Pinterest link to your WhatsApp number.
5. **Receive** the media directly in your WhatsApp chat! 🎥✅

---

## 📂 File Structure

```text
📁 project-root
├── config.php         # API keys and helper functions
├── index.php          # Webhook logic
```

---

## 📸 Example

**Send a message:**

```
https://in.pinterest.com/pin/996632592567664852/
```

**And receive the downloadable video automatically.**

---

## 📌 To Do

- [ ] Add support for image downloading
- [ ] Add Telegram integration
- [ ] Add web UI preview

---

## 👨‍💻 Author

| [<img src="https://avatars.githubusercontent.com/u/112541611?v=4" width="60" alt="Amit Das"/>](https://amitdas.site) |
|:---:|
| [Amit Das](https://amitdas.site) |

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---
