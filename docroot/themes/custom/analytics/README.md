<div align="center">

# Analytics Theme

[![Dev Site Analytics](https://img.shields.io/badge/site-asu_analytics-blue.svg)](https://dev-asu-analytics.ws.asu.edu)

A custom sub-theme of the Webspark Renovation theme, made for the Analytics website.

[Features](#features) •
[Requirements](#requirements) •
[Notes](#notes) •
[Resources](#resources) •
[To Do](#to-do)

</div>
<br>
<br>

# Features

This theme utilizes a large amount of custom functionality provided by Drupal for theming. Instead of documenting everything in this README, you will find documentation within each file of this theme as needed. Files, functions, varables, etc. have all been documented to allow future developers an adequate foundational understanding of the workings of this theme.

<div align="right"><a href="#analytics-theme">↑ Top</a></div>
<br>
<br>

# Requirements

- Drupal 9+
- Webspark 2

<div align="right"><a href="#analytics-theme">↑ Top</a></div>
<br>
<br>

# Notes

## Blocks vs Components?

In the context of Drupal, many reusable pieces of content are referred to as "blocks". We want to use this terminology when also building custom pieces of code for some design elements. However, it may not always make sense to do so. To keep things as simple as possible, the Analytics website will use the term "block" to refer to any piece of custom templating code (`html.twig` files) that are meant to be used by the user via the Layout Builder. For pieces of custom tempating code that is meant for internal use only (for example, template partials meant to be included into other templates), we will use the term "component".

## Analytics specific class names

Given the explantion of Blocks vs Components above, in some areas you may see names using the `ab__` or `ac__` prefix. These stand for "Analytics Block" or "Analytics Component", respectively.

## The use of Twig and HTML Twig file extensions

When Drupal decided to use Twig, they added a "Drupalism" in the sense that Drupal will only render Twig templates that use the extension `html.twig` as opposed to the standard `.twig`. Although this is inherently stupid, there is one advantage that comes from this. When themeing, it gives developers a way to diffrentiate templates that are "final" versus "abstract". Basically, use `.twig` for templates that are meant to be included into or extended by other templates, and use `.html.twig` for templates that are meant to be used by Drupal.

<div align="right"><a href="#analytics-theme">↑ Top</a></div>
<br>
<br>

# Resources

- [Webspark 2](https://brandguide.asu.edu/execution-guidelines/web/building-sites/webspark)

<div align="right"><a href="#analytics-theme">↑ Top</a></div>
<br>
<br>

# To Do

- Document all custom Twig functions and variables

<div align="right"><a href="#analytics-theme">↑ Top</a></div>
