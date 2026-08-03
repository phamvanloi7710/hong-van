# ADR-014 — Unified lead intake and immutable submissions

## Status

Accepted for the P38 Backend/Admin scope. Public Page Builder form-block wiring remains deferred until the public frontend template is supplied.

## Decision

- `hongvan_leads` is the canonical inbox and workflow record for contact, product quote, transport and warehouse enquiries.
- The validated public payload and direct contact fields are encrypted at rest in the lead record and become immutable after creation.
- Transport and warehouse domain requests remain linked capability records; their contact columns contain only a pointer to the linked lead, avoiding duplicate personal contact data.
- Employees may only append status events, assignments and internal notes through the lead workflow API.
- Duplicate submissions are identified by a short-lived canonical HMAC; clients may additionally send `Idempotency-Key`.
- Public submissions require explicit consent, the current privacy-policy version, a honeypot check and the shared public-form rate limiter.
- Notifications run on the queue with retry/backoff and contain only safe identifiers.
- There is no direct lead delete endpoint. Expired personal data is anonymized by `php artisan leads:anonymize-expired`; CSV export is permission-gated and formula-injection safe.

## Consequences

- Admin receives one consistent inbox and timeline across all enquiry types.
- Original customer content cannot be corrected in place; staff must add an internal note that preserves provenance.
- Public form blocks must call the P38 endpoints when the deferred frontend/Page Builder work resumes.
