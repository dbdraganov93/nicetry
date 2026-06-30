# NiceTry SDK quick start

Yes, the intended client experience should be simple:

```php
<?php

require __DIR__ . '/sdk/php/NiceTry.php';

use NiceTry\Sdk\NiceTry;

$NiceTry = new NiceTry('https://api.nicetry.example', getenv('NICETRY_API_KEY'));
$html = $NiceTry->request('google.com', 'DE');
```

The helper sends `POST /v1/fetch` with a public URL and country code/name, then returns the raw origin body. Use `requestEnvelope()` when you need status, content type, country, and body metadata.

## Register and authenticate

```bash
curl -s https://api.nicetry.example/auth/register \
  -H 'Content-Type: application/json' \
  -d '{"email":"you@example.com","password":"secret","plan":"starter"}'

TOKEN=$(curl -s https://api.nicetry.example/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"user@geoproxy.test","password":"password"}' | jq -r .token)
```

## Direct fetch API

```bash
curl -s https://api.nicetry.example/v1/fetch \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"url":"https://google.com","country":"DE","response":"envelope"}'
```

## PHP

```php
<?php

require __DIR__ . '/sdk/php/NiceTry.php';

use NiceTry\Sdk\NiceTry;

$NiceTry = new NiceTry('https://api.nicetry.example', getenv('NICETRY_API_KEY'));
$html = $NiceTry->request('google.com', 'DE');
$envelope = $NiceTry->requestEnvelope('https://ifconfig.me/ip', 'Germany');
```

## JavaScript / Node.js

```javascript
class NiceTry {
  constructor(baseUrl = 'https://api.nicetry.example', apiKey = process.env.NICETRY_API_KEY) {
    this.baseUrl = baseUrl.replace(/\/$/, '');
    this.apiKey = apiKey;
  }

  async request(url, country) {
    const normalizedUrl = url.includes('://') ? url : `https://${url}`;
    const response = await fetch(`${this.baseUrl}/v1/fetch`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(this.apiKey ? { Authorization: `Bearer ${this.apiKey}` } : {}),
      },
      body: JSON.stringify({ url: normalizedUrl, country, response: 'raw' }),
    });

    if (!response.ok) throw new Error(`NiceTry request failed: ${response.status}`);
    return response.text();
  }
}

const NiceTryClient = new NiceTry();
const html = await NiceTryClient.request('google.com', 'DE');
```

## Python

```python
import os
import requests

class NiceTry:
    def __init__(self, base_url="https://api.nicetry.example", api_key=None):
        self.base_url = base_url.rstrip("/")
        self.api_key = api_key or os.getenv("NICETRY_API_KEY")

    def request(self, url, country):
        normalized_url = url if "://" in url else f"https://{url}"
        headers = {"Content-Type": "application/json"}
        if self.api_key:
            headers["Authorization"] = f"Bearer {self.api_key}"
        response = requests.post(
            f"{self.base_url}/v1/fetch",
            json={"url": normalized_url, "country": country, "response": "raw"},
            headers=headers,
            timeout=60,
        )
        response.raise_for_status()
        return response.text

NiceTryClient = NiceTry()
html = NiceTryClient.request("google.com", "DE")
```

## Java

```java
import java.net.URI;
import java.net.http.HttpClient;
import java.net.http.HttpRequest;
import java.net.http.HttpResponse;

public final class NiceTry {
    private final String baseUrl;
    private final String apiKey;
    private final HttpClient http = HttpClient.newHttpClient();

    public NiceTry(String baseUrl, String apiKey) {
        this.baseUrl = baseUrl.replaceAll("/$", "");
        this.apiKey = apiKey;
    }

    public String request(String url, String country) throws Exception {
        String normalizedUrl = url.contains("://") ? url : "https://" + url;
        String body = "{\"url\":\"" + normalizedUrl + "\",\"country\":\"" + country + "\",\"response\":\"raw\"}";
        HttpRequest request = HttpRequest.newBuilder()
            .uri(URI.create(baseUrl + "/v1/fetch"))
            .header("Content-Type", "application/json")
            .header("Authorization", "Bearer " + apiKey)
            .POST(HttpRequest.BodyPublishers.ofString(body))
            .build();
        HttpResponse<String> response = http.send(request, HttpResponse.BodyHandlers.ofString());
        if (response.statusCode() < 200 || response.statusCode() >= 300) {
            throw new IllegalStateException("NiceTry request failed: " + response.statusCode());
        }
        return response.body();
    }
}
```

## Production onboarding flow

1. Register with `POST /auth/register`.
2. Store the returned `api_key.secret` once; it is the value for `NICETRY_API_KEY`.
3. Start checkout with `POST /v1/billing/checkout` using `provider=stripe&method=card` for cards, `provider=stripe&method=google_pay` for Google Pay through Stripe, or `provider=paypal&method=paypal` for PayPal.
4. After checkout succeeds, call `$NiceTry->request('google.com', 'DE')`.

Stripe is the recommended default for cards and wallets; PayPal is implemented as a second provider for customers that prefer PayPal-branded checkout.
