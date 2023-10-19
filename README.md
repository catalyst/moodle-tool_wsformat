Moodle Format Webservices
===================
![GitHub Workflow Status (branch)](https://img.shields.io/github/actions/workflow/status/catalyst/moodle-tool_wsformat/ci.yml?branch=main&label=ci)

Information
-----------
This Moodle plugin:
* exports webservices as a Postman Collection or list of cURL commands
* exported files include the site URL and chosen external service's token
* creates token for external service for current user if it doesn't exist
* creates custom external service and token automatically for testing purposes
* exported webservices automatically added to plugin's custom external service if selected

Installation
------------------------------
1. Navigate to Moodle root directory and clone repo into `admin/tool`
2. [Run Moodle upgrade](https://docs.moodle.org/403/en/Installing_plugins)
3. No further configuration needed.

How to Use
------------------------------
1. Navigate to `Site Administration -> Development -> Format Webservices`
2. Select webservices to export
3. Select external service
4. Submit form using `Update Selection` button
5. Select chosen export format
6. Click `Export all` to begin download

Credit
------------------------------
<img alt="QUT" src="https://cms.qut.edu.au/__data/assets/image/0007/871027/QUT_REALWORLD_LOGO_ANCHOR_LEFT_paths2.png" width="400">
This plugin was originally created by students from Queensland University of Technology as part of their final year Capstone project.
* [Djarran Cotleanu](https://github.com/djarran)
* [Zach Pregl](https://github.com/ZachPregl)
* [Jacqueline ...](https://github.com/FoxxyFace)
* [Henry Mai](https://github.com/mmh140502)

Plugin development supported by [Catalyst IT Australia](https://www.catalyst-au.net/)

<img alt="Catalyst IT" src="https://cdn.rawgit.com/catalyst/moodle-auth_saml2/master/pix/catalyst-logo.svg" width="400">






