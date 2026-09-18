# Email Restriction (FOSSBilling module)

A FOSSBilling module that restricts which email domains clients may use when
they register or change the email address on their account. Domains can be
controlled with an optional whitelist and an optional blacklist.

## List format:

- One domain per line. Blank lines are ignored. Matching is case-insensitive.
- Lines starting with `#` are treated as comments.
- Subdomains are NOT matched by default.
- Use `*.example.com` to match any subdomain of `example.com`. The wildcard does not match the bare domain `example.com` itself.

## Installation

1. Copy the contents of the `src/` directory into a folder named `Emailrestriction` inside your FOSSBilling installation's `modules/` directory.
2. Log in to the FOSSBilling admin area, open `Extensions` tab and activate the extension.
3. Run the FOSSBilling cron job at least once so the module's event hooks are discovered and registered.

## Configuration

Open the settings page from `Extensions -> Email Restriction`.

## License

Apache-2.0
