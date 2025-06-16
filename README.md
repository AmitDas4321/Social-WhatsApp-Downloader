````markdown
# Pinterest WhatsApp Downloader

Download Pinterest videos and media directly via WhatsApp using a lightweight PHP API.

---

## 📌 Overview

This project enables users to send Pinterest links to a connected WhatsApp number. The system listens to webhook messages, extracts valid Pinterest URLs, downloads the media, and replies via WhatsApp with the downloadable content.

Built in PHP, this solution is lightweight, fast, and easy to deploy on any standard server.

---

## 🚀 Features

- Download videos and images from Pinterest via WhatsApp
- Webhook-based automation
- Built-in debug logging system
- Lightweight and fast performance
- No database required

---

## 🛠️ Configuration

Edit the `config.php` file:

```php
define('PINTEREST_API_BASE', 'https://api.amitdas.site/Pinterest/api/');
define('WHATSAPP_INSTANCE_ID', 'YOUR_INSTANCE_ID');
define('WHATSAPP_ACCESS_TOKEN', 'YOUR_ACCESS_TOKEN');
````

> Get your credentials from [https://textsnap.in/](https://textsnap.in/)

---

## 🔗 Set Webhook

After uploading your files to a PHP server, set the webhook using:

```
https://textsnap.in/api/set_webhook?webhook_url=https://yourwebsite.com/index.php&enable=true&instance_id=YOUR_INSTANCE_ID&access_token=YOUR_ACCESS_TOKEN
```

---

## 📝 Usage

1. Deploy this code to a PHP-supported server.
2. Configure your `config.php`.
3. Set the webhook URL using the above endpoint.
4. Send a Pinterest link to your WhatsApp number.
5. Receive the media directly in chat 🎥✅

---

## 📂 File Structure

```
📁 project-root
├── config.php         # API keys and helper function
├── index.php          # Webhook logic
```

---

## 📸 Example

Send a message like:

```
https://in.pinterest.com/pin/996632592567664852/
```

And receive the downloadable video automatically.

---

## 📌 To Do

* [ ] Add support for image downloading
* [ ] Add Telegram integration
* [ ] Add web UI preview

---

## 👨‍💻 Author

**Amit Das**
🔗 [https://amitdas.site](https://amitdas.site)

---

## 📄 License

This project is licensed under the MIT License.
