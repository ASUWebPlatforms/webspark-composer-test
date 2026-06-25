<div align="center">

# My Apps

[![Dev Site My Apps](https://img.shields.io/badge/site-myapps_ws2-blue.svg)](https://dev-myapps-ws2.ws.asu.edu)

A custom module for providing access checks and download capabilities for the My Apps website.

[Secrets Management](#secrets-management) •
[Routes](#routes) •
[Controllers](#controllers) •
[Editing Content](#editing-content) •
[Resources](#resources)

</div>
<br>
<br>

# Secrets Management

This module requires sensitive data in the form of access keys order to work. These items are commonly stored in configuration files via a module settings form, but due to the sensitivity of some of these keys we needed an extra layer of security. We use the [Pantheon Secrets Manager Plugin](https://github.com/pantheon-systems/terminus-secrets-manager-plugin) to store these secrets to the Pantheon environment, and the [Pantheon Customer Secrets Plugin](https://github.com/pantheon-systems/customer-secrets-php-sdk) to retreive them.

## A note on setting secrets for My Apps

There will be times when we will need to swap between production and development credentials for certain services. At the moment, the secrets are set to use the production secrets for everything. In the event that you need to move to an alternate Pantheon environment, you can utilize the [environment override](https://github.com/pantheon-systems/terminus-secrets-manager-plugin#environment-override) feature to change the secret value specifically for your test environment.

## Setting an RSA Key as a secret

A special note needs to be made for secrets where the value is an RSA Key. The beginning `-----` characters will cause the plugin to throw an error because it thinks you are trying to run an option that does not exist. As a result, for settings secrets of this nature we need to use a specific syntax in order for it to work.

First, store the RSA value as a variable:

```bash
export FOO="-----BEGIN RSA PRIVATE KEY----- foobarfoobarfoobar"
```

You can print the variable to ensure the value was stored properly, including line breaks:

```bash
echo $FOO
```

Next, use this alternate syntax to properly set the secret:

```bash
terminus secret:site:set --scope=web --type=runtime -- myapps-ws2 <secret-name> "$FOO"
```

Because we need to use this alternate syntax to set these types of secrets, we also need to use this same syntax and process when updating the secret.

```bash
export FOO="-----BEGIN RSA PRIVATE KEY----- foobarfoobarfoobar"
terminus secret:site:set -- myapps-ws2 <secret-name> "$FOO"
```

<div align="right"><a href="#my-apps">↑ Top</a></div>
<br>
<br>

# Routes

This module provides a route at `ednaquery/checkAccess/{nid}` for all downloads where we will be providing the software. This route triggers the `AsuMyappsController::download` method, which will first check the users access level via EDNA, and either allow or deny the software download.

<div align="right"><a href="#my-apps">↑ Top</a></div>
<br>
<br>

# Controllers

## MyAppsController

This class is responsible managing the downloads and access checks for the My Apps website.

## EDNAController

Queries EDNA server for user/group affiliations. It is important to note that we are using the [EDNA Check Access Proxy](https://github.com/ASU/edna-checkaccess-proxy), as the default EDNA port is no longer open to the internet.

## AWSController

Queries AWS to generate the [Cloudfront Signed URL](https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/private-content-signed-urls.html) or [S3 Presigned URLS](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-presigned-url.html). The default method is to use the Cloudfront Signed URLS. If at a future time this is no longer available to us, we can fallback to the S3 Presigned URLs.

<div align="right"><a href="#my-apps">↑ Top</a></div>
<br>
<br>

# Editing Content

Editing content on this website is straightforward, however because there may be more than one person editing the website at the same time, editors may run into an issue where Drupal gives them an error that `The form has become outdated. Press the back button, copy any unsaved work in the form, and then reload the page.`

This is due to the CSRF tokens on that particular node becoming invalid. Essentially, Drupal thinks that two people are trying to edit the same piece of content at the same time. This can occur for a variety of reasons (the primary being that there are indeed two people editing the site at the same time). Although this is an error, it is just Drupal trying to keep itself secure and is not a reason for concern.

As this may become annoying if something is trying a edit content in a hurry, there is a setting in the `My Apps` module to allow an editor to bypass the CSRF security checks altogether. **It is important to note that CSRF checks are a good thing, and this setting should be used sparingly!**

<div align="right"><a href="#my-apps">↑ Top</a></div>
<br>
<br>

# Resources

- [EDNA Check Access Proxy](https://github.com/ASU/edna-checkaccess-proxy)
- [Pantheon Secrets Manager Plugin](https://github.com/pantheon-systems/terminus-secrets-manager-plugin)
- [Pantheon Customer Secrets Plugin](https://github.com/pantheon-systems/customer-secrets-php-sdk)
- [Cloudfront Signed URL](https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/private-content-signed-urls.html)
- [Cloudfront URLSigner](https://docs.aws.amazon.com/aws-sdk-php/v3/api/class-Aws.CloudFront.UrlSigner.html)
- [S3 Presigned URLS](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-presigned-url.html)

<div align="right"><a href="#my-apps">↑ Top</a></div>
