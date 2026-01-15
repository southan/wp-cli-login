southan/wp-cli-login
====================

Log in to WordPress admin



Quick links: [Using](#using) | [Installing](#installing) | [Contributing](#contributing) | [Support](#support)

## Using

~~~
wp login [<target>...] [--user=<id|login|email>] [--timeout=<timeout>] [--generate] [--open] [--url=<url>]
~~~

Instant, automatic login to any WordPress that WP-CLI has access to.

This command installs a self-destructing MU plugin that listens for a
unique, secret URL and signs the requesting user into WordPresss.

**OPTIONS**

	[<target>...]
		Log in to WP-CLI alias or remote WordPress (see global parameter --ssh).

	[--user=<id|login|email>]
		Log in as specific user. Defaults to first administrator.

	[--timeout=<timeout>]
		Default 30 seconds. Accepts time units e.g. 1d 6h 30m

	[--generate]
		Generate the login script (MU plugin) for manual installation.

	[--open]
		Open the login URL in your system browser. Default true (unless --generate).

	[--url=<url>]
		Defaults to WordPress URL.

**EXAMPLES**

    # Log in as bert
    $ wp login --user=bert

    # Log in to remote WordPress over SSH
    $ wp login user@host

    # Log in to WP-CLI alias @dev
    $ wp login @dev

    # Print login URL instead of opening it & set timeout to 5 minutes
    $ wp login --no-open --timeout=5m
    https://example.com/login/ce24f50a0126d75694b3cf2dedb5a64d6f3636cb0ae3d08e2c8c509b511c7acc

    # Generate login script for manual installation
    $ wp login --generate --url=https://example.com
    https://example.com/login/f4c42e9f5d854827bf47364d817025dc0da27b3c447bd70152de61d27234a9b3
    <?php
    ...

    # Override URL if rewrite not supported (default login URL format)
    $ wp login --url='https://example.com/?login-key=<key>'

## Installing

Installing this package requires WP-CLI v2.5 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install the latest stable version of this package with:

```bash
wp package install southan/wp-cli-login:@stable
```

To install the latest development version of this package, use the following command instead:

```bash
wp package install southan/wp-cli-login:dev-main
```

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

For a more thorough introduction, [check out WP-CLI's guide to contributing](https://make.wordpress.org/cli/handbook/contributing/). This package follows those policy and guidelines.

### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/southan/wp-cli-login/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/southan/wp-cli-login/issues/new). Include as much detail as you can, and clear steps to reproduce if possible. For more guidance, [review our bug report documentation](https://make.wordpress.org/cli/handbook/bug-reports/).

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/southan/wp-cli-login/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, [please follow our guidelines for creating a pull request](https://make.wordpress.org/cli/handbook/pull-requests/) to make sure it's a pleasant experience. See "[Setting up](https://make.wordpress.org/cli/handbook/pull-requests/#setting-up)" for details specific to working on this package locally.

## Support

GitHub issues aren't for general support questions, but there are other venues you can try: https://wp-cli.org/#support


*This README.md is generated dynamically from the project's codebase using `wp scaffold package-readme` ([doc](https://github.com/wp-cli/scaffold-package-command#wp-scaffold-package-readme)). To suggest changes, please submit a pull request against the corresponding part of the codebase.*
