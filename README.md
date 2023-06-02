# Analytics Chooser for Silverstripe settings

Provides a basic set of analytics services, to choose within the site settings administration area.

Currently supported:

+ GA4
+ GA3
+ GTM
+ GTM (with CSP nonce support)

Developers can provide their own service by creating a class that implements the abstract service.

## Installation

```sh
composer require nswdpc/silverstripe-analytics-chooser
```

## Usage

Once installed, your theme will need to include the templates provided by this module:

### Document <head>

```html
<% include NSWDPC/Analytics/Implementation %>
```

### Document <body>

Apply an iframe if using GTM:

```html
<% include NSWDPC/Analytics/GTMIframe %>
```

You can override these templates in your project theme, in the usual Silverstripe way.

## License

[BSD-3-Clause](./LICENSE.md)

## Documentation

* [Documentation](./docs/en/001_index.md)

## Configuration

None, currently

## Maintainers

+ [dpcdigital@NSWDPC:~$](https://dpc.nsw.gov.au)

## Bugtracker

We welcome bug reports, pull requests and feature requests on the Github Issue tracker for this project.

Please review the [code of conduct](./code-of-conduct.md) prior to opening a new issue.

## Security

If you have found a security issue with this module, please email digital[@]dpc.nsw.gov.au in the first instance, detailing your findings.

## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.

Please review the [code of conduct](./code-of-conduct.md) prior to completing a pull request.
