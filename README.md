# BCM Auditor

Token-protected JSON report plugin for WordPress, used by **BCM Network** to help size/migrate VPS hosting.

## What it provides

A read-only JSON endpoint with:
- disk usage + file counts
- database size estimate
- runtime limits (PHP)

## Usage

1. Install and activate
2. WordPress admin → **Tools → BCM Auditor**
3. Generate token/link
4. Use the endpoint:

`GET /wp-json/bcm-auditor/v1/report?token=YOUR_TOKEN`

Optional: `&refresh=1`

## Documentation

- WordPress plugin metadata: [`readme.txt`](./readme.txt)

---

## PT-BR (Resumo)

Plugin que gera um relatório JSON tokenizado para auditoria técnica (tamanho de disco, arquivos, banco e limites), usado na **BCM Network**.
