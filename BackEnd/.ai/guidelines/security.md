# Security guideline

Use Sanctum cookie/session with CSRF for same-origin admin. Deny by default, use policies, sanitize rich text, validate real MIME, and never execute Page Builder code from database.
