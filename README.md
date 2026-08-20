# PR Metadata Validator (`pr-metadata-validator`)

[![GitHub Action](https://img.shields.io/badge/GitHub%20Action-v1.0.0-blue?logo=github-actions)](https://github.com/marketplace/actions/pr-metadata-validator)
[![PHP Version](https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3-777BB4?logo=php)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

**A fast, zero-dependency PHP GitHub Action to enforce branch naming conventions, PR title formats, and issue tracker rules (Jira, Linear, GitHub Issues).**

Keep your Git history clean and traceable. `pr-metadata-validator` intercepts Pull Requests in real-time, validating branch prefixes, Regex patterns, and mandatory ticket keys before allowing merges.

---

## Key Features

* **⚡ Zero Dependencies & Blazing Fast:** Runs on native PHP CLI—executes in milliseconds without installing heavy Node modules or Docker images.
* **🏷️ Branch Name Enforcement:** Enforce prefixes like `feature/`, `bugfix/`, or custom Regex.
* **📋 PR Title Standards:** Validate semantic titles or require issue tracker IDs (e.g., `[JIRA-123] Add user auth`).
* **🎯 Issue Tracker Integration:** Ensures every PR and branch references an active ticket identifier (Jira, Linear, Trello, etc.).
* **⚙️ Highly Parametrized:** Enable, disable, or fine-tune rules directly through simple workflow inputs.
* **❌ Actionable Error Reporting:** Outputs clear failure explanations directly in the CI/CD execution logs.

---

## Quick Start

Add this workflow to your repository in `.github/workflows/validate-pr.yml`:

```yaml
name: "Validate PR Metadata"

on:
  pull_request:
    types: [opened, edited, synchronize]

jobs:
  validate:
    name: "PR & Branch Standards"
    runs-on: ubuntu-latest
    steps:
      - name: Checkout Code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Validate PR Metadata
        uses: tu-usuario/pr-metadata-validator@v1
        with:
          validate_branch: 'true'
          validate_title: 'true'
          require_ticket: 'true'
          allowed_prefixes: 'feature, bugfix, hotfix, release, chore'
```

---

## Configuration Options

Pass inputs via the `with:` block in your workflow step:

| Input Parameter | Type | Default Value | Description |
| :--- | :---: | :---: | :--- |
| `validate_branch` | `boolean` | `true` | Enable or disable branch name validation. |
| `validate_title` | `boolean` | `true` | Enable or disable Pull Request title validation. |
| `require_ticket` | `boolean` | `true` | Require an issue tracker key (e.g., `PROJ-123`). |
| `allowed_prefixes` | `string` | `feature, bugfix, hotfix` | Comma-separated list of allowed branch prefixes. |
| `branch_regex` | `string` | *(Auto-generated)* | Custom PCRE Regex to override default branch matching. |
| `title_regex` | `string` | *(Auto-generated)* | Custom PCRE Regex to override default title matching. |

---

## Usage Examples

### 1. Standard Jira / Linear Convention (Default)

Requires ticket IDs in both branch names and PR titles.

* **Valid Branch:** `feature/APP-102-login-screen`
* **Valid Title:** `[APP-102] Add Google OAuth support`

```yaml
- uses: tu-usuario/pr-metadata-validator@v1
  with:
    require_ticket: 'true'
    allowed_prefixes: 'feature, bugfix, hotfix'
```

### 2. Flexible Teams (No Mandatory Tickets)

Enforces prefix conventions without requiring a ticket key.

* **Valid Branch:** `feature/refactor-database-queries`
* **Valid Title:** `Refactor database queries for performance`

```yaml
- uses: tu-usuario/pr-metadata-validator@v1
  with:
    require_ticket: 'false'
    allowed_prefixes: 'feature, bugfix, hotfix, chore, docs'
```

### 3. Full Custom Regex Rules

Override all default behavior with your team's custom expressions.

```yaml
- uses: tu-usuario/pr-metadata-validator@v1
  with:
    branch_regex: '/^release\/v\d+\.\d+\.\d+$/' # Matches: release/v1.2.0
    title_regex: '/^REL-\d+: .+'           # Matches: REL-99: Production release
```

---

## How It Works

1. The Action reads the `GITHUB_EVENT_PATH` payload injected automatically by GitHub Actions.
2. It extracts the `head.ref` (branch name) and `title` from the Pull Request context.
3. PHP evaluates the metadata against PCRE2 regular expression rules using `preg_match`.
4. If validation fails, error messages are written to `STDERR`, exiting with code `1` to block the PR merge.

---

## Local Testing

To test the script locally without running a full CI pipeline:

1. Create a dummy event JSON file `event.json`:
   ```json
   {
     "pull_request": {
       "head": { "ref": "feature/JIRA-123-new-login" },
       "title": "[JIRA-123] Add login feature"
     }
   }
   ```
2. Execute the script with CLI environment variables:
   ```bash
   GITHUB_EVENT_PATH=event.json REQUIRE_TICKET=true php src/validate_pr.php
   ```

---

## Contributing

Contributions, bug reports, and feature requests are welcome!

1. Fork the project.
2. Create your feature branch (`git checkout -b feature/APP-101-new-rule`).
3. Commit your changes (`git commit -m '[APP-101] Add new validation rule'`).
4. Push to the branch (`git push origin feature/APP-101-new-rule`).
5. Open a Pull Request.

---

## License

This project is licensed under the [MIT License](LICENSE) - feel free to use it in personal and commercial projects.
