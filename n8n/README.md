# Zoho Mail + OpenAI n8n Email Replier

Import `zoho-openai-email-replier.json` into n8n to create a workflow that:

1. Watches a Zoho Mail inbox through IMAP.
2. Sends the incoming email to the OpenAI Responses API.
3. Generates a concise, professional reply.
4. Sends the reply back through Zoho SMTP.

## Required Setup

### 1. OpenAI API key

Set this environment variable where n8n runs:

```bash
OPENAI_API_KEY=sk-...
```

The workflow uses the OpenAI Responses API at:

```text
https://api.openai.com/v1/responses
```

Default model in the workflow:

```text
gpt-5-mini
```

You can change it in the `Prepare Email For OpenAI` node if you prefer another OpenAI model.

### 2. Zoho IMAP credential

Create an IMAP credential in n8n.

For a Zoho-hosted custom domain address like `you@yourdomain.com`:

```text
Host: imappro.zoho.com
Port: 993
SSL/TLS: enabled
Username: your Zoho email address
Password: Zoho app password
```

For a personal Zoho address, use `imap.zoho.com` instead.

Then open the `Zoho IMAP Trigger` node and select that credential.

### 3. Zoho SMTP credential

Create an SMTP credential in n8n.

For a Zoho-hosted custom domain address like `you@yourdomain.com`:

```text
Host: smtppro.zoho.com
Port: 465
SSL/TLS: enabled
Username: your Zoho email address
Password: Zoho app password
```

For a personal Zoho address, use `smtp.zoho.com` instead.

Then open the `Send Zoho Reply` node and select that credential.

Also change `fromEmail` in the `Send Zoho Reply` node from:

```text
your-address@your-domain.com
```

to your real Zoho sender address.

## Professional Reply Behavior

The workflow prompt tells OpenAI to:

- Be warm, concise, helpful, and polished.
- Avoid inventing facts, prices, availability, policies, or commitments.
- Ask one clear follow-up question when important details are missing.
- Return only the email body, with no markdown, subject line, or signature.

## Safer First Run

Before activating the workflow, test it with a private Zoho inbox or change the final node to send replies to your own email address. Once the tone looks right, switch the final `toEmail` field back to:

```text
={{$json.to}}
```
