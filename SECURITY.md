# Security Policy

Security is an important part of the Hong Van project.

If you discover a security vulnerability, please report it privately so the maintainers have an opportunity to investigate and coordinate a fix before technical details are disclosed publicly.

## Supported Versions

The project is currently under active development.

Security fixes are provided for the latest code on the `main` branch and for the latest published release when releases are available.

| Version                                   | Supported |
| ----------------------------------------- | --------- |
| Latest `main`                             | ✅         |
| Latest published release                  | ✅         |
| Older unreleased snapshots                | ❌         |
| Unsupported forks or modified deployments | ❌         |

This policy may be updated when the project adopts a formal long-term release strategy.

## Reporting a Vulnerability

**Do not open a public GitHub Issue for suspected security vulnerabilities.**

Use GitHub's private vulnerability reporting feature for this repository:

1. Open the repository on GitHub.
2. Go to **Security**.
3. Open **Advisories**.
4. Select **Report a vulnerability**.
5. Provide the requested details privately to the maintainers.

Private vulnerability reports are preferred because they allow investigation and remediation without exposing potentially exploitable information before a fix is available.

## What to Include

Please provide enough information to reproduce and understand the vulnerability.

Useful information includes:

* affected component or endpoint
* vulnerability type
* impact
* prerequisites or required permissions
* reproducible steps
* minimal proof of concept, when appropriate
* affected commit, branch, or version
* relevant configuration
* suggested mitigation, if known

Screenshots and logs may be included when useful.

Before submitting, remove:

* passwords
* access tokens
* cookies
* private keys
* production credentials
* personal information
* customer data
* production database contents

## Sensitive Proofs of Concept

Please keep exploit details private until the maintainers confirm that disclosure is appropriate.

Do not:

* publish a working exploit before remediation
* access or modify data that does not belong to you
* intentionally degrade service availability
* perform destructive testing
* persist access after demonstrating the issue
* use a vulnerability to pivot into unrelated systems
* disclose secrets or personal data obtained during testing

Use the minimum activity necessary to demonstrate the security issue.

## Scope

Security reports are especially useful for issues involving:

* authentication or session handling
* authorization or permission bypass
* CSRF
* injection vulnerabilities
* SQL injection
* cross-site scripting
* unsafe HTML or rich-text handling
* arbitrary code execution
* file upload validation
* path traversal
* insecure direct object references
* sensitive information disclosure
* exposed secrets
* preview-link authorization or expiration
* Page Builder content execution
* rate-limit bypasses
* privilege escalation
* dependency vulnerabilities with practical project impact

## Project Security Boundaries

The project intentionally enforces several security properties.

### Administration

Administrative routes and operations are expected to remain authenticated and permission-aware.

Security controls such as:

* authorization
* CSRF protection
* session security
* rate limiting
* audit logging

must not be disabled merely to make a feature work.

### Page Builder

Page Builder content stored in the database must not execute arbitrary:

```text
PHP
Blade
JavaScript
```

Blocks and bindings must follow controlled server-side schemas and allowlists.

### Uploads

Uploaded content must be validated according to the application's security rules.

Do not assume a file is safe based only on its extension or client-provided MIME type.

### Secrets

Secrets, credentials, environment files, private keys, tokens, and production data must not be committed to the repository.

### Reference Sources

The following locations are reserved for reference material:

```text
Template/
FrontEndTemplate/
SourceIntegrations/
```

Proprietary, purchased, licensed, or external source packages must not be committed to the public repository.

## Dependency Vulnerabilities

The project uses automated dependency auditing in CI, including Composer and npm security checks.

A dependency advisory is still worth reporting when:

* it is not detected by the existing pipeline
* the vulnerable functionality is demonstrably reachable
* project-specific usage increases the impact
* additional mitigation is required beyond upgrading the dependency

Please include the affected dependency and advisory identifier when available.

## Response Process

After receiving a private report, maintainers will aim to:

1. acknowledge the report
2. reproduce and validate the issue
3. assess severity and affected versions
4. develop and test a remediation
5. prepare an advisory when appropriate
6. coordinate disclosure with the reporter
7. publish the fix

Response times may vary based on complexity and maintainer availability.

No fixed response-time SLA is currently guaranteed.

## Disclosure

Please allow reasonable time for a fix before public disclosure.

When appropriate, maintainers may publish:

* a GitHub Security Advisory
* a patched release
* upgrade instructions
* mitigation guidance
* CVE information when applicable

Credit may be given to reporters who want public acknowledgement.

## Out of Scope

The following generally do not qualify as project vulnerabilities unless they expose a concrete security impact:

* unsupported local environment configuration
* vulnerabilities caused solely by intentionally disabling project security controls
* attacks requiring modification of trusted application source code
* issues exclusively affecting unsupported third-party forks
* theoretical findings without a reachable security impact
* dependency advisories that do not affect any reachable project functionality
* social-engineering attacks unrelated to the project's implementation

## Public Issues

Regular bugs, documentation problems, feature requests, and non-sensitive security-hardening suggestions may be reported through normal public project channels.

If there is any reasonable chance that a report contains exploitable security information, use private vulnerability reporting instead.

## Safe Harbor

Security research performed in good faith, within the boundaries of this policy, is welcomed.

Researchers should:

* avoid privacy violations
* avoid data destruction
* avoid service disruption
* limit testing to what is necessary to demonstrate the issue
* report vulnerabilities privately
* allow maintainers reasonable time to remediate

The project maintainers will not intentionally pursue action against good-faith researchers who comply with this policy.

## Contact

The preferred security contact is GitHub Private Vulnerability Reporting for this repository.

Do not publish vulnerability details in a public Issue when private reporting is available.
