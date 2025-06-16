This API project is designed to **download Pinterest media directly through WhatsApp**. Users can send Pinterest links to a connected WhatsApp number, and the system automatically processes and replies with the downloadable media content.

It is built using PHP and provides a lightweight API structure, ideal for quick integrations or microservices. The code is organized with a main entry point, configuration setup, and a dedicated API endpoint.

This PHP-based project provides a lightweight API structure, ideal for quick integrations or microservices. It supports JSON-based HTTP interactions and is organized for modular expansion. The code is structured with a main entry point, configuration setup, and a dedicated API endpoint.

---

### 1. Configuration File - `config.php`

This file defines global configuration variables and functions used across the project.

```php
<?php
// Pinterest downloader API base URL (without "?url=")
define('PINTEREST_API_BASE', 'https://api.amitdas.site/Pinterest/api/');

// WhatsApp API config
define('WHATSAPP_INSTANCE_ID', '6778CD9E32DE3');
define('WHATSAPP_ACCESS_TOKEN', '677846eb647bc');

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
```

**Key Elements:**

- `PINTEREST_API_BASE`: Base URL for the Pinterest media downloader.
- `WHATSAPP_INSTANCE_ID` and `WHATSAPP_ACCESS_TOKEN`: Auth credentials used with the TextSnap WhatsApp API.
- `sanitize_input($data)`: Utility function to clean user input, preventing XSS and basic injection.

> To get your `WHATSAPP_INSTANCE_ID` and `WHATSAPP_ACCESS_TOKEN`, visit [http://textsnap.in/](http://textsnap.in/).
>
> After configuration, set your webhook using the following URL format:
>
> ```
> https://textsnap.in/api/set_webhook?webhook_url=https://yourwebsite.com/index.php&enable=true&instance_id=YOUR_INSTANCE_ID&access_token=YOUR_ACCESS_TOKEN
> ```
>
> Replace `https://yourwebsite.com/index.php` with your actual webhook file URL.

---

### 2. Main Entry Point - `index.php`

This file handles incoming webhook requests, extracts Pinterest URLs from WhatsApp messages, downloads the video using an API, and sends it back via the WhatsApp API.

```php
<?php
// ... configuration and logging setup

$DEBUG_LOG = false; // Enable debug.log if needed

// Receive and parse webhook input
// Extract Pinterest link, call API, and send media to WhatsApp

// Full logic includes validation, cURL requests, and response handling
?>
```

**Purpose:**

- Processes WhatsApp webhook data.
- Extracts Pinterest video link from user messages.
- Downloads the video using your Pinterest API.
- Sends the video back via the WhatsApp API.
- Supports debug logging for troubleshooting.

**Purpose:**

- Processes WhatsApp webhook data.
- Extracts Pinterest video link from user messages.
- Downloads the video using your Pinterest API.
- Sends the video back via the WhatsApp API.
- Supports debug logging for troubleshooting.

---

### Usage Instructions

1. Deploy the project files to a PHP-supported server.
2. Update the `config.php` with your actual `PINTEREST_API_BASE`, `WHATSAPP_INSTANCE_ID`, and `WHATSAPP_ACCESS_TOKEN`.
3. Set the webhook using the format:
   ```
   https://textsnap.in/api/set_webhook?webhook_url=https://yourwebsite.com/index.php&enable=true&instance_id=YOUR_INSTANCE_ID&access_token=YOUR_ACCESS_TOKEN
   ```
4. Test by sending a Pinterest video link to your connected WhatsApp number.

---

### Future Improvements

- Add routing logic for multiple endpoints.
- Introduce authentication.
- Connect to a database for dynamic responses.

---

**Author:** Amit Das\
**Project:** Pinterest Media Downloader via WhatsApp (PHP API)

